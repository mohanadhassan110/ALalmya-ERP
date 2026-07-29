@extends('layouts.app')
@section('title', 'تعديل منتج - سنتر العالمية')
@section('page-title', 'تعديل منتج')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil me-2"></i>تعديل: {{ $product->name }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('products.update', $product) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">اسم المنتج <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">كود المنتج</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الفئة <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الكمية الحالية</label>
                            <input type="number" class="form-control" value="{{ $product->stock_quantity }}" disabled>
                            <small class="text-muted">لتغيير الكمية استخدم زر "توريد" من صفحة المنتجات</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" value="{{ old('cost_price', $product->cost_price) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر الجملة <span class="text-danger">*</span></label>
                            <input type="number" name="wholesale_price" class="form-control" step="0.01" value="{{ old('wholesale_price', $product->wholesale_price) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">سعر التجزئة</label>
                            <input type="number" name="retail_price" class="form-control" step="0.01" value="{{ old('retail_price', $product->retail_price) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">وصف</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> تحديث</button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
