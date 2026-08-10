<?php

use App\Http\Controllers\Api\ZKTecoSyncController;
use Illuminate\Support\Facades\Route;

// ZKTeco "Local Service" mode — the Windows service (ZKTeco-Windows-Service)
// running on a PC with LAN access to the physical device calls this single
// endpoint on its own sync cycle (default every 5 min), authenticating via
// the zkteco.api-key middleware (X-API-Key header, matched against the
// zkteco_api_key admin setting) instead of a browser session. One request
// pushes attendance + reports results for previously-executed commands;
// one response returns newly queued commands — no separate polling or
// result-posting endpoint.
Route::prefix('zkteco')->middleware('zkteco.api-key')->group(function () {
    Route::post('/sync', [ZKTecoSyncController::class, 'sync'])->name('api.zkteco.sync');
});
