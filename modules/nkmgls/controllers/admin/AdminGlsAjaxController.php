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

use Nukium\GLS\Common\Legacy\GlsController;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class AdminGlsAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'nkmgls';
        parent::__construct();
    }

    public function displayAjaxCreateCarrier()
    {
        $return = [
            'hasError' => false,
            'errors' => '',
            'data' => '',
        ];
        $errors = [];

        $gls_carrier_code = Tools::getValue('code');
        if (!empty($gls_carrier_code) && array_key_exists($gls_carrier_code, NkmGls::$carrier_definition)) {
            if ($this->module->createCarrier($gls_carrier_code)) {
                die(json_encode($return));
            } else {
                $return['hasError'] = true;
                $errors[] = sprintf($this->l('Error creating the new carrier %s'), $gls_carrier_code);
            }
        } else {
            $return['hasError'] = true;
            $errors[] = $this->l('Invalid parameters');
        }

        if (count($errors) > 0) {
            $return['errors'] = implode("\r\n", $errors);
            $return['hasError'] = true;
        }

        die(json_encode($return));
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

        $postcode = '';
        $city = Tools::getValue('city', '');
        $countryCode = Tools::getValue('country_code', '');

        $idCountry = Country::getByIso($countryCode);
        if ($idCountry !== false) {
            $country = new Country($idCountry);
            $tmpPostcode = Tools::getValue('postcode', '');
            if ($country->checkZipCode($tmpPostcode)) {
                $postcode = $tmpPostcode;
            }
        }

        if (!empty($postcode)) {
            $gls = GlsController::createInstance($module_config);

            $result = $gls->searchRelay(
                $postcode,
                $city,
                $countryCode,
                '',
                [
                    'only_xl' => ((int) $module_config['GLS_GLSRELAIS_XL_ONLY'] === 1),
                ]
            );

            if (isset($result->exitCode->ErrorCode)) {
                if ((int) $result->exitCode->ErrorCode == 998 || (int) $result->exitCode->ErrorCode == 999) {
                    $return['message'] = $this->l('We haven\'t found any GLS Relais in your delivery area. Please expand your search.');
                } elseif ((int) $result->exitCode->ErrorCode == 0) {
                    $relay_points = $result->SearchResults;

                    if (count($relay_points) <= 0) {
                        $return['message'] = $this->l('We haven\'t found any GLS Relais in your delivery area. Please expand your search.');
                    } else {
                        $this->context->smarty->assign([
                            'trans_days' => [
                                '0' => $this->l('Monday'),
                                '1' => $this->l('Tuesday'),
                                '2' => $this->l('Wednesday'),
                                '3' => $this->l('Thursday'),
                                '4' => $this->l('Friday'),
                                '5' => $this->l('Saturday'),
                                '6' => $this->l('Sunday'),
                            ],
                            'relay_points' => $relay_points,
                            'only_xl' => $module_config['GLS_GLSRELAIS_XL_ONLY'],
                            'ps_version_177' => version_compare(_PS_VERSION_, '1.7.7', '>='),
                        ]);

                        if (version_compare(_PS_VERSION_, '1.7.6', '<')) {
                            $return['point_relay_tpl'] = $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/search_result.tpl');
                        } else {
                            $return['point_relay_tpl'] = $this->context->smarty->fetch('module:' . $this->module->name . '/views/templates/admin/search_result.tpl');
                        }
                        $return['result'] = true;
                    }
                } else {
                    $return['message'] = $result->exitCode->ErrorCode . ': ' . $result->exitCode->ErrorDscr;
                }
            } else {
                $return['message'] = $this->l('Unexpected error occured while searching GLS Relais.');
            }
        } else {
            $return['message'] = $this->l('Please fill in a valid postcode.');
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }

    public function displayAjaxChangeRelayPoint()
    {
        $return = [
            'result' => false,
            'message' => '',
        ];

        $id_order = (int) Tools::getValue('glsIdOrder');
        $gls_relay_id = Tools::getValue('glsrelayid');

        if (!empty($gls_relay_id) && $id_order > 0 && Validate::isLoadedObject($order = new Order($id_order))) {
            $module_config = $this->module->getConfigFormValues();

            $gls = GlsController::createInstance($module_config);
            $relay_detail = $gls->getRelayDetail($gls_relay_id);

            if ($relay_detail) {
                $id_country = Country::getByIso($relay_detail['Country']);
                $order_detail = NkmGls::getCartCarrierDetail((int) $order->id_cart, (int) $order->id_customer);
                $gls_product = $this->module->getGlsProductCode(
                    (int) $order->id_carrier,
                    $relay_detail['Country']
                );

                $duplicateAddress = false;
                $sql = '';
                if ($order_detail) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'gls_cart_carrier` SET `parcel_shop_id` = \'' . pSQL($gls_relay_id) . '\',';

                    if ($order_detail['id_carrier'] != $order->id_carrier) {
                        $sql .= 'id_carrier = \'' . (int) $order->id_carrier . '\',';
                        $duplicateAddress = true;
                    }

                    $sql .= 'gls_product = \'' . pSQL($gls_product) . '\',
                        name = \'' . pSQL($relay_detail['Name1']) . '\',
                        address1 = \'' . pSQL($relay_detail['Street1']) . '\',
                        address2 = \'' . pSQL($relay_detail['Street2']) . '\',
                        postcode = \'' . pSQL($relay_detail['ZipCode']) . '\',
                        city = \'' . pSQL($relay_detail['City']) . '\',
                        phone = \'' . pSQL($relay_detail['Phone']) . '\',
                        phone_mobile = \'' . pSQL($relay_detail['Mobile']) . '\',
                        id_country = \'' . (int) $id_country . '\',
                        parcel_shop_working_day = \'' . pSQL(json_encode($relay_detail['GLSWorkingDay'])) . '\'' .
                        'WHERE `id_customer`=' . (int) $order->id_customer . ' AND `id_cart`=' . (int) $order->id_cart;
                } else {
                    $duplicateAddress = true;

                    $sql = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . "gls_cart_carrier VALUES (
                        '" . (int) $order->id_cart . "',
                        '" . (int) $order->id_customer . "',
                        '" . (int) $order->id_carrier . "',
                        '" . pSQL($gls_product) . "',
                        '" . pSQL($gls_relay_id) . "',
                        '" . pSQL($relay_detail['Name1']) . "',
                        '" . pSQL($relay_detail['Street1']) . "',
                        '" . pSQL($relay_detail['Street2']) . "',
                        '" . pSQL($relay_detail['ZipCode']) . "',
                        '" . pSQL($relay_detail['City']) . "',
                        '" . pSQL($relay_detail['Phone']) . "',
                        '" . pSQL($relay_detail['Mobile']) . "',
                        '" . pSQL(Tools::getValue('glsCustomerMobile', '')) . "',
                        '" . (int) $id_country . "',
                        '" . pSQL(json_encode($relay_detail['GLSWorkingDay'])) . "'
                    )";
                }

                if (empty($sql) || (!empty($sql) && !Db::getInstance()->Execute($sql))) {
                    $return['message'] = $this->l('Unexpected error occurred while saving delivery informations.');
                } else {
                    if ($duplicateAddress) {
                        $new_address = new Address();
                    }

                    $addressDelivery = new Address($order->id_address_delivery);
                    $addressDelivery->company = $relay_detail['Name1'];
                    $addressDelivery->address1 = $relay_detail['Street1'];
                    $addressDelivery->address2 = $relay_detail['Street2'];
                    $addressDelivery->postcode = $relay_detail['ZipCode'];
                    $addressDelivery->city = $relay_detail['City'];
                    $addressDelivery->phone = $relay_detail['Phone'];
                    $addressDelivery->id_country = (int) $id_country;
                    $addressDelivery->country = Country::getNameById($this->context->language->id, (int) $id_country);

                    if ($duplicateAddress) {
                        $new_address = clone $addressDelivery;
                        $new_address->id = null;
                        $new_address->phone_mobile = Tools::getValue('glsCustomerMobile', '');
                        $new_address->deleted = true;
                        $new_address->add();

                        $order->id_address_delivery = $new_address->id;
                        $order->update();
                    } else {
                        $addressDelivery->update();
                    }

                    $return['result'] = true;
                }
            } else {
                $return['message'] = $this->l('GLS Relais informations not found. Please select another one.');
            }
        } else {
            $return['message'] = $this->l('Please select a GLS Relais');
        }

        header('Content-Type: application/json');
        $this->ajaxDie(json_encode($return));
    }

    public static function installInBO()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGlsAjax';

        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'GLSAjax';
        }

        $tab->id_parent = -1;
        $tab->module = 'nkmgls';

        return $tab->add();
    }

    public static function removeFromBO()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGlsAjax');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (validate::isLoadedObject($tab)) {
                return $tab->delete();
            }
        }

        return false;
    }
}
