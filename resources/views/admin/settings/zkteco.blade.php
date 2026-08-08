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
                    <label class="form-label">Device IP</label>
                    <input type="text" name="zkteco_ip" value="{{ $v('zkteco_ip') }}" class="form-control" placeholder="e.g. 192.168.1.201">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Port</label>
                    <input type="text" name="zkteco_port" value="{{ $v('zkteco_port', '4370') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Device ID</label>
                    <input type="text" name="zkteco_device_id" value="{{ $v('zkteco_device_id') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No physical ZKTeco device is reachable yet — user sync and attendance pull run in stub mode regardless of these values.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
