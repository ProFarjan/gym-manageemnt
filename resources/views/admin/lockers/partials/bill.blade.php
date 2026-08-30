@php
    $rate = (float) setting('locker_monthly_price', 0);
@endphp

<form method="POST" action="{{ route('admin.lockers.generate-bill', $locker) }}" class="row g-3">
    @csrf

    <div class="col-12">
        <p class="text-muted small mb-0">
            Member: <strong>{{ $locker->member->full_name }}</strong> ({{ $locker->member->admission_id }})<br>
            Rate: <strong>{{ number_format($rate, 2) }}</strong> / month
        </p>
    </div>

    <div class="col-md-6">
        <label class="form-label small mb-0">Months</label>
        <input type="number" name="months" id="lockerBillMonths" class="form-control form-control-sm" value="1" min="1" max="36" step="1" data-rate="{{ $rate }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Total</label>
        <input type="text" id="lockerBillTotal" class="form-control form-control-sm" value="{{ number_format($rate, 2) }}" readonly tabindex="-1">
    </div>

    <div class="col-12">
        <hr class="my-1">
        <h6 class="mb-2">Payment <span class="text-muted small fw-normal">(optional — leave blank to save as fully unpaid)</span></h6>
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small mb-0">Account</label>
                <select name="payment_account_id" class="form-select form-select-sm">
                    <option value="">— None —</option>
                    @foreach ($paymentAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Amount</label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">Payment Note</label>
                <input type="text" name="payment_notes" class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary btn-sm">Generate Bill</button>
    </div>
</form>
