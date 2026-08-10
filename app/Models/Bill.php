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
        'admission_fee_amount',
        'monthly_amount',
        'amount',
        'discount_amount',
        'due_date',
        'duration_months',
        'duration_applied_at',
        'is_auto_renewal',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'admission_fee_amount' => 'decimal:2',
            'monthly_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'due_date' => 'date',
            'duration_applied_at' => 'datetime',
            'is_auto_renewal' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * How much of this bill has been resolved (completed payments only — a
     * refund flips a payment's status away from "completed", so this stays
     * correct automatically with no separate sync step needed). A discount
     * given at payment time counts toward resolving the bill just like cash
     * does — the member no longer owes that portion — so this is cash +
     * discount, not cash minus discount (that "net cash collected" figure
     * is what discountGiven() + this pair give you separately).
     */
    public function paidAmount(): float
    {
        return (float) $this->payments
            ->where('status', 'completed')
            ->sum(fn (Payment $payment) => $payment->amount + $payment->discount_amount);
    }

    /**
     * Total discount given across completed payments against this bill —
     * shown separately so it's clear how much of paidAmount() was waived
     * rather than actually collected as cash.
     */
    public function discountGiven(): float
    {
        return (float) $this->payments
            ->where('status', 'completed')
            ->sum('discount_amount');
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

    /**
     * Itemized breakdown shown on the printable bill. Manually created bills
     * (the open "Create Bill" form) have real BillItem rows; auto-created
     * registration bills (MemberObserver) never do, so they always fall
     * through to the legacy Admission Fee / Monthly Charge composition —
     * admission fee is omitted entirely when the plan didn't charge one.
     * Both shapes carry qty/unit_price/amount uniformly for the invoice table.
     */
    public function lineItems(): array
    {
        if ($this->items->isNotEmpty()) {
            return $this->items->map(fn (BillItem $item) => [
                'label' => $item->particular,
                'qty' => (float) $item->qty,
                'unit_price' => (float) $item->unit_price,
                'amount' => (float) $item->total,
            ])->all();
        }

        $items = [];

        if ((float) $this->admission_fee_amount > 0) {
            $items[] = [
                'label' => 'Admission Fee',
                'qty' => 1.0,
                'unit_price' => (float) $this->admission_fee_amount,
                'amount' => (float) $this->admission_fee_amount,
            ];
        }

        $items[] = [
            'label' => 'Monthly Charge ('.$this->created_at->format('F Y').')',
            'qty' => 1.0,
            'unit_price' => (float) $this->monthly_amount,
            'amount' => (float) $this->monthly_amount,
        ];

        return $items;
    }
}
