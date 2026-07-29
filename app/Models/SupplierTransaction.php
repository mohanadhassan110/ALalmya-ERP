<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierTransaction extends Model
{
    protected $fillable = [
        'supplier_id', 'type', 'amount', 'balance_after', 'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * وصف نوع العملية بالعربي
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'purchase' => 'شراء بضاعة',
            'payment' => 'سداد دفعة',
            'opening_balance' => 'رصيد افتتاحي',
            'adjustment' => 'تعديل يدوي',
            default => $this->type,
        };
    }
}
