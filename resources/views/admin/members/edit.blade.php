@extends('layouts.admin')

@section('title', 'Edit Member')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $member->full_name }} <span class="text-muted small">({{ $member->admission_id }})</span></h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.members.update', $member) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">Personal Information</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $member->full_name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number *</label>
                    <input type="text" name="mobile_number" value="{{ old('mobile_number', $member->mobile_number) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $member->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NID / Birth Registration Number</label>
                    <input type="text" name="nid_number" value="{{ old('nid_number', $member->nid_number) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $member->emergency_contact) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">NID Image</label>
                    <input type="file" name="nid_image" class="form-control">
                    @if ($member->nid_image_path)
                        <div class="form-text">Current file on record — upload a new one to replace it.</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Member Photo</label>
                    <input type="file" name="photo" class="form-control">
                    @if ($member->photo_path)
                        <div class="form-text">Current file on record — upload a new one to replace it.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Health Information</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">Height (cm)</label>
                    <input type="number" step="0.01" name="height" value="{{ old('height', $member->height) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight', $member->weight) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">—</option>
                        @foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                            <option value="{{ $bg }}" @selected(old('blood_group', $member->blood_group) === $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fitness Goal</label>
                    <input type="text" name="fitness_goal" value="{{ old('fitness_goal', $member->fitness_goal) }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Medical Conditions / Allergies</label>
                    <textarea name="medical_conditions" class="form-control" rows="2">{{ old('medical_conditions', $member->medical_conditions) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Membership</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plan *</label>
                    <select name="membership_plan_id" class="form-select" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('membership_plan_id', $member->membership_plan_id) == $plan->id)>
                                {{ $plan->name }} — {{ number_format($plan->price, 2) }} BDT
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount Amount</label>
                    <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount', $member->discount_amount) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount Reason</label>
                    <input type="text" name="discount_reason" value="{{ old('discount_reason', $member->discount_reason) }}" class="form-control">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.members.show', $member) }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
