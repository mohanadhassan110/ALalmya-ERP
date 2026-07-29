<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntry extends Model
{
    protected $fillable = [
        'product_id', 'supplier_id', 'quantity',
        'cost_price', 'total_cost', 'is_opening_stock', 'notes'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'is_opening_stock' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
