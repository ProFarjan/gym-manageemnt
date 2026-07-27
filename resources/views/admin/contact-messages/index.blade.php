@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')
    <h1 class="h4 mb-4">Contact Messages</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th></th><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr class="{{ $message->is_read ? '' : 'fw-bold' }}">
                            <td>@unless($message->is_read)<span class="badge bg-primary">New</span>@endunless</td>
                            <td>{{ $message->name }}</td>
                            <td>{{ $message->email }}</td>
                            <td>{{ $message->subject ?? '—' }}</td>
                            <td>{{ $message->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No messages yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $messages->links() }}
    </div>
@endsection
