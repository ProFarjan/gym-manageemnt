<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        $plans = MembershipPlan::withCount('members')->orderBy('duration_in_months')->get();

        return view('admin.membership-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.membership-plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        MembershipPlan::create($data);

        return redirect()->route('admin.membership-plans.index')->with('status', 'Membership plan created.');
    }

    public function edit(MembershipPlan $membershipPlan)
    {
        return view('admin.membership-plans.edit', ['plan' => $membershipPlan]);
    }

    public function update(Request $request, MembershipPlan $membershipPlan)
    {
        $data = $this->validateData($request);

        $membershipPlan->update($data);

        return redirect()->route('admin.membership-plans.index')->with('status', 'Membership plan updated.');
    }

    public function destroy(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->members()->exists()) {
            return back()->withErrors(['plan' => 'Cannot delete a plan that has members assigned. Deactivate it instead.']);
        }

        $membershipPlan->delete();

        return redirect()->route('admin.membership-plans.index')->with('status', 'Membership plan deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_lifetime' => ['nullable', 'boolean'],
            'duration_in_months' => ['nullable', 'integer', 'min:1', 'required_unless:is_lifetime,1'],
            'price' => ['required', 'numeric', 'min:0'],
            'admission_fee' => ['required', 'numeric', 'min:0'],
            'admission_discount' => ['nullable', 'numeric', 'min:0'],
            'admission_free' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_lifetime'] = $request->boolean('is_lifetime');
        $data['admission_free'] = $request->boolean('admission_free');
        $data['is_active'] = $request->boolean('is_active');
        $data['duration_in_months'] = $data['is_lifetime'] ? null : $data['duration_in_months'];
        $data['admission_discount'] = $data['admission_discount'] ?? 0;

        return $data;
    }
}
