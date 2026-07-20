<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class WooStore extends Model
{
    use HasUid;

    protected $table = 'woo_stores';

    protected $fillable = [
        'customer_id',
        'store_url',
        'store_name',
        'api_token',
        'consumer_key',
        'consumer_secret',
        'webhook_secret',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(WooProduct::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(WooOrder::class, 'store_id');
    }

    public function customers()
    {
        return $this->hasMany(WooCustomer::class, 'store_id');
    }

    public function categories()
    {
        return $this->hasMany(WooCategory::class, 'store_id');
    }

    public function sync()
    {
        try {
            $this->sync_status = 'syncing';
            $this->save();

            \Artisan::call('woo:import-dump', [
                '--customer_id' => $this->customer_id ?? 1,
                '--seed' => true,
            ]);

            $this->last_synced_at = now();
            $this->sync_status = 'completed';
            $this->save();
        } catch (\Exception $e) {
            $this->sync_status = 'failed';
            $this->save();
            \Log::error('WooStore sync failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
