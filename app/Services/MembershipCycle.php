<?php

namespace App\Services;

use App\Models\MembershipPlan;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class MembershipCycle
{
    /**
     * Calculate the next due date for a plan, honoring the fixed-calendar-day
     * renewal rule and falling back to the last day of the month when the
     * anchor day doesn't exist in the target month (e.g. 31st -> Feb 28/29).
     */
    public static function nextDueDate(CarbonInterface $from, MembershipPlan $plan): ?Carbon
    {
        if ($plan->is_lifetime || ! $plan->duration_in_months) {
            return null;
        }

        return Carbon::instance($from)->copy()->addMonthsNoOverflow($plan->duration_in_months);
    }

    /**
     * Extend an existing due date by one or more billing cycles. Always anchors
     * to the prior due date (not "today") so the fixed-calendar-day rule holds
     * even when a renewal payment is made early, late, or catching up multiple
     * missed cycles at once.
     */
    public static function extend(CarbonInterface $currentDueDate, MembershipPlan $plan, int $periods = 1): ?Carbon
    {
        if ($plan->is_lifetime || ! $plan->duration_in_months) {
            return null;
        }

        return Carbon::instance($currentDueDate)->copy()->addMonthsNoOverflow($plan->duration_in_months * $periods);
    }

    /**
     * Same fixed-calendar-day extension as extend(), but for a raw month count
     * not tied to any MembershipPlan — used by manually created bills (the
     * open "Create Bill" form's Duration field), which aren't necessarily
     * priced against the member's actual plan.
     */
    public static function extendByMonths(CarbonInterface $currentDueDate, int $months): Carbon
    {
        return Carbon::instance($currentDueDate)->copy()->addMonthsNoOverflow($months);
    }
}
