<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Contact Message - '.setting('business_name', config('app.name')))
            ->greeting('New website enquiry')
            ->line("From: {$this->contactMessage->name} ({$this->contactMessage->email})")
            ->line($this->contactMessage->subject ? "Subject: {$this->contactMessage->subject}" : '')
            ->line($this->contactMessage->message);
    }
}
