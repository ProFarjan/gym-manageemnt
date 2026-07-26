<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Notifications\ClosureReminderNotification;
use Illuminate\Console\Command;

class SendClosureReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-closure-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 15/10/5/1/Final-day countdown reminders to Expired members before permanent closure';

    private const MILESTONES = [15, 10, 5, 1, 0];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent = 0;

        $members = Member::where('status', 'expired')->whereNotNull('due_date')->get();

        foreach ($members as $member) {
            $closureDate = $member->due_date->copy()->addMonths(3)->startOfDay();
            $daysRemaining = (int) today()->diffInDays($closureDate, false);

            if (in_array($daysRemaining, self::MILESTONES, true)) {
                $member->notify(new ClosureReminderNotification($daysRemaining));
                $sent++;
            }
        }

        $this->info("{$sent} closure reminder(s) sent.");

        return self::SUCCESS;
    }
}
