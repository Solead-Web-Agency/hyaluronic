<?php

declare(strict_types=1);

namespace PayplugPluginCore\Utilities\Traits;

use PayplugPluginCore\Gateways\PaymentGatewayManager;

trait DependenciesLoader
{
    /** @var array<int, string> */
    private $allowed_services = [];

    /** @var array<int, string> */
    private $allowed_gateways = [];

    /**
     * @return object
     * @throws \Exception
     */
    public function get_service(string $name = ''): object
    {
        if (empty($name)) {
            throw new \Exception('Invalid parameter, $name given should be a non empty string.');
        }

        if (!\in_array($name, $this->allowed_services)) {
            throw new \Exception('Given $name is not allower Services.');
        }

        $service_name = '\PayplugPluginCore\Utilities\Services\\' . str_replace('_', '', ucwords($name, '_'));
        if (!class_exists($service_name)) {
            throw new \Exception('Service can\'t be found.');
        }

        return new $service_name();
    }

    /**
     * @param string $name
     * @return object
     * @throws \Exception
     */
    public function get_gateway(string $name = ''): object
    {
        if (empty($name)) {
            throw new \Exception('Invalid parameter, $name given should be a non empty string.');
        }

        if (!\in_array($name, $this->allowed_gateways, true)) {
            throw new \Exception('Given $name is not allower Gateways.');
        }

        $gateway_name = '\PayplugPluginCore\Gateways\\' . str_replace('_', '', ucwords($name, '_')) . 'Gateway';
        if (!class_exists($gateway_name)) {
            throw new \Exception('Gateway can\'t be found.');
        }

        return new $gateway_name();
    }

    public function get_payment_gateway(): PaymentGatewayManager
    {
        return new PaymentGatewayManager();
    }
}
