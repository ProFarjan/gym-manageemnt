@extends('layouts.admin')

@section('title', 'Edit Expense')

@section('content')
    <h1 class="h4 mb-4">Edit Expense</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.expenses.update', $expense) }}">
        @csrf
        @method('PUT')
        @include('admin.expenses._form', ['expense' => $expense])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
