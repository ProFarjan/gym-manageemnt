<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberLoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('member')->check()) {
            return redirect()->route('member.dashboard');
        }

        return view('auth.member-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('member')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $member = Auth::guard('member')->user();

        // "Expired" only disables ZKTeco/door access, not the web portal, so members
        // can still log in to view dues and pay a renewal online. Closed/Pending cannot.
        if (in_array($member->status, ['closed', 'pending'], true)) {
            Auth::guard('member')->logout();

            $message = $member->status === 'closed'
                ? 'Your membership is closed. Please visit the gym for a new admission.'
                : 'Your registration is still pending payment approval.';

            return back()->withErrors(['email' => $message]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login');
    }
}
