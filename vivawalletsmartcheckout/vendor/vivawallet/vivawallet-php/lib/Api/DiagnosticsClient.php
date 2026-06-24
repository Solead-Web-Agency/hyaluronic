<?php

namespace Vivawallet\VivawalletPhp\Api;

use Vivawallet\VivawalletPhp\Application;
use Vivawallet\VivawalletPhp\Http\Authentication\Authentication;
use Vivawallet\VivawalletPhp\Http\Client;
use Vivawallet\VivawalletPhp\Http\Response;

class DiagnosticsClient
{
    private $httpClient;
    private $authentication;

    public function __construct(Authentication $authentication, $config = [])
    {
        $this->authentication = $authentication;
        $this->httpClient     = new Client($config);
    }

    public function sendLogs(array $logs): Response
    {
        return $this->httpClient->request(
            'post',
            Application::BASE_URLS[$this->authentication->getEnvironment()]['api']
            . Application::ENDPOINTS['diagnostics'],
            [
                'json'    => $logs,
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => $this->authentication->getHeader()
                ]
            ]
        );
    }
}
