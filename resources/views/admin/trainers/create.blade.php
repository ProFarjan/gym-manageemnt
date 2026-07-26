@extends('layouts.admin')

@section('title', 'New Trainer')

@section('content')
    <h1 class="h4 mb-4">New Trainer</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.trainers.store') }}">
        @csrf
        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization') }}" class="form-control" placeholder="e.g. Weight Training, Cardio">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact</label>
                    <input type="text" name="contact" value="{{ old('contact') }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3">{{ old('bio') }}</textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Trainer</button>
        <a href="{{ route('admin.trainers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
