@extends('layouts.admin')

@section('title', 'Gallery')

@section('content')
    <h1 class="h4 mb-4">Gallery</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Add Image</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label">Image *</label>
                    <input type="file" name="image" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Caption</label>
                    <input type="text" name="caption" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="sort_order" value="0" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Add</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($images as $image)
            <div class="col-md-3">
                <div class="card h-100">
                    <img src="{{ asset('storage/'.$image->image_path) }}" class="card-img-top" style="height:150px;object-fit:cover;">
                    <div class="card-body">
                        <p class="small mb-2">{{ $image->caption ?? '—' }}</p>
                        <div class="d-flex justify-content-between">
                            <form method="POST" action="{{ route('admin.gallery.toggle', $image) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">{{ $image->is_active ? 'Hide' : 'Show' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.gallery.destroy', $image) }}" onsubmit="return confirm('Delete this image?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No images uploaded yet.</p>
        @endforelse
    </div>
@endsection
