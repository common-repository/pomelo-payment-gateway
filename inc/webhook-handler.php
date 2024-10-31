<?php

class Pomelo_Webhook_Handler {

    private static $instance;

    public static function get_instance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {
    }

    private function __construct() {
        // handle payment redirect
        // add_action('template_redirect', array($this, 'handle_payment_redirect'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }

    public function template_redirect() {
        // webhook
        if (preg_match('@^/pomelo/webhook/?$@', $_SERVER['REQUEST_URI'])) {
            $body = file_get_contents('php://input');
            $body = json_decode($body, true);

            if (self::is_debug()) {
                $logger = wc_get_logger();
                $message = sprintf('WEBHOOK: $_POST = %s; $_GET = %s; $body = %s', json_encode($_POST), json_encode($_GET), json_encode($body));
                $logger->info($message, WC_Gateway_Pomelo::logger_context());
            }

            $order_id = isset($body['localId']) ? $body['localId'] : 0;
            $transaction_id = isset($body['transactionId']) ? $body['transactionId'] : '';
            $signature = isset($body['signature']) ? $body['signature'] : '';

            $this->proceed_payment_callback($order_id, $transaction_id, $signature, true);
            return;
        }

        // payment redirect
        if (preg_match('@^/pomelo/([0-9]+)@', $_SERVER['REQUEST_URI'], $matches)) {
            $order_id = isset($matches[1]) && is_numeric($matches[1]) ? intval($matches[1]) : 0;
            $transaction_id = isset($_GET['transactionId']) ? sanitize_text_field($_GET['transactionId']) : '';
            $signature = isset($_GET['signature']) ? sanitize_text_field($_GET['signature']) : '';
            $this->proceed_payment_callback($order_id, $transaction_id, $signature);
            return;
        }
    }

    /**
     * @param $order_id
     * @param $transaction_id
     * @param $signature
     * @param $isWebhook
     */
    private function proceed_payment_callback($order_id, $transaction_id, $signature, $isWebhook = false) {
        $logger = wc_get_logger();
        if (!$order_id || !$transaction_id || !$signature) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!($order instanceof WC_Order)) {
            return;
        }

        $redirectUrl = $order->{self::get_success_destination_page()}();

        // validate signature
        if (!Pomelo_API::get_instance()->validate_signature($order, $signature)) {
            return;
        }

        $transaction = Pomelo_API::get_instance()->get_order_transaction($order_id, $transaction_id);

        // not a valid transaction
        if (!$transaction) {
            return;
        }

        // update order status
        $logger->info($transaction->state, WC_Gateway_Pomelo::logger_context());
        switch ($transaction->state) {
            // payment is cancelled
            case 'CANCELLED':
                $order->set_transaction_id($transaction_id);
                $order->update_status('failed');
                if (self::is_debug() && $logger instanceof WC_Logger) {
                    $message = sprintf('ORDER_CANCELLED: $order_id = %s; $transaction = %s', $order_id, $transaction->id);
                    $logger->info($message, WC_Gateway_Pomelo::logger_context());
                }
                $redirectUrl = $order->get_checkout_payment_url();
                $logger->info($redirectUrl, WC_Gateway_Pomelo::logger_context());
                break;
            // payment is still pending
            case 'QR_CODE_GENERATED':
                if(!$isWebhook) {
                    $order->set_transaction_id($transaction_id);
                    $order->update_status('failed');
                    if (self::is_debug() && $logger instanceof WC_Logger) {
                        $message = sprintf('ORDER_PENDING: $order_id = %s; $transaction = %s', $order_id, $transaction->id);
                        $logger->info($message, WC_Gateway_Pomelo::logger_context());
                    }
                    $redirectUrl = $order->get_checkout_payment_url();
                    $logger->info($redirectUrl, WC_Gateway_Pomelo::logger_context());
                    break;
                }
            // payment is completed
            case 'FAILED':
                $order->set_transaction_id($transaction_id);
                $order->update_status('failed');
                if (self::is_debug() && $logger instanceof WC_Logger) {
                    $message = sprintf('ORDER_FAILED: $order_id = %s; $transaction = %s', $order_id, $transaction->id);
                    $logger->info($message, WC_Gateway_Pomelo::logger_context());
                }
                $redirectUrl = $order->get_checkout_payment_url();
                $logger->info($redirectUrl, WC_Gateway_Pomelo::logger_context());
                break;
            // payment is completed
            case 'CONFIRMED':
                $order->payment_complete($transaction->id);
                if (self::is_debug() && $logger instanceof WC_Logger) {
                    $message = sprintf('ORDER_CONFIRMED: $order_id = %s; $transaction = %s', $order_id, $transaction->id);
                    $logger->info($message, WC_Gateway_Pomelo::logger_context());
                }
                $logger->info($redirectUrl, WC_Gateway_Pomelo::logger_context());
                break;
            // payment is completed
            case 'AUTHORIZED':
                $order->payment_complete($transaction->id);
                if (self::is_debug() && $logger instanceof WC_Logger) {
                    $message = sprintf('ORDER_AUTHORIZED: $order_id = %s; $transaction = %s', $order_id, $transaction->id);
                    $logger->info($message, WC_Gateway_Pomelo::logger_context());
                }
                $logger->info($redirectUrl, WC_Gateway_Pomelo::logger_context());
                break;
        }

        // redirect to the view order page
        wp_safe_redirect($redirectUrl);
        die;
    }

    /**
     * @return bool
     */
    public static function is_debug() {
        $methods = WC_Payment_Gateways::instance()->get_available_payment_gateways();

        if (!isset($methods['pomelo'])) {
            return false;
        }

        /**
         * @var WC_Gateway_Pomelo $pomelo_payment_gateway
         */
        $pomelo_payment_gateway = $methods['pomelo'];

        return $pomelo_payment_gateway->is_debug();
    }

    public static function get_success_destination_page() {
        $methods = WC_Payment_Gateways::instance()->get_available_payment_gateways();

        if (!isset($methods['pomelo'])) {
            return false;
        }

        /**
         * @var WC_Gateway_Pomelo $pomelo_payment_gateway
         */
        $pomelo_payment_gateway = $methods['pomelo'];

        return $pomelo_payment_gateway->get_destination_page();
    }
}

Pomelo_Webhook_Handler::get_instance();
