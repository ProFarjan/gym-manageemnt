<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Services\PaymentRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OnlineRenewalController extends Controller
{
    public function show()
    {
        $member = Auth::guard('member')->user();

        if ($member->isLifetime()) {
            return redirect()->route('member.dashboard')->with('status', 'Your Lifetime membership has no renewal due.');
        }

        return view('member.renew', ['member' => $member->load('membershipPlan')]);
    }

    /**
     * Kick off a "checkout" with the chosen gateway. This is a local stub —
     * there are no bKash/Nagad merchant sandbox credentials wired up yet, so it
     * simulates the redirect-and-confirm flow real gateways use. Swap the
     * confirm() step for a real callback/webhook handler once credentials exist.
     */
    public function initiate(Request $request)
    {
        $request->validate(['gateway' => ['required', 'in:bkash,nagad']]);

        $member = Auth::guard('member')->user()->load('membershipPlan');

        $token = Str::random(32);
        session([
            "renewal_checkout.{$token}" => [
                'member_id' => $member->id,
                'gateway' => $request->string('gateway')->value(),
                'amount' => $member->membershipPlan->price,
            ],
        ]);

        return redirect()->route('member.renew.checkout', $token);
    }

    public function checkout(string $token)
    {
        $checkout = session("renewal_checkout.{$token}");

        abort_unless($checkout, 404);
        abort_unless($checkout['member_id'] === Auth::guard('member')->id(), 403);

        return view('member.renew-checkout', ['token' => $token, 'checkout' => $checkout]);
    }

    public function confirm(Request $request, string $token)
    {
        $checkout = session("renewal_checkout.{$token}");

        abort_unless($checkout, 404);

        $member = Auth::guard('member')->user();
        abort_unless($checkout['member_id'] === $member->id, 403);

        $account = PaymentAccount::where('type', $checkout['gateway'])->first();

        $payment = PaymentRecorder::record($member, [
            'type' => 'renewal',
            'method' => $checkout['gateway'],
            'payment_account_id' => $account->id,
            'amount' => $checkout['amount'],
            'transaction_reference' => 'SIM-'.strtoupper(Str::random(10)),
        ]);

        session()->forget("renewal_checkout.{$token}");

        return redirect()->route('member.renew.success', $payment);
    }

    public function success(Payment $payment)
    {
        abort_unless($payment->member_id === Auth::guard('member')->id(), 403);

        return view('member.renew-success', compact('payment'));
    }
}
