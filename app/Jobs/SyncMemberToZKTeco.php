<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\ZKTecoCommand;
use App\Models\ZKTecoSyncLog;
use App\Services\ZKTeco\ZKTecoDeviceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncMemberToZKTeco implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public int $syncLogId)
    {
    }

    public function handle(ZKTecoDeviceClient $client): void
    {
        $log = ZKTecoSyncLog::with('member')->find($this->syncLogId);

        if (! $log) {
            return;
        }

        $log->increment('attempts');

        // In Local Service mode the Laravel server has no network path to
        // the device by design — a direct TCP attempt here would just fail
        // (or worse, spuriously succeed if the server happens to have
        // incidental access, bypassing the intended architecture). Queue a
        // command for the Windows service instead and leave this log
        // 'pending' — ZKTecoSyncController::sync() finalizes it (success or
        // failed) once the service reports the result back.
        if (setting('zkteco_mode', 'direct') === 'service') {
            $this->queueForLocalService($log);

            return;
        }

        $method = match ($log->action) {
            'create_user' => 'createUser',
            'update_user' => 'updateUser',
            'disable_user' => 'disableUser',
            'delete_user' => 'deleteUser',
        };

        $client->{$method}($log->member);

        $log->update([
            'status' => 'success',
            'synced_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * The device has no soft-disable command — 'disable_user' maps to the
     * same delete_user command the Windows service already understands
     * (same physical device operation as a real delete), just flagged so
     * ZKTecoSyncController keeps the member's zkteco_user_id instead of
     * clearing it, mirroring Direct mode's ZKTecoDeviceClient::disableUser().
     */
    private function queueForLocalService(ZKTecoSyncLog $log): void
    {
        /** @var Member $member */
        $member = $log->member;

        // The Windows service's executeDeviceCommand() (service_worker.php)
        // requires payload['user_id'] for every one of these command types —
        // it's the device userid string it looks up (delete_user/update_user)
        // or enrolls under (create_user), not Laravel's own member_id. This
        // is always the numeric-only ID (Member::zktecoDeviceUserId()), not
        // admission_id with its "GG" prefix, since the device is
        // keypad/numeric-ID driven — recomputed here rather than trusting a
        // stale zkteco_user_id, in case admission_id changed since it was
        // last set.
        $deviceUserId = $member->zktecoDeviceUserId();

        [$type, $payload] = match ($log->action) {
            'create_user' => ['create_user', ['member_id' => $member->id, 'user_id' => $deviceUserId, 'name' => $member->full_name]],
            'update_user' => ['update_user', ['member_id' => $member->id, 'user_id' => $deviceUserId, 'name' => $member->full_name]],
            'disable_user' => ['delete_user', ['member_id' => $member->id, 'user_id' => $deviceUserId, 'keep_zkteco_user_id' => true]],
            'delete_user' => ['delete_user', ['member_id' => $member->id, 'user_id' => $deviceUserId]],
        };

        ZKTecoCommand::create([
            'zkteco_sync_log_id' => $log->id,
            'type' => $type,
            'payload' => $payload,
            'status' => 'pending',
        ]);
    }

    public function failed(Throwable $exception): void
    {
        ZKTecoSyncLog::whereKey($this->syncLogId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
