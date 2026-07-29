@extends('layouts.app')
@section('title', 'تحديث الأسعار - سنتر العالمية')
@section('page-title', 'تحديث الأسعار السريع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-currency-dollar me-2 text-primary"></i>تحديث الأسعار</h4>
    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>التعديلات تؤثر على المعاملات المستقبلية فقط</span>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الكود..." value="{{ request('search') }}">
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
                <a href="{{ route('prices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                        <th>المنتج</th>
                        <th>الفئة</th>
                        <th style="width: 160px;">سعر التكلفة</th>
                        <th style="width: 160px;">سعر الجملة</th>
                        <th style="width: 160px;">سعر التجزئة</th>
                        <th style="width: 100px;">حفظ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr id="row-{{ $product->id }}">
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            @if($product->sku)<small class="text-muted">{{ $product->sku }}</small>@endif
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $product->category->name }}</span></td>
                        <td>
                            <input type="number" class="form-control form-control-sm" id="cost-{{ $product->id }}" value="{{ $product->cost_price }}" step="0.01" min="0">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm" id="wholesale-{{ $product->id }}" value="{{ $product->wholesale_price }}" step="0.01" min="0">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm" id="retail-{{ $product->id }}" value="{{ $product->retail_price }}" step="0.01" min="0">
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success w-100" onclick="updatePrice({{ $product->id }})">
                                <i class="bi bi-check-lg"></i> حفظ
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا توجد منتجات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updatePrice(productId) {
        const cost = document.getElementById('cost-' + productId).value;
        const wholesale = document.getElementById('wholesale-' + productId).value;
        const retail = document.getElementById('retail-' + productId).value;

        fetch(`/prices/${productId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                cost_price: cost,
                wholesale_price: wholesale,
                retail_price: retail,
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('row-' + productId);
                row.style.background = '#d4edda';
                setTimeout(() => row.style.background = '', 1500);
            }
        })
        .catch(err => alert('حدث خطأ أثناء التحديث'));
    }
</script>
@endsection
