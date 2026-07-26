<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalTrainingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sessions_count',
        'price',
        'validity_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function memberAssignments(): HasMany
    {
        return $this->hasMany(MemberTrainingPackage::class);
    }
}
