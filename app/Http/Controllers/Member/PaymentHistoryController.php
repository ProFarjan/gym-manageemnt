<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        $payments = $member->payments()->with('paymentAccount')->latest()->paginate(15);

        return view('member.payments', compact('payments'));
    }

    public function receipt(Payment $payment)
    {
        abort_unless($payment->member_id === Auth::guard('member')->id(), 403);

        $payment->load('member', 'paymentAccount', 'bill.items');

        $pdf = Pdf::loadView('pdf.receipt', ['payment' => $payment]);

        return $pdf->stream("{$payment->receipt_number}.pdf");
    }
}
