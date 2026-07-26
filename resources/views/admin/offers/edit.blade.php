@extends('layouts.admin')

@section('title', 'Edit Offer')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $offer->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.offers.update', $offer) }}">
        @csrf
        @method('PUT')
        @include('admin.offers._form', ['offer' => $offer])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
