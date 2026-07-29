@extends('layouts.app')
@section('title', 'المنتجات - سنتر العالمية')
@section('page-title', 'إدارة المنتجات')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-box-seam-fill me-2 text-primary"></i>المنتجات</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> إضافة منتج جديد
    </a>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم..." value="{{ request('search') }}">
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
                        <th>الكمية</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            @if($product->sku)<small class="text-muted">{{ $product->sku }}</small>@endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $product->category->name }}</span></td>
                        <td>{{ number_format($product->cost_price, 2) }}</td>
                        <td>{{ number_format($product->wholesale_price, 2) }}</td>
                        <td>
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger">نفد</span>
                            @elseif($product->stock_quantity <= 5)
                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#addStockModal{{ $product->id }}">
                                    <i class="bi bi-plus-lg"></i> توريد
                                </button>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>


                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">لا توجد منتجات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $products->withQueryString()->links() }}</div>

@foreach($products as $product)
{{-- Add Stock Modal --}}
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
                    <div class="mb-3">
                        <label class="form-label">الكمية <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سعر الشراء للقطعة <span class="text-danger">*</span></label>
                        <input type="number" name="cost_price" class="form-control" step="0.01" value="{{ $product->cost_price }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المورد</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">بدون مورد (بضاعة خالصة)</option>
                            @foreach(\App\Models\Supplier::orderBy('name')->get() as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_opening_stock" value="1" class="form-check-input" id="openStock{{ $product->id }}">
                        <label class="form-check-label" for="openStock{{ $product->id }}">بضاعة خالصة (رصيد افتتاحي)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control">
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
