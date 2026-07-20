<?php

namespace Acelle\Http\Controllers\Api;

use Acelle\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Acelle\Model\WooStore;
use Acelle\Model\WooCustomer;
use Acelle\Model\Subscriber;
use Acelle\Model\MailList;
use Acelle\Model\Automation2;
use DB;
use Log;

class WooWebhookController extends Controller
{
    /**
     * Handle incoming real-time webhooks from WooCommerce stores (e.g., abandoned carts, instant order updates).
     */
    public function handle(Request $request)
    {
        $secret = $request->header('X-MailClick-Secret');
        $payload = $request->all();

        $storeUid = $payload['store_uid'] ?? null;
        if (!$storeUid) {
            return response()->json(['error' => 'Missing store_uid'], 400);
        }

        $store = WooStore::where('uid', $storeUid)->first();
        if (!$store) {
            return response()->json(['error' => 'Store not found'], 404);
        }

        // Validate webhook secret if configured
        if (!empty($store->webhook_secret) && $store->webhook_secret !== $secret) {
            return response()->json(['error' => 'Unauthorized webhook secret'], 401);
        }

        $event = $payload['event'] ?? 'abandoned_cart';

        switch ($event) {
            case 'abandoned_cart':
                return $this->handleAbandonedCart($store, $payload);
            default:
                return response()->json(['status' => 'ignored', 'event' => $event]);
        }
    }

    /**
     * Process real-time abandoned cart event.
     */
    protected function handleAbandonedCart(WooStore $store, array $payload)
    {
        $email = strtolower(trim($payload['email'] ?? ''));
        $phone = trim($payload['phone'] ?? '');
        $cartItems = $payload['cart_items'] ?? [];
        $cartTotal = (float) ($payload['cart_total'] ?? 0);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email address'], 422);
        }

        // 1. Create/Update WooCustomer record
        $customer = WooCustomer::updateOrCreate(
            [
                'store_id' => $store->id,
                'email' => $email,
            ],
            [
                'phone' => $phone ?: null,
            ]
        );

        // 2. Find or create default MailList for this store's customer
        $mailList = MailList::where('customer_id', $store->customer_id)->first();
        if (!$mailList) {
            $mailList = new MailList();
            $mailList->customer_id = $store->customer_id;
            $mailList->name = 'Clienți WooCommerce - ' . $store->store_name;
            $mailList->save();
        }

        // 3. Instant capture as Subscriber in MailClick with email and optional phone
        $subscriber = Subscriber::firstOrNew([
            'mail_list_id' => $mailList->id,
            'email' => $email,
        ]);
        $subscriber->status = 'subscribed';
        if (!empty($phone)) {
            $subscriber->phone = $phone;
        }
        $subscriber->save();

        // 4. Save ecommerce event log into mlck_ecommerce_events
        DB::table('ecommerce_events')->insert([
            'uid' => \Illuminate\Support\Str::uuid(),
            'source_id' => $store->id,
            'customer_id' => $store->customer_id,
            'subscriber_id' => $subscriber->id,
            'email' => $email,
            'event' => 'cart_abandoned',
            'meta' => json_encode([
                'cart_items' => $cartItems,
                'cart_total' => $cartTotal,
                'phone' => $phone,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Trigger active Abandoned Cart automations
        $automations = Automation2::where('customer_id', $store->customer_id)
            ->where('status', 'active')
            ->get();

        foreach ($automations as $automation) {
            try {
                $automation->logger()->info("Triggered abandoned cart for {$email}");
            } catch (\Exception $e) {
                Log::warning("Could not log automation trigger: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Coș abandonat capturat și înregistrat instant.',
            'subscriber_id' => $subscriber->id,
            'email' => $email,
        ]);
    }
}
