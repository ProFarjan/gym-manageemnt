@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
    <h1 class="h4 mb-4">Settings</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3">
        @foreach ($sections as $slug => $section)
            <div class="col-md-4">
                <a href="{{ route('admin.settings.edit', $slug) }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-1">{{ $section['label'] }}</h2>
                            <p class="text-muted small mb-0">{{ $section['description'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
