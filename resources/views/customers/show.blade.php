@extends('layouts.app')
@section('title', $customer->name . ' - حساب العميل')
@section('page-title', 'حساب العميل: ' . $customer->name)

@section('content')
<div class="row g-4">
    {{-- Customer Info --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-fill me-2"></i>بيانات العميل</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">الاسم</td><td class="fw-bold">{{ $customer->name }}</td></tr>
                    <tr><td class="text-muted">النوع</td><td><span class="badge {{ $customer->type === 'wholesale' ? 'bg-warning text-dark' : 'bg-info' }}">{{ $customer->type === 'wholesale' ? 'جملة' : 'تجزئة' }}</span></td></tr>
                    <tr><td class="text-muted">الهاتف</td><td>{{ $customer->phone ?? '—' }}</td></tr>
                    <tr><td class="text-muted">العنوان</td><td>{{ $customer->address ?? '—' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Balance Card --}}
        <div class="card mt-3">
            <div class="card-body text-center">
                @if($customer->balance > 0)
                    <div class="stat-card bg-gradient-danger">
                        <div class="stat-label">عليه (مدين)</div>
                        <div class="stat-value">{{ number_format($customer->balance, 2) }} ج.م</div>
                    </div>
                @elseif($customer->balance < 0)
                    <div class="stat-card bg-gradient-success">
                        <div class="stat-label">له (رصيد دائن / سلفة)</div>
                        <div class="stat-value">{{ number_format(abs($customer->balance), 2) }} ج.م</div>
                    </div>
                @else
                    <div class="stat-card bg-gradient-info">
                        <div class="stat-label">الرصيد</div>
                        <div class="stat-value">صفر</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Invoices --}}
        @if($invoices->count() > 0)
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-receipt me-2"></i>آخر الفواتير</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($invoices as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span>{{ $inv->invoice_number }}</span>
                        <span class="fw-bold">{{ number_format($inv->total, 2) }} ج.م</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Transaction Ledger --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i>دفتر الحساب</span>
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
                                    @if($tx->type === 'invoice')
                                        <span class="badge bg-danger">{{ $tx->type_name }}</span>
                                    @elseif($tx->type === 'payment' || $tx->type === 'advance')
                                        <span class="badge bg-success">{{ $tx->type_name }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $tx->type_name }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($tx->amount, 2) }} ج.م</td>
                                <td>
                                    @if($tx->balance_after > 0)
                                        <span class="text-danger">{{ number_format($tx->balance_after, 2) }} ج.م</span>
                                    @elseif($tx->balance_after < 0)
                                        <span class="text-success">{{ number_format(abs($tx->balance_after), 2) }} ج.م (له)</span>
                                    @else
                                        <span class="text-muted">صفر</span>
                                    @endif
                                </td>
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
