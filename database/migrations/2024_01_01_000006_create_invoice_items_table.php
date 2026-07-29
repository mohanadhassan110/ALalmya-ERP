<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name'); // اسم المنتج (snapshot وقت البيع)
            $table->integer('quantity')->default(1); // الكمية
            $table->decimal('cost_price', 12, 2)->default(0); // سعر التكلفة وقت البيع (snapshot)
            $table->decimal('selling_price', 12, 2)->default(0); // سعر البيع الفعلي
            $table->decimal('line_total', 12, 2)->default(0); // الإجمالي = الكمية × سعر البيع
            $table->decimal('line_profit', 12, 2)->default(0); // الربح = (سعر البيع - سعر التكلفة) × الكمية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
