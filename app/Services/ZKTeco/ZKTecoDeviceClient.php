<?php

namespace App\Services\ZKTeco;

use App\Models\Member;
use Illuminate\Support\Facades\Log;

/**
 * Stub ZKTeco device client. There is no physical ZKTeco device reachable in
 * this environment, so these calls simulate success and log what a real
 * integration would send. Swap the body of each method for calls to the
 * device's SDK/protocol (ZKTeco devices are typically driven over a
 * proprietary TCP/UDP protocol, e.g. via the zklib/pyzk family of libraries)
 * once a device is on the network and its IP/port are known — see
 * config/zkteco.php for where those settings will live (Settings UI is
 * built in Phase 10).
 */
class ZKTecoDeviceClient
{
    public function createUser(Member $member): bool
    {
        Log::info("[ZKTeco stub] createUser for member {$member->admission_id} ({$member->full_name})");

        return true;
    }

    public function updateUser(Member $member): bool
    {
        Log::info("[ZKTeco stub] updateUser for member {$member->admission_id} ({$member->full_name})");

        return true;
    }

    public function disableUser(Member $member): bool
    {
        Log::info("[ZKTeco stub] disableUser for member {$member->admission_id} ({$member->full_name})");

        return true;
    }

    public function deleteUser(Member $member): bool
    {
        Log::info("[ZKTeco stub] deleteUser for member {$member->admission_id} ({$member->full_name})");

        return true;
    }

    /**
     * Pull fresh attendance logs from the device. Returns an empty set in
     * stub mode — a real implementation would poll the device (or receive a
     * push) for fingerprint/RFID punches since the last sync.
     *
     * @return array<int, array{member_id: int, check_in: string, zkteco_log_id: string}>
     */
    public function pullAttendanceLogs(): array
    {
        Log::info('[ZKTeco stub] pullAttendanceLogs called — no device configured, returning no records.');

        return [];
    }
}
