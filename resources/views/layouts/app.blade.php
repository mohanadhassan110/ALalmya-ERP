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
            transition: transform 0.3s ease;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
        }

        .sidebar-brand {
            padding: 24px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background: rgba(0,0,0,0.15);
        }

        .sidebar-brand h4 {
            color: var(--gold);
            font-weight: 800;
            margin: 0;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
        }

        .sidebar-brand small {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            display: block;
            margin-top: 4px;
        }

        .sidebar-nav {
            padding: 12px 0;
        }

        .sidebar-nav .nav-section {
            padding: 10px 20px 6px;
            color: rgba(255,255,255,0.35);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-right: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.07);
            border-right-color: var(--gold);
        }

        .sidebar-nav .nav-link.active {
            background: rgba(233, 69, 96, 0.15);
            border-right-color: var(--accent);
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }

        .sidebar-nav .nav-link .badge {
            margin-right: auto;
            font-size: 0.65rem;
        }

        /* ===== Main Content ===== */
        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-right 0.3s ease;
        }

        /* ===== Top Navbar ===== */
        .top-navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 12px 28px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .top-navbar h5 {
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            font-size: 1.05rem;
        }

        .page-content {
            padding: 24px 28px;
        }

        /* ===== Cards ===== */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0,0,0,0.12);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 16px 20px;
            font-weight: 700;
        }

        .card-body {
            padding: 20px;
        }

        /* ===== Stat Cards ===== */
        .stat-card {
            border-radius: 16px;
            padding: 22px;
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
            font-size: 2.2rem;
            opacity: 0.8;
        }

        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
        }

        .stat-card .stat-label {
            font-size: 0.82rem;
            opacity: 0.85;
            font-weight: 500;
        }

        .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #00b894 0%, #00cec9 100%); }
        .bg-gradient-danger { background: linear-gradient(135deg, #e94560 0%, #ee5a24 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f0a500 0%, #ffc947 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%); }
        .bg-gradient-dark { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }

        /* ===== Buttons ===== */
        .btn-primary {
            background: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
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
            font-size: 0.88rem;
        }

        .table thead th {
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            border: none;
            padding: 12px 16px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 16px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: rgba(233, 69, 96, 0.04);
        }

        /* ===== Forms ===== */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            padding: 10px 14px;
            font-size: 0.88rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 6px;
        }

        /* ===== Badges ===== */
        .badge {
            font-weight: 600;
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 8px;
        }

        /* ===== Alerts ===== */
        .alert {
            border: none;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeInUp 0.4s ease;
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
            .main-content {
                margin-right: 0;
            }
            .sidebar-toggle {
                display: inline-flex;
            }
        }

        /* ===== Print ===== */
        @media print {
            .sidebar, .top-navbar, .no-print { display: none !important; }
            .main-content { margin: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd; }
        }

        /* ===== Scrollbar ===== */
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

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
            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.index') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> سجل الفواتير
            </a>

            <div class="nav-section">المخزون</div>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i> المنتجات
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i> الفئات
            </a>
            <a href="{{ route('prices.index') }}" class="nav-link {{ request()->routeIs('prices.*') ? 'active' : '' }}">
                <i class="bi bi-currency-dollar"></i> تحديث الأسعار
            </a>

            <div class="nav-section">الحسابات</div>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> الموردين
            </a>
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> العملاء
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-dark sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h5>@yield('page-title', 'سنتر العالمية للمفروشات')</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted" style="font-size: 0.82rem;">
                    <i class="bi bi-calendar3"></i> {{ now()->format('Y/m/d') }}
                </span>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content animate-fade-in">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
