@extends('layouts.member')

@section('title', 'Renew Membership')

@section('content')
    <h1 class="h4 mb-4">Renew Membership</h1>

    <div class="card" style="max-width: 480px;">
        <div class="card-body">
            <p><strong>Plan:</strong> {{ $member->membershipPlan->name }}</p>
            <p><strong>Current Due Date:</strong> {{ $member->due_date?->format('d M Y') ?? '—' }}</p>
            <p><strong>Amount Due:</strong> {{ number_format($member->membershipPlan->price, 2) }} BDT</p>

            <hr>

            <p class="text-muted small">Pay online to renew instantly:</p>

            <form method="POST" action="{{ route('member.renew.initiate') }}" class="d-inline">
                @csrf
                <input type="hidden" name="gateway" value="bkash">
                <button type="submit" class="btn btn-danger">Pay with bKash</button>
            </form>
            <form method="POST" action="{{ route('member.renew.initiate') }}" class="d-inline">
                @csrf
                <input type="hidden" name="gateway" value="nagad">
                <button type="submit" class="btn btn-warning">Pay with Nagad</button>
            </form>
        </div>
    </div>
@endsection
