@extends('layouts.admin')

@section('title', 'Expenses')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Expenses</h1>
        @can('payments.create')
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">+ New Expense</a>
        @endcan
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Account</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->description }}</td>
                            <td>{{ $expense->category ?? '—' }}</td>
                            <td>{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ $expense->paymentAccount->name }}</td>
                            <td>
                                @can('payments.update')
                                    <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @endcan
                                @can('payments.delete')
                                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('Delete this expense?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No expenses recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $expenses->links() }}
    </div>
@endsection
