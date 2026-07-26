@extends('layouts.admin')

@section('title', 'New Offer')

@section('content')
    <h1 class="h4 mb-4">New Offer</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.offers.store') }}">
        @csrf
        @include('admin.offers._form', ['offer' => null])
        <button type="submit" class="btn btn-primary">Create Offer</button>
        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
