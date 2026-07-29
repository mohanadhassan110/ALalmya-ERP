@extends('layouts.app')
@section('title', 'سجل الفواتير - سنتر العالمية')
@section('page-title', 'سجل الفواتير')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i>سجل الفواتير</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.create-retail') }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i> تجزئة</a>
        <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-gold"><i class="bi bi-truck me-1"></i> جملة</a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">كل الأنواع</option>
                    <option value="retail" {{ request('type') == 'retail' ? 'selected' : '' }}>تجزئة</option>
                    <option value="wholesale" {{ request('type') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="من تاريخ">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="إلى تاريخ">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> بحث</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
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
                        <th>رقم الفاتورة</th>
                        <th>النوع</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td><a href="{{ route('invoices.show', $invoice) }}" class="fw-bold text-decoration-none">{{ $invoice->invoice_number }}</a></td>
                        <td><span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }}">{{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}</span></td>
                        <td>{{ $invoice->customer->name ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($invoice->total, 2) }}</td>
                        <td class="text-success">{{ number_format($invoice->paid, 2) }}</td>
                        <td class="text-danger">{{ number_format($invoice->remaining, 2) }}</td>
                        <td>
                            @if($invoice->payment_status === 'paid')
                                <span class="badge bg-success">مدفوعة</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning text-dark">جزئي</span>
                            @else
                                <span class="badge bg-danger">غير مدفوعة</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $invoice->created_at->format('Y/m/d H:i') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-info"><i class="bi bi-eye"></i></a>
                                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف الفاتورة؟ سيتم إرجاع المخزون.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">لا توجد فواتير</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $invoices->withQueryString()->links() }}</div>
@endsection
