@extends('layouts.admin')

@section('title', 'New Registration')

@section('content')
    <h1 class="h4 mb-4">New Member Registration</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card mb-3">
            <div class="card-header">Personal Information</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control" placeholder="e.g. Jane Rahman" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="mobile_number" value="{{ old('mobile_number') }}" class="form-control" placeholder="e.g. 01XXXXXXXXX" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="text" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control dob-datepicker"
                               placeholder="Select date of birth" autocomplete="off"
                               data-min="{{ now()->subYears(100)->format('Y-m-d') }}" data-max="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="1" placeholder="House, Road, Area, City">{{ old('address') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">NID / Birth Registration Number</label>
                    <input type="text" name="nid_number" value="{{ old('nid_number') }}" class="form-control" placeholder="NID or birth certificate number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}" class="form-control" placeholder="Name & phone number">
                </div>
                <div class="col-md-6">
                    <label class="form-label">NID Image</label>
                    <input type="file" name="nid_image" accept="image/*" class="form-control" data-preview="nidImagePreview">
                    <img id="nidImagePreview" class="img-preview mt-2 d-none" alt="NID preview">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Member Photo</label>
                    <input type="file" name="photo" accept="image/*" class="form-control" data-preview="photoPreview">
                    <img id="photoPreview" class="img-preview mt-2 d-none" alt="Member photo preview">
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Health Information</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">Height (cm)</label>
                    <input type="number" step="0.01" name="height" value="{{ old('height') }}" class="form-control" placeholder="e.g. 165">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Weight (kg)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" class="form-control" placeholder="e.g. 60">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">—</option>
                        @foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                            <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fitness Goal</label>
                    <input type="text" name="fitness_goal" value="{{ old('fitness_goal') }}" class="form-control" placeholder="e.g. Weight loss, muscle gain">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Medical Conditions / Allergies</label>
                    <textarea name="medical_conditions" class="form-control" rows="1" placeholder="Any conditions we should know about">{{ old('medical_conditions') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Membership</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Plan <span class="text-danger">*</span></label>
                    <select name="membership_plan_id" class="form-select" required>
                        <option value="">Select a plan</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('membership_plan_id') == $plan->id)>
                                {{ $plan->name }} — {{ number_format($plan->price, 2) }} BDT
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount Amount</label>
                    <input type="number" step="0.01" name="discount_amount" value="{{ old('discount_amount') }}" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Discount Reason</label>
                    <input type="text" name="discount_reason" value="{{ old('discount_reason') }}" class="form-control" placeholder="e.g. Student discount">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Register Member</button>
        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
