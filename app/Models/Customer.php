<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'address', 'type', 'balance', 'notes'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CustomerTransaction::class)->orderBy('created_at', 'desc');
    }

    /**
     * هل العميل له رصيد دائن (سلفة/دفعة مقدمة)؟
     */
    public function hasCreditBalance(): bool
    {
        return $this->balance < 0;
    }

    /**
     * قيمة الرصيد الدائن (السلفة)
     */
    public function getCreditAmountAttribute(): float
    {
        return $this->balance < 0 ? abs($this->balance) : 0;
    }

    /**
     * قيمة المديونية
     */
    public function getDebtAmountAttribute(): float
    {
        return $this->balance > 0 ? $this->balance : 0;
    }
}
