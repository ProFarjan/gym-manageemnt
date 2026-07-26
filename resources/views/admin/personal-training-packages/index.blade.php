@extends('layouts.admin')

@section('title', 'Personal Training Packages')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Personal Training Packages</h1>
        @can('personal_training.create')
            <a href="{{ route('admin.personal-training-packages.create') }}" class="btn btn-primary">+ New Package</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Sessions</th>
                        <th>Price</th>
                        <th>Validity</th>
                        <th>Assigned</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>{{ $package->name }}</td>
                            <td>{{ $package->sessions_count }}</td>
                            <td>{{ number_format($package->price, 2) }} BDT</td>
                            <td>{{ $package->validity_days }} days</td>
                            <td>{{ $package->member_assignments_count }}</td>
                            <td>
                                <span class="badge bg-{{ $package->is_active ? 'success' : 'secondary' }}">
                                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @can('personal_training.update')
                                    <a href="{{ route('admin.personal-training-packages.edit', $package) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('personal_training.delete')
                                    <form method="POST" action="{{ route('admin.personal-training-packages.destroy', $package) }}" class="d-inline" onsubmit="return confirm('Delete this package?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No packages created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
