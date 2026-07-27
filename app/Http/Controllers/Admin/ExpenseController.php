<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('paymentAccount')->latest('expense_date')->paginate(20);

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $accounts = PaymentAccount::where('is_active', true)->get();

        return view('admin.expenses.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('status', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        $accounts = PaymentAccount::where('is_active', true)->get();

        return view('admin.expenses.edit', compact('expense', 'accounts'));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validateData($request));

        return redirect()->route('admin.expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('status', 'Expense deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'expense_date' => ['required', 'date'],
        ]);
    }
}
