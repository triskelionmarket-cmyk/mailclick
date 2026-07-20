<?php
/**
 * MailClick Product View Tracker for WooCommerce.
 *
 * Tracks single product view events for logged-in users / guests with saved emails
 * and posts product_viewed events to MailClick for Browse Abandonment automations.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MailClick_Product_Tracker {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_product_view_script'));
        add_action('wp_ajax_mailclick_track_product_view', array($this, 'handle_track_product_view'));
        add_action('wp_ajax_nopriv_mailclick_track_product_view', array($this, 'handle_track_product_view'));
    }

    public function enqueue_product_view_script() {
        if (!function_exists('is_product') || !is_product()) {
            return;
        }

        global $post;
        $product = wc_get_product($post->ID);
        if (!$product) {
            return;
        }

        $current_user = wp_get_current_user();
        $email = $current_user->exists() ? $current_user->user_email : '';

        wp_enqueue_script(
            'mailclick-product-view',
            MAILCLICK_CONNECT_PLUGIN_URL . 'assets/js/mailclick-product.js',
            array('jquery'),
            MAILCLICK_CONNECT_VERSION,
            true
        );

        wp_localize_script('mailclick-product-view', 'mailclick_product_vars', array(
            'ajax_url'      => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('mailclick_product_nonce'),
            'user_email'    => $email,
            'product_id'    => $product->get_id(),
            'product_title' => $product->get_name(),
            'price'         => $product->get_price(),
            'page_url'      => get_permalink($product->get_id()),
        ));
    }

    public function handle_track_product_view() {
        check_ajax_referer('mailclick_product_nonce', 'nonce');

        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email'));
        }

        $options = get_option('mailclick_connect_options', array());
        if (empty($options['store_uid'])) {
            wp_send_json_error(array('message' => 'Store not connected to MailClick'));
        }

        $payload = array(
            'event'         => 'product_viewed',
            'store_uid'     => $options['store_uid'],
            'email'         => $email,
            'product_id'    => sanitize_text_field($_POST['product_id'] ?? ''),
            'product_title' => sanitize_text_field($_POST['product_title'] ?? ''),
            'price'         => sanitize_text_field($_POST['price'] ?? ''),
            'page_url'      => esc_url_raw($_POST['page_url'] ?? ''),
            'timestamp'     => current_time('mysql'),
        );

        $mailclick_url = $options['mailclick_url'] ?? 'https://app.mailclick.ro';
        $webhook_endpoint = rtrim($mailclick_url, '/') . '/api/v1/woo/webhook';

        $response = wp_remote_post($webhook_endpoint, array(
            'headers' => array(
                'Content-Type'       => 'application/json',
                'X-MailClick-Secret'  => $options['webhook_secret'] ?? '',
            ),
            'body'    => json_encode($payload),
            'timeout' => 5,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        wp_send_json_success(array('message' => 'Product view tracked'));
    }
}
