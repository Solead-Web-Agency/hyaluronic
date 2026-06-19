<?php

declare(strict_types=1);

namespace PayplugPluginCore\Gateways;

use PayplugPluginCore\Models\Entities\PaymentInputDTO;

abstract class AbstractPaymentGateway
{
    /**
     * @var string
     */
    protected $id;

    /** @var array<int, string> */
    protected $expected_context;

    /**
     * @return array<string, mixed>
     * @throws \Exception
     */
    abstract public function formatPaymentAttributes(PaymentInputDTO $payment_inputDTO): array;

    /**
     * @return array<string, mixed>
     */
    public function getDefaultAttributeFromDTO(PaymentInputDTO $payment_inputDTO): array
    {
        $customer = $payment_inputDTO->getCustomer() ?? [];
        $urls     = $payment_inputDTO->getUrls() ?? [];

        return [
            'amount'           => $payment_inputDTO->getAmount(),
            'currency'         => $payment_inputDTO->getCurrencyIsoCode(),
            'billing'          => $customer['billing'],
            'shipping'         => $customer['shipping'] + ['delivery_type' => 'BILLING'],
            'hosted_payment'   => [
                'return_url' => $urls['return'],
                'cancel_url' => $urls['cancel'],
            ],
            'notification_url' => $urls['notification'],
            'metadata'         => $payment_inputDTO->getMetadata(),
            'allow_save_card'  => false,
            'force_3ds'        => false,
        ];
    }
}
