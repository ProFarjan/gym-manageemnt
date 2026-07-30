<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Source</th></tr>
        </thead>
        <tbody>
            @forelse ($recentAttendance as $attendance)
                <tr>
                    <td>{{ $attendance->check_in->format('d M Y') }}</td>
                    <td>{{ $attendance->check_in->format('h:i A') }}</td>
                    <td>
                        @if ($attendance->check_out)
                            {{ $attendance->check_out->format('h:i A') }}
                        @else
                            <span class="badge bg-info">Still In</span>
                        @endif
                    </td>
                    <td>{{ $attendance->duration_minutes ? $attendance->duration_minutes.' min' : '—' }}</td>
                    <td class="text-capitalize">{{ $attendance->source }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No attendance recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
