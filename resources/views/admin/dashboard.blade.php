@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-4">Welcome, {{ auth()->user()->name }}</h1>

    <div class="card">
        <div class="card-body">
            <p class="mb-1"><strong>Role:</strong> {{ auth()->user()->roles->pluck('name')->join(', ') ?: '—' }}</p>
            <p class="mb-0"><strong>Permissions:</strong> {{ auth()->user()->getAllPermissions()->pluck('name')->join(', ') ?: '—' }}</p>
        </div>
    </div>
@endsection
