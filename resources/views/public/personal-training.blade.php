@extends('layouts.public')

@section('title', 'Personal Training - '.setting('business_name', config('app.name')))

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/cable-machine.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Personal Training</p>
                <h1 class="display-4 mb-2">One-on-One Coaching,<br>Built Around You</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">Personalized programs, real accountability, faster results.</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
            @if ($trainers->isNotEmpty())
                <div class="row g-4 mb-6">
                    @foreach ($trainers as $index => $trainer)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                            <div class="card trainer-card text-center p-4">
                                @if ($trainer->trainerProfile?->photo_path)
                                    <img src="{{ asset('storage/'.$trainer->trainerProfile->photo_path) }}" class="rounded-circle mx-auto mb-3" style="width:100px;height:100px;object-fit:cover;">
                                @else
                                    <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center feature-icon" style="width:100px;height:100px;font-size:2rem;">
                                        {{ strtoupper(substr($trainer->name, 0, 1)) }}
                                    </div>
                                @endif
                                <h3 class="h6 mb-1">{{ $trainer->name }}</h3>
                                <p class="text-muted small mb-0">{{ $trainer->trainerProfile?->specialization ?? 'Personal Trainer' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($packages->isNotEmpty())
                <div class="text-center mb-5" data-aos="fade-up">
                    <p class="section-eyebrow">Packages</p>
                    <h2 class="section-title display-6">Session Packages</h2>
                </div>
                <div class="row g-4 justify-content-center">
                    @foreach ($packages as $index => $package)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                            <div class="card plan-card h-100">
                                <h3 class="h5">{{ $package->name }}</h3>
                                <div class="plan-price my-3">{{ number_format($package->price, 0) }}<span class="fs-6"> BDT</span></div>
                                <p class="text-muted small">{{ $package->sessions_count }} sessions &middot; valid {{ $package->validity_days }} days</p>
                                <a href="{{ route('contact') }}" class="btn btn-outline-primary rounded-pill px-4 mt-3">Enquire Now</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted" data-aos="fade-up">Contact us for current personal training package pricing.</p>
            @endif
        </div>
    </section>
@endsection
