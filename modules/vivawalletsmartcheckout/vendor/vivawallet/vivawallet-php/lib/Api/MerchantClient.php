<?php

namespace Vivawallet\VivawalletPhp\Api;

use Vivawallet\VivawalletPhp\Application;
use Vivawallet\VivawalletPhp\Http\Authentication\Authentication;
use Vivawallet\VivawalletPhp\Http\Client;
use Vivawallet\VivawalletPhp\Http\Response;

class MerchantClient
{
    private $httpClient;
    private $authentication;

    public function __construct(Authentication $authentication, $config = [])
    {
        $this->authentication = $authentication;
        $this->httpClient     = new Client($config);
    }

    public function getInfo(): Response
    {
        return $this->httpClient->request(
            'get',
            Application::BASE_URLS[$this->authentication->getEnvironment()]['api']
            . Application::ENDPOINTS['merchants'],
            [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => $this->authentication->getHeader()
                ]
            ]
        );
    }
}
