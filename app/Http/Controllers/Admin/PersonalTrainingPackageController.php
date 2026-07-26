<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalTrainingPackage;
use Illuminate\Http\Request;

class PersonalTrainingPackageController extends Controller
{
    public function index()
    {
        $packages = PersonalTrainingPackage::withCount('memberAssignments')->get();

        return view('admin.personal-training-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.personal-training-packages.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        PersonalTrainingPackage::create($data);

        return redirect()->route('admin.personal-training-packages.index')->with('status', 'Package created.');
    }

    public function edit(PersonalTrainingPackage $personalTrainingPackage)
    {
        return view('admin.personal-training-packages.edit', ['package' => $personalTrainingPackage]);
    }

    public function update(Request $request, PersonalTrainingPackage $personalTrainingPackage)
    {
        $data = $this->validateData($request);

        $personalTrainingPackage->update($data);

        return redirect()->route('admin.personal-training-packages.index')->with('status', 'Package updated.');
    }

    public function destroy(PersonalTrainingPackage $personalTrainingPackage)
    {
        if ($personalTrainingPackage->memberAssignments()->exists()) {
            return back()->withErrors(['package' => 'Cannot delete a package that has been assigned to members. Deactivate it instead.']);
        }

        $personalTrainingPackage->delete();

        return redirect()->route('admin.personal-training-packages.index')->with('status', 'Package deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sessions_count' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
