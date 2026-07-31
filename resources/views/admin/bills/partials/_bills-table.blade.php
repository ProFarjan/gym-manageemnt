<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Member</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Discount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bills as $bill)
                    @php
                        $status = $bill->statusLabel();
                        $paid = $bill->paidAmount();
                        $balance = $bill->balanceDue();
                        $subtotal = $bill->admission_fee_amount + $bill->monthly_amount;
                        $paymentDiscount = $bill->discountGiven();
                    @endphp
                    <tr>
                        <td>{{ $bill->bill_number }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($bill->member->photo_path)
                                    <img src="{{ asset('storage/'.$bill->member->photo_path) }}" alt="" class="member-avatar">
                                @else
                                    @php
                                        $initial = strtoupper(substr($bill->member->full_name, 0, 1)) ?: '?';
                                        $avatarColors = ['e64980','ae3ec9','7048e8','4263eb','1971c2','0c8599','2f9e44','66a80f','f08c00','e8590c'];
                                        $avatarColor = $avatarColors[ord($initial) % count($avatarColors)];
                                    @endphp
                                    <span class="member-avatar member-avatar-initials" style="background-color: #{{ $avatarColor }};">{{ $initial }}</span>
                                @endif
                                <span>{{ $bill->member->full_name }} <span class="text-muted small">({{ $bill->member->admission_id }})</span></span>
                            </div>
                        </td>
                        <td>{{ $bill->membershipPlan?->name ?? '—' }}</td>
                        <td>{{ number_format($subtotal, 2) }}</td>
                        <td>{{ $bill->discount_amount > 0 ? number_format($bill->discount_amount, 2) : '—' }}</td>
                        <td>
                            {{ number_format($paid, 2) }}
                            @if ($paymentDiscount > 0)
                                <div class="text-muted small">({{ number_format($paymentDiscount, 2) }} discounted)</div>
                            @endif
                        </td>
                        <td>{{ number_format($balance, 2) }}</td>
                        <td>{{ $bill->due_date?->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ match($status) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                default => 'secondary',
                            } }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Action
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('bills.view')
                                        <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.bills.view-panel', $bill) }}" data-modal-title="Bill {{ $bill->bill_number }} — {{ $bill->member->full_name }}">View</a></li>
                                    @endcan
                                    @can('bills.create')
                                        @if ($status !== 'paid')
                                            <li><a class="dropdown-item" href="#" data-modal-url="{{ route('admin.bills.pay-panel', $bill) }}" data-modal-title="Pay Bill {{ $bill->bill_number }} — {{ $bill->member->full_name }}">Pay</a></li>
                                        @endif
                                    @endcan
                                    @can('bills.delete')
                                        @if ($status === 'unpaid')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" data-delete-url="{{ route('admin.bills.destroy', $bill) }}" data-delete-name="Bill {{ $bill->bill_number }}">Delete</a></li>
                                        @endif
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No bills found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2">
    <div class="text-muted small">
        @if ($bills->total() > 0)
            Showing {{ $bills->firstItem() }} to {{ $bills->lastItem() }} of {{ $bills->total() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
    </div>
    {{ $bills->links() }}
</div>
