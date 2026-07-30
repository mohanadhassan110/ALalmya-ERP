@extends('layouts.app')
@section('title', 'فاتورة تجزئة - سنتر العالمية')
@section('page-title', 'فاتورة تجزئة جديدة')
@section('breadcrumb')
    <a href="{{ route('home') }}">الرئيسية</a> / <a href="{{ route('invoices.index') }}">الفواتير</a> / فاتورة تجزئة
@endsection

@section('styles')
<style>
    /* POS-specific styles */
    .search-results {
        position: absolute;
        top: 100%;
        right: 0;
        left: 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 12px 12px;
        z-index: 100;
        max-height: 350px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        display: none;
    }
    .search-results.show { display: block; }

    .search-result-item {
        padding: 10px 16px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.15s;
    }
    .search-result-item:hover, .search-result-item.highlighted {
        background: rgba(233, 69, 96, 0.06);
    }
    .search-result-item:last-child { border-bottom: none; }

    .search-result-item .product-info {
        flex: 1;
    }
    .search-result-item .product-name {
        font-weight: 600;
        font-size: 0.88rem;
        color: #333;
    }
    .search-result-item .product-meta {
        font-size: 0.75rem;
        color: #888;
        display: flex;
        gap: 12px;
        margin-top: 2px;
    }

    .cart-item {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 8px;
        transition: all 0.2s;
        animation: fadeInUp 0.2s ease;
    }
    .cart-item:hover {
        border-color: var(--accent);
        box-shadow: 0 2px 8px rgba(233,69,96,0.08);
    }

    .cart-item .item-name {
        font-weight: 700;
        font-size: 0.88rem;
    }
    .cart-item .item-meta {
        font-size: 0.75rem;
        color: #888;
    }

    .cart-item .price-warning {
        color: var(--accent);
        font-size: 0.72rem;
        font-weight: 600;
    }
    .cart-item .stock-warning {
        color: #f0a500;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .summary-card {
        position: sticky;
        top: 68px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
    }
    .summary-row.total-row {
        font-size: 1.15rem;
        padding: 10px 0;
        border-top: 2px solid var(--accent);
        margin-top: 4px;
    }

    .change-display {
        background: linear-gradient(135deg, #00b894, #00cec9);
        color: #fff;
        border-radius: 10px;
        padding: 10px 16px;
        text-align: center;
        font-weight: 700;
        margin-top: 8px;
    }
    .change-display.overpaid {
        background: linear-gradient(135deg, #0984e3, #74b9ff);
    }

    /* Success State */
    .success-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        z-index: 2000;
        backdrop-filter: blur(4px);
    }
    .success-overlay.show { display: flex; justify-content: center; align-items: center; }

    .success-card {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 440px;
        width: 90%;
        animation: fadeInUp 0.4s ease;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .success-card .success-icon {
        font-size: 4rem;
        color: var(--success);
        display: block;
        margin-bottom: 16px;
    }
    .success-card h3 {
        font-weight: 800;
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .summary-card {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1020;
            border-radius: 16px 16px 0 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
            max-height: 50vh;
            overflow-y: auto;
        }
        .page-content {
            padding-bottom: 320px !important;
        }
    }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('invoices.store-retail') }}" id="invoiceForm">
    @csrf
    <div class="row g-3">
        {{-- Products Search & Cart --}}
        <div class="col-lg-8">
            {{-- Search Box --}}
            <div class="card mb-3">
                <div class="card-body py-3 position-relative">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text"
                               id="productSearch"
                               class="form-control border-start-0 ps-0"
                               placeholder="ابحث بالاسم أو الباركود أو SKU... (اضغط Enter للإضافة)"
                               autocomplete="off"
                               autofocus>
                    </div>
                    <div class="search-results" id="searchResults"></div>
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-plus-fill me-2"></i>سلة الفاتورة <span class="badge bg-primary" id="cartCount">0</span></span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()" id="clearCartBtn" style="display:none;">
                            <i class="bi bi-trash me-1"></i>تفريغ
                        </button>
                    </div>
                </div>
                <div class="card-body" id="cartContainer">
                    <div id="emptyCart" class="empty-state">
                        <i class="bi bi-cart"></i>
                        <p>ابحث عن منتج وأضفه للسلة لبدء الفاتورة</p>
                        <small class="text-muted">اختصارات: Enter لإضافة • ↑↓ للتنقل</small>
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
                        <span class="text-muted">المجموع الفرعي:</span>
                        <span class="fw-bold" id="subtotal">0.00 ج.م</span>
                    </div>

                    <div class="mb-3 mt-2">
                        <label class="form-label"><i class="bi bi-percent me-1"></i>الخصم</label>
                        <input type="number" name="discount" id="discountInput" class="form-control" step="0.01" min="0" value="0" oninput="recalculate()">
                        <div class="text-danger small mt-1" id="discountWarning" style="display:none;">الخصم أكبر من المجموع!</div>
                    </div>

                    <div class="summary-row total-row">
                        <span class="fw-bold" style="color: var(--accent);">الإجمالي:</span>
                        <span class="fw-bold fs-5" style="color: var(--accent);" id="totalDisplay">0.00 ج.م</span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-cash-stack me-1"></i>المبلغ المدفوع <span class="required">*</span></label>
                        <input type="number" name="paid" id="paidInput" class="form-control form-control-lg text-center fw-bold" step="0.01" min="0" value="0" required oninput="recalculate()">
                    </div>

                    <div class="summary-row">
                        <span class="text-muted">المتبقي:</span>
                        <span class="fw-bold text-danger" id="remainingDisplay">0.00 ج.م</span>
                    </div>

                    <div id="changeDisplay" style="display:none;"></div>

                    <div class="mb-3 mt-3">
                        <label class="form-label"><i class="bi bi-chat-text me-1"></i>ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات اختيارية..."></textarea>
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
        <div id="successChange" style="display:none;" class="change-display mb-3"></div>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
            <a id="successPrintBtn" href="#" target="_blank" class="btn btn-dark">
                <i class="bi bi-printer me-1"></i> طباعة
            </a>
            <a id="successViewBtn" href="#" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i> عرض الفاتورة
            </a>
            <button type="button" class="btn btn-gold" onclick="startNewSale()">
                <i class="bi bi-cart-plus me-1"></i> فاتورة جديدة
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // === Data ===
    const products = @json($products);
    let cart = [];
    let searchHighlightIndex = -1;
    let isSubmitting = false;

    // === Product Search ===
    const searchInput = document.getElementById('productSearch');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        searchHighlightIndex = -1;

        if (query.length < 1) {
            searchResults.classList.remove('show');
            return;
        }

        const matches = products.filter(p =>
            p.name.toLowerCase().includes(query) ||
            (p.sku && p.sku.toLowerCase().includes(query))
        ).slice(0, 15);

        if (matches.length === 0) {
            searchResults.innerHTML = '<div class="text-center text-muted py-3"><i class="bi bi-search me-1"></i>لا توجد نتائج</div>';
        } else {
            searchResults.innerHTML = matches.map((p, i) => {
                const stockClass = p.stock_quantity <= 0 ? 'stock-out' : (p.stock_quantity <= 5 ? 'stock-low' : 'stock-healthy');
                const stockText = p.stock_quantity <= 0 ? 'نفد' : p.stock_quantity;
                const cat = p.category ? p.category.name : '';
                return `<div class="search-result-item" data-product-id="${p.id}" data-index="${i}" onclick="addProductToCart(${p.id})">
                    <div class="product-info">
                        <div class="product-name">${p.name}</div>
                        <div class="product-meta">
                            ${cat ? `<span><i class="bi bi-tag"></i> ${cat}</span>` : ''}
                            ${p.sku ? `<span><i class="bi bi-upc-scan"></i> ${p.sku}</span>` : ''}
                            <span class="${stockClass}"><i class="bi bi-box"></i> ${stockText}</span>
                            ${p.retail_price > 0 ? `<span><i class="bi bi-tag"></i> ${parseFloat(p.retail_price).toFixed(2)} ج.م</span>` : ''}
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge ${p.stock_quantity <= 0 ? 'bg-danger' : 'bg-success'}">${stockText}</span>
                    </div>
                </div>`;
            }).join('');
        }

        searchResults.classList.add('show');
    });

    // Keyboard navigation in search
    searchInput.addEventListener('keydown', function(e) {
        const items = searchResults.querySelectorAll('.search-result-item');
        if (!searchResults.classList.contains('show') || items.length === 0) {
            if (e.key === 'Enter') e.preventDefault();
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            searchHighlightIndex = Math.min(searchHighlightIndex + 1, items.length - 1);
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            searchHighlightIndex = Math.max(searchHighlightIndex - 1, 0);
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (searchHighlightIndex >= 0 && items[searchHighlightIndex]) {
                items[searchHighlightIndex].click();
            } else if (items.length === 1) {
                items[0].click();
            }
        } else if (e.key === 'Escape') {
            searchResults.classList.remove('show');
            searchHighlightIndex = -1;
        }
    });

    function updateHighlight(items) {
        items.forEach((el, i) => {
            el.classList.toggle('highlighted', i === searchHighlightIndex);
            if (i === searchHighlightIndex) el.scrollIntoView({ block: 'nearest' });
        });
    }

    // Close search when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.remove('show');
        }
    });

    // === Cart Management ===
    function addProductToCart(productId) {
        const product = products.find(p => p.id === productId);
        if (!product) return;

        if (product.stock_quantity <= 0) {
            showToast(`${product.name} - نفد من المخزون`, 'error');
            return;
        }

        // Check if already in cart
        const existingIndex = cart.findIndex(item => item.product_id === productId);
        if (existingIndex >= 0) {
            // Increment quantity
            const newQty = cart[existingIndex].quantity + 1;
            if (newQty > product.stock_quantity) {
                showToast(`الكمية المطلوبة أكبر من المتاح (${product.stock_quantity})`, 'warning');
                return;
            }
            cart[existingIndex].quantity = newQty;
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                sku: product.sku,
                category: product.category ? product.category.name : '',
                stock_quantity: product.stock_quantity,
                cost_price: parseFloat(product.cost_price),
                retail_price: parseFloat(product.retail_price) || 0,
                wholesale_price: parseFloat(product.wholesale_price) || 0,
                quantity: 1,
                selling_price: parseFloat(product.retail_price) || 0,
            });
        }

        // Clear search
        searchInput.value = '';
        searchResults.classList.remove('show');
        searchInput.focus();

        renderCart();
        recalculate();
    }

    function updateCartItem(index, field, value) {
        if (field === 'quantity') {
            const newQty = parseInt(value) || 0;
            if (newQty <= 0) {
                removeCartItem(index);
                return;
            }
            if (newQty > cart[index].stock_quantity) {
                showToast(`الكمية المطلوبة أكبر من المتاح (${cart[index].stock_quantity})`, 'warning');
                return;
            }
            cart[index].quantity = newQty;
        } else if (field === 'selling_price') {
            cart[index].selling_price = parseFloat(value) || 0;
        }
        renderCart();
        recalculate();
    }

    function removeCartItem(index) {
        cart.splice(index, 1);
        renderCart();
        recalculate();
    }

    function clearCart() {
        if (cart.length === 0) return;
        confirmAction('تفريغ السلة', 'هل أنت متأكد من تفريغ كل الأصناف؟', () => {
            cart = [];
            renderCart();
            recalculate();
            searchInput.focus();
        });
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const emptyState = document.getElementById('emptyCart');
        const clearBtn = document.getElementById('clearCartBtn');
        const cartCount = document.getElementById('cartCount');

        if (cart.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            clearBtn.style.display = 'none';
            cartCount.textContent = '0';
            return;
        }

        emptyState.style.display = 'none';
        clearBtn.style.display = 'inline-flex';
        cartCount.textContent = cart.length;

        container.innerHTML = cart.map((item, index) => {
            const lineTotal = item.quantity * item.selling_price;
            const belowCost = item.selling_price > 0 && item.selling_price < item.cost_price;
            const stockLow = item.quantity >= item.stock_quantity;

            return `<div class="cart-item">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4 col-12">
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">
                            ${item.category ? `<span class="badge bg-light text-dark me-1">${item.category}</span>` : ''}
                            ${item.sku ? `<span class="text-muted">${item.sku}</span>` : ''}
                            <span class="${item.stock_quantity <= 5 ? 'stock-low' : 'stock-healthy'}">متاح: ${item.stock_quantity}</span>
                        </div>
                        ${belowCost ? `<div class="price-warning"><i class="bi bi-exclamation-triangle"></i> سعر البيع أقل من التكلفة (${item.cost_price.toFixed(2)})</div>` : ''}
                        ${stockLow ? `<div class="stock-warning"><i class="bi bi-exclamation-triangle"></i> ${item.quantity === item.stock_quantity ? 'آخر كمية متاحة' : 'كمية منخفضة'}</div>` : ''}
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label small mb-0">الكمية</label>
                        <input type="number" class="form-control text-center" value="${item.quantity}" min="1" max="${item.stock_quantity}"
                               onchange="updateCartItem(${index}, 'quantity', this.value)"
                               oninput="updateCartItem(${index}, 'quantity', this.value)">
                    </div>
                    <div class="col-4 col-md-3">
                        <label class="form-label small mb-0">سعر البيع</label>
                        <input type="number" class="form-control text-center ${belowCost ? 'border-danger' : ''}" value="${item.selling_price}" step="0.01" min="0"
                               placeholder="${item.retail_price > 0 ? 'مقترح: ' + item.retail_price.toFixed(0) : ''}"
                               onchange="updateCartItem(${index}, 'selling_price', this.value)"
                               oninput="updateCartItem(${index}, 'selling_price', this.value)">
                    </div>
                    <div class="col-3 col-md-2 text-center">
                        <label class="form-label small mb-0 d-block">الإجمالي</label>
                        <strong class="d-block">${lineTotal.toFixed(2)}</strong>
                    </div>
                    <div class="col-1 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeCartItem(${index})" title="حذف">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');

        // Update hidden form inputs
        updateFormInputs();
    }

    function updateFormInputs() {
        // Remove old hidden inputs
        document.querySelectorAll('.cart-hidden-input').forEach(el => el.remove());
        const form = document.getElementById('invoiceForm');

        cart.forEach((item, i) => {
            ['product_id', 'quantity', 'selling_price'].forEach(field => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${i}][${field}]`;
                input.value = item[field];
                input.className = 'cart-hidden-input';
                form.appendChild(input);
            });
        });
    }

    function recalculate() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.quantity * item.selling_price;
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const total = Math.max(0, subtotal - discount);
        const paid = parseFloat(document.getElementById('paidInput').value) || 0;
        const remaining = total - paid;
        const change = paid - total;

        // Update displays
        document.getElementById('subtotal').textContent = subtotal.toFixed(2) + ' ج.م';
        document.getElementById('totalDisplay').textContent = total.toFixed(2) + ' ج.م';
        document.getElementById('remainingDisplay').textContent = Math.max(0, remaining).toFixed(2) + ' ج.م';

        // Discount warning
        const discountWarning = document.getElementById('discountWarning');
        discountWarning.style.display = discount > subtotal ? 'block' : 'none';

        // Change display
        const changeDisplay = document.getElementById('changeDisplay');
        if (paid > 0 && change > 0) {
            changeDisplay.style.display = 'block';
            changeDisplay.className = 'change-display overpaid';
            changeDisplay.innerHTML = `<i class="bi bi-arrow-return-left me-1"></i> الباقي للعميل: <strong>${change.toFixed(2)} ج.م</strong>`;
        } else {
            changeDisplay.style.display = 'none';
        }

        // Submit button state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = cart.length === 0 || discount > subtotal || isSubmitting;
    }

    // === Form Submission ===
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (isSubmitting || cart.length === 0) return;

        isSubmitting = true;
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الحفظ...';

        updateFormInputs();

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showSuccessState(data.invoice);
            } else {
                throw new Error(data.message || 'حدث خطأ');
            }
        })
        .catch(err => {
            showToast(err.message || 'حدث خطأ في حفظ الفاتورة', 'error');
            isSubmitting = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة <small class="d-block" style="font-size: 0.7rem; opacity: 0.8;">Ctrl+Enter</small>';
        });
    });

    function showSuccessState(invoice) {
        const overlay = document.getElementById('successOverlay');
        document.getElementById('successInvoiceNumber').textContent = invoice.invoice_number;
        document.getElementById('successTotal').textContent = parseFloat(invoice.total).toFixed(2) + ' ج.م';
        document.getElementById('successPrintBtn').href = `/invoices/${invoice.id}/print`;
        document.getElementById('successViewBtn').href = `/invoices/${invoice.id}`;

        // Show change if overpaid
        const paid = parseFloat(invoice.paid);
        const total = parseFloat(invoice.total);
        const changeDiv = document.getElementById('successChange');
        if (paid > total) {
            changeDiv.style.display = 'block';
            changeDiv.textContent = `الباقي للعميل: ${(paid - total).toFixed(2)} ج.م`;
        } else {
            changeDiv.style.display = 'none';
        }

        overlay.classList.add('show');
    }

    function startNewSale() {
        cart = [];
        isSubmitting = false;
        renderCart();
        recalculate();
        document.getElementById('discountInput').value = 0;
        document.getElementById('paidInput').value = 0;
        document.querySelector('[name="notes"]').value = '';
        document.getElementById('successOverlay').classList.remove('show');
        document.getElementById('submitBtn').innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> حفظ الفاتورة <small class="d-block" style="font-size: 0.7rem; opacity: 0.8;">Ctrl+Enter</small>';
        searchInput.focus();
    }

    // === Keyboard Shortcuts ===
    document.addEventListener('keydown', function(e) {
        // Ctrl+Enter → Submit
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const form = document.getElementById('invoiceForm');
            if (!document.getElementById('submitBtn').disabled) {
                form.requestSubmit();
            }
        }
        // Escape → Focus search
        if (e.key === 'Escape') {
            const overlay = document.getElementById('successOverlay');
            if (overlay.classList.contains('show')) {
                startNewSale();
            } else {
                searchInput.focus();
            }
        }
        // F2 → New sale
        if (e.key === 'F2') {
            e.preventDefault();
            startNewSale();
        }
    });

    // Initial focus
    searchInput.focus();
</script>
@endsection
