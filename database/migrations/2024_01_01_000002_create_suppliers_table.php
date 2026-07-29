<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم المورد
            $table->string('phone')->nullable(); // رقم الهاتف
            $table->string('address')->nullable(); // العنوان
            $table->decimal('initial_balance', 12, 2)->default(0); // الرصيد الافتتاحي (المديونية)
            $table->decimal('current_balance', 12, 2)->default(0); // الرصيد الحالي (المتبقي عليه)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
