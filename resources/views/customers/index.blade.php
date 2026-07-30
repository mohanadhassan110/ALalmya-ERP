@extends('layouts.app')
@section('title', 'العملاء - سنتر العالمية')
@section('page-title', 'إدارة العملاء')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / العملاء
@endsection

@section('content')
@php
    $totalDebt = $customers->where('balance', '>', 0)->sum('balance');
    $totalCredit = $customers->where('balance', '<', 0)->sum(fn($c) => abs($c->balance));
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>العملاء</h4>
        <small class="text-muted">{{ $customers->count() }} عميل — ديون: {{ number_format($totalDebt, 2) }} ج.م — أرصدة دائنة: {{ number_format($totalCredit, 2) }} ج.م</small>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة عميل</a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الهاتف..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    <option value="wholesale" {{ request('type') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                    <option value="retail" {{ request('type') == 'retail' ? 'selected' : '' }}>تجزئة</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> بحث</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                        <th>اسم العميل</th>
                        <th>النوع</th>
                        <th>الهاتف</th>
                        <th>الرصيد</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td class="fw-bold">
                            <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none">{{ $customer->name }}</a>
                        </td>
                        <td>
                            <span class="badge {{ $customer->type === 'wholesale' ? 'bg-warning text-dark' : 'bg-info' }}">
                                {{ $customer->type === 'wholesale' ? 'جملة' : 'تجزئة' }}
                            </span>
                        </td>
                        <td>
                            @if($customer->phone)
                                <div class="d-flex gap-1 align-items-center">
                                    <span>{{ $customer->phone }}</span>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="btn btn-sm btn-outline-success p-0 px-1" title="واتساب">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <a href="tel:{{ $customer->phone }}" class="btn btn-sm btn-outline-primary p-0 px-1" title="اتصال">
                                        <i class="bi bi-telephone"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->balance > 0)
                                <span class="fw-bold text-danger"><i class="bi bi-arrow-up-circle me-1"></i>عليه: {{ number_format($customer->balance, 2) }} ج.م</span>
                            @elseif($customer->balance < 0)
                                <span class="fw-bold text-success"><i class="bi bi-arrow-down-circle me-1"></i>له: {{ number_format(abs($customer->balance), 2) }} ج.م</span>
                            @else
                                <span class="text-muted">صفر</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info" title="الحساب"><i class="bi bi-eye"></i> الحساب</a>
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $customer->id }}" title="سداد"><i class="bi bi-cash"></i> سداد</button>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-warning" title="تعديل"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="bi bi-people"></i>
                            <p>لا يوجد عملاء</p>
                            <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary mt-2">إضافة أول عميل</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($customers as $customer)
<div class="modal fade" id="payModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.payment', $customer) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">سداد / سلفة - {{ $customer->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert {{ $customer->balance > 0 ? 'alert-danger' : ($customer->balance < 0 ? 'alert-success' : 'alert-info') }}">
                        @if($customer->balance > 0)
                            عليه: <strong>{{ number_format($customer->balance, 2) }} ج.م</strong>
                        @elseif($customer->balance < 0)
                            له (سلفة): <strong>{{ number_format(abs($customer->balance), 2) }} ج.م</strong>
                        @else
                            الرصيد: <strong>صفر</strong>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع العملية</label>
                        <select name="type" class="form-select" required>
                            <option value="payment">سداد (خصم من المديونية)</option>
                            <option value="advance">سلفة / دفعة مقدمة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="required">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> تأكيد</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
