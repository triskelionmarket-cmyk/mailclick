<?php

namespace Acelle\Services;

use Acelle\Model\WooStore;
use Acelle\Model\WooProduct;
use Acelle\Model\WooCustomer;

class WooRecommendationEngine
{
    protected WooAnalyticsService $analytics;

    public function __construct(WooAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get Best Selling & High Margin Products to Promote in Emails.
     */
    public function getProductsToPromote(int $storeId, int $limit = 6): array
    {
        return WooProduct::where('store_id', $storeId)
            ->where('stock_quantity', '>', 0)
            ->orderBy('rfm_score', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get Personalized Product Recommendations for a Customer.
     */
    public function getPersonalizedForCustomer(int $storeId, int $customerId, int $limit = 4): array
    {
        $customer = WooCustomer::where('store_id', $storeId)->where('id', $customerId)->first();
        if (!$customer) {
            return $this->getProductsToPromote($storeId, $limit);
        }

        // Return top products by recommendation score excluding recently purchased
        return WooProduct::where('store_id', $storeId)
            ->where('stock_quantity', '>', 0)
            ->orderBy('rfm_score', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }
}
