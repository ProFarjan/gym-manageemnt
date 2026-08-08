<?php

namespace App\Notifications\Channels;

use App\Services\SmsGateway;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Sends through the generic gateway configured at Admin > Settings > SMS
 * Gateway when enabled; otherwise logs what would have been sent so nothing
 * throws while the gateway is off or unconfigured.
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

        if (! setting('sms_enabled')) {
            Log::info("[SMS stub, gateway disabled] To {$mobile}: {$message}");

            return;
        }

        $result = SmsGateway::send($mobile, $message);

        if (! $result['success']) {
            Log::warning("[SMS send failed] To {$mobile}: {$result['message']}");
        }
    }
}
