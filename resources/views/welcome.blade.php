<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('business_name', config('app.name')) }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        @if (setting('logo_path'))
            <img src="{{ asset('storage/'.setting('logo_path')) }}" style="height:64px;" class="mb-3">
        @endif
        <h1 class="mb-3">{{ setting('business_name', config('app.name')) }}</h1>
        <p class="text-muted mb-4">{{ setting('business_tagline', "Mymensingh's First Ever & Only Dedicated Ladies Gym") }}</p>
        <a href="{{ route('login') }}" class="btn btn-primary me-2">Staff Login</a>
        <a href="{{ route('member.login') }}" class="btn btn-outline-primary">Member Login</a>
    </div>
</body>
</html>
