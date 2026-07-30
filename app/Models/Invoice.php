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
        'payment_status', 'status',
        'cancellation_reason', 'cancelled_at',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'remaining' => 'decimal:2',
        'profit' => 'decimal:2',
        'cancelled_at' => 'datetime',
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
     * هل الفاتورة ملغاة؟
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * هل الفاتورة نشطة؟
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === null;
    }

    /**
     * Scope: فقط الفواتير النشطة
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });
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
