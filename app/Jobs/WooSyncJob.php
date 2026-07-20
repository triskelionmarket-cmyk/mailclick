<?php

namespace Acelle\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Acelle\Model\WooStore;
use Acelle\Model\WooCategory;
use Acelle\Model\WooProduct;
use Acelle\Model\WooCustomer;
use Acelle\Model\WooOrder;
use Acelle\Model\WooOrderItem;
use Acelle\Model\WooReview;
use Acelle\Library\WooApiClient;
use Carbon\Carbon;
use Log;

class WooSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected WooStore $store;
    protected bool $fullSync;

    public function __construct(WooStore $store, bool $fullSync = false)
    {
        $this->store = $store;
        $this->fullSync = $fullSync;
    }

    /**
     * Execute the sync job.
     */
    public function handle(): void
    {
        $this->store->sync_status = 'syncing';
        $this->store->save();

        try {
            $client = new WooApiClient($this->store);

            $params = [];
            if (!$this->fullSync && $this->store->last_synced_at) {
                $params['after'] = $this->store->last_synced_at->toIso8601String();
            }

            // 1. Sync Categories
            $this->syncCategories($client);

            // 2. Sync Products
            $this->syncProducts($client, $params);

            // 3. Sync Reviews
            $this->syncReviews($client, $params);

            // 4. Sync Customers
            $this->syncCustomers($client, $params);

            // 5. Sync Orders & Line Items
            $this->syncOrders($client, $params);

            // 6. Calculate RFM Metrics & Product Scores
            $this->calculateRfmAndScores();

            $this->store->sync_status = 'completed';
            $this->store->last_synced_at = Carbon::now();
            $this->store->save();

            Log::info("WooSyncJob successfully completed for store: {$this->store->store_name} (#{$this->store->id})");
        } catch (\Exception $e) {
            $this->store->sync_status = 'failed';
            $this->store->save();

            Log::error("WooSyncJob failed for store #{$this->store->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync Categories.
     */
    protected function syncCategories(WooApiClient $client): void
    {
        $categories = $client->getCategories();

        foreach ($categories as $catData) {
            WooCategory::updateOrCreate(
                [
                    'store_id' => $this->store->id,
                    'woo_category_id' => $catData['id'],
                ],
                [
                    'name' => $catData['name'] ?? 'Uncategorized',
                    'slug' => $catData['slug'] ?? '',
                ]
            );
        }
    }

    /**
     * Sync Products.
     */
    protected function syncProducts(WooApiClient $client, array $params): void
    {
        $products = $client->getProducts($params);

        foreach ($products as $pData) {
            $price = (float) ($pData['price'] ?? 0);
            $regularPrice = (float) ($pData['regular_price'] ?? $price);
            $salePrice = !empty($pData['sale_price']) ? (float) $pData['sale_price'] : null;
            $stock = is_numeric($pData['stock_quantity'] ?? null) ? (int) $pData['stock_quantity'] : 0;

            WooProduct::updateOrCreate(
                [
                    'store_id' => $this->store->id,
                    'woo_product_id' => $pData['id'],
                ],
                [
                    'name' => $pData['name'] ?? 'Produs Fără Nume',
                    'sku'  => $pData['sku'] ?? null,
                    'price' => $price,
                    'regular_price' => $regularPrice,
                    'sale_price' => $salePrice,
                    'stock_quantity' => $stock,
                    'categories_json' => $pData['categories'] ?? [],
                    'images_json' => $pData['images'] ?? [],
                ]
            );
        }
    }

    /**
     * Sync Reviews.
     */
    protected function syncReviews(WooApiClient $client, array $params): void
    {
        $reviews = $client->getReviews($params);

        foreach ($reviews as $rData) {
            WooReview::updateOrCreate(
                [
                    'store_id' => $this->store->id,
                    'woo_review_id' => $rData['id'],
                ],
                [
                    'woo_product_id' => $rData['product_id'] ?? 0,
                    'rating' => (int) ($rData['rating'] ?? 5),
                    'review_text' => strip_tags($rData['review'] ?? ''),
                    'reviewer_name' => $rData['reviewer'] ?? '',
                    'reviewer_email' => $rData['reviewer_email'] ?? '',
                ]
            );
        }
    }

    /**
     * Sync Customers.
     */
    protected function syncCustomers(WooApiClient $client, array $params): void
    {
        $customers = $client->getCustomers($params);

        foreach ($customers as $cData) {
            $email = strtolower(trim($cData['email'] ?? ''));
            if (empty($email)) {
                continue;
            }

            WooCustomer::updateOrCreate(
                [
                    'store_id' => $this->store->id,
                    'email' => $email,
                ],
                [
                    'woo_customer_id' => $cData['id'] ?? 0,
                    'first_name' => $cData['first_name'] ?? '',
                    'last_name' => $cData['last_name'] ?? '',
                    'phone' => $cData['billing']['phone'] ?? null,
                    'total_spent' => (float) ($cData['total_spent'] ?? 0),
                    'orders_count' => (int) ($cData['orders_count'] ?? 0),
                ]
            );
        }
    }

    /**
     * Sync Orders & Order Items.
     */
    protected function syncOrders(WooApiClient $client, array $params): void
    {
        $orders = $client->getOrders($params);

        foreach ($orders as $oData) {
            $email = strtolower(trim($oData['billing']['email'] ?? ''));
            if (empty($email)) {
                continue;
            }

            $dateCreated = isset($oData['date_created']) ? Carbon::parse($oData['date_created']) : Carbon::now();

            // Find or create customer record for this order
            $wooCustomer = WooCustomer::firstOrCreate(
                [
                    'store_id' => $this->store->id,
                    'email' => $email,
                ],
                [
                    'woo_customer_id' => $oData['customer_id'] ?? 0,
                    'first_name' => $oData['billing']['first_name'] ?? '',
                    'last_name' => $oData['billing']['last_name'] ?? '',
                    'phone' => $oData['billing']['phone'] ?? null,
                ]
            );

            // Update order record
            $order = WooOrder::updateOrCreate(
                [
                    'store_id' => $this->store->id,
                    'woo_order_id' => $oData['id'],
                ],
                [
                    'customer_id' => $wooCustomer->id,
                    'customer_email' => $email,
                    'customer_phone' => $oData['billing']['phone'] ?? null,
                    'total' => (float) ($oData['total'] ?? 0),
                    'status' => $oData['status'] ?? 'completed',
                    'payment_method' => $oData['payment_method_title'] ?? ($oData['payment_method'] ?? 'N/A'),
                    'items_count' => count($oData['line_items'] ?? []),
                    'date_created' => $dateCreated,
                ]
            );

            // Sync Line Items
            if (!empty($oData['line_items']) && is_array($oData['line_items'])) {
                foreach ($oData['line_items'] as $itemData) {
                    WooOrderItem::updateOrCreate(
                        [
                            'store_id' => $this->store->id,
                            'order_id' => $order->id,
                            'woo_product_id' => $itemData['product_id'] ?? 0,
                        ],
                        [
                            'name' => $itemData['name'] ?? '',
                            'qty'  => (int) ($itemData['quantity'] ?? 1),
                            'price' => (float) ($itemData['price'] ?? 0),
                            'total' => (float) ($itemData['total'] ?? 0),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Calculate RFM Scores & Product Performance.
     */
    protected function calculateRfmAndScores(): void
    {
        $now = Carbon::now();

        // 1. Calculate Customer RFM Scores & CLV
        $customers = WooCustomer::where('store_id', $this->store->id)->get();

        foreach ($customers as $cust) {
            $lastOrder = WooOrder::where('store_id', $this->store->id)
                ->where('customer_email', $cust->email)
                ->orderBy('date_created', 'desc')
                ->first();

            $totalSpent = WooOrder::where('store_id', $this->store->id)
                ->where('customer_email', $cust->email)
                ->where('status', 'completed')
                ->sum('total');

            $ordersCount = WooOrder::where('store_id', $this->store->id)
                ->where('customer_email', $cust->email)
                ->where('status', 'completed')
                ->count();

            $recencyDays = $lastOrder ? $now->diffInDays($lastOrder->date_created) : 999;

            // Recency Score (1-5)
            $rScore = match (true) {
                $recencyDays <= 30  => 5,
                $recencyDays <= 60  => 4,
                $recencyDays <= 90  => 3,
                $recencyDays <= 180 => 2,
                default             => 1,
            };

            // Frequency Score (1-5)
            $fScore = match (true) {
                $ordersCount >= 10 => 5,
                $ordersCount >= 5  => 4,
                $ordersCount >= 3  => 3,
                $ordersCount >= 2  => 2,
                default            => 1,
            };

            // Monetary Score (1-5)
            $mScore = match (true) {
                $totalSpent >= 5000 => 5,
                $totalSpent >= 2000 => 4,
                $totalSpent >= 1000 => 3,
                $totalSpent >= 500  => 2,
                default             => 1,
            };

            $combinedRfm = round(($rScore * 0.35) + ($fScore * 0.35) + ($mScore * 0.30), 2);

            // Estimated CLV
            $aov = $ordersCount > 0 ? ($totalSpent / $ordersCount) : 0;
            $clv = round($aov * $ordersCount * 1.25, 2);

            $cust->total_spent = $totalSpent;
            $cust->orders_count = $ordersCount;
            $cust->rfm_recency = $recencyDays;
            $cust->rfm_frequency = $ordersCount;
            $cust->rfm_monetary = $totalSpent;
            $cust->rfm_score = $combinedRfm;
            $cust->clv_estimated = $clv;
            $cust->last_order_at = $lastOrder ? $lastOrder->date_created : null;
            $cust->save();
        }

        // 2. Calculate Product Performance & Recommendation Scores
        $products = WooProduct::where('store_id', $this->store->id)->get();

        foreach ($products as $prod) {
            $unitsSold = WooOrderItem::where('store_id', $this->store->id)
                ->where('woo_product_id', $prod->woo_product_id)
                ->sum('qty');

            $revenue = WooOrderItem::where('store_id', $this->store->id)
                ->where('woo_product_id', $prod->woo_product_id)
                ->sum('total');

            $avgRating = WooReview::where('store_id', $this->store->id)
                ->where('woo_product_id', $prod->woo_product_id)
                ->avg('rating') ?: 5.0;

            $stockFactor = $prod->stock_quantity > 0 ? 1.0 : 0.1;

            // Product Recommendation Score
            $prodScore = round(
                ($unitsSold * 0.40) +
                ($revenue * 0.05) +
                ($avgRating * 20 * 0.15) +
                ($stockFactor * 10 * 0.15),
                2
            );

            $prod->rfm_score = $prodScore;
            $prod->save();
        }
    }
}
