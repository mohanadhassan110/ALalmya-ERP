<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * شاشة تسجيل الدخول للقسم المالي
     */
    public function loginForm()
    {
        return view('reports.login');
    }

    /**
     * التحقق من كلمة المرور المالية
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $financialHash = env('FINANCIAL_PASSWORD_HASH', \Illuminate\Support\Facades\Hash::make('Hassan@2004'));

        if (\Illuminate\Support\Facades\Hash::check($request->password, $financialHash)) {
            session(['financial_authenticated' => true]);
            $intendedUrl = session('financial_intended_url', route('reports.dashboard'));
            session()->forget('financial_intended_url');
            return redirect($intendedUrl);
        }

        return back()->with('error', 'كلمة المرور غير صحيحة');
    }

    /**
     * تسجيل خروج من القسم المالي
     */
    public function logout()
    {
        session()->forget('financial_authenticated');
        return redirect()->route('home')->with('success', 'تم تسجيل الخروج من القسم المالي');
    }

    /**
     * لوحة التحكم المالية المحمية
     * تعرض: قيمة المخزون / ديون الموردين / إجمالي المبيعات / الأرباح / المصروفات / صافي الربح
     */
    public function dashboard(Request $request)
    {
        // فترة التقرير
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // 1. إجمالي قيمة المخزون
        $inventoryValue = Product::selectRaw('SUM(stock_quantity * cost_price) as total')->value('total') ?? 0;

        // 2. إجمالي ديون الموردين
        $supplierDebts = Supplier::sum('current_balance');

        // 3. إجمالي المبيعات (في الفترة)
        $totalSales = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('total');

        // 4. إجمالي الأرباح (في الفترة) - مخفية عن العملاء
        $totalProfit = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->sum('profit');

        // 5. إجمالي المصروفات (في الفترة)
        $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');

        // 6. صافي الربح النهائي = الأرباح - المصروفات
        $netProfit = $totalProfit - $totalExpenses;

        // 7. إحصائيات إضافية
        $totalInvoices = Invoice::whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count();
        $retailInvoices = Invoice::where('type', 'retail')->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count();
        $wholesaleInvoices = Invoice::where('type', 'wholesale')->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59'])->count();

        // 8. المبالغ المستحقة من العملاء
        $customerDebts = Customer::where('balance', '>', 0)->sum('balance');
        $customerCredits = Customer::where('balance', '<', 0)->sum('balance');

        // 9. المنتجات الأكثر مبيعاً
        $topProducts = \App\Models\InvoiceItem::selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_sales')
            ->whereHas('invoice', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
            })
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // 10. المنتجات منخفضة المخزون
        $lowStockProducts = Product::where('stock_quantity', '<=', 5)
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->get();

        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();

        return view('reports.dashboard', compact(
            'inventoryValue', 'supplierDebts', 'totalSales', 'totalProfit',
            'totalExpenses', 'netProfit', 'totalInvoices', 'retailInvoices',
            'wholesaleInvoices', 'customerDebts', 'customerCredits',
            'topProducts', 'lowStockProducts', 'outOfStockProducts',
            'dateFrom', 'dateTo'
        ));
    }
}
