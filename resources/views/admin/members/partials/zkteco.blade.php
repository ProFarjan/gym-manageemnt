<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Action</th><th>Status</th><th>Attempts</th><th>Synced At</th><th>Error</th></tr>
        </thead>
        <tbody>
            @forelse ($member->zkTecoSyncLogs as $log)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                    <td>
                        <span class="badge bg-{{ match($log->status) { 'success' => 'success', 'failed' => 'danger', default => 'secondary' } }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td>{{ $log->attempts }}</td>
                    <td>{{ $log->synced_at?->format('d M Y h:i A') ?? '—' }}</td>
                    <td class="small text-danger">{{ $log->error_message }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No device sync activity yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
