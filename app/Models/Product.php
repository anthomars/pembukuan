<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'name',
        'hpp',
        'sell_price',
        'has_stock',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'hpp' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'has_stock' => 'boolean',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
