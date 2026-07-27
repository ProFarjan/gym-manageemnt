<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', setting('business_name', config('app.name')))</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="w-100" style="max-width: 420px;">
        <div class="text-center mb-4">
            @if (setting('logo_path'))
                <img src="{{ asset('storage/'.setting('logo_path')) }}" style="height:48px;" class="mb-2">
            @endif
            <h1 class="h4 mb-1">{{ setting('business_name', config('app.name')) }}</h1>
            <p class="text-muted small mb-0">{{ setting('business_tagline', "Mymensingh's First Ever & Only Dedicated Ladies Gym") }}</p>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
