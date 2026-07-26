<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #d6336c; }
        .header p { margin: 2px 0; font-size: 11px; color: #555; }
        .meta { width: 100%; margin-bottom: 20px; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        table.items th { background: #f8f5f7; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; }
        .footer { margin-top: 40px; font-size: 11px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GirliGirl Gym &amp; Fitness</h1>
        <p>Reja Tower, 3rd Floor, Lift-2 (Opposite of BRAC Bank), 24/Ka, Shaymachoron Roy Road, Notun Bazar, Mymensingh</p>
        <p>Phone: 01728-381737</p>
    </div>

    <h2 style="text-align:center; font-size:16px;">Payment Receipt / Invoice</h2>

    <table class="meta">
        <tr>
            <td width="50%">
                <strong>Receipt No:</strong> {{ $payment->receipt_number }}<br>
                <strong>Invoice No:</strong> {{ $payment->invoice_number }}<br>
                <strong>Date:</strong> {{ $payment->created_at->format('d M Y, h:i A') }}
            </td>
            <td width="50%">
                <strong>Member:</strong> {{ $payment->member->full_name }}<br>
                <strong>Admission ID:</strong> {{ $payment->member->admission_id }}<br>
                <strong>Mobile:</strong> {{ $payment->member->mobile_number }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th>Payment Method</th>
                <th>Account</th>
                <th class="text-right">Amount (BDT)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ ucfirst(str_replace('_', ' ', $payment->type)) }} Payment
                    @if ($payment->period_start && $payment->period_end)
                        <br><small>Period: {{ $payment->period_start->format('d M Y') }} - {{ $payment->period_end->format('d M Y') }}</small>
                    @endif
                </td>
                <td>{{ ucfirst($payment->method) }}{{ $payment->transaction_reference ? ' ('.$payment->transaction_reference.')' : '' }}</td>
                <td>{{ $payment->paymentAccount->name }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if ($payment->discount_amount > 0)
                <tr>
                    <td colspan="3">Discount @if($payment->discount_reason) ({{ $payment->discount_reason }}) @endif</td>
                    <td class="text-right">-{{ number_format($payment->discount_amount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="3">Net Amount Paid</td>
                <td class="text-right">{{ number_format($payment->amount - $payment->discount_amount, 2) }}</td>
            </tr>
            @if ($payment->status === 'refunded')
                <tr>
                    <td colspan="3">Refunded on {{ $payment->refunded_at->format('d M Y') }}</td>
                    <td class="text-right">-{{ number_format($payment->refund_amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Thank you for choosing GirliGirl Gym &amp; Fitness — Mymensingh's First Ever &amp; Only Dedicated Ladies Gym.
    </div>
</body>
</html>
