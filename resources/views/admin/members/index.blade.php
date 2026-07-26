@extends('layouts.admin')

@section('title', 'Members')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Members</h1>
        @can('members.create')
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary">+ New Registration</a>
        @endcan
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, Admission ID or Mobile">
        </div>
        <div class="col-auto">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach (['pending', 'active', 'expired', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Filter</button>
        </div>
    </form>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Admission ID</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>{{ $member->admission_id }}</td>
                            <td>{{ $member->full_name }}</td>
                            <td>{{ $member->mobile_number }}</td>
                            <td>{{ $member->membershipPlan?->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ match($member->status) {
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'expired' => 'secondary',
                                    'closed' => 'dark',
                                } }}">{{ ucfirst($member->status) }}</span>
                            </td>
                            <td>{{ $member->due_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.members.show', $member) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $members->links() }}
    </div>
@endsection
