<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware لحماية القسم المالي بكلمة مرور
 */
class FinancialAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من وجود جلسة مصادقة مالية
        if (!session('financial_authenticated')) {
            // حفظ الرابط المطلوب للعودة إليه بعد المصادقة
            session(['financial_intended_url' => $request->url()]);
            return redirect()->route('reports.login');
        }

        return $next($request);
    }
}
