<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMemberRequest;
use App\Http\Requests\Admin\UpdateMemberRequest;
use App\Models\Bill;
use App\Models\Locker;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\PaymentAccount;
use App\Models\PersonalTrainingPackage;
use App\Models\User;
use App\Services\AdmissionIdGenerator;
use App\Services\MembershipCycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $members = Member::query()
            ->with('membershipPlan')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_id', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // Total Due = sum of balanceDue() across every bill a member has —
        // computed once here for whichever members are on this page, rather
        // than per-row in the view, to avoid an N+1 query per member.
        $memberIds = collect($members->items())->pluck('id');
        $totalDueByMember = Bill::whereIn('member_id', $memberIds)
            ->with('payments')
            ->get()
            ->groupBy('member_id')
            ->map(fn ($bills) => $bills->sum(fn (Bill $bill) => $bill->balanceDue()));

        if ($request->ajax()) {
            return view('admin.members.partials._members-table', compact('members', 'perPage', 'totalDueByMember'));
        }

        return view('admin.members.index', compact('members', 'perPage', 'totalDueByMember'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $plans = MembershipPlan::where('is_active', true)->get();
        $suggestedAdmissionId = AdmissionIdGenerator::generate();

        return view('admin.members.create', compact('plans', 'suggestedAdmissionId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();
        $data['discount_amount'] = $data['discount_amount'] ?? 0;

        $member = new Member($data);
        $member->registration_type = 'admin';
        $member->registered_by = $request->user()->id;
        $member->status = 'pending';

        if ($request->hasFile('nid_image')) {
            $member->nid_image_path = $request->file('nid_image')->store('members/nid', 'public');
        }

        if ($request->hasFile('photo')) {
            $member->photo_path = $request->file('photo')->store('members/photos', 'public');
        }

        $member->save();

        return redirect()->route('admin.members.show', $member)
            ->with('status', "Member {$member->admission_id} registered successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $member->load([
            'membershipPlan',
            'registeredBy',
            'payments' => fn ($q) => $q->with('paymentAccount')->latest('id')->limit(10),
            'memberTrainingPackages' => fn ($q) => $q->with(['package', 'trainer'])->latest('id')->limit(20),
        ]);

        return view('admin.members.show', compact('member'));
    }

    /**
     * Printable membership application form — a standalone page (not the
     * admin layout) opened in a new tab and printed via the browser, same
     * pattern as the Bill view/print modal.
     */
    public function print(Member $member)
    {
        $member->load(['membershipPlan', 'registeredBy']);

        return view('admin.members.print', compact('member'));
    }

    /**
     * Modal panel: record-payment form.
     */
    public function payDuePanel(Member $member)
    {
        $paymentAccounts = PaymentAccount::where('is_active', true)->get();
        $bills = $member->bills()->with('payments')->oldest('id')->get();

        $totalBilled = 0;
        $totalDiscount = 0;
        $totalPaid = 0;
        $totalDue = 0;
        $targetBill = null;

        foreach ($bills as $bill) {
            $totalBilled += $bill->admission_fee_amount + $bill->monthly_amount;
            $totalDiscount += $bill->discount_amount;
            $totalPaid += $bill->paidAmount();
            $totalDue += $bill->balanceDue();

            if (! $targetBill && $bill->statusLabel() !== 'paid') {
                $targetBill = $bill;
            }
        }

        return view('admin.members.partials.pay-due', compact(
            'member', 'paymentAccounts', 'totalBilled', 'totalDiscount', 'totalPaid', 'totalDue', 'targetBill'
        ));
    }

    /**
     * Modal panel: payment history + refund. AJAX-searchable and paginated —
     * the initial open (via the Action dropdown) renders the full panel with
     * the search box; subsequent search/page requests from within the modal
     * return just the results fragment so the search input never loses focus.
     */
    public function paymentsPanel(Request $request, Member $member)
    {
        $payments = $member->payments()
            ->with('paymentAccount')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('method', 'like', "%{$search}%")
                        ->orWhereHas('paymentAccount', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->has('page') || $request->has('search')) {
            return view('admin.members.partials._payments-results', compact('payments'));
        }

        return view('admin.members.partials.payments-searchable', compact('member', 'payments'));
    }

    /**
     * Modal panel: recent attendance. Same AJAX-searchable/paginated pattern
     * as paymentsPanel() above.
     */
    public function attendancePanel(Request $request, Member $member)
    {
        $attendances = $member->attendances()
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('source', 'like', '%'.$request->string('search').'%');
            })
            ->latest('check_in')
            ->paginate(10)
            ->withQueryString();

        if ($request->has('page') || $request->has('search')) {
            return view('admin.members.partials._attendance-results', compact('attendances'));
        }

        return view('admin.members.partials.attendance', compact('member', 'attendances'));
    }

    /**
     * Modal panel: training packages + assign form.
     */
    public function trainingPanel(Member $member)
    {
        $member->load(['memberTrainingPackages.package', 'memberTrainingPackages.trainer']);
        $trainingPackages = PersonalTrainingPackage::where('is_active', true)->get();
        $trainers = User::role('Trainer')->get();

        return view('admin.members.partials.training', compact('member', 'trainingPackages', 'trainers'));
    }

    /**
     * Modal panel: the member's assigned locker(s) (usually 0 or 1) plus,
     * when they have none, a small inline form to assign one of the
     * currently-available lockers.
     */
    public function lockerPanel(Member $member)
    {
        $member->load('lockers');
        $availableLockers = Locker::whereNull('member_id')->orderBy('locker_number')->get();

        return view('admin.members.partials.locker', compact('member', 'availableLockers'));
    }

    /**
     * Assign one of the currently-available lockers to this member — the
     * inverse direction of LockerController::assign() (which starts from a
     * specific locker and picks a member); this starts from the member and
     * picks a locker, for the inline form on the Locker modal panel.
     */
    public function assignLocker(Request $request, Member $member)
    {
        $data = $request->validate(['locker_id' => ['required', 'exists:lockers,id']]);

        $locker = Locker::whereNull('member_id')->findOrFail($data['locker_id']);
        $locker->update(['member_id' => $member->id, 'assigned_at' => now(), 'rent_due_date' => null]);

        return redirect()->route('admin.members.show', $member)
            ->with('status', "Locker #{$locker->locker_number} assigned.");
    }

    /**
     * Modal panel: ZKTeco sync log.
     */
    public function zkTecoPanel(Member $member)
    {
        $member->load(['zkTecoSyncLogs' => fn ($q) => $q->latest()]);

        return view('admin.members.partials.zkteco', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $plans = MembershipPlan::where('is_active', true)->get();

        return view('admin.members.edit', compact('member', 'plans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();
        $data['discount_amount'] = $data['discount_amount'] ?? 0;

        $member->fill($data);

        if ($request->hasFile('nid_image')) {
            if ($member->nid_image_path) {
                Storage::disk('public')->delete($member->nid_image_path);
            }
            $member->nid_image_path = $request->file('nid_image')->store('members/nid', 'public');
        }

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $member->photo_path = $request->file('photo')->store('members/photos', 'public');
        }

        $member->save();

        return redirect()->route('admin.members.show', $member)
            ->with('status', 'Member profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        if ($member->status !== 'pending') {
            return back()->withErrors(['member' => 'Only pending registrations can be deleted. Use Close for active/expired members.']);
        }

        $member->delete();

        return redirect()->route('admin.members.index')->with('status', 'Member registration deleted.');
    }

    /**
     * Manually close a membership (business rule: 3 months unpaid, or admin discretion).
     */
    public function close(Member $member)
    {
        $member->status = 'closed';
        $member->closed_at = now();
        $member->save();

        return back()->with('status', "Member {$member->admission_id} membership closed.");
    }

    /**
     * Manually reactivate an Expired or Closed member (admin discretion —
     * e.g. a courtesy extension, or correcting a mistake — separate from
     * the normal "renew by paying a bill" flow). Extends due_date the same
     * way a real renewal payment would (one cycle on the member's own
     * plan, or a flat month if they have none/a lifetime plan) rather than
     * just flipping status, so they aren't immediately re-expired by
     * tomorrow's status sync. Routes through the normal save(), so
     * MemberObserver's existing active-transition handling (ZKTeco
     * re-enrollment) applies exactly as it already does for a renewal.
     */
    public function activate(Member $member)
    {
        $plan = $member->membershipPlan;
        $anchor = $member->due_date ?? now();

        $member->due_date = $plan
            ? MembershipCycle::extend($anchor, $plan, 1)
            : MembershipCycle::extendByMonths($anchor, 1);
        $member->status = 'active';
        $member->closed_at = null;
        $member->save();

        return back()->with('status', "Member {$member->admission_id} reactivated.");
    }
}
