<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'type', 'customer_id',
        'subtotal', 'discount', 'total',
        'paid', 'remaining', 'profit',
        'payment_status', 'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'remaining' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * توليد رقم فاتورة تلقائي
     */
    public static function generateInvoiceNumber(string $type): string
    {
        $prefix = $type === 'retail' ? 'RET' : 'WHL';
        $lastInvoice = self::where('type', $type)->latest('id')->first();
        $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, 4)) + 1) : 1;
        return $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
