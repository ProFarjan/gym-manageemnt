@php
    $v = fn($field, $default = null) => old($field, $package?->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Package Name *</label>
            <input type="text" name="name" value="{{ $v('name') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Sessions Count *</label>
            <input type="number" name="sessions_count" value="{{ $v('sessions_count') }}" class="form-control" min="1" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Price (BDT) *</label>
            <input type="number" step="0.01" name="price" value="{{ $v('price') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Validity (days) *</label>
            <input type="number" name="validity_days" value="{{ $v('validity_days', 30) }}" class="form-control" min="1" required>
        </div>
        <div class="col-md-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked($v('is_active', true))>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
        </div>
    </div>
</div>
