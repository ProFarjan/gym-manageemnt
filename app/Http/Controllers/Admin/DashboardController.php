<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'expired_members' => Member::where('status', 'expired')->count(),
            'closed_members' => Member::where('status', 'closed')->count(),
            'today_attendance' => Attendance::whereDate('check_in', today())->count(),
            'today_collection' => Payment::where('status', 'completed')->whereDate('created_at', today())->sum('amount'),
            'monthly_collection' => Payment::where('status', 'completed')
                ->whereBetween('created_at', [today()->startOfMonth(), today()->endOfMonth()])
                ->sum('amount'),
            'upcoming_renewals' => Member::where('status', 'active')
                ->whereBetween('due_date', [today(), today()->addDays(7)])
                ->count(),
        ];

        $todayAttendance = Attendance::with('member')
            ->whereDate('check_in', today())
            ->orderByDesc('check_in')
            ->get();

        $revenueChart = $this->revenueChartData();
        $membershipChart = [
            'labels' => ['Active', 'Expired', 'Closed', 'Pending'],
            'data' => [
                $stats['active_members'],
                $stats['expired_members'],
                $stats['closed_members'],
                Member::where('status', 'pending')->count(),
            ],
        ];

        return view('admin.dashboard', compact('stats', 'revenueChart', 'membershipChart', 'todayAttendance'));
    }

    private function revenueChartData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = today()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = (float) Payment::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
        }

        return compact('labels', 'data');
    }
}
