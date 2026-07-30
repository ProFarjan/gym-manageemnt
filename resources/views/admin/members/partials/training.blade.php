<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Package</th><th>Trainer</th><th>Sessions</th><th>Expires</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($member->memberTrainingPackages as $assignment)
                <tr>
                    <td>{{ $assignment->package->name }}</td>
                    <td>{{ $assignment->trainer?->name ?? '—' }}</td>
                    <td>{{ $assignment->sessions_used }} / {{ $assignment->package->sessions_count }}</td>
                    <td>{{ $assignment->expires_at->format('d M Y') }} @if ($assignment->isExpired())<span class="badge bg-secondary">Expired</span>@endif</td>
                    <td>
                        @can('personal_training.update')
                            @if (!$assignment->isExpired() && $assignment->sessionsRemaining() > 0)
                                <form method="POST" action="{{ route('admin.training-packages.log-session', $assignment) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Log Session</button>
                                </form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No training packages assigned.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@can('personal_training.create')
    <div class="card-body border-top">
        <form method="POST" action="{{ route('admin.members.training-packages.store', $member) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-5">
                <label class="form-label">Assign Package</label>
                <select name="personal_training_package_id" class="form-select" required>
                    <option value="">Select a package</option>
                    @foreach ($trainingPackages as $tp)
                        <option value="{{ $tp->id }}">{{ $tp->name }} ({{ $tp->sessions_count }} sessions)</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Trainer</label>
                <select name="trainer_id" class="form-select">
                    <option value="">—</option>
                    @foreach ($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Assign</button>
            </div>
        </form>
    </div>
@endcan
