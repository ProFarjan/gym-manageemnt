<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMemberRequest;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Services\AdmissionIdGenerator;

class MemberRegistrationController extends Controller
{
    public function create()
    {
        $plans = MembershipPlan::where('is_active', true)->get();

        return view('public.register', compact('plans'));
    }

    public function store(RegisterMemberRequest $request)
    {
        $data = $request->validated();

        $member = new Member($data);
        $member->admission_id = AdmissionIdGenerator::generate();
        $member->password = $data['password'];
        $member->registration_type = 'online';
        $member->status = 'pending';
        $member->save();

        return redirect()->route('register.pending')->with('admission_id', $member->admission_id);
    }

    public function pending()
    {
        if (! session('admission_id')) {
            return redirect()->route('register.create');
        }

        return view('public.register-pending', ['admission_id' => session('admission_id')]);
    }
}
