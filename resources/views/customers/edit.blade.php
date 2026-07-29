@extends('layouts.app')
@section('title', 'تعديل عميل - سنتر العالمية')
@section('page-title', 'تعديل عميل')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil me-2"></i>تعديل: {{ $customer->name }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">اسم العميل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع العميل</label>
                        <select name="type" class="form-select" required>
                            <option value="wholesale" {{ $customer->type == 'wholesale' ? 'selected' : '' }}>جملة</option>
                            <option value="retail" {{ $customer->type == 'retail' ? 'selected' : '' }}>تجزئة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> تحديث</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
