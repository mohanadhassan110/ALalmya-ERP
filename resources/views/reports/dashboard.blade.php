@extends('layouts.app')
@section('title', 'التقارير المالية - سنتر العالمية')
@section('page-title', 'لوحة التحكم المالية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-lock-fill me-2 text-danger"></i>التقارير المالية</h4>
    <div class="d-flex gap-2 align-items-center">
        <form method="POST" action="{{ route('reports.logout') }}" class="d-inline">
            @csrf
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-left me-1"></i> خروج من القسم المالي</button>
        </form>
    </div>
</div>

{{-- Date Range Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">من تاريخ</label>
                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">إلى تاريخ</label>
                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-filter"></i> تصفية</button>
                <a href="{{ route('reports.dashboard') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Key Metrics --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-6">
        <div class="stat-card bg-gradient-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">قيمة المخزون</div>
                    <div class="stat-value">{{ number_format($inventoryValue, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-box-seam-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card bg-gradient-danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">ديون الموردين</div>
                    <div class="stat-value">{{ number_format($supplierDebts, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-building stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card bg-gradient-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي المبيعات</div>
                    <div class="stat-value">{{ number_format($totalSales, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-graph-up-arrow stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card bg-gradient-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي الأرباح</div>
                    <div class="stat-value">{{ number_format($totalProfit, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-currency-dollar stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #e17055 0%, #d63031 100%);">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">إجمالي المصروفات</div>
                    <div class="stat-value">{{ number_format($totalExpenses, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-wallet2 stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="stat-card" style="background: linear-gradient(135deg, {{ $netProfit >= 0 ? '#00b894, #00cec9' : '#d63031, #e17055' }});">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">صافي الربح النهائي</div>
                    <div class="stat-value">{{ number_format($netProfit, 0) }}</div>
                    <div class="stat-label">جنيه مصري</div>
                </div>
                <i class="bi bi-trophy-fill stat-icon"></i>
            </div>
        </div>
    </div>
</div>

{{-- Secondary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">عدد الفواتير</div>
                <div class="fs-3 fw-bold text-primary">{{ $totalInvoices }}</div>
                <div class="small">
                    <span class="badge bg-info">{{ $retailInvoices }} تجزئة</span>
                    <span class="badge bg-warning text-dark">{{ $wholesaleInvoices }} جملة</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">ديون العملاء</div>
                <div class="fs-3 fw-bold text-danger">{{ number_format($customerDebts, 0) }}</div>
                <small class="text-muted">جنيه</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">أرصدة دائنة للعملاء</div>
                <div class="fs-3 fw-bold text-success">{{ number_format(abs($customerCredits), 0) }}</div>
                <small class="text-muted">جنيه (سلف)</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="text-muted small">منتجات نفدت</div>
                <div class="fs-3 fw-bold text-danger">{{ $outOfStockProducts }}</div>
                <small class="text-muted">منتج</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Top Products --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-star-fill me-2 text-warning"></i>المنتجات الأكثر مبيعاً</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المنتج</th>
                                <th>الكمية المباعة</th>
                                <th>إجمالي المبيعات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $i => $tp)
                            <tr>
                                <td><span class="badge bg-primary">{{ $i + 1 }}</span></td>
                                <td class="fw-bold">{{ $tp->product_name }}</td>
                                <td>{{ $tp->total_qty }}</td>
                                <td class="fw-bold">{{ number_format($tp->total_sales, 2) }} ج.م</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>منتجات منخفضة المخزون</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>المتبقي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $lsp)
                            <tr>
                                <td>{{ $lsp->name }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $lsp->stock_quantity }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">المخزون جيد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
