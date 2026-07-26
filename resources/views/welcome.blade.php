<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="text-center">
        <h1 class="mb-3">GirliGirl Gym &amp; Fitness</h1>
        <p class="text-muted mb-4">Mymensingh's First Ever &amp; Only Dedicated Ladies Gym</p>
        <a href="{{ route('login') }}" class="btn btn-primary me-2">Staff Login</a>
        <a href="{{ route('member.login') }}" class="btn btn-outline-primary">Member Login</a>
    </div>
</body>
</html>
