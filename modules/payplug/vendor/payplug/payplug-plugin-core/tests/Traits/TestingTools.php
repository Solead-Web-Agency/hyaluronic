<?php

declare(strict_types=1);

namespace PayplugPluginCore\Tests\Traits;

trait TestingTools
{
    use FormatDataProvider;

    /**
     * @var object|null
     */
    private $payment_input_dto_mock;
    /**
     * @var object|null
     */
    private $payment_output_dto_mock;
    /**
     * @var object|null
     */
    private $payment_mock;

    public function get_mock(string $name = ''): object
    {
        if (empty($name)) {
            throw new \Exception('Invalid parameter, $name given should be a non empty string.');
        }

        if (!empty($this->{$name})) {
            return $this->{$name};
        }

        $mock = '\\PayplugPluginCore\\Tests\\Mock\\' . str_replace('_', '', ucwords($name, '_')) . 'Mock';
        if (!class_exists($mock)) {
            throw new \Exception('Mock can\'t be found. Given: ' . $mock);
        }

        $this->{$name} = new $mock();

        return $this->{$name};
    }
}
