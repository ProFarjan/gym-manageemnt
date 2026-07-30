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
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
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
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>{{ $member->admission_id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($member->photo_path)
                                        <img src="{{ asset('storage/'.$member->photo_path) }}" alt="" class="member-avatar">
                                    @else
                                        @php
                                            $initial = strtoupper(substr($member->full_name, 0, 1)) ?: '?';
                                            $avatarColors = ['e64980','ae3ec9','7048e8','4263eb','1971c2','0c8599','2f9e44','66a80f','f08c00','e8590c'];
                                            $avatarColor = $avatarColors[ord($initial) % count($avatarColors)];
                                        @endphp
                                        <span class="member-avatar member-avatar-initials" style="background-color: #{{ $avatarColor }};">{{ $initial }}</span>
                                    @endif
                                    <span>{{ $member->full_name }}</span>
                                </div>
                            </td>
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
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('members.view')
                                            <li><a class="dropdown-item" href="{{ route('admin.members.show', $member) }}">View</a></li>
                                        @endcan
                                        @can('members.update')
                                            <li><a class="dropdown-item" href="{{ route('admin.members.edit', $member) }}">Edit</a></li>
                                        @endcan
                                        @can('payments.create')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.members.pay-due', $member) }}" data-modal-title="Pay Due — {{ $member->full_name }}">Pay Due</a></li>
                                        @endcan
                                        @can('payments.view')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.members.payments-panel', $member) }}" data-modal-title="Payment History — {{ $member->full_name }}">View Payments</a></li>
                                        @endcan
                                        @can('members.view')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.members.attendance-panel', $member) }}" data-modal-title="Attendance Records — {{ $member->full_name }}">Attendance Records</a></li>
                                        @endcan
                                        @can('personal_training.view')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.members.training-panel', $member) }}" data-modal-title="Training Packages — {{ $member->full_name }}">Training</a></li>
                                        @endcan
                                        @can('settings.view')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.members.zkteco-panel', $member) }}" data-modal-title="ZKTeco Sync — {{ $member->full_name }}">ZKTeco Sync</a></li>
                                        @endcan
                                        @can('members.delete')
                                            @if ($member->status === 'pending')
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-delete-url="{{ route('admin.members.destroy', $member) }}" data-delete-name="{{ $member->full_name }}">Delete</a></li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
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

    <div class="modal fade" id="memberActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberActionModalLabel">&nbsp;</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="memberActionModalBody">
                    <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="memberDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="memberDeleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="memberDeleteName"></strong>'s pending registration? This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
