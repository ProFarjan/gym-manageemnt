<?php

namespace App\Notifications;

use App\Models\Member;
use App\Notifications\Concerns\FiltersAvailableChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClosureReminderNotification extends Notification implements ShouldQueue
{
    use FiltersAvailableChannels, Queueable;

    /**
     * @param  int  $daysRemaining  0 means this is the Final Day warning.
     */
    public function __construct(public int $daysRemaining)
    {
    }

    private function label(): string
    {
        return $this->daysRemaining === 0 ? 'Final Day' : "{$this->daysRemaining} day(s)";
    }

    public function via(object $notifiable): array
    {
        return $this->availableChannels($notifiable, ['mail', 'sms']);
    }

    public function toMail(Member $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required: Your Membership Will Be Closed')
            ->greeting("Hi {$notifiable->full_name},")
            ->line("Your membership has been unpaid since {$notifiable->due_date->format('d M Y')}.")
            ->line('Remaining time before permanent closure: '.$this->label().'.')
            ->line('Once closed, you will need to complete a new admission to rejoin.')
            ->action('Renew Now', route('member.renew'))
            ->line('Please renew as soon as possible to keep your membership.');
    }

    public function toSms(Member $notifiable): string
    {
        return "GirliGirl Gym: Your membership will be permanently closed in ".$this->label().". Renew now to avoid losing your membership.";
    }
}
