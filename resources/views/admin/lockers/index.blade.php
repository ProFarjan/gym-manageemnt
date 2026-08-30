@extends('layouts.admin')

@section('title', 'Lockers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Lockers</h1>
        @can('lockers.create')
            <a href="{{ route('admin.lockers.create') }}" class="btn btn-primary">+ Add Locker</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="ajax-panel" data-panel-url="{{ route('admin.lockers.index') }}">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" data-ajax-param="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Locker #, Location, or Member">
            </div>
            <div class="col-auto">
                <select data-ajax-param="per_page" name="per_page" class="form-select">
                    @foreach ([10, 20, 50, 100] as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} per page</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>

        <div class="ajax-panel-results">
            @include('admin.lockers.partials._lockers-table', ['lockers' => $lockers])
        </div>
    </div>

    <div class="modal fade" id="lockerActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lockerActionModalLabel">&nbsp;</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="lockerActionModalBody">
                    <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="lockerDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Locker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="lockerDeleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="lockerDeleteName"></strong>? This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
