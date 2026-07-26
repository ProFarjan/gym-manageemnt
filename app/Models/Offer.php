<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'discount_type',
        'discount_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'discount_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function isCurrentlyRunning(): bool
    {
        return $this->is_active
            && now()->toDateString() >= $this->start_date->toDateString()
            && now()->toDateString() <= $this->end_date->toDateString();
    }
}
