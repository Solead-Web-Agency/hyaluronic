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
use Nukium\GLS\Common\Legacy\GlsApi;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class AdminGlsLabelController extends ModuleAdminController
{
    private static $tab_lang = ['fr' => 'GLS étiquettes'];

    public $order_state = [];
    public $carrier = [];
    public $tmpDirectory = 'tmp';

    public $verifyTrackingUrl = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        $this->module = 'nkmgls';
        $this->context = Context::getContext();

        parent::__construct();

        $this->name = 'GlsLabel';

        $this->order_state = OrderState::getOrderStates($this->context->language->id);
        $this->carrier = Carrier::getCarriers($this->context->language->id, false, false, false, null, null);
    }

    public static function installInBO()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGlsLabel';

        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            if (isset(self::$tab_lang[$lang['iso_code']])) {
                $tab->name[(int) $lang['id_lang']] = self::$tab_lang[$lang['iso_code']];
            } else {
                $tab->name[(int) $lang['id_lang']] = 'GLS delivery label';
            }
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = 'nkmgls';

        return $tab->add();
    }

    public static function removeFromBO()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGlsLabel');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (validate::isLoadedObject($tab)) {
                return $tab->delete();
            }
        }

        return false;
    }

    public function renderView()
    {
        $init_form = false;
        if (empty($this->errors)
            && ((bool) Tools::isSubmit('generateLabelStep2') === true || (bool) Tools::isSubmit('generateLabelStep3Cancel') === true)
        ) {
            $init_form = $this->initFormLabelStep2();
        } elseif (empty($this->errors) && (bool) Tools::isSubmit('generateLabelStep3') === true) {
            $init_form = $this->initFormLabelStep3();
        }

        if (!$init_form) {
            $statuses = [];
            foreach ($this->order_state as $value) {
                if ((int) $value['id_order_state'] > 0 && $value['id_order_state'] != _PS_OS_CANCELED_ && $value['id_order_state'] != _PS_OS_ERROR_) {
                    $statuses[] = ['id_option' => $value['id_order_state'], 'name' => $value['name'], 'val' => $value['id_order_state']];
                }
            }
            $new_statuses = $statuses;
            array_unshift($new_statuses, ['id_option' => '0', 'name' => $this->l('(No change)'), 'val' => '']);

            $init_form = $this->initFormLabel($statuses, $new_statuses) . $this->initFormShopReturn($new_statuses);
        }

        return $init_form;
    }

    public function getConfigFormValues()
    {
        $fieds = [
            'GLS_LABEL_NEW_ORDER_STATE' => Tools::getValue('GLS_LABEL_NEW_ORDER_STATE', Configuration::get('GLS_LABEL_NEW_ORDER_STATE')),
            'GLS_LABEL_SINGLE_NEW_ORDER_STATE' => Tools::getValue('GLS_LABEL_SINGLE_NEW_ORDER_STATE', Configuration::get('GLS_LABEL_SINGLE_NEW_ORDER_STATE')),
        ];

        foreach ($this->order_state as $value) {
            if (in_array($value['id_order_state'], explode(',', Configuration::get('GLS_LABEL_ORDER_STATE_FILTER')))) {
                $fieds['GLS_LABEL_ORDER_STATE_FILTER_' . $value['id_order_state']] = true;
            } else {
                $fieds['GLS_LABEL_ORDER_STATE_FILTER_' . $value['id_order_state']] = false;
            }
        }

        $carrier_filter = Configuration::get('GLS_LABEL_CARRIER_FILTER', '');
        foreach ($this->carrier as $value) {
            if (empty($carrier_filter) && $value['external_module_name'] == 'nkmgls') {
                $fieds['GLS_LABEL_CARRIER_FILTER_' . $value['id_carrier']] = true;
            } elseif (in_array($value['id_carrier'], explode(',', $carrier_filter))) {
                $fieds['GLS_LABEL_CARRIER_FILTER_' . $value['id_carrier']] = true;
            } else {
                $fieds['GLS_LABEL_CARRIER_FILTER_' . $value['id_carrier']] = false;
            }
        }

        return $fieds;
    }

    public function initFormLabel($statuses, $new_statuses)
    {
        $carriers = [];
        foreach ($this->carrier as $value) {
            $name = $value['name'] . ' (' . $this->l('ID:') . ' ' . $value['id_carrier'] . ')';
            if ($value['active'] == '0') {
                $name .= ' [' . $this->l('Disabled') . ']';
            }
            $carriers[] = ['id_option' => $value['id_carrier'], 'name' => $name, 'val' => $value['id_carrier']];
        }

        $this->fields_value = $this->getConfigFormValues();
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Delivery label printing'),
                'icon' => 'icon-print',
            ],
            'description' => $this->l('Generate your GLS shipping labels.'),
            'input' => [
                [
                    'type' => 'checkbox',
                    'label' => $this->l('Carriers'),
                    'name' => 'GLS_LABEL_CARRIER_FILTER',
                    'multiple' => true,
                    'required' => true,
                    'values' => [
                        'query' => $carriers,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'checkbox',
                    'label' => $this->l('Order statuses'),
                    'name' => 'GLS_LABEL_ORDER_STATE_FILTER',
                    'multiple' => true,
                    'required' => true,
                    'values' => [
                        'query' => $statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Change orders status to'),
                    'name' => 'GLS_LABEL_NEW_ORDER_STATE',
                    'require' => false,
                    'options' => [
                        'query' => $new_statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
            ],
            'buttons' => [
                0 => [
                    'type' => 'submit',
                    'title' => $this->l('Next'),
                    'id' => 'generateLabelStep2',
                    'name' => 'generateLabelStep2',
                    'class' => 'pull-right',
                    'icon' => 'process-icon-next',
                    'js' => '$(this).val(\'1\')',
                ],
            ],
        ];

        $this->submit_action = 'generateLabel';
        $this->show_toolbar = false;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    public function initFormShopReturn($new_statuses)
    {
        $label_type_query = [];
        if (Configuration::get('GLS_API_SHOP_RETURN_SERVICE')) {
            $label_type_query = [
                ['id' => 'return', 'name' => $this->l('Return')],
                ['id' => 'shipment', 'name' => $this->l('Shipment')],
            ];
        } else {
            $label_type_query = [
                ['id' => 'shipment', 'name' => $this->l('Shipment')],
            ];
        }

        $this->fields_value = $this->getConfigFormValues();
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Single delivery label printing'),
                'icon' => 'icon-print',
            ],
            'description' => $this->l('Generate your GLS shipping labels for a single order by searching for its ID or reference.'),
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->l('Label type'),
                    'name' => 'GLS_LABEL_SINGLE_TYPE',
                    'require' => true,
                    'options' => [
                        'query' => $label_type_query,
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Order ID'),
                    'lang' => false,
                    'name' => 'GLS_LABEL_ORDER_ID',
                    'class' => 'input fixed-width-xl',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Order reference'),
                    'lang' => false,
                    'name' => 'GLS_LABEL_ORDER_REF',
                    'class' => 'input fixed-width-xl',
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Change order status to'),
                    'name' => 'GLS_LABEL_SINGLE_NEW_ORDER_STATE',
                    'require' => false,
                    'options' => [
                        'query' => $new_statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
            ],
            'buttons' => [
                0 => [
                    'type' => 'submit',
                    'title' => $this->l('Next'),
                    'id' => 'generateLabelStep3',
                    'name' => 'generateLabelStep3',
                    'class' => 'pull-right',
                    'icon' => 'process-icon-next',
                    'js' => '$(this).val(\'1\')',
                ],
            ],
        ];

        $this->submit_action = 'generateLabel';
        $this->show_toolbar = false;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    private function getOrders()
    {
        $sql = new DbQuery();
        $sql->select('o.*,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            ca.`name` AS `caname`,
            a.`id_country`,
            oc.`weight` as `order_weight`,
            IF(gls.`customer_phone_mobile` IS NOT NULL AND gls.`customer_phone_mobile` != \'\', gls.`customer_phone_mobile`, a.`phone_mobile`) AS `customer_phone_mobile`,
            a.`phone` AS `customer_phone`,
            c.`email` as `customer_email`')
            ->from('orders', 'o')
            ->leftJoin('gls_cart_carrier', 'gls', 'gls.`id_cart` = o.`id_cart` AND gls.`id_customer` = o.`id_customer`')
            ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
            ->leftJoin('order_carrier', 'oc', 'o.`id_order` = oc.`id_order`')
            ->leftJoin('carrier', 'ca', 'o.`id_carrier` = ca.`id_carrier`')
            ->leftJoin('order_state', 'os', 'os.`id_order_state` = o.`current_state`')
            ->leftJoin('order_state_lang', 'osl', 'os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id)
            ->leftJoin('address', 'a', 'a.`id_address` = o.`id_address_delivery`')
            ->where('ca.id_carrier IS NOT NULL')
            ->orderBy('o.id_order ASC');

        if (Shop::isFeatureActive() && Shop::getContextShopID()) {
            $sql->where('o.id_shop = ' . Shop::getContextShopID());
        }

        $id_country_corsica = Country::getByIso('COS');

        if (Tools::getIsset('orderBox') && is_array(Tools::getValue('orderBox')) && count(Tools::getValue('orderBox')) > 0) {
            $sql->where('o.`id_order` IN (' . implode(',', array_map('intval', Tools::getValue('orderBox'))) . ')');
        } elseif (Tools::getIsset('GLS_LABEL_ORDER_ID') && (int) Tools::getValue('GLS_LABEL_ORDER_ID') > 0) {
            $sql->where('o.`id_order` = ' . (int) Tools::getValue('GLS_LABEL_ORDER_ID'));

            if (Tools::getIsset('GLS_LABEL_SINGLE_TYPE') && Tools::getValue('GLS_LABEL_SINGLE_TYPE') == 'return') {
                if ($id_country_corsica !== false) {
                    $sql->where('(a.`id_country` = ' . (int) Country::getByIso('FR') . ' OR a.`id_country` = ' . (int) $id_country_corsica . ')');
                } else {
                    $sql->where('a.`id_country` = ' . (int) Country::getByIso('FR'));
                }
            }
        } elseif (Tools::getIsset('GLS_LABEL_ORDER_REF') && !Tools::isEmpty(Tools::getValue('GLS_LABEL_ORDER_REF'))) {
            $sql->where('o.`reference` = \'' . pSQL(Tools::getValue('GLS_LABEL_ORDER_REF')) . '\'');

            if (Tools::getIsset('GLS_LABEL_SINGLE_TYPE') && Tools::getValue('GLS_LABEL_SINGLE_TYPE') == 'return') {
                if ($id_country_corsica !== false) {
                    $sql->where('(a.`id_country` = ' . (int) Country::getByIso('FR') . ' OR a.`id_country` = ' . (int) $id_country_corsica . ')');
                } else {
                    $sql->where('a.`id_country` = ' . (int) Country::getByIso('FR'));
                }
            }
        } else {
            $order_carrier = explode(',', Configuration::get('GLS_LABEL_CARRIER_FILTER'));
            $carriers_id_history = $this->module->getCarrierIdHistory();
            foreach ($carriers_id_history as $value) {
                foreach ($order_carrier as $c) {
                    if (is_array($value) && in_array($c, $value)) {
                        $order_carrier = array_merge($order_carrier, $value);
                    }
                }
            }

            if (is_array($order_carrier) && count($order_carrier) > 0) {
                $sql->where('ca.`id_carrier` IN (' . implode(',', array_map('intval', $order_carrier)) . ')');
            }
            $sql->where('o.current_state IN (' . Configuration::get('GLS_LABEL_ORDER_STATE_FILTER') . ')');
        }

        return Db::getInstance()->ExecuteS($sql);
    }

    public function initFormLabelStep2()
    {
        $order_state = [];
        if (Configuration::get('GLS_LABEL_ORDER_STATE_FILTER')) {
            $order_state = explode(',', Configuration::get('GLS_LABEL_ORDER_STATE_FILTER'));
        }

        $order_carrier = [];
        if (Configuration::get('GLS_LABEL_CARRIER_FILTER')) {
            $order_carrier = explode(',', Configuration::get('GLS_LABEL_CARRIER_FILTER'));
        }
        if (is_array($order_state)
            && count($order_state) > 0
            && is_array($order_carrier)
            && count($order_carrier) > 0
        ) {
            foreach ($this->order_state as $status) {
                $this->statuses_array[$status['id_order_state']] = $status['name'];
            }

            $data_order = $this->getOrders();
            if (!empty($data_order)) {
                $this->fields_list = [
                    'id_order' => [
                        'title' => $this->trans('ID', [], 'Admin.Global'),
                        'align' => 'text-center',
                        'class' => 'fixed-width-xs',
                    ],
                    'reference' => [
                        'title' => $this->trans('Reference', [], 'Admin.Global'),
                    ],
                    'customer' => [
                        'title' => $this->trans('Customer', [], 'Admin.Global'),
                        'havingFilter' => true,
                    ],
                    'total_paid_tax_incl' => [
                        'title' => $this->trans('Total', [], 'Admin.Global'),
                        'align' => 'text-right',
                        'type' => 'price',
                        'currency' => true,
                        'badge_success' => true,
                    ],
                    'payment' => [
                        'title' => $this->trans('Payment', [], 'Admin.Global'),
                    ],
                    'osname' => [
                        'title' => $this->trans('Status', [], 'Admin.Global'),
                        'type' => 'select',
                        'color' => 'color',
                        'list' => $this->statuses_array,
                        'filter_key' => 'os!id_order_state',
                        'filter_type' => 'int',
                        'order_key' => 'osname',
                    ],
                    'date_add' => [
                        'title' => $this->trans('Date', [], 'Admin.Global'),
                        'type' => 'datetime',
                        'filter_key' => 'a!date_add',
                    ],
                    'caname' => [
                        'title' => $this->l('Carrier'),
                    ],
                ];

                $helper = new HelperList();
                $helper->module = $this->module;
                $helper->identifier = 'id_order';
                $helper->shopLinkType = '';
                $helper->simple_header = true;
                $helper->show_toolbar = true;
                $helper->bulk_actions = [
                    'divider' => [
                        'text' => 'divider',
                    ],
                ];
                $helper->force_show_bulk_actions = true;
                $helper->no_link = true;
                $helper->title = $this->l('Orders');
                $helper->table = 'order';
                $helper->token = Tools::getAdminTokenLite('Admin' . $this->name);
                $helper->currentIndex = $this->context->link->getAdminLink('Admin' . $this->name);

                return $helper->generateList($data_order, $this->fields_list);
            }
        }

        $this->errors[] = $this->l('No orders has been found, please check your search filters.');
    }

    public function initFormLabelStep3()
    {
        $tpl = $this->createTemplate('label_list.tpl');

        $data_order = $this->getOrders();
        if (!empty($data_order)) {
            $sql = new DbQuery();
            $sql->select('GROUP_CONCAT(c.`id_country` SEPARATOR \',\') AS `countries`')
                ->from('country', 'c')
                ->where("c.`iso_code` IN ('DE','AT','BE','CY','ES','EE','FI','FR','COS','MC','GR','IE','IT','LV','LT','LU','MT','NL','PT','SK','SI','CZ','RO','BG','HR','HU','DK','PL','SE')");
            $result = Db::getInstance()->getRow($sql);

            $glsCarriersIds = $this->module->getCarrierIdHistory();

            $gls_mobile_required = [];
            foreach ($glsCarriersIds as $key => $value) {
                if ($key != 'GLSCHEZVOUS') {
                    $gls_mobile_required = array_merge($gls_mobile_required, $value);
                }
            }

            $carriers = [];
            foreach ($this->carrier as $value) {
                $carriers[$value['id_carrier']] = $value;
            }

            $data = [
                'list' => $data_order,
                'carriers' => $carriers,
                'cee_countries' => explode(',', $result['countries']),
                'shop_return_service' => Configuration::get('GLS_API_SHOP_RETURN_SERVICE'),
                'gls_mobile_required' => $gls_mobile_required,
                'gls_carriers_ids' => $glsCarriersIds,
                'back_step2_url' => $this->context->link->getAdminLink('Admin' . $this->name),
                'gls_label_single_type' => Tools::getValue('GLS_LABEL_SINGLE_TYPE', ''),
                'gls_order_reference_enable' => Configuration::get('GLS_EXPORT_ORDER_REFERENCE_ENABLE'),
            ];

            if (Tools::getIsset('gls_print_label_from_order')
                && (int) Tools::getValue('gls_print_label_from_order') == 1
                && Tools::getIsset('GLS_LABEL_ORDER_ID')
                && (int) Tools::getValue('GLS_LABEL_ORDER_ID') >= 0) {
                $data['back_step2_url'] = $this->context->link->getAdminLink('AdminOrders') . '&vieworder&id_order=' . (int) Tools::getValue('GLS_LABEL_ORDER_ID');
            }

            $tpl->assign($data);

            return $tpl->fetch();
        } else {
            $this->errors[] = $this->l('No orders has been found, please check your search filters.');

            return false;
        }
    }

    public function initToolbarTitle()
    {
        $this->toolbar_title = $this->l('GLS delivery label');
    }

    public function postProcess()
    {
        if ((bool) Tools::isSubmit('generateLabelStep2') === true) {
            $form_values = $this->getConfigFormValues();
            foreach ($form_values as $key => $value) {
                if (strpos($key, 'GLS_LABEL_ORDER_STATE_FILTER') === false && strpos($key, 'GLS_LABEL_CARRIER_FILTER') === false) {
                    Configuration::updateValue($key, trim($value));
                }
            }

            $order_state_selected = [];
            foreach ($this->order_state as $value) {
                if (Tools::getValue('GLS_LABEL_ORDER_STATE_FILTER_' . $value['id_order_state'])) {
                    $order_state_selected[] = $value['id_order_state'];
                }
            }
            Configuration::updateValue('GLS_LABEL_ORDER_STATE_FILTER', implode(',', array_map('intval', $order_state_selected)));

            if (count($order_state_selected) <= 0) {
                $this->errors[] = $this->l('Please select one or more order status.');
            }

            $carrier_selected = [];
            foreach ($this->carrier as $value) {
                if (Tools::getValue('GLS_LABEL_CARRIER_FILTER_' . $value['id_carrier'])) {
                    $carrier_selected[] = $value['id_carrier'];
                }
            }
            Configuration::updateValue('GLS_LABEL_CARRIER_FILTER', implode(',', array_map('intval', $carrier_selected)));

            if (count($carrier_selected) <= 0) {
                $this->errors[] = $this->l('Please select one or more carrier.');
            }
        } elseif ((bool) Tools::isSubmit('generateLabelStep3') === true) {
            if (Tools::getIsset('orderBox') && (
                !is_array(Tools::getValue('orderBox'))
                || (is_array(Tools::getValue('orderBox')) && count(Tools::getValue('orderBox')) <= 0))
            ) {
                $this->errors[] = $this->l('Please select at least one order.');
            } elseif (Tools::getIsset('GLS_LABEL_ORDER_ID') && Tools::getIsset('GLS_LABEL_ORDER_REF')) {
                Configuration::updateValue('GLS_LABEL_SINGLE_NEW_ORDER_STATE', (int) Tools::getValue('GLS_LABEL_SINGLE_NEW_ORDER_STATE'));
                if ((int) Tools::getValue('GLS_LABEL_ORDER_ID') <= 0 && Tools::isEmpty(Tools::getValue('GLS_LABEL_ORDER_REF'))) {
                    $this->errors[] = $this->l('No order has been found.');
                }
            } elseif (Tools::getIsset('orderBox') !== true
                && Tools::getIsset('GLS_LABEL_ORDER_ID') !== true
                && Tools::getIsset('GLS_LABEL_ORDER_REF') !== true
            ) {
                $this->errors[] = $this->l('An error occured, please try again.');
            }
        }

        return parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin-order.css');

        Media::addJsDef([
            'back_url' => $this->context->link->getAdminLink('Admin' . $this->name),
            'print_block' => $this->l('Fill in the order informations before you can print the associated label'),
            'print_ready' => $this->l('Print label'),
            'ajax_uri' => $this->context->link->getAdminLink('Admin' . $this->name) . '&ajax=1&action=',
            'carrier_disabled' => $this->l('Disabled'),
            'download_labels_url' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/tmp/',
            'modal_title' => $this->l('Please wait, file is being prepared'),
            'modal_title_ready' => $this->l('File is ready'),
        ]);
        $this->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin-label.js');
    }

    public function ajaxProcessGenerateLabel()
    {
        $return = [
            'hasError' => false,
            'errors' => '',
            'data' => '',
        ];

        try {
            $id_order = (int) Tools::getValue('order', 0);
            $order = new Order($id_order);

            if (
                $id_order <= 0 ||
                !Validate::isLoadedObject($order)
            ) {
                throw new GlsException(
                    $this->l('An error occured , please contact technical support.')
                );
            }

            Shop::setContext(
                Shop::CONTEXT_SHOP,
                $order->id_shop
            );

            $api = GlsApi::createInstance(
                Configuration::get('GLS_API_LOGIN'),
                Configuration::get('GLS_API_PWD'),
                $this->context->language->iso_code
            );
            $customer = new Customer($order->id_customer);

            $carriers_id_history = $this->module->getCarrierIdHistory();
            $id_carrier = $order->id_carrier;
            $old_id_carrier = $id_carrier;
            $old_is_gls_relais = false;
            $gls_service = Tools::getValue('gls_service', $id_carrier);

            if ((int) $gls_service != (int) $order->id_carrier) {
                $id_carrier = (int) $gls_service;
                if (isset($carriers_id_history['GLSRELAIS']) && in_array($old_id_carrier, $carriers_id_history['GLSRELAIS'])) {
                    $old_is_gls_relais = true;
                }
            }

            $is_gls_relais = false;
            $is_chezvousplus = false;
            $is_gls_13h = false;
            if (isset($carriers_id_history['GLSRELAIS']) && in_array($id_carrier, $carriers_id_history['GLSRELAIS'])) {
                $is_gls_relais = true;
            } elseif (isset($carriers_id_history['GLSCHEZVOUSPLUS']) && in_array($id_carrier, $carriers_id_history['GLSCHEZVOUSPLUS'])) {
                $is_chezvousplus = true;
            } elseif (isset($carriers_id_history['GLS13H']) && in_array($id_carrier, $carriers_id_history['GLS13H'])) {
                $is_gls_13h = true;
            }

            $customer_address = new Address($order->id_address_delivery);

            $customer_invoice_address = new Address($order->id_address_invoice);

            if ($old_is_gls_relais) {
                $customer_address = $customer_invoice_address;
            }

            if (Configuration::get('GLS_API_SHOP_RETURN_ADDRESS')) {
                $shop_address = $this->context->shop->getAddress();
            } else {
                $shop_address = new Address();
                $shop_address->company = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_NAME');
                $shop_address->id_country = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_COUNTRY');
                $shop_address->address1 = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS1');
                $shop_address->address2 = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_ADDRESS2');
                $shop_address->postcode = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_POSTCODE');
                $shop_address->city = Configuration::get('GLS_API_SHOP_RETURN_ADDRESS_CITY');
            }

            if (Tools::getValue('gls_label_single_type') == 'return' || Tools::getValue('gls_label_single_type') == 'return_shipment') {
                $delivery_adresses = $shop_address;
                if ($is_gls_relais) {
                    $customer_address = $customer_invoice_address;
                }
            } else {
                $delivery_adresses = $customer_address;
            }

            $delivery_country_iso = $this->getCountryIso($delivery_adresses);
            $customer_country_iso = $this->getCountryIso($customer_address);

            if ($delivery_country_iso == 'IE') {
            }

            $query = new DbQuery();
            $query->select('c.*')
                ->from('gls_cart_carrier', 'c')
                ->where('c.`id_customer` = ' . (int) $order->id_customer)
                ->where('c.`id_cart` = ' . (int) $order->id_cart);
            $cart_carrier_detail = Db::getInstance()->getRow($query);

            if (Tools::getValue('mobile')) {
                $customer_mobile = preg_replace('/[^0-9\+]/', '', Tools::getValue('mobile'));
            } elseif (!empty($cart_carrier_detail['customer_phone_mobile'])) {
                $customer_mobile = preg_replace('/[^0-9\+]/', '', $cart_carrier_detail['customer_phone_mobile']);
            } else {
                $customer_mobile = preg_replace('/[^0-9\+]/', '', $customer_address->phone_mobile);
            }

            if ($is_gls_relais
                && strpos(Tools::strtoupper($customer->email), 'MESSAGE.MANOMANO.COM') !== false
                && preg_match('/^(2500[[:alnum:]]{6})[[:space:]]-[[:space:]].*$/', $customer_address->address1, $matches)) {
                if (is_array($matches) && isset($matches[1])) {
                    $cart_carrier_detail['parcel_shop_id'] = $matches[1];
                }
            }

            $reference1 = Tools::substr(Tools::getValue('reference1'), 0, 20);
            $reference2 = Tools::substr(Tools::getValue('reference2'), 0, 20);

            $gls_api_customer_id = Configuration::get('GLS_API_CUSTOMER_ID');
            $gls_api_contact_id = Configuration::get('GLS_API_CONTACT_ID');
            $gls_api_label_format = Configuration::get('GLS_API_DELIVERY_LABEL_FORMAT');

            $data = [
                'shipperId' => $gls_api_customer_id . ' ' . $gls_api_contact_id,
                'references' => [$reference1, $reference2],
                'addresses' => [
                    'delivery' => $this->buildAddressData($delivery_adresses),
                ],
                'labelSize' => $gls_api_label_format,
            ];

            if (Tools::getValue('delivery_date')) {
                $data['shipmentDate'] = Tools::getValue('delivery_date');
            }

            if (Tools::getValue('incoterm')) {
                $data['incoterm'] = Tools::getValue('incoterm');
            }

            $additional_address_data = [
                'email' => $customer->email,
                'phone' => preg_replace('/[^0-9\+]/', '', $customer_address->phone),
                'mobile' => $customer_mobile,
            ];

            if (Tools::getValue('gls_label_single_type') == 'return' || Tools::getValue('gls_label_single_type') == 'return_shipment') {
                $company_anum = preg_replace('/[^[:alnum:]]/', '', $customer_address->company);
                if (!empty($customer_address->company) && !empty($company_anum)) {
                    $customer_name = Tools::strtoupper(nkmStripAccents($customer_address->company));
                } else {
                    $customer_name = Tools::strtoupper(nkmStripAccents($customer_address->firstname . ' ' . $customer_address->lastname));
                }

                $tab_adresse = nkmCutSentenceMulti($customer_address->address1, 35);

                $data['addresses']['pickup'] = [
                    'id' => $customer_address->id,
                    'name1' => Tools::substr($customer_name, 0, 35),
                    'street1' => Tools::strtoupper(nkmStripAccents($tab_adresse[0])),
                    'country' => $customer_country_iso,
                    'zipCode' => $customer_address->postcode,
                    'city' => Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->city)), 0, 35),
                    'email' => $additional_address_data['email'],
                    'phone' => $additional_address_data['phone'],
                    'mobile' => $additional_address_data['mobile'],
                ];

                if (isset($tab_adresse[1])) {
                    $data['addresses']['pickup']['name2'] = Tools::strtoupper(nkmStripAccents($tab_adresse[1]));
                    $data['addresses']['pickup']['name3'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->address2)), 0, 35);
                } else {
                    $data['addresses']['pickup']['name2'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->address2)), 0, 35);
                }
            } else {
                $data['addresses']['delivery']['id'] = $delivery_adresses->id;
                $data['addresses']['delivery']['email'] = $additional_address_data['email'];
                $data['addresses']['delivery']['phone'] = $additional_address_data['phone'];
                $data['addresses']['delivery']['mobile'] = $additional_address_data['mobile'];
            }

            if (isset($data['addresses']['delivery'])) {
                $data['addresses']['delivery'] = $this->formatData($data['addresses']['delivery']);
            }

            if (isset($data['addresses']['pickup'])) {
                $data['addresses']['pickup'] = $this->formatData($data['addresses']['pickup']);
            }

            $return_label = false;

            if (Tools::getValue('gls_label_single_type') == 'return' || Tools::getValue('gls_label_single_type') == 'return_shipment') {
                $return_label = true;
            }

            if (!$return_label) {
                $data['addresses']['delivery']['contact'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->firstname . ' ' . $customer_address->lastname)), 0, 35);
            }

            $parcels = [];
            $parcels_return = [];
            $total_weight = 0;
            foreach (Tools::getValue('weight') as $key => $value) {
                if (is_numeric($value) && !empty($value)) {
                    $tmp = [
                        'weight' => (float) str_replace(',', '.', $value),
                    ];
                    $total_weight += (float) str_replace(',', '.', $value);

                    if ($return_label) {
                        $service_detail = ['name' => 'shopreturnservice'];

                        if (Tools::getValue('gls_label_single_type') == 'return' || Tools::getValue('gls_label_single_type') == 'return_shipment') {
                            $shopreturn_infos = [];
                            $shopreturn_infos[] = ['name' => 'returnonly', 'value' => 'Y'];
                            $service_detail['infos'] = $shopreturn_infos;
                        } else {
                            array_push($parcels_return, $tmp);
                        }

                        $tmp['services'] = [];
                        array_push($tmp['services'], $service_detail);
                    } elseif ($is_gls_relais) {
                        $relais_infos = [];
                        $relais_infos[] = ['name' => 'parcelshopid', 'value' => $cart_carrier_detail['parcel_shop_id']];

                        $tmp['services'] = [];
                        array_push($tmp['services'], [
                            'name' => 'shopDeliveryService',
                            'infos' => $relais_infos,
                        ]);
                    } elseif ($is_chezvousplus) {
                        $tmp['services'] = [];
                        array_push($tmp['services'], [
                            'name' => 'flexDeliveryService',
                        ]);
                    } elseif ($is_gls_13h) {
                        $gls13h_infos = [];
                        $gls13h_infos[] = ['name' => 'deliverytime', 'value' => '13:00'];

                        $tmp['services'] = [];
                        array_push($tmp['services'], [
                            'name' => 'express',
                            'infos' => $gls13h_infos,
                        ]);
                    }

                    array_push($parcels, $tmp);
                } else {
                    $return['hasError'] = true;
                    $return['errors'] = sprintf($this->l('Wrong weight on package #%s'), $key + 1);
                    break;
                }
            }
            $data['parcels'] = $parcels;

            if (is_array($parcels_return) && count($parcels_return) > 0) {
                $data['returns'] = $parcels_return;
            }

            $additional_customer_address = $customer_address;
            if ($is_gls_relais) {
                $additional_customer_address = $customer_invoice_address;
            }

            $additional_customer_address = $this->buildAddressData($additional_customer_address);
            $additional_customer_address = $this->formatData($additional_customer_address);
            $additional_customer_address['email'] = $additional_address_data['email'];
            $additional_customer_address['phone'] = $additional_address_data['phone'];
            $additional_customer_address['mobile'] = $additional_address_data['mobile'];

            if (isset($data['addresses']['delivery']['contact'])) {
                $additional_customer_address['contact'] = $data['addresses']['delivery']['contact'];
            }

            $data['additional_temp_data'] = [
                'customer_address' => $additional_customer_address,
            ];

            if (!$return['hasError']) {
                $result = $api->post('shipments', $data);

                if ($result === false) {
                    $return['hasError'] = true;
                    $return['errors'] = '';
                    foreach ($api->error as $error) {
                        if (!empty($return['errors'])) {
                            $return['errors'] .= "\r\n";
                        }
                        if (is_array($error) && isset($error['message'])) {
                            $return['errors'] .= $error['message'] . ' - ' . $this->l('Error code:') . ' ' . $error['code'];
                        } else {
                            $return['errors'] .= $error;
                        }
                    }
                } else {
                    $id_order_carrier = $order->getIdOrderCarrier();
                    $old_tracking_number = $order->getWsShippingNumber();
                    $shippingNumbers = [];
                    if (!empty($old_tracking_number)) {
                        $shippingNumbers = explode(',', $old_tracking_number);
                    }

                    if (isset($result->parcels) && is_array($result->parcels) && count($result->parcels) > 0) {
                        foreach ($result->parcels as $p) {
                            $shippingNumbers[] = $p->trackId;
                        }

                        if (isset($result->returns) && is_array($result->returns) && count($result->returns) > 0) {
                            foreach ($result->returns as $p) {
                                $shippingNumbers[] = $p->trackId;
                            }
                        }

                        $tracking_number = implode(',', $shippingNumbers);

                        if (!isset($this->verifyTrackingUrl[$id_carrier])) {
                            if (Validate::isLoadedObject($carrier = new Carrier($id_carrier))) {
                                $this->verifyTrackingUrl[$id_carrier] = true;
                                if (empty($carrier->url)) {
                                    $carrier->url = pSQL(NkmGls::$trackingUrl);
                                    $carrier->update();
                                }
                            }
                        }

                        $order_carrier = new OrderCarrier($id_order_carrier);
                        if (!Validate::isLoadedObject($order_carrier)) {
                            $return['hasError'] = true;
                            $return['errors'] = $this->l('The order carrier ID is invalid.');
                        } else {
                            $old_id_carrier = $order_carrier->id_carrier;
                            $old_weight = (float) $order_carrier->weight;
                            if (!empty($id_carrier) && $old_id_carrier != $id_carrier) {
                                $order->id_carrier = (int) $id_carrier;
                                $order_carrier->id_carrier = (int) $id_carrier;
                                if (!empty($total_weight) && (float) $total_weight != $old_weight) {
                                    $order_carrier->weight = (float) $total_weight;
                                }
                                $order_carrier->update();

                                if ($old_is_gls_relais) {
                                    $order->id_address_delivery = $order->id_address_invoice;
                                }

                                $gls_product = $this->module->getGlsProductCode(
                                    (int) $id_carrier,
                                    $delivery_country_iso
                                );

                                $sql = 'UPDATE ' . _DB_PREFIX_ . 'gls_cart_carrier SET `customer_phone_mobile`=\'' . pSQL($customer_mobile) . '\', `id_carrier`=' . (int) $id_carrier . ', `gls_product`=\'' . pSQL($gls_product) . '\'';
                                $sql .= ',`parcel_shop_id` = NULL, `name` = NULL, `address1` = NULL, `address2` = NULL, `postcode` = NULL,
                                    `city` = NULL, `phone` = NULL, `phone_mobile` = NULL, `id_country` = NULL, `parcel_shop_working_day` = NULL';
                                $sql .= ' WHERE `id_customer`=' . (int) $order->id_customer . ' AND `id_cart`=' . (int) $order->id_cart;
                                Db::getInstance()->Execute($sql);
                            } elseif (!empty($total_weight) && (float) $total_weight != $old_weight) {
                                $order_carrier->weight = (float) $total_weight;
                                $order_carrier->update();
                            }

                            $order_carrier = new OrderCarrier($id_order_carrier);

                            if (property_exists($order, 'shipping_number')) {
                                $order->shipping_number = pSQL($tracking_number);
                                $order->update();
                            }

                            $order_carrier->tracking_number = pSQL($tracking_number);
                            if (!$order_carrier->update()) {
                                $return['hasError'] = true;
                                $return['errors'] = $this->l('The order carrier cannot be updated.');
                            } elseif (Tools::getValue('gls_label_single_type') != 'return' && Tools::getValue('gls_label_single_type') != 'return_shipment') {
                                if (!isset($gls_product)) {
                                    $gls_product = $this->module->getGlsProductCode(
                                        (int) $order->id_carrier,
                                        $delivery_country_iso
                                    );
                                }

                                foreach ($result->parcels as $k => $p) {
                                    if (isset($data['parcels'][$k])) {
                                        $glsLabel = new GlsLabelClass();
                                        $glsLabel->id_order = $order->id;
                                        $glsLabel->shipping_number = $p->trackId;
                                        $glsLabel->weight = $data['parcels'][$k]['weight'];
                                        $glsLabel->gls_product = $gls_product;
                                        $glsLabel->delivery_date = $data['shipmentDate'];
                                        $glsLabel->reference1 = $reference1;
                                        $glsLabel->reference2 = $reference2;
                                        $glsLabel->add();
                                    }
                                }
                            }
                        }
                    } else {
                        $return['hasError'] = true;
                        $return['errors'] = $this->l('An error occured , please contact technical support.');
                    }

                    if (!$return['hasError']) {
                        if (Tools::getValue('gls_label_single_type') == 'return' || Tools::getValue('gls_label_single_type') == 'shipment') {
                            $new_order_state = (int) Configuration::get('GLS_LABEL_SINGLE_NEW_ORDER_STATE');
                        } else {
                            $new_order_state = (int) Configuration::get('GLS_LABEL_NEW_ORDER_STATE');
                        }

                        if ($new_order_state > 0 && $new_order_state != (int) $order->current_state) {
                            $order->setCurrentState($new_order_state, (int) $this->context->employee->id);

                            if (Tools::getValue('gls_label_single_type') != 'return' && Tools::getValue('gls_label_single_type') != 'return_shipment') {
                                if (!$this->module->sendInTransitEmail($order_carrier, $order, $id_carrier)) {
                                    $return['hasError'] = false;
                                    $return['errors'] = $this->l('An error occurred while sending an email to the customer.');
                                }
                            }
                        }

                        if (
                            Tools::getValue('gls_label_single_type') == 'return'
                            && Configuration::get('GLS_API_SHOP_RETURN_EMAIL_ALERT')
                            && isset($result->location)
                            && !empty($result->location)
                            && isset($result->labels)
                            && is_array($result->labels)
                            && count($result->labels) > 0
                        ) {
                            $data = [
                                '{lastname}' => $customer->lastname,
                                '{firstname}' => $customer->firstname,
                                '{followup}' => $result->location,
                                '{order_name}' => $order->reference,
                            ];

                            $file_attachment = [];
                            $i = 0;
                            foreach ($result->labels as $label) {
                                ++$i;
                                $file_attachment[] = [
                                    'name' => $order->reference . '-return' . $i . '.pdf',
                                    'content' => base64_decode($label),
                                    'mime' => 'application/pdf',
                                ];
                            }

                            if (!Mail::Send(
                                (int) $customer->id_lang,
                                'gls_label_return',
                                $this->l('Your order return request has been accepted'),
                                $data,
                                $customer->email,
                                $customer->firstname . ' ' . $customer->lastname,
                                null,
                                null,
                                $file_attachment,
                                null,
                                $this->module->getLocalPath() . 'mails/'
                            )) {
                                $return['hasError'] = false;
                                $return['errors'] = $this->l('An error occurred while sending the return confirtmation email to the customer.');
                            }
                        }

                        $gls_log = new GlsLogClass();
                        $gls_log->log(sprintf($this->l('Delivery label(s) successfully generated for order %s'), $order->id));

                        $return['data'] = $result;
                    }
                }
            }

            if (Tools::getValue('local_print') && $return['hasError'] === false) {
                if (isset($return['data']->labels) && is_array($return['data']->labels)) {
                    $i = 0;
                    $dirname = _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . $this->tmpDirectory . DIRECTORY_SEPARATOR;
                    foreach ($return['data']->labels as $label) {
                        ++$i;
                        $pdf = fopen($dirname . 'order-' . $order->id . '-label-' . ($return_label ? 'return-' : '') . $i . '.pdf', 'w');
                        fwrite($pdf, base64_decode($label));
                        fclose($pdf);
                    }
                }
                $return['data'] = 'local_print';
            }
        } catch (Exception $e) {
            $return['hasError'] = true;
            $return['errors'] = $e->getMessage();
        }

        die(json_encode($return));

        return false;
    }

    public function ajaxProcessGenerateAllLabels()
    {
        $return = [
            'hasError' => false,
            'errors' => '',
        ];
        $ignoreFiles = [
            '.',
            '..',
            'index.php',
            '.gitkeep',
        ];

        if (Tools::getValue('prepareMerge')) {
            $dirname = _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . $this->tmpDirectory . DIRECTORY_SEPARATOR;
            foreach (scandir($dirname) as $file) {
                if (!in_array($file, $ignoreFiles, true)) {
                    Tools::deleteFile($dirname . $file);
                }
            }
        } elseif (Tools::getValue('mergePDF')) {
            $filename = 'orders-' . date('dmY-His') . '.pdf';

            $pdf = new \Jurosh\PDFMerge\PDFMerger();

            if (Configuration::get('GLS_API_DELIVERY_LABEL_FORMAT') == 'A5') {
                $orientation = 'horizontal';
            } else {
                $orientation = 'vertical';
            }

            $dirname = _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . $this->tmpDirectory . DIRECTORY_SEPARATOR;
            $i = 0;
            foreach (scandir($dirname) as $file) {
                if (!in_array($file, $ignoreFiles, true)) {
                    $pdf->addPDF($dirname . $file, 'all', $orientation);
                    ++$i;
                }
            }
            if ($i > 0) {
                $pdf->merge('file', $dirname . $filename);
                $return['data'] = ['pdf' => $filename];
            } else {
                $return['hasError'] = true;
            }
        }

        die(json_encode($return));
    }

    public function initModal()
    {
        $tpl = $this->createTemplate('modal.tpl');
        $tpl->assign([
            'gls_img_path' => __PS_BASE_URI__ . 'modules/' . $this->module->name . '/views/img/admin/',
        ]);

        parent::initModal();
        $this->modals[] = [
            'modal_id' => 'modalMergeLabels',
            'modal_class' => '',
            'modal_title' => $this->l('Please wait, file is being prepared'),
            'modal_content' => $tpl->fetch(),
        ];
    }

    private function formatData($address)
    {
        if (isset($address['zipCode'], $address['country'])) {
            switch ($address['country']) {
                case 'LV':
                case 'SI':
                    $address['zipCode'] = preg_replace('/[^0-9]/', '', $address['zipCode']);
                    break;
                default:
                    break;
            }
        }

        return $address;
    }

    private function getCountryIso(Address $address)
    {
        $country_iso = Country::getIsoById($address->id_country);

        if ($country_iso == 'COS') {
            $country_iso = 'FR';
        }

        return $country_iso;
    }

    private function buildAddressData(Address $address)
    {
        $tab_adresse = nkmCutSentenceMulti($address->address1, 35);

        $company_anum = preg_replace('/[^[:alnum:]]/', '', $address->company);
        if (!empty($address->company) && !empty($company_anum)) {
            $name1 = Tools::strtoupper(nkmStripAccents($address->company));
        } else {
            $name1 = Tools::strtoupper(nkmStripAccents($address->firstname . ' ' . $address->lastname));
        }

        $res = [
            'name1' => Tools::substr($name1, 0, 35),
            'street1' => Tools::strtoupper(nkmStripAccents($tab_adresse[0])),
            'country' => $this->getCountryIso($address),
            'zipCode' => $address->postcode,
            'city' => Tools::substr(Tools::strtoupper(nkmStripAccents($address->city)), 0, 35),
        ];

        if (isset($tab_adresse[1])) {
            $res['name2'] = Tools::strtoupper(nkmStripAccents($tab_adresse[1]));
            $res['name3'] = Tools::substr(Tools::strtoupper(nkmStripAccents($address->address2)), 0, 35);
        } else {
            $res['name2'] = Tools::substr(Tools::strtoupper(nkmStripAccents($address->address2)), 0, 35);
        }

        return $res;
    }
}
