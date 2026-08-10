<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per SMS the app has attempted to send via SmsGateway::send() —
 * covers both real notifications (member registration, renewal/closure
 * reminders, etc, via the "sms" notification channel) and the Settings >
 * SMS Gateway test-send, since both funnel through that single method.
 */
class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_number',
        'message',
        'status',
        'response_message',
    ];
}
