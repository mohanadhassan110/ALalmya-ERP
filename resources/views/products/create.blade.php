@extends('layouts.app')
@section('title', 'إضافة منتج - سنتر العالمية')
@section('page-title', 'إضافة منتج جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-2"></i>إضافة منتج جديد</div>
            <div class="card-body">
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">كود المنتج</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الفئة <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">اختر الفئة</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الكمية الأولية <span class="text-danger">*</span></label>
                            <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" min="0" required>
                        </div>

                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="bi bi-currency-dollar me-1"></i>الأسعار</h6></div>

                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة (الشراء) <span class="text-danger">*</span></label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" value="{{ old('cost_price', 0) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر الجملة <span class="text-danger">*</span></label>
                            <input type="number" name="wholesale_price" class="form-control" step="0.01" value="{{ old('wholesale_price', 0) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التجزئة المقترح</label>
                            <input type="number" name="retail_price" class="form-control" step="0.01" value="{{ old('retail_price', 0) }}">
                        </div>

                        <div class="col-12"><hr><h6 class="fw-bold text-primary"><i class="bi bi-truck me-1"></i>مصدر البضاعة</h6></div>

                        <div class="col-md-6">
                            <label class="form-label">المورد</label>
                            <select name="supplier_id" class="form-select" id="supplierSelect">
                                <option value="">بدون مورد (بضاعة خالصة)</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_opening_stock" value="1" class="form-check-input" id="openingStock" {{ old('is_opening_stock') ? 'checked' : '' }}>
                                <label class="form-check-label" for="openingStock">بضاعة خالصة (رصيد افتتاحي - لا تضاف لمديونية المورد)</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">وصف / ملاحظات</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i> حفظ المنتج</button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-lg">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
