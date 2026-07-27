@extends('layouts.admin')

@section('title', 'New Expense')

@section('content')
    <h1 class="h4 mb-4">New Expense</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.expenses.store') }}">
        @csrf
        @include('admin.expenses._form', ['expense' => null])
        <button type="submit" class="btn btn-primary">Save Expense</button>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
