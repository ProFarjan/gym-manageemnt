<?php

namespace App\Notifications;

use App\Models\Member;
use App\Notifications\Concerns\FiltersAvailableChannels;
use App\Services\SmsTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalReminderNotification extends Notification implements ShouldQueue
{
    use FiltersAvailableChannels, Queueable;

    /**
     * @param  int  $daysUntilDue  0 means due today.
     */
    public function __construct(public int $daysUntilDue)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->availableChannels($notifiable, ['mail', 'sms']);
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

        return SmsTemplateRenderer::render(
            'sms_template_renewal_reminder',
            '{{business_name}}: Your membership is due for renewal {{when}} ({{due_date}}). Please renew to avoid interruption.',
            [
                'business_name' => setting('business_name', config('app.name')),
                'full_name' => $notifiable->full_name,
                'when' => $when,
                'due_date' => $notifiable->due_date->format('d M Y'),
            ]
        );
    }
}
