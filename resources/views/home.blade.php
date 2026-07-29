@extends('layouts.app')
@section('title', 'الصفحة الرئيسية - سنتر العالمية')
@section('page-title', 'الصفحة الرئيسية')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #fff; border-radius: 20px;">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-2">مرحباً بك في سنتر العالمية للمفروشات</h2>
                        <p class="mb-3 opacity-75">نظام إدارة متكامل لنقاط البيع والمحاسبة والمخزون</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('invoices.create-retail') }}" class="btn btn-gold btn-lg">
                                <i class="bi bi-cart-plus-fill me-1"></i> فاتورة تجزئة جديدة
                            </a>
                            <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-truck me-1"></i> فاتورة جملة جديدة
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bi bi-shop" style="font-size: 8rem; opacity: 0.15;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Quick Actions --}}
    <div class="col-md-3 col-6">
        <a href="{{ route('products.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">المنتجات</div>
                        <div class="stat-value">{{ \App\Models\Product::count() }}</div>
                    </div>
                    <i class="bi bi-box-seam-fill stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('invoices.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">فواتير اليوم</div>
                        <div class="stat-value">{{ \App\Models\Invoice::whereDate('created_at', today())->count() }}</div>
                    </div>
                    <i class="bi bi-receipt stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('customers.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">العملاء</div>
                        <div class="stat-value">{{ \App\Models\Customer::count() }}</div>
                    </div>
                    <i class="bi bi-people-fill stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="{{ route('suppliers.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">الموردين</div>
                        <div class="stat-value">{{ \App\Models\Supplier::count() }}</div>
                    </div>
                    <i class="bi bi-building stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Recent Invoices --}}
<div class="row g-4 mt-2">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>آخر الفواتير</span>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>رقم الفاتورة</th>
                                <th>النوع</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Invoice::latest()->limit(8)->get() as $invoice)
                            <tr>
                                <td><a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none fw-bold">{{ $invoice->invoice_number }}</a></td>
                                <td>
                                    <span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }}">
                                        {{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ number_format($invoice->total, 2) }} ج.م</td>
                                <td>
                                    @if($invoice->payment_status === 'paid')
                                        <span class="badge bg-success">مدفوعة</span>
                                    @elseif($invoice->payment_status === 'partial')
                                        <span class="badge bg-warning text-dark">جزئي</span>
                                    @else
                                        <span class="badge bg-danger">غير مدفوعة</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $invoice->created_at->format('m/d H:i') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">لا توجد فواتير بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-fill me-2 text-warning"></i>وصول سريع
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('products.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-1"></i> إضافة منتج</a>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-outline-success"><i class="bi bi-plus-circle me-1"></i> إضافة مورد</a>
                    <a href="{{ route('customers.create') }}" class="btn btn-outline-warning"><i class="bi bi-plus-circle me-1"></i> إضافة عميل</a>
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-danger"><i class="bi bi-wallet2 me-1"></i> المصروفات</a>
                    <a href="{{ route('prices.index') }}" class="btn btn-outline-info"><i class="bi bi-currency-dollar me-1"></i> تحديث الأسعار</a>
                    <a href="{{ route('reports.dashboard') }}" class="btn btn-dark"><i class="bi bi-shield-lock-fill me-1"></i> التقارير المالية</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
