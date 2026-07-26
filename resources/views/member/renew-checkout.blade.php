@extends('layouts.member')

@section('title', 'Checkout')

@section('content')
    <h1 class="h4 mb-4">{{ ucfirst($checkout['gateway']) }} Checkout</h1>

    <div class="alert alert-warning" style="max-width: 480px;">
        This is a demo checkout — no real {{ ucfirst($checkout['gateway']) }} transaction will be made.
        Real gateway credentials have not been configured yet (see Settings once available).
    </div>

    <div class="card" style="max-width: 480px;">
        <div class="card-body">
            <p><strong>Amount:</strong> {{ number_format($checkout['amount'], 2) }} BDT</p>
            <form method="POST" action="{{ route('member.renew.confirm', $token) }}">
                @csrf
                <button type="submit" class="btn btn-success w-100">Simulate Successful Payment</button>
            </form>
        </div>
    </div>
@endsection
