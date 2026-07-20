<?php

if (!defined('ABSPATH')) {
    exit;
}

class MailClick_Dashboard {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('wp_ajax_mailclick_trigger_sync', array($this, 'handle_trigger_sync'));
    }

    public function register_admin_menu() {
        add_menu_page(
            'MailClick',
            'MailClick',
            'manage_options',
            'mailclick-connect',
            array($this, 'render_dashboard_page'),
            'dashicons-email-alt2',
            56
        );
    }

    public function render_dashboard_page() {
        $options = get_option('mailclick_connect_options', array());
        $is_connected = !empty($options['api_token']) && !empty($options['store_uid']);
        $connect_url = MailClick_OAuth::get_connect_url();

        ?>
        <div class="wrap mailclick-dashboard-wrap">
            <div class="mailclick-header d-flex justify-content-between align-items-center mb-4">
                <div class="mailclick-brand">
                    <h1 class="wp-heading-inline font-bold">MailClick <span class="badge badge-primary">WooCommerce</span></h1>
                    <p class="description">Platformă integrată de email marketing și analiză predictivă magazin</p>
                </div>
                <div class="mailclick-status">
                    <?php if ($is_connected) : ?>
                        <span class="mc-badge mc-badge-success"><span class="dashicons dashicons-yes-alt"></span> Conectat la MailClick</span>
                    <?php else : ?>
                        <span class="mc-badge mc-badge-warning"><span class="dashicons dashicons-warning"></span> Neconectat</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$is_connected) : ?>
                <!-- Disconnected State -->
                <div class="mc-card mc-connect-box text-center p-5">
                    <div class="mc-icon-hero mb-3">
                        <span class="dashicons dashicons-admin-links"></span>
                    </div>
                    <h2>Conectează magazinul tău la MailClick</h2>
                    <p class="mc-subtitle">Apasă pe butonul de mai jos pentru a autoriza conectarea 1-Click cu datele contului tău de pe platformă.</p>
                    <a href="<?php echo esc_url($connect_url); ?>" class="button button-primary button-hero mc-btn-primary">
                        <span class="dashicons dashicons-rest-api me-2"></span> Conectează cu datele din MailClick
                    </a>
                </div>
            <?php else : ?>
                <!-- Connected Dashboard State -->
                <div class="mc-grid-3 mb-4">
                    <div class="mc-card mc-stat-card">
                        <div class="mc-stat-icon text-primary"><span class="dashicons dashicons-cart"></span></div>
                        <div class="mc-stat-content">
                            <span class="mc-stat-label">Stare Sincronizare</span>
                            <h3 class="mc-stat-value text-success">Activă (La 4 Ore)</h3>
                        </div>
                    </div>
                    <div class="mc-card mc-stat-card">
                        <div class="mc-stat-icon text-warning"><span class="dashicons dashicons-archive"></span></div>
                        <div class="mc-stat-content">
                            <span class="mc-stat-label">ID Magazin MailClick</span>
                            <h3 class="mc-stat-value"><?php echo esc_html(substr($options['store_uid'], 0, 12)); ?>...</h3>
                        </div>
                    </div>
                    <div class="mc-card mc-stat-card">
                        <div class="mc-stat-icon text-info"><span class="dashicons dashicons-calendar-alt"></span></div>
                        <div class="mc-stat-content">
                            <span class="mc-stat-label">Conectat La</span>
                            <h3 class="mc-stat-value"><?php echo esc_html($options['connected_at'] ?? 'Recent'); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="mc-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="m-0">Acțiuni de Sincronizare</h3>
                        <div>
                            <button type="button" id="mc-btn-sync" class="button button-primary me-2">
                                <span class="dashicons dashicons-update me-1"></span> Sincronizează Acum
                            </button>
                            <button type="button" id="mc-btn-disconnect" class="button button-link-delete">
                                Disconectează Magazin
                            </button>
                        </div>
                    </div>
                    <p class="description">Sincronizarea automată rulează în fundal la fiecare 4 ore. Poți declanșa o sincronizare manuală oricând.</p>
                </div>
            <?php endif; ?>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#mc-btn-disconnect').on('click', function(e) {
                e.preventDefault();
                if (!confirm('Ești sigur că vrei să deconectezi magazinul de la MailClick?')) return;

                $.post(ajaxurl, {
                    action: 'mailclick_disconnect',
                    nonce: '<?php echo wp_create_nonce('mailclick_admin_nonce'); ?>'
                }, function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.data.message || 'Eroare la deconectare.');
                    }
                });
            });

            $('#mc-btn-sync').on('click', function(e) {
                e.preventDefault();
                var btn = $(this);
                btn.prop('disabled', true).text('Se sincronizează...');

                $.post(ajaxurl, {
                    action: 'mailclick_trigger_sync',
                    nonce: '<?php echo wp_create_nonce('mailclick_admin_nonce'); ?>'
                }, function(res) {
                    btn.prop('disabled', false).html('<span class="dashicons dashicons-update me-1"></span> Sincronizează Acum');
                    alert(res.data.message || 'Sincronizare solicitată cu succes.');
                });
            });
        });
        </script>
        <?php
    }

    public function handle_trigger_sync() {
        check_ajax_referer('mailclick_admin_nonce', 'nonce');

        $options = get_option('mailclick_connect_options', array());
        if (empty($options['api_token']) || empty($options['store_uid'])) {
            wp_send_json_error(array('message' => 'Magazinul nu este conectat.'));
        }

        $mailclick_url = $options['mailclick_url'] ?? 'https://app.mailclick.ro';
        $endpoint = rtrim($mailclick_url, '/') . '/api/v1/woo/keys';

        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $options['passport_token'],
                'Accept'        => 'application/json',
            ),
            'body' => array(
                'store_uid' => $options['store_uid'],
            ),
        ));

        wp_send_json_success(array('message' => 'Comanda de sincronizare a fost transmisă către MailClick.'));
    }
}
