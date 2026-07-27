@extends('layouts.public')

@section('title', 'Contact Us - '.setting('business_name', config('app.name')))

@section('content')
    <section class="pt-6 pb-6" style="margin-top: 90px;">
        <div class="container py-4">
            <div class="text-center mb-5" data-aos="fade-up">
                <p class="section-eyebrow">Contact Us</p>
                <h1 class="section-title display-5">We'd Love to Hear From You</h1>
                <p class="lead text-muted">Questions about membership, classes, or personal training? Send us a message.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success" data-aos="fade-up">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" data-aos="fade-up">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-5">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="card feature-card p-4 mb-3">
                        <h3 class="h6">Address</h3>
                        <p class="text-muted small mb-0">{{ setting('business_address') }}</p>
                    </div>
                    <div class="card feature-card p-4 mb-3">
                        <h3 class="h6">Phone</h3>
                        <p class="text-muted small mb-0">{{ setting('business_phone') }}</p>
                    </div>
                    <div class="card feature-card p-4">
                        <h3 class="h6">Hours</h3>
                        <p class="text-muted small mb-0">Open daily until {{ \Carbon\Carbon::parse(setting('gym_closing_time', '22:00'))->format('g:i A') }}</p>
                    </div>
                </div>

                <div class="col-lg-7" data-aos="fade-left">
                    <div class="card feature-card p-4">
                        <form method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Subject</label>
                                    <input type="text" name="subject" value="{{ old('subject') }}" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message *</label>
                                    <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn-gradient border-0">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
