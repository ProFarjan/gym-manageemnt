<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberTrainingPackage;
use App\Models\PersonalTrainingPackage;
use Illuminate\Http\Request;

class MemberTrainingPackageController extends Controller
{
    public function store(Request $request, Member $member)
    {
        $data = $request->validate([
            'personal_training_package_id' => ['required', 'exists:personal_training_packages,id'],
            'trainer_id' => ['nullable', 'exists:users,id'],
        ]);

        $package = PersonalTrainingPackage::findOrFail($data['personal_training_package_id']);

        $member->memberTrainingPackages()->create([
            'personal_training_package_id' => $package->id,
            'trainer_id' => $data['trainer_id'] ?? null,
            'purchased_at' => now(),
            'expires_at' => now()->addDays($package->validity_days),
        ]);

        return back()->with('status', "Assigned {$package->name} to {$member->full_name}.");
    }

    public function logSession(MemberTrainingPackage $trainingPackage)
    {
        if ($trainingPackage->sessionsRemaining() <= 0) {
            return back()->withErrors(['package' => 'No sessions remaining on this package.']);
        }

        $trainingPackage->increment('sessions_used');

        return back()->with('status', 'Session logged.');
    }
}
