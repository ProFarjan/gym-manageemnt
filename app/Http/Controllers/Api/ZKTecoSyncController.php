<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\ZKTecoCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * "Local Service" mode API for the ZKTeco Windows Service — one single,
 * symmetric endpoint (matching the generic "api" block already used by
 * every project built on that shared Windows service, not a one-off for
 * this app). The Laravel server can't reach the device directly in this
 * mode — a Windows service on a PC with LAN access to the device does, so
 * it calls sync() on its own interval (default every 5 min) to:
 *   - push attendance (possibly empty)
 *   - report results for commands it executed last cycle (command_results)
 *   - receive newly queued commands to execute this cycle (commands)
 * all in one request/response pair. No separate polling or result-posting
 * endpoint — a project whose backend doesn't return "commands" simply never
 * gets any, and this is a no-op for it.
 */
class ZKTecoSyncController extends Controller
{
    private const IN_TYPES = [0, 4]; // check-in, overtime-in

    private const OUT_TYPES = [1, 5]; // check-out, overtime-out

    private const AUTH_METHOD_SOURCE = [
        0 => 'manual',   // password
        1 => 'fingerprint',
        2 => 'rfid',      // card
    ];

    private const MAX_COMMANDS_PER_SYNC = 20;

    public function sync(Request $request)
    {
        $data = $request->validate([
            'device_name' => ['nullable', 'string', 'max:255'],
            'records' => ['nullable', 'array'],
            'records.*.user_id' => ['required_with:records', 'string'],
            'records.*.attendance_datetime' => ['required_with:records', 'string'],
            'records.*.attendance_type' => ['nullable', 'integer'],
            'records.*.auth_method' => ['nullable', 'integer'],
            'command_results' => ['nullable', 'array'],
            'command_results.*.id' => ['required', 'integer'],
            'command_results.*.success' => ['required', 'boolean'],
            'command_results.*.message' => ['nullable', 'string'],
            'command_results.*.data' => ['nullable', 'array'],
        ]);

        $deviceName = $data['device_name'] ?? 'default';
        $saved = 0;
        $skipped = 0;

        foreach ($data['records'] ?? [] as $record) {
            if ($this->ingestAttendance($record, $deviceName)) {
                $saved++;
            } else {
                $skipped++;
            }
        }

        foreach ($data['command_results'] ?? [] as $result) {
            $this->applyCommandResult($result);
        }

        $commands = ZKTecoCommand::where('status', 'pending')
            ->oldest('id')
            ->limit(self::MAX_COMMANDS_PER_SYNC)
            ->get();

        if ($commands->isNotEmpty()) {
            ZKTecoCommand::whereIn('id', $commands->pluck('id'))->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'received' => count($data['records'] ?? []),
            'saved' => $saved,
            'skipped' => $skipped,
            'commands' => $commands->map(fn (ZKTecoCommand $c) => [
                'id' => $c->id,
                'type' => $c->type,
                'payload' => $c->payload,
            ])->values(),
        ]);
    }

    /**
     * @param  array{id: int, success: bool, message?: string, data?: array}  $result
     */
    private function applyCommandResult(array $result): void
    {
        $command = ZKTecoCommand::find($result['id']);

        if (! $command) {
            return;
        }

        $command->update([
            'status' => $result['success'] ? 'completed' : 'failed',
            'result' => ['message' => $result['message'] ?? null, 'data' => $result['data'] ?? null],
            'completed_at' => now(),
        ]);

        // A successful create_user command is the moment this member becomes
        // enrolled on the device — link it back so future commands/attendance
        // matching can use the same identifier.
        if ($result['success'] && $command->type === 'create_user' && ! empty($command->payload['member_id'])) {
            Member::whereKey($command->payload['member_id'])
                ->update(['zkteco_user_id' => $command->payload['user_id']]);
        }

        if ($result['success'] && $command->type === 'delete_user' && ! empty($command->payload['member_id'])) {
            Member::whereKey($command->payload['member_id'])->update(['zkteco_user_id' => null]);
        }
    }

    /**
     * @param  array{user_id: string, attendance_datetime: string, attendance_type?: int, auth_method?: int}  $record
     */
    private function ingestAttendance(array $record, string $deviceName): bool
    {
        $userId = $record['user_id'];
        $type = (int) ($record['attendance_type'] ?? 0);
        $authMethod = (int) ($record['auth_method'] ?? 0);

        $datetime = Carbon::parse($record['attendance_datetime']);

        $member = Member::where('zkteco_user_id', $userId)
            ->orWhere('admission_id', $userId)
            ->first();

        if (! $member) {
            return false;
        }

        // Idempotent — the Windows service may resend a batch after a
        // failed/retried request, so the same punch can arrive twice.
        $logId = md5("{$deviceName}|{$userId}|{$record['attendance_datetime']}|{$type}");

        if ($member->attendances()->where('zkteco_log_id', $logId)->exists()) {
            return false;
        }

        $source = self::AUTH_METHOD_SOURCE[$authMethod] ?? 'manual';

        if (in_array($type, self::IN_TYPES, true)) {
            if ($member->openAttendance()) {
                return false;
            }

            $member->attendances()->create([
                'check_in' => $datetime,
                'source' => $source,
                'zkteco_log_id' => $logId,
            ]);

            return true;
        }

        if (in_array($type, self::OUT_TYPES, true)) {
            $open = $member->openAttendance();

            if (! $open) {
                return false;
            }

            $open->update([
                'check_out' => $datetime,
                'duration_minutes' => $open->check_in->diffInMinutes($datetime),
                'zkteco_log_id' => $open->zkteco_log_id ?? $logId,
            ]);

            return true;
        }

        return false;
    }
}
