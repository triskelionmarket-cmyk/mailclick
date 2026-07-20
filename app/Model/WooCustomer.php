<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooCustomer extends Model
{
    protected $table = 'woo_customers';

    protected $fillable = [
        'store_id',
        'woo_customer_id',
        'email',
        'phone',
        'first_name',
        'last_name',
        'total_spent',
        'orders_count',
        'rfm_recency',
        'rfm_frequency',
        'rfm_monetary',
        'rfm_score',
        'clv_estimated',
        'last_order_at',
    ];

    protected $casts = [
        'total_spent' => 'float',
        'rfm_monetary' => 'float',
        'rfm_score' => 'float',
        'clv_estimated' => 'float',
        'last_order_at' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(WooStore::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(WooOrder::class, 'customer_id');
    }
}
