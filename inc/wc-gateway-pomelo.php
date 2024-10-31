<?php

if (!class_exists('WC_Payment_Gateway')) {
    return;
}

class WC_Gateway_Pomelo extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'pomelo';
        $this->has_fields = false;
        $this->order_button_text = __('Proceed to payment', 'pomelo');
        $this->method_title = __('Secure Online Payment Gateway', 'pomelo');
        /* translators: %s: Link to WC system status page */
        $this->method_description = __('Secure online payments powered by Pomelo Pay', 'pomelo');
        $this->supports = array(
            'products',
        );

        $this->title = __('Secure Online Payment', 'pomelo');
        $this->description = __('Secure online payments powered by Pomelo Pay', 'pomelo');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // update settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public static function logger_context() {
        return array('source' => 'pomelo-payment-gateway');
    }

    public function process_payment($order_id) {
        global $woocommerce;
        $order = new WC_Order($order_id);

        $transaction = Pomelo_API::get_instance()->create_transaction($order);

        if ($this->is_debug()) {
            $logger = wc_get_logger();
            if ($logger instanceof WC_Logger) {
                if (is_wp_error($transaction)) {
                    $message = sprintf('PROCESS_PAYMENT_ERROR: $order_id = %s; $message = ', $order_id) . $transaction->get_error_message();
                    $logger->error($message, self::logger_context());
                } else {
                    $message = sprintf('PROCESS_PAYMENT: $order_id = %s; $transaction = %s', $order_id, json_encode($transaction));
                    $logger->info($message, self::logger_context());
                }
            }
        }

        // error
        if (is_wp_error($transaction)) {
            wc_add_notice(__('Payment error:', 'pomelo') . $transaction->get_error_message(), 'error');
            return array(
                'result' => 'failure',
                'message' => $transaction->get_error_message(),
            );
        }

        // Mark as on-hold (we're awaiting the cheque)
        $order->update_status('on-hold', __('Awaiting payment status', 'pomelo'));

        // Remove cart
        $woocommerce->cart->empty_cart();

        // success - return redirect to the pomelo payment page
        return array(
            'result' => 'success',
            'redirect' => $transaction->url,
        );
    }

    public function is_debug() {
        return $this->settings['debug'] == 'yes';
    }

    public function is_webhook_enabled() {
        return $this->settings['webhook'] == 'yes';
    }

    public function get_destination_page() {
        if(array_key_exists('destination', $this->settings)) {
            return $this->settings['destination'];
        } else {
            return 'get_view_order_url';
        }
    }

    /**
     * Init settings for gateways.
     */
    public function init_settings() {
        parent::init_settings();
        $this->enabled = !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
    }

    /**
     * @return int validity in hours
     */
    public function get_validity() {
        return is_numeric($this->settings['validity']) ? intval($this->settings['validity']) : 0;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $validity_options = array(
            '' => __('Use the default payment expiry setting managed on your Pomelo account', 'pomelo'),
        );
        for ($validity_in_hours = 1; $validity_in_hours <= 48; $validity_in_hours++) {
            $validity_options[$validity_in_hours] = sprintf('%s %s', $validity_in_hours, _n('hour', 'hours', $validity_in_hours, 'pomelo'));
        }

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'pomelo'),
                'type' => 'checkbox',
                'label' => __('Enable Pomelo Payment Gateway', 'pomelo'),
                'default' => 'yes',
            ),
            'validity' => array(
                'title' => __('Validity', 'pomelo'),
                'type' => 'select',
                // 'class' => 'wc-enhanced-select',
                'description' => __('Payment validity in hours, payments will automatically expiry after this amount of hours if you specify a value here.', 'pomelo'),
                'default' => '',
                // 'desc_tip' => true,
                'options' => $validity_options,
            ),
            'destination' => array(
                'title' => 'Destination page after the order has been created',
                'description' => 'Depending on your theme settings you can select to redirect to the thank you page or the account page',
                'type' => 'select',
                'default' => 'get_checkout_order_received_url',
                'options' => array(
                    'get_checkout_order_received_url' => 'Checkout > Order received (thanks)',
                    'get_view_order_url' => 'Account > View order',
                ) // array of options for select/multiselects only
            ),
            'webhook' => array(
                'title' => __('Webhook Usage', 'pomelo'),
                'type' => 'checkbox',
                'label' => __('Enable webhook', 'pomelo'),
                // 'default' => 'yes',
                'description' => __('If enabled orders will also be updated if asynchronous events are received form Pomelo Pay such as payment timeouts and confirmation or cancellations.', 'pomelo'),

            ),
            'debug' => array(
                'title' => __('Debug Logging', 'pomelo'),
                'type' => 'checkbox',
                'label' => __('Enable debug mode', 'pomelo'),
                'description' => sprintf(__('If enabled, logs can be viewed <a href="%s" target="_blank">here</a>', 'pomelo'), admin_url('/admin.php?page=wc-status&tab=logs')),
            ),
            'api_details' => array(
                'title' => __('API credentials', 'pomelo'),
                'type' => 'title',
                /* translators: %s: URL */
                'description' => sprintf(__('<strong>Note:</strong> Your WooCommerce currency which has been set to %s must match the merchant account currency for your company on Pomelo Pay.', 'pomelo'), get_woocommerce_currency()),
            ),
            'sandbox' => array(
                'title' => __('Sandbox Mode', 'pomelo'),
                'type' => 'checkbox',
                'label' => __('Enable sandbox mode', 'pomelo'),
                'default' => 'yes',
                'description' => __('If enabled the sandbox API keys will be used and payments will be redirected to the sandbox environment at Pomelo Pay. Please note that sandbox payments do not involve real transactions so make sure to disable this before going live.', 'pomelo'),
            ),
            'sandbox_api_id' => array(
                'title' => __('Sandbox API Client ID', 'pomelo'),
                'type' => 'text',
                'description' => __('Get your Sandbox API Client ID credentials from <a href="https://dashboard.dev.pomelopay.com/connect/applications" target="_blank">Pomelo</a>.', 'pomelo'),
                'default' => '',
                // 'desc_tip' => true,
                // 'placeholder' => __('Optional', 'pomelo'),
            ),
            'sandbox_api_key' => array(
                'title' => __('Sandbox API key', 'pomelo'),
                'type' => 'text',
                'description' => __('Get your Sandbox API Key credentials from <a href="https://dashboard.dev.pomelopay.com/connect/applications" target="_blank">Pomelo</a>.', 'pomelo'),
                'default' => '',
                // 'desc_tip' => true,
                // 'placeholder' => __('Optional', 'pomelo'),
            ),
            'live_api_id' => array(
                'title' => __('Live API Client ID', 'pomelo'),
                'type' => 'text',
                'description' => __('Get your Production API credentials from <a href="https://dashboard.pomelopay.com/connect/applications" target="_blank">Pomelo</a>.', 'pomelo'),
                'default' => '',
                // 'desc_tip' => true,
                // 'placeholder' => __('Optional', 'pomelo'),
            ),
            'live_api_key' => array(
                'title' => __('Live API key', 'pomelo'),
                'type' => 'text',
                'description' => __('Get your Production API credentials from <a href="https://dashboard.pomelopay.com/connect/applications" target="_blank">Pomelo</a>.', 'pomelo'),
                'default' => '',
                // 'desc_tip' => true,
                // 'placeholder' => __('Optional', 'pomelo'),
            ),
        );
    }
}