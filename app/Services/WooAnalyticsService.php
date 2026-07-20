<?php

namespace Acelle\Services;

use Acelle\Model\WooStore;
use Acelle\Model\WooProduct;
use Acelle\Model\WooOrder;
use Acelle\Model\WooOrderItem;
use Acelle\Model\WooCustomer;
use Acelle\Model\WooReview;
use DB;

class WooAnalyticsService
{
    /**
     * Get Store KPIs (Revenue, Orders, AOV, Repeat Rate, Churn Rate).
     */
    public function getStoreKPIs(int $storeId): array
    {
        $totalRevenue = (float) WooOrder::where('store_id', $storeId)->where('status', 'completed')->sum('total');
        $totalOrders = WooOrder::where('store_id', $storeId)->count();
        $totalCustomers = WooCustomer::where('store_id', $storeId)->count();

        $aov = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0.0;

        $repeatCustomers = WooCustomer::where('store_id', $storeId)->where('orders_count', '>=', 2)->count();
        $repeatRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 2) : 0.0;

        $inactiveCustomers = WooCustomer::where('store_id', $storeId)->where('rfm_recency', '>', 90)->count();
        $churnRate = $totalCustomers > 0 ? round(($inactiveCustomers / $totalCustomers) * 100, 2) : 0.0;

        $cartRecoveryRate = 0.0;
        try {
            if (\Schema::hasTable('ecommerce_events')) {
                $recoveredCarts = DB::table('ecommerce_events')
                    ->where('source_id', $storeId)
                    ->where('event_type', 'cart_recovered')
                    ->count();

                $abandonedCarts = DB::table('ecommerce_events')
                    ->where('source_id', $storeId)
                    ->where('event_type', 'cart_abandoned')
                    ->count();

                $cartRecoveryRate = $abandonedCarts > 0 ? round(($recoveredCarts / $abandonedCarts) * 100, 2) : 0.0;
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'aov' => $aov,
            'repeat_rate' => $repeatRate,
            'churn_rate' => $churnRate,
            'cart_recovery_rate' => $cartRecoveryRate,
        ];
    }

    /**
     * Get Cross-sell products ("Frequently Bought Together") for a given product.
     */
    public function getCrossSellProducts(int $storeId, int $wooProductId, int $limit = 5): array
    {
        $coPurchased = DB::table('woo_order_items as oi1')
            ->join('woo_order_items as oi2', function ($join) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                     ->on('oi1.woo_product_id', '!=', 'oi2.woo_product_id');
            })
            ->where('oi1.store_id', $storeId)
            ->where('oi1.woo_product_id', $wooProductId)
            ->select('oi2.woo_product_id', DB::raw('COUNT(*) as co_purchases'))
            ->groupBy('oi2.woo_product_id')
            ->orderBy('co_purchases', 'desc')
            ->limit($limit)
            ->pluck('woo_product_id')
            ->toArray();

        if (empty($coPurchased)) {
            return WooProduct::where('store_id', $storeId)
                ->where('woo_product_id', '!=', $wooProductId)
                ->orderBy('rfm_score', 'desc')
                ->take($limit)
                ->get()
                ->toArray();
        }

        return WooProduct::where('store_id', $storeId)
            ->whereIn('woo_product_id', $coPurchased)
            ->get()
            ->toArray();
    }

    /**
     * Get Winback Target Customers (RFM "At Risk" or "Lost" with high past CLV).
     */
    public function getWinbackCandidates(int $storeId, int $limit = 50): array
    {
        return WooCustomer::where('store_id', $storeId)
            ->where('rfm_recency', '>', 60)
            ->where('total_spent', '>', 200)
            ->orderBy('clv_estimated', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }
}
