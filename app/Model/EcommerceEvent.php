<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class EcommerceEvent extends Model
{
    protected $table = 'ecommerce_events';

    protected $fillable = [
        'source_id',
        'subscriber_id',
        'email',
        'event_type',
        'page_url',
        'source_product_id',
        'product_title',
        'value',
        'meta',
    ];

    // ─── Constants ───────────────────────────────────────────

    const TYPE_PAGE_VIEW = 'page_view';
    const TYPE_PRODUCT_VIEW = 'product_view';
    const TYPE_ADD_TO_CART = 'add_to_cart';
    const TYPE_BEGIN_CHECKOUT = 'begin_checkout';
    const TYPE_PURCHASE = 'purchase';

    // ─── Relationships ───────────────────────────────────────

    public function source()
    {
        return $this->belongsTo('Acelle\Model\Source');
    }

    public function subscriber()
    {
        return $this->belongsTo('Acelle\Model\Subscriber');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Get parsed meta data.
     */
    public function getData()
    {
        return $this->meta ? json_decode($this->meta, true) : [];
    }

    /**
     * Record a tracking event.
     */
    public static function record($data, $source)
    {
        $event = new self();
        $event->source_id = $source->id;
        $event->email = $data['email'] ?? null;
        $event->event_type = $data['event_type'] ?? self::TYPE_PAGE_VIEW;
        $event->page_url = $data['page_url'] ?? $data['url'] ?? null;
        $event->source_product_id = $data['source_product_id'] ?? null;
        $event->product_title = $data['product_title'] ?? null;
        $event->value = $data['value'] ?? null;
        $event->meta = isset($data['meta']) ? json_encode($data['meta']) : null;

        // Try to link to subscriber
        if ($event->email && $source->mail_list_id) {
            $subscriber = Subscriber::where('mail_list_id', $source->mail_list_id)
                ->where('email', $event->email)
                ->first();
            if ($subscriber) {
                $event->subscriber_id = $subscriber->id;
            }
        }

        $event->save();

        return $event;
    }
}