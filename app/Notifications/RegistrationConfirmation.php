<?php

namespace App\Notifications;

use App\Models\Member;
use App\Notifications\Concerns\FiltersAvailableChannels;
use App\Services\SmsTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationConfirmation extends Notification implements ShouldQueue
{
    use FiltersAvailableChannels, Queueable;

    public function via(object $notifiable): array
    {
        return $this->availableChannels($notifiable, ['mail', 'sms']);
    }

    public function toMail(Member $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to GirliGirl Gym & Fitness')
            ->greeting("Hi {$notifiable->full_name},")
            ->line("Your admission ID is {$notifiable->admission_id}.")
            ->line($notifiable->status === 'pending'
                ? 'Your registration is pending payment approval. We will activate your membership once payment is confirmed.'
                : 'Your membership is now active. Welcome to the gym!')
            ->line('Thank you for choosing GirliGirl Gym & Fitness.');
    }

    public function toSms(Member $notifiable): string
    {
        return SmsTemplateRenderer::render(
            'sms_template_registration',
            'Welcome to {{business_name}}! Your Admission ID: {{admission_id}}. Status: {{status}}',
            [
                'business_name' => setting('business_name', config('app.name')),
                'full_name' => $notifiable->full_name,
                'admission_id' => $notifiable->admission_id,
                'status' => ucfirst($notifiable->status),
            ]
        );
    }
}
