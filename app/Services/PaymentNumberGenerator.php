<?php

namespace App\Services;

use App\Models\Payment;

class PaymentNumberGenerator
{
    public static function receiptNumber(): string
    {
        return self::generate('RCP', fn ($candidate) => Payment::where('receipt_number', $candidate)->exists());
    }

    public static function invoiceNumber(): string
    {
        return self::generate('INV', fn ($candidate) => Payment::where('invoice_number', $candidate)->exists());
    }

    private static function generate(string $prefix, callable $exists): string
    {
        $sequence = Payment::count() + 1;
        $candidate = sprintf('%s-%06d', $prefix, $sequence);

        while ($exists($candidate)) {
            $sequence++;
            $candidate = sprintf('%s-%06d', $prefix, $sequence);
        }

        return $candidate;
    }
}
