<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Submitted - {{ setting('business_name', config('app.name')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="site-body d-flex align-items-center justify-content-center vh-100" style="background: var(--gg-gradient);">
    <div class="card feature-card text-center p-5" style="max-width: 480px;" data-aos="zoom-in">
        <div class="feature-icon mx-auto mb-3" style="font-size:2rem;">✓</div>
        <h1 class="h4 mb-3">Registration Submitted</h1>
        <p>Your admission ID is <strong>{{ $admission_id }}</strong>.</p>
        <p class="text-muted">Your account is <strong>Pending</strong> until payment is confirmed. Once approved, you can log in to the Member Portal to view your membership status.</p>
        <a href="{{ route('member.login') }}" class="btn-gradient border-0 mt-3">Go to Member Login</a>
    </div>
</body>
</html>
