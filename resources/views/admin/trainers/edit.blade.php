@extends('layouts.admin')

@section('title', 'Edit Trainer')

@section('content')
    <h1 class="h4 mb-4">Edit {{ $trainer->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.trainers.update', $trainer) }}">
        @csrf
        @method('PUT')
        <div class="card mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $trainer->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $trainer->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $trainer->phone) }}" class="form-control">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $trainer->is_active))>
                        <label for="is_active" class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization', $trainer->trainerProfile?->specialization) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact</label>
                    <input type="text" name="contact" value="{{ old('contact', $trainer->trainerProfile?->contact) }}" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="3">{{ old('bio', $trainer->trainerProfile?->bio) }}</textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.trainers.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
@endsection
