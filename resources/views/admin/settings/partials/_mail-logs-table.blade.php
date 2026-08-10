<div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
        <thead>
            <tr>
                <th>To</th>
                <th>Subject</th>
                <th>Mailer</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mailLogs as $log)
                <tr>
                    <td>{{ $log->to_address }}</td>
                    <td>{{ $log->subject ?? '—' }}</td>
                    <td>{{ $log->mailer ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'danger' }}">{{ ucfirst($log->status) }}</span>
                        @if ($log->status === 'failed' && $log->error_message)
                            <div class="text-danger small mt-1">{{ \Illuminate\Support\Str::limit($log->error_message, 80) }}</div>
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No emails sent yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 px-3 pb-3">
    <div class="text-muted small">
        @if ($mailLogs->total() > 0)
            Showing {{ $mailLogs->firstItem() }} to {{ $mailLogs->lastItem() }} of {{ $mailLogs->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $mailLogs->links() }}
</div>
