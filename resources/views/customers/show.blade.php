@extends('layouts.app')
@section('title', $customer->name . ' - حساب العميل')
@section('page-title', 'حساب العميل: ' . $customer->name)
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / <a href="{{ route('customers.index') }}">العملاء</a> / {{ $customer->name }}
@endsection

@section('content')
<div class="row g-4">
    {{-- Customer Info --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-person-fill me-2"></i>بيانات العميل</span>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><td class="text-muted">الاسم</td><td class="fw-bold">{{ $customer->name }}</td></tr>
                    <tr>
                        <td class="text-muted">النوع</td>
                        <td><span class="badge {{ $customer->type === 'wholesale' ? 'bg-warning text-dark' : 'bg-info' }}">{{ $customer->type === 'wholesale' ? 'جملة' : 'تجزئة' }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">الهاتف</td>
                        <td>
                            @if($customer->phone)
                                {{ $customer->phone }}
                                <div class="mt-1 d-flex gap-1">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-whatsapp me-1"></i>واتساب
                                    </a>
                                    <a href="tel:{{ $customer->phone }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-telephone me-1"></i>اتصال
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted">العنوان</td><td>{{ $customer->address ?? '—' }}</td></tr>
                    @if($customer->notes)
                    <tr><td class="text-muted">ملاحظات</td><td>{{ $customer->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Balance Card --}}
        <div class="card mt-3">
            <div class="card-body text-center p-0">
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

        {{-- Quick Actions --}}
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-lightning-fill me-2 text-warning"></i>إجراءات سريعة</div>
            <div class="card-body d-grid gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payModal">
                    <i class="bi bi-cash me-1"></i> تسجيل سداد / سلفة
                </button>
                @if($customer->type === 'wholesale')
                <a href="{{ route('invoices.create-wholesale') }}" class="btn btn-gold">
                    <i class="bi bi-truck me-1"></i> فاتورة جملة جديدة
                </a>
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
                    <a href="{{ route('invoices.show', $inv) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold">{{ $inv->invoice_number }}</span>
                            <small class="text-muted d-block">{{ $inv->created_at->format('Y/m/d') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold">{{ number_format($inv->total, 2) }} ج.م</span>
                            @if($inv->isCancelled())
                                <small class="badge bg-secondary d-block mt-1">ملغاة</small>
                            @elseif($inv->payment_status === 'paid')
                                <small class="badge bg-success d-block mt-1">مدفوعة</small>
                            @else
                                <small class="badge bg-warning text-dark d-block mt-1">{{ $inv->payment_status === 'partial' ? 'جزئي' : 'غير مدفوعة' }}</small>
                            @endif
                        </div>
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
                                    @elseif(in_array($tx->type, ['payment', 'advance']))
                                        <span class="badge bg-success">{{ $tx->type_name }}</span>
                                    @elseif($tx->type === 'adjustment')
                                        <span class="badge bg-warning text-dark">{{ $tx->type_name }}</span>
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
                                <td class="text-muted small">
                                    {{ $tx->description ?? '—' }}
                                    @if($tx->invoice_id)
                                        <a href="{{ route('invoices.show', $tx->invoice_id) }}" class="text-decoration-none"><i class="bi bi-box-arrow-up-left"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="bi bi-journal"></i>
                                    <p>لا توجد حركات بعد</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $transactions->links() }}</div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.payment', $customer) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">سداد / سلفة - {{ $customer->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert {{ $customer->balance > 0 ? 'alert-danger' : ($customer->balance < 0 ? 'alert-success' : 'alert-info') }}">
                        @if($customer->balance > 0)
                            عليه: <strong>{{ number_format($customer->balance, 2) }} ج.م</strong>
                        @elseif($customer->balance < 0)
                            له (سلفة): <strong>{{ number_format(abs($customer->balance), 2) }} ج.م</strong>
                        @else
                            الرصيد: <strong>صفر</strong>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع العملية</label>
                        <select name="type" class="form-select" required>
                            <option value="payment">سداد (خصم من المديونية)</option>
                            <option value="advance">سلفة / دفعة مقدمة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">المبلغ <span class="required">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" step="0.01" min="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" name="description" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i> تأكيد</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
