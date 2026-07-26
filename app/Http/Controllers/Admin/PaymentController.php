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
            'type' => ['required', 'in:admission,monthly,package,renewal,personal_training'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['required_with:discount_amount', 'nullable', 'string', 'max:255'],
            'periods' => ['nullable', 'integer', 'min:1', 'max:24'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['method'] = 'manual';
        $data['created_by'] = $request->user()->id;

        $payment = PaymentRecorder::record($member, $data);

        return redirect()->route('admin.members.show', $member)
            ->with('status', "Payment recorded ({$payment->receipt_number}).");
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
