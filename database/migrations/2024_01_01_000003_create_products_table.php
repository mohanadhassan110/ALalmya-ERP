<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المنتج
            $table->string('sku')->nullable()->unique(); // كود المنتج
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->decimal('cost_price', 12, 2)->default(0); // سعر التكلفة (الشراء)
            $table->decimal('wholesale_price', 12, 2)->default(0); // سعر الجملة الافتراضي
            $table->decimal('retail_price', 12, 2)->default(0); // سعر التجزئة المقترح
            $table->integer('stock_quantity')->default(0); // الكمية في المخزن
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
