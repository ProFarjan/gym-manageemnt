<div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
        <thead>
            <tr>
                <th>To</th>
                <th>Message</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($smsLogs as $log)
                <tr>
                    <td>{{ $log->to_number }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($log->message, 60) }}</td>
                    <td>
                        <span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span>
                        @if ($log->response_message)
                            <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($log->response_message, 80) }}</div>
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No SMS sent yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 px-3 pb-3">
    <div class="text-muted small">
        @if ($smsLogs->total() > 0)
            Showing {{ $smsLogs->firstItem() }} to {{ $smsLogs->lastItem() }} of {{ $smsLogs->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $smsLogs->links() }}
</div>
