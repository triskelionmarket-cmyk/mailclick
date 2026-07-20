<?php
/**
 * MailClick Order Tracker for WooCommerce.
 *
 * Sends order_completed webhook to MailClick when a WooCommerce order
 * transitions to "completed" status. Also handles VIP auto-tagging
 * based on cumulative customer spending.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MailClick_Order_Tracker {

    private static $instance = null;

    /**
     * VIP spending threshold in store currency.
     * Customers who exceed this total get tagged as VIP.
     */
    const VIP_THRESHOLD = 5000;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Fire when order status changes to completed
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completed'), 10, 1);

        // Also catch status transitions for safety
        add_action('woocommerce_order_status_changed', array($this, 'handle_order_status_changed'), 10, 4);
    }

    /**
     * Handle order completed event.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public function handle_order_completed($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Prevent duplicate sends (meta flag)
        if ($order->get_meta('_mailclick_order_sent')) {
            return;
        }

        $this->send_order_webhook($order);

        // Mark as sent to prevent duplicates
        $order->update_meta_data('_mailclick_order_sent', current_time('mysql'));
        $order->save();

        // Check VIP threshold
        $this->check_vip_threshold($order);
    }

    /**
     * Handle order status change (fallback for edge cases).
     */
    public function handle_order_status_changed($order_id, $old_status, $new_status, $order) {
        if ($new_status !== 'completed') {
            return;
        }

        // The main hook above should handle it, but this is a safety net
        if ($order->get_meta('_mailclick_order_sent')) {
            return;
        }

        $this->send_order_webhook($order);

        $order->update_meta_data('_mailclick_order_sent', current_time('mysql'));
        $order->save();

        $this->check_vip_threshold($order);
    }

    /**
     * Send order data to MailClick webhook endpoint.
     *
     * @param WC_Order $order
     */
    private function send_order_webhook($order) {
        $options = get_option('mailclick_connect_options', array());

        if (empty($options['store_uid'])) {
            return; // Not connected
        }

        // Build line items
        $line_items = array();
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $line_items[] = array(
                'source_product_id' => $product ? $product->get_id() : null,
                'title'             => $item->get_name(),
                'quantity'          => $item->get_quantity(),
                'price'             => (float) $order->get_item_total($item, false, true),
                'line_total'        => (float) $item->get_total(),
            );
        }

        $payload = array(
            'event'          => 'order_completed',
            'store_uid'      => $options['store_uid'],
            'order_id'       => $order->get_id(),
            'email'          => $order->get_billing_email(),
            'first_name'     => $order->get_billing_first_name(),
            'last_name'      => $order->get_billing_last_name(),
            'total'          => (float) $order->get_total(),
            'currency'       => $order->get_currency(),
            'date_completed' => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d H:i:s') : current_time('mysql'),
            'date_created'   => $order->get_date_created() ? $order->get_date_created()->date('Y-m-d H:i:s') : current_time('mysql'),
            'line_items'     => $line_items,
            'meta'           => array(
                'payment_method'  => $order->get_payment_method_title(),
                'shipping_method' => $order->get_shipping_method(),
                'order_number'    => $order->get_order_number(),
                'customer_note'   => $order->get_customer_note(),
            ),
        );

        $mailclick_url = $options['mailclick_url'] ?? 'https://app.mailclick.ro';
        $webhook_endpoint = rtrim($mailclick_url, '/') . '/api/v1/woo/webhook';

        $response = wp_remote_post($webhook_endpoint, array(
            'headers' => array(
                'Content-Type'      => 'application/json',
                'X-MailClick-Secret' => $options['webhook_secret'] ?? '',
            ),
            'body'    => json_encode($payload),
            'timeout' => 10,
        ));

        if (is_wp_error($response)) {
            error_log('[MailClick] Order webhook failed for #' . $order->get_id() . ': ' . $response->get_error_message());
        }
    }

    /**
     * Check if customer has reached VIP spending threshold.
     * If so, send a VIP tagging event to MailClick.
     *
     * @param WC_Order $order
     */
    private function check_vip_threshold($order) {
        $email = $order->get_billing_email();
        if (empty($email)) {
            return;
        }

        // Calculate total spending for this customer
        $customer_orders = wc_get_orders(array(
            'billing_email' => $email,
            'status'        => array('wc-completed'),
            'limit'         => -1,
            'return'        => 'ids',
        ));

        $total_spent = 0;
        foreach ($customer_orders as $coid) {
            $co = wc_get_order($coid);
            if ($co) {
                $total_spent += (float) $co->get_total();
            }
        }

        // Check if they've already been tagged
        $options = get_option('mailclick_connect_options', array());
        $vip_tagged = get_user_meta_by_email($email, '_mailclick_vip_tagged');

        if ($total_spent >= self::VIP_THRESHOLD && empty($vip_tagged)) {
            // Send VIP tag event
            $mailclick_url = $options['mailclick_url'] ?? 'https://app.mailclick.ro';
            $webhook_endpoint = rtrim($mailclick_url, '/') . '/api/v1/woo/webhook';

            $payload = array(
                'event'     => 'customer_vip',
                'store_uid' => $options['store_uid'] ?? '',
                'email'     => $email,
                'total_spent' => $total_spent,
                'tag'       => 'vip-customer',
            );

            wp_remote_post($webhook_endpoint, array(
                'headers' => array(
                    'Content-Type'      => 'application/json',
                    'X-MailClick-Secret' => $options['webhook_secret'] ?? '',
                ),
                'body'    => json_encode($payload),
                'timeout' => 5,
            ));

            // Mark as tagged (use option since customer may not have WP account)
            update_option('_mailclick_vip_' . md5($email), current_time('mysql'));
        }
    }
}

/**
 * Helper: Get meta by email (for guests without WP accounts).
 */
function get_user_meta_by_email($email, $key) {
    return get_option('_mailclick_vip_' . md5($email), '');
}
