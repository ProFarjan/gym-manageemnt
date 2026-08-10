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
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $v('mail_host') }}" class="form-control" placeholder="e.g. smtp.gmail.com">
                    <div class="form-text">The mail server's hostname — not your email address. For Gmail this is always <code>smtp.gmail.com</code>.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Port</label>
                    <input type="number" name="mail_port" value="{{ $v('mail_port', 587) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Encryption</label>
                    <select name="mail_encryption" class="form-select">
                        <option value="tls" @selected($v('mail_encryption', 'tls') === 'tls')>STARTTLS (port 587)</option>
                        <option value="ssl" @selected($v('mail_encryption') === 'ssl')>SSL / TLS (port 465)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="mail_username" value="{{ $v('mail_username') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="mail_password" value="{{ $v('mail_password') }}" class="form-control">
                    <div class="form-text">For Gmail, this must be an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App Password</a>, not your normal account password.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $v('mail_from_address') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $v('mail_from_name', config('app.name')) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Send a Test Email To</label>
                    <input type="email" name="mail_test_email" value="{{ old('mail_test_email') }}" class="form-control" placeholder="you@example.com">
                </div>
                <div class="col-12">
                    <p class="text-muted small mb-0">
                        If the test email field is filled in, a real test email is sent through the SMTP settings
                        above the moment you save — the result (success or the exact SMTP error) shows up in the
                        confirmation message. Leave it blank to just save without testing.
                    </p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

    <div class="card mt-4 ajax-panel" data-panel-url="{{ route('admin.settings.email.mail-logs') }}">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>Mail Log</span>
            <input type="text" class="form-control form-control-sm" style="max-width:240px;" placeholder="Search recipient or subject" data-ajax-param="search">
        </div>
        <div class="ajax-panel-results">
            @include('admin.settings.partials._mail-logs-table', ['mailLogs' => $mailLogs])
        </div>
    </div>
@endsection
