<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

class RevolutApi
{
    public $mode;
    public $sandboxEnable = false;
    public $api_base_url;
    private $api_url;
    public $api_key;
    private $api_key_live;
    private $api_key_sandbox;
    private $module_version;
    public $api_urls = array(
        'live' => 'https://merchant.revolut.com',
        'dev' => 'https://merchant.revolut.codes',
        'sandbox' => 'https://sandbox-merchant.revolut.com',
    );
    public $mgmt_api_urls = array(
        'live' => 'https://merchant-mgmt.revolut.com',
        'dev' => 'https://merchant-mgmt.revolut.codes',
        'sandbox' => 'https://sandbox-merchant-mgmt.revolut.com',
    );

    public function __construct($mode, $api_key_live, $api_key_sandbox, $module_version)
    {
        $this->mode = $mode;
        $this->api_key_live = $api_key_live;
        $this->api_key_sandbox = $api_key_sandbox;
        $this->module_version = $module_version;

        if ($this->mode == 'live') {
            $this->api_key = $this->api_key_live;
//            only for dev
//            $this->mode = 'dev';
        } elseif ($this->mode == 'sandbox') {
            $this->api_key = $this->api_key_sandbox;
            $this->sandboxEnable = true;
        }

        $this->api_base_url = $this->api_urls[$this->mode];
        $this->mgmt_api_base_url = $this->mgmt_api_urls[$this->mode] . '/api';
    }

    /**
     * Update Revolut order
     *
     * @param
     * @return string
     */
    public function updateRevolutOrder($revolut_order_id, $params)
    {
        $path = '/orders/' . $revolut_order_id;
        $response = $this->apiRequestPatch($path, $params);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error: Can not update Revolut order ' . $revolut_order_id . ' or it already be updated. ' . Tools::jsonEncode($response),
                3
            );

