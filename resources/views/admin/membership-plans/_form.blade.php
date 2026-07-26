@php
    $v = fn($field, $default = null) => old($field, $plan?->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Plan Name *</label>
            <input type="text" name="name" value="{{ $v('name') }}" class="form-control" required>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" name="is_lifetime" value="1" id="is_lifetime" class="form-check-input" @checked($v('is_lifetime')) onchange="document.getElementById('duration_wrap').style.display = this.checked ? 'none' : 'block';">
                <label for="is_lifetime" class="form-check-label">Lifetime plan (no recurring due date)</label>
            </div>
        </div>
        <div class="col-md-6" id="duration_wrap" style="{{ $v('is_lifetime') ? 'display:none;' : '' }}">
            <label class="form-label">Duration (months)</label>
            <input type="number" name="duration_in_months" value="{{ $v('duration_in_months') }}" class="form-control" min="1">
        </div>
        <div class="col-md-6">
            <label class="form-label">Price (BDT) *</label>
            <input type="number" step="0.01" name="price" value="{{ $v('price') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Admission Fee (BDT) *</label>
            <input type="number" step="0.01" name="admission_fee" value="{{ $v('admission_fee', 2500) }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Admission Discount (BDT)</label>
            <input type="number" step="0.01" name="admission_discount" value="{{ $v('admission_discount', 0) }}" class="form-control">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input type="checkbox" name="admission_free" value="1" id="admission_free" class="form-check-input" @checked($v('admission_free'))>
                <label for="admission_free" class="form-check-label">Admission Free</label>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked($v('is_active', true))>
                <label for="is_active" class="form-check-label">Active (visible for new registrations)</label>
            </div>
        </div>
    </div>
</div>
