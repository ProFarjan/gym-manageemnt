<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Locker #</th>
                    <th>Location</th>
                    <th>Assigned To</th>
                    <th>Assigned Since</th>
                    <th>Rent Due Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lockers as $locker)
                    <tr>
                        <td>{{ $locker->locker_number }}</td>
                        <td>{{ $locker->location ?? '—' }}</td>
                        <td>
                            @if ($locker->member)
                                {{ $locker->member->full_name }} <span class="text-muted small">({{ $locker->member->admission_id }})</span>
                            @else
                                <span class="badge bg-secondary">Available</span>
                            @endif
                        </td>
                        <td>{{ $locker->assigned_at?->format('d M Y') ?? '—' }}</td>
                        <td>
                            @if ($locker->rent_due_date)
                                <span class="{{ $locker->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                    {{ $locker->rent_due_date->format('d M Y') }}
                                    @if ($locker->isOverdue())
                                        (Overdue)
                                    @endif
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Action
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('lockers.update')
                                        @if ($locker->isAssigned())
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.lockers.bill-panel', $locker) }}" data-modal-title="Generate Rent Bill — Locker #{{ $locker->locker_number }}">Generate Rent Bill</a></li>
                                            <li>
                                                <form method="POST" action="{{ route('admin.lockers.unassign', $locker) }}" onsubmit="return confirm('Unassign this locker?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">Unassign</button>
                                                </form>
                                            </li>
                                        @else
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.lockers.assign-panel', $locker) }}" data-modal-title="Assign Locker #{{ $locker->locker_number }}">Assign</a></li>
                                        @endif
                                        <li><a class="dropdown-item" href="{{ route('admin.lockers.edit', $locker) }}">Edit</a></li>
                                    @endcan
                                    @can('lockers.delete')
                                        @if (! $locker->isAssigned())
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" data-delete-url="{{ route('admin.lockers.destroy', $locker) }}" data-delete-name="Locker #{{ $locker->locker_number }}">Delete</a></li>
                                        @endif
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No lockers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
    <div class="text-muted small">
        @if ($lockers->total() > 0)
            Showing {{ $lockers->firstItem() }} to {{ $lockers->lastItem() }} of {{ $lockers->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $lockers->links() }}
</div>
