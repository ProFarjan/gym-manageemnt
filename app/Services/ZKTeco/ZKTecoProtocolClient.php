<?php

namespace App\Services\ZKTeco;

use RuntimeException;

/**
 * Real ZKTeco device protocol client (TCP transport only) — a PHP port of
 * the reverse-engineered ZK protocol as implemented by the widely-used
 * pyzk library (https://github.com/fananimi/pyzk), which every open-source
 * ZKTeco integration (zklib, various PHP/C#/Java ports) is ultimately based
 * on, since ZKTeco does not publish an official protocol spec.
 *
 * getUsers() tries the modern chunked "buffered read" flow first
 * (CMD_PREPARE_BUFFER/CMD_READ_BUFFER, per pyzk) and falls back to the
 * older single-shot CMD_USER_TEMP_RRQ request (per the simpler, proven
 * https://github.com/ProFarjan/ZKTeco-Windows-Service implementation) if
 * the device doesn't support the former — different firmware generations
 * support different subsets of this protocol.
 *
 * Devices with a real communication password (CMD_AUTH) aren't supported —
 * the key-obfuscation algorithm varies across firmware and getting it wrong
 * would fail silently. Note this is distinct from CMD_ACK_UNAUTH on
 * CMD_CONNECT, which many devices send unconditionally regardless of
 * whether a password is actually required for read-only operations; per
 * the ProFarjan reference above, that response is treated as fine to
 * proceed on, not a hard failure.
 */
class ZKTecoProtocolClient
{
    private const CMD_CONNECT = 1000;

    private const CMD_EXIT = 1001;

    private const CMD_ACK_OK = 2000;

    private const CMD_ACK_UNAUTH = 2005;

    private const CMD_PREPARE_DATA = 1500;

    private const CMD_DATA = 1501;

    private const CMD_FREE_DATA = 1502;

    private const CMD_PREPARE_BUFFER = 1503;

    private const CMD_READ_BUFFER = 1504;

    private const CMD_DELETE_USER = 18;

    private const CMD_USERTEMP_RRQ = 9;

    private const CMD_GET_FREE_SIZES = 50;

    private const FCT_USER = 5;

    private const TCP_MAGIC_1 = 20560; // 0x5050

    private const TCP_MAGIC_2 = 32130; // 0x7D82

    private const MAX_CHUNK = 0xFFC0;

    /** @var resource|null */
    private $socket;

    private int $sessionId = 0;

    private int $replyId = 0;

