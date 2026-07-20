<?php

if (!defined('ABSPATH')) {
    exit;
}

class MailClick_OAuth {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        add_action('wp_ajax_mailclick_disconnect', array($this, 'handle_disconnect'));
    }

    /**
     * Get OAuth connect URL to MailClick app.
     */
    public static function get_connect_url() {
        $mailclick_url = get_option('mailclick_app_url', 'https://app.mailclick.ro');
        $store_url = site_url();
        $store_name = get_bloginfo('name');
        $callback_url = admin_url('admin.php?page=mailclick-connect&action=oauth_callback');

        $query = http_build_query(array(
            'store_url'    => $store_url,
            'store_name'   => $store_name,
            'callback_url' => $callback_url,
        ));

        return rtrim($mailclick_url, '/') . '/woo/connect?' . $query;
    }

    /**
     * Handle OAuth callback from MailClick.
     */
    public function handle_oauth_callback() {
        if (isset($_GET['page']) && $_GET['page'] === 'mailclick-connect' && isset($_GET['action']) && $_GET['action'] === 'oauth_callback') {
            
            if (isset($_GET['status']) && $_GET['status'] === 'success') {
                $options = array(
                    'store_uid'      => sanitize_text_field($_GET['store_uid'] ?? ''),
                    'api_token'      => sanitize_text_field($_GET['api_token'] ?? ''),
                    'passport_token' => sanitize_text_field($_GET['passport_token'] ?? ''),
                    'webhook_secret' => sanitize_text_field($_GET['webhook_secret'] ?? ''),
                    'mailclick_url'  => esc_url_raw($_GET['mailclick_url'] ?? 'https://app.mailclick.ro'),
                    'connected_at'   => current_time('mysql'),
                );

                // Auto-generate WooCommerce REST API Keys if WooCommerce is active
                $ck_cs = self::create_wc_api_keys();
                if ($ck_cs) {
                    $options['consumer_key'] = $ck_cs['consumer_key'];
                    $options['consumer_secret'] = $ck_cs['consumer_secret'];

                    // Post keys back to MailClick API
                    wp_remote_post(rtrim($options['mailclick_url'], '/') . '/api/v1/woo/keys', array(
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $options['passport_token'],
                            'Accept'        => 'application/json',
                        ),
                        'body' => array(
                            'store_uid'       => $options['store_uid'],
                            'consumer_key'    => $ck_cs['consumer_key'],
                            'consumer_secret' => $ck_cs['consumer_secret'],
                        ),
                    ));
                }

                update_option('mailclick_connect_options', $options);
                update_option('mailclick_app_url', $options['mailclick_url']);

                wp_redirect(admin_url('admin.php?page=mailclick-connect&connected=1'));
                exit;
            }
        }
    }

    /**
     * Helper to programmatically create WooCommerce REST API Consumer Key & Secret.
     */
    public static function create_wc_api_keys() {
        if (!class_exists('WooCommerce')) {
            return false;
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $description = 'MailClick Connect API Key (' . current_time('Y-m-d H:i') . ')';

        $consumer_key    = 'ck_' . wc_rand_hash();
        $consumer_secret = 'cs_' . wc_rand_hash();

        $data = array(
            'user_id'         => $user_id,
            'description'     => $description,
            'permissions'     => 'read_write',
            'consumer_key'    => wc_api_hash($consumer_key),
            'consumer_secret' => $consumer_secret,
            'truncated_key'   => substr($consumer_key, -7),
        );

        $wpdb->insert($wpdb->prefix . 'woocommerce_api_keys', $data, array('%d', '%s', '%s', '%s', '%s', '%s'));

        return array(
            'consumer_key'    => $consumer_key,
            'consumer_secret' => $consumer_secret,
        );
    }

    /**
     * Handle Store Disconnect.
     */
    public function handle_disconnect() {
        check_ajax_referer('mailclick_admin_nonce', 'nonce');

        if (current_user_can('manage_options')) {
            delete_option('mailclick_connect_options');
            wp_send_json_success(array('message' => 'Magazinul a fost deconectat de la MailClick.'));
        }

        wp_send_json_error(array('message' => 'Permisiuni insuficiente.'));
    }
}
