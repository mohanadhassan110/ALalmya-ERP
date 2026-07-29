<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سجل حركات المورد (شراء بضاعة / سداد ديون)
        Schema::create('supplier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['purchase', 'payment', 'opening_balance', 'adjustment']);
            // purchase = شراء بضاعة (يزيد المديونية)
            // payment = سداد دفعة (يقلل المديونية)
            // opening_balance = رصيد افتتاحي
            // adjustment = تعديل يدوي
            $table->decimal('amount', 12, 2); // المبلغ
            $table->decimal('balance_after', 12, 2); // الرصيد بعد العملية
            $table->text('description')->nullable(); // وصف العملية
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_transactions');
    }
};
