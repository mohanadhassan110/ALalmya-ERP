<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| سنتر العالمية - نظام إدارة المركز
|--------------------------------------------------------------------------
*/

// الصفحة الرئيسية
Route::get('/', function () {
    return view('home');
})->name('home');

// ========== الفئات ==========
Route::resource('categories', CategoryController::class)->except(['show']);

// ========== المنتجات ==========
Route::resource('products', ProductController::class)->except(['show']);
Route::post('products/{product}/add-stock', [ProductController::class, 'addStock'])->name('products.add-stock');
Route::get('api/products/search', [ProductController::class, 'search'])->name('api.products.search');

// ========== الموردين ==========
Route::resource('suppliers', SupplierController::class);
Route::post('suppliers/{supplier}/payment', [SupplierController::class, 'makePayment'])->name('suppliers.payment');

// ========== العملاء ==========
Route::resource('customers', CustomerController::class);
Route::post('customers/{customer}/payment', [CustomerController::class, 'makePayment'])->name('customers.payment');

// ========== الفواتير ==========
Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('invoices/retail/create', [InvoiceController::class, 'createRetail'])->name('invoices.create-retail');
Route::post('invoices/retail', [InvoiceController::class, 'storeRetail'])->name('invoices.store-retail');
Route::get('invoices/wholesale/create', [InvoiceController::class, 'createWholesale'])->name('invoices.create-wholesale');
Route::post('invoices/wholesale', [InvoiceController::class, 'storeWholesale'])->name('invoices.store-wholesale');
Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

// ========== المصروفات ==========
Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

// ========== الأسعار (تحديث سريع) ==========
Route::get('prices', [PriceController::class, 'index'])->name('prices.index');
Route::put('prices/{product}', [PriceController::class, 'update'])->name('prices.update');

// ========== القسم المالي (محمي بكلمة مرور) ==========
Route::get('reports/login', [ReportController::class, 'loginForm'])->name('reports.login');
Route::post('reports/login', [ReportController::class, 'login'])->name('reports.authenticate');
Route::post('reports/logout', [ReportController::class, 'logout'])->name('reports.logout');

Route::middleware(\App\Http\Middleware\FinancialAuth::class)->group(function () {
    Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
});
