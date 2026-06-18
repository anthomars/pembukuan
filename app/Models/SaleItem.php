<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'barcode_snapshot',
        'product_name_snapshot',
        'qty',
        'sell_price',
        'hpp_snapshot',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'sell_price' => 'decimal:2',
        'hpp_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
