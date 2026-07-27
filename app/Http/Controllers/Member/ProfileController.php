<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('member.profile', ['member' => Auth::guard('member')->user()]);
    }

    public function update(Request $request)
    {
        $member = Auth::guard('member')->user();

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member->id)],
            'mobile_number' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'current_password' => ['nullable', 'required_with:password', 'current_password:member'],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
        ]);

        $member->email = $data['email'] ?? null;
        $member->mobile_number = $data['mobile_number'];

        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $member->photo_path = $request->file('photo')->store('members/photos', 'public');
        }

        if (! empty($data['password'])) {
            $member->password = $data['password'];
        }

        $member->save();

        return back()->with('status', 'Profile updated.');
    }
}
