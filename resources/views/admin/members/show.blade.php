@extends('layouts.admin')

@section('title', $member->full_name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $member->full_name }} <span class="text-muted small">({{ $member->admission_id }})</span></h1>
        <div>
            @can('members.update')
                <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-outline-secondary">Edit</a>
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
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Status</p>
                    <span class="badge bg-{{ match($member->status) {
                        'active' => 'success', 'pending' => 'warning', 'expired' => 'secondary', 'closed' => 'dark',
                    } }} fs-6">{{ ucfirst($member->status) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Plan</p>
                    <p class="mb-0">{{ $member->membershipPlan?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Admission Date</p>
                    <p class="mb-0">{{ $member->admission_date?->format('d M Y') ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Due Date</p>
                    <p class="mb-0">{{ $member->due_date?->format('d M Y') ?? ($member->isLifetime() ? 'Lifetime' : '—') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4">
        @can('members.update')
            @if ($member->status === 'pending')
                <form method="POST" action="{{ route('admin.members.approve', $member) }}">
                    @csrf
                    <button class="btn btn-success">Approve &amp; Activate</button>
                </form>
            @endif
            @if (in_array($member->status, ['active', 'expired']))
                <form method="POST" action="{{ route('admin.members.close', $member) }}" onsubmit="return confirm('Close this membership permanently?');">
                    @csrf
                    <button class="btn btn-outline-dark">Close Membership</button>
                </form>
            @endif
        @endcan
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card mb-3">
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
            <div class="card mb-3">
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
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Amount</th><th>Discount</th><th>Method</th><th>Account</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($member->payments as $payment)
                        <tr>
                            <td>{{ $payment->created_at->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->discount_amount > 0 ? number_format($payment->discount_amount, 2) : '—' }}</td>
                            <td>{{ ucfirst($payment->method) }}</td>
                            <td>{{ $payment->paymentAccount->name }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'refunded' ? 'danger' : 'success' }}">{{ ucfirst($payment->status) }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Receipt</a>
                                @can('payments.update')
                                    @if ($payment->status !== 'refunded')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#refund-{{ $payment->id }}">Refund</button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @if ($payment->status !== 'refunded')
                            <tr class="collapse" id="refund-{{ $payment->id }}">
                                <td colspan="8" class="bg-light">
                                    <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="d-flex gap-2 align-items-end">
                                        @csrf
                                        <div>
                                            <label class="form-label small mb-0">Refund Amount</label>
                                            <input type="number" step="0.01" name="refund_amount" value="{{ $payment->amount }}" max="{{ $payment->amount }}" class="form-control form-control-sm" required>
                                        </div>
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Confirm refund?');">Confirm Refund</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">No payments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('payments.create')
            <div class="card-body border-top">
                <h6>Record Payment</h6>
                <form method="POST" action="{{ route('admin.members.payments.store', $member) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            <option value="monthly">Monthly</option>
                            <option value="admission">Admission</option>
                            <option value="package">Package</option>
                            <option value="renewal">Renewal</option>
                            <option value="personal_training">Personal Training</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Account</label>
                        <select name="payment_account_id" class="form-select form-select-sm" required>
                            @foreach ($paymentAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Discount</label>
                        <input type="number" step="0.01" name="discount_amount" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Discount Reason</label>
                        <input type="text" name="discount_reason" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small mb-0">Periods</label>
                        <input type="number" name="periods" value="1" min="1" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Save</button>
                    </div>
                </form>
                <div class="form-text">"Periods" covers multiple billing cycles at once (e.g. catching up 2 missed months). Only applies to Monthly/Admission/Package/Renewal types.</div>
            </div>
        @endcan
    </div>

    <div class="card mt-3">
        <div class="card-header">Personal Training Packages</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr><th>Package</th><th>Trainer</th><th>Sessions</th><th>Expires</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($member->memberTrainingPackages as $assignment)
                        <tr>
                            <td>{{ $assignment->package->name }}</td>
                            <td>{{ $assignment->trainer?->name ?? '—' }}</td>
                            <td>{{ $assignment->sessions_used }} / {{ $assignment->package->sessions_count }}</td>
                            <td>{{ $assignment->expires_at->format('d M Y') }} @if ($assignment->isExpired())<span class="badge bg-secondary">Expired</span>@endif</td>
                            <td>
                                @can('personal_training.update')
                                    @if (!$assignment->isExpired() && $assignment->sessionsRemaining() > 0)
                                        <form method="POST" action="{{ route('admin.training-packages.log-session', $assignment) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">Log Session</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">No training packages assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('personal_training.create')
            <div class="card-body border-top">
                <form method="POST" action="{{ route('admin.members.training-packages.store', $member) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label">Assign Package</label>
                        <select name="personal_training_package_id" class="form-select" required>
                            <option value="">Select a package</option>
                            @foreach ($trainingPackages as $tp)
                                <option value="{{ $tp->id }}">{{ $tp->name }} ({{ $tp->sessions_count }} sessions)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Trainer</label>
                        <select name="trainer_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($trainers as $trainer)
                                <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Assign</button>
                    </div>
                </form>
            </div>
        @endcan
    </div>
@endsection
