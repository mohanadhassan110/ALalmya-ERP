<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'سنتر العالمية للمفروشات')</title>
    <meta name="description" content="نظام إدارة سنتر العالمية للمفروشات - نقطة بيع ومحاسبة متكاملة">

    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Cairo (Arabic optimized) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1a1a2e;
            --primary-light: #16213e;
            --accent: #e94560;
            --accent-hover: #c53049;
            --gold: #f0a500;
            --gold-light: #ffc947;
            --success: #00b894;
            --info: #0984e3;
            --sidebar-width: 260px;
            --bg-body: #f0f2f5;
            --card-shadow: 0 2px 12px rgba(0,0,0,0.08);
            --glass-bg: rgba(255,255,255,0.85);
        }

        * {
            font-family: 'Cairo', sans-serif;
        }

        body {
            background: var(--bg-body);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-light) 100%);
            z-index: 1040;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.15);
        }

        .sidebar-brand h4 {
            color: var(--gold);
            font-weight: 800;
            margin: 0;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .sidebar-brand small {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            display: block;
            margin-top: 4px;
        }

        .sidebar-nav {
            padding: 8px 0;
        }

        .sidebar-nav .nav-section {
            padding: 12px 20px 4px;
            color: rgba(255,255,255,0.35);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-right: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.07);
            border-right-color: var(--gold);
        }

        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(233, 69, 96, 0.15);
            border-right-color: var(--accent);
            font-weight: 600;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.05rem;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link .badge {
            margin-right: auto;
            font-size: 0.6rem;
        }

        .sidebar-footer {
            padding: 12px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
            margin-top: auto;
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== Top Navbar ===== */
        .top-navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 10px 24px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1030;
            min-height: 56px;
        }

        .top-navbar h5 {
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            font-size: 1rem;
        }

        .breadcrumb-nav {
            font-size: 0.78rem;
            color: #888;
        }
        .breadcrumb-nav a {
            color: var(--accent);
            text-decoration: none;
        }
        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .page-content {
            padding: 20px 24px;
        }

        /* ===== Cards ===== */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0,0,0,0.12);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 14px 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .card-body {
            padding: 20px;
        }

        /* ===== Stat Cards ===== */
        .stat-card {
            border-radius: 16px;
            padding: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .stat-card .stat-label {
            font-size: 0.78rem;
            opacity: 0.85;
            font-weight: 500;
        }

        .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #e94560 0%, #ee5a24 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f0a500 0%, #ffc947 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%); }
        .bg-gradient-dark { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .bg-gradient-teal { background: linear-gradient(135deg, #00b894 0%, #55efc4 100%); }

        /* ===== Buttons ===== */
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        .btn-gold {
            background: var(--gold);
            border-color: var(--gold);
            color: #1a1a2e;
            font-weight: 600;
        }
        .btn-gold:hover {
            background: var(--gold-light);
            border-color: var(--gold-light);
            color: #1a1a2e;
        }

        /* ===== Tables ===== */
        .table {
            font-size: 0.85rem;
        }

        .table thead th {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 10px 14px;
            white-space: nowrap;
            font-size: 0.82rem;
        }

        .table tbody td {
            padding: 10px 14px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(233, 69, 96, 0.04);
        }

        /* ===== Forms ===== */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            padding: 9px 14px;
            font-size: 0.85rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.82rem;
            color: #444;
            margin-bottom: 4px;
        }

        /* Required field indicator */
        .form-label .required {
            color: var(--accent);
            margin-right: 2px;
        }

        /* ===== Badges ===== */
        .badge {
            font-weight: 600;
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 8px;
        }

        /* ===== Alerts ===== */
        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeInUp 0.3s ease;
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* ===== Toast Notifications ===== */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast-msg {
            padding: 12px 20px;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 280px;
            cursor: pointer;
        }

        .toast-msg.toast-success { background: linear-gradient(135deg, #00b894, #00cec9); }
        .toast-msg.toast-error { background: linear-gradient(135deg, #e94560, #ee5a24); }
        .toast-msg.toast-warning { background: linear-gradient(135deg, #f0a500, #ffc947); color: #1a1a2e; }
        .toast-msg.toast-info { background: linear-gradient(135deg, #0984e3, #74b9ff); }

        .toast-msg.toast-hiding {
            animation: fadeOutRight 0.3s ease forwards;
        }

        @keyframes fadeOutRight {
            to { opacity: 0; transform: translateX(-30px); }
        }

        /* ===== Sidebar Overlay ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
            backdrop-filter: blur(2px);
        }

        /* ===== Responsive ===== */
        .sidebar-toggle {
            display: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar.show + .sidebar-overlay {
                display: block;
            }
            .main-content {
                margin-right: 0;
            }
            .sidebar-toggle {
                display: inline-flex;
            }
            .page-content {
                padding: 16px;
            }
            .top-navbar {
                padding: 10px 16px;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                padding: 16px;
            }
            .stat-card .stat-value {
                font-size: 1.2rem;
            }
            .page-content {
                padding: 12px;
            }
            /* Responsive table → card view */
            .table-responsive-cards table thead {
                display: none;
            }
            .table-responsive-cards table tbody tr {
                display: block;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 1px 6px rgba(0,0,0,0.08);
                margin-bottom: 8px;
                padding: 12px;
            }
            .table-responsive-cards table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 4px 0;
                border: none;
            }
            .table-responsive-cards table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #666;
                font-size: 0.78rem;
                margin-left: 8px;
            }
        }

        /* ===== Invoice specific ===== */
        .invoice-item-row {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            transition: all 0.2s;
        }
        .invoice-item-row:hover {
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(233,69,96,0.1);
        }

        /* ===== Stock Status ===== */
        .stock-healthy { color: #00b894; }
        .stock-low { color: #f0a500; }
        .stock-out { color: #e94560; }

        /* ===== Cancelled Invoice ===== */
        .invoice-cancelled {
            opacity: 0.65;
            position: relative;
        }
        .invoice-cancelled::after {
            content: 'ملغاة';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 1.5rem;
            font-weight: 900;
            color: rgba(233, 69, 96, 0.25);
            pointer-events: none;
        }

        /* ===== Confirmation Modal ===== */
        .modal-confirm .modal-header {
            background: linear-gradient(135deg, #e94560, #ee5a24);
            color: #fff;
            border-bottom: none;
        }
        .modal-confirm .btn-close {
            filter: brightness(0) invert(1);
        }

        /* ===== Print ===== */
        @media print {
            .sidebar, .top-navbar, .no-print, .sidebar-overlay, .toast-container { display: none !important; }
            .main-content { margin: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd; }
        }

        /* ===== Scrollbar ===== */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #aaa;
        }
        .empty-state i {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
            opacity: 0.4;
        }
        .empty-state p {
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <h4><i class="bi bi-shop"></i> سنتر العالمية</h4>
            <small>نظام إدارة المفروشات</small>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">الرئيسية</div>
            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i> الصفحة الرئيسية
            </a>

            <div class="nav-section">نقطة البيع</div>
            <a href="{{ route('invoices.create-retail') }}" class="nav-link {{ request()->routeIs('invoices.create-retail') ? 'active' : '' }}">
                <i class="bi bi-cart-plus-fill"></i> فاتورة تجزئة
            </a>
            <a href="{{ route('invoices.create-wholesale') }}" class="nav-link {{ request()->routeIs('invoices.create-wholesale') ? 'active' : '' }}">
                <i class="bi bi-truck"></i> فاتورة جملة
            </a>
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.index', 'invoices.show') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> سجل الفواتير
            </a>

            <div class="nav-section">المخزون</div>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> المنتجات
                @php $lowStock = \App\Models\Product::where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count(); @endphp
                @if($lowStock > 0)
                    <span class="badge bg-warning text-dark">{{ $lowStock }}</span>
                @endif
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> الفئات
            </a>
            <a href="{{ route('prices.index') }}" class="nav-link {{ request()->routeIs('prices.*') ? 'active' : '' }}">
                <i class="bi bi-currency-dollar"></i> تحديث الأسعار
            </a>

            <div class="nav-section">العملاء والموردين</div>
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> العملاء
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> الموردين
            </a>

            <div class="nav-section">المالية</div>
            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> المصروفات
            </a>
            <a href="{{ route('reports.dashboard') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i> التقارير المالية
                <span class="badge bg-danger">محمي</span>
            </a>
        </nav>
    </aside>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-dark sidebar-toggle" onclick="toggleSidebar()" aria-label="القائمة">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h5>@yield('page-title', 'سنتر العالمية للمفروشات')</h5>
                    @hasSection('breadcrumb')
                    <div class="breadcrumb-nav">@yield('breadcrumb')</div>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted d-none d-md-inline" style="font-size: 0.8rem;">
                    <i class="bi bi-calendar3"></i> {{ now()->format('Y/m/d') }}
                </span>
                @hasSection('header-actions')
                    @yield('header-actions')
                @endif
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content animate-fade-in">
            @yield('content')
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ===== Sidebar Toggle =====
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').style.display = 'none';
        }

        // Close sidebar when clicking a nav link on mobile
        document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) closeSidebar();
            });
        });

        // ===== Toast Notification System =====
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-triangle-fill',
                warning: 'bi-exclamation-circle-fill',
                info: 'bi-info-circle-fill'
            };

            const toast = document.createElement('div');
            toast.className = `toast-msg toast-${type}`;
            toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i> ${message}`;
            toast.onclick = () => removeToast(toast);
            container.appendChild(toast);

            setTimeout(() => removeToast(toast), duration);
        }

        function removeToast(toast) {
            toast.classList.add('toast-hiding');
            setTimeout(() => toast.remove(), 300);
        }

        // Show flash messages as toasts
        @if(session('success'))
            showToast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
            showToast(@json(session('error')), 'error', 6000);
        @endif

        // Show validation errors as toast
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast(@json($error), 'error', 6000);
            @endforeach
        @endif

        // ===== Confirmation Modal Helper =====
        function confirmAction(title, message, formOrCallback) {
            // Create a simple confirmation modal dynamically
            const existingModal = document.getElementById('confirmModal');
            if (existingModal) existingModal.remove();

            const modalHtml = `
            <div class="modal fade" id="confirmModal" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content modal-confirm">
                        <div class="modal-header">
                            <h6 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>${title}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0" style="font-size: 0.88rem;">${message}</p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="button" class="btn btn-sm btn-danger" id="confirmBtn">تأكيد</button>
                        </div>
                    </div>
                </div>
            </div>`;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('confirmModal'));

            document.getElementById('confirmBtn').onclick = () => {
                if (typeof formOrCallback === 'function') {
                    formOrCallback();
                } else if (formOrCallback instanceof HTMLFormElement) {
                    formOrCallback.submit();
                }
                modal.hide();
            };

            modal.show();
        }

        // ===== Currency Formatter =====
        function formatCurrency(amount) {
            return new Intl.NumberFormat('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(amount) + ' ج.م';
        }
    </script>
    @yield('scripts')
</body>
</html>
