<?php

namespace App\Services;

use App\Models\Bill;

class BillNumberGenerator
{
    public static function generate(): string
    {
        $sequence = Bill::count() + 1;
        $candidate = sprintf('BILL-%06d', $sequence);

        while (Bill::where('bill_number', $candidate)->exists()) {
            $sequence++;
            $candidate = sprintf('BILL-%06d', $sequence);
        }

        return $candidate;
    }
}
