<div class="row g-3 mb-3 pay-due-summary">
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-5">Member ID</dt><dd class="col-7">{{ $member->admission_id }}</dd>
            <dt class="col-5">Member Name</dt><dd class="col-7">{{ $member->full_name }}</dd>
            <dt class="col-5">Member Phone</dt><dd class="col-7">{{ $member->mobile_number }}</dd>
            <dt class="col-5">Due Date</dt><dd class="col-7">{{ $member->due_date?->format('d M Y') ?? ($member->isLifetime() ? 'Lifetime' : '—') }}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-6">Total Billed</dt><dd class="col-6">{{ number_format($totalBilled, 2) }}</dd>
            <dt class="col-6">Total Paid</dt><dd class="col-6">{{ number_format($totalPaid, 2) }}</dd>
            <dt class="col-6">Total Discount</dt><dd class="col-6">{{ number_format($totalDiscount, 2) }}</dd>
            <dt class="col-6">Total Due</dt><dd class="col-6">{{ number_format($totalDue, 2) }}</dd>
        </dl>
    </div>
</div>

<hr>

@if ($targetBill)
    <form method="POST" action="{{ route('admin.members.payments.store', $member) }}" class="row g-3">
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
            <label class="form-label small mb-0">Amount</label>
            <input type="number" step="0.01" name="amount" value="{{ number_format($targetBill->balanceDue(), 2, '.', '') }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-12">
            <label class="form-label small mb-0">Payment Notes</label>
            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
        </div>
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>
    </form>
    <div class="form-text mt-2">
        Paying against bill {{ $targetBill->bill_number }} (balance {{ number_format($targetBill->balanceDue(), 2) }}).
    </div>
@else
    <p class="text-muted mb-0">No outstanding dues for this member.</p>
@endif
