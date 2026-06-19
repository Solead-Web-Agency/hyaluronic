<?php

declare(strict_types=1);

namespace PayplugPluginCore\Gateways\Payment;

use PayplugPluginCore\Gateways\AbstractPaymentGateway;
use PayplugPluginCore\Models\Entities\PaymentInputDTO;

class EmailLinkPaymentGateway extends AbstractPaymentGateway
{
    public function __construct()
    {
        $this->id = 'email_link';
        $this->expected_context = [];
    }

    /**
     * @param PaymentInputDTO $payment_inputDTO
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function formatPaymentAttributes(PaymentInputDTO $payment_inputDTO): array
    {
        $attributes = $this->getDefaultAttributeFromDTO($payment_inputDTO);
        if (empty($attributes)) {
            throw new \Exception('Can\'t generate default payment attributes');
        }

        // todo : Set validator to check this point
        // Vérification des clés attendues dans $context
        $context = $payment_inputDTO->getContext() ?? [];
        foreach ($this->expected_context as $key) {
            if (!\array_key_exists($key, $context)) {
                throw new \Exception('Resource attribe can\'t be formated, excepted parameter " ' . $key . '" is missing.');
            }
        }

        $attributes['billing']['landline_phone_number'] = $attributes['billing']['landline_phone_number'] ?: $attributes['shipping']['landline_phone_number'];
        $attributes['billing']['mobile_phone_number'] = $attributes['billing']['mobile_phone_number'] ?: $attributes['shipping']['mobile_phone_number'];
        $attributes['allow_save_card'] = false;

        $attributes['hosted_payment']['sent_by'] = 'EMAIL';
        unset($attributes['hosted_payment']['cancel_url'], $attributes['hosted_payment']['return_url']);

        return $attributes;
    }
}
