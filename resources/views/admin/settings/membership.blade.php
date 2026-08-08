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

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
