<form method="POST" action="{{ route('admin.bills.store') }}" class="row g-3">
    @csrf
    <div class="col-md-6">
        <label class="form-label small mb-0">Invoice No.</label>
        <input type="text" class="form-control form-control-sm" value="{{ $invoiceNumberPreview }}" readonly tabindex="-1">
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Invoice Date</label>
        <input type="text" name="due_date" class="form-control form-control-sm invoice-date-picker" value="{{ now()->format('Y-m-d') }}" autocomplete="off">
    </div>

    <div class="col-md-6">
        <label class="form-label small mb-0">Select Member</label>
        <div class="member-ajax-select" data-ajax-url="{{ route('admin.bills.members-search') }}">
            <input type="text" class="form-control form-control-sm member-ajax-input" placeholder="Search by name, phone or member ID" autocomplete="off" required>
            <input type="hidden" name="member_id" class="member-ajax-value">
            <div class="list-group member-ajax-results d-none"></div>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Duration (Month)</label>
        <input type="number" name="duration_months" class="form-control form-control-sm" min="1" max="120" placeholder="Optional">
    </div>

    <div class="col-12">
        <hr class="my-1">
    </div>

    <div class="col-12">
        <table class="table table-sm align-middle mb-2">
            <thead>
                <tr>
                    <th>Particulars</th>
                    <th style="width:90px;">Qty</th>
                    <th style="width:130px;">Unit Price</th>
                    <th style="width:130px;">Total</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody id="billItemsBody">
                <tr class="bill-item-row">
                    <td><input type="text" name="particulars[]" class="form-control form-control-sm" required></td>
                    <td><input type="number" name="qty[]" class="form-control form-control-sm bill-item-qty" value="1" min="0.01" step="0.01" required></td>
                    <td><input type="number" name="unit_price[]" class="form-control form-control-sm bill-item-price" value="0" min="0" step="0.01" required></td>
                    <td><input type="text" class="form-control form-control-sm bill-item-total" value="0.00" readonly tabindex="-1"></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger bill-item-remove">&times;</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="billItemAddRow">+ Add Row</button>
    </div>

    <template id="billItemRowTemplate">
        <tr class="bill-item-row">
            <td><input type="text" name="particulars[]" class="form-control form-control-sm" required></td>
            <td><input type="number" name="qty[]" class="form-control form-control-sm bill-item-qty" value="1" min="0.01" step="0.01" required></td>
            <td><input type="number" name="unit_price[]" class="form-control form-control-sm bill-item-price" value="0" min="0" step="0.01" required></td>
            <td><input type="text" class="form-control form-control-sm bill-item-total" value="0.00" readonly tabindex="-1"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger bill-item-remove">&times;</button></td>
        </tr>
    </template>

    <div class="col-12">
        <div class="row">
            <div class="col-md-7">
                <label class="form-label small mb-0">Notes</label>
                <textarea name="notes" class="form-control form-control-sm" rows="4"></textarea>
            </div>
            <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Sub Total</span>
                    <strong id="billSubTotal">0.00</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <label for="billDiscountInput" class="text-muted small mb-0">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount_amount" id="billDiscountInput" class="form-control form-control-sm text-end" style="width:130px;" value="0">
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <strong>Grand Total</strong>
                    <strong id="billGrandTotal">0.00</strong>
                </div>
            </div>
        </div>
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
        <button type="submit" class="btn btn-primary btn-sm">Save</button>
    </div>
</form>
