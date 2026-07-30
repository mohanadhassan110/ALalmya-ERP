@extends('layouts.app')
@section('title', 'فاتورة ' . $invoice->invoice_number)
@section('page-title', 'تفاصيل الفاتورة')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / <a href="{{ route('invoices.index') }}">الفواتير</a> / {{ $invoice->invoice_number }}
@endsection

@section('content')
@if($invoice->isCancelled())
<div class="alert alert-danger mb-3">
    <div class="d-flex align-items-center">
        <i class="bi bi-x-circle-fill fs-4 me-3"></i>
        <div>
            <strong>فاتورة ملغاة</strong>
            <div class="small">تم الإلغاء بتاريخ {{ $invoice->cancelled_at?->format('Y/m/d H:i') }} — السبب: {{ $invoice->cancellation_reason }}</div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-md-8">
        <div class="card {{ $invoice->isCancelled() ? 'opacity-75' : '' }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-receipt me-2"></i>فاتورة رقم: <strong>{{ $invoice->invoice_number }}</strong>
                </span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }} fs-6">
                        {{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}
                    </span>
                    @if($invoice->isCancelled())
                        <span class="badge bg-danger fs-6">ملغاة</span>
                    @endif
                </div>
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
                                <td>{{ number_format($item->selling_price, 2) }} ج.م</td>
                                <td class="fw-bold">{{ number_format($item->line_total, 2) }} ج.م</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-3 no-print d-flex gap-2 flex-wrap">
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-dark">
                <i class="bi bi-printer me-1"></i> طباعة
            </a>
            @if($invoice->isActive())
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="bi bi-x-circle me-1"></i> إلغاء الفاتورة
            </button>
            @endif
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i> العودة
            </a>
            @if($invoice->type === 'retail')
            <a href="{{ route('invoices.create-retail') }}" class="btn btn-gold">
                <i class="bi bi-cart-plus me-1"></i> فاتورة تجزئة جديدة
            </a>
            @else
            <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-gold">
                <i class="bi bi-truck me-1"></i> فاتورة جملة جديدة
            </a>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2"></i>تفاصيل الفاتورة</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">التاريخ</td><td>{{ $invoice->created_at->format('Y/m/d H:i') }}</td></tr>
                    @if($invoice->customer)
                    <tr>
                        <td class="text-muted">العميل</td>
                        <td class="fw-bold">
                            <a href="{{ route('customers.show', $invoice->customer) }}" class="text-decoration-none">
                                {{ $invoice->customer->name }}
                            </a>
                            @if($invoice->customer->phone)
                            <br><a href="tel:{{ $invoice->customer->phone }}" class="text-muted small"><i class="bi bi-telephone"></i> {{ $invoice->customer->phone }}</a>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr><td class="text-muted">المجموع</td><td>{{ number_format($invoice->subtotal, 2) }} ج.م</td></tr>
                    @if($invoice->discount > 0)
                    <tr><td class="text-muted">الخصم</td><td class="text-danger">-{{ number_format($invoice->discount, 2) }} ج.م</td></tr>
                    @endif
                    <tr class="fs-5"><td class="text-muted fw-bold">الإجمالي</td><td class="fw-bold" style="color: var(--accent);">{{ number_format($invoice->total, 2) }} ج.م</td></tr>
                    <tr><td class="text-muted">المدفوع</td><td class="text-success fw-bold">{{ number_format($invoice->paid, 2) }} ج.م</td></tr>
                    <tr><td class="text-muted">المتبقي</td><td class="text-danger fw-bold">{{ number_format($invoice->remaining, 2) }} ج.م</td></tr>
                    <tr>
                        <td class="text-muted">الحالة</td>
                        <td>
                            @if($invoice->isCancelled())
                                <span class="badge bg-danger fs-6">ملغاة</span>
                            @elseif($invoice->payment_status === 'paid')
                                <span class="badge bg-success fs-6">مدفوعة</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge bg-warning text-dark fs-6">جزئي</span>
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

{{-- Cancel Modal --}}
@if($invoice->isActive())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-confirm">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>إلغاء الفاتورة</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-1"></i>
                        سيتم إرجاع المخزون {{ $invoice->customer ? 'وتعديل رصيد العميل' : '' }} عند الإلغاء.
                        <strong>لا يمكن التراجع عن هذا الإجراء.</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">سبب الإلغاء <span class="required">*</span></label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="اكتب سبب إلغاء الفاتورة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">تراجع</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i> تأكيد الإلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
