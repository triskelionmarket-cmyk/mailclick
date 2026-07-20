<?php
/**
 * Plugin Name: MailClick Connect for WooCommerce
 * Plugin URI: https://mailclick.ro
 * Description: Plugin oficial MailClick pentru WooCommerce: Conectare 1-Click, Sincronizare automată date magazin, Dashboard analitic avansat și Captură în timp real Coș Abandonat.
 * Version: 1.0.0
 * Author: MailClick Team
 * Author URI: https://mailclick.ro
 * License: GPLv2 or later
 * Text Domain: mailclick-connect
 * WC requires at least: 5.0
 * WC tests up to: 9.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('MAILCLICK_CONNECT_VERSION', '1.0.0');
define('MAILCLICK_CONNECT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MAILCLICK_CONNECT_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once MAILCLICK_CONNECT_PLUGIN_DIR . 'includes/class-mailclick-oauth.php';
require_once MAILCLICK_CONNECT_PLUGIN_DIR . 'includes/class-mailclick-dashboard.php';
require_once MAILCLICK_CONNECT_PLUGIN_DIR . 'includes/class-mailclick-cart-tracker.php';
require_once MAILCLICK_CONNECT_PLUGIN_DIR . 'includes/class-mailclick-order-tracker.php';
require_once MAILCLICK_CONNECT_PLUGIN_DIR . 'includes/class-mailclick-product-tracker.php';

class MailClick_Connect_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize sub-modules
        MailClick_OAuth::get_instance();
        MailClick_Dashboard::get_instance();
        MailClick_Cart_Tracker::get_instance();
        MailClick_Order_Tracker::get_instance();
        MailClick_Product_Tracker::get_instance();

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'mailclick') !== false) {
            wp_enqueue_style('mailclick-admin-css', MAILCLICK_CONNECT_PLUGIN_URL . 'assets/css/mailclick-admin.css', array(), MAILCLICK_CONNECT_VERSION);
        }
    }

    public function enqueue_frontend_assets() {
        if (is_checkout() || is_cart() || is_product()) {
            wp_enqueue_script('mailclick-cart-js', MAILCLICK_CONNECT_PLUGIN_URL . 'assets/js/mailclick-cart.js', array('jquery'), MAILCLICK_CONNECT_VERSION, true);
            
            $options = get_option('mailclick_connect_options', array());
            wp_localize_script('mailclick-cart-js', 'mailclick_vars', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mailclick_cart_nonce'),
                'store_uid' => isset($options['store_uid']) ? $options['store_uid'] : '',
                'mailclick_url' => isset($options['mailclick_url']) ? $options['mailclick_url'] : 'https://app.mailclick.ro',
            ));
        }
    }
}

MailClick_Connect_Plugin::get_instance();
