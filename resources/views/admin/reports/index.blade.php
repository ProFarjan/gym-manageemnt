@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
    <h1 class="h4 mb-4">Reports</h1>

    <div class="row g-3">
        @foreach ($reports as $report)
            <div class="col-md-4">
                <a href="{{ route($report['route']) }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <h2 class="h6 mb-1">{{ $report['label'] }}</h2>
                            <p class="text-muted small mb-0">{{ $report['description'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
