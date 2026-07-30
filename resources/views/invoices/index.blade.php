@extends('layouts.app')
@section('title', 'سجل الفواتير - سنتر العالمية')
@section('page-title', 'سجل الفواتير')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / سجل الفواتير
@endsection

@section('content')
{{-- Header with today's stats --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt me-2 text-primary"></i>سجل الفواتير</h4>
        @if(isset($todayStats))
        <small class="text-muted">اليوم: {{ $todayStats['count'] }} فاتورة — {{ number_format($todayStats['total'], 2) }} ج.م — محصّل: {{ number_format($todayStats['paid'], 2) }} ج.م</small>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.create-retail') }}" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i> تجزئة</a>
        <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-gold"><i class="bi bi-truck me-1"></i> جملة</a>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">بحث</label>
                <input type="text" name="search" class="form-control" placeholder="رقم فاتورة أو عميل..." value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">النوع</label>
                <select name="type" class="form-select">
                    <option value="">الكل</option>
                    <option value="retail" {{ request('type') == 'retail' ? 'selected' : '' }}>تجزئة</option>
                    <option value="wholesale" {{ request('type') == 'wholesale' ? 'selected' : '' }}>جملة</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">الحالة</label>
                <select name="payment_status" class="form-select">
                    <option value="">الكل</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>جزئي</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> بحث</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
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
                    <tr class="{{ $invoice->isCancelled() ? 'opacity-50' : '' }}">
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-bold text-decoration-none">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }}">
                                {{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->customer)
                                <a href="{{ route('customers.show', $invoice->customer) }}" class="text-decoration-none">{{ $invoice->customer->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold">{{ number_format($invoice->total, 2) }}</td>
                        <td class="text-success">{{ number_format($invoice->paid, 2) }}</td>
                        <td class="text-danger">{{ number_format($invoice->remaining, 2) }}</td>
                        <td>
                            @if($invoice->isCancelled())
                                <span class="badge bg-secondary">ملغاة</span>
                            @elseif($invoice->payment_status === 'paid')
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
                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-outline-info" title="عرض"><i class="bi bi-eye"></i></a>
                                @if(!$invoice->isCancelled())
                                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark" title="طباعة"><i class="bi bi-printer"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="bi bi-receipt"></i>
                            <p>لا توجد فواتير</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $invoices->withQueryString()->links() }}</div>
@endsection
