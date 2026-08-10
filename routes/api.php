<?php

use App\Http\Controllers\Api\ZKTecoSyncController;
use Illuminate\Support\Facades\Route;

// ZKTeco "Local Service" mode — the Windows service (ZKTeco-Windows-Service)
// running on a PC with LAN access to the physical device calls this on its
// own sync cycle (default every 5 min), authenticating via the
// zkteco.api-key middleware (X-API-Key header, matched against the
// zkteco_api_key admin setting) instead of a browser session. sync()
// accepts pushed attendance (possibly empty) and returns any pending
// device-management commands in the same response, so commands aren't
// delayed until attendance happens to be pushed.
Route::prefix('zkteco')->middleware('zkteco.api-key')->group(function () {
    Route::post('/sync', [ZKTecoSyncController::class, 'sync'])->name('api.zkteco.sync');
    Route::post('/commands/{command}/result', [ZKTecoSyncController::class, 'postResult'])->name('api.zkteco.commands.result');
});
