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

    <section class="pb-5 pb-lg-6 bg-white">
        <div class="container">
            <div class="stats-bar row g-3 g-md-4">
                <div class="col-6 col-md-3" data-aos="fade-up">
                    <div class="stat-icon-card">
                        <div class="stat-icon-badge">👥</div>
                        <div class="stat-counter" data-counter="{{ $stats['members'] }}" data-suffix="+" style="font-size:2rem;">0</div>
                        <p class="text-muted small mb-0">Members</p>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon-card">
                        <div class="stat-icon-badge">🏆</div>
                        <div class="stat-counter" data-counter="{{ $stats['trainers'] }}" data-suffix="+" style="font-size:2rem;">0</div>
                        <p class="text-muted small mb-0">Expert Trainers</p>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon-card">
                        <div class="stat-icon-badge">📅</div>
                        <div class="stat-counter" data-counter="{{ $stats['classes'] }}" data-suffix="+" style="font-size:2rem;">0</div>
                        <p class="text-muted small mb-0">Weekly Classes</p>
                    </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon-card">
                        <div class="stat-icon-badge">🔒</div>
                        <div class="stat-counter" data-counter="100" data-suffix="%" style="font-size:2rem;">0</div>
                        <p class="text-muted small mb-0">Ladies Only</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 py-lg-6 bg-white">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Why GirliGirl</p>
                <h2 class="section-title display-6">Everything You Need to Thrive</h2>
                <p class="text-muted mx-auto" style="max-width:560px;">
                    From your first orientation to your hundredth session, every part of the experience is built
                    around helping you keep showing up.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🏋️</div>
                        <h3 class="h5">Personal Training</h3>
                        <p class="text-muted small mb-0">One-on-one coaching tailored to your goals, from certified trainers who know how to push you further.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">💪</div>
                        <h3 class="h5">Weight Training</h3>
                        <p class="text-muted small mb-0">A fully-equipped strength zone with guided programs for every level, from first-timers to seasoned lifters.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🧘</div>
                        <h3 class="h5">Daily Classes</h3>
                        <p class="text-muted small mb-0">Zumba, yoga, HIIT and more — a fresh class every day to keep your routine exciting and effective.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">📲</div>
                        <h3 class="h5">Member Portal</h3>
                        <p class="text-muted small mb-0">Track your membership status, payment history, and attendance online, anytime.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 py-lg-6">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Getting Started</p>
                <h2 class="section-title display-6">Three Steps to Your First Workout</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4 step-card" data-aos="fade-up">
                    <div class="step-connector d-none d-md-block"></div>
                    <div class="step-number">1</div>
                    <h3 class="h5">Choose a Plan</h3>
                    <p class="text-muted small mx-auto" style="max-width:280px;">Monthly, 3/6/12-month, or Lifetime — pick what fits, all prices upfront.</p>
                </div>
                <div class="col-md-4 step-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-connector d-none d-md-block"></div>
                    <div class="step-number">2</div>
                    <h3 class="h5">Register Online</h3>
                    <p class="text-muted small mx-auto" style="max-width:280px;">Pay via bKash or Nagad for instant approval, or visit us to pay in person.</p>
                </div>
                <div class="col-md-4 step-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-number">3</div>
                    <h3 class="h5">Start Training</h3>
                    <p class="text-muted small mx-auto" style="max-width:280px;">Walk in, check in with fingerprint/RFID, and get started with your trainer.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 py-lg-6 bg-white">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 position-relative" data-aos="fade-right">
                    <div class="image-reveal" style="height:420px;">
                        <img src="{{ asset('images/stock/mat-workout.jpg') }}" alt="Member training on the floor">
                    </div>
                    <div class="image-badge d-none d-md-block">
                        <div class="stat-counter" data-counter="{{ $stats['members'] }}" data-suffix="+" style="font-size:1.75rem;">0</div>
                        <p class="text-muted small mb-0">Members &amp; growing</p>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <p class="section-eyebrow">The Experience</p>
                    <h2 class="section-title display-6 mb-3">Train in a Space That Feels Like Yours</h2>
                    <p class="text-muted mb-3">
                        Bright, modern, and built exclusively for women — every corner of the gym is designed so
                        you can focus entirely on your workout, without a second thought about anything else.
                    </p>
                    <div class="amenity-item">
                        <span class="amenity-check">✓</span>
                        <span>Open daily, 6AM – {{ \Carbon\Carbon::parse(setting('gym_closing_time', '22:00'))->format('g:i A') }}</span>
                    </div>
                    <div class="amenity-item">
                        <span class="amenity-check">✓</span>
                        <span>100% private, ladies-only floor and reception</span>
                    </div>
                    <div class="amenity-item">
                        <span class="amenity-check">✓</span>
                        <span>Fingerprint &amp; RFID secured entry</span>
                    </div>
                    <div class="amenity-item">
                        <span class="amenity-check">✓</span>
                        <span>Certified trainers on the floor every session</span>
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
                    <h2 class="section-title display-6">Plans Built Around Your Goal</h2>
                    <p class="text-muted mx-auto" style="max-width:560px;">
                        Transparent pricing, no hidden fees — every plan includes full gym access, fingerprint
                        entry, and daily fitness classes.
                    </p>
                </div>
                <div class="row g-4 justify-content-center">
                    @foreach ($plans as $index => $plan)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            <div class="card plan-card position-relative {{ $loop->iteration === 2 ? 'featured' : '' }}">
                                @if ($loop->iteration === 2)
                                    <div class="ribbon-badge">Most Popular</div>
                                @endif
                                <h3 class="h5">{{ $plan->name }}</h3>
                                <div class="plan-price my-3">{{ number_format($plan->price, 0) }}<span class="fs-6"> BDT</span></div>
                                <p class="small {{ $loop->iteration === 2 ? '' : 'text-muted' }}">
                                    {{ $plan->is_lifetime ? 'One-time, lifetime access' : $plan->duration_in_months.' month(s)' }}
                                </p>
                                <ul class="plan-features">
                                    <li><span class="amenity-check">✓</span> Full gym access</li>
                                    <li><span class="amenity-check">✓</span> Fingerprint/RFID entry</li>
                                    <li><span class="amenity-check">✓</span> Daily fitness classes</li>
                                    <li>
                                        <span class="amenity-check">✓</span>
                                        {{ $plan->admission_free ? 'Admission Free' : 'Admission '.number_format($plan->admission_fee - $plan->admission_discount, 0).' BDT' }}
                                    </li>
                                </ul>
                                <a href="{{ route('register.create') }}" class="btn {{ $loop->iteration === 2 ? 'btn-light' : 'btn-outline-primary' }} rounded-pill px-4">Choose Plan</a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{ route('membership-plans') }}" class="btn btn-link text-decoration-none fw-semibold">See full plan comparison &rarr;</a>
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
                                @if ($image->caption)
                                    <div class="gallery-caption-overlay">{{ $image->caption }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-5" data-aos="fade-up">
                    <a href="{{ route('gallery') }}" class="btn-outline-gradient">View Full Gallery</a>
                </div>
            </div>
        </section>
    @endif

    <section class="py-5 py-lg-6">
        <div class="container py-5">
            <div class="cta-section position-relative p-5 text-center overflow-hidden" data-aos="zoom-in">
                <div class="hero-bg" style="background-image: url('{{ asset('images/stock/floor-class.jpg') }}'); animation-duration: 30s;"></div>
                <div class="position-relative" style="z-index:2;">
                    <h2 class="display-6 mb-3">Ready to Start Your Journey?</h2>
                    <p class="lead mb-4">Register online today — approval is instant with bKash or Nagad payment.</p>
                    <a href="{{ route('register.create') }}" class="btn-hero-primary">Join Now</a>
                    <div class="trust-row">
                        <span class="trust-badge">✓ Instant Online Approval</span>
                        <span class="trust-badge">✓ No Hidden Fees</span>
                        <span class="trust-badge">✓ bKash &amp; Nagad Accepted</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
