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

    <form method="POST" action="{{ route('admin.settings.update', $section) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Gym Name</label>
                    <input type="text" name="business_name" value="{{ $v('business_name', config('app.name')) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="business_tagline" value="{{ $v('business_tagline', "Mymensingh's First Ever & Only Dedicated Ladies Gym") }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="business_address" value="{{ $v('business_address') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="business_phone" value="{{ $v('business_phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" accept="image/*" class="form-control" data-preview="logoPreview">
                    <img id="logoPreview" class="img-preview mt-2 {{ $v('logo_path') ? '' : 'd-none' }}"
                         src="{{ $v('logo_path') ? asset('storage/'.$v('logo_path')) : '' }}" alt="Logo preview">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
@endsection
