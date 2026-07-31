@php
    $paid = $bill->paidAmount();
    $balance = $bill->balanceDue();
    $discountGiven = $bill->discountGiven();
@endphp

<div class="row g-3 mb-3 pay-due-summary">
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-5">Bill Number</dt><dd class="col-7">{{ $bill->bill_number }}</dd>
            <dt class="col-5">Member Name</dt><dd class="col-7">{{ $bill->member->full_name }}</dd>
            <dt class="col-5">Member Phone</dt><dd class="col-7">{{ $bill->member->mobile_number }}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-6">Bill Amount</dt><dd class="col-6">{{ number_format($bill->amount, 2) }}</dd>
            <dt class="col-6">Resolved So Far</dt><dd class="col-6">{{ number_format($paid, 2) }} @if ($discountGiven > 0)<span class="text-muted small">({{ number_format($discountGiven, 2) }} discounted)</span>@endif</dd>
            <dt class="col-6">Balance Due</dt><dd class="col-6">{{ number_format($balance, 2) }}</dd>
        </dl>
    </div>
</div>

<hr>

<form method="POST" action="{{ route('admin.bills.pay', $bill) }}" class="row g-3" data-balance-due="{{ number_format($balance, 2, '.', '') }}">
    @csrf
    <div class="col-md-6">
        <label class="form-label small mb-0">Account</label>
        <select name="payment_account_id" class="form-select form-select-sm" required>
            @foreach ($paymentAccounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Amount (Cash Received)</label>
        <input type="number" step="0.01" name="amount" id="billPayAmount" value="{{ number_format($balance, 2, '.', '') }}" class="form-control form-control-sm" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Discount Reason</label>
        <input type="text" name="discount_reason" class="form-control form-control-sm">
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Discount Amount</label>
        <input type="number" step="0.01" name="discount_amount" id="billPayDiscount" class="form-control form-control-sm">
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6 text-end">
        <span class="text-muted small">Total Settled (Amount + Discount):</span>
        <strong id="billPayTotalSettled">{{ number_format($balance, 2, '.', '') }}</strong>
        <div class="small text-danger d-none" id="billPayOverageWarning">Exceeds balance due — reduce Amount or Discount.</div>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
    </div>
</form>
