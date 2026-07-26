@extends('layouts.admin')

@section('title', 'Membership Plans')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Membership Plans</h1>
        @can('membership_plans.create')
            <a href="{{ route('admin.membership-plans.create') }}" class="btn btn-primary">+ New Plan</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Admission Fee</th>
                        <th>Admission Discount</th>
                        <th>Members</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->is_lifetime ? 'Lifetime' : $plan->duration_in_months.' month(s)' }}</td>
                            <td>{{ number_format($plan->price, 2) }} BDT</td>
                            <td>{{ $plan->admission_free ? 'Free' : number_format($plan->admission_fee, 2).' BDT' }}</td>
                            <td>{{ number_format($plan->admission_discount, 2) }} BDT</td>
                            <td>{{ $plan->members_count }}</td>
                            <td>
                                <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @can('membership_plans.update')
                                    <a href="{{ route('admin.membership-plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('membership_plans.delete')
                                    <form method="POST" action="{{ route('admin.membership-plans.destroy', $plan) }}" class="d-inline" onsubmit="return confirm('Delete this plan?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
