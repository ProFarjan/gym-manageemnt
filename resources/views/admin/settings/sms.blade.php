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
                    <label class="form-label">Driver</label>
                    <input type="text" name="sms_driver" value="{{ $v('sms_driver', 'log') }}" class="form-control" placeholder="e.g. log (dev) or your provider's name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">API Key</label>
                    <input type="text" name="sms_api_key" value="{{ $v('sms_api_key') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sender ID</label>
                    <input type="text" name="sms_sender_id" value="{{ $v('sms_sender_id') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <p class="text-muted small mb-0">No live SMS gateway is connected yet — messages are logged instead of sent until real credentials and a provider integration are added.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
