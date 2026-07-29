@extends('layouts.app')
@section('title', 'الموردين - سنتر العالمية')
@section('page-title', 'إدارة الموردين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>الموردين</h4>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> إضافة مورد</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
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
                        <td>{{ $supplier->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-decoration-none">{{ $supplier->name }}</a>
                        </td>
                        <td>{{ $supplier->phone ?? '—' }}</td>
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
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>


                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">لا يوجد موردين</td></tr>
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
{{-- Payment Modal --}}
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
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="description" class="form-control" placeholder="وصف الدفعة">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> تأكيد السداد</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
