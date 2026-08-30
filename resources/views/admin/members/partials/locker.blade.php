<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Locker #</th><th>Location</th><th>Rent Due Date</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($member->lockers as $locker)
                <tr>
                    <td>{{ $locker->locker_number }}</td>
                    <td>{{ $locker->location ?? '—' }}</td>
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
                    <td>
                        @can('lockers.update')
                            <a href="#" class="btn btn-sm btn-outline-primary" data-modal-url="{{ route('admin.lockers.bill-panel', $locker) }}" data-modal-title="Generate Rent Bill — Locker #{{ $locker->locker_number }}">Generate Rent Bill</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No locker assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@can('lockers.update')
    @if ($member->lockers->isEmpty() && $availableLockers->isNotEmpty())
        <div class="card-body border-top">
            <form method="POST" action="{{ route('admin.members.assign-locker', $member) }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-9">
                    <label class="form-label">Assign a Locker</label>
                    <select name="locker_id" class="form-select" required>
                        <option value="">Select an available locker</option>
                        @foreach ($availableLockers as $locker)
                            <option value="{{ $locker->id }}">#{{ $locker->locker_number }} @if ($locker->location) ({{ $locker->location }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Assign</button>
                </div>
            </form>
        </div>
    @elseif ($member->lockers->isEmpty())
        <div class="card-body border-top text-muted small">No lockers currently available to assign.</div>
    @endif
@endcan
