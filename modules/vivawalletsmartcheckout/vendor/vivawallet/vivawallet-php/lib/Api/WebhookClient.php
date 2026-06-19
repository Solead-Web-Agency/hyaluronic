<?php

namespace Vivawallet\VivawalletPhp\Api;

use Vivawallet\VivawalletPhp\Application;
use Vivawallet\VivawalletPhp\Http\Authentication\Authentication;
use Vivawallet\VivawalletPhp\Http\Client;
use Vivawallet\VivawalletPhp\Http\Response;

class WebhookClient
{
    private $httpClient;
    private $authentication;

    public function __construct(Authentication $authentication, $config = [])
    {
        $this->authentication = $authentication;
        $this->httpClient     = new Client($config);
    }

    public function createWebhook(string $url): Response
    {
        return $this->httpClient->request(
            'post',
            Application::BASE_URLS[$this->authentication->getEnvironment()]['api']
            . Application::ENDPOINTS['webhook'],
            [
                'json'    => [
                    'url' => $url
                ],
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => $this->authentication->getHeader()
                ]
            ]
        );
    }

    public function getVerificationToken(): Response
    {
        return $this->httpClient->request(
            'get',
            Application::BASE_URLS[$this->authentication->getEnvironment()]['api']
            . Application::ENDPOINTS['webhookToken'],
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'Authorization' => $this->authentication->getHeader()
                ]
            ]
        );
    }
}
