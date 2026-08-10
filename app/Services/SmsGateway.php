<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Generic REST-based SMS gateway driven entirely by admin-configured
 * key/value params (Settings > SMS Gateway). Each row's KEY is the literal
 * query/form param name the provider expects (e.g. "to", "text", "apikey")
 * — its VALUE is sent as-is, except the placeholder tokens "@number" and
 * "@message" are substituted with the real recipient number and message
 * text wherever they appear, including inside the URL itself. This lets
 * each provider's exact param names and URL shape be configured without
 * any code changes. An optional "method" row (GET or POST, case-
 * insensitive) picks the HTTP verb — defaults to GET when not set.
 */
class SmsGateway
{
    /**
     * @return array{success: bool, message: string}
     */
    public static function send(string $number, string $message): array
    {
        $result = self::attempt($number, $message);

        try {
            SmsLog::create([
                'to_number' => $number,
                'message' => $message,
                'status' => $result['success'] ? 'sent' : 'failed',
                'response_message' => $result['message'],
            ]);
        } catch (Throwable) {
            // Never let logging break an SMS that already sent (or a
            // legitimate failure from being reported back to the caller).
        }

        return $result;
    }

    /**
     * @return array{success: bool, message: string}
     */
    private static function attempt(string $number, string $message): array
    {
        if (! setting('sms_enabled')) {
            return ['success' => false, 'message' => 'SMS Gateway is disabled.'];
        }

        $params = json_decode(setting('sms_gateway_params', '[]') ?: '[]', true) ?: [];

        // Param values are substituted raw — they're merged into $requestParams
        // and Http::get()/post() encodes them correctly when building the
        // request. The URL is a literal string the client sends as-is, so
        // anything spliced into it (a message can contain spaces, &, etc.)
        // must be pre-encoded or it produces an invalid/broken URI.
        $substituteParam = fn (string $value) => str_replace(['@number', '@message'], [$number, $message], $value);
        $substituteUrl = fn (string $value) => str_replace(
            ['@number', '@message'],
            [rawurlencode($number), rawurlencode($message)],
            $value
        );

        $url = null;
        $method = 'GET';
        $requestParams = [];

        foreach ($params as $row) {
            $key = trim($row['key'] ?? '');
            $value = trim($row['value'] ?? '');

            if ($key === '') {
                continue;
            }

            match (strtolower($key)) {
                'url' => $url = $substituteUrl($value),
                'method' => $method = strtoupper($value) === 'POST' ? 'POST' : 'GET',
                default => $requestParams[$key] = $substituteParam($value),
            };
        }

        if (! $url) {
            return ['success' => false, 'message' => 'Gateway is missing the URL parameter configuration.'];
        }

        try {
            // Http::get($url, []) — an *empty* array, not omitted — still
            // passes query=[] to Guzzle, which replaces (wipes) any query
            // string already embedded in $url. Only pass it when non-empty
            // so URL-embedded @number/@message placeholders survive.
            $response = $method === 'POST'
                ? Http::asForm()->timeout(15)->post($url, $requestParams)
                : ($requestParams === [] ? Http::timeout(15)->get($url) : Http::timeout(15)->get($url, $requestParams));

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Message sent.'];
            }

            return ['success' => false, 'message' => "Gateway responded with HTTP {$response->status()}."];
        } catch (Throwable $e) {
            Log::warning("SMS gateway send failed: {$e->getMessage()}");

            return ['success' => false, 'message' => 'Could not reach the SMS gateway: '.$e->getMessage()];
        }
    }
}
