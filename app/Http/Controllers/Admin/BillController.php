<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Member;
use App\Models\PaymentAccount;
use App\Services\BillNumberGenerator;
use App\Services\PaymentRecorder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $query = Bill::query()
            ->with(['member', 'membershipPlan', 'payments'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($q) use ($search) {
                    $q->where('bill_number', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($q) use ($search) {
                            $q->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_id', 'like', "%{$search}%")
                                ->orWhere('mobile_number', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id');

        // Status is derived from linked payments, not a column, so it can't be
        // filtered at the DB level — only pay the full-fetch cost when a status
        // filter is actually requested; the common case stays a normal paginate().
        if ($request->filled('status')) {
            $status = $request->string('status')->value();
            $page = $request->integer('page', 1);
            $filtered = $query->get()->filter(fn (Bill $bill) => $bill->statusLabel() === $status)->values();

            $bills = new LengthAwarePaginator(
                $filtered->forPage($page, $perPage),
                $filtered->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $bills = $query->paginate($perPage)->withQueryString();
        }

        if ($request->ajax()) {
            return view('admin.bills.partials._bills-table', compact('bills'));
        }

        return view('admin.bills.index', compact('bills', 'perPage'));
    }

    public function createPanel()
    {
        $paymentAccounts = PaymentAccount::where('is_active', true)->get();
        $invoiceNumberPreview = BillNumberGenerator::generate();

        return view('admin.bills.partials.create', compact('paymentAccounts', 'invoiceNumberPreview'));
    }

    /**
     * Select2 AJAX data source for the Create Bill member picker — member
     * counts will grow past what's reasonable to embed client-side, unlike
     * the small, mostly-static payment-accounts list.
     */
    public function membersSearch(Request $request)
    {
        $search = $request->string('q')->value();

        $members = Member::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('admission_id', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            })
            ->orderBy('full_name')
            ->paginate(20, ['id', 'full_name', 'admission_id', 'mobile_number'], 'page', $request->integer('page', 1));

        return response()->json([
            'results' => $members->getCollection()->map(fn (Member $m) => [
                'id' => $m->id,
                'text' => "{$m->full_name} — {$m->mobile_number} ({$m->admission_id})",
            ]),
            'pagination' => ['more' => $members->hasMorePages()],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'due_date' => ['nullable', 'date'],
            'duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'particulars' => ['required', 'array', 'min:1'],
            'particulars.*' => ['required', 'string', 'max:255'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_account_id' => ['nullable', 'required_with:amount', 'exists:payment_accounts,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $member = Member::findOrFail($data['member_id']);

        $rows = collect($data['particulars'])->map(fn ($particular, $i) => [
            'particular' => $particular,
            'qty' => (float) $data['qty'][$i],
            'unit_price' => (float) $data['unit_price'][$i],
            'total' => round((float) $data['qty'][$i] * (float) $data['unit_price'][$i], 2),
        ]);

        $subtotal = $rows->sum('total');
        $discountAmount = $data['discount_amount'] ?? 0;
        $grandTotal = max(0, $subtotal - $discountAmount);

        // Validate any bundled payment against the computed total up front,
        // before writing anything — a rejected payment shouldn't still leave
        // a half-created bill behind.
        if (! empty($data['amount']) && $data['amount'] > $grandTotal + 0.01) {
            return back()->withErrors([
                'amount' => "Payment amount ({$data['amount']}) exceeds the bill's Grand Total ({$grandTotal}).",
            ])->withInput();
        }

        $bill = DB::transaction(function () use ($data, $member, $rows, $discountAmount, $grandTotal, $request) {
            $bill = Bill::create([
                'member_id' => $member->id,
                'bill_number' => BillNumberGenerator::generate(),
                'admission_fee_amount' => 0,
                'monthly_amount' => 0,
                'amount' => $grandTotal,
                'discount_amount' => $discountAmount,
                'due_date' => $data['due_date'] ?? now(),
                'duration_months' => $data['duration_months'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($rows as $row) {
                $bill->items()->create($row);
            }

            if (! empty($data['amount']) && $data['amount'] > 0) {
                $memberWasPending = $member->status === 'pending';

                PaymentRecorder::record($member, [
                    'type' => 'admission',
                    'method' => 'manual',
                    'bill_id' => $bill->id,
                    'payment_account_id' => $data['payment_account_id'],
                    'amount' => $data['amount'],
                    'notes' => $data['payment_notes'] ?? null,
                    'created_by' => $request->user()->id,
                ]);

                PaymentRecorder::applyBillDurationIfJustCompleted($bill, $memberWasPending);
            }

            return $bill;
        });

        return redirect()->route('admin.bills.index')
            ->with('status', "Bill {$bill->bill_number} created.");
    }

    public function viewPanel(Bill $bill)
    {
        $bill->load(['member', 'membershipPlan', 'payments.paymentAccount']);

        return view('admin.bills.partials.view', compact('bill'));
    }

    public function payPanel(Bill $bill)
    {
        $bill->load(['member', 'payments']);
        $paymentAccounts = PaymentAccount::where('is_active', true)->get();

        return view('admin.bills.partials.pay', compact('bill', 'paymentAccounts'));
    }

    public function pay(Request $request, Bill $bill)
    {
        $bill->load('payments');

        $data = $request->validate([
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['required_with:discount_amount', 'nullable', 'string', 'max:255'],
        ]);

        // Amount + discount together must not resolve more than what's actually
        // owed — this is exactly the mistake that produced a "paid" total higher
        // than the bill itself (e.g. forgetting to reduce a pre-filled Amount
        // before adding a discount on top of it).
        $settled = $data['amount'] + ($data['discount_amount'] ?? 0);
        $balance = $bill->balanceDue();

        if ($settled > $balance + 0.01) {
            return back()->withErrors([
                'amount' => "Amount + Discount ({$settled}) exceeds the balance due ({$balance}). Reduce the amount or discount so they add up to at most the balance due.",
            ])->withInput();
        }

        $data['type'] = 'admission';
        $data['method'] = 'manual';
        $data['bill_id'] = $bill->id;
        $data['created_by'] = $request->user()->id;

        $memberWasPending = $bill->member->status === 'pending';
        $payment = PaymentRecorder::record($bill->member, $data);
        PaymentRecorder::applyBillDurationIfJustCompleted($bill, $memberWasPending);

        return redirect()->route('admin.bills.index')
            ->with('status', "Payment recorded against {$bill->bill_number} ({$payment->receipt_number}).");
    }

    public function destroy(Bill $bill)
    {
        $bill->load('payments');

        if ($bill->paidAmount() > 0) {
            return back()->withErrors(['bill' => 'Only unpaid bills can be deleted.']);
        }

        $bill->delete();

        return redirect()->route('admin.bills.index')->with('status', "Bill {$bill->bill_number} deleted.");
    }
}
