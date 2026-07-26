@extends('layouts.admin')

@section('title', 'New Membership Plan')

@section('content')
    <h1 class="h4 mb-4">New Membership Plan</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.membership-plans.store') }}">
        @csrf
        @include('admin.membership-plans._form', ['plan' => null])
        <button type="submit" class="btn btn-primary">Create Plan</button>
        <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
