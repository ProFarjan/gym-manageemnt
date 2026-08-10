<?php

namespace App\Observers;

use App\Jobs\SyncMemberToZKTeco;
use App\Models\Bill;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\ZKTecoSyncLog;
use App\Notifications\RegistrationConfirmation;
use App\Services\BillNumberGenerator;
use App\Services\RenewalBillGenerator;

class MemberObserver
{
    public function created(Member $member): void
    {
        if ($member->status === 'active') {
            $this->queueSync($member, 'create_user');
        }

        if ($member->membership_plan_id && $member->membershipPlan) {
            $charges = $this->planCharges($member->membershipPlan, $member->discount_amount);

            Bill::create([
                'member_id' => $member->id,
                'membership_plan_id' => $member->membership_plan_id,
                'bill_number' => BillNumberGenerator::generate(),
                ...$charges,
                'due_date' => now(),
            ]);
        }

        $member->notify(new RegistrationConfirmation);
    }

    public function updated(Member $member): void
    {
        if ($member->wasChanged('status')) {
            $previous = $member->getOriginal('status');
            $current = $member->status;

            match (true) {
                $current === 'active' && $previous === 'pending' => $this->queueSync($member, 'create_user'),
                $current === 'active' => $this->queueSync($member, 'update_user'),
                $current === 'expired' => $this->queueSync($member, 'disable_user'),
                $current === 'closed' => $this->queueSync($member, 'delete_user'),
                default => null,
            };

            if ($current === 'expired') {
                RenewalBillGenerator::generateFor($member);
            }

            if ($current === 'closed') {
                RenewalBillGenerator::removeUnpaidFor($member);
            }

            return;
        }

        if ($member->status === 'active' && $member->wasChanged(['full_name', 'photo_path'])) {
            $this->queueSync($member, 'update_user');
        }

        // The Membership section (Plan/Discount) is only editable on the member
        // edit form while status is "pending" — a pending member can't have any
        // completed payments yet (any payment auto-activates them), so their
        // registration bill is always safe to freely recompute here.
        if ($member->status === 'pending' && $member->wasChanged(['membership_plan_id', 'discount_amount'])) {
            $this->syncPendingBill($member);
        }
    }

    private function syncPendingBill(Member $member): void
    {
        $bill = $member->bills()->with('payments')->latest('id')->first();

        if (! $bill || $bill->paidAmount() > 0) {
            return;
        }

        $plan = MembershipPlan::find($member->membership_plan_id);

        if (! $plan) {
            return;
        }

        $bill->update([
            'membership_plan_id' => $member->membership_plan_id,
            ...$this->planCharges($plan, $member->discount_amount),
        ]);
    }

    /**
     * @return array{admission_fee_amount: float, monthly_amount: float, amount: float, discount_amount: float}
     */
    private function planCharges(MembershipPlan $plan, float $discountAmount): array
    {
        $admissionFeeAmount = $plan->admissionFeeCharged();
        $monthlyAmount = (float) $plan->price;

        return [
            'admission_fee_amount' => $admissionFeeAmount,
            'monthly_amount' => $monthlyAmount,
            'amount' => max(0, $admissionFeeAmount + $monthlyAmount - $discountAmount),
            'discount_amount' => $discountAmount,
        ];
    }

    private function queueSync(Member $member, string $action): void
    {
        $log = ZKTecoSyncLog::create([
            'member_id' => $member->id,
            'action' => $action,
            'status' => 'pending',
        ]);

        SyncMemberToZKTeco::dispatch($log->id);
    }
}
