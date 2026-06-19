<?php

/**
 * Google Merchant Center
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class GsaClient
{
    const ENDPOINT = 'todo';
    const API_URL = _GMC_API_URL;

    /**
     * Retrieve CURL Handle
     * @param string $apiKey
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $addHeaders
     * @return resource
     */
    private static function getCurlHandle($apiKey, $url, $method = 'GET', $params = array(), $addHeaders = array())
    {

        // initialize curl library
        $curlHandle = curl_init();
        
        $headers = array(
            'Content-type' => 'application/json',
            'X-API-Key' => $apiKey,
            "X-Platform" => "Prestashop",
            "X-Module" => 2,
            "X-Shop-Url" => rtrim((string) BT_GmcModuleTools::getBaseLink(), '/'),            
            "X-Platform-Version" => _PS_VERSION_, 
            "X-Shop-Name" => Configuration::get('PS_SHOP_NAME'),
        );

        // Combine headers, prevent override of original headers
        $headers = ($headers + $addHeaders);
        $curlHeaders = array();
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_ENCODING, '');
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 15);
        curl_setopt($curlHandle, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curlHandle, CURLOPT_FAILONERROR, false);

        if ($method == 'GET') {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curlHandle, CURLOPT_HTTPGET, true);
            curl_setopt($curlHandle, CURLOPT_POST, false);
        } elseif ($method == 'POST') {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curlHandle, CURLOPT_POST, true);
            // Content type is set to JSON so we can encode our form data this way
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($params));
        }

        return $curlHandle;
    }

    /**
     * Retrieve credentials provided by API
     * @param string $apiKey
     * @since 1.0.0
     * @return object
     */
    public static function getCredentials($apiKey)
    {
        $addHeaders = array(
            'X-IS-Shop-Name' => Configuration::get('PS_SHOP_NAME'),
        );
        $response = self::getResponse($apiKey, '/connect', 'GET', array(), $addHeaders);
        if (!empty($response) && isset($response->result)) {
            return $response;
        }

        return null;
    }

    /**
     * Retrieve response regarding a query
     * @param string $apiKey
     * @param string $path
     * @param array $params
     * @param array $addHeaders
     * @throws Exception
     * @return string
     */
    public static function getResponse($apiKey, $path, $method = 'GET', $params = array(), $addHeaders = array())
    {
        // Create URL
        $url =  self::API_URL . $path;

        if (sizeof($params) && $method == 'GET') {
            $url .= '?' . http_build_query($params);
        }

        // Retrieve CURL handle
        $curlHandle = self::getCurlHandle($apiKey, $url, $method, $params, $addHeaders);

        $errorNb = $errorMessage = null;

        $response = curl_exec($curlHandle);

        $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $errorNb = curl_errno($curlHandle);
            $errorMessage = curl_error($curlHandle);
            curl_close($curlHandle);
        } else {
            $response = json_decode($response);
        }

        if ($httpCode == 200) {
            return $response;
        } elseif ($httpCode == 429) {
            throw new Exception('Error Processing Request - Too many requests', 2);
        } elseif ($httpCode == 403) {
            return $httpCode;
        } elseif ($httpCode == 419) {
            throw new Exception('Error Processing CSRF Token Request - Server error', 4);
        } elseif ($httpCode == 500) {
            throw new Exception('Error Processing Request - Server error', 5);
        } elseif (empty($httpCode)) {
            throw new Exception('Error Processing Request - Unknown', 6);
        }

        throw new Exception("Error Processing Request - HTTP Code $httpCode", 1);
    }


    /**
     * Api authentification
     * @param string $apiKey
     * @throws Exception
     * @return string
     */
    public static function authApi($sApiKey)
    {
        $response = self::getResponse($sApiKey, '/auth', 'GET', array());

        if (!empty($response) && isset($response->result)) {
            return $response;
        }

        return null;
    }

    /**
     * Update configuration
     *
     * @param string $sApiKey
     * @param array $configuration
     * @return object
     */
    public static function updateModuleConfigurationForGsa($sApiKey, $aConfiguration)
    {
        if (empty($sApiKey)) {
            return false;
        }

        $response = self::getResponse($sApiKey, '/gsa/configuration', 'POST', array('configuration' => $aConfiguration));
        if (!empty($response->result)) {
            return true;
        }

        return false;
    }

    /**
     * get configuration
     *
     * @param string $sApiKey
     * @return object
     */
    public static function getModuleConfigurationForGsa($sApiKey)
    {
        if (empty($sApiKey)) {
            return false;
        }

        $response = self::getResponse($sApiKey, '/gsa/configuration', 'GET');
        if (!empty($response->result)) {
            return $response;
        }

        return false;
    }

    /**
     * get the shop id for the shop
     *
     * @param string $sApiKey
     * @return object
     */
    public static function getShopId($sApiKey)
    {
        if (empty($sApiKey)) {
            return false;
        }

        $response = self::getResponse($sApiKey, '/gsa/shop', 'GET');
        if (!empty($response)) {
            return $response;
        }

        return false;
    }

    /**
     * Disable shop
     * @param string $apiKey
     * @throws Exception
     * @return string
     */
    public static function disableShop($sApiKey)
    {
        if (empty($sApiKey)) {
            return false;
        }

        $response = self::getResponse($sApiKey, '/gsa/disableShop', 'POST', array());

        if (!empty($response->result)) {
            return true;
        }

        return false;
    }

     /**
     * Api authentification
     * @param string $apiKey
     * @throws Exception
     * @return string
     */
    public static function simpleAuth($sApiKey)
    {
        $response = self::getResponse($sApiKey, '/simpleAuth', 'GET', array());

        if (!empty($response) && isset($response->result)) {
            return $response;
        }

        return null;
    }

     /**
     * Enable shop
     * @param string $apiKey
     * @throws Exception
     * @return string
     */
    public static function enableShop($sApiKey)
    {
        if (empty($sApiKey)) {
            return false;
        }

        $response = self::getResponse($sApiKey, '/gsa/enableShop', 'POST', array());
        
        if (!empty($response->result)) {
            return true;
        }

        return false;
    }

}
