<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('customer');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats for the filter bar
        $todayStats = [
            'count' => Invoice::active()->whereDate('created_at', today())->count(),
            'total' => Invoice::active()->whereDate('created_at', today())->sum('total'),
            'paid' => Invoice::active()->whereDate('created_at', today())->sum('paid'),
        ];

        return view('invoices.index', compact('invoices', 'todayStats'));
    }

    /**
     * شاشة إنشاء فاتورة تجزئة (Track 1 - Retail)
     * السعر يتم إدخاله يدوياً بناءً على التفاوض الفعلي
     */
    public function createRetail()
    {
        $products = Product::with('category')->orderBy('name')->get();
        return view('invoices.create-retail', compact('products'));
    }

    /**
     * شاشة إنشاء فاتورة جملة (Track 2 - Wholesale)
     * تحميل أسعار الجملة الافتراضية مع إمكانية التعديل السريع
     */
    public function createWholesale()
    {
        $products = Product::with('category')->orderBy('name')->get();
        $customers = Customer::where('type', 'wholesale')->orderBy('name')->get();
        return view('invoices.create-wholesale', compact('products', 'customers'));
    }

    /**
     * حفظ فاتورة تجزئة
     * الربح = (سعر البيع اليدوي - سعر التكلفة) × الكمية → مخفي في الباك إند
     */
    public function storeRetail(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $totalProfit = 0;
            $items = [];

            // حساب كل عنصر
            foreach ($request->items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("الكمية المطلوبة من {$product->name} أكبر من المتاح ({$product->stock_quantity})");
                }

                $lineTotal = $itemData['quantity'] * $itemData['selling_price'];
                $lineProfit = ($itemData['selling_price'] - $product->cost_price) * $itemData['quantity'];

                $subtotal += $lineTotal;
                $totalProfit += $lineProfit;

                $items[] = [
                    'product' => $product,
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $product->cost_price,
                    'selling_price' => $itemData['selling_price'],
                    'line_total' => $lineTotal,
                    'line_profit' => $lineProfit,
                ];
            }

            $discount = $request->discount ?? 0;
            if ($discount > $subtotal) {
                throw new \Exception("الخصم لا يمكن أن يكون أكبر من المجموع الفرعي");
            }
            $total = $subtotal - $discount;
            $paid = $request->paid;
            $remaining = $total - $paid;

            // إنشاء الفاتورة
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('retail'),
                'type' => 'retail',
                'customer_id' => null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'remaining' => max(0, $remaining),
                'profit' => $totalProfit - $discount, // الربح بعد الخصم
                'payment_status' => $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            // إضافة عناصر الفاتورة وخصم المخزون
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                    'line_total' => $item['line_total'],
                    'line_profit' => $item['line_profit'],
                ]);

                // خصم من المخزون
                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            return $invoice;
        });

        // Return JSON for AJAX POS submissions
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice' => $invoice->load('items'),
                'message' => 'تم إنشاء فاتورة التجزئة بنجاح',
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'تم إنشاء فاتورة التجزئة بنجاح');
    }

    /**
     * حفظ فاتورة جملة
     * مع ربط حساب العميل وتطبيق نظام السلفة/الرصيد الدائن
     */
    public function storeWholesale(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'paid' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice = DB::transaction(function () use ($request) {
            $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);
            $subtotal = 0;
            $totalProfit = 0;
            $items = [];

            foreach ($request->items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                if ($product->stock_quantity < $itemData['quantity']) {
                    throw new \Exception("الكمية المطلوبة من {$product->name} أكبر من المتاح ({$product->stock_quantity})");
                }

                $lineTotal = $itemData['quantity'] * $itemData['selling_price'];
                $lineProfit = ($itemData['selling_price'] - $product->cost_price) * $itemData['quantity'];

                $subtotal += $lineTotal;
                $totalProfit += $lineProfit;

                $items[] = [
                    'product' => $product,
                    'quantity' => $itemData['quantity'],
                    'cost_price' => $product->cost_price,
                    'selling_price' => $itemData['selling_price'],
                    'line_total' => $lineTotal,
                    'line_profit' => $lineProfit,
                ];
            }

            $discount = $request->discount ?? 0;
            if ($discount > $subtotal) {
                throw new \Exception("الخصم لا يمكن أن يكون أكبر من المجموع الفرعي");
            }
            $total = $subtotal - $discount;

            // تطبيق نظام الرصيد الدائن / السلفة
            $paid = $request->paid;
            $effectiveTotal = $total;

            // إذا كان للعميل رصيد دائن (سلفة سابقة) → يتم خصمه تلقائياً
            if ($customer->balance < 0) {
                $creditAvailable = abs($customer->balance);
                $creditUsed = min($creditAvailable, $effectiveTotal);
                $paid += $creditUsed; // إضافة الرصيد الدائن المستخدم للمدفوع
            }

            $remaining = $total - $paid;

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber('wholesale'),
                'type' => 'wholesale',
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid' => $paid,
                'remaining' => max(0, $remaining),
                'profit' => $totalProfit - $discount,
                'payment_status' => $remaining <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            // إضافة عناصر الفاتورة وخصم المخزون
            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'selling_price' => $item['selling_price'],
                    'line_total' => $item['line_total'],
                    'line_profit' => $item['line_profit'],
                ]);

                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            // تحديث رصيد العميل: المديونية الجديدة هي الإجمالي ناقص ما دفعه العميل فعليا
            $customer->balance += ($total - $request->paid);
            $customer->save();

            // تسجيل حركة الفاتورة في دفتر حساب العميل
            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'type' => 'invoice',
                'amount' => $total,
                'balance_after' => $customer->balance,
                'description' => "فاتورة جملة رقم {$invoice->invoice_number}",
                'invoice_id' => $invoice->id,
            ]);

            // تسجيل حركة السداد إن وجد
            if ($request->paid > 0) {
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'payment',
                    'amount' => $request->paid,
                    'balance_after' => $customer->balance,
                    'description' => "سداد مع فاتورة رقم {$invoice->invoice_number}",
                    'invoice_id' => $invoice->id,
                ]);
            }

            return $invoice;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'invoice' => $invoice->load('items', 'customer'),
                'message' => 'تم إنشاء فاتورة الجملة بنجاح',
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'تم إنشاء فاتورة الجملة بنجاح');
    }

    /**
     * عرض تفاصيل الفاتورة
     */
    public function show(Invoice $invoice)
    {
        $invoice->load('items.product', 'customer');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * طباعة الفاتورة
     */
    public function print(Invoice $invoice)
    {
        $invoice->load('items.product', 'customer');
        return view('invoices.print', compact('invoice'));
    }

    /**
     * إلغاء الفاتورة (بدلاً من الحذف)
     * يستعيد المخزون ورصيد العميل ويحفظ سبب الإلغاء
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($invoice->isCancelled()) {
            $message = 'هذه الفاتورة ملغاة بالفعل';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $message], 422);
            }
            return back()->with('error', $message);
        }

        DB::transaction(function () use ($request, $invoice) {
            // إرجاع المخزون
            foreach ($invoice->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }

            // إرجاع رصيد العميل إن وجد
            if ($invoice->customer_id) {
                $customer = Customer::lockForUpdate()->find($invoice->customer_id);
                if ($customer) {
                    // عكس تأثير الفاتورة على الرصيد
                    $customer->balance -= $invoice->total;

                    // عكس تأثير السداد
                    $paymentTransaction = CustomerTransaction::where('invoice_id', $invoice->id)
                        ->where('type', 'payment')
                        ->first();
                    if ($paymentTransaction) {
                        $customer->balance += $paymentTransaction->amount;
                    }

                    $customer->save();

                    // تسجيل حركة الإلغاء
                    CustomerTransaction::create([
                        'customer_id' => $customer->id,
                        'type' => 'adjustment',
                        'amount' => $invoice->total,
                        'balance_after' => $customer->balance,
                        'description' => "إلغاء فاتورة رقم {$invoice->invoice_number} - {$request->cancellation_reason}",
                        'invoice_id' => $invoice->id,
                    ]);
                }
            }

            // تحديث حالة الفاتورة
            $invoice->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);
        });

        $message = "تم إلغاء الفاتورة {$invoice->invoice_number} وإرجاع المخزون بنجاح";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('invoices.index')
            ->with('success', $message);
    }

    /**
     * حذف الفاتورة (Legacy - redirects to cancel)
     * محفوظة للتوافقية لكن نفضل الإلغاء
     */
    public function destroy(Invoice $invoice)
    {
        // Redirect to show page with a prompt to cancel instead
        return redirect()->route('invoices.show', $invoice)
            ->with('error', 'لحماية السجلات المحاسبية، استخدم خيار "إلغاء الفاتورة" بدلاً من الحذف');
    }
}
