@extends('layouts.app')
@section('title', 'الصفحة الرئيسية - سنتر العالمية')
@section('page-title', 'الصفحة الرئيسية')

@section('content')
@php
    use App\Models\Product;
    use App\Models\Invoice;
    use App\Models\Customer;
    use App\Models\Supplier;
    use App\Models\Expense;

    // Today's stats
    $todayInvoices = Invoice::active()->whereDate('created_at', today());
    $todaySales = $todayInvoices->sum('total');
    $todayPaid = Invoice::active()->whereDate('created_at', today())->sum('paid');
    $todayCount = Invoice::active()->whereDate('created_at', today())->count();
    $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
    $unpaidToday = Invoice::active()->whereDate('created_at', today())->where('payment_status', '!=', 'paid')->count();

    // Inventory
    $totalProducts = Product::count();
    $lowStockProducts = Product::where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->get();
    $outOfStock = Product::where('stock_quantity', '<=', 0)->count();

    // Accounts
    $totalCustomerDebt = Customer::where('balance', '>', 0)->sum('balance');
    $totalSupplierDebt = Supplier::sum('current_balance');
    $totalCustomers = Customer::count();
    $totalSuppliers = Supplier::count();
@endphp

{{-- Welcome Banner --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #fff; border-radius: 18px;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="fw-bold mb-1">مرحباً بك في سنتر العالمية</h3>
                        <p class="mb-3 opacity-75" style="font-size: 0.9rem;">نظام إدارة المفروشات - {{ now()->locale('ar')->translatedFormat('l j F Y') }}</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('invoices.create-retail') }}" class="btn btn-gold">
                                <i class="bi bi-cart-plus-fill me-1"></i> فاتورة تجزئة
                            </a>
                            <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-outline-light">
                                <i class="bi bi-truck me-1"></i> فاتورة جملة
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bi bi-shop" style="font-size: 6rem; opacity: 0.12;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Today at a Glance --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="fw-bold mb-3"><i class="bi bi-sun-fill me-2 text-warning"></i>اليوم في لمحة</h5>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('invoices.index', ['date_from' => today()->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-success card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">مبيعات اليوم</div>
                        <div class="stat-value">{{ number_format($todaySales, 0) }}</div>
                        <div class="stat-label">{{ $todayCount }} فاتورة</div>
                    </div>
                    <i class="bi bi-cash-coin stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card bg-gradient-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">المحصّل نقداً</div>
                    <div class="stat-value">{{ number_format($todayPaid, 0) }}</div>
                    <div class="stat-label">ج.م</div>
                </div>
                <i class="bi bi-wallet-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('expenses.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-danger card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">مصروفات اليوم</div>
                        <div class="stat-value">{{ number_format($todayExpenses, 0) }}</div>
                        <div class="stat-label">ج.م</div>
                    </div>
                    <i class="bi bi-wallet2 stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        @if($unpaidToday > 0)
        <a href="{{ route('invoices.index', ['date_from' => today()->format('Y-m-d'), 'payment_status' => 'unpaid']) }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-warning card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">فواتير غير مدفوعة</div>
                        <div class="stat-value">{{ $unpaidToday }}</div>
                        <div class="stat-label">اليوم</div>
                    </div>
                    <i class="bi bi-exclamation-triangle stat-icon"></i>
                </div>
            </div>
        </a>
        @else
        <div class="stat-card bg-gradient-teal">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">فواتير اليوم</div>
                    <div class="stat-value"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-label">كلها مدفوعة</div>
                </div>
                <i class="bi bi-check-all stat-icon"></i>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Quick Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('products.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-primary card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">المنتجات</div>
                        <div class="stat-value">{{ $totalProducts }}</div>
                        @if($outOfStock > 0)<div class="stat-label" style="opacity:1;"><i class="bi bi-exclamation-circle"></i> {{ $outOfStock }} نفد</div>@endif
                    </div>
                    <i class="bi bi-box-seam-fill stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('customers.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-warning card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">ديون العملاء</div>
                        <div class="stat-value">{{ number_format($totalCustomerDebt, 0) }}</div>
                        <div class="stat-label">{{ $totalCustomers }} عميل</div>
                    </div>
                    <i class="bi bi-people-fill stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('suppliers.index') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-danger card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">ديون الموردين</div>
                        <div class="stat-value">{{ number_format($totalSupplierDebt, 0) }}</div>
                        <div class="stat-label">{{ $totalSuppliers }} مورد</div>
                    </div>
                    <i class="bi bi-building stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('reports.dashboard') }}" class="text-decoration-none">
            <div class="stat-card bg-gradient-dark card-hover">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">التقارير المالية</div>
                        <div class="stat-value"><i class="bi bi-shield-lock-fill"></i></div>
                        <div class="stat-label">محمية بكلمة مرور</div>
                    </div>
                    <i class="bi bi-graph-up stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Recent Invoices --}}
    <div class="col-lg-7">
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
                                <th>الفاتورة</th>
                                <th>النوع</th>
                                <th>الإجمالي</th>
                                <th>الحالة</th>
                                <th>الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(Invoice::active()->latest()->limit(8)->get() as $invoice)
                            <tr>
                                <td><a href="{{ route('invoices.show', $invoice) }}" class="text-decoration-none fw-bold">{{ $invoice->invoice_number }}</a></td>
                                <td><span class="badge {{ $invoice->type === 'retail' ? 'bg-info' : 'bg-warning text-dark' }}">{{ $invoice->type === 'retail' ? 'تجزئة' : 'جملة' }}</span></td>
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
                                <td class="text-muted small">{{ $invoice->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="bi bi-receipt"></i>
                                    <p>لا توجد فواتير بعد</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar: Low Stock + Quick Actions --}}
    <div class="col-lg-5">
        {{-- Low Stock Alert --}}
        @if($lowStockProducts->count() > 0 || $outOfStock > 0)
        <div class="card mb-3">
            <div class="card-header text-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>تنبيهات المخزون
                <span class="badge bg-danger ms-1">{{ $lowStockProducts->count() + $outOfStock }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @if($outOfStock > 0)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> منتجات نفدت</span>
                        <span class="badge bg-danger">{{ $outOfStock }}</span>
                    </div>
                    @endif
                    @foreach($lowStockProducts->take(5) as $lp)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="fw-bold">{{ $lp->name }}</span>
                        <span class="badge bg-warning text-dark">{{ $lp->stock_quantity }} قطعة</span>
                    </div>
                    @endforeach
                    @if($lowStockProducts->count() > 5)
                    <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action text-center text-primary">
                        عرض الكل ({{ $lowStockProducts->count() }})
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-fill me-2 text-warning"></i>وصول سريع
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('products.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-1"></i> إضافة منتج</a>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-outline-success"><i class="bi bi-plus-circle me-1"></i> إضافة مورد</a>
                    <a href="{{ route('customers.create') }}" class="btn btn-outline-warning"><i class="bi bi-plus-circle me-1"></i> إضافة عميل</a>
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-danger"><i class="bi bi-wallet2 me-1"></i> تسجيل مصروف</a>
                    <a href="{{ route('prices.index') }}" class="btn btn-outline-info"><i class="bi bi-currency-dollar me-1"></i> تحديث الأسعار</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
