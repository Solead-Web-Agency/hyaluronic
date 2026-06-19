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

class AdminCedWishSettingController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!Tools::getIsset('merchant_id')) {
            $link = new LinkCore();
            $controller_link = $link->getAdminLink('AdminModules');
            Tools::redirectAdmin($controller_link . '&configure=cedwish');
        }
    }

    public function displayAjaxIsAuthorisationDone()
    {
        $access_token = Configuration::get('CED_WISH_ACCESS_TOKEN');
        if (Tools::strlen($access_token) > 10) {
            die(
                Tools::jsonEncode(
                    array(
                        'redirect' => true
                    )
                )
            );
        } else {
            die(Tools::jsonEncode(array('waiting' => '5')));
        }
    }

    public function displayAjaxResetToken()
    {
        Configuration::updateValue('CED_WISH_ACCESS_TOKEN', '');
        Configuration::updateValue('CED_WISH_REFRESH_TOKEN', '');
        die(
            Tools::jsonEncode(
                array(
                    'success' => true
                )
            )
        );
    }

    public function displayAjaxAuthorisationUrl()
    {
        $api_mode = Configuration::get('CED_WISH_API_MODE');
        if ($api_mode) {
            $redirect_uri = $this->context->link->getModuleLink(
                'cedwish',
                'authorization',
                []
            );
            $response = CedWishApi::getAuthorizationUrl($api_mode, $redirect_uri);
            die(
                Tools::jsonEncode(
                    array(
                        'redirect_uri' => $response,
                        'success' => true
                    )
                )
            );
        } else {
            die(Tools::jsonEncode(array('error' => 'Failed to generate redirect url')));
        }
    }

    public function displayAjaxRefreshToken()
    {
        if (Configuration::get('CED_WISH_ACCESS_TOKEN') && Configuration::get('CED_WISH_REFRESH_TOKEN')) {
            header('Content-Type: application/json');
            $apiHelper = new CedWishApi();
            $apiHelper->refreshToken();
            die(
                Tools::jsonEncode(
                    array(
                        'success' => true
                    )
                )
            );
        } else {
            die(Tools::jsonEncode(array('error' => 'Failed to refresh token')));
        }
    }

    public function displayAjaxGetWarehouses()
    {
        header('Content-Type: application/json');
        $apiHelper = new CedWishApi();
        $response = $apiHelper->getMerchantWarehouses();
        if (isset($response['code']) && ($response['code'] == 0)) {
            $response = $response['data'];
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "cedwish_warehouse` ");
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cedwish_warehouse` (
                    `id_cedwish_warehouse`,
                    `id`,
                    `shipping_type`,
                    `name`,
                    `address`,
                    `destination_countries`,
                    `ship_to_name`,
                    `city`,
                    `state`,
                    `country_code`,
                    `zipcode`,
                    `street_address1`,
                    `street_address2`
                    ) VALUES ';
            foreach ($response as $warehouse) {
                if (!isset($warehouse['destination_countries'])) {
                    $warehouse['destination_countries'] = array();
                }
                if (!isset($warehouse['shipping_type'])) {
                    $warehouse['shipping_type'] = '';
                }

                if (isset($warehouse['name'])) {
                    $warehouse['warehouse_type_name'] = $warehouse['name'];
                }

                if (!isset($warehouse['address'])) {
                    $warehouse['address'] = array(
                        'ship_to_name' => '',
                        'city' => '',
                        'state' => '',
                        'country_code' => '',
                        'zipcode' => '',
                        'street_address1' => '',
                        'street_address2' => '',
                    );
                }

                $sql .= "(
                        NULL,
                        '" . pSQL($warehouse['id']) . "',
                        '" . pSQL($warehouse['shipping_type']) . "',
                        '" . pSQL($warehouse['warehouse_type_name']) . "',
                        '" . pSQL(json_encode($warehouse['address'])) . "',
                        '" . pSQL(json_encode($warehouse['destination_countries'])) . "',
                        '" . pSQL($warehouse['address']['ship_to_name']) . "',
                        '" . pSQL($warehouse['address']['city']) . "',
                        '" . pSQL($warehouse['address']['state']) . "',
                        '" . pSQL($warehouse['address']['country_code']) . "',
                        '" . pSQL($warehouse['address']['zipcode']) . "',
                        '" . pSQL($warehouse['address']['street_address1']) . "',
                        '" . pSQL($warehouse['address']['street_address2']) . "'
                        ), ";
            }
            $sql = rtrim($sql, ", ");
            Db::getInstance()->execute($sql);

            $response = array(
                'success' => true,
                'message' => 'Warehouses Fetched successfully.'
            );
        } elseif (isset($response['message']) && $response['message']) {
            $response = array(
                'success' => false,
                'message' => $response['message']
            );
        }
        if ($response) {
            die(
                Tools::jsonEncode(
                    $response
                )
            );
        }
    }

    public function displayAjaxGetColors()
    {
        header('Content-Type: application/json');
        $response = CedWishHelper::getCarriers(true);
        if (!empty($response)) {
            $response = array(
                'success' => true,
                'message' => $this->l('Colors Fetched successfully.')
            );
        } elseif (isset($response['message']) && $response['message']) {
            $response = array(
                'success' => false,
                'message' => $response['message']
            );
        }
        if ($response) {
            die(
                Tools::jsonEncode(
                    $response
                )
            );
        }
    }

    public function displayAjaxGetCarriers()
    {
        header('Content-Type: application/json');
        $response = CedWishHelper::getCarriers(true);
        if (!empty($response)) {
            $response = array(
                'success' => true,
                'message' => $this->l('Carriers Fetched successfully.')
            );
        } elseif (isset($response['message']) && $response['message']) {
            $response = array(
                'success' => false,
                'message' => $response['message']
            );
        }
        if ($response) {
            die(
                Tools::jsonEncode(
                    $response
                )
            );
        }
    }

    public function displayAjaxGetCurrencyFromWish()
    {
        header('Content-Type: application/json');
        $apiHelper = new CedWishApi();
        $ajax_output = '';
        $success = false;
        $response = $apiHelper->getWishCurrency();
        if (isset($response['code']) && ($response['code'] == 0)) {
            $response = $response['data'];
            if (isset($response['localized_currency'])
                && $response['localized_currency']
            ) {
                Configuration::updateValue('CED_WISH_WISH_CURRENCY', $response['localized_currency']);
                foreach ($response as $key => $value) {
                    $ajax_output .= $key . '  =>  ' . $value . PHP_EOL;
                }
                $success = true;
            } else {
                $ajax_output .= 'Some Error While getting Currency.';
                $success = false;
            }
        }

        $params = CedWishHelper::getInstallationStats();
        $params['current_step'] = "CONFIGURATION";
        CedWishHelper::registerSellerInfo($params);
        if ($response) {
            die(
                Tools::jsonEncode(
                    array('success' => $success, 'message' => $ajax_output)
                )
            );
        }
    }
}
