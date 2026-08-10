<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GenericExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\PaymentAccount;
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
        $searched = $request->boolean('search') || $request->filled('format');

        $status = $request->query('status');
        $planId = $request->query('plan_id');

        // Due date is an optional, additive filter here (unlike Admissions,
        // where the date range is the report's whole point) — plenty of
        // members (e.g. Pending, never activated) have no due_date at all,
        // so silently defaulting to "this month" would hide them the moment
        // the page loads. Only touch due_date filtering once the admin
        // actually fills in one of the two date boxes.
        $hasDueDateFilter = $request->filled('from') || $request->filled('to');
        [$from, $to] = $this->dateRange($request);

        $rows = collect();

        if ($searched) {
            $rows = Member::with('membershipPlan')
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($planId, fn ($q) => $q->where('membership_plan_id', $planId))
                ->when($hasDueDateFilter, fn ($q) => $q->whereNotNull('due_date')->whereBetween('due_date', [$from, $to]))
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
        }

        return $this->respond($request, 'Member Report', ['Admission ID', 'Name', 'Mobile', 'Email', 'Plan', 'Status', 'Due Date'], $rows, false, 'admin.reports.members', [
            'searched' => $searched,
            'filters' => [
                'plan_id' => $planId,
                'status' => $status,
                'from' => $hasDueDateFilter ? $from->format('Y-m-d') : '',
                'to' => $hasDueDateFilter ? $to->format('Y-m-d') : '',
            ],
            'plans' => MembershipPlan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function attendance(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $member = $request->query('member');
        $source = $request->query('source');

        $rows = collect();

        if ($searched) {
            $rows = Attendance::with('member')
                ->whereBetween('check_in', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->when($member, fn ($q) => $q->whereHas('member', function ($q) use ($member) {
                    $q->where('full_name', 'like', "%{$member}%")->orWhere('admission_id', 'like', "%{$member}%");
                }))
                ->when($source, fn ($q) => $q->where('source', $source))
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
        }

        return $this->respond($request, 'Attendance Report', ['Date', 'Member', 'Check In', 'Check Out', 'Duration', 'Source'], $rows, true, 'admin.reports.attendance', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'member' => $member,
                'source' => $source,
            ],
        ]);
    }

    public function payments(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $accountId = $request->query('payment_account_id');
        $type = $request->query('type');

        $rows = collect();

        if ($searched) {
            $rows = Payment::with('member', 'paymentAccount')
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->when($accountId, fn ($q) => $q->where('payment_account_id', $accountId))
                ->when($type, fn ($q) => $q->where('type', $type))
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
        }

        return $this->respond($request, 'Payment Report', ['Date', 'Member', 'Type', 'Amount', 'Discount', 'Method', 'Account', 'Status', 'Receipt No'], $rows, true, 'admin.reports.payments', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'payment_account_id' => $accountId,
                'type' => $type,
            ],
            'accounts' => PaymentAccount::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function due(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        $status = $request->query('status');
        $planId = $request->query('plan_id');

        // With no explicit due-date range typed in, preserve this report's
        // original purpose — "who's due soon" — rather than requiring the
        // admin to always fill in a range just to see the default view.
        $hasDueDateFilter = $request->filled('from') || $request->filled('to');
        [$from, $to] = $this->dateRange($request);

        $rows = collect();

        if ($searched) {
            $rows = Member::with('membershipPlan')
                ->whereIn('status', $status ? [$status] : ['active', 'expired'])
                ->when($planId, fn ($q) => $q->where('membership_plan_id', $planId))
                ->whereNotNull('due_date')
                ->when(
                    $hasDueDateFilter,
                    fn ($q) => $q->whereBetween('due_date', [$from, $to]),
                    fn ($q) => $q->where('due_date', '<=', today()->addDays(7))
                )
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
        }

        return $this->respond($request, 'Due Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Status', 'Due Date', 'Overdue/Remaining'], $rows, false, 'admin.reports.due', [
            'searched' => $searched,
            'filters' => [
                'plan_id' => $planId,
                'status' => $status,
                'from' => $hasDueDateFilter ? $from->format('Y-m-d') : '',
                'to' => $hasDueDateFilter ? $to->format('Y-m-d') : '',
            ],
            'plans' => MembershipPlan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function expiredMembers(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        $planId = $request->query('plan_id');

        // Additive/optional, like Members — leaving both blank means "show
        // every expired member" (this report's original, unfiltered
        // behavior), not some arbitrary default window.
        $hasDueDateFilter = $request->filled('from') || $request->filled('to');
        [$from, $to] = $this->dateRange($request);

        $rows = collect();

        if ($searched) {
            $rows = Member::with('membershipPlan')
                ->where('status', 'expired')
                ->when($planId, fn ($q) => $q->where('membership_plan_id', $planId))
                ->when($hasDueDateFilter, fn ($q) => $q->whereNotNull('due_date')->whereBetween('due_date', [$from, $to]))
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
        }

        return $this->respond($request, 'Expired Member Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Due Date', 'Expired Since'], $rows, false, 'admin.reports.expired-members', [
            'searched' => $searched,
            'filters' => [
                'plan_id' => $planId,
                'from' => $hasDueDateFilter ? $from->format('Y-m-d') : '',
                'to' => $hasDueDateFilter ? $to->format('Y-m-d') : '',
            ],
            'plans' => MembershipPlan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function closedMembers(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        $planId = $request->query('plan_id');

        // Additive/optional, like Expired Members — leaving both blank means
        // "show every closed member" rather than some arbitrary default window.
        $hasClosedDateFilter = $request->filled('from') || $request->filled('to');
        [$from, $to] = $this->dateRange($request);

        $rows = collect();

        if ($searched) {
            $rows = Member::with('membershipPlan')
                ->where('status', 'closed')
                ->when($planId, fn ($q) => $q->where('membership_plan_id', $planId))
                ->when($hasClosedDateFilter, fn ($q) => $q->whereBetween('closed_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]))
                ->orderByDesc('closed_at')
                ->get()
                ->map(fn (Member $m) => [
                    $m->admission_id,
                    $m->full_name,
                    $m->mobile_number,
                    $m->membershipPlan?->name ?? '—',
                    $m->closed_at?->format('d M Y') ?? '—',
                ]);
        }

        return $this->respond($request, 'Closed Member Report', ['Admission ID', 'Name', 'Mobile', 'Plan', 'Closed At'], $rows, false, 'admin.reports.closed-members', [
            'searched' => $searched,
            'filters' => [
                'plan_id' => $planId,
                'from' => $hasClosedDateFilter ? $from->format('Y-m-d') : '',
                'to' => $hasClosedDateFilter ? $to->format('Y-m-d') : '',
            ],
            'plans' => MembershipPlan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function collection(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $accountId = $request->query('payment_account_id');
        $type = $request->query('type');

        $rows = collect();

        if ($searched) {
            $payments = Payment::with('paymentAccount')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->when($accountId, fn ($q) => $q->where('payment_account_id', $accountId))
                ->when($type, fn ($q) => $q->where('type', $type))
                ->orderBy('created_at')
                ->get();

            $rows = $payments->map(fn (Payment $p) => [
                $p->created_at->format('d M Y'),
                $p->paymentAccount->name,
                ucfirst(str_replace('_', ' ', $p->type)),
                number_format($p->amount - $p->discount_amount, 2),
            ]);

            $rows->push(['', '', 'TOTAL', number_format($payments->sum(fn (Payment $p) => $p->amount - $p->discount_amount), 2)]);
        }

        return $this->respond($request, 'Collection Report', ['Date', 'Account', 'Type', 'Net Amount'], $rows, true, 'admin.reports.collection', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'payment_account_id' => $accountId,
                'type' => $type,
            ],
            'accounts' => PaymentAccount::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function expenses(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $category = $request->query('category');
        $accountId = $request->query('payment_account_id');

        $rows = collect();

        if ($searched) {
            $rows = Expense::with('paymentAccount', 'createdBy')
                ->whereBetween('expense_date', [$from, $to])
                ->when($category, fn ($q) => $q->where('category', $category))
                ->when($accountId, fn ($q) => $q->where('payment_account_id', $accountId))
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
        }

        return $this->respond($request, 'Expense Report', ['Date', 'Description', 'Category', 'Amount', 'Account', 'Recorded By'], $rows, true, 'admin.reports.expenses', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'category' => $category,
                'payment_account_id' => $accountId,
            ],
            'categories' => Expense::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'accounts' => PaymentAccount::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function offers(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        $status = $request->query('status');
        $discountType = $request->query('discount_type');

        // Optional/additive, like the other non-primary date filters — blank
        // means "show every offer" rather than defaulting to some window.
        $hasDateFilter = $request->filled('from') || $request->filled('to');
        [$from, $to] = $this->dateRange($request);

        $rows = collect();

        if ($searched) {
            $rows = Offer::when($discountType, fn ($q) => $q->where('discount_type', $discountType))
                ->when($hasDateFilter, fn ($q) => $q->whereBetween('start_date', [$from, $to]))
                ->orderByDesc('start_date')
                ->get()
                ->map(fn (Offer $o) => [
                    'name' => $o->name,
                    'label' => $o->isCurrentlyRunning() ? 'Running' : ($o->is_active ? 'Scheduled/Expired' : 'Inactive'),
                    'start' => $o->start_date->format('d M Y'),
                    'end' => $o->end_date->format('d M Y'),
                    'discount' => $o->discount_type === 'percentage' ? "{$o->discount_amount}%" : number_format($o->discount_amount, 2).' BDT',
                ])
                ->when($status, fn ($rows) => $rows->where('label', $status))
                ->map(fn ($r) => [$r['name'], $r['start'], $r['end'], $r['discount'], $r['label']])
                ->values();
        }

        return $this->respond($request, 'Offer Report', ['Name', 'Start Date', 'End Date', 'Discount', 'Status'], $rows, false, 'admin.reports.offers', [
            'searched' => $searched,
            'filters' => [
                'status' => $status,
                'discount_type' => $discountType,
                'from' => $hasDateFilter ? $from->format('Y-m-d') : '',
                'to' => $hasDateFilter ? $to->format('Y-m-d') : '',
            ],
        ]);
    }

    public function discounts(Request $request)
    {
        $searched = $request->boolean('search') || $request->filled('format');

        [$from, $to] = $this->dateRange($request);
        $member = $request->query('member');
        $appliedBy = $request->query('applied_by');

        $rows = collect();

        if ($searched) {
            $rows = Payment::with('member', 'createdBy')
                ->where('discount_amount', '>', 0)
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->when($member, fn ($q) => $q->whereHas('member', function ($q) use ($member) {
                    $q->where('full_name', 'like', "%{$member}%")->orWhere('admission_id', 'like', "%{$member}%");
                }))
                ->when($appliedBy, fn ($q) => $q->where('created_by', $appliedBy))
                ->orderBy('created_at')
                ->get()
                ->map(fn (Payment $p) => [
                    $p->created_at->format('d M Y'),
                    $p->member->full_name,
                    number_format($p->discount_amount, 2),
                    $p->discount_reason ?? '—',
                    $p->createdBy?->name ?? '—',
                ]);
        }

        return $this->respond($request, 'Discount Report', ['Date', 'Member', 'Discount Amount', 'Reason', 'Applied By'], $rows, true, 'admin.reports.discounts', [
            'searched' => $searched,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
                'member' => $member,
                'applied_by' => $appliedBy,
            ],
            'staffUsers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->query('from')) : today()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->query('to')) : today()->endOfMonth();

        // Admissions labels its boxes "To Date" (left) / "From Date" (right)
        // per an explicit layout request, which reads confusingly against
        // the from/to field names underneath — auto-correcting order here
        // means whichever box actually holds the earlier date, the range is
        // always valid instead of silently returning zero rows when it's
        // typed "backwards" relative to the field names.
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

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
