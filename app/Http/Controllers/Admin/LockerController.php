<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Locker;
use App\Models\Member;
use App\Models\PaymentAccount;
use App\Services\BillNumberGenerator;
use App\Services\PaymentRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LockerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $lockers = Locker::query()
            ->with('member')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('locker_number', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                            ->orWhere('admission_id', 'like', "%{$search}%");
                    });
            })
            ->orderBy('locker_number')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.lockers.partials._lockers-table', compact('lockers'));
        }

        return view('admin.lockers.index', compact('lockers', 'perPage'));
    }

    public function create()
    {
        return view('admin.lockers.create');
    }

    public function store(Request $request)
    {
        Locker::create($this->validateData($request));

        return redirect()->route('admin.lockers.index')->with('status', 'Locker added.');
    }

    public function edit(Locker $locker)
    {
        return view('admin.lockers.edit', compact('locker'));
    }

    public function update(Request $request, Locker $locker)
    {
        $locker->update($this->validateData($request, $locker));

        return redirect()->route('admin.lockers.index')->with('status', 'Locker updated.');
    }

    public function destroy(Locker $locker)
    {
        if ($locker->isAssigned()) {
            return back()->withErrors(['locker' => 'Cannot delete a locker that is currently assigned. Unassign it first.']);
        }

        $locker->delete();

        return redirect()->route('admin.lockers.index')->with('status', 'Locker deleted.');
    }

    public function assignPanel(Locker $locker)
    {
        $members = Member::orderBy('full_name')->get(['id', 'full_name', 'admission_id']);

        return view('admin.lockers.partials.assign', compact('locker', 'members'));
    }

    public function assign(Request $request, Locker $locker)
    {
        $data = $request->validate(['member_id' => ['required', 'exists:members,id']]);

        $locker->update([
            'member_id' => $data['member_id'],
            'assigned_at' => now(),
            'rent_due_date' => null,
        ]);

        return redirect()->route('admin.lockers.index')->with('status', "Locker #{$locker->locker_number} assigned.");
    }

    public function unassign(Locker $locker)
    {
        $locker->update([
            'member_id' => null,
            'assigned_at' => null,
            'rent_due_date' => null,
        ]);

        return redirect()->route('admin.lockers.index')->with('status', "Locker #{$locker->locker_number} unassigned.");
    }

    public function billPanel(Locker $locker)
    {
        $locker->load('member');
        $paymentAccounts = PaymentAccount::where('is_active', true)->get();

        return view('admin.lockers.partials.bill', compact('locker', 'paymentAccounts'));
    }

    /**
     * Generate a locker rent Bill for the given number of months at the
     * fixed rate in Settings, mirroring BillController::store()'s shape
     * (custom line item + duration_months + an optional bundled payment,
     * all in one transaction) but scoped to a single locker.
     */
    public function generateBill(Request $request, Locker $locker)
    {
        abort_unless($locker->isAssigned(), 422, 'This locker has no member assigned.');

        $data = $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:36'],
            'payment_account_id' => ['nullable', 'required_with:amount', 'exists:payment_accounts,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $rate = (float) setting('locker_monthly_price', 0);
        $months = (int) $data['months'];
        $total = round($rate * $months, 2);

        if (! empty($data['amount']) && $data['amount'] > $total + 0.01) {
            return back()->withErrors([
                'amount' => "Payment amount ({$data['amount']}) exceeds the rent total ({$total}).",
            ])->withInput();
        }

        $bill = DB::transaction(function () use ($locker, $months, $total, $data, $request) {
            $bill = Bill::create([
                'member_id' => $locker->member_id,
                'locker_id' => $locker->id,
                'bill_number' => BillNumberGenerator::generate(),
                'admission_fee_amount' => 0,
                'monthly_amount' => 0,
                'amount' => $total,
                'discount_amount' => 0,
                'due_date' => now(),
                'duration_months' => $months,
            ]);

            $bill->items()->create([
                'particular' => "Locker Rent — #{$locker->locker_number} ({$months} month(s))",
                'qty' => 1,
                'unit_price' => $total,
                'total' => $total,
            ]);

            if (! empty($data['amount']) && $data['amount'] > 0) {
                PaymentRecorder::record($locker->member, [
                    'type' => 'locker',
                    'method' => 'manual',
                    'bill_id' => $bill->id,
                    'payment_account_id' => $data['payment_account_id'],
                    'amount' => $data['amount'],
                    'notes' => $data['payment_notes'] ?? null,
                    'created_by' => $request->user()->id,
                ]);

                PaymentRecorder::applyLockerBillDurationIfJustCompleted($bill);
            }

            return $bill;
        });

        return redirect()->route('admin.bills.index')
            ->with('status', "Locker rent bill {$bill->bill_number} created for #{$locker->locker_number}.");
    }

    /**
     * @return array{locker_number: string, location: ?string, notes: ?string}
     */
    private function validateData(Request $request, ?Locker $locker = null): array
    {
        return $request->validate([
            'locker_number' => ['required', 'string', 'max:50', 'unique:lockers,locker_number,'.($locker?->id ?? 'NULL').',id'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
