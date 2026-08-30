@extends('layouts.admin')

@section('title', 'New Locker')

@section('content')
    <h1 class="h4 mb-4">New Locker</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.lockers.store') }}">
        @csrf
        @include('admin.lockers._form', ['locker' => null])
        <button type="submit" class="btn btn-primary">Add Locker</button>
        <a href="{{ route('admin.lockers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
