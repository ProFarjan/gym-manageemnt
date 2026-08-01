<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Services\PaymentRecorder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Member $member)
    {
        $data = $request->validate([
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        $bill = $member->oldestOutstandingBill();

        if (! $bill) {
            return back()->withErrors(['amount' => 'This member has no outstanding bills to pay.']);
        }

        $balance = $bill->balanceDue();

        if ($data['amount'] > $balance + 0.01) {
            return back()->withErrors([
                'amount' => "Amount ({$data['amount']}) exceeds the balance due ({$balance}) on bill {$bill->bill_number}.",
            ])->withInput();
        }

        $data['type'] = 'admission';
        $data['method'] = 'manual';
        $data['bill_id'] = $bill->id;
        $data['created_by'] = $request->user()->id;

        $memberWasPending = $member->status === 'pending';
        $payment = PaymentRecorder::record($member, $data);
        PaymentRecorder::applyBillDurationIfJustCompleted($bill, $memberWasPending);

        return redirect()->route('admin.members.show', $member)
            ->with('status', "Payment recorded against {$bill->bill_number} ({$payment->receipt_number}).");
    }

    public function refund(Request $request, Payment $payment)
    {
        if ($payment->status === 'refunded') {
            return back()->withErrors(['payment' => 'This payment has already been refunded.']);
        }

        $data = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:'.$payment->amount],
        ]);

        $payment->update([
            'status' => 'refunded',
            'refund_amount' => $data['refund_amount'],
            'refunded_at' => now(),
        ]);

        return back()->with('status', "Payment {$payment->receipt_number} refunded.");
    }

    public function receipt(Payment $payment)
    {
        $payment->load('member', 'paymentAccount');

        $pdf = Pdf::loadView('pdf.receipt', ['payment' => $payment]);

        return $pdf->stream("{$payment->receipt_number}.pdf");
    }
}
