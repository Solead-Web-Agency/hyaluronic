<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Mock;

use Payplug\Resource\Payment;
use PayplugPluginCore\Models\Entities\PaymentOutputDTO;

class PaymentOutputDTOMock
{
    /**
     * @var bool
     */
    public $result;
    /**
     * @var string
     */
    public $code;
    /**
     * @var string
     */
    public $message;
    /**
     * @var object
     */
    public $resource;

    /**
     * @param array<string, mixed>|null $custom_props
     * @throws \Exception
     */
    public static function get(?array $custom_props): PaymentOutputDTO
    {
        // get default
        $payment_attributes = [
            'id' => 'pay_id',
            'object' => 'payment',
            'is_live' => true,
            'amount' => 4200,
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
                'return_url' => 'https://example.net/success?id=42',
                'cancel_url' => 'https://example.net/cancel?id=42',
                'paid_at' => null,
                'sent_by' => null,
            ],
            'notification' => [
                'url' => 'https://example.net/notifications?id=42',
                'response_code' => null,
            ],
            'metadata' => [
                'ID Client' => 1,
                'ID Cart' => 1,
                'Website' => 'website.com',
            ],
            'failure' => null,
            'installment_plan_id' => null,
            'authorization' => null,
            'refundable_after' => null,
            'refundable_until' => null,
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
                'delivery_type' => 'BILLING',
            ],
        ];
        $props = [
            'result' => true,
            'code' => 200,
            'message' => 'OK',
            'resource' => Payment::fromAttributes($payment_attributes),
        ];

        // set custom props
        if ($custom_props !== null) {
            foreach ($custom_props as $prop => $value) {
                $props[$prop] = $value;
            }
        }

        //
        $paymentOutputDTO = new PaymentOutputDTO();
        $result = $paymentOutputDTO->hydrate($props);
        if ($result === null) {
            throw new \RuntimeException('PaymentOutputDTOMock failed to hydrate DTO.');
        }

        return $result;
    }
}
