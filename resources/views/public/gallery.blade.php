@extends('layouts.public')

@section('title', 'Gallery - '.setting('business_name', config('app.name')))

@section('body-class', 'has-hero')

@section('content')
    <section class="page-hero">
        <div class="hero-bg" style="background-image: url('{{ asset('images/stock/hero-group-class.jpg') }}');"></div>
        <div class="container position-relative" style="z-index:2;">
            <div class="text-center" data-aos="fade-up">
                <p class="section-eyebrow" style="color:#fff;">Gallery</p>
                <h1 class="display-4 mb-2">A Look Inside</h1>
                <p class="lead mb-0" style="color:rgba(255,255,255,.9);">Our space, our classes, our community.</p>
            </div>
        </div>
    </section>

    <section class="pt-6 pb-6">
        <div class="container py-4">
            @if ($images->isNotEmpty())
                <div class="row g-3">
                    @foreach ($images as $index => $image)
                        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="{{ ($index % 6) * 80 }}">
                            <div class="gallery-item">
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->caption }}">
                            </div>
                            @if ($image->caption)
                                <p class="text-muted small mt-2 mb-0">{{ $image->caption }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-muted" data-aos="fade-up">Gallery photos coming soon.</p>
            @endif
        </div>
    </section>
@endsection
