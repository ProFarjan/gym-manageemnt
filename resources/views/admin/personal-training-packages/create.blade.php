@extends('layouts.admin')

@section('title', 'New Package')

@section('content')
    <h1 class="h4 mb-4">New Personal Training Package</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.personal-training-packages.store') }}">
        @csrf
        @include('admin.personal-training-packages._form', ['package' => null])
        <button type="submit" class="btn btn-primary">Create Package</button>
        <a href="{{ route('admin.personal-training-packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
