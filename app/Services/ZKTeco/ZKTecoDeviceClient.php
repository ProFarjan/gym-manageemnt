<?php

namespace App\Services\ZKTeco;

use App\Models\Member;
use Illuminate\Support\Facades\Log;

/**
 * ZKTeco device client. createUser/updateUser/disableUser/deleteUser(Member)
 * and pullAttendanceLogs() below are still stubs (no physical device is
 * reachable in this development environment, so they simulate success and
 * log what a real call would send). listUsers()/deleteUserById() are real —
 * they speak the actual ZKTeco TCP protocol via ZKTecoProtocolClient. IP/
 * port/device ID are configurable in Admin > Settings and read here via
 * setting().
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

    /**
     * Raw TCP reachability check against the device's IP/port — genuinely
     * opens a socket rather than simulating success like the rest of this
     * stub client. This only proves something is listening on that host and
     * port, not that it speaks the ZKTeco protocol or that the Device ID is
     * correct, since no real protocol handshake is implemented here yet.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $ip, int $port, float $timeout = 3.0): array
    {
        if ($ip === '') {
            return ['success' => false, 'message' => 'Enter a Device IP first.'];
        }

        $start = microtime(true);
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        $elapsedMs = (int) round((microtime(true) - $start) * 1000);

        if ($connection) {
            fclose($connection);

            return ['success' => true, 'message' => "Connected to {$ip}:{$port} in {$elapsedMs}ms."];
        }

        return ['success' => false, 'message' => "Could not connect to {$ip}:{$port} — {$errstr} (errno {$errno})."];
    }

    /**
     * List every user currently enrolled on the device via the real ZKTeco
     * protocol.
     *
     * @return array{success: bool, message: string, users: array<int, array{uid:int,user_id:string,name:string,privilege:int,card:int}>}
     */
    public function listUsers(): array
    {
        $ip = setting('zkteco_ip');
        $port = (int) setting('zkteco_port', 4370);

        if (! $ip) {
            return ['success' => false, 'message' => 'Set the Device IP in ZKTeco Settings first.', 'users' => []];
        }

        $client = new ZKTecoProtocolClient;
        $connect = $client->connect($ip, $port);

        if (! $connect['success']) {
            return ['success' => false, 'message' => $connect['message'], 'users' => []];
        }

        try {
            $users = $client->getUsers();

            return ['success' => true, 'message' => count($users).' user(s) found.', 'users' => $users];
        } catch (\Throwable $e) {
            Log::warning("[ZKTeco] listUsers failed{$this->deviceSuffix()}: {$e->getMessage()}");

            return ['success' => false, 'message' => 'Failed to read users from the device: '.$e->getMessage(), 'users' => []];
        } finally {
            $client->disconnect();
        }
    }

    /**
     * Delete a single user directly on the device by its ZKTeco uid (the
     * device's own internal slot number, as returned by listUsers() — not a
     * Member id).
     *
     * @return array{success: bool, message: string}
     */
    public function deleteUserById(int $uid): array
    {
        $ip = setting('zkteco_ip');
        $port = (int) setting('zkteco_port', 4370);

        if (! $ip) {
            return ['success' => false, 'message' => 'Set the Device IP in ZKTeco Settings first.'];
        }

        $client = new ZKTecoProtocolClient;
        $connect = $client->connect($ip, $port);

        if (! $connect['success']) {
            return ['success' => false, 'message' => $connect['message']];
        }

        try {
            $deleted = $client->deleteUser($uid);

            return $deleted
                ? ['success' => true, 'message' => 'User deleted from the device.']
                : ['success' => false, 'message' => 'Device rejected the delete request.'];
        } catch (\Throwable $e) {
            Log::warning("[ZKTeco] deleteUserById({$uid}) failed{$this->deviceSuffix()}: {$e->getMessage()}");

            return ['success' => false, 'message' => 'Failed to delete user: '.$e->getMessage()];
        } finally {
            $client->disconnect();
        }
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
