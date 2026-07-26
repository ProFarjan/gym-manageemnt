<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">{{ config('app.name') }}</a>

            <ul class="navbar-nav me-auto">
                @can('members.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.members.index') }}">Members</a>
                    </li>
                @endcan
                @can('membership_plans.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.membership-plans.index') }}">Plans</a>
                    </li>
                @endcan
                @can('offers.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.offers.index') }}">Offers</a>
                    </li>
                @endcan
                @can('personal_training.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.personal-training-packages.index') }}">PT Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.classes.index') }}">Classes</a>
                    </li>
                @endcan
                @can('users.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.trainers.index') }}">Trainers</a>
                    </li>
                @endcan
            </ul>

            <div class="d-flex align-items-center ms-auto">
                <span class="text-white small me-3">
                    {{ auth()->user()->name }}
                    @if (auth()->user()->roles->isNotEmpty())
                        <span class="badge bg-light text-primary ms-1">{{ auth()->user()->roles->first()->name }}</span>
                    @endif
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
