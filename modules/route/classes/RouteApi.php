<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

require_once 'RouteSentry.php';

class RouteApi
{
    /**
     * Route API endpoint (Production)
     *
     * @var string
     */
    const ENDPOINT_PROD = 'https://api.route.com/v1';

    /**
     * Route API endpoint (Stage)
     *
     * @var string
     */
    const ENDPOINT_STAGE = 'https://api-stage.route.com/v1';

    /**
     * Private Token
     *
     * @var string
     */
    private $routeToken;

    /**
     * Current Environment
     *
     * @var string stage | prod
     */
    private $env;

    /**
     * Class Constructor
     *
     * @param string $token API KEY
     * @param string $env Environment
     */
    public function __construct($token = '', $env = 'production')
    {
        $this->routeToken = $token;
        $this->env = $env;
    }

    /**
     * Shortcut for GET request
     *
     * @param string $path
     * @param array $params
     *
     * @return mixed|json
     */
    public function get($path, $params = null)
    {
        return $this->apiRequest('GET', $path, $params);
    }

    /**
     * Shortcut for POST request
     *
     * @param string $path
     * @param array $params
     *
     * @return mixed|json
     */
    public function post($path, $params = null)
    {
        return $this->apiRequest('POST', $path, $params);
    }

    /**
     * Create a CURL Request to Route API endpoint
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $path URL path
     * @param array $params Object of params to be requested or sent
     */
    private function apiRequest($method, $path, $params = null)
    {
        $curl = curl_init();

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if (!empty($this->routeToken)) {
            array_push($headers, 'Token: ' . $this->routeToken);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);

            if (isset($params)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
            }
        } elseif ($method === 'GET' && isset($params)) {
            $path = $path . '?' . http_build_query($params);
        }

        curl_setopt($curl, CURLOPT_URL, $this->getEndpoint() . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($method === 'POST') {
            $validResponses = [200, 201];
        } elseif ($method === 'GET') {
            $validResponses = [200, 201, 404];
        }

        if (!in_array($status_code, $validResponses)) {
            RouteSentry::track(
                'error',
                'RouteAPI ' . $method . ' request error',
                debug_backtrace(),
                [
                    'path' => $path,
                    'params' => $params,
                    'response' => $response,
                    'env' => $this->env,
                ]
            );
        }

        return [
            'body' => json_decode($response, true),
            'status_code' => $status_code,
        ];
    }

    /**
     * Return the correct endpoint for the selected environment
     *
     * @return string
     */
    private function getEndpoint()
    {
        if ($this->env === 'stage') {
            return self::ENDPOINT_STAGE;
        } else {
            return self::ENDPOINT_PROD;
        }
    }
}
