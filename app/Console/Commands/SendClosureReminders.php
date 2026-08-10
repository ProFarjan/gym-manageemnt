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
    protected $description = 'Send countdown reminders to Expired members before permanent closure, on the milestones configured in Settings > Membership';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent = 0;

        $milestones = collect(explode(',', setting('closure_reminder_days', '15,10,5,1,0')))
            ->map(fn ($d) => (int) trim($d))
            ->unique();

        $members = Member::where('status', 'expired')->whereNotNull('due_date')->get();

        foreach ($members as $member) {
            $closureDate = $member->due_date->copy()->addMonths(3)->startOfDay();
            $daysRemaining = (int) today()->diffInDays($closureDate, false);

            if ($milestones->contains($daysRemaining)) {
                $member->notify(new ClosureReminderNotification($daysRemaining));
                $sent++;
            }
        }

        $this->info("{$sent} closure reminder(s) sent.");

        return self::SUCCESS;
    }
}
