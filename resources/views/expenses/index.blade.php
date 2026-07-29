@extends('layouts.app')
@section('title', 'المصروفات - سنتر العالمية')
@section('page-title', 'إدارة المصروفات')

@section('content')
<div class="row g-4">
    {{-- Add Expense Form --}}
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 80px;">
            <div class="card-header"><i class="bi bi-plus-circle me-2"></i>تسجيل مصروف جديد</div>
            <div class="card-body">
                <form method="POST" action="{{ route('expenses.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">السبب <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" required placeholder="سبب المصروف">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control" value="{{ $date }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger w-100"><i class="bi bi-wallet2 me-1"></i> تسجيل المصروف</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Today's Expenses --}}
    <div class="col-md-8">
        {{-- Date Picker --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" class="d-flex gap-2 align-items-end">
                    <div class="flex-fill">
                        <label class="form-label small">عرض مصروفات تاريخ:</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-search"></i> عرض</button>
                </form>
            </div>
        </div>

        {{-- Daily Total --}}
        <div class="stat-card bg-gradient-danger mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">إجمالي مصروفات {{ $date }}</div>
                    <div class="stat-value">{{ number_format($todayTotal, 2) }} ج.م</div>
                </div>
                <i class="bi bi-wallet2 stat-icon"></i>
            </div>
        </div>

        {{-- Expense List --}}
        <div class="card">
            <div class="card-header"><i class="bi bi-list me-2"></i>مصروفات يوم {{ $date }}</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المبلغ</th>
                                <th>السبب</th>
                                <th>ملاحظات</th>
                                <th>الوقت</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->id }}</td>
                                <td class="fw-bold text-danger">{{ number_format($expense->amount, 2) }} ج.م</td>
                                <td>{{ $expense->reason }}</td>
                                <td class="text-muted small">{{ $expense->notes ?? '—' }}</td>
                                <td class="text-muted small">{{ $expense->created_at->format('H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('حذف هذا المصروف؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">لا توجد مصروفات لهذا اليوم</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- History --}}
        @if($history->count() > 0)
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>سجل المصروفات (آخر 30 يوم)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>التاريخ</th>
                                <th>عدد المصروفات</th>
                                <th>الإجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $day)
                            <tr>
                                <td class="fw-bold">{{ $day->expense_date }}</td>
                                <td><span class="badge bg-info">{{ $day->count }}</span></td>
                                <td class="fw-bold text-danger">{{ number_format($day->total, 2) }} ج.م</td>
                                <td>
                                    <a href="{{ route('expenses.index', ['date' => $day->expense_date]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> عرض</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
