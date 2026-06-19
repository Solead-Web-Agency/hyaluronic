<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Units\Gateways\Payment\StandardPaymentGateway;

use PayplugPluginCore\Tests\Mock\PaymentInputDTOMock;

/**
 * @group unit
 * @group gateways
 * @group payment
 * @group standard_payment_gateway
 */
class formatPaymentAttributesTest extends standardPaymentGatewayBase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws \ReflectionException
     */
    public function testWhenGivenDTOIsInvalid(): void
    {
        $this->expectException(\TypeError::class);
        (new \ReflectionMethod($this->gateway, 'formatPaymentAttributes'))->invoke($this->gateway, null);
    }

    public function testWhenDefaultAttributesCanBeSet(): void
    {
        $exception_msg = 'Can\'t generate default payment attributes';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($exception_msg);
        $this->gateway
            ->shouldReceive('getDefaultAttributeFromDTO')
            ->once()
            ->andReturn([]);
        $this->gateway->formatPaymentAttributes(PaymentInputDTOMock::get([]));
    }

    public static function StandardPaymentRequiredContext(): \Generator
    {
        yield ['is_deferred'];
        yield ['is_integrated'];
        yield ['is_guest'];
    }
    /**
     * @dataProvider StandardPaymentRequiredContext
     *
     * @param mixed $key
     */
    public function testWhenRequiredContextIsMissing($key): void
    {
        $exception_msg = 'Resource attribe can\'t be formated, excepted parameter " ' . $key . '" is missing.';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($exception_msg);
        $this->gateway
            ->shouldReceive('getDefaultAttributeFromDTO')
            ->once()
            ->andReturn([
                'amount' => 4242,
                'currency' => 'EUR',
            ]);

        $context = [
            'is_deferred' => false,
            'is_integrated' => false,
            'is_guest' => false,
        ];
        unset($context[$key]);
        $this->gateway->formatPaymentAttributes(PaymentInputDTOMock::get([
            'context' => $context,
        ]));
    }

    /**
     * @dataProvider StandardPaymentRequiredContext
     *
     * @param mixed $key
     */
    public function testWhenPaymentAttributesIsReturned($key): void
    {
        $this->gateway
            ->shouldReceive('getDefaultAttributeFromDTO')
            ->once()
            ->andReturn([
                'amount' => 4242,
                'currency' => 'EUR',
                'hosted_payment' => [
                    'cancel_url' => 'cancel_url',
                ],
                'allow_save_card' => true,
            ]);

        $context = [
            'is_deferred' => false,
            'is_integrated' => false,
            'is_guest' => false,
        ];
        $context[$key] = true;
        $expected = [];
        switch ($key) {
            case 'is_deferred':
                $expected = [
                    'authorized_amount' => 4242,
                    'currency' => 'EUR',
                    'hosted_payment' => [
                        'cancel_url' => 'cancel_url',
                    ],
                    'allow_save_card' => true,
                ];
                break;
            case 'is_integrated':
                $expected = [
                    'amount' => 4242,
                    'currency' => 'EUR',
                    'hosted_payment' => [],
                    'integration' => 'INTEGRATED_PAYMENT',
                    'allow_save_card' => true,
                ];
                break;
            case 'is_guest':
                $expected = [
                    'amount' => 4242,
                    'currency' => 'EUR',
                    'hosted_payment' => [
                        'cancel_url' => 'cancel_url',
                    ],
                    'allow_save_card' => false,
                ];
                break;
        }
        $this->assertEquals(
            $expected,
            $this->gateway->formatPaymentAttributes(PaymentInputDTOMock::get([
                'context' => $context,
            ]))
        );
    }
}
