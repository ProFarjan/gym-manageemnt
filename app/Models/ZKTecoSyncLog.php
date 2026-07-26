<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZKTecoSyncLog extends Model
{
    use HasFactory;

    protected $table = 'zkteco_sync_logs';

    protected $fillable = [
        'member_id',
        'action',
        'status',
        'attempts',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
