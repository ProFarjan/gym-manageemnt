@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $title }}</h1>
        <div>
            @if ($searched)
                <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" class="btn btn-outline-secondary" target="_blank">Export PDF</a>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'xlsx']) }}" class="btn btn-outline-secondary">Export Excel</a>
                <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" class="btn btn-outline-secondary" target="_blank">Print</a>
            @endif
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">All Reports</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="search" value="1">

                <div class="col-md-6">
                    <label class="form-label">Plan</label>
                    <select name="plan_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) $filters['plan_id'] === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All (Active + Expired)</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="expired" @selected($filters['status'] === 'expired')>Expired</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">To Due Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="text" name="to" value="{{ $filters['to'] }}" class="form-control report-datepicker"
                               placeholder="Select date" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">From Due Date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="text" name="from" value="{{ $filters['from'] }}" class="form-control report-datepicker"
                               placeholder="Select date" autocomplete="off">
                    </div>
                </div>

                <div class="col-12">
                    <p class="text-muted small mb-0">Leave both dates blank to see the default view: everyone overdue or due within the next 7 days.</p>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    @if ($searched)
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            @foreach ($headings as $heading)
                                <th>{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($headings) }}" class="text-center text-muted py-4">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-muted small mt-2">{{ count($rows) }} record(s)</p>
    @endif
@endsection
