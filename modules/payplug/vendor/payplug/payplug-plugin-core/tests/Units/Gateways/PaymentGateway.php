<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Units\Gateways;

use PayplugPluginCore\Models\Entities\PaymentInputDTO;

class PaymentGateway
{
    /**
     * @var string
     */
    protected $id;
    /** @var array<string, mixed> */
    protected $expected_context;

    /**
     * @param string $payment_gateway_name
     *
     * @throws \Exception
     */
    public function load(string $payment_gateway_name): self
    {
        if (empty($payment_gateway_name)) {
            throw new \Exception('Invalid parameter, $payment_gateway_name given should be a non empty string.');
        }

        $payment_gateway_path = '\PayplugPluginCore\gateways\payment\\'
            . str_replace('_', '', ucwords($payment_gateway_name, '_'))
            . 'PaymentGateway';
        if (!class_exists($payment_gateway_path)) {
            throw new \Exception('Payment Gateway can\'t be found.');
        }

        $instance = new $payment_gateway_path();
        if (!$instance instanceof self) {
            throw new \Exception('Payment Gateway class does not extend PaymentGateway.');
        }

        return $instance;
    }

    /**
     * @param PaymentInputDTO $payment_inputDTO
     *
     * @return array<string, mixed>
     */
    public function getDefaultAttributeFromDTO(PaymentInputDTO $payment_inputDTO): array
    {
        $customer = $payment_inputDTO->getCustomer() ?? [];
        $urls = $payment_inputDTO->getUrls() ?? [];

        return [
            'amount' => $payment_inputDTO->getAmount(),
            'currency' => $payment_inputDTO->getCurrencyIsoCode(),
            'billing' => $customer['billing'] ?? null,
            'shipping' => ($customer['shipping'] ?? []) + ['delivery_type' => 'BILLING'],
            'hosted_payment' => [
                'return_url' => $urls['return'] ?? null,
                'cancel_url' => $urls['cancel'] ?? null,
            ],
            'notification_url' => $urls['notification'] ?? null,
            'metadata' => $payment_inputDTO->getMetadata(),
            'allow_save_card' => false,
            'force_3ds' => false,
        ];
    }
}
