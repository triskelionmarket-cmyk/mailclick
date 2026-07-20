<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooOrder extends Model
{
    protected $table = 'woo_orders';

    protected $fillable = [
        'store_id',
        'woo_order_id',
        'order_number',
        'customer_id',
        'woo_customer_id',
        'customer_email',
        'customer_phone',
        'billing_email',
        'billing_first_name',
        'billing_last_name',
        'total',
        'currency',
        'status',
        'payment_method',
        'items_count',
        'date_created',
    ];

    protected $casts = [
        'total' => 'float',
        'date_created' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(WooStore::class, 'store_id');
    }

    public function customer()
    {
        return $this->belongsTo(WooCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(WooOrderItem::class, 'order_id');
    }
}
