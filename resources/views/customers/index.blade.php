@extends('layouts.app')
@section('title', 'العملاء - سنتر العالمية')
@section('page-title', 'إدارة العملاء')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>العملاء</h4>
    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة عميل</a>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="بحث بالاسم..." value="{{ request('search') }}">
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
                        <th>#</th>
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
                        <td>{{ $customer->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('customers.show', $customer) }}" class="text-decoration-none">{{ $customer->name }}</a>
                        </td>
                        <td>
                            <span class="badge {{ $customer->type === 'wholesale' ? 'bg-warning text-dark' : 'bg-info' }}">
                                {{ $customer->type === 'wholesale' ? 'جملة' : 'تجزئة' }}
                            </span>
                        </td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>
                            @if($customer->balance > 0)
                                <span class="fw-bold text-danger">عليه: {{ number_format($customer->balance, 2) }} ج.م</span>
                            @elseif($customer->balance < 0)
                                <span class="fw-bold text-success">له: {{ number_format(abs($customer->balance), 2) }} ج.م</span>
                            @else
                                <span class="text-muted">صفر</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i> الحساب</a>
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $customer->id }}"><i class="bi bi-cash"></i> سداد/سلفة</button>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            </div>


                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا يوجد عملاء</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($customers as $customer)
{{-- Payment/Advance Modal --}}
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
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> تأكيد</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
