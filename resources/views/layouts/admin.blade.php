<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - {{ setting('business_name', config('app.name')) }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="admin-body">
    @include('layouts.partials.admin-sidebar')

    <div class="admin-main">
        @include('layouts.partials.admin-topbar')

        <main class="admin-content">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
