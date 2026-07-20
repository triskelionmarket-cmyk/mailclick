<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class EcommerceOrderItem extends Model
{
    protected $table = 'ecommerce_order_items';

    protected $fillable = [
        'ecommerce_order_id',
        'product_id',
        'source_product_id',
        'title',
        'quantity',
        'price',
        'line_total',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function order()
    {
        return $this->belongsTo('Acelle\Model\EcommerceOrder', 'ecommerce_order_id');
    }

    public function product()
    {
        return $this->belongsTo('Acelle\Model\Product');
    }

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Get formatted price.
     */
    public function getFormattedPrice()
    {
        return number_format($this->price, 2);
    }

    /**
     * Get formatted line total.
     */
    public function getFormattedLineTotal()
    {
        return number_format($this->line_total, 2);
    }
}