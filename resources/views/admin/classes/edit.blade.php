@extends('layouts.admin')

@section('title', 'Edit Class')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $gymClass->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.classes.update', $gymClass) }}">
        @csrf
        @method('PUT')
        @include('admin.classes._form', ['gymClass' => $gymClass])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
