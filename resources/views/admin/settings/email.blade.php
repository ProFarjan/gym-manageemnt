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
                <div class="col-md-4">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="mail_host" value="{{ $v('mail_host') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Port</label>
                    <input type="number" name="mail_port" value="{{ $v('mail_port') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="mail_username" value="{{ $v('mail_username') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="mail_password" value="{{ $v('mail_password') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Address</label>
                    <input type="email" name="mail_from_address" value="{{ $v('mail_from_address') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Name</label>
                    <input type="text" name="mail_from_name" value="{{ $v('mail_from_name', config('app.name')) }}" class="form-control">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
