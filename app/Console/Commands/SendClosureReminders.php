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

        // Includes 'closed', not just 'expired': app:sync-membership-statuses
        // runs earlier in the day (00:30) and already flips a member to
        // 'closed' once its closure date arrives, so on that exact day the
        // member is closed by the time this command runs — excluding
        // 'closed' here would silently swallow the "0 days / Final Day"
        // milestone every time. This is safe: for a member closed on any
        // earlier day, $daysRemaining is already negative and will never
        // match a (non-negative) configured milestone again.
        $members = Member::whereIn('status', ['expired', 'closed'])->whereNotNull('due_date')->get();

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
