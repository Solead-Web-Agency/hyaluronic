<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Mock;

use Payplug\Resource\Payment;

class PaymentMock
{
    /** @var array<string, array<string, mixed>> */
    public static $payment_parameters = [
        'oneclick' => [
            'is_paid' => true,
            'paid_at' => 1614949567,
            'is_3ds' => false,
            'is_live' => true,
            'card' => [
                'last4' => '0001',
                'country' => 'FR',
                'exp_year' => 2030,
                'exp_month' => 9,
                'brand' => 'CB',
                'id' => 'card_3EOJHyQXNCG8gZ452cUA0y',
                'metadata' => null,
            ],
            'hosted_payment' => [
                'paid_at' => 1614949567,
            ],
            'refundable_after' => 1614949567,
            'refundable_until' => 1630501567,
            'metadata' => [
                'ID Client' => 4,
                'ID Cart' => 17,
                'Website' => 'http://localhost',
            ],
        ],
    ];

    /**
     * @param array<string, mixed> $parameters
     */
    public static function getStandard(array $parameters = []): Payment
    {
        $resource = self::get($parameters);

        return Payment::fromAttributes($resource);
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public static function get(array $parameters): array
    {
        $defaultConfiguration = [
            'id' => 'pay_azerty',
            'object' => 'payment',
            'is_live' => true,
            'amount' => 42420,
            'amount_refunded' => 0,
            'currency' => 'EUR',
            'created_at' => time(),
            'description' => null,
            'is_paid' => false,
            'paid_at' => null,
            'is_refunded' => false,
            'is_3ds' => null,
            'save_card' => false,
            'card' => [
                'last4' => null,
                'country' => null,
                'exp_year' => null,
                'exp_month' => null,
                'brand' => null,
                'id' => null,
                'metadata' => null,
            ],
            'hosted_payment' => [
                'payment_url' => 'payment_url',
                'return_url' => 'return_url',
                'cancel_url' => 'cancel_url',
                'paid_at' => null,
                'sent_by' => null,
            ],
            'notification' => [
                'url' => 'notification_url',
                'response_code' => null,
            ],
            'metadata' => [
                'ID Client' => 1,
                'ID Cart' => 1,
                'Website' => 'http://my.localhost.com',
            ],
            'failure' => null,
            'installment_plan_id' => null,
            'authorization' => null,
            'refundable_after' => null,
            'refundable_until' => null,
            'billing' => [
                'title' => null,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address1' => 'my address mock',
                'address2' => null,
                'company_name' => 'Mock Inc.',
                'postcode' => '75000',
                'city' => 'Paris',
                'state' => null,
                'country' => 'FR',
                'email' => 'jdoe@payplug.com',
                'mobile_phone_number' => '+33612345678',
                'landline_phone_number' => '+33112345678',
                'language' => 'fr',
            ],
            'shipping' => [
                'title' => null,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address1' => 'my address mock',
                'address2' => null,
                'company_name' => 'Mock Inc.',
                'postcode' => '75000',
                'city' => 'Paris',
                'state' => null,
                'country' => 'FR',
                'email' => 'jdoe@payplug.com',
                'mobile_phone_number' => '+33612345678',
                'landline_phone_number' => '+33112345678',
                'language' => 'fr',
                'delivery_type' => 'BILLING',
            ],
        ];

        if (!empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $defaultConfiguration[$key] = $value;
            }
        }

        return $defaultConfiguration;
    }
}
