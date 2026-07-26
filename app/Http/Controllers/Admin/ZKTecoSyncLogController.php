<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncMemberToZKTeco;
use App\Models\ZKTecoSyncLog;
use Illuminate\Http\Request;

class ZKTecoSyncLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ZKTecoSyncLog::with('member')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.zkteco-sync-logs.index', compact('logs'));
    }

    public function retry(ZKTecoSyncLog $syncLog)
    {
        if ($syncLog->status !== 'failed') {
            return back()->withErrors(['sync' => 'Only failed sync entries can be retried.']);
        }

        $syncLog->update(['status' => 'pending', 'error_message' => null]);
        SyncMemberToZKTeco::dispatch($syncLog->id);

        return back()->with('status', 'Retry queued.');
    }
}
