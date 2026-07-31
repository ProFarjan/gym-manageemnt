<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\PaymentAccount;
use App\Services\PaymentRecorder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        $payment = PaymentRecorder::record($bill->member, $data);

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
