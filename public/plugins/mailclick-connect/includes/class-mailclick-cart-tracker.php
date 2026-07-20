<?php

if (!defined('ABSPATH')) {
    exit;
}

class MailClick_Cart_Tracker {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_ajax_mailclick_track_cart', array($this, 'handle_track_cart'));
        add_action('wp_ajax_nopriv_mailclick_track_cart', array($this, 'handle_track_cart'));
    }

    public function handle_track_cart() {
        check_ajax_referer('mailclick_cart_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Email invalid.'));
        }

        $options = get_option('mailclick_connect_options', array());
        if (empty($options['store_uid'])) {
            wp_send_json_error(array('message' => 'Magazinul nu este conectat la MailClick.'));
        }

        // Get WooCommerce Cart contents
        $cart_items = array();
        if (function_exists('WC') && WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                $_product = $values['data'];
                $cart_items[] = array(
                    'product_id' => $values['product_id'],
                    'name'       => $_product->get_name(),
                    'qty'        => $values['quantity'],
                    'price'      => $_product->get_price(),
                    'total'      => $values['line_total'],
                );
            }
        }

        $payload = array(
            'event'       => 'abandoned_cart',
            'store_uid'   => $options['store_uid'],
            'email'       => $email,
            'phone'       => $phone,
            'cart_items'  => $cart_items,
            'cart_total'  => function_exists('WC') && WC()->cart ? WC()->cart->get_total('edit') : 0,
            'timestamp'   => current_time('mysql'),
        );

        // Forward webhook payload to MailClick App
        $mailclick_url = $options['mailclick_url'] ?? 'https://app.mailclick.ro';
        $webhook_endpoint = rtrim($mailclick_url, '/') . '/api/v1/woo/webhook';

        wp_remote_post($webhook_endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-MailClick-Secret' => $options['webhook_secret'] ?? '',
            ),
            'body'    => json_encode($payload),
            'timeout' => 5,
        ));

        wp_send_json_success(array('message' => 'Cart event captured.'));
    }
}
