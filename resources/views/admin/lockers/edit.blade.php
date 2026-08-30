@extends('layouts.admin')

@section('title', 'Edit Locker')

@section('content')
    <h1 class="h4 mb-4">Edit Locker #{{ $locker->locker_number }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.lockers.update', $locker) }}">
        @csrf
        @method('PUT')
        @include('admin.lockers._form', ['locker' => $locker])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.lockers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
