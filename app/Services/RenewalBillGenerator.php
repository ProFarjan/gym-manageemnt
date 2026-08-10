<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Member;

/**
 * Auto-creates a renewal Bill the moment a member expires (see
 * MemberObserver::updated()), gated behind Settings > Membership's
 * "Auto-Generate Renewal Bill" toggle. Always bills exactly one month at a
 * time — price / duration_in_months — even when the member's plan is a
 * multi-month package (e.g. a 3-month plan charges 1/3 of its price per
 * generated bill), so renewing doesn't force paying for the whole original
 * package again. duration_months is set to 1 on the bill so
 * PaymentRecorder::applyBillDurationIfJustCompleted() extends the member's
 * due date by exactly one month once it's paid off, regardless of how many
 * months the original plan spanned.
 */
class RenewalBillGenerator
{
    public static function generateFor(Member $member): ?Bill
    {
        if (! setting('auto_generate_renewal_bill')) {
            return null;
        }

        $plan = $member->membershipPlan;

        if (! $plan || $plan->is_lifetime || ! $plan->duration_in_months) {
            return null;
        }

        $monthlyRate = round((float) $plan->price / $plan->duration_in_months, 2);

        if ($monthlyRate <= 0) {
            return null;
        }

        $bill = Bill::create([
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'bill_number' => BillNumberGenerator::generate(),
            'admission_fee_amount' => 0,
            'monthly_amount' => 0,
            'amount' => $monthlyRate,
            'discount_amount' => 0,
            'due_date' => now(),
            'duration_months' => 1,
            'is_auto_renewal' => true,
            'notes' => "Auto-generated renewal bill ({$plan->name}, 1 of {$plan->duration_in_months} month(s)).",
        ]);

        BillItem::create([
            'bill_id' => $bill->id,
            'particular' => "Monthly Renewal — {$plan->name}",
            'qty' => 1,
            'unit_price' => $monthlyRate,
            'total' => $monthlyRate,
        ]);

        return $bill;
    }

    /**
     * Delete every unpaid auto-renewal bill left over when a member is
     * permanently closed — a bill for access they were never granted (they
     * never paid) shouldn't remain outstanding forever. Only ever touches
     * bills this generator created (is_auto_renewal) and only ever ones
     * with zero completed payments against them, so a bill with real
     * payment history (even a partial payment) is never deleted.
     */
    public static function removeUnpaidFor(Member $member): void
    {
        $member->bills()
            ->where('is_auto_renewal', true)
            ->with('payments')
            ->get()
            ->each(function (Bill $bill) {
                if ($bill->paidAmount() <= 0) {
                    $bill->delete();
                }
            });
    }
}
