<?php

namespace Acelle\Http\Controllers\Api;

use Acelle\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Acelle\Model\WooStore;
use Acelle\Model\WooCustomer;
use Acelle\Model\Subscriber;
use Acelle\Model\MailList;
use Acelle\Model\Automation2;
use Acelle\Model\EcommerceOrder;
use Acelle\Model\EcommerceEvent;
use Acelle\Model\Source;
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
            case 'order_completed':
                return $this->handleOrderCompleted($store, $payload);
            case 'customer_vip':
                return $this->handleCustomerVip($store, $payload);
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
            $mailList->name = 'WooCommerce Customers - ' . $store->store_name;
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

        // 4. Save ecommerce event log (use correct column names matching migration)
        DB::table('ecommerce_events')->insert([
            'source_id' => $store->id,
            'subscriber_id' => $subscriber->id,
            'email' => $email,
            'event_type' => 'cart_abandoned',
            'value' => $cartTotal,
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

        $triggeredCount = 0;
        foreach ($automations as $automation) {
            try {
                // Only trigger abandoned-cart type automations
                if ($automation->getTriggerType() !== 'woo-abandoned-cart') {
                    continue;
                }

                // Check if automation is for this store
                $automationSourceUid = $automation->getTriggerAction()->getOption('source_uid');
                if (!$automationSourceUid) {
                    continue;
                }

                // Check if subscriber is in the automation's mail list
                if ($subscriber->mail_list_id != $automation->mail_list_id) {
                    // Try to find subscriber in automation's list
                    $listSubscriber = Subscriber::where('mail_list_id', $automation->mail_list_id)
                        ->where('email', $email)
                        ->first();

                    if (!$listSubscriber) {
                        $automation->logger()->info("Subscriber {$email} not in automation's list, skipping direct trigger");
                        continue;
                    }
                    $subscriber = $listSubscriber;
                }

                // Check if already triggered
                $existingTrigger = $automation->getAutoTriggerFor($subscriber);
                if (!is_null($existingTrigger)) {
                    $automation->logger()->info("Subscriber {$email} already has an active trigger, skipping");
                    continue;
                }

                // Note: the actual email sending waits for checkForAbandonedCart() in the scheduler
                // which respects the configured wait time. Here we just log the event.
                $automation->logger()->info("Abandoned cart event captured for {$email}, will be triggered after wait period");
                $triggeredCount++;
            } catch (\Exception $e) {
                Log::warning("Could not process automation trigger: " . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Abandoned cart captured and recorded.',
            'subscriber_id' => $subscriber->id,
            'email' => $email,
            'automations_notified' => $triggeredCount,
        ]);
    }

    /**
     * Process order completed webhook from WooCommerce.
     */
    protected function handleOrderCompleted(WooStore $store, array $payload)
    {
        $orderData = [
            'source_order_id' => $payload['order_id'] ?? $payload['id'] ?? null,
            'email'           => strtolower(trim($payload['email'] ?? $payload['billing']['email'] ?? '')),
            'first_name'      => $payload['first_name'] ?? $payload['billing']['first_name'] ?? null,
            'last_name'       => $payload['last_name'] ?? $payload['billing']['last_name'] ?? null,
            'status'          => 'completed',
            'total'           => (float) ($payload['total'] ?? 0),
            'currency'        => $payload['currency'] ?? 'RON',
            'ordered_at'      => $payload['date_completed'] ?? $payload['date_created'] ?? now(),
            'items'           => $payload['line_items'] ?? $payload['items'] ?? [],
            'meta'            => $payload['meta'] ?? null,
        ];

        if (empty($orderData['email']) || !filter_var($orderData['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid or missing email'], 422);
        }

        if (empty($orderData['source_order_id'])) {
            return response()->json(['error' => 'Missing order_id'], 422);
        }

        // Find the Source model linked to this WooStore
        $source = \Acelle\Model\Source::where('customer_id', $store->customer_id)->first();
        if (!$source) {
            return response()->json(['error' => 'No source found for this store'], 404);
        }

        // Create/update the order via the existing model method
        $order = EcommerceOrder::createFromWebhook($orderData, $source);

        // Also record a purchase event in ecommerce_events
        EcommerceEvent::record([
            'email'             => $orderData['email'],
            'event_type'        => EcommerceEvent::TYPE_PURCHASE,
            'value'             => $orderData['total'],
            'source_product_id' => $orderData['source_order_id'],
            'product_title'     => 'Order #' . $orderData['source_order_id'],
            'meta'              => [
                'order_id' => $order->id,
                'total'    => $orderData['total'],
                'currency' => $orderData['currency'],
                'items'    => count($orderData['items']),
            ],
        ], $source);

        // Clean up any abandoned cart events for this email (they bought!)
        DB::table('ecommerce_events')
            ->where('source_id', $source->id)
            ->where('email', $orderData['email'])
            ->where('event_type', 'cart_abandoned')
            ->delete();

        Log::info("Order completed webhook processed: #{$orderData['source_order_id']} for {$orderData['email']}");

        return response()->json([
            'status'   => 'success',
            'message'  => 'Order completed processed.',
            'order_id' => $order->id,
            'email'    => $orderData['email'],
        ]);
    }

    /**
     * Handle VIP customer tagging from WooCommerce.
     */
    protected function handleCustomerVip(WooStore $store, array $payload)
    {
        $email = strtolower(trim($payload['email'] ?? ''));
        $tag = $payload['tag'] ?? 'vip-customer';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'Invalid email'], 422);
        }

        // Find subscriber in any mail list of this customer
        $subscriber = Subscriber::whereHas('mailList', function ($q) use ($store) {
            $q->where('customer_id', $store->customer_id);
        })->where('email', $email)->first();

        if (!$subscriber) {
            Log::info("VIP webhook: Subscriber {$email} not found in any list");
            return response()->json(['status' => 'skipped', 'message' => 'Subscriber not found']);
        }

        // Add VIP tag
        $subscriber->addTags([$tag]);

        Log::info("VIP tag '{$tag}' added to subscriber {$email} (total spent: " . ($payload['total_spent'] ?? 'N/A') . ")");

        return response()->json([
            'status'  => 'success',
            'message' => 'VIP tag applied.',
            'email'   => $email,
            'tag'     => $tag,
        ]);
    }
}
