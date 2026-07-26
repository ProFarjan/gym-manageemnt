<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    public function index()
    {
        $classes = GymClass::with('trainer')->orderByRaw("FIELD(day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")->orderBy('start_time')->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $trainers = User::role('Trainer')->get();

        return view('admin.classes.create', compact('trainers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        GymClass::create($data);

        return redirect()->route('admin.classes.index')->with('status', 'Class created.');
    }

    public function edit(GymClass $class)
    {
        $trainers = User::role('Trainer')->get();

        return view('admin.classes.edit', ['gymClass' => $class, 'trainers' => $trainers]);
    }

    public function update(Request $request, GymClass $class)
    {
        $data = $this->validateData($request);

        $class->update($data);

        return redirect()->route('admin.classes.index')->with('status', 'Class updated.');
    }

    public function destroy(GymClass $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')->with('status', 'Class deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trainer_id' => ['nullable', 'exists:users,id'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
