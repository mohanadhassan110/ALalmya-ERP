<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'initial_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $initialBalance = $request->initial_balance ?? 0;

            $supplier = Supplier::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'initial_balance' => $initialBalance,
                'current_balance' => $initialBalance,
                'notes' => $request->notes,
            ]);

            // تسجيل الرصيد الافتتاحي في السجل
            if ($initialBalance > 0) {
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'type' => 'opening_balance',
                    'amount' => $initialBalance,
                    'balance_after' => $initialBalance,
                    'description' => 'رصيد افتتاحي',
                ]);
            }
        });

        return redirect()->route('suppliers.index')
            ->with('success', 'تم إضافة المورد بنجاح');
    }

    /**
     * عرض تفاصيل المورد وسجل الحركات الكامل
     */
    public function show(Supplier $supplier)
    {
        $transactions = $supplier->transactions()->orderBy('created_at', 'desc')->paginate(30);
        return view('suppliers.show', compact('supplier', 'transactions'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($request->only('name', 'phone', 'address', 'notes'));

        return redirect()->route('suppliers.index')
            ->with('success', 'تم تحديث بيانات المورد بنجاح');
    }

    /**
     * سداد دفعة للمورد
     */
    public function makePayment(Request $request, Supplier $supplier)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $supplier) {
            $supplier->current_balance -= $request->amount;
            $supplier->save();

            SupplierTransaction::create([
                'supplier_id' => $supplier->id,
                'type' => 'payment',
                'amount' => $request->amount,
                'balance_after' => $supplier->current_balance,
                'description' => $request->description ?? 'سداد دفعة',
            ]);
        });

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', "تم سداد {$request->amount} جنيه بنجاح");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->current_balance != 0) {
            return redirect()->route('suppliers.index')
                ->with('error', 'لا يمكن حذف المورد لوجود رصيد متبقي عليه');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح');
    }
}
