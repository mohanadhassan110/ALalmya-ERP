@extends('layouts.app')
@section('title', 'المنتجات - سنتر العالمية')
@section('page-title', 'إدارة المنتجات')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / المنتجات
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam-fill me-2 text-primary"></i>المنتجات</h4>
        <small class="text-muted">{{ $products->total() }} منتج — {{ \App\Models\Product::where('stock_quantity', '<=', 0)->count() }} نفد — {{ \App\Models\Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count() }} منخفض</small>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> إضافة منتج
    </a>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو SKU..." value="{{ request('search') }}" autofocus>
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select">
                    <option value="">كل الفئات</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> بحث</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الفئة</th>
                        <th>سعر التكلفة</th>
                        <th>سعر الجملة</th>
                        <th>سعر التجزئة</th>
                        <th>المخزون</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="text-muted">{{ $product->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            @if($product->sku)<small class="text-muted"><i class="bi bi-upc-scan me-1"></i>{{ $product->sku }}</small>@endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $product->category->name }}</span></td>
                        <td>{{ number_format($product->cost_price, 2) }}</td>
                        <td>{{ number_format($product->wholesale_price, 2) }}</td>
                        <td>{{ $product->retail_price > 0 ? number_format($product->retail_price, 2) : '—' }}</td>
                        <td>
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>نفد</span>
                            @elseif($product->stock_quantity <= 5)
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addStockModal{{ $product->id }}" title="توريد">
                                    <i class="bi bi-plus-lg"></i> توريد
                                </button>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-warning" title="تعديل"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline"
                                      onsubmit="event.preventDefault(); confirmAction('حذف المنتج', 'هل أنت متأكد من حذف {{ $product->name }}؟', this);">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <p>لا توجد منتجات</p>
                            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary mt-2">إضافة أول منتج</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $products->withQueryString()->links() }}</div>

{{-- Stock Modals --}}
@foreach($products as $product)
<div class="modal fade" id="addStockModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('products.add-stock', $product) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">توريد بضاعة: {{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        المخزون الحالي: <strong>{{ $product->stock_quantity }}</strong> — سعر التكلفة: <strong>{{ number_format($product->cost_price, 2) }} ج.م</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الكمية <span class="required">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سعر الشراء للقطعة <span class="required">*</span></label>
                        <input type="number" name="cost_price" class="form-control" step="0.01" value="{{ $product->cost_price }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">بدون مورد (بضاعة خالصة)</option>
                            @foreach(\App\Models\Supplier::orderBy('name')->get() as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }} (عليه: {{ number_format($sup->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_opening_stock" value="1" class="form-check-input" id="openStock{{ $product->id }}">
                        <label class="form-check-label" for="openStock{{ $product->id }}">بضاعة خالصة (رصيد افتتاحي)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control" placeholder="ملاحظات اختيارية">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> إضافة للمخزن</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
