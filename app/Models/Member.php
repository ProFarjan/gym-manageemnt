<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'admission_id',
        'full_name',
        'mobile_number',
        'email',
        'password',
        'date_of_birth',
        'address',
        'nid_number',
        'nid_image_path',
        'photo_path',
        'emergency_contact',
        'height',
        'weight',
        'blood_group',
        'fitness_goal',
        'medical_conditions',
        'membership_plan_id',
        'status',
        'registration_type',
        'admission_date',
        'due_date',
        'closed_at',
        'discount_amount',
        'discount_reason',
        'registered_by',
        'zkteco_user_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'due_date' => 'date',
            'closed_at' => 'datetime',
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function memberTrainingPackages(): HasMany
    {
        return $this->hasMany(MemberTrainingPackage::class);
    }

    public function isLifetime(): bool
    {
        return (bool) $this->membershipPlan?->is_lifetime;
    }
}