    /**
     * @return array{success: bool, message: string}
     */
    public function connect(string $ip, int $port, float $timeout = 8.0): array
    {
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client("tcp://{$ip}:{$port}", $errno, $errstr, $timeout);

        if (! $socket) {
            return ['success' => false, 'message' => "Could not connect to {$ip}:{$port} — {$errstr} (errno {$errno})."];
        }

        stream_set_timeout($socket, (int) ceil($timeout));
        $this->socket = $socket;
        $this->sessionId = 0;
        $this->replyId = 0xFFFE;

        try {
            $response = $this->sendAndReceive(self::CMD_CONNECT, '');
            $this->sessionId = $response['session'];

            // Many devices reply CMD_ACK_UNAUTH to every CMD_CONNECT
            // regardless of whether a communication password is actually
            // enforced for read-only operations — proceed with the session
            // rather than failing outright. If the device genuinely does
            // require a password, the specific follow-up command (e.g.
            // reading users) will fail on its own and surface a clear
            // error there instead of a false "requires a password" here.
            if ($response['command'] !== self::CMD_ACK_OK && $response['command'] !== self::CMD_ACK_UNAUTH) {
                $this->closeSocket();

                return ['success' => false, 'message' => "Device rejected the connection (response code {$response['command']})."];
            }

            return ['success' => true, 'message' => 'Connected.'];
        } catch (RuntimeException $e) {
            $this->closeSocket();

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function disconnect(): void
    {
        if (! $this->socket) {
            return;
        }

        try {
            $this->sendAndReceive(self::CMD_EXIT, '');
        } catch (RuntimeException) {
            // Best-effort — we're closing the socket either way.
        }

        $this->closeSocket();
    }

    /**
     * @return array<int, array{uid: int, user_id: string, name: string, privilege: int, card: int}>
     */
    public function getUsers(): array
    {
        try {
            return $this->getUsersViaBuffer();
        } catch (RuntimeException) {
            // Some firmware doesn't support the CMD_PREPARE_BUFFER flow at
            // all (or CMD_GET_FREE_SIZES, needed to size records) — fall
            // back to the older, simpler single-shot request instead of
            // failing outright.
            return $this->getUsersSimple();
        }
    }

    private function getUsersViaBuffer(): array
    {
        $userCount = $this->readUserCount();

        if ($userCount === 0) {
            return [];
        }

        $buffer = $this->readWithBuffer(self::CMD_USERTEMP_RRQ, self::FCT_USER);

        if (strlen($buffer) <= 4) {
            return [];
        }

        $totalSize = unpack('V', substr($buffer, 0, 4))[1];
        $recordSize = $totalSize > 0 ? intdiv($totalSize, $userCount) : 72;
        $chunkLen = $recordSize === 28 ? 28 : 72;

        return $this->parseUserRecords(substr($buffer, 4), $chunkLen);
    }

    /**
     * Older single-shot request (CMD_USER_TEMP_RRQ with a 1-byte FCT_USER
     * payload) — no buffer-size negotiation, no CMD_GET_FREE_SIZES needed
     * first. Matches https://github.com/ProFarjan/ZKTeco-Windows-Service,
     * a proven-working reference against real hardware. If the whole reply
     * doesn't fit in one packet, the device announces a size via
     * CMD_PREPARE_DATA and then streams the rest as further unsolicited
     * packets with no additional request needed.
     */
    private function getUsersSimple(): array
    {
        $response = $this->sendAndReceive(self::CMD_USERTEMP_RRQ, chr(self::FCT_USER));

        if (! $response['status']) {
            throw new RuntimeException('Device rejected the user list request.');
        }

        $data = $response['payload'];

        if ($response['command'] === self::CMD_PREPARE_DATA && strlen($data) >= 4) {
            $size = unpack('V', substr($data, 0, 4))[1];
            $data = '';

            while (strlen($data) < $size) {
                $data .= $this->receive()['payload'];
            }

            $data = substr($data, 0, $size);
        }

        if (strlen($data) <= 11) {
            return [];
        }

        $records = substr($data, 11);

        return $this->parseUserRecords($records, $this->detectRecordLength($records));
    }

    /**
     * @return array<int, array{uid: int, user_id: string, name: string, privilege: int, card: int}>
     */
    private function parseUserRecords(string $records, int $chunkLen): array
    {
        $format = $chunkLen === 28
            ? 'vuid/Cprivilege/a5password/a8name/Vcard/x/Cgroup/vtimezone/Vuserid'
            : 'vuid/Cprivilege/a8password/a24name/Vcard/x/a7group/x/a24userid';

        $users = [];
        $offset = 0;

        while (strlen($records) - $offset >= $chunkLen) {
            $rec = unpack($format, substr($records, $offset, $chunkLen));
            $offset += $chunkLen;

            $name = $this->cstr($rec['name']);
            $userId = $chunkLen === 28 ? (string) $rec['userid'] : $this->cstr($rec['userid']);
            $userId = $userId !== '' ? $userId : (string) $rec['uid'];

            $users[] = [
                'uid' => $rec['uid'],
                'user_id' => $userId,
                'name' => $name !== '' ? $name : "NN-{$userId}",
                'privilege' => $rec['privilege'],
                'card' => $rec['card'],
            ];
        }

        return $users;
    }

    private function detectRecordLength(string $records): int
    {
        if (strlen($records) % 72 === 0) {
            return 72;
        }

        if (strlen($records) % 28 === 0) {
            return 28;
        }

        return 72;
    }

    public function deleteUser(int $uid): bool
    {
        $response = $this->sendAndReceive(self::CMD_DELETE_USER, pack('v', $uid));

        return $response['status'];
    }

    private function readUserCount(): int
    {
        $response = $this->sendAndReceive(self::CMD_GET_FREE_SIZES, '');

        if (! $response['status'] || strlen($response['payload']) < 80) {
            throw new RuntimeException("Could not read device capacity info.");
        }

        $fields = array_values(unpack('V20val', substr($response['payload'], 0, 80)));

        return (int) $fields[4];
    }

    /**
     * Bulk "reserved table" read (e.g. all enrolled users) — the device
     * either returns everything in one CMD_DATA reply, or replies with a
     * total size up front and streams it back via chunked CMD_READ_BUFFER
     * requests capped at MAX_CHUNK bytes each.
     */
    private function readWithBuffer(int $command, int $fct, int $ext = 0): string
    {
        $commandString = pack('c', 1).pack('v', $command).pack('VV', $fct, $ext);
        $response = $this->sendAndReceive(self::CMD_PREPARE_BUFFER, $commandString);

        if (! $response['status']) {
            throw new RuntimeException('Device rejected the buffered read request.');
        }

        if ($response['command'] === self::CMD_DATA) {
            return $response['payload'];
        }

        // payload[0] is a 1-byte marker; payload[1..5) is the total size
        // (LE uint32) of the buffered data that follows.
        $size = unpack('V', substr($response['payload'], 1, 4))[1];

        $data = '';
        $start = 0;
        $remaining = $size;

        while ($remaining > 0) {
            $chunkSize = min(self::MAX_CHUNK, $remaining);
            $chunkResponse = $this->sendAndReceive(self::CMD_READ_BUFFER, pack('VV', $start, $chunkSize));

            if ($chunkResponse['command'] !== self::CMD_DATA) {
                throw new RuntimeException('Unexpected response while reading buffered data from the device.');
            }

            $data .= $chunkResponse['payload'];
            $start += $chunkSize;
            $remaining -= $chunkSize;
        }

        $this->sendAndReceive(self::CMD_FREE_DATA, '');

        return $data;
    }

    /**
     * @return array{command: int, session: int, payload: string, status: bool}
     */
    private function sendAndReceive(int $command, string $data): array
    {
        if (! $this->socket) {
            throw new RuntimeException('Not connected to the device.');
        }

        $checksumBuf = pack('v4', $command, 0, $this->sessionId, $this->replyId).$data;
        $checksum = $this->checksum16($checksumBuf);

        $this->replyId++;
        if ($this->replyId >= 0xFFFF) {
            $this->replyId -= 0xFFFF;
        }

        $packet = pack('v4', $command, $checksum, $this->sessionId, $this->replyId).$data;
        $tcpPacket = pack('vvV', self::TCP_MAGIC_1, self::TCP_MAGIC_2, strlen($packet)).$packet;

        if (fwrite($this->socket, $tcpPacket) === false) {
            throw new RuntimeException('Failed to send data to the device.');
        }

        return $this->receive();
    }

    /**
     * Reads one incoming packet without sending anything first — used when
     * the device streams multiple unsolicited follow-up packets after a
     * single request (see getUsersSimple()).
     *
     * @return array{command: int, session: int, payload: string, status: bool}
     */
    private function receive(): array
    {
        $tcpTop = $this->readExactly(8);
        $top = unpack('vmagic1/vmagic2/Vlength', $tcpTop);

        if ($top['magic1'] !== self::TCP_MAGIC_1 || $top['magic2'] !== self::TCP_MAGIC_2) {
            throw new RuntimeException('Received an invalid response from the device.');
        }

        $body = $this->readExactly($top['length']);
        $header = unpack('vcommand/vchecksum/vsession/vreply', substr($body, 0, 8));

        $this->replyId = $header['reply'];

        return [
            'command' => $header['command'],
            'session' => $header['session'],
            'payload' => substr($body, 8),
            'status' => in_array($header['command'], [self::CMD_ACK_OK, self::CMD_PREPARE_DATA, self::CMD_DATA], true),
        ];
    }

    private function readExactly(int $length): string
    {
        if ($length === 0) {
            return '';
        }

        $data = '';

        while (strlen($data) < $length) {
            $chunk = fread($this->socket, $length - strlen($data));

            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->socket);

                if ($meta['timed_out'] ?? false) {
                    throw new RuntimeException('Timed out waiting for a response from the device.');
                }

                if (feof($this->socket)) {
                    throw new RuntimeException('Connection closed by the device.');
                }

                usleep(10000);

                continue;
            }

            $data .= $chunk;
        }

        return $data;
    }

    /**
     * Same 16-bit one's-complement checksum the device expects — sum 16-bit
     * little-endian words with end-around carry, then bitwise NOT.
     */
    private function checksum16(string $bytes): int
    {
        $len = strlen($bytes);
        $checksum = 0;
        $i = 0;

        while ($len > 1) {
            $checksum += unpack('v', substr($bytes, $i, 2))[1];
            if ($checksum > 0xFFFF) {
                $checksum -= 0xFFFF;
            }
            $i += 2;
            $len -= 2;
        }

        if ($len) {
            $checksum += ord($bytes[$i]);
        }

        while ($checksum > 0xFFFF) {
            $checksum -= 0xFFFF;
        }

        $checksum = ~$checksum;
        while ($checksum < 0) {
            $checksum += 0xFFFF;
        }

        return $checksum & 0xFFFF;
    }

    /**
     * Matches Python's `.split(b"\x00")[0]` — everything up to (not
     * including) the first NUL byte, not just trimmed trailing NULs.
     */
    private function cstr(string $raw): string
    {
        $pos = strpos($raw, "\0");

        return trim($pos === false ? $raw : substr($raw, 0, $pos));
    }

    private function closeSocket(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}
