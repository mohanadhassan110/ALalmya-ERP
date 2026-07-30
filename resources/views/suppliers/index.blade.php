@extends('layouts.app')
@section('title', 'الموردين - سنتر العالمية')
@section('page-title', 'إدارة الموردين')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / الموردين
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-building me-2 text-primary"></i>الموردين</h4>
        <small class="text-muted">{{ $suppliers->count() }} مورد — إجمالي المديونيات: {{ number_format($suppliers->sum('current_balance'), 2) }} ج.م</small>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة مورد</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>اسم المورد</th>
                        <th>الهاتف</th>
                        <th>الرصيد الافتتاحي</th>
                        <th>الرصيد الحالي</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td class="fw-bold">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none">{{ $supplier->name }}</a>
                        </td>
                        <td>
                            @if($supplier->phone)
                                <div class="d-flex gap-1 align-items-center">
                                    <span>{{ $supplier->phone }}</span>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supplier->phone) }}" target="_blank" class="btn btn-sm btn-outline-success p-0 px-1" title="واتساب">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <a href="tel:{{ $supplier->phone }}" class="btn btn-sm btn-outline-primary p-0 px-1" title="اتصال">
                                        <i class="bi bi-telephone"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ number_format($supplier->initial_balance, 2) }} ج.م</td>
                        <td>
                            <span class="fw-bold {{ $supplier->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($supplier->current_balance, 2) }} ج.م
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i> السجل</a>
                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $supplier->id }}"><i class="bi bi-cash"></i> سداد</button>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="bi bi-building"></i>
                            <p>لا يوجد موردين</p>
                            <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary mt-2">إضافة أول مورد</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Summary --}}
@if($suppliers->count() > 0)
<div class="card mt-3">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="text-muted small">إجمالي الموردين</div>
                <div class="fw-bold fs-5">{{ $suppliers->count() }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">إجمالي المديونيات</div>
                <div class="fw-bold fs-5 text-danger">{{ number_format($suppliers->sum('current_balance'), 2) }} ج.م</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">إجمالي الأرصدة الافتتاحية</div>
                <div class="fw-bold fs-5">{{ number_format($suppliers->sum('initial_balance'), 2) }} ج.م</div>
            </div>
        </div>
    </div>
</div>
@endif

@foreach($suppliers as $supplier)
<div class="modal fade" id="payModal{{ $supplier->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.payment', $supplier) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">سداد دفعة لـ {{ $supplier->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">الرصيد الحالي: <strong>{{ number_format($supplier->current_balance, 2) }} ج.م</strong></div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="required">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="description" class="form-control" placeholder="وصف الدفعة">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> تأكيد السداد</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
