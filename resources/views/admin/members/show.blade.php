@extends('layouts.admin')

@section('title', $member->full_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $member->full_name }} <span class="text-muted small">({{ $member->admission_id }})</span></h1>
        <div class="d-flex gap-2">
            @can('members.update')
                <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-outline-secondary">Edit</a>
            @endcan
            @can('attendance.create')
                @if ($member->status === 'active')
                    @php($openAttendance = $member->openAttendance())
                    @if ($openAttendance)
                        <form method="POST" action="{{ route('admin.members.attendance.check-out', $member) }}">
                            @csrf
                            <button class="btn btn-outline-primary">Check Out (in since {{ $openAttendance->check_in->format('h:i A') }})</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.members.attendance.check-in', $member) }}">
                            @csrf
                            <button class="btn btn-primary">Check In</button>
                        </form>
                    @endif
                @endif
            @endcan
            @can('members.update')
                @if (in_array($member->status, ['active', 'expired']))
                    <form method="POST" action="{{ route('admin.members.close', $member) }}" onsubmit="return confirm('Close this membership permanently?');">
                        @csrf
                        <button class="btn btn-outline-dark">Close Membership</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card bg-primary-subtle border-0">
                <div class="card-body">
                    <p class="text-muted small mb-1">Status</p>
                    <span class="badge bg-{{ match($member->status) {
                        'active' => 'success', 'pending' => 'warning', 'expired' => 'secondary', 'closed' => 'dark',
                    } }} fs-6">{{ ucfirst($member->status) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info-subtle border-0">
                <div class="card-body">
                    <p class="text-muted small mb-1">Plan</p>
                    <p class="mb-0">{{ $member->membershipPlan?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success-subtle border-0">
                <div class="card-body">
                    <p class="text-muted small mb-1">Admission Date</p>
                    <p class="mb-0">{{ $member->admission_date?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning-subtle border-0">
                <div class="card-body">
                    <p class="text-muted small mb-1">Due Date</p>
                    <p class="mb-0">{{ $member->due_date?->format('d M Y') ?? ($member->isLifetime() ? 'Lifetime' : '—') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Mobile</dt><dd class="col-7">{{ $member->mobile_number }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7">{{ $member->email ?? '—' }}</dd>
                        <dt class="col-5">Date of Birth</dt><dd class="col-7">{{ $member->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                        <dt class="col-5">Address</dt><dd class="col-7">{{ $member->address ?? '—' }}</dd>
                        <dt class="col-5">NID Number</dt><dd class="col-7">{{ $member->nid_number ?? '—' }}</dd>
                        <dt class="col-5">Emergency Contact</dt><dd class="col-7">{{ $member->emergency_contact ?? '—' }}</dd>
                        <dt class="col-5">Registered By</dt><dd class="col-7">{{ $member->registeredBy?->name ?? ucfirst($member->registration_type) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Health Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Height</dt><dd class="col-7">{{ $member->height ? $member->height.' cm' : '—' }}</dd>
                        <dt class="col-5">Weight</dt><dd class="col-7">{{ $member->weight ? $member->weight.' kg' : '—' }}</dd>
                        <dt class="col-5">Blood Group</dt><dd class="col-7">{{ $member->blood_group ?? '—' }}</dd>
                        <dt class="col-5">Fitness Goal</dt><dd class="col-7">{{ $member->fitness_goal ?? '—' }}</dd>
                        <dt class="col-5">Medical Conditions</dt><dd class="col-7">{{ $member->medical_conditions ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    @if ($member->membershipPlan)
        <div class="card mb-3">
            <div class="card-header">Package Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Package Name</dt>
                    <dd class="col-sm-9">{{ $member->membershipPlan->name }}</dd>

                    <dt class="col-sm-3">Price</dt>
                    <dd class="col-sm-9">{{ number_format($member->membershipPlan->price, 2) }} BDT</dd>

                    <dt class="col-sm-3">Duration</dt>
                    <dd class="col-sm-9">{{ $member->membershipPlan->is_lifetime ? 'Lifetime' : $member->membershipPlan->duration_in_months.' Month(s)' }}</dd>

                    <dt class="col-sm-3">Admission Fee</dt>
                    <dd class="col-sm-9">
                        @if ($member->membershipPlan->admission_free)
                            <span class="badge bg-success">Free</span>
                        @else
                            {{ number_format($member->membershipPlan->admission_fee, 2) }} BDT
                            @if ($member->membershipPlan->admission_discount > 0)
                                <span class="text-muted small">(Discount: {{ number_format($member->membershipPlan->admission_discount, 2) }} BDT)</span>
                            @endif
                        @endif
                    </dd>

                    @if ($member->discount_amount > 0)
                        <dt class="col-sm-3">Member Discount</dt>
                        <dd class="col-sm-9">
                            {{ number_format($member->discount_amount, 2) }} BDT
                            @if ($member->discount_reason)
                                <span class="text-muted small">({{ $member->discount_reason }})</span>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    @if ($member->nid_image_path || $member->photo_path)
        <div class="card mb-3">
            <div class="card-header">Documents</div>
            <div class="card-body d-flex gap-4">
                @if ($member->photo_path)
                    <div>
                        <p class="small text-muted mb-1">Photo</p>
                        <img src="{{ asset('storage/'.$member->photo_path) }}" style="height:120px;" class="rounded border">
                    </div>
                @endif
                @if ($member->nid_image_path)
                    <div>
                        <p class="small text-muted mb-1">NID</p>
                        <img src="{{ asset('storage/'.$member->nid_image_path) }}" style="height:120px;" class="rounded border">
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">Payment History</div>
        @include('admin.members.partials.payments', ['member' => $member])
    </div>

    <div class="card mt-3">
        <div class="card-header">Personal Training Packages</div>
        @include('admin.members.partials.training', ['member' => $member, 'showAssignForm' => false])
    </div>
@endsection
