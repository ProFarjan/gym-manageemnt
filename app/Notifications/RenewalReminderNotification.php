<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  int  $daysUntilDue  0 means due today.
     */
    public function __construct(public int $daysUntilDue)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'sms'];
    }

    public function toMail(Member $notifiable): MailMessage
    {
        $when = $this->daysUntilDue === 0 ? 'today' : "in {$this->daysUntilDue} day(s)";

        return (new MailMessage)
            ->subject('Membership Renewal Reminder')
            ->greeting("Hi {$notifiable->full_name},")
            ->line("Your membership is due for renewal {$when} ({$notifiable->due_date->format('d M Y')}).")
            ->action('Renew Now', route('member.renew'))
            ->line('Please renew on time to avoid interruption to your gym access.');
    }

    public function toSms(Member $notifiable): string
    {
        $when = $this->daysUntilDue === 0 ? 'today' : "in {$this->daysUntilDue} day(s)";

        return "GirliGirl Gym: Your membership is due for renewal {$when} ({$notifiable->due_date->format('d M Y')}). Please renew to avoid interruption.";
    }
}
