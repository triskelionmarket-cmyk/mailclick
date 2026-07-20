<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class WooReview extends Model
{
    protected $table = 'woo_reviews';

    protected $fillable = [
        'store_id',
        'woo_review_id',
        'woo_product_id',
        'rating',
        'review_text',
        'reviewer_name',
        'reviewer_email',
    ];

    public function store()
    {
        return $this->belongsTo(WooStore::class, 'store_id');
    }
}
