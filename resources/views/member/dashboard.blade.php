@extends('layouts.member')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-4">Welcome, {{ $member->full_name }}</h1>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Admission ID</p>
                    <p class="h5 mb-0">{{ $member->admission_id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Status</p>
                    <p class="h5 mb-0 text-capitalize">{{ $member->status }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Due Date</p>
                    <p class="h5 mb-0">{{ $member->due_date?->format('d M Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if (!$member->isLifetime() && in_array($member->status, ['active', 'expired']))
        <div class="mt-3">
            <a href="{{ route('member.renew') }}" class="btn btn-primary">Renew Membership</a>
        </div>
    @endif
@endsection
