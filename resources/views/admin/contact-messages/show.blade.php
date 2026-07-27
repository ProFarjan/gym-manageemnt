@extends('layouts.admin')

@section('title', 'Contact Message')

@section('content')
    <h1 class="h4 mb-4">Message from {{ $message->name }}</h1>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            <dl class="row">
                <dt class="col-3">Name</dt><dd class="col-9">{{ $message->name }}</dd>
                <dt class="col-3">Email</dt><dd class="col-9">{{ $message->email }}</dd>
                <dt class="col-3">Phone</dt><dd class="col-9">{{ $message->phone ?? '—' }}</dd>
                <dt class="col-3">Subject</dt><dd class="col-9">{{ $message->subject ?? '—' }}</dd>
                <dt class="col-3">Received</dt><dd class="col-9">{{ $message->created_at->format('d M Y, h:i A') }}</dd>
            </dl>
            <hr>
            <p style="white-space: pre-line;">{{ $message->message }}</p>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary">Back</a>
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger">Delete</button>
            </form>
        </div>
    </div>
@endsection
