<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooCategory extends Model
{
    protected $table = 'woo_categories';

    protected $fillable = [
        'store_id',
        'woo_category_id',
        'name',
        'slug',
    ];

    public function store()
    {
        return $this->belongsTo(WooStore::class, 'store_id');
    }
}
