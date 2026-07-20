<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooOrderItem extends Model
{
    protected $table = 'woo_order_items';

    protected $fillable = [
        'store_id',
        'order_id',
        'woo_product_id',
        'name',
        'qty',
        'price',
        'total',
    ];

    protected $casts = [
        'price' => 'float',
        'total' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(WooOrder::class, 'order_id');
    }
}
