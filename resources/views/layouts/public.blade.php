<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', setting('business_name', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', setting('business_tagline'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="site-body @yield('body-class')">

    <nav class="site-navbar navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                @if (setting('logo_path'))
                    <img src="{{ asset('storage/'.setting('logo_path')) }}" style="height:32px;">
                @endif
                {{ setting('business_name', config('app.name')) }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="siteNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('membership-plans') }}">Membership</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Programs</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('personal-training') }}">Personal Training</a></li>
                            <li><a class="dropdown-item" href="{{ route('weight-training') }}">Weight Training</a></li>
                            <li><a class="dropdown-item" href="{{ route('classes') }}">Daily Fitness Classes</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Resources</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('diet-nutrition') }}">Diet &amp; Nutrition</a></li>
                            <li><a class="dropdown-item" href="{{ route('tips-tricks') }}">Tips &amp; Tricks</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('gallery') }}">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('member.login') }}" class="nav-link">Member Login</a>
                    <a href="{{ route('register.create') }}" class="btn-cta">Join Now</a>
                </div>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="site-footer pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5>{{ setting('business_name', config('app.name')) }}</h5>
                    <p class="small">{{ setting('business_tagline') }}</p>
                    <div class="d-flex gap-2 mt-3">
                        <a href="#" class="social-icon">f</a>
                        <a href="#" class="social-icon">ig</a>
                        <a href="#" class="social-icon">yt</a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('about') }}">About Us</a></li>
                        <li class="mb-2"><a href="{{ route('membership-plans') }}">Membership Plans</a></li>
                        <li class="mb-2"><a href="{{ route('gallery') }}">Gallery</a></li>
                        <li class="mb-2"><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h5>Programs</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('personal-training') }}">Personal Training</a></li>
                        <li class="mb-2"><a href="{{ route('weight-training') }}">Weight Training</a></li>
                        <li class="mb-2"><a href="{{ route('classes') }}">Daily Classes</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <p class="small mb-1">{{ setting('business_address') }}</p>
                    <p class="small mb-1">Phone: {{ setting('business_phone') }}</p>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <p class="text-center small mb-0">&copy; {{ date('Y') }} {{ setting('business_name', config('app.name')) }}. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
