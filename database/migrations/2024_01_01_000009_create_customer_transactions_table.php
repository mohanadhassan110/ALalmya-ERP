<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سجل حركات العميل (فاتورة / سداد / سلفة)
        Schema::create('customer_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['invoice', 'payment', 'advance', 'refund', 'adjustment']);
            // invoice = فاتورة (تزيد المديونية)
            // payment = سداد (يقلل المديونية)
            // advance = سلفة/دفعة مقدمة (تخلي الرصيد سالب = له)
            // refund = مرتجع
            // adjustment = تعديل يدوي
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->text('description')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_transactions');
    }
};
