<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTrainingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'personal_training_package_id',
        'trainer_id',
        'sessions_used',
        'purchased_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(PersonalTrainingPackage::class, 'personal_training_package_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function sessionsRemaining(): int
    {
        return max(0, $this->package->sessions_count - $this->sessions_used);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
