@extends('layouts.app')
@section('title', 'فاتورة جملة - سنتر العالمية')
@section('page-title', 'فاتورة جملة جديدة')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / <a href="{{ route('invoices.index') }}">الفواتير</a> / فاتورة جملة
@endsection

@section('styles')
<style>
    .search-results {
        position: absolute; top: 100%; right: 0; left: 0;
        background: #fff; border: 1px solid #dee2e6; border-top: none;
        border-radius: 0 0 12px 12px; z-index: 100; max-height: 350px;
        overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.12); display: none;
    }
    .search-results.show { display: block; }
    .search-result-item {
        padding: 10px 16px; cursor: pointer; display: flex;
        justify-content: space-between; align-items: center;
        border-bottom: 1px solid #f5f5f5; transition: background 0.15s;
    }
    .search-result-item:hover, .search-result-item.highlighted { background: rgba(233,69,96,0.06); }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-item .product-name { font-weight: 600; font-size: 0.88rem; }
    .search-result-item .product-meta { font-size: 0.75rem; color: #888; display: flex; gap: 12px; margin-top: 2px; }

    .cart-item {
        background: #fff; border: 1px solid #eee; border-radius: 12px;
        padding: 12px 16px; margin-bottom: 8px; transition: all 0.2s; animation: fadeInUp 0.2s ease;
    }
    .cart-item:hover { border-color: var(--accent); box-shadow: 0 2px 8px rgba(233,69,96,0.08); }
    .cart-item .item-name { font-weight: 700; font-size: 0.88rem; }
    .cart-item .item-meta { font-size: 0.75rem; color: #888; }
    .cart-item .price-warning { color: var(--accent); font-size: 0.72rem; font-weight: 600; }
    .cart-item .stock-warning { color: #f0a500; font-size: 0.72rem; font-weight: 600; }

    .summary-card { position: sticky; top: 68px; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; }
    .summary-row.total-row { font-size: 1.15rem; padding: 10px 0; border-top: 2px solid var(--accent); margin-top: 4px; }

    .customer-balance-card {
        border-radius: 10px; padding: 10px 16px; text-align: center; font-size: 0.85rem;
    }

    .success-overlay {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.6); z-index: 2000; backdrop-filter: blur(4px);
    }
    .success-overlay.show { display: flex; justify-content: center; align-items: center; }
    .success-card {
        background: #fff; border-radius: 20px; padding: 40px; text-align: center;
        max-width: 440px; width: 90%; animation: fadeInUp 0.4s ease; box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .success-card .success-icon { font-size: 4rem; color: var(--success); display: block; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .summary-card {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 1020;
            border-radius: 16px 16px 0 0; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); max-height: 50vh; overflow-y: auto;
        }
        .page-content { padding-bottom: 320px !important; }
    }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('invoices.store-wholesale') }}" id="invoiceForm">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            {{-- Customer Selection --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-7">
                            <label class="form-label fw-bold"><i class="bi bi-person-fill me-1"></i>العميل <span class="required">*</span></label>
                            <select name="customer_id" class="form-select" id="customerSelect" required onchange="onCustomerChange()">
                                <option value="">اختر العميل</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-balance="{{ $cust->balance }}" data-phone="{{ $cust->phone }}">
                                        {{ $cust->name }}
                                        @if($cust->balance > 0) (عليه: {{ number_format($cust->balance, 2) }})
                                        @elseif($cust->balance < 0) (له: {{ number_format(abs($cust->balance), 2) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div id="customerBalance" style="display:none;">
                                <div class="customer-balance-card" id="balanceCard"></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('customers.create') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i> جديد
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card mb-3">
                <div class="card-body py-3 position-relative">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="productSearch" class="form-control border-start-0 ps-0"
                               placeholder="ابحث بالاسم أو SKU..." autocomplete="off">
                    </div>
                    <div class="search-results" id="searchResults"></div>
                </div>
            </div>

            {{-- Cart --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-truck me-2"></i>أصناف الفاتورة <span class="badge bg-primary" id="cartCount">0</span></span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display:none;">
                        <i class="bi bi-trash me-1"></i>تفريغ
                    </button>
                </div>
                <div class="card-body" id="cartContainer">
                    <div id="emptyCart" class="empty-state">
                        <i class="bi bi-truck"></i>
                        <p>اختر العميل ثم ابحث عن المنتجات</p>
                    </div>
                    <div id="cartItems"></div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="col-lg-4">
            <div class="card summary-card">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>ملخص الفاتورة</div>
                <div class="card-body">
                    <div class="summary-row">
                        <span class="text-muted">المجموع:</span>
                        <span class="fw-bold" id="subtotal">0.00 ج.م</span>
                    </div>
                    <div class="mb-3 mt-2">
                        <label class="form-label">الخصم</label>
                        <input type="number" name="discount" id="discountInput" class="form-control" step="0.01" min="0" value="0" oninput="recalculate()">
                    </div>
                    <div class="summary-row total-row">
                        <span class="fw-bold" style="color: var(--accent);">الإجمالي:</span>
                        <span class="fw-bold fs-5" style="color: var(--accent);" id="totalDisplay">0.00 ج.م</span>
                    </div>

                    <div id="creditNotice" class="alert alert-success small mt-2" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> سيتم خصم <strong id="creditAmount"></strong> من الرصيد الدائن
                    </div>

                    <hr>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-cash-stack me-1"></i>المبلغ المدفوع نقداً <span class="required">*</span></label>
                        <input type="number" name="paid" id="paidInput" class="form-control form-control-lg text-center fw-bold" step="0.01" min="0" value="0" required oninput="recalculate()">
                    </div>
                    <div class="summary-row">
                        <span class="text-muted">المتبقي (يضاف للحساب):</span>
                        <span class="fw-bold text-danger" id="remainingDisplay">0.00 ج.م</span>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn" disabled>
                        <i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة
                        <small class="d-block" style="font-size: 0.7rem; opacity: 0.8;">Ctrl+Enter</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Success Overlay --}}
<div class="success-overlay" id="successOverlay">
    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h3>تم حفظ الفاتورة بنجاح!</h3>
        <p class="text-muted mb-1">رقم الفاتورة: <strong id="successInvoiceNumber"></strong></p>
        <p class="fs-5 fw-bold mb-3" style="color: var(--accent);" id="successTotal"></p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a id="successPrintBtn" href="#" target="_blank" class="btn btn-dark"><i class="bi bi-printer me-1"></i> طباعة</a>
            <a id="successViewBtn" href="#" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i> عرض</a>
            <button type="button" class="btn btn-gold" onclick="startNewSale()"><i class="bi bi-truck me-1"></i> فاتورة جديدة</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const products = @json($products);
    let cart = [];
    let searchHighlightIndex = -1;
    let isSubmitting = false;

    // === Customer Selection ===
    function onCustomerChange() {
        const select = document.getElementById('customerSelect');
        const opt = select.selectedOptions[0];
        const balDiv = document.getElementById('customerBalance');
        const balCard = document.getElementById('balanceCard');

        if (opt && opt.value) {
            const balance = parseFloat(opt.dataset.balance);
            balDiv.style.display = 'block';
            if (balance > 0) {
                balCard.className = 'customer-balance-card bg-danger bg-opacity-10 text-danger';
                balCard.innerHTML = `<strong>عليه: ${balance.toFixed(2)} ج.م</strong>`;
            } else if (balance < 0) {
                balCard.className = 'customer-balance-card bg-success bg-opacity-10 text-success';
                balCard.innerHTML = `<strong>له: ${Math.abs(balance).toFixed(2)} ج.م</strong>`;
            } else {
                balCard.className = 'customer-balance-card bg-info bg-opacity-10 text-info';
                balCard.innerHTML = `<strong>صفر</strong>`;
            }
            recalculate();
        } else {
            balDiv.style.display = 'none';
        }
    }

    // === Search ===
    const searchInput = document.getElementById('productSearch');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        searchHighlightIndex = -1;
        if (query.length < 1) { searchResults.classList.remove('show'); return; }

        const matches = products.filter(p =>
            p.name.toLowerCase().includes(query) || (p.sku && p.sku.toLowerCase().includes(query))
        ).slice(0, 15);

        if (matches.length === 0) {
            searchResults.innerHTML = '<div class="text-center text-muted py-3">لا توجد نتائج</div>';
        } else {
            searchResults.innerHTML = matches.map((p, i) => {
                const stockClass = p.stock_quantity <= 0 ? 'stock-out' : (p.stock_quantity <= 5 ? 'stock-low' : 'stock-healthy');
                return `<div class="search-result-item" onclick="addProductToCart(${p.id})">
                    <div><div class="product-name">${p.name}</div>
                    <div class="product-meta">
                        ${p.category ? `<span>${p.category.name}</span>` : ''}
                        <span class="${stockClass}">متاح: ${p.stock_quantity <= 0 ? 'نفد' : p.stock_quantity}</span>
                        <span>جملة: ${parseFloat(p.wholesale_price).toFixed(2)}</span>
                    </div></div>
                    <span class="badge ${p.stock_quantity <= 0 ? 'bg-danger' : 'bg-success'}">${p.stock_quantity <= 0 ? 'نفد' : p.stock_quantity}</span>
                </div>`;
            }).join('');
        }
        searchResults.classList.add('show');
    });

    searchInput.addEventListener('keydown', function(e) {
        const items = searchResults.querySelectorAll('.search-result-item');
        if (!searchResults.classList.contains('show') || items.length === 0) { if (e.key === 'Enter') e.preventDefault(); return; }
        if (e.key === 'ArrowDown') { e.preventDefault(); searchHighlightIndex = Math.min(searchHighlightIndex + 1, items.length - 1); items.forEach((el, i) => el.classList.toggle('highlighted', i === searchHighlightIndex)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); searchHighlightIndex = Math.max(searchHighlightIndex - 1, 0); items.forEach((el, i) => el.classList.toggle('highlighted', i === searchHighlightIndex)); }
        else if (e.key === 'Enter') { e.preventDefault(); if (searchHighlightIndex >= 0) items[searchHighlightIndex].click(); else if (items.length === 1) items[0].click(); }
        else if (e.key === 'Escape') { searchResults.classList.remove('show'); }
    });

    document.addEventListener('click', e => { if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) searchResults.classList.remove('show'); });

    // === Cart ===
    function addProductToCart(productId) {
        const product = products.find(p => p.id === productId);
        if (!product || product.stock_quantity <= 0) { showToast('المنتج غير متاح', 'error'); return; }

        const existing = cart.findIndex(item => item.product_id === productId);
        if (existing >= 0) {
            if (cart[existing].quantity + 1 > product.stock_quantity) { showToast(`الكمية أكبر من المتاح (${product.stock_quantity})`, 'warning'); return; }
            cart[existing].quantity++;
        } else {
            cart.push({
                product_id: product.id, name: product.name, sku: product.sku,
                category: product.category ? product.category.name : '',
                stock_quantity: product.stock_quantity,
                cost_price: parseFloat(product.cost_price),
                wholesale_price: parseFloat(product.wholesale_price) || 0,
                quantity: 1,
                selling_price: parseFloat(product.wholesale_price) || 0,
            });
        }
        searchInput.value = '';
        searchResults.classList.remove('show');
        searchInput.focus();
        renderCart();
        recalculate();
    }

    function updateCartItem(index, field, value) {
        if (field === 'quantity') {
            const v = parseInt(value) || 0;
            if (v <= 0) { removeCartItem(index); return; }
            if (v > cart[index].stock_quantity) { showToast('كمية أكبر من المتاح', 'warning'); return; }
            cart[index].quantity = v;
        } else if (field === 'selling_price') { cart[index].selling_price = parseFloat(value) || 0; }
        renderCart(); recalculate();
    }

    function removeCartItem(index) { cart.splice(index, 1); renderCart(); recalculate(); }
    function clearCart() {
        if (cart.length === 0) return;
        confirmAction('تفريغ السلة', 'تفريغ كل الأصناف؟', () => { cart = []; renderCart(); recalculate(); });
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const empty = document.getElementById('emptyCart');
        document.getElementById('clearCartBtn').style.display = cart.length > 0 ? 'inline-flex' : 'none';
        document.getElementById('cartCount').textContent = cart.length;

        if (cart.length === 0) { container.innerHTML = ''; empty.style.display = 'block'; return; }
        empty.style.display = 'none';

        container.innerHTML = cart.map((item, i) => {
            const total = item.quantity * item.selling_price;
            const belowCost = item.selling_price > 0 && item.selling_price < item.cost_price;
            return `<div class="cart-item">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4 col-12">
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">${item.category ? `<span class="badge bg-light text-dark me-1">${item.category}</span>` : ''}<span class="${item.stock_quantity <= 5 ? 'stock-low' : ''}">متاح: ${item.stock_quantity}</span></div>
                        ${belowCost ? `<div class="price-warning"><i class="bi bi-exclamation-triangle"></i> أقل من التكلفة (${item.cost_price.toFixed(2)})</div>` : ''}
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label small mb-0">الكمية</label>
                        <input type="number" class="form-control text-center" value="${item.quantity}" min="1" max="${item.stock_quantity}"
                               onchange="updateCartItem(${i},'quantity',this.value)" oninput="updateCartItem(${i},'quantity',this.value)">
                    </div>
                    <div class="col-4 col-md-3">
                        <label class="form-label small mb-0">السعر</label>
                        <input type="number" class="form-control text-center ${belowCost ? 'border-danger' : ''}" value="${item.selling_price}" step="0.01"
                               onchange="updateCartItem(${i},'selling_price',this.value)" oninput="updateCartItem(${i},'selling_price',this.value)">
                    </div>
                    <div class="col-3 col-md-2 text-center">
                        <label class="form-label small mb-0 d-block">الإجمالي</label>
                        <strong>${total.toFixed(2)}</strong>
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeCartItem(${i})"><i class="bi bi-x-lg"></i></button>
                    </div>
                </div>
            </div>`;
        }).join('');
        updateFormInputs();
    }

    function updateFormInputs() {
        document.querySelectorAll('.cart-hidden-input').forEach(el => el.remove());
        const form = document.getElementById('invoiceForm');
        cart.forEach((item, i) => {
            ['product_id', 'quantity', 'selling_price'].forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = `items[${i}][${field}]`;
                input.value = item[field]; input.className = 'cart-hidden-input';
                form.appendChild(input);
            });
        });
    }

    function recalculate() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.quantity * item.selling_price);

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const total = Math.max(0, subtotal - discount);
        const paid = parseFloat(document.getElementById('paidInput').value) || 0;

        // Customer credit
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
        } else {
            document.getElementById('creditNotice').style.display = 'none';
        }

        const remaining = total - paid - creditUsed;

        document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ج.م';
        document.getElementById('totalDisplay').textContent = total.toFixed(2) + ' ج.م';
        document.getElementById('remainingDisplay').textContent = Math.max(0, remaining).toFixed(2) + ' ج.م';

        const hasCustomer = custSelect.value !== '';
        document.getElementById('submitBtn').disabled = cart.length === 0 || !hasCustomer || discount > subtotal || isSubmitting;
    }

    // === Form Submit ===
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (isSubmitting || cart.length === 0) return;
        isSubmitting = true;
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الحفظ...';
        updateFormInputs();

        fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('successInvoiceNumber').textContent = data.invoice.invoice_number;
                document.getElementById('successTotal').textContent = parseFloat(data.invoice.total).toFixed(2) + ' ج.م';
                document.getElementById('successPrintBtn').href = `/invoices/${data.invoice.id}/print`;
                document.getElementById('successViewBtn').href = `/invoices/${data.invoice.id}`;
                document.getElementById('successOverlay').classList.add('show');
            } else throw new Error(data.message || 'خطأ');
        })
        .catch(err => {
            showToast(err.message || 'خطأ في حفظ الفاتورة', 'error');
            isSubmitting = false; btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة';
        });
    });

    function startNewSale() {
        cart = []; isSubmitting = false;
        renderCart(); recalculate();
        document.getElementById('discountInput').value = 0;
        document.getElementById('paidInput').value = 0;
        document.querySelector('[name="notes"]').value = '';
        document.getElementById('customerSelect').selectedIndex = 0;
        onCustomerChange();
        document.getElementById('successOverlay').classList.remove('show');
        document.getElementById('submitBtn').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة';
        searchInput.focus();
    }

    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') { e.preventDefault(); if (!document.getElementById('submitBtn').disabled) document.getElementById('invoiceForm').requestSubmit(); }
        if (e.key === 'Escape' && document.getElementById('successOverlay').classList.contains('show')) startNewSale();
        if (e.key === 'F2') { e.preventDefault(); startNewSale(); }
    });
</script>
@endsection
