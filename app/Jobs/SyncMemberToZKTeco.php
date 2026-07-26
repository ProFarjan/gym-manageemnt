<?php

namespace App\Jobs;

use App\Models\ZKTecoSyncLog;
use App\Services\ZKTeco\ZKTecoDeviceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncMemberToZKTeco implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public int $syncLogId)
    {
    }

    public function handle(ZKTecoDeviceClient $client): void
    {
        $log = ZKTecoSyncLog::with('member')->find($this->syncLogId);

        if (! $log) {
            return;
        }

        $log->increment('attempts');

        $method = match ($log->action) {
            'create_user' => 'createUser',
            'update_user' => 'updateUser',
            'disable_user' => 'disableUser',
            'delete_user' => 'deleteUser',
        };

        $client->{$method}($log->member);

        $log->update([
            'status' => 'success',
            'synced_at' => now(),
            'error_message' => null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        ZKTecoSyncLog::whereKey($this->syncLogId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
