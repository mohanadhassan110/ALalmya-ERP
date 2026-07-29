@extends('layouts.app')
@section('title', 'فاتورة ' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة')

@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-receipt me-2"></i>فاتورة رقم: <strong>{{ $invoice->invoice_number }}</strong>
                </span>
                <span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }} fs-6">
                    {{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>سعر البيع</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="fw-bold">{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->selling_price, 2) }}</td>
                                <td class="fw-bold">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Print Button --}}
        <div class="mt-3 no-print">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-dark"><i class="bi bi-printer me-1"></i> طباعة الفاتورة</a>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-right me-1"></i> العودة</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>تفاصيل الفاتورة</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">التاريخ</td><td>{{ $invoice->created_at->format('Y/m/d H:i') }}</td></tr>
                    @if($invoice->customer)
                    <tr><td class="text-muted">العميل</td><td class="fw-bold"><a href="{{ route('customers.show', $invoice->customer) }}">{{ $invoice->customer->name }}</a></td></tr>
                    @endif
                    <tr><td class="text-muted">المجموع</td><td>{{ number_format($invoice->subtotal, 2) }} ج.م</td></tr>
                    @if($invoice->discount > 0)
                    <tr><td class="text-muted">الخصم</td><td class="text-danger">-{{ number_format($invoice->discount, 2) }} ج.م</td></tr>
                    @endif
                    <tr class="fs-5"><td class="text-muted fw-bold">الإجمالي</td><td class="fw-bold text-primary">{{ number_format($invoice->total, 2) }} ج.م</td></tr>
                    <tr><td class="text-muted">المدفوع</td><td class="text-success fw-bold">{{ number_format($invoice->paid, 2) }} ج.م</td></tr>
                    <tr><td class="text-muted">المتبقي</td><td class="text-danger fw-bold">{{ number_format($invoice->remaining, 2) }} ج.م</td></tr>
                    <tr>
                        <td class="text-muted">حالة الدفع</td>
                        <td>
                            @if($invoice->payment_status === 'paid')
                                <span class="badge bg-success fs-6">مدفوعة بالكامل</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning text-dark fs-6">مدفوعة جزئياً</span>
                            @else
                                <span class="badge bg-danger fs-6">غير مدفوعة</span>
                            @endif
                        </td>
                    </tr>
                    @if($invoice->notes)
                    <tr><td class="text-muted">ملاحظات</td><td>{{ $invoice->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
