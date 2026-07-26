@extends('layouts.admin')

@section('title', 'New Class')

@section('content')
    <h1 class="h4 mb-4">New Class</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.classes.store') }}">
        @csrf
        @include('admin.classes._form', ['gymClass' => null])
        <button type="submit" class="btn btn-primary">Create Class</button>
        <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
