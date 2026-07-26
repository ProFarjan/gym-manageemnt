<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Member;
use App\Services\ZKTeco\ZKTecoDeviceClient;
use Illuminate\Console\Command;

class PullZKTecoAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkteco:pull-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull fresh fingerprint/RFID punches from the ZKTeco device and record attendance';

    /**
     * Execute the console command.
     */
    public function handle(ZKTecoDeviceClient $client): int
    {
        $logs = $client->pullAttendanceLogs();

        foreach ($logs as $log) {
            $member = Member::find($log['member_id']);

            if (! $member) {
                continue;
            }

            $member->attendances()->create([
                'check_in' => $log['check_in'],
                'source' => 'fingerprint',
                'zkteco_log_id' => $log['zkteco_log_id'],
            ]);
        }

        $this->info(count($logs).' attendance record(s) pulled from device.');

        return self::SUCCESS;
    }
}
