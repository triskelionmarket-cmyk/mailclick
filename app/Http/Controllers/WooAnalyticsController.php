<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Model\WooStore;
use Acelle\Model\WooProduct;
use Acelle\Model\WooOrder;
use Acelle\Model\WooCustomer;
use Acelle\Model\WooCategory;
use Auth;

class WooAnalyticsController extends Controller
{
    /**
     * Display WooCommerce Analytics Dashboard in MailClick.
     */
    public function index(Request $request)
    {
        $customer = Auth::user()->customer;
        if (!$customer) {
            return back()->with('error', 'Profil client inactiv.');
        }

        $stores = WooStore::where('customer_id', $customer->id)->get();
        $selectedStoreId = $request->get('store_id', $stores->first()?->id);

        $selectedStore = $stores->firstWhere('id', $selectedStoreId);

        if (!$selectedStore) {
            return view('woo.analytics_empty', [
                'stores' => $stores,
            ]);
        }

        // Summary Stats
        $totalRevenue = WooOrder::where('store_id', $selectedStore->id)->where('status', 'completed')->sum('total');
        $totalOrders = WooOrder::where('store_id', $selectedStore->id)->count();
        $totalCustomers = WooCustomer::where('store_id', $selectedStore->id)->count();
        $avgClv = WooCustomer::where('store_id', $selectedStore->id)->avg('clv_estimated') ?: 0;

        // Top Recommended Products (by P_score)
        $topProducts = WooProduct::where('store_id', $selectedStore->id)
            ->orderBy('rfm_score', 'desc')
            ->take(10)
            ->get();

        // RFM Customer Segments Breakdown
        $rfmSegments = [
            'champions'  => WooCustomer::where('store_id', $selectedStore->id)->where('rfm_score', '>=', 4.2)->count(),
            'loyal'      => WooCustomer::where('store_id', $selectedStore->id)->whereBetween('rfm_score', [3.2, 4.19])->count(),
            'at_risk'    => WooCustomer::where('store_id', $selectedStore->id)->whereBetween('rfm_score', [2.0, 3.19])->count(),
            'lost'       => WooCustomer::where('store_id', $selectedStore->id)->where('rfm_score', '<', 2.0)->count(),
        ];

        // All Products with Pagination for Purchase Cost editing
        $productsQuery = WooProduct::where('store_id', $selectedStore->id);
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $productsQuery->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }
        $products = $productsQuery->orderBy('id', 'desc')->paginate(15);

        // Winback Candidates
        $analyticsService = new \Acelle\Services\WooAnalyticsService();
        $winbackCustomers = $analyticsService->getWinbackCandidates($selectedStore->id, 10);

        // Recent Orders
        $recentOrders = WooOrder::where('store_id', $selectedStore->id)
            ->orderBy('id', 'desc')
            ->take(15)
            ->get();

        return view('woo.analytics', [
            'stores'            => $stores,
            'selectedStore'     => $selectedStore,
            'totalRevenue'      => $totalRevenue,
            'totalOrders'       => $totalOrders,
            'totalCustomers'    => $totalCustomers,
            'avgClv'            => round($avgClv, 2),
            'topProducts'       => $topProducts,
            'rfmSegments'       => $rfmSegments,
            'products'          => $products,
            'recentOrders'      => $recentOrders,
            'winbackCustomers'  => $winbackCustomers,
        ]);
    }

    /**
     * Inline Update Purchase Cost for WooCommerce Product.
     */
    public function updatePurchaseCost(Request $request, $id)
    {
        $request->validate([
            'purchase_cost' => 'required|numeric|min:0',
        ]);

        $customer = Auth::user()->customer;
        $product = WooProduct::whereHas('store', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->findOrFail($id);

        $product->purchase_cost = (float) $request->input('purchase_cost');
        $product->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Costul de achiziție a fost actualizat cu succes.',
            'product_id' => $product->id,
            'purchase_cost' => number_format($product->purchase_cost, 2),
            'profit_margin' => $product->profit_margin . '%',
        ]);
    }

    /**
     * Inline Update Replenishment Cycle (Days) for WooCommerce Product.
     */
    public function updateReplenishmentDays(Request $request, $id)
    {
        $request->validate([
            'replenishment_days' => 'nullable|integer|min:0',
        ]);

        $customer = Auth::user()->customer;
        $wooProduct = WooProduct::whereHas('store', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })->findOrFail($id);

        $days = $request->input('replenishment_days') ? (int) $request->input('replenishment_days') : null;
        $wooProduct->replenishment_days = $days;
        $wooProduct->save();

        // Also sync to main Product model if exists
        $mainProduct = \Acelle\Model\Product::where('source_id', $wooProduct->store->source_id ?? null)
            ->where('source_item_id', $wooProduct->woo_product_id)
            ->first();

        if ($mainProduct) {
            $mainProduct->replenishment_days = $days;
            $mainProduct->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ciclul de reaprovizionare a fost actualizat.',
            'product_id' => $wooProduct->id,
            'replenishment_days' => $days,
        ]);
    }

    /**
     * Import Purchase Costs via CSV file (columns: sku, purchase_cost or woo_product_id, purchase_cost).
     */
    public function importPurchaseCosts(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:mlck_woo_stores,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $customer = Auth::user()->customer;
        $store = WooStore::where('customer_id', $customer->id)->findOrFail($request->input('store_id'));

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return back()->with('error', 'Fișierul CSV este gol sau nevalid.');
        }

        $header = array_map('strtolower', array_map('trim', $header));
        $skuIdx = array_search('sku', $header);
        $idIdx = array_search('woo_product_id', $header);
        $costIdx = array_search('purchase_cost', $header);

        if ($costIdx === false || ($skuIdx === false && $idIdx === false)) {
            return back()->with('error', 'Fișierul CSV trebuie să conțină coloanele "purchase_cost" și "sku" sau "woo_product_id".');
        }

        $updatedCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $cost = isset($row[$costIdx]) ? (float) trim($row[$costIdx]) : null;
            if ($cost === null || $cost < 0) {
                continue;
            }

            $query = WooProduct::where('store_id', $store->id);
            if ($skuIdx !== false && !empty($row[$skuIdx])) {
                $query->where('sku', trim($row[$skuIdx]));
            } elseif ($idIdx !== false && !empty($row[$idIdx])) {
                $query->where('woo_product_id', trim($row[$idIdx]));
            } else {
                continue;
            }

            $product = $query->first();
            if ($product) {
                $product->purchase_cost = $cost;
                $product->save();
                $updatedCount++;
            }
        }
        fclose($handle);

        return back()->with('success', "Au fost actualizate costurile de achiziție pentru {$updatedCount} produse.");
    }
}
