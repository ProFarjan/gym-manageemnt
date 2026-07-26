<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Payment;

class PaymentRecorder
{
    private const MEMBERSHIP_TYPES = ['admission', 'monthly', 'package', 'renewal'];

    /**
     * Record a payment for a member and apply the resulting membership status /
     * due-date side effects. Shared by the admin manual-payment form and the
     * member-portal online renewal flow so both stay consistent.
     */
    public static function record(Member $member, array $data): Payment
    {
        $periodStart = $member->due_date ?? $member->admission_date;

        $payment = new Payment([
            'member_id' => $member->id,
            'payment_account_id' => $data['payment_account_id'],
            'type' => $data['type'],
            'method' => $data['method'],
            'amount' => $data['amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'discount_reason' => $data['discount_reason'] ?? null,
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'status' => 'completed',
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);
        $payment->receipt_number = PaymentNumberGenerator::receiptNumber();
        $payment->invoice_number = PaymentNumberGenerator::invoiceNumber();

        if (in_array($data['type'], self::MEMBERSHIP_TYPES, true)) {
            $plan = $member->membershipPlan;
            $periods = $data['periods'] ?? 1;

            if ($member->status === 'pending') {
                $member->admission_date = now();
                $member->due_date = MembershipCycle::nextDueDate(now(), $plan);
            } else {
                $member->due_date = MembershipCycle::extend($member->due_date ?? now(), $plan, $periods);
            }

            $member->status = 'active';
            $member->save();

            $payment->period_start = $periodStart;
            $payment->period_end = $member->due_date;
        }

        $payment->save();

        return $payment;
    }
}
