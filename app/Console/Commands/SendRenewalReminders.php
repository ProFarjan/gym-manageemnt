<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Notifications\RenewalReminderNotification;
use Illuminate\Console\Command;

class SendRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-renewal-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 3-days-before and due-date renewal reminders to Active members';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent = 0;

        foreach ([3, 0] as $daysUntilDue) {
            $members = Member::where('status', 'active')
                ->whereDate('due_date', today()->addDays($daysUntilDue))
                ->get();

            foreach ($members as $member) {
                $member->notify(new RenewalReminderNotification($daysUntilDue));
                $sent++;
            }
        }

        $this->info("{$sent} renewal reminder(s) sent.");

        return self::SUCCESS;
    }
}
