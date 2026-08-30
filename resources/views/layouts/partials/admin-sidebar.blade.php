<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <a href="{{ route('admin.dashboard') }}">
            @if (setting('logo_path'))
                <img src="{{ asset('storage/'.setting('logo_path')) }}" alt="">
            @endif
            <span class="brand-text">{{ setting('business_name', config('app.name')) }}</span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
            </li>

            @can('members.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}"
                       href="{{ route('admin.members.index') }}">
                        <i class="bi bi-people"></i><span>Members</span>
                    </a>
                </li>
            @endcan

            @can('bills.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bills.*') ? 'active' : '' }}"
                       href="{{ route('admin.bills.index') }}">
                        <i class="bi bi-receipt"></i><span>Bills</span>
                    </a>
                </li>
            @endcan

            @can('lockers.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.lockers.*') ? 'active' : '' }}"
                       href="{{ route('admin.lockers.index') }}">
                        <i class="bi bi-lock"></i><span>Lockers</span>
                    </a>
                </li>
            @endcan

            @canany(['membership_plans.view', 'offers.view'])
                @php $packagesActive = request()->routeIs(['admin.membership-plans.*', 'admin.offers.*']); @endphp
                <li class="nav-item">
                    <a class="nav-link has-treeview {{ $packagesActive ? 'active' : '' }}"
                       href="#" data-bs-toggle="collapse" data-bs-target="#navPackages"
                       aria-expanded="{{ $packagesActive ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam"></i><span>Packages</span>
                        <i class="bi bi-chevron-down treeview-caret"></i>
                    </a>
                    <div class="collapse {{ $packagesActive ? 'show' : '' }}" id="navPackages">
                        <ul class="nav flex-column sub-nav">
                            @can('membership_plans.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.membership-plans.*') ? 'active' : '' }}"
                                       href="{{ route('admin.membership-plans.index') }}">Plans</a>
                                </li>
                            @endcan
                            @can('offers.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}"
                                       href="{{ route('admin.offers.index') }}">Offers</a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @canany(['users.view', 'personal_training.view'])
                @php $trainersActive = request()->routeIs(['admin.trainers.*', 'admin.classes.*', 'admin.personal-training-packages.*']); @endphp
                <li class="nav-item">
                    <a class="nav-link has-treeview {{ $trainersActive ? 'active' : '' }}"
                       href="#" data-bs-toggle="collapse" data-bs-target="#navTrainers"
                       aria-expanded="{{ $trainersActive ? 'true' : 'false' }}">
                        <i class="bi bi-person-badge"></i><span>Trainers</span>
                        <i class="bi bi-chevron-down treeview-caret"></i>
                    </a>
                    <div class="collapse {{ $trainersActive ? 'show' : '' }}" id="navTrainers">
                        <ul class="nav flex-column sub-nav">
                            @can('users.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.trainers.*') ? 'active' : '' }}"
                                       href="{{ route('admin.trainers.index') }}">Trainer List</a>
                                </li>
                            @endcan
                            @can('personal_training.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"
                                       href="{{ route('admin.classes.index') }}">Classes</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.personal-training-packages.*') ? 'active' : '' }}"
                                       href="{{ route('admin.personal-training-packages.index') }}">PT Packages</a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcanany

            @can('payments.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}"
                       href="{{ route('admin.expenses.index') }}">
                        <i class="bi bi-cash-stack"></i><span>Expense</span>
                    </a>
                </li>
            @endcan

            @can('members.update')
                @php $marketingActive = request()->routeIs(['admin.bulk-notifications.*', 'admin.zkteco-sync-logs.*']); @endphp
                <li class="nav-item">
                    <a class="nav-link has-treeview {{ $marketingActive ? 'active' : '' }}"
                       href="#" data-bs-toggle="collapse" data-bs-target="#navMarketing"
                       aria-expanded="{{ $marketingActive ? 'true' : 'false' }}">
                        <i class="bi bi-megaphone"></i><span>Marketing</span>
                        <i class="bi bi-chevron-down treeview-caret"></i>
                    </a>
                    <div class="collapse {{ $marketingActive ? 'show' : '' }}" id="navMarketing">
                        <ul class="nav flex-column sub-nav">
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">
                                    SMS Campaign <span class="badge bg-secondary soon-badge">Soon</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">
                                    Mail Campaign <span class="badge bg-secondary soon-badge">Soon</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.bulk-notifications.*') ? 'active' : '' }}"
                                   href="{{ route('admin.bulk-notifications.create') }}">Send Message</a>
                            </li>
                            @can('settings.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.zkteco-sync-logs.*') ? 'active' : '' }}"
                                       href="{{ route('admin.zkteco-sync-logs.index') }}">Logs</a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </li>
            @endcan

            @can('reports.view')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
                       href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-bar-chart"></i><span>Reports</span>
                    </a>
                </li>
            @endcan
        </ul>
    </nav>

    @can('settings.view')
        <div class="admin-sidebar-footer">
            <div class="text-uppercase px-3 mb-1 sidebar-heading">System</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                       href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear"></i><span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}"
                       href="{{ route('admin.gallery.index') }}">
                        <i class="bi bi-images"></i><span>Gallery</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"
                       href="{{ route('admin.contact-messages.index') }}">
                        <i class="bi bi-envelope"></i><span>Contact Messages</span>
                    </a>
                </li>
            </ul>
        </div>
    @endcan
</aside>
