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
        $products = WooProduct::where('store_id', $selectedStore->id)
            ->search($request->keyword ?? '')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('woo.analytics', [
            'stores'          => $stores,
            'selectedStore'   => $selectedStore,
            'totalRevenue'    => $totalRevenue,
            'totalOrders'     => $totalOrders,
            'totalCustomers'  => $totalCustomers,
            'avgClv'          => round($avgClv, 2),
            'topProducts'     => $topProducts,
            'rfmSegments'     => $rfmSegments,
            'products'        => $products,
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
}
