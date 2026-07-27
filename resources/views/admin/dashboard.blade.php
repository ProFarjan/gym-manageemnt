@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h4 mb-4">Welcome, {{ auth()->user()->name }}</h1>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Members</p>
                    <p class="h4 mb-0">{{ number_format($stats['total_members']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Active Members</p>
                    <p class="h4 mb-0 text-success">{{ number_format($stats['active_members']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Expired Members</p>
                    <p class="h4 mb-0 text-secondary">{{ number_format($stats['expired_members']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Closed Members</p>
                    <p class="h4 mb-0 text-dark">{{ number_format($stats['closed_members']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Today's Attendance</p>
                    <p class="h4 mb-0">{{ number_format($stats['today_attendance']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Today's Collection</p>
                    <p class="h4 mb-0">{{ number_format($stats['today_collection'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Monthly Collection</p>
                    <p class="h4 mb-0">{{ number_format($stats['monthly_collection'], 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted small mb-1">Upcoming Renewals (7d)</p>
                    <p class="h4 mb-0 text-warning">{{ number_format($stats['upcoming_renewals']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Revenue (Last 6 Months)</div>
                <div class="card-body">
                    <canvas id="revenueChart" height="90"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Membership Breakdown</div>
                <div class="card-body">
                    <canvas id="membershipChart" height="90"></canvas>
                </div>
            </div>
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
                options: { responsive: true, plugins: { legend: { display: false } } },
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
                options: { responsive: true },
            });
        });
    </script>
@endpush
