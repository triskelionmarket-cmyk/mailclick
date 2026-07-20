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

                update_option('mailclick_connect_options', $options);
                update_option('mailclick_app_url', $options['mailclick_url']);

                wp_redirect(admin_url('admin.php?page=mailclick-connect&connected=1'));
                exit;
            }
        }
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
