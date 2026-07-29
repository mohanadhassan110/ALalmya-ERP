<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // رقم الفاتورة
            $table->enum('type', ['retail', 'wholesale']); // نوع الفاتورة: تجزئة / جملة
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null'); // العميل (اختياري للتجزئة)
            $table->decimal('subtotal', 12, 2)->default(0); // المجموع قبل الخصم
            $table->decimal('discount', 12, 2)->default(0); // الخصم
            $table->decimal('total', 12, 2)->default(0); // الإجمالي النهائي
            $table->decimal('paid', 12, 2)->default(0); // المدفوع
            $table->decimal('remaining', 12, 2)->default(0); // المتبقي
            $table->decimal('profit', 12, 2)->default(0); // الربح المخفي (لا يظهر للعميل)
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('paid'); // حالة الدفع
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
