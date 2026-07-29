@extends('layouts.app')
@section('title', $supplier->name . ' - سجل المورد')
@section('page-title', 'سجل المورد: ' . $supplier->name)

@section('content')
<div class="row g-4">
    {{-- Supplier Info Card --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-building me-2"></i>بيانات المورد</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">الاسم</td><td class="fw-bold">{{ $supplier->name }}</td></tr>
                    <tr><td class="text-muted">الهاتف</td><td>{{ $supplier->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">العنوان</td><td>{{ $supplier->address ?? '—' }}</td></tr>
                    <tr><td class="text-muted">الرصيد الافتتاحي</td><td>{{ number_format($supplier->initial_balance, 2) }} ج.م</td></tr>
                    <tr>
                        <td class="text-muted">الرصيد الحالي</td>
                        <td class="fw-bold fs-5 {{ $supplier->current_balance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($supplier->current_balance, 2) }} ج.م
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>سجل الحركات</span>
                <span class="badge bg-info">{{ $transactions->total() }} حركة</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>نوع العملية</th>
                                <th>المبلغ</th>
                                <th>الرصيد بعدها</th>
                                <th>الوصف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            <tr>
                                <td class="text-muted small">{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                                <td>
                                    @if($tx->type === 'purchase')
                                        <span class="badge bg-danger">{{ $tx->type_name }}</span>
                                    @elseif($tx->type === 'payment')
                                        <span class="badge bg-success">{{ $tx->type_name }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $tx->type_name }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($tx->amount, 2) }} ج.م</td>
                                <td>{{ number_format($tx->balance_after, 2) }} ج.م</td>
                                <td class="text-muted small">{{ $tx->description ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">لا توجد حركات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
