<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $customers = $query->orderBy('name')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'type' => 'required|in:retail,wholesale',
            'notes' => 'nullable|string',
        ]);

        Customer::create($request->only('name', 'phone', 'address', 'type', 'notes'));

        return redirect()->route('customers.index')
            ->with('success', 'تم إضافة العميل بنجاح');
    }

    /**
     * عرض حساب العميل وسجل الحركات (دفتر الحساب)
     */
    public function show(Customer $customer)
    {
        $transactions = $customer->transactions()->orderBy('created_at', 'desc')->paginate(30);
        $invoices = $customer->invoices()->latest()->limit(10)->get();
        return view('customers.show', compact('customer', 'transactions', 'invoices'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'type' => 'required|in:retail,wholesale',
            'notes' => 'nullable|string',
        ]);

        $customer->update($request->only('name', 'phone', 'address', 'type', 'notes'));

        return redirect()->route('customers.index')
            ->with('success', 'تم تحديث بيانات العميل بنجاح');
    }

    /**
     * تسجيل سداد / سلفة من العميل
     */
    public function makePayment(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:payment,advance',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $customer) {
            // السداد يقلل الرصيد (المديونية)
            // السلفة أيضاً تقلل الرصيد (ممكن يخلي الرصيد سالب = له رصيد دائن)
            $customer->balance -= $request->amount;
            $customer->save();

            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'balance_after' => $customer->balance,
                'description' => $request->description ??
                    ($request->type === 'advance' ? 'دفعة مقدمة / سلفة' : 'سداد'),
            ]);
        });

        $message = $request->type === 'advance'
            ? "تم تسجيل سلفة بقيمة {$request->amount} جنيه"
            : "تم تسجيل سداد بقيمة {$request->amount} جنيه";

        return redirect()->route('customers.show', $customer)
            ->with('success', $message);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->balance != 0) {
            return redirect()->route('customers.index')
                ->with('error', 'لا يمكن حذف العميل لوجود رصيد في حسابه');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }
}
