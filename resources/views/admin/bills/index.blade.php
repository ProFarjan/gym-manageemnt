@extends('layouts.admin')

@section('title', 'Bills')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Bills</h1>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Bill #, Name or Admission ID">
        </div>
        <div class="col-auto">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                @foreach (['unpaid', 'partial', 'paid'] as $status)
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
                        <th>Bill #</th>
                        <th>Member</th>
                        <th>Plan</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        @php
                            $status = $bill->statusLabel();
                            $paid = $bill->paidAmount();
                            $balance = $bill->balanceDue();
                        @endphp
                        <tr>
                            <td>{{ $bill->bill_number }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($bill->member->photo_path)
                                        <img src="{{ asset('storage/'.$bill->member->photo_path) }}" alt="" class="member-avatar">
                                    @else
                                        @php
                                            $initial = strtoupper(substr($bill->member->full_name, 0, 1)) ?: '?';
                                            $avatarColors = ['e64980','ae3ec9','7048e8','4263eb','1971c2','0c8599','2f9e44','66a80f','f08c00','e8590c'];
                                            $avatarColor = $avatarColors[ord($initial) % count($avatarColors)];
                                        @endphp
                                        <span class="member-avatar member-avatar-initials" style="background-color: #{{ $avatarColor }};">{{ $initial }}</span>
                                    @endif
                                    <span>{{ $bill->member->full_name }} <span class="text-muted small">({{ $bill->member->admission_id }})</span></span>
                                </div>
                            </td>
                            <td>{{ $bill->membershipPlan?->name ?? '—' }}</td>
                            <td>{{ number_format($bill->amount, 2) }}</td>
                            <td>{{ number_format($paid, 2) }}</td>
                            <td>{{ number_format($balance, 2) }}</td>
                            <td>{{ $bill->due_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ match($status) {
                                    'paid' => 'success',
                                    'partial' => 'warning',
                                    default => 'secondary',
                                } }}">{{ ucfirst($status) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @can('bills.view')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.bills.view-panel', $bill) }}" data-modal-title="Bill {{ $bill->bill_number }} — {{ $bill->member->full_name }}">View</a></li>
                                        @endcan
                                        @can('bills.create')
                                            @if ($status !== 'paid')
                                                <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.bills.pay-panel', $bill) }}" data-modal-title="Pay Bill {{ $bill->bill_number }} — {{ $bill->member->full_name }}">Pay</a></li>
                                            @endif
                                        @endcan
                                        @can('bills.delete')
                                            @if ($status === 'unpaid')
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-delete-url="{{ route('admin.bills.destroy', $bill) }}" data-delete-name="Bill {{ $bill->bill_number }}">Delete</a></li>
                                            @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No bills found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $bills->links() }}
    </div>

    <div class="modal fade" id="billActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="billActionModalLabel">&nbsp;</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="billActionModalBody">
                    <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="billDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="billDeleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="billDeleteName"></strong>? This cannot be undone.
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
