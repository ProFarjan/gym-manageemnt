<?php

namespace App\Notifications;

use App\Models\Member;
use App\Models\Payment;
use App\Notifications\Concerns\FiltersAvailableChannels;
use App\Services\SmsTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use FiltersAvailableChannels, Queueable;

    public function __construct(public Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->availableChannels($notifiable, ['mail', 'sms']);
    }

    public function toMail(Member $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Payment Received - GirliGirl Gym & Fitness')
            ->greeting("Hi {$notifiable->full_name},")
            ->line('We have received your payment.')
            ->line('Amount: '.number_format($this->payment->amount, 2).' BDT')
            ->line('Receipt No: '.$this->payment->receipt_number);

        if ($this->payment->period_end) {
            $mail->line('New Due Date: '.$this->payment->period_end->format('d M Y'));
        }

        return $mail->line('Thank you for your payment.');
    }

    public function toSms(Member $notifiable): string
    {
        return SmsTemplateRenderer::render(
            'sms_template_payment_received',
            'Payment received: {{amount}} BDT. Receipt: {{receipt_number}}.',
            [
                'business_name' => setting('business_name', config('app.name')),
                'full_name' => $notifiable->full_name,
                'amount' => number_format($this->payment->amount, 2),
                'receipt_number' => $this->payment->receipt_number,
                'due_date' => $this->payment->period_end?->format('d M Y') ?? '',
            ]
        );
    }
}
