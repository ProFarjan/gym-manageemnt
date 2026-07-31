@extends('layouts.admin')

@section('title', 'Members')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Members</h1>
        @can('members.create')
            <a href="{{ route('admin.members.create') }}" class="btn btn-primary">+ New Registration</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="ajax-panel" data-panel-url="{{ route('admin.members.index') }}">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-auto">
                <input type="text" data-ajax-param="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, Admission ID or Mobile">
            </div>
            <div class="col-auto">
                <select data-ajax-param="status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'active', 'expired', 'closed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
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
            @include('admin.members.partials._members-table', ['members' => $members])
        </div>
    </div>

    <div class="modal fade" id="memberActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberActionModalLabel">&nbsp;</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="memberActionModalBody">
                    <div class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="memberDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="memberDeleteForm">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="memberDeleteName"></strong>'s pending registration? This cannot be undone.
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
