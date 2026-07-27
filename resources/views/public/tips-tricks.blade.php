@extends('layouts.public')

@section('title', 'Tips & Tricks - '.setting('business_name', config('app.name')))

@php
    $tips = [
        ['icon' => '🔥', 'title' => 'Warm Up Properly', 'body' => 'Spend 5–10 minutes on dynamic stretches before lifting — it reduces injury risk and improves performance.'],
        ['icon' => '📈', 'title' => 'Progressive Overload', 'body' => 'Gradually increase weight, reps, or sets over time. Small, consistent increases beat big jumps.'],
        ['icon' => '😴', 'title' => 'Prioritize Sleep', 'body' => 'Muscles recover and grow during rest. Aim for 7–8 hours of sleep for the best training results.'],
        ['icon' => '🧘‍♀️', 'title' => 'Don\'t Skip Rest Days', 'body' => 'Recovery days are part of the program, not a break from it — they prevent burnout and injury.'],
        ['icon' => '📝', 'title' => 'Track Your Workouts', 'body' => 'Logging your sessions helps you see real progress and stay motivated on tough days.'],
        ['icon' => '🎧', 'title' => 'Find Your Rhythm', 'body' => 'A consistent pre-workout routine — music, warm-up, mindset — makes showing up easier every time.'],
    ];
@endphp

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/yoga-pose.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Tips &amp; Tricks</p>
                <h1 class="display-4 mb-2">Train Smarter</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">Practical advice from our trainers to help you get more out of every session.</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
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
                <a href="{{ route('classes') }}" class="btn-gradient">Explore Our Classes</a>
            </div>
        </div>
    </section>
@endsection
