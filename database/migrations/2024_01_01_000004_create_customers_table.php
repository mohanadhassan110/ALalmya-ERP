<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم العميل
            $table->string('phone')->nullable(); // رقم الهاتف
            $table->string('address')->nullable(); // العنوان
            $table->enum('type', ['retail', 'wholesale'])->default('wholesale'); // نوع العميل
            $table->decimal('balance', 12, 2)->default(0); // الرصيد: موجب = عليه (مدين) / سالب = له (دائن/سلفة)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
