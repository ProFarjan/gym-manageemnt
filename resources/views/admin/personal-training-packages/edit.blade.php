@extends('layouts.admin')

@section('title', 'Edit Package')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $package->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.personal-training-packages.update', $package) }}">
        @csrf
        @method('PUT')
        @include('admin.personal-training-packages._form', ['package' => $package])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.personal-training-packages.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
