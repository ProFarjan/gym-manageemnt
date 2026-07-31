@php
    $status = $bill->statusLabel();
    $paid = $bill->paidAmount();
    $balance = $bill->balanceDue();
@endphp

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-5">Bill Number</dt><dd class="col-7">{{ $bill->bill_number }}</dd>
            <dt class="col-5">Member</dt><dd class="col-7">{{ $bill->member->full_name }} ({{ $bill->member->admission_id }})</dd>
            <dt class="col-5">Phone</dt><dd class="col-7">{{ $bill->member->mobile_number }}</dd>
            <dt class="col-5">Plan</dt><dd class="col-7">{{ $bill->membershipPlan?->name ?? '—' }}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <dl class="row mb-0">
            <dt class="col-6">Amount</dt><dd class="col-6">{{ number_format($bill->amount, 2) }} BDT</dd>
            <dt class="col-6">Discount</dt><dd class="col-6">{{ $bill->discount_amount > 0 ? number_format($bill->discount_amount, 2).' BDT' : '—' }}</dd>
            <dt class="col-6">Paid</dt><dd class="col-6">{{ number_format($paid, 2) }} BDT</dd>
            <dt class="col-6">Balance Due</dt><dd class="col-6">{{ number_format($balance, 2) }} BDT</dd>
            <dt class="col-6">Due Date</dt><dd class="col-6">{{ $bill->due_date?->format('d M Y') ?? '—' }}</dd>
            <dt class="col-6">Status</dt>
            <dd class="col-6">
                <span class="badge bg-{{ match($status) {
                    'paid' => 'success',
                    'partial' => 'warning',
                    default => 'secondary',
                } }}">{{ ucfirst($status) }}</span>
            </dd>
        </dl>
    </div>
</div>

<hr>

<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Date</th><th>Amount</th><th>Discount</th><th>Method</th><th>Account</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($bill->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->discount_amount > 0 ? number_format($payment->discount_amount, 2) : '—' }}</td>
                    <td>{{ ucfirst($payment->method) }}</td>
                    <td>{{ $payment->paymentAccount->name }}</td>
                    <td>
                        <span class="badge bg-{{ $payment->status === 'refunded' ? 'danger' : 'success' }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Receipt</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No payments recorded against this bill yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
