<div class="row g-3 mb-3 pay-due-summary">
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-5">Member ID</dt><dd class="col-7">{{ $member->admission_id }}</dd>
            <dt class="col-5">Member Name</dt><dd class="col-7">{{ $member->full_name }}</dd>
            <dt class="col-5">Member Phone</dt><dd class="col-7">{{ $member->mobile_number }}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-6">Last Payment Date</dt><dd class="col-6">{{ $lastPayment?->created_at?->format('d M Y') ?? '—' }}</dd>
            <dt class="col-6">Last Payment Amount</dt><dd class="col-6">{{ $lastPayment ? number_format($lastPayment->amount, 2) : '—' }}</dd>
            <dt class="col-6">Due Date</dt><dd class="col-6">{{ $member->due_date?->format('d M Y') ?? ($member->isLifetime() ? 'Lifetime' : '—') }}</dd>
        </dl>
    </div>
</div>

<hr>

<form method="POST" action="{{ route('admin.members.payments.store', $member) }}" class="row g-3">
    @csrf
    <div class="col-md-6">
        <label class="form-label small mb-0">Type</label>
        <select name="type" id="payType" class="form-select form-select-sm" required data-plan-price="{{ $member->membershipPlan?->price ?? 0 }}">
            <option value="monthly">Monthly</option>
            <option value="admission">Admission</option>
            <option value="package">Package</option>
            <option value="renewal">Renewal</option>
            <option value="personal_training">Personal Training</option>
        </select>
    </div>
    <div class="col-md-6" id="payPeriodsField">
        <label class="form-label small mb-0">Periods (Month)</label>
        <input type="number" name="periods" id="payPeriods" value="1" min="1" class="form-control form-control-sm">
        <div class="form-text mb-0">Covers multiple billing cycles at once.</div>
    </div>
    <div class="col-md-6 d-none" id="payPackageField">
        <label class="form-label small mb-0">Package</label>
        <select id="payPackage" class="form-select form-select-sm">
            <option value="">Select a package</option>
            @foreach ($trainingPackages as $tp)
                <option value="{{ $tp->id }}" data-price="{{ $tp->price }}">{{ $tp->name }} ({{ number_format($tp->price, 2) }} BDT)</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Account</label>
        <select name="payment_account_id" class="form-select form-select-sm" required>
            @foreach ($paymentAccounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Amount</label>
        <input type="number" step="0.01" name="amount" id="payAmount" class="form-control form-control-sm" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Discount Reason</label>
        <input type="text" name="discount_reason" class="form-control form-control-sm">
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Discount Amount</label>
        <input type="number" step="0.01" name="discount_amount" id="payDiscount" class="form-control form-control-sm">
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6 text-end">
        <span class="text-muted small">Sub Total:</span>
        <strong id="paySubTotal">0.00</strong>
    </div>
    <div class="col-md-6"></div>
    <div class="col-md-6 text-end">
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
    </div>
</form>
