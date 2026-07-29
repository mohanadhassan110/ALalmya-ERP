@extends('layouts.app')
@section('title', 'فاتورة جملة - سنتر العالمية')
@section('page-title', 'فاتورة جملة جديدة')

@section('content')
<form method="POST" action="{{ route('invoices.store-wholesale') }}" id="invoiceForm">
    @csrf
    <div class="row g-4">
        {{-- Products Section --}}
        <div class="col-md-8">
            {{-- Customer Selection --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-bold"><i class="bi bi-person-fill me-1"></i>العميل <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" id="customerSelect" required onchange="onCustomerChange()">
                                <option value="">اختر العميل</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-balance="{{ $cust->balance }}">
                                        {{ $cust->name }}
                                        @if($cust->balance > 0) (عليه: {{ number_format($cust->balance, 2) }}) @elseif($cust->balance < 0) (له: {{ number_format(abs($cust->balance), 2) }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div id="customerBalance" class="text-center" style="display:none;">
                                <small class="d-block text-muted">رصيد العميل</small>
                                <strong id="balanceDisplay"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-truck me-2"></i>أصناف الفاتورة</span>
                    <button type="button" class="btn btn-sm btn-gold" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i> إضافة صنف</button>
                </div>
                <div class="card-body">
                    <div id="itemsContainer"></div>
                    <div id="emptyMsg" class="text-center text-muted py-5">
                        <i class="bi bi-truck fs-1 d-block mb-2 opacity-50"></i>
                        اضغط "إضافة صنف" لبدء الفاتورة
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>ملخص الفاتورة</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المجموع:</span>
                        <span class="fw-bold" id="subtotal">0.00 ج.م</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الخصم</label>
                        <input type="number" name="discount" class="form-control" step="0.01" min="0" value="0" oninput="calculateTotal()">
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="fw-bold text-primary">الإجمالي:</span>
                        <span class="fw-bold text-primary" id="total">0.00 ج.م</span>
                    </div>

                    <div id="creditNotice" class="alert alert-success small" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i>
                        سيتم خصم <strong id="creditAmount"></strong> من الرصيد الدائن تلقائياً
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label class="form-label">المبلغ المدفوع نقداً <span class="text-danger">*</span></label>
                        <input type="number" name="paid" class="form-control form-control-lg" step="0.01" min="0" value="0" required oninput="calculateTotal()">
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">المتبقي (يضاف للحساب):</span>
                        <span class="fw-bold text-danger" id="remaining">0.00 ج.م</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                        <i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const products = @json($products);
    let itemIndex = 0;

    function onCustomerChange() {
        const select = document.getElementById('customerSelect');
        const opt = select.selectedOptions[0];
        const balDiv = document.getElementById('customerBalance');
        const balDisplay = document.getElementById('balanceDisplay');

        if (opt && opt.value) {
            const balance = parseFloat(opt.dataset.balance);
            balDiv.style.display = 'block';
            if (balance > 0) {
                balDisplay.innerHTML = `<span class="text-danger">${balance.toFixed(2)} ج.م (عليه)</span>`;
            } else if (balance < 0) {
                balDisplay.innerHTML = `<span class="text-success">${Math.abs(balance).toFixed(2)} ج.م (له)</span>`;
            } else {
                balDisplay.innerHTML = `<span class="text-muted">صفر</span>`;
            }
            calculateTotal();
        } else {
            balDiv.style.display = 'none';
        }
    }

    function addItem() {
        document.getElementById('emptyMsg').style.display = 'none';
        const container = document.getElementById('itemsContainer');

        const html = `
        <div class="invoice-item-row" id="item-${itemIndex}">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small">المنتج</label>
                    <select class="form-select" name="items[${itemIndex}][product_id]" onchange="onProductChange(this, ${itemIndex})" required>
                        <option value="">اختر المنتج</option>
                        ${products.map(p => `<option value="${p.id}" data-wholesale="${p.wholesale_price}" data-stock="${p.stock_quantity}">${p.name} (متاح: ${p.stock_quantity})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">الكمية</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" onchange="calculateTotal()" oninput="calculateTotal()" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">سعر البيع</label>
                    <input type="number" name="items[${itemIndex}][selling_price]" class="form-control" step="0.01" min="0" onchange="calculateTotal()" oninput="calculateTotal()" required>
                </div>
                <div class="col-md-2 text-center">
                    <label class="form-label small d-block">الإجمالي</label>
                    <strong class="line-total" id="lineTotal-${itemIndex}">0.00</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${itemIndex})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
        updateSubmitBtn();
    }

    function onProductChange(select, idx) {
        const opt = select.selectedOptions[0];
        if (opt && opt.value) {
            const wholesalePrice = opt.dataset.wholesale;
            const row = document.getElementById('item-' + idx);
            // في الجملة يتم تحميل سعر الجملة الافتراضي تلقائياً مع إمكانية التعديل
            const priceInput = row.querySelector(`[name="items[${idx}][selling_price]"]`);
            priceInput.value = wholesalePrice;
        }
        calculateTotal();
    }

    function removeItem(idx) {
        const el = document.getElementById('item-' + idx);
        if (el) el.remove();
        calculateTotal();
        updateSubmitBtn();
        if (document.getElementById('itemsContainer').children.length === 0) {
            document.getElementById('emptyMsg').style.display = 'block';
        }
    }

    function calculateTotal() {
        let subtotal = 0;
        const rows = document.getElementById('itemsContainer').querySelectorAll('.invoice-item-row');

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('[name*="[quantity]"]')?.value) || 0;
            const price = parseFloat(row.querySelector('[name*="[selling_price]"]')?.value) || 0;
            const lineTotal = qty * price;
            const idx = row.id.replace('item-', '');
            document.getElementById('lineTotal-' + idx).textContent = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const discount = parseFloat(document.querySelector('[name="discount"]')?.value) || 0;
        const total = subtotal - discount;
        const paid = parseFloat(document.querySelector('[name="paid"]')?.value) || 0;

        // التحقق من الرصيد الدائن للعميل
        const custSelect = document.getElementById('customerSelect');
        const custOpt = custSelect.selectedOptions[0];
        let creditUsed = 0;
        if (custOpt && custOpt.value) {
            const balance = parseFloat(custOpt.dataset.balance);
            if (balance < 0) {
                creditUsed = Math.min(Math.abs(balance), total);
                document.getElementById('creditNotice').style.display = 'block';
                document.getElementById('creditAmount').textContent = creditUsed.toFixed(2) + ' ج.م';
            } else {
                document.getElementById('creditNotice').style.display = 'none';
            }
        }

        const remaining = total - paid - creditUsed;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ج.م';
        document.getElementById('total').textContent = total.toFixed(2) + ' ج.م';
        document.getElementById('remaining').textContent = Math.max(0, remaining).toFixed(2) + ' ج.م';
    }

    function updateSubmitBtn() {
        const count = document.getElementById('itemsContainer').querySelectorAll('.invoice-item-row').length;
        document.getElementById('submitBtn').disabled = count === 0;
    }
</script>
@endsection
