<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $propertyIds = $this->filteredPropertyIds();
        $isFiltered  = session('filter_property_id') !== null;

        $query = Expense::with('property')->latest();

        if ($isFiltered) {
            $query->whereIn('property_id', $propertyIds);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('expense_date', $request->month)
                  ->whereYear('expense_date', $request->year);
        }

        $expenses   = $query->get();
        $properties = Property::whereIn('id', $propertyIds)->get();
        $totalAmount = $expenses->sum('amount');

        $totalThisMonthQuery = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year);

        if ($isFiltered) {
            $totalThisMonthQuery->whereIn('property_id', $propertyIds);
        }

        $totalThisMonth = $totalThisMonthQuery->sum('amount');

        $byCategory = $expenses->groupBy('category')
            ->map(fn($group) => $group->sum('amount'))
            ->sortByDesc(fn($v) => $v);

        return view('expenses.index', compact(
            'expenses', 'properties', 'totalAmount', 'byCategory', 'totalThisMonth'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'    => ['nullable', 'exists:properties,id'],
            'category'       => ['required', 'string'],
            'description'    => ['required', 'string', 'max:255'],
            'amount'         => ['required', 'numeric', 'min:0'],
            'vendor'         => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,mpesa,bank,cheque'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'expense_date'   => ['required', 'date'],
            'notes'          => ['nullable', 'string'],
        ]);

        $validated['account_id'] = auth()->user()->account_id;

        $expense = Expense::create($validated);

        try {
            AuditService::log(
                'expense.recorded',
                'Expense of ' . currency($validated['amount']) . ' recorded — ' . $validated['description'],
                $expense,
                ['amount' => $validated['amount'], 'category' => $validated['category'], 'vendor' => $validated['vendor'] ?? null]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return redirect()->route('expenses.index')
            ->with('success', 'Expense of ' . currency($validated['amount']) . ' recorded.');
    }

    public function destroy(Expense $expense)
    {
        try {
            AuditService::log(
                'expense.deleted',
                'Expense deleted — ' . $expense->description . ' (' . currency($expense->amount) . ')',
                null,
                ['amount' => $expense->amount, 'category' => $expense->category]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense removed.');
    }
}