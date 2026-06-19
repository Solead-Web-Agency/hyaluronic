<?php

declare(strict_types=1);

namespace PayplugPluginCore\Gateways;

class PaymentGatewayManager
{
    /**
     * @throws \Exception
     */
    public function load(string $payment_method_name): AbstractPaymentGateway
    {
        if (empty($payment_method_name)) {
            throw new \Exception('Invalid parameter, $payment_method_name given should be a non empty string.');
        }

        $class = '\PayplugPluginCore\Gateways\Payment\\'
            . str_replace('_', '', ucwords($payment_method_name, '_'))
            . 'PaymentGateway';

        if (!class_exists($class)) {
            throw new \Exception('Payment method can\'t be found.');
        }

        $paymentGateway = new $class();
        if (!$paymentGateway instanceof AbstractPaymentGateway) {
            throw new \Exception('Payment method is not a valid PaymentGatewayManager.');
        }

        return $paymentGateway;
    }
}
