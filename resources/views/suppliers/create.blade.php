@extends('layouts.app')
@section('title', 'إضافة مورد - سنتر العالمية')
@section('page-title', 'إضافة مورد جديد')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-2"></i>إضافة مورد جديد</div>
            <div class="card-body">
                <form method="POST" action="{{ route('suppliers.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">اسم المورد <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرصيد الافتتاحي (المديونية السابقة)</label>
                        <input type="number" name="initial_balance" class="form-control" step="0.01" value="{{ old('initial_balance', 0) }}" min="0">
                        <small class="text-muted">المبلغ المستحق للمورد عند بدء استخدام النظام</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> حفظ</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
