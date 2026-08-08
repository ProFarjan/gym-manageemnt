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
                    <label class="form-label">Merchant ID</label>
                    <input type="text" name="nagad_merchant_id" value="{{ $v('nagad_merchant_id') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Merchant Key</label>
                    <input type="password" name="nagad_merchant_key" value="{{ $v('nagad_merchant_key') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <div class="form-check">
                        <input type="checkbox" name="nagad_sandbox" value="1" id="nagad_sandbox" class="form-check-input" @checked($v('nagad_sandbox', '1') === '1')>
                        <label for="nagad_sandbox" class="form-check-label">Sandbox Mode</label>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
