<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Online Registration - {{ setting('business_name', config('app.name')) }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container py-5" style="max-width: 720px;">
        <div class="text-center mb-4">
            @if (setting('logo_path'))
                <img src="{{ asset('storage/'.setting('logo_path')) }}" style="height:56px;" class="mb-2">
            @endif
            <h1 class="h3 mb-1">{{ setting('business_name', config('app.name')) }}</h1>
            <p class="text-muted">Online Membership Registration</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <h2 class="h6 mb-3">Your Details</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" name="mobile_number" value="{{ old('mobile_number') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Health Information (Optional)</h2>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Height (cm)</label>
                            <input type="number" step="0.01" name="height" value="{{ old('height') }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" class="form-control">
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
                            <input type="text" name="fitness_goal" value="{{ old('fitness_goal') }}" class="form-control">
                        </div>
                    </div>

                    <h2 class="h6 mb-3">Select a Membership Plan</h2>
                    <div class="row g-2 mb-4">
                        @foreach ($plans as $plan)
                            <div class="col-md-6">
                                <div class="form-check border rounded p-3">
                                    <input type="radio" name="membership_plan_id" value="{{ $plan->id }}" id="plan{{ $plan->id }}"
                                        class="form-check-input" required @checked(old('membership_plan_id') == $plan->id)>
                                    <label for="plan{{ $plan->id }}" class="form-check-label w-100">
                                        <strong>{{ $plan->name }}</strong> — {{ number_format($plan->price, 2) }} BDT
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-muted small">
                        Your registration will remain <strong>Pending</strong> until payment is confirmed.
                        Pay online via bKash/Nagad or visit the gym to complete payment with our staff.
                    </p>

                    <button type="submit" class="btn btn-primary w-100">Submit Registration</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
