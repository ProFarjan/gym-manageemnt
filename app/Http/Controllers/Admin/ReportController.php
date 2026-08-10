<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Member;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private const REPORTS = [
        ['route' => 'admin.reports.admissions', 'label' => 'Admission Report', 'description' => 'New admissions within a date range.'],
        ['route' => 'admin.reports.members', 'label' => 'Member Report', 'description' => 'Full member list, filterable by status.'],
        ['route' => 'admin.reports.attendance', 'label' => 'Attendance Report', 'description' => 'Check-in/check-out records within a date range.'],
        ['route' => 'admin.reports.payments', 'label' => 'Payment Report', 'description' => 'All payments within a date range.'],
        ['route' => 'admin.reports.due', 'label' => 'Due Report', 'description' => 'Members overdue or due within 7 days.'],
        ['route' => 'admin.reports.expired-members', 'label' => 'Expired Member Report', 'description' => 'Members currently Expired.'],
        ['route' => 'admin.reports.closed-members', 'label' => 'Closed Member Report', 'description' => 'Members currently Closed.'],
        ['route' => 'admin.reports.collection', 'label' => 'Collection Report', 'description' => 'Completed payment collections within a date range.'],
        ['route' => 'admin.reports.expenses', 'label' => 'Expense Report', 'description' => 'Recorded expenses within a date range.'],
        ['route' => 'admin.reports.offers', 'label' => 'Offer Report', 'description' => 'All promotional offers and their status.'],
        ['route' => 'admin.reports.discounts', 'label' => 'Discount Report', 'description' => 'Payments with a manual discount applied.'],
    ];

    public function index()
    {
        return view('admin.reports.index', ['reports' => self::REPORTS]);
    }

    public function admissions(Request $request)
    {
        // The filter-only landing state (nothing searched yet) is the
        // default — a hidden "search" field on the form is what flips this
        // to true, so submitting with every field left blank still counts
        // as a real search (show everything in the default date range)
        // rather than looking like a fresh, un-searched page load.
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $registeredBy = $request->query('registered_by');
        $address = $request->query('address');

        $rows = collect();

        if ($searched) {
            $rows = Member::with(['membershipPlan', 'registeredBy'])
                ->whereNotNull('admission_date')
                ->whereBetween('admission_date', [$from, $to])
                ->when($registeredBy, fn ($q) => $q->where('registered_by', $registeredBy))
                ->when($address, fn ($q) => $q->where('address', 'like', "%{$address}%"))
                ->orderBy('admission_date')
                ->get()
                ->map(fn (Member $m) => [
                    $m->admission_id,
                    $m->full_name,
                    $m->mobile_number,
                    $m->membershipPlan?->name ?? '—',
                    $m->registeredBy?->name ?? '—',
                    $m->admission_date->format('d M Y'),
                ]);
        }

        return $this->respond($request, 'Admission Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Registered By', 'Admission Date'], $rows, true, 'admin.reports.admissions', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'registered_by' => $registeredBy,
                'address' => $address,
            ],
            'staffUsers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function members(Request $request)
    {
        $status = $request->query('status');

        $rows = Member::with('membershipPlan')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('full_name')
            ->get()
            ->map(fn (Member $m) => [
                $m->admission_id,
                $m->full_name,
                $m->mobile_number,
                $m->email ?? '—',
                $m->membershipPlan?->name ?? '—',
                ucfirst($m->status),
                $m->due_date?->format('d M Y') ?? '—',
            ]);

        return $this->respond($request, 'Member Report', ['Admission ID', 'Name', 'Mobile', 'Email', 'Plan', 'Status', 'Due Date'], $rows, false);
    }

    public function attendance(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Attendance::with('member')
            ->whereBetween('check_in', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('check_in')
            ->get()
            ->map(fn (Attendance $a) => [
                $a->check_in->format('d M Y'),
                $a->member->full_name,
                $a->check_in->format('h:i A'),
                $a->check_out?->format('h:i A') ?? 'Still In',
                $a->duration_minutes ? "{$a->duration_minutes} min" : '—',
                ucfirst($a->source),
            ]);

        return $this->respond($request, 'Attendance Report', ['Date', 'Member', 'Check In', 'Check Out', 'Duration', 'Source'], $rows, true);
    }

    public function payments(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Payment::with('member', 'paymentAccount')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Payment $p) => [
                $p->created_at->format('d M Y'),
                $p->member->full_name,
                ucfirst(str_replace('_', ' ', $p->type)),
                number_format($p->amount, 2),
                number_format($p->discount_amount, 2),
                ucfirst($p->method),
                $p->paymentAccount->name,
                ucfirst($p->status),
                $p->receipt_number,
            ]);

        return $this->respond($request, 'Payment Report', ['Date', 'Member', 'Type', 'Amount', 'Discount', 'Method', 'Account', 'Status', 'Receipt No'], $rows, true);
    }

    public function due(Request $request)
    {
        $rows = Member::with('membershipPlan')
            ->whereIn('status', ['active', 'expired'])
            ->whereNotNull('due_date')
            ->where('due_date', '<=', today()->addDays(7))
            ->orderBy('due_date')
            ->get()
            ->map(fn (Member $m) => [
                $m->admission_id,
                $m->full_name,
                $m->mobile_number,
                $m->membershipPlan?->name ?? '—',
                ucfirst($m->status),
                $m->due_date->format('d M Y'),
                $m->due_date->isPast() ? today()->diffInDays($m->due_date).' day(s) overdue' : today()->diffInDays($m->due_date).' day(s) left',
            ]);

        return $this->respond($request, 'Due Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Status', 'Due Date', 'Overdue/Remaining'], $rows, false);
    }

    public function expiredMembers(Request $request)
    {
        $rows = Member::with('membershipPlan')
            ->where('status', 'expired')
            ->orderBy('due_date')
            ->get()
            ->map(fn (Member $m) => [
                $m->admission_id,
                $m->full_name,
                $m->mobile_number,
                $m->membershipPlan?->name ?? '—',
                $m->due_date?->format('d M Y') ?? '—',
                $m->due_date ? today()->diffInDays($m->due_date).' day(s) ago' : '—',
            ]);

        return $this->respond($request, 'Expired Member Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Due Date', 'Expired Since'], $rows, false);
    }

    public function closedMembers(Request $request)
    {
        $rows = Member::with('membershipPlan')
            ->where('status', 'closed')
            ->orderByDesc('closed_at')
            ->get()
            ->map(fn (Member $m) => [
                $m->admission_id,
                $m->full_name,
                $m->mobile_number,
                $m->membershipPlan?->name ?? '—',
                $m->closed_at?->format('d M Y') ?? '—',
            ]);

        return $this->respond($request, 'Closed Member Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Closed At'], $rows, false);
    }

    public function collection(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $payments = Payment::with('paymentAccount')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('created_at')
            ->get();

        $rows = $payments->map(fn (Payment $p) => [
            $p->created_at->format('d M Y'),
            $p->paymentAccount->name,
            ucfirst(str_replace('_', ' ', $p->type)),
            number_format($p->amount - $p->discount_amount, 2),
        ]);

        $rows->push(['', '', 'TOTAL', number_format($payments->sum(fn (Payment $p) => $p->amount - $p->discount_amount), 2)]);

        return $this->respond($request, 'Collection Report', ['Date', 'Account', 'Type', 'Net Amount'], $rows, true);
    }

    public function expenses(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Expense::with('paymentAccount', 'createdBy')
            ->whereBetween('expense_date', [$from, $to])
            ->orderBy('expense_date')
            ->get()
            ->map(fn (Expense $e) => [
                $e->expense_date->format('d M Y'),
                $e->description,
                $e->category ?? '—',
                number_format($e->amount, 2),
                $e->paymentAccount->name,
                $e->createdBy?->name ?? '—',
            ]);

        return $this->respond($request, 'Expense Report', ['Date', 'Description', 'Category', 'Amount', 'Account', 'Recorded By'], $rows, true);
    }

    public function offers(Request $request)
    {
        $rows = Offer::orderByDesc('start_date')->get()->map(fn (Offer $o) => [
            $o->name,
            $o->start_date->format('d M Y'),
            $o->end_date->format('d M Y'),
            $o->discount_type === 'percentage' ? "{$o->discount_amount}%" : number_format($o->discount_amount, 2).' BDT',
            $o->isCurrentlyRunning() ? 'Running' : ($o->is_active ? 'Scheduled/Expired' : 'Inactive'),
        ]);

        return $this->respond($request, 'Offer Report', ['Name', 'Start Date', 'End Date', 'Discount', 'Status'], $rows, false);
    }

    public function discounts(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $rows = Payment::with('member', 'createdBy')
            ->where('discount_amount', '>', 0)
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Payment $p) => [
                $p->created_at->format('d M Y'),
                $p->member->full_name,
                number_format($p->discount_amount, 2),
                $p->discount_reason ?? '—',
                $p->createdBy?->name ?? '—',
            ]);

        return $this->respond($request, 'Discount Report', ['Date', 'Member', 'Discount Amount', 'Reason', 'Applied By'], $rows, true);
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->query('from')) : today()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->query('to')) : today()->endOfMonth();

        return [$from, $to];
    }

    /**
     * @param  string|null  $view  Defaults to the generic 'admin.reports.show' — pass a
     *                             dedicated view name for a report with its own filter UI
     *                             (e.g. admissions).
     * @param  array<string, mixed>  $viewData  Extra data merged in for that dedicated view.
     */
    private function respond(Request $request, string $title, array $headings, iterable $rows, bool $showDateFilter, ?string $view = null, array $viewData = [])
    {
        $rowsArray = collect($rows)->values()->all();

        if ($request->query('format') === 'xlsx') {
            return Excel::download(new GenericExport($rowsArray, $headings), Str::slug($title).'.xlsx');
        }

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('pdf.report', compact('title', 'headings', 'rowsArray'));

            return $pdf->stream(Str::slug($title).'.pdf');
        }

        if ($request->query('format') === 'print') {
            return view('admin.reports.print', compact('title', 'headings', 'rowsArray'));
        }

        return view($view ?? 'admin.reports.show', array_merge([
            'title' => $title,
            'headings' => $headings,
            'rows' => $rowsArray,
            'showDateFilter' => $showDateFilter,
        ], $viewData));
    }
}
