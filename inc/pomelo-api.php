<?php

class Pomelo_API {

    private static $instance;
    // private static $settings;

    /**
     * @var WC_Gateway_Pomelo $gateway
     */
    private static $gateway;

    /**
     * @var \PomeloPayConnect\Client $client
     */
    private static $client;

    public static function get_instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
    }

    private function __construct() {
        $this->get_settings();
        $this->init_client();
    }

    /**
     * @param WC_Order $order
     * @param string $signature
     * @return bool
     */
    public function validate_signature($order, $signature) {
        return (new \PomeloPayConnect\Crypt\Verify())->validateSignature(self::$client, $signature, floatval($order->get_total()) * 100, $order->get_currency());
    }

    /**
     * @param int $order_id
     * @param string $transaction_id
     * @return null|object
     */
    public function get_order_transaction($order_id, $transaction_id) {
        try {
            $transaction = self::$client->transactions->get($transaction_id);

            // not the order transaction
            if (!isset($transaction->id) || !isset($transaction->localId) || $transaction->localId != $order_id) {
                return null;
            }

            return $transaction;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * @param WC_Order $order
     * @return WP_Error|object
     */
    public function create_transaction($order) {
        $json = array(
            'currency' => $order->get_currency(),
            'amount' => floatval($order->get_total()) * 100,
            'localId' => $order->get_id(),
            'redirectUrl' => home_url(sprintf('/pomelo/%s', $order->get_id())),
        );

        if (self::$gateway->is_webhook_enabled()) {
            $json['webhook'] = home_url('/pomelo/webhook');
        }

        if ($validity = self::$gateway->get_validity()) {
            $json['validForHours'] = $validity;
        }

        if (Pomelo_Webhook_Handler::is_debug()) {
            $logger = wc_get_logger();
            $message = sprintf('CREATE_TRANSACTION: $json = %s', json_encode($json));
            $logger->info($message, WC_Gateway_Pomelo::logger_context());
        }

        try {
            $transaction = self::$client->transactions->create($json);

            if (!isset($transaction->url) || !$transaction->url) {
                return new WP_Error('unknown_error', 'Unknown error');
            }

            return $transaction;
        } catch (Exception $ex) {
            return new WP_Error($ex->getMessage(), $ex->getMessage());
        }
    }

    private function init_client() {
        if (self::$client instanceof \PomeloPayConnect\Client) {
            return;
        }

        $settings = self::$gateway->settings;

        $sandbox = $settings['sandbox'] == 'yes';

        // live credentials
        $api_id = $settings['live_api_id'];
        $api_key = $settings['live_api_key'];

        // sandbox credentials
        if ($sandbox) {
            $api_id = $settings['sandbox_api_id'];
            $api_key = $settings['sandbox_api_key'];
        }

        $mode = !$sandbox ? 'production' : 'sandbox';
        self::$client = new \PomeloPayConnect\Client($api_key, $api_id, $mode);
    }

    private function get_settings() {
        $methods = WC_Payment_Gateways::instance()->get_available_payment_gateways();

        if (!isset($methods['pomelo'])) {
            return;
        }

        /**
         * @var WC_Gateway_Pomelo $pomelo_payment_gateway
         */
        self::$gateway = $methods['pomelo'];
    }
}