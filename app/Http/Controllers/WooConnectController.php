<?php

namespace Acelle\Http\Controllers;

use Illuminate\Http\Request;
use Acelle\Model\WooStore;
use Illuminate\Support\Str;
use Auth;

class WooConnectController extends Controller
{
    /**
     * OAuth Connect Authorization Page.
     * Shows prompt to user to approve connecting WooCommerce store to MailClick account.
     */
    public function authorizeStore(Request $request)
    {
        $request->validate([
            'store_url' => 'required|url',
            'store_name' => 'nullable|string',
            'callback_url' => 'required|url',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->guest(route('login'));
        }

        $customer = $user->customer;

        return view('woo.connect_authorize', [
            'user' => $user,
            'customer' => $customer,
            'storeUrl' => $request->get('store_url'),
            'storeName' => $request->get('store_name', parse_url($request->get('store_url'), PHP_URL_HOST)),
            'callbackUrl' => $request->get('callback_url'),
        ]);
    }

    /**
     * Approve Store Connection.
     * Creates or updates WooStore record and redirects back to WP plugin with credentials.
     */
    public function approveStore(Request $request)
    {
        $request->validate([
            'store_url' => 'required|url',
            'store_name' => 'nullable|string',
            'callback_url' => 'required|url',
        ]);

        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return back()->with('error', 'Contul tău nu are un profil de client activ.');
        }

        $storeUrl = rtrim($request->get('store_url'), '/');
        $storeName = $request->get('store_name', parse_url($storeUrl, PHP_URL_HOST));

        // Find existing store or create new
        $store = WooStore::where('customer_id', $customer->id)
            ->where('store_url', $storeUrl)
            ->first();

        if (!$store) {
            $store = new WooStore();
            $store->customer_id = $customer->id;
            $store->store_url = $storeUrl;
        }

        $store->store_name = $storeName;
        $store->api_token = Str::random(60);
        $store->webhook_secret = 'whsec_' . Str::random(40);
        $store->sync_status = 'idle';
        $store->save();

        // Issue Passport Token for API
        $token = $user->createToken('WooCommerce Store: ' . $storeName)->accessToken;

        // Redirect back to WP plugin callback
        $query = http_build_query([
            'status' => 'success',
            'store_uid' => $store->uid,
            'api_token' => $store->api_token,
            'passport_token' => $token,
            'webhook_secret' => $store->webhook_secret,
            'mailclick_url' => config('app.url'),
        ]);

        $redirectUrl = $request->get('callback_url') . (str_contains($request->get('callback_url'), '?') ? '&' : '?') . $query;

        return redirect()->away($redirectUrl);
    }
}
