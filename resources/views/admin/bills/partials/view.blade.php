@php
    $status = $bill->statusLabel();
    $paid = $bill->paidAmount();
    $balance = $bill->balanceDue();
    $discountGiven = $bill->discountGiven();
    $lineItems = $bill->lineItems();
    $subtotal = collect($lineItems)->sum('amount');
@endphp

<style>
    .bill-print-header {
        text-align: center;
        border-bottom: 2px solid #d6336c;
        padding-bottom: 12px;
    }
    .bill-print-header-inner {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        text-align: left;
    }
    .bill-print-logo {
        height: 50px;
    }
    .bill-meta-table {
        width: 100%;
        border-collapse: collapse;
    }
    @media print {
        html, body { background: #fff !important; }
        body * { visibility: hidden; }
        #billActionModal, #billActionModal * { visibility: visible; }
        #billActionModal .modal-dialog { max-width: 100%; margin: 0; }
        #billActionModal .modal-content { border: none; background: #fff !important; }
        #billActionModal .modal-header,
        #billActionModal .no-print,
        .modal-backdrop { display: none !important; }
        #billActionModal { position: absolute; inset: 0; background: #fff !important; }
    }
</style>

<div class="bill-print-header mb-3">
    <div class="bill-print-header-inner">
        @if (setting('logo_path'))
            <img src="{{ asset('storage/'.setting('logo_path')) }}" class="bill-print-logo">
        @endif
        <div>
            <h5 class="mb-0">{{ setting('business_name', config('app.name')) }}</h5>
            @if (setting('business_address'))
                <p class="small text-muted mb-0">{{ setting('business_address') }}</p>
            @endif
            @if (setting('business_phone'))
                <p class="small text-muted mb-0">Phone: {{ setting('business_phone') }}</p>
            @endif
        </div>
    </div>
</div>

<h6 class="text-center text-uppercase mb-3">Bill / Invoice</h6>

<table class="bill-meta-table mb-3">
    <tr>
        <td class="w-50 align-top">
            <strong>Bill No:</strong> {{ $bill->bill_number }}<br>
            <strong>Date:</strong> {{ $bill->created_at->format('d M Y') }}<br>
            <strong>Due Date:</strong> {{ $bill->due_date?->format('d M Y') ?? '—' }}
            @if ($bill->duration_months)
                <br><strong>Duration:</strong> {{ $bill->duration_months }} Month(s)
            @endif
        </td>
        <td class="w-50 align-top">
            <strong>Member:</strong> {{ $bill->member->full_name }}<br>
            <strong>Admission ID:</strong> {{ $bill->member->admission_id }}<br>
            <strong>Mobile:</strong> {{ $bill->member->mobile_number }}
        </td>
    </tr>
</table>

<div class="table-responsive">
    <table class="table table-bordered mb-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Particulars</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Total (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lineItems as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['label'] }}</td>
                    <td class="text-end">{{ rtrim(rtrim(number_format($item['qty'], 2), '0'), '.') }}</td>
                    <td class="text-end">{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="text-end">{{ number_format($item['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end">Subtotal</td>
                <td class="text-end">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if ($bill->discount_amount > 0)
                <tr>
                    <td colspan="4" class="text-end">Discount</td>
                    <td class="text-end">-{{ number_format($bill->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="fw-bold">
                <td colspan="4" class="text-end">Grand Total</td>
                <td class="text-end">{{ number_format($bill->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-end">Paid</td>
                <td class="text-end">{{ number_format($paid, 2) }}</td>
            </tr>
            @if ($discountGiven > 0)
                <tr>
                    <td colspan="4" class="text-end text-muted small">— of which discounted</td>
                    <td class="text-end text-muted small">{{ number_format($discountGiven, 2) }}</td>
                </tr>
            @endif
            <tr class="fw-bold">
                <td colspan="4" class="text-end">Balance Due</td>
                <td class="text-end">{{ number_format($balance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<p class="mb-3 no-print">
    <strong>Status:</strong>
    <span class="badge bg-{{ match($status) {
        'paid' => 'success',
        'partial' => 'warning',
        default => 'secondary',
    } }}">{{ ucfirst($status) }}</span>
</p>

<div class="no-print">
    <hr>

    <h6>Payments Received</h6>
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
                    <td class="no-print">
                        <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Receipt</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No payments recorded against this bill yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
