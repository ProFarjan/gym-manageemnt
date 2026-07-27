@php
    $v = fn($field, $default = null) => old($field, $expense?->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-body row g-3">
        <div class="col-md-12">
            <label class="form-label">Description *</label>
            <input type="text" name="description" value="{{ $v('description') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <input type="text" name="category" value="{{ $v('category') }}" class="form-control" placeholder="e.g. Utilities, Rent, Maintenance">
        </div>
        <div class="col-md-4">
            <label class="form-label">Amount (BDT) *</label>
            <input type="number" step="0.01" name="amount" value="{{ $v('amount') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date *</label>
            <input type="date" name="expense_date" value="{{ $v('expense_date') instanceof \Carbon\Carbon ? $v('expense_date')->format('Y-m-d') : $v('expense_date') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Account *</label>
            <select name="payment_account_id" class="form-select" required>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected($v('payment_account_id') == $account->id)>{{ $account->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
