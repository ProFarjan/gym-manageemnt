<form method="POST" action="{{ route('admin.lockers.assign', $locker) }}" class="row g-3">
    @csrf

    <div class="col-12">
        <label class="form-label small mb-0">Member</label>
        <select name="member_id" class="form-select form-select-sm" required>
            <option value="">— Select a member —</option>
            @foreach ($members as $member)
                <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->admission_id }})</option>
            @endforeach
        </select>
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary btn-sm">Assign Locker</button>
    </div>
</form>
