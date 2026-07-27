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
 * once a device is on the network — its IP/port/device ID are configurable
 * in Admin > Settings and read here via setting().
 */
class ZKTecoDeviceClient
{
    public function createUser(Member $member): bool
    {
        $this->log('createUser', $member);

        return true;
    }

    public function updateUser(Member $member): bool
    {
        $this->log('updateUser', $member);

        return true;
    }

    public function disableUser(Member $member): bool
    {
        $this->log('disableUser', $member);

        return true;
    }

    public function deleteUser(Member $member): bool
    {
        $this->log('deleteUser', $member);

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
        Log::info("[ZKTeco stub{$this->deviceSuffix()}] pullAttendanceLogs called — no device configured, returning no records.");

        return [];
    }

    private function log(string $action, Member $member): void
    {
        Log::info("[ZKTeco stub{$this->deviceSuffix()}] {$action} for member {$member->admission_id} ({$member->full_name})");
    }

    private function deviceSuffix(): string
    {
        $ip = setting('zkteco_ip');

        return $ip ? " @ {$ip}:".setting('zkteco_port', '4370') : '';
    }
}
