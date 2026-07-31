<div class="table-responsive">
    <table class="table mb-0">
        <thead>
            <tr><th>Date</th><th>Type</th><th>Amount</th><th>Discount</th><th>Method</th><th>Account</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payment->type)) }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->discount_amount > 0 ? number_format($payment->discount_amount, 2) : '—' }}</td>
                    <td>{{ ucfirst($payment->method) }}</td>
                    <td>{{ $payment->paymentAccount->name }}</td>
                    <td>
                        <span class="badge bg-{{ $payment->status === 'refunded' ? 'danger' : 'success' }}">{{ ucfirst($payment->status) }}</span>
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Receipt</a>
                        @can('payments.update')
                            @if ($payment->status !== 'refunded')
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#refund-{{ $payment->id }}">Refund</button>
                            @endif
                        @endcan
                    </td>
                </tr>
                @if ($payment->status !== 'refunded')
                    <tr class="collapse" id="refund-{{ $payment->id }}">
                        <td colspan="8" class="bg-light">
                            <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="d-flex gap-2 align-items-end">
                                @csrf
                                <div>
                                    <label class="form-label small mb-0">Refund Amount</label>
                                    <input type="number" step="0.01" name="refund_amount" value="{{ $payment->amount }}" max="{{ $payment->amount }}" class="form-control form-control-sm" required>
                                </div>
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Confirm refund?');">Confirm Refund</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-2 d-flex justify-content-center">
    {{ $payments->onEachSide(1)->links() }}
</div>
