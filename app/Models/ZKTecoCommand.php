<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A queued device-management command (create/update/delete a user, or list
 * all users) for "Local Service" mode — the Laravel server can't reach the
 * device directly in this mode, so the Windows service picks these up when
 * it calls the /api/zkteco/sync endpoint and executes them locally against
 * the device, then reports the result back.
 */
class ZKTecoCommand extends Model
{
    use HasFactory;

    protected $table = 'zkteco_commands';

    protected $fillable = [
        'zkteco_sync_log_id',
        'type',
        'payload',
        'status',
        'result',
        'sent_at',
        'completed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function syncLog(): BelongsTo
    {
        return $this->belongsTo(ZKTecoSyncLog::class, 'zkteco_sync_log_id');
    }
}
