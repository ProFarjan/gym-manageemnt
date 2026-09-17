<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Services\ZKTeco\ZKTecoProtocolClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * One-shot bulk push: create/overwrite every active member as a user on the
 * ZKTeco device directly over Direct IP mode, using the member's own id as
 * the device uid and admission_id as the device user_id (same convention as
 * ZKTecoDeviceClient::pushUser()), all with the given fingerprint/keypad
 * login password. Opens a single device connection for the whole batch
 * instead of reconnecting per member, since this is 80+ writes in one go.
 */
class PushActiveMembersToZKTeco extends Command
{
    protected $signature = 'zkteco:push-active-members {--password=123}';

    protected $description = 'Create/update every active member as a user on the ZKTeco device (Direct IP mode)';

    public function handle(): int
    {
        $ip = setting('zkteco_ip');
        $port = (int) setting('zkteco_port', 4370);

        if (! $ip) {
            $this->error('Set the Device IP in ZKTeco Settings first.');

            return self::FAILURE;
        }

        $password = (string) $this->option('password');

        $members = Member::where('status', 'active')->orderBy('id')->get();
        $this->info("Pushing {$members->count()} active member(s) to {$ip}:{$port} ...");

        $client = new ZKTecoProtocolClient;
        $connect = $client->connect($ip, $port);

        if (! $connect['success']) {
            $this->error($connect['message']);

            return self::FAILURE;
        }

        $success = 0;
        $failed = 0;
        $bar = $this->output->createProgressBar($members->count());
        $bar->start();

        try {
            foreach ($members as $member) {
                try {
                    $userId = $member->admission_id;
                    $ok = $client->setUser($member->id, $userId, $member->full_name, $password);

                    if (! $ok) {
                        throw new \RuntimeException('Device rejected the write.');
                    }

                    if ($member->zkteco_user_id !== $userId) {
                        $member->forceFill(['zkteco_user_id' => $userId])->save();
                    }

                    $success++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning("[ZKTeco] bulk push failed for member #{$member->id} ({$member->admission_id}): {$e->getMessage()}");
                }

                $bar->advance();
            }
        } finally {
            $client->disconnect();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$success} succeeded, {$failed} failed.");

        if ($failed > 0) {
            $this->warn('Check storage/logs/laravel.log for per-member failure details.');
        }

        return self::SUCCESS;
    }
}
