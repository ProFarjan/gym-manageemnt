<?php

namespace App\Services\ZKTeco;

use App\Models\Member;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * ZKTeco device client. Every method here speaks the actual ZKTeco TCP
 * protocol via ZKTecoProtocolClient — none of this is simulated. IP/port/
 * device ID are configurable in Admin > Settings and read here via
 * setting(). createUser/updateUser/disableUser/deleteUser(Member) throw a
 * RuntimeException on any failure (no IP configured, connect failed, device
 * rejected the write) rather than returning false, so a queued
 * SyncMemberToZKTeco job correctly retries and lands in ZKTecoSyncLog as
 * 'failed' instead of being force-marked 'success' just because no
 * exception happened to be thrown.
 *
 * The device's numeric uid (its internal record slot, 1-65535) is the
 * Member's own primary key — small, unique, and guaranteed to fit. The
 * device's 9-char user_id string is the Member's admission_id, which is
 * what gets stored back onto Member.zkteco_user_id once a create/update
 * succeeds.
 */
class ZKTecoDeviceClient
{
    public function createUser(Member $member, string $password = ''): bool
    {
        return $this->pushUser($member, $password);
    }

    public function updateUser(Member $member, string $password = ''): bool
    {
        return $this->pushUser($member, $password);
    }

    /**
     * The base ZKTeco protocol has no per-user "soft disable" flag — the
     * closest real effect is removing the user's device record so they can
     * no longer clock in/out. zkteco_user_id is deliberately left as-is (see
     * deleteUser(), which clears it) so re-activating the member just
     * recreates the same record via pushUser().
     */
    public function disableUser(Member $member): bool
    {
        return $this->removeFromDevice($member);
    }

    public function deleteUser(Member $member): bool
    {
        $removed = $this->removeFromDevice($member);

        if ($removed && $member->zkteco_user_id !== null) {
            $member->forceFill(['zkteco_user_id' => null])->save();
        }

        return $removed;
    }

    private function pushUser(Member $member, string $password = ''): bool
    {
        $ip = setting('zkteco_ip');
        $port = (int) setting('zkteco_port', 4370);

        if (! $ip) {
            throw new RuntimeException('Set the Device IP in ZKTeco Settings first.');
        }

        $client = new ZKTecoProtocolClient;
        $connect = $client->connect($ip, $port);

        if (! $connect['success']) {
            throw new RuntimeException($connect['message']);
        }

        try {
            $userId = $member->admission_id;
            $created = $client->setUser($member->id, $userId, $member->full_name, $password);

            if (! $created) {
                throw new RuntimeException('Device rejected the create/update request.');
            }

            if ($member->zkteco_user_id !== $userId) {
                $member->forceFill(['zkteco_user_id' => $userId])->save();
            }

            Log::info("[ZKTeco] pushed user{$this->deviceSuffix()}: uid={$member->id} user_id={$userId} name={$member->full_name}");

            return true;
        } finally {
            $client->disconnect();
        }
    }

    private function removeFromDevice(Member $member): bool
    {
        $ip = setting('zkteco_ip');
        $port = (int) setting('zkteco_port', 4370);

        if (! $ip) {
            throw new RuntimeException('Set the Device IP in ZKTeco Settings first.');
        }

        $client = new ZKTecoProtocolClient;
        $connect = $client->connect($ip, $port);

        if (! $connect['success']) {
            throw new RuntimeException($connect['message']);
        }

        try {
            $removed = $client->deleteUser($member->id);

            Log::info("[ZKTeco] removed user{$this->deviceSuffix()}: uid={$member->id} success=".($removed ? '1' : '0'));

            return $removed;
        } finally {
            $client->disconnect();
        }
    }

    /**
     * Pull fresh attendance logs from the device. Not yet implemented for
     * Direct IP mode (always returns empty) — in Local Service mode, the
     * Windows service pushes attendance logs directly via the API instead
     * of this method being called.
     *
     * @return array<int, array{member_id: int, check_in: string, zkteco_log_id: string}>
     */
    public function pullAttendanceLogs(): array
    {
        return [];
    }

    /**
     * Raw TCP reachability check against the device's IP/port. This only
     * proves something is listening on that host and port, not that it
     * speaks the ZKTeco protocol or that the Device ID is correct.
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

    private function deviceSuffix(): string
    {
        $ip = setting('zkteco_ip');

        return $ip ? " @ {$ip}:".setting('zkteco_port', '4370') : '';
    }
}
