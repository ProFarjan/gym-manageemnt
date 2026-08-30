<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locker extends Model
{
    use HasFactory;

    protected $fillable = [
        'locker_number',
        'location',
        'member_id',
        'rent_due_date',
        'assigned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rent_due_date' => 'date',
            'assigned_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function isAssigned(): bool
    {
        return $this->member_id !== null;
    }

    public function isOverdue(): bool
    {
        return $this->rent_due_date !== null && $this->rent_due_date->isPast();
    }
}
