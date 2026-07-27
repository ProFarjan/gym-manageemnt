@extends('layouts.public')

@section('title', 'Membership Plans - '.setting('business_name', config('app.name')))

@section('content')
    <section class="pt-6 pb-6" style="margin-top: 90px;">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Membership Plans</p>
                <h1 class="section-title display-5">Choose the Plan That Fits You</h1>
                <p class="lead text-muted">All prices are transparent — no hidden fees.</p>
            </div>

            <div class="row g-4 justify-content-center">
                @foreach ($plans as $index => $plan)
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ ($index % 3) * 100 }}">
                        <div class="card plan-card {{ $plan->name === '12 Month' ? 'featured' : '' }} h-100">
                            <h3 class="h5">{{ $plan->name }}</h3>
                            <div class="plan-price my-3">{{ number_format($plan->price, 0) }}<span class="fs-6"> BDT</span></div>
                            <p class="small {{ $plan->name === '12 Month' ? '' : 'text-muted' }}">
                                {{ $plan->is_lifetime ? 'One-time, lifetime access' : $plan->duration_in_months.' month(s)' }}
                            </p>
                            <ul class="list-unstyled text-start small mt-3 mb-4">
                                <li class="mb-2">✓ Full gym access</li>
                                <li class="mb-2">✓ Fingerprint/RFID entry</li>
                                <li class="mb-2">✓ Daily fitness classes</li>
                                @if (! $plan->admission_free)
                                    <li class="mb-2">
                                        Admission Fee: {{ number_format($plan->admission_fee - $plan->admission_discount, 2) }} BDT
                                        @if ($plan->admission_discount > 0)
                                            <span class="text-decoration-line-through {{ $plan->name === '12 Month' ? '' : 'text-muted' }}">{{ number_format($plan->admission_fee, 2) }}</span>
                                        @endif
                                    </li>
                                @else
                                    <li class="mb-2">✓ Admission Free</li>
                                @endif
                            </ul>
                            <a href="{{ route('register.create') }}" class="btn {{ $plan->name === '12 Month' ? 'btn-light' : 'btn-outline-primary' }} rounded-pill px-4">Choose Plan</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="text-center text-muted mt-5" data-aos="fade-up">
                Have questions about which plan is right for you? <a href="{{ route('contact') }}">Contact us</a> — we're happy to help.
            </p>
        </div>
    </section>
@endsection
