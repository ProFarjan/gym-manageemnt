@php
    $v = fn($field, $default = null) => old($field, $offer?->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-12">
            <label class="form-label">Offer Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ $v('name') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" value="{{ $v('start_date') instanceof \Carbon\Carbon ? $v('start_date')->format('Y-m-d') : $v('start_date') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" value="{{ $v('end_date') instanceof \Carbon\Carbon ? $v('end_date')->format('Y-m-d') : $v('end_date') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
            <select name="discount_type" class="form-select" required>
                <option value="percentage" @selected($v('discount_type') === 'percentage')>Percentage</option>
                <option value="fixed" @selected($v('discount_type') === 'fixed')>Fixed Amount (BDT)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="discount_amount" value="{{ $v('discount_amount') }}" class="form-control" required>
        </div>
        <div class="col-md-12">
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked($v('is_active', true))>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
        </div>
    </div>
</div>
