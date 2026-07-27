@extends('layouts.public')

@section('title', setting('business_name', config('app.name')).' - '.setting('business_tagline'))
@section('body-class', 'has-hero')

@section('content')
    <section class="hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/hero-group-class.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="row align-items-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <p class="text-uppercase fw-bold mb-3" style="letter-spacing:.15em; color:rgba(255,255,255,.85);">
                        {{ setting('business_tagline') }}
                    </p>
                    <h1 class="display-3 mb-4">Strong. Confident.<br>Unstoppable.</h1>
                    <p class="lead mb-4" style="max-width:560px;">
                        A dedicated fitness space designed for women — expert trainers, modern equipment, and a
                        community that pushes you further every day.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('register.create') }}" class="btn-hero-primary">Join Now</a>
                        <a href="{{ route('membership-plans') }}" class="btn-hero-outline">View Plans</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="marquee-band">
        <div class="marquee-track">
            @for ($i = 0; $i < 2; $i++)
                <span>Ladies Only</span>
                <span>Personal Training</span>
                <span>Daily Fitness Classes</span>
                <span>Weight Training</span>
                <span>Instant Online Approval</span>
                <span>Certified Trainers</span>
            @endfor
        </div>
    </div>

    <section class="py-5 py-lg-6">
        <div class="container py-4">
            <div class="row text-center g-4">
                <div class="col-6 col-md-3" data-aos="fade-up">
                    <div class="stat-counter" data-counter="{{ $stats['members'] }}" data-suffix="+">0</div>
                    <p class="text-muted">Members</p>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-counter" data-counter="{{ $stats['trainers'] }}" data-suffix="+">0</div>
                    <p class="text-muted">Expert Trainers</p>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-counter" data-counter="{{ $stats['classes'] }}" data-suffix="+">0</div>
                    <p class="text-muted">Weekly Classes</p>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-counter" data-counter="100" data-suffix="%">0</div>
                    <p class="text-muted">Ladies Only</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 py-lg-6 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Why GirliGirl</p>
                <h2 class="section-title display-6">Everything You Need to Thrive</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🏋️</div>
                        <h3 class="h5">Personal Training</h3>
                        <p class="text-muted mb-0">One-on-one coaching tailored to your goals, from certified trainers who know exactly how to push you further.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">💪</div>
                        <h3 class="h5">Weight Training</h3>
                        <p class="text-muted mb-0">A fully-equipped strength zone with guided programs for every level, from first-timers to seasoned lifters.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🧘</div>
                        <h3 class="h5">Daily Fitness Classes</h3>
                        <p class="text-muted mb-0">Zumba, yoga, HIIT and more — a fresh class every day to keep your routine exciting and effective.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 py-lg-6">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="image-reveal" style="height:420px;">
                        <img src="{{ asset('images/stock/mat-workout.jpg') }}" alt="Member training on the floor">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <p class="section-eyebrow">The Experience</p>
                    <h2 class="section-title display-6 mb-3">Train in a Space That Feels Like Yours</h2>
                    <p class="text-muted mb-4">
                        Bright, modern, and built exclusively for women — every corner of the gym is designed so
                        you can focus entirely on your workout, without a second thought about anything else.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-counter" style="font-size:1.5rem;">6AM–{{ \Carbon\Carbon::parse(setting('gym_closing_time', '22:00'))->format('gA') }}</div>
                            <p class="text-muted small mb-0">Open Daily</p>
                        </div>
                        <div class="col-6">
                            <div class="stat-counter" style="font-size:1.5rem;">100%</div>
                            <p class="text-muted small mb-0">Private &amp; Secure</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($plans->isNotEmpty())
        <section class="py-5 py-lg-6">
            <div class="container py-4">
                <div class="text-center mb-5" data-aos="fade-up">
                    <p class="section-eyebrow">Membership</p>
                    <h2 class="section-title display-6">Simple, Honest Pricing</h2>
                </div>
                <div class="row g-4 justify-content-center">
                    @foreach ($plans as $index => $plan)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            <div class="card plan-card {{ $loop->iteration === 2 ? 'featured' : '' }}">
                                <h3 class="h5">{{ $plan->name }}</h3>
                                <div class="plan-price my-3">{{ number_format($plan->price, 0) }}<span class="fs-6"> BDT</span></div>
                                <p class="text-muted small">{{ $plan->is_lifetime ? 'One-time, lifetime access' : $plan->duration_in_months.' month(s)' }}</p>
                                <a href="{{ route('register.create') }}" class="btn {{ $loop->iteration === 2 ? 'btn-light' : 'btn-outline-primary' }} rounded-pill px-4 mt-3">Choose Plan</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{ route('membership-plans') }}" class="btn btn-link text-decoration-none fw-semibold">See all plans &rarr;</a>
                </div>
            </div>
        </section>
    @endif

    @if ($images->isNotEmpty())
        <section class="py-5 py-lg-6 bg-white">
            <div class="container py-4">
                <div class="text-center mb-5" data-aos="fade-up">
                    <p class="section-eyebrow">Inside the Gym</p>
                    <h2 class="section-title display-6">A Space Built for You</h2>
                </div>
                <div class="row g-3">
                    @foreach ($images as $index => $image)
                        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="{{ $index * 80 }}">
                            <div class="gallery-item">
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->caption }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-5 py-lg-6">
        <div class="container py-5">
            <div class="cta-section p-5 text-center" data-aos="zoom-in">
                <h2 class="display-6 mb-3">Ready to Start Your Journey?</h2>
                <p class="lead mb-4">Register online today — approval is instant with bKash or Nagad payment.</p>
                <a href="{{ route('register.create') }}" class="btn-hero-primary">Join Now</a>
            </div>
        </div>
    </section>
@endsection
