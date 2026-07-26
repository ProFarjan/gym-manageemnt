@extends('layouts.admin')

@section('title', 'ZKTeco Device Sync')

@section('content')
    <h1 class="h4 mb-4">ZKTeco Device Sync Log</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach (['pending', 'success', 'failed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Member</th><th>Action</th><th>Status</th><th>Attempts</th><th>Synced At</th><th>Error</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td><a href="{{ route('admin.members.show', $log->member) }}">{{ $log->member->full_name }}</a></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                            <td>
                                <span class="badge bg-{{ match($log->status) { 'success' => 'success', 'failed' => 'danger', default => 'secondary' } }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->attempts }}</td>
                            <td>{{ $log->synced_at?->format('d M Y h:i A') ?? '—' }}</td>
                            <td class="small text-danger">{{ $log->error_message }}</td>
                            <td>
                                @if ($log->status === 'failed')
                                    <form method="POST" action="{{ route('admin.zkteco-sync-logs.retry', $log) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Retry</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No sync activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
@endsection
