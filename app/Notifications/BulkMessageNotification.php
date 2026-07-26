<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BulkMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels  Any of 'mail', 'sms'.
     */
    public function __construct(public string $subject, public string $body, public array $channels)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->channels;
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
