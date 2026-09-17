@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-4">Welcome, {{ auth()->user()->name }}</h1>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Total Members', 'value' => number_format($stats['total_members']), 'icon' => 'people-fill', 'accent' => 'primary'],
            ['label' => 'Active Members', 'value' => number_format($stats['active_members']), 'icon' => 'person-check-fill', 'accent' => 'success'],
            ['label' => 'Expired Members', 'value' => number_format($stats['expired_members']), 'icon' => 'person-x-fill', 'accent' => 'secondary'],
            ['label' => 'Closed Members', 'value' => number_format($stats['closed_members']), 'icon' => 'person-dash-fill', 'accent' => 'dark'],
            ['label' => "Today's Attendance", 'value' => number_format($stats['today_attendance']), 'icon' => 'calendar-check-fill', 'accent' => 'info'],
            ['label' => "Today's Collection", 'value' => number_format($stats['today_collection'], 2), 'icon' => 'cash-coin', 'accent' => 'primary'],
            ['label' => 'Monthly Collection', 'value' => number_format($stats['monthly_collection'], 2), 'icon' => 'graph-up-arrow', 'accent' => 'success'],
            ['label' => 'Upcoming Renewals (7d)', 'value' => number_format($stats['upcoming_renewals']), 'icon' => 'bell-fill', 'accent' => 'warning'],
        ] as $stat)
            <div class="col-6 col-md-3">
                <div class="card stat-card stat-card--{{ $stat['accent'] }}">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-card-icon"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                        <div>
                            <p class="stat-card-label mb-1">{{ $stat['label'] }}</p>
                            <p class="stat-card-value mb-0">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">Revenue (Last 6 Months)</div>
                <div class="card-body">
                    <div style="height: 280px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">Membership Breakdown</div>
                <div class="card-body">
                    <div style="height: 280px;">
                        <canvas id="membershipChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">Today's Attendance</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Admission ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Duration</th>
                        <th>Source</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($todayAttendance as $attendance)
                        <tr>
                            <td>{{ $attendance->member->admission_id }}</td>
                            <td>{{ $attendance->member->full_name }}</td>
                            <td>{{ $attendance->member->mobile_number }}</td>
                            <td>{{ $attendance->check_in->format('h:i A') }}</td>
                            <td>
                                @if ($attendance->check_out)
                                    {{ $attendance->check_out->format('h:i A') }}
                                @else
                                    <span class="badge bg-success">Still In</span>
                                @endif
                            </td>
                            <td>
                                @if ($attendance->duration_minutes)
                                    {{ $attendance->duration_minutes }} min
                                @else
                                    {{ $attendance->check_in->diffInMinutes(now()) }} min <span class="text-muted small">(ongoing)</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($attendance->source) }}</td>
                            <td class="text-end">
                                @can('attendance.create')
                                    @unless ($attendance->check_out)
                                        <form method="POST" action="{{ route('admin.members.attendance.check-out', $attendance->member) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Check Out</button>
                                        </form>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No attendance recorded today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: @json($revenueChart['labels']),
                    datasets: [{
                        label: 'Revenue (BDT)',
                        data: @json($revenueChart['data']),
                        borderColor: '#d6336c',
                        backgroundColor: 'rgba(214, 51, 108, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
            });

            new Chart(document.getElementById('membershipChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($membershipChart['labels']),
                    datasets: [{
                        data: @json($membershipChart['data']),
                        backgroundColor: ['#198754', '#6c757d', '#212529', '#ffc107'],
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        });
    </script>
@endpush
