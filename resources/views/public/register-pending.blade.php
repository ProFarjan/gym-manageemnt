<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Submitted - {{ setting('business_name', config('app.name')) }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="text-center" style="max-width: 480px;">
        <h1 class="h4 mb-3">Registration Submitted</h1>
        <p>Your admission ID is <strong>{{ $admission_id }}</strong>.</p>
        <p class="text-muted">Your account is <strong>Pending</strong> until payment is confirmed. Once approved, you can log in to the Member Portal to view your membership status.</p>
        <a href="{{ route('member.login') }}" class="btn btn-primary mt-3">Go to Member Login</a>
    </div>
</body>
</html>
