<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class EcommerceOrder extends Model
{
    use HasUid;

    protected $table = 'ecommerce_orders';

    protected $fillable = [
        'source_id',
        'customer_id',
        'subscriber_id',
        'source_order_id',
        'email',
        'first_name',
        'last_name',
        'status',
        'total',
        'currency',
        'meta',
        'ordered_at',
    ];

    protected $dates = ['ordered_at'];

    // ─── Relationships ───────────────────────────────────────

    public function source()
    {
        return $this->belongsTo('Acelle\Model\Source');
    }

    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function subscriber()
    {
        return $this->belongsTo('Acelle\Model\Subscriber');
    }

    public function items()
    {
        return $this->hasMany('Acelle\Model\EcommerceOrderItem');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeBySource($query, $sourceId)
    {
        return $query->where('source_id', $sourceId);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $keyword)
    {
        if (!empty(trim($keyword))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('email', 'like', '%' . $keyword . '%')
                    ->orWhere('source_order_id', 'like', '%' . $keyword . '%')
                    ->orWhere('first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $keyword . '%');
            });
        }
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
     * Get full name.
     */
    public function getFullName()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotal()
    {
        return number_format($this->total, 2) . ' ' . $this->currency;
    }

    /**
     * Link order to a subscriber by email.
     */
    public function linkToSubscriber()
    {
        if ($this->subscriber_id) {
            return;
        }

        $source = $this->source;
        if (!$source || !$source->mail_list_id) {
            return;
        }

        $subscriber = Subscriber::where('mail_list_id', $source->mail_list_id)
            ->where('email', $this->email)
            ->first();

        if ($subscriber) {
            $this->subscriber_id = $subscriber->id;
            $this->save();
        }
    }

    /**
     * Create or update an order from webhook data.
     */
    public static function createFromWebhook($data, $source)
    {
        $order = self::where('source_id', $source->id)
            ->where('source_order_id', $data['source_order_id'])
            ->first();

        if (!$order) {
            $order = new self();
            $order->uid = uniqid();
            $order->source_id = $source->id;
            $order->customer_id = $source->customer_id;
            $order->source_order_id = $data['source_order_id'];
        }

        $order->email = $data['email'];
        $order->first_name = $data['first_name'] ?? null;
        $order->last_name = $data['last_name'] ?? null;
        $order->status = $data['status'] ?? 'pending';
        $order->total = $data['total'] ?? 0;
        $order->currency = $data['currency'] ?? 'RON';
        $order->meta = isset($data['meta']) ? json_encode($data['meta']) : null;
        $order->ordered_at = $data['ordered_at'] ?? now();
        $order->save();

        // Sync line items
        if (isset($data['items']) && is_array($data['items'])) {
            // Remove old items and recreate
            $order->items()->delete();

            foreach ($data['items'] as $itemData) {
                $item = new EcommerceOrderItem();
                $item->ecommerce_order_id = $order->id;
                $item->source_product_id = $itemData['source_product_id'] ?? null;
                $item->title = $itemData['title'] ?? 'Unknown Product';
                $item->quantity = $itemData['quantity'] ?? 1;
                $item->price = $itemData['price'] ?? 0;
                $item->line_total = $itemData['line_total'] ?? ($item->price * $item->quantity);

                // Try to link to existing product
                if ($item->source_product_id) {
                    $product = Product::where('source_id', $source->id)
                        ->where('source_item_id', $item->source_product_id)
                        ->first();
                    if ($product) {
                        $item->product_id = $product->id;
                    }
                }

                $item->save();
            }
        }

        // Try to link to subscriber
        $order->linkToSubscriber();

        return $order;
    }

    /**
     * Get total revenue for a source.
     */
    public static function getTotalRevenue($sourceId, $status = 'completed')
    {
        return self::where('source_id', $sourceId)
            ->where('status', $status)
            ->sum('total');
    }

    /**
     * Get order count for a source.
     */
    public static function getOrderCount($sourceId, $status = null)
    {
        $query = self::where('source_id', $sourceId);
        if ($status) {
            $query->where('status', $status);
        }
        return $query->count();
    }
}