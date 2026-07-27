@extends('layouts.public')

@section('title', 'Weight Training - '.setting('business_name', config('app.name')))

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/weight-training-press.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Weight Training</p>
                <h1 class="display-4 mb-2">Build Real Strength</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9); max-width:600px; margin:0 auto;">
                    A fully-equipped strength zone with free weights, machines, and guided programs.
                </p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
            <div class="row align-items-center g-5 mb-6">
                <div class="col-lg-6" data-aos="fade-right">
                    <p class="section-eyebrow">Why Lift With Us</p>
                    <h2 class="h3 mb-3">Built for Beginners and Lifters Alike</h2>
                    <p class="text-muted">
                        Whether it's your first time picking up a dumbbell or you're chasing a new personal best,
                        our strength zone is designed to meet you where you are.
                    </p>
                    <a href="{{ route('register.create') }}" class="btn-outline-gradient">Get Started</a>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card feature-card p-4">
                                <div class="feature-icon mb-3">🏋️‍♀️</div>
                                <h3 class="h6">Free Weights</h3>
                                <p class="text-muted small mb-0">Dumbbells, barbells, and racks for every strength level.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card feature-card p-4">
                                <div class="feature-icon mb-3">⚙️</div>
                                <h3 class="h6">Guided Machines</h3>
                                <p class="text-muted small mb-0">Beginner-friendly resistance machines with correct-form guidance.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card feature-card p-4">
                                <div class="feature-icon mb-3">📋</div>
                                <h3 class="h6">Structured Programs</h3>
                                <p class="text-muted small mb-0">Progressive plans so you always know your next step.</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card feature-card p-4">
                                <div class="feature-icon mb-3">🎯</div>
                                <h3 class="h6">Form Coaching</h3>
                                <p class="text-muted small mb-0">Trainers on the floor to correct form and prevent injury.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow">New to Weight Training?</p>
                <h2 class="section-title display-6 mb-3">We'll Start You Off Right</h2>
                <p class="text-muted mx-auto" style="max-width:640px;">
                    Every new member gets a floor orientation covering equipment safety and proper form before
                    starting a strength program. No question is too basic — that's what we're here for.
                </p>
                <a href="{{ route('personal-training') }}" class="btn btn-outline-primary rounded-pill px-4 mt-2">Pair It With Personal Training</a>
            </div>
        </div>
    </section>
@endsection
