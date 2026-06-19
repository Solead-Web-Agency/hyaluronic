<?php

namespace Vivawallet\VivawalletPhp\Api;

use Vivawallet\VivawalletPhp\Application;
use Vivawallet\VivawalletPhp\Http\Authentication\BasicAuthentication;
use Vivawallet\VivawalletPhp\Http\Client;
use Vivawallet\VivawalletPhp\Http\Response;

class AuthenticationClient
{
    private $httpClient;

    public function __construct($config = [])
    {
        $this->httpClient = new Client($config);
    }

    public function getBearerToken(BasicAuthentication $basicAuthentication, $grantType, $scope): Response
    {
        return $this->httpClient->request(
            'post',
            Application::BASE_URLS[$basicAuthentication->getEnvironment()]['accounts']
            . Application::ENDPOINTS['accountsToken'],
            [
                'form_params' => [
                    'grant_type' => $grantType,
                    'scope'      => $scope
                ],
                'headers'     => [
                    'Content-type'  => 'application/x-www-form-urlencoded',
                    'Authorization' => $basicAuthentication->getHeader()
                ]
            ]
        );
    }
}
