@extends('layouts.member')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-4">Welcome, {{ $member->full_name }}</h1>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Admission ID</p>
                    <p class="h5 mb-0">{{ $member->admission_id }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Plan</p>
                    <p class="h5 mb-0">{{ $member->membershipPlan?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Status</p>
                    <p class="h5 mb-0 text-capitalize">{{ $member->status }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Due Date</p>
                    <p class="h5 mb-0">{{ $member->isLifetime() ? 'Lifetime' : ($member->due_date?->format('d M Y') ?? 'N/A') }}</p>
                </div>
            </div>
        </div>
    </div>

    @if (!$member->isLifetime() && in_array($member->status, ['active', 'expired']))
        <div class="mb-4">
            <a href="{{ route('member.renew') }}" class="btn btn-primary">Renew Membership</a>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('member.payments.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 mb-1">Payment History</h2>
                        <p class="text-muted small mb-0">View past payments and download receipts.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('member.attendance.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 mb-1">Attendance History</h2>
                        <p class="text-muted small mb-0">See your gym check-in/check-out log.</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('member.profile.edit') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6 mb-1">Profile</h2>
                        <p class="text-muted small mb-0">Update your photo, email, mobile, or password.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
