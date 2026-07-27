@extends('layouts.member')

@section('title', 'Attendance History')

@section('content')
    <h1 class="h4 mb-4">Attendance History</h1>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Source</th></tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
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
                        <tr><td colspan="5" class="text-center text-muted py-4">No attendance recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
@endsection
