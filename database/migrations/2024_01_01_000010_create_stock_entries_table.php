<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سجل حركات المخزن (إضافة بضاعة)
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            // supplier_id = null يعني "بضاعة خالصة" (رصيد افتتاحي)
            $table->integer('quantity'); // الكمية المضافة
            $table->decimal('cost_price', 12, 2); // سعر الشراء للقطعة
            $table->decimal('total_cost', 12, 2); // إجمالي تكلفة الدفعة
            $table->boolean('is_opening_stock')->default(false); // هل هي بضاعة خالصة؟
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
    }
};
