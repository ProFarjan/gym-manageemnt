@extends('layouts.admin')

@section('title', 'Edit Membership Plan')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $plan->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.membership-plans.update', $plan) }}">
        @csrf
        @method('PUT')
        @include('admin.membership-plans._form', ['plan' => $plan])
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.membership-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
