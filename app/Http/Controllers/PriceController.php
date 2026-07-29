<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    /**
     * شاشة البحث السريع وتحديث الأسعار
     * التعديلات تؤثر على المعاملات المستقبلية فقط
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('prices.index', compact('products', 'categories'));
    }

    /**
     * تحديث سعر منتج واحد بسرعة (AJAX)
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'cost_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'retail_price' => 'nullable|numeric|min:0',
        ]);

        $updates = [];
        if ($request->has('cost_price')) $updates['cost_price'] = $request->cost_price;
        if ($request->has('wholesale_price')) $updates['wholesale_price'] = $request->wholesale_price;
        if ($request->has('retail_price')) $updates['retail_price'] = $request->retail_price;

        $product->update($updates);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'تم تحديث السعر بنجاح']);
        }

        return redirect()->route('prices.index')
            ->with('success', "تم تحديث أسعار {$product->name} بنجاح");
    }
}
