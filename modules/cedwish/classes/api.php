<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class CedWishApi
{
    const WISH_API_LIVE_MODE = 1;
    const WISH_API_SANDBOX_MODE = 2;
    const WISH_API_LIVE_ENDPOINT = 'https://merchant.wish.com/api/';
    const WISH_API_LIVE_CLIENT_ID = '5b3c7b2e19c76b2c8f1c4b0f';
    const WISH_API_LIVE_CLIENT_SECRET = '019f1c3a906d48ca941e2392255f5620';
    const WISH_API_SANDBOX_ENDPOINT = 'https://sandbox.merchant.wish.com/api/';
    const WISH_API_SANDBOX_CLIENT_ID = '615475e38cae699b5a6ea741';
    const WISH_API_SANDBOX_CLIENT_SECRET = '7fa5a1a26f2c44d2b25996fe9a51d519';
    const CEDCOMMERCE_PUBLIC_APP_URL = 'https://apps.cedcommerce.com/marketplace-integration/wish/auth/authorise';
    const CEDCOMMERCE_WISH_API_REDIRECT_URI = 'https://apps.cedcommerce.com/marketplace-integration/wish/auth/index';
    const PRODUCT = 'products';
    const ORDER = 'orders';
    const VARIATION = 'variations';
    const ORDER_SHIPMENT = 'orders/{id}/tracking';
    const ORDER_CARRIERS = 'orders/shipping_carriers';
    const ORDER_CANCEL = 'orders/{id}/refund';
    const WEBHOOKS_TOPICS = 'webhook/topics';
    const WEBHOOKS_SUBSCRIPTIONS = 'webhook/subscriptions';
    protected $version = 'v3';
    protected $old_version = 'v2';
    /**
     * @var string
     */
    protected $throttle;

    public static function getAuthorizationUrl($mode, $redirect_uri)
    {
        $url = self::CEDCOMMERCE_PUBLIC_APP_URL;
        $params = array();
        $params['mode'] = $mode;
        $params['redirect_uri'] = $redirect_uri;
        return $url . '?' . http_build_query($params);
    }

    public function getToken($code)
    {
        $params = $this->getConfig();
        $params['code'] = $code;
        $params['grant_type'] = 'authorization_code';

        $response = $this->getRequest(
            'oauth/access_token',
            $params
        );

        if ((isset($response['data']) && ($response['code'] == 0)) && isset($response['data']['access_token']) &&
            $response['data']['access_token']) {
            Configuration::updateValue('CED_WISH_ACCESS_TOKEN', $response['data']['access_token']);
            Configuration::updateValue('CED_WISH_REFRESH_TOKEN', $response['data']['refresh_token']);
            Configuration::updateValue('CED_WISH_EXPIRY_TIME', $response['data']['expiry_time']);
            Configuration::updateValue('CED_WISH_MERCHANT_ID', $response['data']['merchant_id']);
            $response = $response['data'];
            return array(
                'success' => true,
                'message' => $response
            );
        } elseif (isset($response['message'])) {
            $response = $response['message'];
        }
        return array(
            'success' => false,
            'message' => $response
        );
    }

    protected function getConfig()
    {
        if (Configuration::get('CED_WISH_API_MODE') && ((int)Configuration::get('CED_WISH_API_MODE') == 2)) {
            $params = array(
                'client_id' => self::WISH_API_SANDBOX_CLIENT_ID,
                'client_secret' => self::WISH_API_SANDBOX_CLIENT_SECRET,
                'redirect_uri' => self::CEDCOMMERCE_WISH_API_REDIRECT_URI
            );
        } else {
            $params = array(
                'client_id' => self::WISH_API_LIVE_CLIENT_ID,
                'client_secret' => self::WISH_API_LIVE_CLIENT_SECRET,
                'redirect_uri' => self::CEDCOMMERCE_WISH_API_REDIRECT_URI
            );
        }
        return $params;
    }

    protected function getRequest($method, $params = array())
    {
        return $this->request("get", $method, $params);
    }

    protected function request($type, $method, $params, $repeat_once = false)
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            try {
                $ch = curl_init();
                $url = $this->getRequestUrl(false);
                $url .= $method;
                $access_token = Configuration::get('CED_WISH_ACCESS_TOKEN');
                $result = false;
                $this->bindUrlParams($url, $params, $type);
                $curlConfig = array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_ENCODING => "",
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => Tools::strtoupper($type),
                );

                if ($access_token) {
                    if (in_array($type, array("post", "put"))) {
                        $curlConfig[CURLOPT_HTTPHEADER] = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $access_token,
                        );
                        $curlConfig[CURLOPT_POSTFIELDS] = json_encode($params);
                    } else {
                        $curlConfig[CURLOPT_HTTPHEADER] = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $access_token,
                        );
                    }
                } else {
                    $curlConfig[CURLOPT_HTTPHEADER] = array(
                        'Content-Type: application/json',
                    );
                }

                curl_setopt_array($ch, $curlConfig);
                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if ($result && ($httpcode == '200')) {
                    if ($data = @json_decode($result, true)) {
                        if (isset($data['code']) && (in_array($data['code'], array('1008','1016','1009')))) {
                            $this->refreshToken();
                            if (!$repeat_once) {
                                return $this->request($type, $method, $params, !$repeat_once);
                            }
                        }
                        return $data;
                    } else {
                        return array(
                            'success' => false,
                            'message' => json_decode($data, true)
                        );
                    }
                } elseif ($result) {
                    if ($data = @json_decode($result, true)) {
                        if (isset($data['code']) && (in_array($data['code'], array('1008','1016','1009')))) {
                            $this->refreshToken();
                            if (!$repeat_once) {
                                return $this->request($type, $method, $params, !$repeat_once);
                            }
                        }
                        return $data;
                    }
                    return array(
                        'success' => false,
                        'message' => $result
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => 'Failed to get Response from wish.com'
                    );
                }
            } catch (Exception $e) {
                CedWishHelper::addLog($e->getMessage() . $e->getTraceAsString());
                return array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
        } else {
            try {
                $client = new Client();
                $url = $this->getRequestUrl(false);
                $url .= $method;
                $access_token = Configuration::get('CED_WISH_ACCESS_TOKEN');
                $response = false;
                $this->bindUrlParams($url, $params, $type);
                if ($access_token) {
                    if (in_array($type, array("post", "put"))) {
                        $response = $client->{$type}(
                            $url,
                            [
                                'body' => json_encode($params),
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer ' . $access_token,
                                )
                            ]
                        );
                    } else {
                        $response = $client->{$type}(
                            $url,
                            [
                                'headers' => array(
                                    'Content-Type' => 'application/json',
                                    'Authorization' => 'Bearer ' . $access_token,
                                )
                            ]
                        );
                    }
                } else {
                    $response = $client->{$type}(
                        $url,
                        [
                            'headers' => array(
                                'Content-Type' => 'application/json',
                            )
                        ]
                    );
                }

                if ($response && ($response->getStatusCode() == '200')) {
                    if ($response->json()) {
                        $data = $response->json();
                        if (isset($data['estatus']) && ($data['estatus'] == 'success')) {
                            return isset($data['info' . $method]) ? $data['info' . $method] : $data;
                        } elseif (isset($data['estatus']) && ($data['estatus'] == 'error')) {
                            return array(
                                'success' => false,
                                'message' => $data['mensaje']
                            );
                        }
                        return $response->json();
                    } else {
                        if ($response->getBody()) {
                            return array(
                                'success' => false,
                                'message' => $response->getReasonPhrase()
                            );
                        }
                    }
                } elseif ($response) {
                    return array(
                        'success' => false,
                        'message' => $response->getReasonPhrase()
                    );
                } else {
                    return array(
                        'success' => false,
                        'message' => 'Failed to get Response from wish.com'
                    );
                }
            } catch (ClientException $e) {
                $json = $e->getResponse()->getBody()->getContents();
                $this->throttle = $e->getResponse()->getHeader('wish-rate-limit-remaining');
                $json = json_decode($json, true);
                if (isset($json['code']) && (in_array($json['code'], array('1008','1016','1009')))) {
                    $this->refreshToken();
                    if (!$repeat_once) {
                        return $this->request($type, $method, $params, !$repeat_once);
                    }
                }
                return array(
                    'success' => false,
                    'message' => isset($json['message']) ? $json['message'] : $e->getMessage()
                );
            } catch (Exception $e) {
                CedWishHelper::addLog($e->getMessage() . $e->getTraceAsString());
                return array(
                    'success' => false,
                    'message' => $e->getMessage()
                );
            }
        }
    }

    protected function getRequestUrl($old_version = false)
    {
        if (Configuration::get('CED_WISH_API_MODE') && ((int)Configuration::get('CED_WISH_API_MODE') == 2)) {
            $url = self::WISH_API_SANDBOX_ENDPOINT;
        } else {
            $url = self::WISH_API_LIVE_ENDPOINT;
        }

        if ($old_version) {
            $url .= $this->old_version . '/';
        } else {
            $url .= $this->version . '/';
        }
        return $url;
    }

    protected function bindUrlParams(&$url, $params, $is_post = false)
    {
        if ((count($params)==1) && isset($params['0']) && $params['0'] && !is_array($params['0'])) {
            $url .= '/' . $params['0'];
            unset($params['0']);
        }
        if (!empty($params) && ($is_post != 'post')) {
            $result = parse_url($url);
            if (isset($result['query']) && !empty($result['query'])) {
                $url .= '&' . http_build_query($params);
            } else {
                $url .= '?' . http_build_query($params);
            }
        }
    }

    public function refreshToken()
    {
        $params = $this->getConfig();
        $params['refresh_token'] = trim(Configuration::get('CED_WISH_REFRESH_TOKEN'));
        $params['grant_type'] = 'refresh_token';
        $response = $this->getRequest(
            'oauth/refresh_token',
            $params
        );

        if ((isset($response['data']) && ($response['code'] == 0)) && isset($response['data']['access_token']) &&
            $response['data']['access_token']) {
            Configuration::updateValue('CED_WISH_ACCESS_TOKEN', $response['data']['access_token']);
            Configuration::updateValue('CED_WISH_REFRESH_TOKEN', $response['data']['refresh_token']);
            Configuration::updateValue('CED_WISH_EXPIRY_TIME', $response['data']['expiry_time']);
            Configuration::updateValue('CED_WISH_MERCHANT_ID', $response['data']['merchant_id']);
            return array(
                'success' => true,
                'message' => $response
            );
        }
        return array(
            'success' => false,
            'message' => $response
        );
    }

    public function getWishCurrency()
    {
        return $this->getRequest(
            'merchant/currency_settings'
        );
    }

    public function getWebHooksTopic()
    {
        return $this->getRequest(
            self::WEBHOOKS_TOPICS
        );
    }

    public function getBrand($params)
    {
        return $this->getRequest('brands', $params);
    }

    public function getMerchantWarehouses()
    {
        return $this->getRequest('merchant/warehouses');
    }

    public function getMerchantReturnWarehouses()
    {
        return $this->getRequest('returns/get-all-warehouses', array(), true);
    }

    public function createWarehouse($params)
    {
        return $this->postRequest('merchant/warehouses', $params);
    }

    protected function postRequest($method, $params)
    {
        return $this->request("post", $method, $params);
    }

    public function createReturnWarehouse($params)
    {
        return $this->postRequest('returns/create-return-warehouse', $params, true);
    }

    public function addLogistic($params)
    {
        return $this->postRequest('returns/set-product-logistics', $params, true);
    }

    public function updateReturnWarehouse($id, $params)
    {
        $params['warehouse_id'] = $id;
        return $this->postRequest('returns/edit-return-warehouse', $params, true);
    }

    public function enrollProduct($params)
    {
        return $this->postRequest('returns/enroll-product-in-returns', $params, true);
    }

    public function unEnrollProduct($params)
    {
        return $this->postRequest('returns/disable-return-setting-for-region', $params, true);
    }

    public function getProduct($product_id)
    {
        return $this->getRequest(self::PRODUCT, array($product_id));
    }

    public function createProduct($product)
    {
        return $this->postRequest(self::PRODUCT, $product);
    }

    public function updateProduct($idProduct, $product, $sku_updated = false)
    {
        if (!$sku_updated) {
        }
        return $this->putRequest(self::PRODUCT . '/' . $idProduct, $product);
    }

    protected function putRequest($method, $params)
    {
        return $this->request("put", $method, $params);
    }

    public function massUpdateProduct($product)
    {
        return $this->postRequest('products/bulk_update', $product);
    }

    public function deleteProduct($product_id)
    {
        return $this->deleteRequest(self::PRODUCT, array($product_id));
    }

    protected function deleteRequest($method, $params = array())
    {
        return $this->request("delete", $method, $params);
    }

    public function enableProduct($product_id)
    {
        return $this->postRequest('product/enable', array('id' => $product_id), true);
    }

    public function createVariation($product_id, $variation)
    {
        return $this->postRequest(self::PRODUCT . '/' . $product_id . '/' . self::VARIATION, $variation);
    }

    public function getPendingOrders($params = array())
    {
        $objDateTime = new DateTime(date("Y-m-d H:i:s", strtotime("-1 days")));
        $objDateTime = $objDateTime->format('c');
        $params['released_at_min'] = $objDateTime;
        return $this->getRequest(self::ORDER, $params);
    }

    public function getShippedOrders($params)
    {
        return $this->getRequest(self::SHIPPED_ORDERS, $params);
    }

    public function getShippingLabel($order, $params)
    {
        return $this->getRequest(self::ORDER_ATTACHMENT . '/' . $order, $params);
    }

    public function getDeliveredOrders($params)
    {
        return $this->getRequest(self::DELIVERED_ORDERS, $params);
    }

    public function getOrder($params)
    {
        return $this->getRequest(self::ORDER, $params);
    }

    public function getJobStatus($params)
    {
        return $this->getRequest('variant/get-bulk-update-job-status', $params, true);
    }

    public function getJobSuccessSkus($params)
    {
        return $this->getRequest('variant/get-bulk-update-job-successes', $params, true);
    }

    public function getJobFailureSkus($params)
    {
        return $this->getRequest('variant/get-bulk-update-job-failures', $params, true);
    }

    public function makeShipment($id, $params)
    {
        $method = self::ORDER_SHIPMENT;
        $method = str_replace('{id}', $id, $method);
        return $this->putRequest($method, $params);
    }

    public function createBatch($params)
    {
        return $this->postRequest('products/bulk_get', $params);
    }

    public function updateBatch($params)
    {
        return $this->postRequest('product/get-download-job-status', $params, true);
    }

    public function updateBulkProductJob($job_id)
    {
        return $this->getRequest('products/bulk_get/' . $job_id, array());
    }

    public function updateBulkUpdateProductJob($job_id)
    {
        return $this->getRequest('products/bulk_update/' . $job_id, array());
    }

    public function cancelOrder($id, $params)
    {
        $method = self::ORDER_CANCEL;
        $method = str_replace('{id}', $id, $method);
        return $this->putRequest($method, $params);
    }

    public function getCarriers($order_type = 'GENERAL', $dest_country_code = '')
    {
        $params = array(
            'order_type' => $order_type
        );
        if ($dest_country_code && ($order_type != 'GENERAL')) {
            $params['dest_country_code'] = $dest_country_code;
        }
        return $this->getRequest(
            self::ORDER_CARRIERS,
            $params
        );
    }

    public function getAcceptedColors()
    {
        return $this->getRequest(
            'products/variations/colors'
        );
    }
}
