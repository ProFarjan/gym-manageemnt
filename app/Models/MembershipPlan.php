<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_in_months',
        'is_lifetime',
        'price',
        'admission_fee',
        'admission_discount',
        'admission_free',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_lifetime' => 'boolean',
            'admission_free' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'admission_fee' => 'decimal:2',
            'admission_discount' => 'decimal:2',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
