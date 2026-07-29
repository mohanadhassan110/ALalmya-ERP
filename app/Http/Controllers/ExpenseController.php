<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        // مصروفات اليوم المحدد
        $expenses = Expense::whereDate('expense_date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $todayTotal = $expenses->sum('amount');

        // سجل المصروفات الكامل (حسب التاريخ)
        $history = Expense::selectRaw('expense_date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('expense_date')
            ->orderBy('expense_date', 'desc')
            ->limit(30)
            ->get();

        return view('expenses.index', compact('expenses', 'todayTotal', 'date', 'history'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create($request->only('amount', 'reason', 'expense_date', 'notes'));

        return redirect()->route('expenses.index', ['date' => $request->expense_date])
            ->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function destroy(Expense $expense)
    {
        $date = $expense->expense_date->format('Y-m-d');
        $expense->delete();

        return redirect()->route('expenses.index', ['date' => $date])
            ->with('success', 'تم حذف المصروف بنجاح');
    }
}
