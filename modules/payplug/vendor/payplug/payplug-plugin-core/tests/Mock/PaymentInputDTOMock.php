<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Mock;

use PayplugPluginCore\Models\Entities\PaymentInputDTO;
use PayplugPluginCore\Tests\Traits\TestingTools;

class PaymentInputDTOMock
{
    use TestingTools;

    /**
     * @param array<string, mixed>|null $custom_props
     * @throws \Exception
     */
    public static function get(?array $custom_props): PaymentInputDTO
    {
        // get default
        $props = [
            'api_bearer' => 'token_bearer',
            'payment_method' => 'payment method',
            'amount' => 4242,
            'currency_iso_code' => 'EUR',
            'customer' => [
                'identifier' => 42,
                'billing' => [
                    'title' => 'mr',
                    'first_name' => 'John',
                    'last_name' => 'Watson',
                    'mobile_phone_number' => '+33612345678',
                    'email' => 'john.watson@example.net',
                    'address1' => '221B Baker Street',
                    'postcode' => 'NW16XE',
                    'city' => 'London',
                    'country' => 'GB',
                    'language' => 'en',
                ],
                'shipping' => [
                    'title' => 'mr',
                    'first_name' => 'John',
                    'last_name' => 'Watson',
                    'mobile_phone_number' => '+33612345678',
                    'email' => 'john.watson@example.net',
                    'address1' => '221B Baker Street',
                    'postcode' => 'NW16XE',
                    'city' => 'London',
                    'country' => 'GB',
                    'language' => 'en',
                ],
            ],
            'urls' => [
                'return' => 'https://example.net/success?id=42',
                'cancel' => 'https://example.net/cancel?id=42',
                'notification' => 'https://example.net/notifications?id=42',
            ],
            'metadata' => [
                'cart ID' => '42',
                'custom data' => 'lorem ipsum',
            ],
            'context' => [],
        ];

        // set custom props
        if (!empty($custom_props)) {
            foreach ($custom_props as $prop => $value) {
                $props[$prop] = $value;
            }
        }

        //
        $paymentInputDTO = new PaymentInputDTO();
        $result = $paymentInputDTO->hydrate($props);
        if ($result === null) {
            throw new \RuntimeException('PaymentInputDTOMock failed to hydrate DTO.');
        }

        return $result;
    }
}
