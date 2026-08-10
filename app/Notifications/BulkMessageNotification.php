<?php

namespace App\Notifications;

use App\Models\Member;
use App\Notifications\Concerns\FiltersAvailableChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkMessageNotification extends Notification implements ShouldQueue
{
    use FiltersAvailableChannels, Queueable;

    /**
     * @param  array<int, string>  $channels  Any of 'mail', 'sms', as picked by the admin.
     */
    public function __construct(public string $subject, public string $body, public array $channels)
    {
    }

    public function via(object $notifiable): array
    {
        // Respects the admin's channel picks, but still skips 'mail' for a
        // member with no email address (etc) rather than queuing a job
        // that's doomed to fail once it reaches that channel.
        return $this->availableChannels($notifiable, $this->channels);
    }

    public function toMail(Member $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting("Hi {$notifiable->full_name},")
            ->line($this->body);
    }

    public function toSms(Member $notifiable): string
    {
        return $this->body;
    }
}
