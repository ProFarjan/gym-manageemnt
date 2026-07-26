@extends('layouts.admin')

@section('title', 'Class Schedule')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Daily Fitness Classes</h1>
        @can('personal_training.create')
            <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">+ New Class</a>
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
                        <th>Class</th>
                        <th>Trainer</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classes as $class)
                        <tr>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->trainer?->name ?? '—' }}</td>
                            <td>{{ ucfirst($class->day_of_week) }}</td>
                            <td>{{ \Carbon\Carbon::parse($class->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('g:i A') }}</td>
                            <td>{{ $class->capacity }}</td>
                            <td>
                                <span class="badge bg-{{ $class->is_active ? 'success' : 'secondary' }}">
                                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @can('personal_training.update')
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('personal_training.delete')
                                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" class="d-inline" onsubmit="return confirm('Delete this class?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No classes scheduled yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
