@extends('layouts.member')

@section('title', 'Profile')

@section('content')
    <h1 class="h4 mb-4">My Profile</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Full Name</dt><dd class="col-7">{{ $member->full_name }}</dd>
                        <dt class="col-5">Admission ID</dt><dd class="col-7">{{ $member->admission_id }}</dd>
                        <dt class="col-5">Date of Birth</dt><dd class="col-7">{{ $member->date_of_birth?->format('d M Y') ?? '—' }}</dd>
                        <dt class="col-5">Address</dt><dd class="col-7">{{ $member->address ?? '—' }}</dd>
                        <dt class="col-5">Blood Group</dt><dd class="col-7">{{ $member->blood_group ?? '—' }}</dd>
                    </dl>
                    <p class="text-muted small mb-0">These details can only be changed by gym staff — visit the front desk to update them.</p>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">Update Profile</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3 d-flex align-items-center gap-3">
                            @if ($member->photo_path)
                                <img src="{{ asset('storage/'.$member->photo_path) }}" class="rounded" style="height:80px;width:80px;object-fit:cover;">
                            @endif
                            <div class="flex-grow-1">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="photo" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $member->email) }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" name="mobile_number" value="{{ old('mobile_number', $member->mobile_number) }}" class="form-control" required>
                        </div>

                        <hr>
                        <p class="text-muted small">Leave password fields blank to keep your current password.</p>

                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
