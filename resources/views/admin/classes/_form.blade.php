@php
    $v = fn($field, $default = null) => old($field, $gymClass?->{$field} ?? $default);
    $vTime = fn($field) => old($field, $gymClass ? \Carbon\Carbon::parse($gymClass->{$field})->format('H:i') : null);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Class Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ $v('name') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Trainer</label>
            <select name="trainer_id" class="form-select">
                <option value="">—</option>
                @foreach ($trainers as $trainer)
                    <option value="{{ $trainer->id }}" @selected($v('trainer_id') == $trainer->id)>{{ $trainer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Day of Week <span class="text-danger">*</span></label>
            <select name="day_of_week" class="form-select" required>
                @foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                    <option value="{{ $day }}" @selected($v('day_of_week') === $day)>{{ ucfirst($day) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="time" name="start_time" value="{{ $vTime('start_time') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">End Time <span class="text-danger">*</span></label>
            <input type="time" name="end_time" value="{{ $vTime('end_time') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Capacity <span class="text-danger">*</span></label>
            <input type="number" name="capacity" value="{{ $v('capacity', 20) }}" class="form-control" min="1" required>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked($v('is_active', true))>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
        </div>
    </div>
</div>
