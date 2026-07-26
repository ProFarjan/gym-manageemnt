@extends('layouts.member')

@section('title', 'Payment Successful')

@section('content')
    <div class="alert alert-success" style="max-width: 480px;">
        Payment successful! Your membership has been renewed.
    </div>

    <div class="card" style="max-width: 480px;">
        <div class="card-body">
            <p><strong>Receipt No:</strong> {{ $payment->receipt_number }}</p>
            <p><strong>Amount:</strong> {{ number_format($payment->amount, 2) }} BDT</p>
            <p><strong>New Due Date:</strong> {{ $payment->period_end?->format('d M Y') }}</p>
            <a href="{{ route('member.dashboard') }}" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
@endsection
