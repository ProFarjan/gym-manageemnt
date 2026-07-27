@extends('layouts.public')

@section('title', 'Daily Fitness Classes - '.setting('business_name', config('app.name')))

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/floor-class.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Daily Fitness Classes</p>
                <h1 class="display-4 mb-2">A Fresh Class Every Day</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">Group energy, expert instruction — find your favorite.</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
            @if ($classes->isNotEmpty())
                <div class="row g-4">
                    @foreach ($classes as $index => $class)
                        <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                            <div class="card class-card p-4">
                                <span class="badge bg-light text-dark mb-2" style="width:fit-content;">{{ ucfirst($class->day_of_week) }}</span>
                                <h3 class="h5">{{ $class->name }}</h3>
                                <p class="text-muted small mb-1">
                                    {{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }}
                                    - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}
                                </p>
                                <p class="text-muted small mb-0">With {{ $class->trainer?->name ?? 'Our Trainers' }}</p>
                                <p class="text-muted small mb-0">Capacity: {{ $class->capacity }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted" data-aos="fade-up">Class schedule coming soon — check back or contact us for details.</p>
            @endif

            <div class="text-center mt-6" data-aos="fade-up">
                <a href="{{ route('register.create') }}" class="btn-gradient">Join a Class Today</a>
            </div>
        </div>
    </section>
@endsection
