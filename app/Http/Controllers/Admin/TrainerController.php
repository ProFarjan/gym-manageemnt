<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = User::role('Trainer')->with('trainerProfile')->get();

        return view('admin.trainers.index', compact('trainers'));
    }

    public function create()
    {
        return view('admin.trainers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);
        $user->assignRole('Trainer');

        $user->trainerProfile()->create([
            'specialization' => $data['specialization'] ?? null,
            'contact' => $data['contact'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        return redirect()->route('admin.trainers.index')->with('status', 'Trainer added.');
    }

    public function edit(User $trainer)
    {
        $trainer->load('trainerProfile');

        return view('admin.trainers.edit', ['trainer' => $trainer]);
    }

    public function update(Request $request, User $trainer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$trainer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
        ]);

        $trainer->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $trainer->trainerProfile()->updateOrCreate([], [
            'specialization' => $data['specialization'] ?? null,
            'contact' => $data['contact'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        return redirect()->route('admin.trainers.index')->with('status', 'Trainer updated.');
    }

    public function destroy(User $trainer)
    {
        $trainer->delete();

        return redirect()->route('admin.trainers.index')->with('status', 'Trainer removed.');
    }
}
