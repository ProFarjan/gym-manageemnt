<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Stub SMS channel. No SMS gateway account has been configured yet (SRS calls
 * for a configurable "SMS Gateway" in Settings — see config/sms.php), so this
 * logs what would be sent instead of calling a real provider. Swap the body
 * for an HTTP call to the chosen gateway (common BD providers use a simple
 * REST API) once credentials exist.
 */
class SmsChannel
{
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $mobile = $notifiable->mobile_number ?? null;

        if (! $mobile) {
            return;
        }

        $message = $notification->toSms($notifiable);

        Log::info("[SMS stub] To {$mobile}: {$message}");
    }
}
