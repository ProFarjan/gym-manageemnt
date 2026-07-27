@extends('layouts.public')

@section('title', 'About Us - '.setting('business_name', config('app.name')))

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/gym-interior.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">About Us</p>
                <h1 class="display-4 mb-2">{{ setting('business_name', config('app.name')) }}</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">{{ setting('business_tagline') }}</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-5">
        <div class="container py-4">
            <div class="row align-items-center g-5 mb-6">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="image-reveal" style="height:380px;">
                        <img src="{{ asset('images/stock/floor-class.jpg') }}" alt="Members in a group fitness class">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <p class="section-eyebrow">Our Story</p>
                    <h2 class="h3 mb-3">A Space Built Only for Women</h2>
                    <p class="text-muted">
                        We opened our doors with one goal: give the women of Mymensingh a fitness space where they
                        can train without hesitation, judgement, or discomfort. Every trainer, every class, and
                        every piece of equipment here is chosen with that goal in mind.
                    </p>
                    <p class="text-muted">
                        Whether you're taking your first step into fitness or chasing a competitive goal, our team
                        works with you individually — because no two journeys look the same.
                    </p>
                    <div class="row g-3 mt-2">
                        <div class="col-6">
                            <div class="stat-counter" data-counter="{{ $stats['members'] }}" data-suffix="+">0</div>
                            <p class="text-muted mb-0">Happy Members</p>
                        </div>
                        <div class="col-6">
                            <div class="stat-counter" data-counter="{{ $stats['trainers'] }}" data-suffix="+">0</div>
                            <p class="text-muted mb-0">Certified Trainers</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Our Values</p>
                <h2 class="section-title display-6">Why Members Choose Us</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🔒</div>
                        <h3 class="h5">Privacy First</h3>
                        <p class="text-muted mb-0">A fully ladies-only environment, from the reception desk to the training floor.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🎯</div>
                        <h3 class="h5">Goal-Focused</h3>
                        <p class="text-muted mb-0">Every membership starts with a fitness goal conversation — we track progress against it.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card feature-card p-4">
                        <div class="feature-icon mb-3">🤝</div>
                        <h3 class="h5">Real Community</h3>
                        <p class="text-muted mb-0">Group classes and a shared space that keeps you motivated on the days it's hardest to show up.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container py-5">
            <div class="cta-section p-5 text-center" data-aos="zoom-in">
                <h2 class="display-6 mb-3">Come See It for Yourself</h2>
                <p class="lead mb-4">Visit us or register online — we'll help you find the right plan.</p>
                <a href="{{ route('contact') }}" class="btn-hero-primary">Get in Touch</a>
            </div>
        </div>
    </section>
@endsection
