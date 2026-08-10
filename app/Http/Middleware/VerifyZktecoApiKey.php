<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Machine-to-machine auth for the ZKTeco "Local Service" sync API — the
 * Windows service authenticates via a shared key (Settings > ZKTeco), sent
 * as the X-API-Key header, instead of a browser session.
 */
class VerifyZktecoApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = setting('zkteco_api_key');

        if (! $configured || ! hash_equals($configured, (string) $request->header('X-API-Key'))) {
            return response()->json(['success' => false, 'message' => 'Invalid or missing API key.'], 401);
        }

        return $next($request);
    }
}
