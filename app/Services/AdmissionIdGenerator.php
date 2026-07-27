<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\DB;

class AdmissionIdGenerator
{
    public static function generate(): string
    {
        $prefix = setting('membership_prefix', 'GG');

        return DB::transaction(function () use ($prefix) {
            $sequence = Member::count() + 1;
            $candidate = sprintf('%s%05d', $prefix, $sequence);

            while (Member::where('admission_id', $candidate)->exists()) {
                $sequence++;
                $candidate = sprintf('%s%05d', $prefix, $sequence);
            }

            return $candidate;
        });
    }
}
