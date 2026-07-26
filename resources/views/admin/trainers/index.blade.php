@extends('layouts.admin')

@section('title', 'Trainers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Trainers</h1>
        @can('users.create')
            <a href="{{ route('admin.trainers.create') }}" class="btn btn-primary">+ New Trainer</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Specialization</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trainers as $trainer)
                        <tr>
                            <td>{{ $trainer->name }}</td>
                            <td>{{ $trainer->email }}</td>
                            <td>{{ $trainer->trainerProfile?->specialization ?? '—' }}</td>
                            <td>{{ $trainer->trainerProfile?->contact ?? $trainer->phone ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $trainer->is_active ? 'success' : 'secondary' }}">
                                    {{ $trainer->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td>
                                @can('users.update')
                                    <a href="{{ route('admin.trainers.edit', $trainer) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('users.delete')
                                    <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}" class="d-inline" onsubmit="return confirm('Remove this trainer?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No trainers added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
