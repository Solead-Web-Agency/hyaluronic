<?php
/**
 *  Module made by Nukium
 *
 *  @author    Nukium
 *  @copyright 2023 Nukium SAS
 *  @license   All rights reserved
 *
 * ███    ██ ██    ██ ██   ██ ██ ██    ██ ███    ███
 * ████   ██ ██    ██ ██  ██  ██ ██    ██ ████  ████
 * ██ ██  ██ ██    ██ █████   ██ ██    ██ ██ ████ ██
 * ██  ██ ██ ██    ██ ██  ██  ██ ██    ██ ██  ██  ██
 * ██   ████  ██████  ██   ██ ██  ██████  ██      ██
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Nukium\GLS\Common\Exception\GlsException;
use Nukium\GLS\Common\Legacy\GlsController;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class NkmGlsCheckoutModuleFrontController extends ModuleFrontController
{
    const L_SPECIFIC = 'checkout';

    public function __construct()
    {
        $this->module = 'nkmgls';
        parent::__construct();
    }

    public function displayAjaxSavePhoneMobile()
    {
        $cart = $this->context->cart;
        $return = [
            'result' => false,
            'message' => '',
        ];

        try {
            $phone_mobile = Tools::getValue('gls_customer_mobile');
            $id_carrier = Tools::getValue('id_carrier');
            $is_relay = Tools::getValue('is_relay');

            if ($cart && !empty($id_carrier)) {
                $customer_address = new Address($cart->id_address_delivery);
                $customer_country_iso = '';
                if ($customer_address) {
                    $customer_country_iso = Country::getIsoById($customer_address->id_country);
                }
                $gls_product = $this->module->getGlsProductCode(
                    (int) $id_carrier,
                    $customer_country_iso
                );

                $query = new DbQuery();
                $query->select('c.*')
                    ->from('gls_cart_carrier', 'c')
                    ->where('c.`id_customer` = ' . (int) $cart->id_customer)
                    ->where('c.`id_cart` = ' . (int) $cart->id);

                if (Db::getInstance()->getRow($query)) {
                    $sql = 'UPDATE ' . _DB_PREFIX_ . 'gls_cart_carrier SET `customer_phone_mobile`=\'' . pSQL($phone_mobile) . '\', `id_carrier`=' . (int) $id_carrier . ', `gls_product`=\'' . pSQL($gls_product) . '\'';
                    if (!$is_relay) {
                        $sql .= ',`parcel_shop_id` = NULL, `name` = NULL, `address1` = NULL, `address2` = NULL, `postcode` = NULL,
                            `city` = NULL, `phone` = NULL, `phone_mobile` = NULL, `id_country` = NULL, `parcel_shop_working_day` = NULL';
                    }
                    $sql .= ' WHERE `id_customer`=' . (int) $cart->id_customer . ' AND `id_cart`=' . (int) $cart->id;

                    if (Db::getInstance()->Execute($sql)) {
                        $return['result'] = true;
                    } else {
                        throw new GlsException($this->module->l('Unexpected error occurred.', self::L_SPECIFIC));
                    }
                } else {
                    if (Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'gls_cart_carrier
                        (`id_customer`, `id_cart`, `id_carrier`, `gls_product`, `customer_phone_mobile`)
                        VALUES (' . (int) $cart->id_customer . ', ' . (int) $cart->id . ', ' . (int) $id_carrier . ', \'' . pSQL($gls_product) . '\', \'' . pSQL($phone_mobile) . '\')')) {
                        $return['result'] = true;
                    } else {
                        throw new GlsException($this->module->l('Unexpected error occurred.', self::L_SPECIFIC));
                    }
                }
            }
        } catch (GlsException $e) {
            $return['result'] = false;
            $return['message'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }

    public function displayAjaxSelectRelayPoint()
    {
        $return = [
            'result' => false,
            'message' => '',
        ];

        $gls_relay_id = Tools::getValue('glsrelayid');
        $module_config = $this->module->getConfigFormValues();

        $gls = GlsController::createInstance($module_config);
        $relay_detail = $gls->getRelayDetail($gls_relay_id);

        if ($relay_detail) {
            $cart = $this->context->cart;
            $id_country = Country::getByIso($relay_detail['Country']);

            $gls_product = $this->module->getGlsProductCode(
                Configuration::get('GLS_GLSRELAIS_ID', (int) $cart->id_carrier, $this->context->shop->id_shop_group, $this->context->shop->id),
                $relay_detail['Country']
            );

            Db::getInstance()->delete('gls_cart_carrier', 'id_cart = "' . pSQL($cart->id) . '"');
            $sql = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . "gls_cart_carrier VALUES (
                '" . (int) $cart->id . "',
                '" . (int) $cart->id_customer . "',
                '" . (int) Configuration::get('GLS_GLSRELAIS_ID', (int) $cart->id_carrier, $this->context->shop->id_shop_group, $this->context->shop->id) . "',
                '" . pSQL($gls_product) . "',
                '" . pSQL(Tools::getValue('glsrelayid')) . "',
                '" . pSQL($relay_detail['Name1']) . "',
                '" . pSQL($relay_detail['Street1']) . "',
                '" . pSQL($relay_detail['Street2']) . "',
                '" . pSQL($relay_detail['ZipCode']) . "',
                '" . pSQL($relay_detail['City']) . "',
                '" . pSQL($relay_detail['Phone']) . "',
                '" . pSQL($relay_detail['Mobile']) . "',
                '" . pSQL(Tools::getValue('gls_customer_mobile', '')) . "',
                '" . (int) $id_country . "',
                '" . pSQL(json_encode($relay_detail['GLSWorkingDay'])) . "'
            )";

            if (!Db::getInstance()->Execute($sql)) {
                $return['message'] = $this->module->l('Unexpected error occurred while saving delivery informations.', self::L_SPECIFIC);
            } else {
                $return['result'] = true;
            }
        } else {
            $return['message'] = $this->module->l('GLS Relais informations not found. Please select another one.', self::L_SPECIFIC);
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }

    public function displayAjaxSearchRelay()
    {
        $module_config = $this->module->getConfigFormValues();
        $return = [
            'result' => false,
            'message' => '',
            'point_relay_tpl' => '',
            'point_relay_maps' => [],
        ];

        $address1 = Tools::getValue('address1', '');
        $postcode = '';
        $city = Tools::getValue('city', '');
        $countryCode = 'FR';

        $cart_carrier_detail = null;
        if (Validate::isLoadedObject($this->context->cart)) {
            $cart_carrier_detail = NkmGls::getCartCarrierDetail(
                $this->context->cart->id,
                $this->context->cart->id_customer,
                $module_config['GLS_GLSRELAIS_ID']
            );
        }

        $deliveryAddress = new Address((int) $this->context->cart->id_address_delivery);
        if (Validate::isLoadedObject($deliveryAddress)) {
            $country = new Country((int) $deliveryAddress->id_country);
            $tmpPostcode = Tools::getValue('postcode', '');

            if ($country->checkZipCode($tmpPostcode)) {
                $postcode = $tmpPostcode;
                $countryCode = $country->iso_code;
            }
        }

        if ($countryCode === 'COS') {
            $countryCode = 'FR';
        }

        if (!empty($postcode)) {
            $gls = GlsController::createInstance($module_config);

            $result = $gls->searchRelay(
                $postcode,
                $city,
                $countryCode,
                $address1,
                [
                    'only_xl' => ((int) $module_config['GLS_GLSRELAIS_XL_ONLY'] === 1),
                ]
            );

            if (isset($result->exitCode->ErrorCode)) {
                if (
                    (int) $result->exitCode->ErrorCode == 998 ||
                    (int) $result->exitCode->ErrorCode == 999
                ) {
                    $return['message'] = $this->module->l('We haven\'t found any GLS Relais in your delivery area. Please expand your search.', self::L_SPECIFIC);
                } elseif ((int) $result->exitCode->ErrorCode == 0) {
                    $relay_points = $result->SearchResults;

                    if (empty($relay_points)) {
                        $return['message'] = $this->module->l('We haven\'t found any GLS Relais in your delivery area. Please expand your search.', self::L_SPECIFIC);
                    } else {
                        $this->context->smarty->assign([
                            'trans_days' => [
                                '0' => $this->module->l('Monday', self::L_SPECIFIC),
                                '1' => $this->module->l('Tuesday', self::L_SPECIFIC),
                                '2' => $this->module->l('Wednesday', self::L_SPECIFIC),
                                '3' => $this->module->l('Thursday', self::L_SPECIFIC),
                                '4' => $this->module->l('Friday', self::L_SPECIFIC),
                                '5' => $this->module->l('Saturday', self::L_SPECIFIC),
                                '6' => $this->module->l('Sunday', self::L_SPECIFIC),
                            ],
                            'current_relay' => ($cart_carrier_detail !== null && !empty($cart_carrier_detail['parcel_shop_id']))
                                ? $cart_carrier_detail['parcel_shop_id']
                                : '',
                            'relay_points' => $relay_points,
                        ]);
                        $return['point_relay_tpl'] = $this->context->smarty->fetch('module:' . $this->module->name . '/views/templates/front/search_result.tpl');
                        $return['result'] = true;
                    }
                } else {
                    $return['message'] = $result->exitCode->ErrorDscr;
                }
            } else {
                $return['message'] = $this->module->l('Service temporarily unavailable, try again later.', self::L_SPECIFIC);
            }
        } else {
            $return['message'] = $this->module->l('Please fill in a valid postcode.', self::L_SPECIFIC);
        }

        if (!$return['result']) {
            http_response_code(400);
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }
}
