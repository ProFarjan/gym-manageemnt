<header class="admin-topbar">
    <button class="admin-sidebar-toggle" type="button" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="adminSidebar">
        <i class="bi bi-list"></i>
    </button>

    <h1 class="admin-page-title">@yield('title', 'Dashboard')</h1>

    <div class="admin-topbar-user dropdown ms-auto">
        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
            <span class="admin-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
            @if (auth()->user()->roles->isNotEmpty())
                <span class="badge bg-light text-dark">{{ auth()->user()->roles->first()->name }}</span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</header>
