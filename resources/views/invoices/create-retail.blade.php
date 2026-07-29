@extends('layouts.app')
@section('title', 'فاتورة تجزئة - سنتر العالمية')
@section('page-title', 'فاتورة تجزئة جديدة')

@section('content')
<form method="POST" action="{{ route('invoices.store-retail') }}" id="invoiceForm">
    @csrf
    <div class="row g-4">
        {{-- Products Section --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-plus-fill me-2"></i>أصناف الفاتورة</span>
                    <button type="button" class="btn btn-sm btn-gold" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i> إضافة صنف</button>
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        {{-- Items will be added here --}}
                    </div>
                    <div id="emptyMsg" class="text-center text-muted py-5">
                        <i class="bi bi-cart fs-1 d-block mb-2 opacity-50"></i>
                        اضغط "إضافة صنف" لبدء الفاتورة
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Section --}}
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>ملخص الفاتورة</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المجموع:</span>
                        <span class="fw-bold" id="subtotal">0.00</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الخصم</label>
                        <input type="number" name="discount" class="form-control" step="0.01" min="0" value="0" oninput="calculateTotal()">
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="fw-bold text-primary">الإجمالي:</span>
                        <span class="fw-bold text-primary" id="total">0.00</span>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">المبلغ المدفوع <span class="text-danger">*</span></label>
                        <input type="number" name="paid" class="form-control form-control-lg" step="0.01" min="0" value="0" required oninput="calculateTotal()">
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">المتبقي:</span>
                        <span class="fw-bold text-danger" id="remaining">0.00</span>
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
    // بيانات المنتجات
    const products = @json($products);
    let itemIndex = 0;

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
                        ${products.map(p => `<option value="${p.id}" data-cost="${p.cost_price}" data-retail="${p.retail_price}" data-stock="${p.stock_quantity}">${p.name} (متاح: ${p.stock_quantity})</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">الكمية</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="1" min="1" onchange="calculateTotal()" oninput="calculateTotal()" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">سعر البيع</label>
                    <input type="number" name="items[${itemIndex}][selling_price]" class="form-control" step="0.01" min="0" placeholder="السعر" onchange="calculateTotal()" oninput="calculateTotal()" required>
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
            const retailPrice = opt.dataset.retail || 0;
            const row = document.getElementById('item-' + idx);
            // في التجزئة السعر يدوي (لكن نضع التجزئة المقترح كمساعد)
            row.querySelector(`[name="items[${idx}][selling_price]"]`).placeholder = `مقترح: ${retailPrice}`;
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
        const container = document.getElementById('itemsContainer');
        const rows = container.querySelectorAll('.invoice-item-row');

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('[name*="[quantity]"]')?.value) || 0;
            const price = parseFloat(row.querySelector('[name*="[selling_price]"]')?.value) || 0;
            const lineTotal = qty * price;
            const idx = row.id.replace('item-', '');
            const lineTotalEl = document.getElementById('lineTotal-' + idx);
            if (lineTotalEl) lineTotalEl.textContent = lineTotal.toFixed(2);
            subtotal += lineTotal;
        });

        const discount = parseFloat(document.querySelector('[name="discount"]')?.value) || 0;
        const total = subtotal - discount;
        const paid = parseFloat(document.querySelector('[name="paid"]')?.value) || 0;
        const remaining = total - paid;

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
