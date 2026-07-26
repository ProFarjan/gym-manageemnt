<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\DB;

class AdmissionIdGenerator
{
    // TODO: source from Settings (Membership Prefix) once the Settings module exists (Phase 10).
    private const PREFIX = 'GG';

    public static function generate(): string
    {
        return DB::transaction(function () {
            $sequence = Member::count() + 1;
            $candidate = sprintf('%s%05d', self::PREFIX, $sequence);

            while (Member::where('admission_id', $candidate)->exists()) {
                $sequence++;
                $candidate = sprintf('%s%05d', self::PREFIX, $sequence);
            }

            return $candidate;
        });
    }
}
