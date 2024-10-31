<?php
/**
 * Plugin Name: Pomelo Payment Gateway
 * Plugin URI: https://wordpress.org/plugins/pomelo-payment-gateway
 * Description: Pomelo Payment Gateway
 * Author: Pomelo Pay Developers
 * Version: 1.0.3
 * Author URI: mailto:developers@pomelopay.com
 * Text Domain: pomelo
 * Domain Path: /languages
 */

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/inc/pomelo-api.php');
require_once(__DIR__ . '/inc/webhook-handler.php');

class Pomelo_Woocommerce {

    private static $instance;

    public static $BASE_PATH;
    public static $BASE_URL;

    public static function get_instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
    }

    private function __construct() {
        $this->init_variables();

        // init payment gateway
        add_action('plugins_loaded', array($this, 'init_payment_gateway'));
        add_filter('woocommerce_payment_gateways', array($this, 'woocommerce_payment_gateways'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
        add_action('init', array($this, 'load_text_domain'));
    }

    public function load_text_domain() {
        load_plugin_textdomain('pomelo', false, self::$BASE_PATH . '/languages/');
    }

    /**
     * Show action links on the plugin screen.
     * @param mixed $links Plugin Action links.
     * @return array
     */
    public function plugin_action_links($links) {
        $action_links = array(
            'settings' => sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                admin_url('/admin.php?page=wc-settings&tab=checkout&section=pomelo'),
                esc_attr__('View Pomelo settings', 'pomelo'),
                esc_html__('Settings', 'pomelo')
            ),
        );

        return array_merge($action_links, $links);
    }

    public function woocommerce_payment_gateways($methods) {
        $methods[] = 'WC_Gateway_Pomelo';
        return $methods;
    }

    public function init_payment_gateway() {
        require_once(self::$BASE_PATH . '/inc/wc-gateway-pomelo.php');
    }

    private function init_variables() {
        self::$BASE_PATH = untrailingslashit(plugin_dir_path(__FILE__));
        self::$BASE_URL = untrailingslashit(plugin_dir_url(__FILE__));
    }

}

Pomelo_Woocommerce::get_instance();