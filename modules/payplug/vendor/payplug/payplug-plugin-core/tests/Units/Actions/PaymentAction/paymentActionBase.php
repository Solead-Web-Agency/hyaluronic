<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Units\Actions\PaymentAction;

use Mockery;
use PayplugPluginCore\Actions\PaymentAction;
use PHPUnit\Framework\TestCase;

abstract class paymentActionBase extends TestCase
{
    /**
     * @var (\Mockery\MockInterface & \PayplugPluginCore\Actions\PaymentAction)
     */
    protected $action;

    public function setUp(): void
    {
        $this->action = Mockery::mock(PaymentAction::class, [])->makePartial();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
