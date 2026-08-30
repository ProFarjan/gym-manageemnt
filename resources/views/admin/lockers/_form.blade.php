@php
    $v = fn($field, $default = null) => old($field, $locker?->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Locker Number <span class="text-danger">*</span></label>
            <input type="text" name="locker_number" value="{{ $v('locker_number') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Location</label>
            <input type="text" name="location" value="{{ $v('location') }}" class="form-control" placeholder="e.g. Men's changing room">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ $v('notes') }}</textarea>
        </div>
    </div>
</div>
