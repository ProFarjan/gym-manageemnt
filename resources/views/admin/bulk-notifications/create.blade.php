@extends('layouts.admin')

@section('title', 'Bulk Notification')

@section('content')
    <h1 class="h4 mb-4">Send Bulk SMS / Email</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.bulk-notifications.store') }}">
        @csrf
        <div class="card mb-3" style="max-width: 640px;">
            <div class="card-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">Send To</label>
                    <select name="target_status" class="form-select" required>
                        <option value="all">All Members</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Subject (email only)</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Message</label>
                    <textarea name="body" class="form-control" rows="4" required></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label d-block">Channels</label>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="channels[]" value="mail" id="ch-mail" class="form-check-input" checked>
                        <label for="ch-mail" class="form-check-label">Email</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="channels[]" value="sms" id="ch-sms" class="form-check-input" checked>
                        <label for="ch-sms" class="form-check-label">SMS</label>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
@endsection
