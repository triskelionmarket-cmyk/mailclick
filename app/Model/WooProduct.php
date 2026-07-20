<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooProduct extends Model
{
    protected $table = 'woo_products';

    protected $fillable = [
        'store_id',
        'woo_product_id',
        'name',
        'sku',
        'price',
        'regular_price',
        'sale_price',
        'purchase_cost',
        'stock_status',
        'stock_quantity',
        'categories_json',
        'images_json',
        'rfm_score',
        'conversion_rate',
    ];

    protected $casts = [
        'categories_json' => 'array',
        'images_json' => 'array',
        'price' => 'float',
        'regular_price' => 'float',
        'sale_price' => 'float',
        'purchase_cost' => 'float',
        'rfm_score' => 'float',
        'conversion_rate' => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(WooStore::class, 'store_id');
    }

    /**
     * Compute gross profit margin based on price and purchase cost.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->price <= 0 || $this->purchase_cost <= 0) {
            return 0.0;
        }

        return round((($this->price - $this->purchase_cost) / $this->price) * 100, 2);
    }
}
