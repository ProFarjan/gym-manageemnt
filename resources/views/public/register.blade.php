<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Online Registration - {{ setting('business_name', config('app.name')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="site-body" style="background: linear-gradient(180deg, #fff0f6 0%, #f8f5f7 320px);">
    <div class="container py-5" style="max-width: 760px;">
        <div class="text-center mb-4" data-aos="fade-up">
            <a href="{{ route('home') }}" class="text-decoration-none">
                @if (setting('logo_path'))
                    <img src="{{ asset('storage/'.setting('logo_path')) }}" style="height:56px;" class="mb-2">
                @endif
                <h1 class="h3 mb-1" style="color:#2b2b2b;">{{ setting('business_name', config('app.name')) }}</h1>
            </a>
            <p class="section-eyebrow mb-0">Online Membership Registration</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" data-aos="fade-up">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card feature-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body p-4 p-md-5">
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <p class="section-eyebrow mb-2">Step 1</p>
                    <h2 class="h5 mb-3">Your Details</h2>
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

                    <p class="section-eyebrow mb-2">Step 2</p>
                    <h2 class="h5 mb-3">Health Information <span class="text-muted fw-normal">(Optional)</span></h2>
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

                    <p class="section-eyebrow mb-2">Step 3</p>
                    <h2 class="h5 mb-3">Select a Membership Plan</h2>
                    <div class="row g-3 mb-4">
                        @foreach ($plans as $plan)
                            <div class="col-md-6">
                                <label class="d-block position-relative">
                                    <input type="radio" name="membership_plan_id" value="{{ $plan->id }}"
                                        class="form-check-input position-absolute top-0 end-0 m-3" required @checked(old('membership_plan_id') == $plan->id)>
                                    <div class="card feature-card p-3 h-100" style="cursor:pointer;">
                                        <strong>{{ $plan->name }}</strong>
                                        <span class="d-block text-muted small">{{ number_format($plan->price, 2) }} BDT</span>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <p class="text-muted small">
                        Your registration will remain <strong>Pending</strong> until payment is confirmed.
                        Pay online via bKash/Nagad or visit the gym to complete payment with our staff.
                    </p>

                    <button type="submit" class="btn-gradient border-0 w-100">Submit Registration</button>
                </form>
            </div>
        </div>

        <p class="text-center text-muted small mt-4" data-aos="fade-up">
            Already a member? <a href="{{ route('member.login') }}">Log in to the Member Portal</a>
        </p>
    </div>
</body>
</html>
