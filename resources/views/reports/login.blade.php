<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دخول القسم المالي - سنتر العالمية</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #e94560 0%, #c53049 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
    </style>
</head>
<body>
    <div class="login-card animate-fade-in">
        <div class="login-icon">
            <i class="bi bi-shield-lock-fill text-white" style="font-size: 2.2rem;"></i>
        </div>
        <h3 class="text-center fw-bold mb-1">القسم المالي</h3>
        <p class="text-center text-muted mb-4">أدخل كلمة المرور للوصول للتقارير المالية</p>

        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('reports.authenticate') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label fw-bold">كلمة المرور</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="أدخل كلمة المرور" required autofocus>
                </div>
            </div>
            <button type="submit" class="btn btn-danger btn-lg w-100 mb-3">
                <i class="bi bi-unlock-fill me-1"></i> دخول
            </button>
            <div class="text-center">
                <a href="{{ route('home') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-right me-1"></i> العودة للرئيسية</a>
            </div>
        </form>
    </div>
</body>
</html>
