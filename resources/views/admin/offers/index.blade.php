@extends('layouts.admin')

@section('title', 'Offers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Offers</h1>
        @can('offers.create')
            <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">+ New Offer</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Period</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td>{{ $offer->name }}</td>
                            <td>{{ $offer->start_date->format('d M Y') }} – {{ $offer->end_date->format('d M Y') }}</td>
                            <td>
                                {{ $offer->discount_type === 'percentage' ? $offer->discount_amount.'%' : number_format($offer->discount_amount, 2).' BDT' }}
                            </td>
                            <td>
                                @if ($offer->isCurrentlyRunning())
                                    <span class="badge bg-success">Running</span>
                                @elseif ($offer->is_active)
                                    <span class="badge bg-warning">Scheduled/Expired</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @can('offers.update')
                                    <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('offers.delete')
                                    <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" class="d-inline" onsubmit="return confirm('Delete this offer?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No offers created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
