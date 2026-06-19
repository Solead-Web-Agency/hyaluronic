<?php

declare(strict_types=1);

namespace PayplugPluginCore\Utilities\Services;

use http\Exception\RuntimeException;
use Payplug\Payment;
use Payplug\Payplug;

class Api
{
    /**
     * @var \Payplug\Payplug|null
     */
    private $payplug_api;

    /**
     * @var string
     */
    private $bearer_token;

    /**
     * @param array<string, mixed> $datas
     * @return array<string, mixed>
     */
    public function createPaymentResource(array $datas): array
    {
        try {
            if (null === $this->payplug_api) {
                throw new RuntimeException('API Payplug must be initialized.');
            }
            $response = [
                'code' => 200,
                'message' => 'OK',
                'resource' => Payment::create($datas, $this->payplug_api),
                'result' => true,
            ];
        } catch (\Exception $e) {
            $response = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'resource' => null,
                'result' => false,
            ];
        }

        return $response;
    }

    public function getBearerToken(): string
    {
        return $this->bearer_token;
    }

    private function setBearerToken(string $bearer_token): void
    {
        $this->bearer_token = $bearer_token;
    }

    /**
     * @return $this
     * @throws \Payplug\Exception\ConfigurationException
     */
    public function load(string $bearer_token): self
    {
        $this->setBearerToken($bearer_token);
        $this->initialize();

        return $this;
    }

    /**
     * @throws \Exception
     */
    private function initialize(): void
    {
        try {
            $this->payplug_api = Payplug::init([
                'secretKey'  => $this->getBearerToken(),
                'apiVersion' => '2019-08-06',
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Payplug API can\'t be initialized. Error thrown: ' . $e->getMessage());
        }
    }
}
