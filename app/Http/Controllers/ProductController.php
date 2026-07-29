<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockEntry;
use App\Models\SupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name')->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * إضافة منتج جديد مع بضاعة أولية
     * يدعم: بضاعة من مورد (تزيد مديونيته) أو بضاعة خالصة (رصيد افتتاحي)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_opening_stock' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            // إنشاء المنتج
            $product = Product::create([
                'name' => $request->name,
                'sku' => $request->sku,
                'category_id' => $request->category_id,
                'cost_price' => $request->cost_price,
                'wholesale_price' => $request->wholesale_price,
                'retail_price' => $request->retail_price ?? 0,
                'stock_quantity' => $request->stock_quantity,
                'description' => $request->description,
            ]);

            // إضافة حركة مخزن إذا كانت الكمية أكبر من صفر
            if ($request->stock_quantity > 0) {
                $totalCost = $request->stock_quantity * $request->cost_price;
                $isOpeningStock = $request->boolean('is_opening_stock') || !$request->supplier_id;

                $stockEntry = StockEntry::create([
                    'product_id' => $product->id,
                    'supplier_id' => $isOpeningStock ? null : $request->supplier_id,
                    'quantity' => $request->stock_quantity,
                    'cost_price' => $request->cost_price,
                    'total_cost' => $totalCost,
                    'is_opening_stock' => $isOpeningStock,
                    'notes' => $isOpeningStock ? 'بضاعة خالصة - رصيد افتتاحي' : 'بضاعة جديدة من المورد',
                ]);

                // إذا كانت البضاعة من مورد → زيادة مديونية المورد
                if (!$isOpeningStock && $request->supplier_id) {
                    $supplier = Supplier::findOrFail($request->supplier_id);
                    $supplier->current_balance += $totalCost;
                    $supplier->save();

                    SupplierTransaction::create([
                        'supplier_id' => $supplier->id,
                        'type' => 'purchase',
                        'amount' => $totalCost,
                        'balance_after' => $supplier->current_balance,
                        'description' => "شراء {$request->stock_quantity} قطعة من {$product->name}",
                    ]);
                }
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->update($request->only(
            'name', 'sku', 'category_id', 'cost_price',
            'wholesale_price', 'retail_price', 'description'
        ));

        return redirect()->route('products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * إضافة بضاعة جديدة لمنتج موجود (توريد)
     */
    public function addStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'is_opening_stock' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $product) {
            $totalCost = $request->quantity * $request->cost_price;
            $isOpeningStock = $request->boolean('is_opening_stock') || !$request->supplier_id;

            // تحديث سعر التكلفة والكمية
            $product->cost_price = $request->cost_price;
            $product->stock_quantity += $request->quantity;
            $product->save();

            // تسجيل حركة المخزن
            StockEntry::create([
                'product_id' => $product->id,
                'supplier_id' => $isOpeningStock ? null : $request->supplier_id,
                'quantity' => $request->quantity,
                'cost_price' => $request->cost_price,
                'total_cost' => $totalCost,
                'is_opening_stock' => $isOpeningStock,
                'notes' => $request->notes,
            ]);

            // تحديث مديونية المورد
            if (!$isOpeningStock && $request->supplier_id) {
                $supplier = Supplier::findOrFail($request->supplier_id);
                $supplier->current_balance += $totalCost;
                $supplier->save();

                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'type' => 'purchase',
                    'amount' => $totalCost,
                    'balance_after' => $supplier->current_balance,
                    'description' => "توريد {$request->quantity} قطعة من {$product->name}",
                ]);
            }
        });

        return redirect()->route('products.index')
            ->with('success', "تم إضافة {$request->quantity} قطعة للمخزن بنجاح");
    }

    /**
     * API: بحث سريع عن المنتجات (للاستخدام في الفواتير)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $products = Product::where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'لا يمكن حذف المنتج لوجود فواتير مرتبطة به');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }
}
