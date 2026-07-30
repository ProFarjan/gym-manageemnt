<h6>Record Payment</h6>
<form method="POST" action="{{ route('admin.members.payments.store', $member) }}" class="row g-2 align-items-end">
    @csrf
    <div class="col-md-2">
        <label class="form-label small mb-0">Type</label>
        <select name="type" class="form-select form-select-sm" required>
            <option value="monthly">Monthly</option>
            <option value="admission">Admission</option>
            <option value="package">Package</option>
            <option value="renewal">Renewal</option>
            <option value="personal_training">Personal Training</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-0">Account</label>
        <select name="payment_account_id" class="form-select form-select-sm" required>
            @foreach ($paymentAccounts as $account)
                <option value="{{ $account->id }}">{{ $account->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-0">Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control form-control-sm" required>
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-0">Discount</label>
        <input type="number" step="0.01" name="discount_amount" class="form-control form-control-sm">
    </div>
    <div class="col-md-2">
        <label class="form-label small mb-0">Discount Reason</label>
        <input type="text" name="discount_reason" class="form-control form-control-sm">
    </div>
    <div class="col-md-1">
        <label class="form-label small mb-0">Periods</label>
        <input type="number" name="periods" value="1" min="1" class="form-control form-control-sm">
    </div>
    <div class="col-md-1">
        <button type="submit" class="btn btn-primary btn-sm w-100">Save</button>
    </div>
</form>
<div class="form-text">"Periods" covers multiple billing cycles at once (e.g. catching up 2 missed months). Only applies to Monthly/Admission/Package/Renewal types.</div>
