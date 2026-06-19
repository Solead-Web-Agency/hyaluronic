<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Traits;

trait FormatDataProvider
{
    public function invalidArrayFormatDataProvider(): \Generator
    {
        yield [42];

        yield [null];

        yield [false];

        yield ['lorem ipsum'];
    }

    public function invalidBoolFormatDataProvider(): \Generator
    {
        yield ['lorem Ipsum'];

        yield [42];

        yield [['key' => 'value']];

        yield [null];
    }

    public function invalidIntegerFormatDataProvider(): \Generator
    {
        yield [null];

        yield [['key' => 'value']];

        yield [true];

        yield ['lorem ipsum'];
    }

    public function invalidFloatFormatDataProvider(): \Generator
    {
        yield [null];

        yield [['key' => 'value']];

        yield [true];

        yield ['lorem ipsum'];

        yield [42];
    }

    public function invalidNumericFormatDataProvider(): \Generator
    {
        yield [null];

        yield [['key' => 'value']];

        yield [true];

        yield ['lorem ipsum'];

        yield ['123abc'];
    }

    public function invalidJSONFormatDataProvider(): \Generator
    {
        yield [''];

        yield ['{"feature": \'value\'}'];

        yield ['{"feature": "value", }'];

        yield ['{{}}'];
    }

    public function invalidObjectFormatDataProvider(): \Generator
    {
        yield [42];

        yield [['key' => 'value']];

        yield [true];

        yield ['lorem ipsum'];
    }

    public function invalidStringFormatDataProvider(): \Generator
    {
        yield [42];

        yield [['key' => 'value']];

        yield [false];

        yield [null];
    }

    public function invalidEmailFormatDataProvider(): \Generator
    {
        yield ['@test.com'];

        yield ['email@test'];

        yield ['emailtest.com'];
    }

    public function invalidPhoneFormatDataProvider(): \Generator
    {
        yield [42];

        yield ['invalid phone number'];

        yield [false];

        yield [null];
    }
}
