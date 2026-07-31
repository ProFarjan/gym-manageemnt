<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'bill_number',
        'amount',
        'discount_amount',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Net amount actually received against this bill (completed payments only —
     * a refund flips a payment's status away from "completed", so this stays
     * correct automatically with no separate sync step needed).
     */
    public function paidAmount(): float
    {
        return (float) $this->payments
            ->where('status', 'completed')
            ->sum(fn (Payment $payment) => $payment->amount - $payment->discount_amount);
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->amount - $this->paidAmount());
    }

    public function statusLabel(): string
    {
        $paid = $this->paidAmount();

        if ($paid <= 0) {
            return 'unpaid';
        }

        return $paid < (float) $this->amount ? 'partial' : 'paid';
    }
}
