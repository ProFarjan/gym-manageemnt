<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class AutoCheckoutAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-checkout-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-checkout any attendance still open past gym closing time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $closingTime = config('gym.closing_time');
        $closedCount = 0;

        Attendance::whereNull('check_out')->each(function (Attendance $attendance) use ($closingTime, &$closedCount) {
            $checkoutAt = $attendance->check_in->copy()->setTimeFromTimeString($closingTime);

            if ($checkoutAt->lessThanOrEqualTo($attendance->check_in)) {
                $checkoutAt = $attendance->check_in->copy()->addMinutes(1);
            }

            if (now()->lessThan($checkoutAt)) {
                return;
            }

            $attendance->check_out = $checkoutAt;
            $attendance->duration_minutes = $attendance->check_in->diffInMinutes($checkoutAt);
            $attendance->save();
            $closedCount++;
        });

        $this->info("Auto-checked-out {$closedCount} attendance record(s).");

        return self::SUCCESS;
    }
}
