<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTransaction extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'amount', 'balance_after',
        'description', 'invoice_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'invoice' => 'فاتورة',
            'payment' => 'سداد',
            'advance' => 'سلفة / دفعة مقدمة',
            'refund' => 'مرتجع',
            'adjustment' => 'تعديل يدوي',
            default => $this->type,
        };
    }
}