            return '';
        }

        return $response['public_id'];
    }

    /**
     * @param $revolut_order_id
     *
     * @return mixed|string
     */
    public function cancelRevolutOrder($revolut_order_id)
    {
        $path = '/orders/' . $revolut_order_id . '/cancel';
        $params = null;

        $response = $this->apiRequest($path, $params);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error: Can not cancel Revolut order ' . $revolut_order_id . ' or it is already be canceled.',
                3
            );
        }

        return $response;
    }

    /**
     * Capture Revolut order
     *
     * @param
     * @return boolean
     */
    public function captureRevolutOrder($revolut_order_id)
    {
        // check if order already captured
        $order = $this->apiRequestGet('/orders/' . $revolut_order_id);
        if (isset($order['state']) && $order['state'] == 'COMPLETED') {
            return true;
        }

        $path = '/orders/' . $revolut_order_id . '/capture';
        $params = null;

        $response = $this->apiRequest($path, $params);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error: Can not capture Revolut order ' . $revolut_order_id . ' or it already be captured.',
                3
            );

            return false;
        }

        return true;
    }

    /**
     * Retrieve Revolut order
     *
     * @param
     * @return array || boolean
     */
    public function retrieveRevolutOrder($revolut_order_id)
    {
        $path = '/orders/' . $revolut_order_id;
        $params = null;

        $response = $this->apiRequest($path, $params, false);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog('Error: Can not retrieve Revolut order ' . $revolut_order_id, 3);

            return array();
        }

        return $response;
    }

    public function refundRevolutOrder($revolut_order_id, $params)
    {
        $path = '/orders/' . $revolut_order_id . '/refund';
        $response = $this->apiRequest($path, $params);

        if (!isset($response['public_id']) || !isset($response['id'])) {
            // log bug
            PrestaShopLogger::addLog('Error: Can not refund Revolut order ' . $revolut_order_id, 3);
        }

        return $response;
    }

    /**
     * Get Revolut webhook URLs
     *
     * @param
     * @return array
     */
    public function getRevolutWebhookUrls()
    {
        $urls = array();

        $path = '/webhooks';

        $response = $this->apiRequestGet($path);

        if (is_array($response) && count($response)) {
            foreach ($response as $response_item) {
                if (isset($response_item['url']) && $response_item['url'] != '' && isset($response_item['events'])
                    && count($response_item['events']) == 2) {
                    $urls[] = $response_item['url'];
                }
            }
        }

        return $urls;
    }

    /**
     * Set Revolut webhook URL
     *
     * @param
     * @return string
     */
    public function setRevolutWebhookUrl($url)
    {
        $path = '/webhooks';

        // check webhook URL existed
        if ($this->checkIfUrlExist($url)) {
            return true;
        }

        $params = array(
            'url' => $url,
            'events' => ["ORDER_COMPLETED", "ORDER_AUTHORISED"]
        );

        $response = $this->apiRequest($path, $params);

        if (empty($response['url'])) {
            // log bug
            PrestaShopLogger::addLog(
                'Error: Can not set Revolut webhook URL ' . $params['url'] . ' with Revolut Merchant API.',
                3
            );
            return false;
        }

        return true;
    }

    /**
     * Checks if URL already set for webhook
     *
     * @param String
     * @return boolean
     */
    public function checkIfUrlExist($url)
    {
        $webhook_urls = $this->getRevolutWebhookUrls();
        return in_array($url, $webhook_urls);
    }

    /**
     * apiRequest
     *
     * @param array
     * @return array
     */
    public function apiRequest($path, $params, $use_post = true, $new_api_version = false)
    {
        $response = array();

        try {
            $this->api_url = $new_api_version ? $this->api_base_url . '/api' : $this->api_base_url . '/api/1.0';
            $url = $this->api_url . $path;

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json; charset=utf-8',
                'User-Agent: Revolut Payment Gateway/' . $this->module_version . ' PrestaShop/' . _PS_VERSION_
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if ($use_post) {
                curl_setopt($ch, CURLOPT_POST, $use_post);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }

            $result = curl_exec($ch);
            $response = json_decode($result, true);
            curl_close($ch);
        } catch (Exception $e) {
            //echo $e->getMessage();
            // log bug
            PrestaShopLogger::addLog('Error: ' . $e->getMessage(), 3);
        }

        return $response;
    }

    /**
     * apiRequestPatch
     *
     * @param array
     * @return array
     */
    public function apiRequestPatch($path, $params, $new_api_version = false)
    {
        $response = array();

        try {
            $this->api_url = $new_api_version ? $this->api_base_url . '/api' : $this->api_base_url . '/api/1.0';
            $url = $this->api_url . $path;

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json; charset=utf-8',
                'User-Agent: Revolut Payment/' . $this->module_version . ' PrestaShop/' . _PS_VERSION_
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

            $result = curl_exec($ch);
            $response = json_decode($result, true);

            curl_close($ch);
        } catch (Exception $e) {
            //echo $e->getMessage();
            // log bug
            PrestaShopLogger::addLog('Error: ' . $e->getMessage(), 3);
        }

        return $response;
    }

    /**
     * apiRequestGet
     *
     * @param array
     * @return array
     */
    public function apiRequestGet($path, $new_api_version = false)
    {
        $response = array();

        try {
            $this->api_url = $new_api_version ? $this->api_base_url . '/api' : $this->api_base_url . '/api/1.0';
            $url = $this->api_url . $path;

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json; charset=utf-8',
                'User-Agent: Revolut Payment/' . $this->module_version . ' PrestaShop/' . _PS_VERSION_
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $response = json_decode($result, true);

            curl_close($ch);
        } catch (Exception $e) {
            //echo $e->getMessage();
            // log bug
            PrestaShopLogger::addLog('Error: ' . $e->getMessage(), 3);
        }

        return $response;
    }

    /**
     * apiRequestGet
     *
     * @param array
     * @return array
     */
    public function mgmtApiRequestGet($path, $public_key_request = false)
    {
        $response = array();

        $merchant_public_key = Configuration::get("REVOLUT_MERCHANT_PUBLIC_TOKEN_{$this->mode}");

        if ($public_key_request) {
            $merchant_public_key = $this->api_key;
        }

        if (empty($merchant_public_key)) {
            return array();
        }

        try {
            $url = $this->mgmt_api_base_url . $path;
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $merchant_public_key,
                'Content-Type: application/json; charset=utf-8',
                'User-Agent: Revolut Payment/' . $this->module_version . ' PrestaShop/' . _PS_VERSION_
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            $response = json_decode($result, true);
            curl_close($ch);
        } catch (Exception $e) {
            //echo $e->getMessage();
            // log bug
            PrestaShopLogger::addLog('Error: ' . $e->getMessage(), 3);
        }

        return $response;
    }
}
