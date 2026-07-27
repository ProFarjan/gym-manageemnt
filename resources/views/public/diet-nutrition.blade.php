@extends('layouts.public')

@section('title', 'Diet & Nutrition - '.setting('business_name', config('app.name')))

@php
    $tips = [
        ['icon' => '🥗', 'title' => 'Protein First', 'body' => 'Aim for a palm-sized portion of protein at every meal — it keeps you fuller longer and supports muscle recovery.'],
        ['icon' => '💧', 'title' => 'Hydrate Well', 'body' => 'Drink water throughout the day, not just during workouts. Even mild dehydration can hurt performance and focus.'],
        ['icon' => '🍚', 'title' => 'Smart Carbs', 'body' => 'Whole grains, fruits, and vegetables fuel your workouts better than refined sugar — and keep energy steady.'],
        ['icon' => '🥑', 'title' => 'Healthy Fats', 'body' => 'Nuts, seeds, and oils in moderation support hormone health — don\'t fear fat, just choose the right kind.'],
        ['icon' => '⏰', 'title' => 'Meal Timing', 'body' => 'Eating a light meal 1–2 hours before training gives you energy without feeling heavy on the floor.'],
        ['icon' => '🍽️', 'title' => 'Portion Awareness', 'body' => 'Consistency beats restriction — sustainable portions you can maintain long-term always win.'],
    ];
@endphp

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/nutrition-bowl.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Diet &amp; Nutrition</p>
                <h1 class="display-4 mb-2">Fuel Your Progress</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">Simple nutrition guidance to support your training.</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
            <div class="row align-items-center g-5 mb-6">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="image-reveal" style="height:340px;">
                        <img src="{{ asset('images/stock/nutrition-fruits.jpg') }}" alt="Fresh fruit and produce">
                    </div>
                </div>
                <div class="col-lg-7" data-aos="fade-left">
                    <p class="section-eyebrow">Ask a Trainer</p>
                    <h2 class="h3 mb-3">Food Is Part of the Program</h2>
                    <p class="text-muted">
                        Training hard only gets you so far without the right fuel. Our trainers build simple,
                        sustainable eating guidance into every personal training package — no fad diets, just food
                        that supports how you train.
                    </p>
                </div>
            </div>

            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Quick Tips</p>
                <h2 class="section-title display-6">Nutrition Basics</h2>
            </div>

            <div class="row g-4">
                @foreach ($tips as $index => $tip)
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                        <div class="card tip-card p-4">
                            <div class="feature-icon mb-3">{{ $tip['icon'] }}</div>
                            <h3 class="h6">{{ $tip['title'] }}</h3>
                            <p class="text-muted small mb-0">{{ $tip['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-6" data-aos="fade-up">
                <p class="text-muted mx-auto mb-3" style="max-width:600px;">
                    Want a nutrition plan built around your specific fitness goal? Our trainers can help as part of
                    your personal training package.
                </p>
                <a href="{{ route('personal-training') }}" class="btn-gradient">Talk to a Trainer</a>
            </div>
        </div>
    </section>
@endsection
