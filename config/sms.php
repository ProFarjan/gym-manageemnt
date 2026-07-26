<?php

// TODO: move these into the Settings module (Phase 10) so Admin can edit them
// from the Admin Panel instead of the .env file. No SMS gateway account has
// been configured yet, so the SmsChannel is a stub — see
// app/Notifications/Channels/SmsChannel.php.
return [
    'driver' => env('SMS_DRIVER', 'log'),
    'api_key' => env('SMS_API_KEY'),
    'sender_id' => env('SMS_SENDER_ID'),
];
