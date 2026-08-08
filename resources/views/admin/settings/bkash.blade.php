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
                    <label class="form-label">App Key</label>
                    <input type="text" name="bkash_app_key" value="{{ $v('bkash_app_key') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">App Secret</label>
                    <input type="password" name="bkash_app_secret" value="{{ $v('bkash_app_secret') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="bkash_username" value="{{ $v('bkash_username') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="bkash_password" value="{{ $v('bkash_password') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="bkash_sandbox" value="1" id="bkash_sandbox" class="form-check-input" @checked($v('bkash_sandbox', '1') === '1')>
                        <label for="bkash_sandbox" class="form-check-label">Sandbox Mode</label>
                    </div>
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No live bKash merchant account is connected yet — the Member Portal renewal flow uses a local demo checkout until real credentials and the bKash SDK are wired in.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
