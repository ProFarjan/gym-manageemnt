<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per email the app has actually attempted to send — populated
 * automatically for every successful send via the MessageSent event
 * listener (AppServiceProvider), regardless of which notification/mailable
 * triggered it. Failed sends are only logged where the caller already has
 * a try/catch around Mail::send() (currently just the Settings > Email
 * test-send), since Laravel has no equivalent global "mail failed" event.
 */
class MailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to_address',
        'subject',
        'mailer',
        'status',
        'error_message',
    ];
}
