@extends('layouts.admin')

@section('title', $meta['label'])

@php
    $v = fn($key, $default = null) => old($key, $settings[$key] ?? $default);
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $meta['label'] }}</h1>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">All Settings</a>
    </div>

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

    <form method="POST" action="{{ route('admin.settings.update', $section) }}">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Admission ID Prefix</label>
                    <input type="text" name="membership_prefix" value="{{ $v('membership_prefix', 'GG') }}" class="form-control" maxlength="10">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gym Closing Time (for auto-checkout)</label>
                    <input type="time" name="gym_closing_time" value="{{ $v('gym_closing_time', '22:00') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-semibold">Renewal &amp; Closure Reminders</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <p class="text-muted small mb-0">
                        Reminders are sent by email/SMS to members as their due date approaches, and again on a
                        countdown after expiry before the membership is permanently closed. Days are counted
                        relative to the due date (renewal) or the 3-months-after-expiry closure date.
                    </p>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Renewal Reminder — Days Before Due Date</label>
                    <input type="text" name="renewal_reminder_days" value="{{ $v('renewal_reminder_days', '3,0') }}" class="form-control" placeholder="3,0">
                    <div class="form-text">Comma-separated. <code>0</code> means on the due date itself.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Renewal Reminder — Send Time</label>
                    <input type="time" name="renewal_reminder_time" value="{{ $v('renewal_reminder_time', '09:00') }}" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Closure Reminder — Days Before Closure</label>
                    <input type="text" name="closure_reminder_days" value="{{ $v('closure_reminder_days', '15,10,5,1,0') }}" class="form-control" placeholder="15,10,5,1,0">
                    <div class="form-text">Comma-separated. <code>0</code> means the final day before permanent closure.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Closure Reminder — Send Time</label>
                    <input type="time" name="closure_reminder_time" value="{{ $v('closure_reminder_time', '09:15') }}" class="form-control">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
