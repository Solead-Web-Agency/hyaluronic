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

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class AdminGlsPackingListController extends ModuleAdminController
{
    private static $tab_lang = ['fr' => 'GLS bordereau'];

    public $order_state = [];
    public $tmpDirectory = 'tmp';

    public $verifyTrackingUrl = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        $this->module = 'nkmgls';
        $this->context = Context::getContext();

        parent::__construct();

        $this->name = 'GlsPackingList';

        $this->order_state = OrderState::getOrderStates($this->context->language->id);
    }

    public static function installInBO()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGlsPackingList';

        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            if (isset(self::$tab_lang[$lang['iso_code']])) {
                $tab->name[(int) $lang['id_lang']] = self::$tab_lang[$lang['iso_code']];
            } else {
                $tab->name[(int) $lang['id_lang']] = 'GLS packing list';
            }
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = 'nkmgls';

        if ($tab->add()) {
            $idTabBefore = (int) Tab::getIdFromClassName('AdminGlsLabel');
            $tabBefore = new Tab($idTabBefore);
            $newPosition = $tabBefore->position + 1;
            if ($newPosition < $tab->position) {
                $tab->updatePosition(0, $newPosition);
            } elseif ($newPosition > $tab->position) {
                $tab->updatePosition(1, $newPosition);
            }

            return true;
        }

        return false;
    }

    public static function removeFromBO()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGlsPackingList');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            if (validate::isLoadedObject($tab)) {
                return $tab->delete();
            } else {
                return false;
            }
        }

        return true;
    }

    public function renderView()
    {
        $init_form = false;
        if ((empty($this->errors) && ((bool) Tools::isSubmit('generatePackingListStep2') === true))
            || (!empty($this->errors) && ((bool) Tools::isSubmit('printPackingList') === true))
        ) {
            $init_form = $this->initFormStep2();
        }

        if (!$init_form) {
            $statuses = [];
            foreach ($this->order_state as $value) {
                if ((int) $value['id_order_state'] > 0) {
                    $statuses[] = ['id_option' => $value['id_order_state'], 'name' => $value['name'], 'val' => $value['id_order_state']];
                }
            }

            $init_form = $this->initForm($statuses);
        }

        return $init_form;
    }

    public function getConfigFormValues()
    {
        $fieds = [
            'GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER' => date('Y-m-d'),
            'GLS_PACKING_LIST_ORDER_DATE_TO_FILTER' => date('Y-m-d'),
        ];

        foreach ($this->order_state as $value) {
            if (in_array($value['id_order_state'], explode(',', Configuration::get('GLS_PACKING_LIST_ORDER_STATE_FILTER')))) {
                $fieds['GLS_PACKING_LIST_ORDER_STATE_FILTER_' . $value['id_order_state']] = true;
            } else {
                $fieds['GLS_PACKING_LIST_ORDER_STATE_FILTER_' . $value['id_order_state']] = false;
            }
        }

        return $fieds;
    }

    public function initForm($statuses)
    {
        $this->fields_value = $this->getConfigFormValues();
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Packing list printing'),
                'icon' => 'icon-print',
            ],
            'description' => $this->l('Generate your GLS packing list'),
            'input' => [
                [
                    'type' => 'date',
                    'label' => $this->l('From'),
                    'name' => 'GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER',
                    'maxlength' => 10,
                    'required' => false,
                    'hint' => $this->l('Format: 2014-12-31 (inclusive).'),
                ],
                [
                    'type' => 'date',
                    'label' => $this->l('To'),
                    'name' => 'GLS_PACKING_LIST_ORDER_DATE_TO_FILTER',
                    'maxlength' => 10,
                    'required' => false,
                    'hint' => $this->l('Format: 2015-12-31 (inclusive).'),
                ],
                [
                    'type' => 'checkbox',
                    'label' => $this->l('Order statuses'),
                    'name' => 'GLS_PACKING_LIST_ORDER_STATE_FILTER',
                    'multiple' => true,
                    'required' => true,
                    'values' => [
                        'query' => $statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
            ],
            'buttons' => [
                0 => [
                    'type' => 'submit',
                    'title' => $this->l('Next'),
                    'id' => 'generatePackingListStep2',
                    'name' => 'generatePackingListStep2',
                    'class' => 'pull-right',
                    'icon' => 'process-icon-next',
                    'js' => '$(this).val(\'1\')',
                ],
            ],
        ];
        $this->submit_action = 'generatePackingList';
        $this->show_toolbar = false;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    private function getPackages()
    {
        $sql = new DbQuery();
        $sql->select('gl.*,
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            ca.`name` AS `caname`,
            o.`date_add` AS `order_date`,
            o.`reference`
            ')
            ->from('gls_label', 'gl')
            ->innerJoin('orders', 'o', 'gl.id_order=o.id_order')
            ->leftJoin('gls_cart_carrier', 'gls', 'gls.`id_cart` = o.`id_cart` AND gls.`id_customer` = o.`id_customer`')
            ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
            ->leftJoin('order_carrier', 'oc', 'o.`id_order` = oc.`id_order`')
            ->leftJoin('carrier', 'ca', 'o.`id_carrier` = ca.`id_carrier`')
            ->leftJoin('order_state', 'os', 'os.`id_order_state` = o.`current_state`')
            ->leftJoin('order_state_lang', 'osl', 'os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id)
            ->orderBy('gl.id_order ASC, gl.id_gls_label ASC');

        if (Shop::isFeatureActive() && Shop::getContextShopID()) {
            $sql->where('o.id_shop = ' . Shop::getContextShopID());
        }

        $sql->where('o.current_state IN (' . Configuration::get('GLS_PACKING_LIST_ORDER_STATE_FILTER') . ')');

        if (Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER') != '' && Validate::isDate(Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER'))) {
            $sql->where('gl.`date_add` >= \'' . pSQL(Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_FROM_FILTER')) . '\'');
        }
        if (Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_TO_FILTER') != '' && Validate::isDate(Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_TO_FILTER'))) {
            $sql->where('DATE_ADD(gl.`date_add`, INTERVAL -1 DAY) <= \'' . pSQL(Tools::getValue('GLS_PACKING_LIST_ORDER_DATE_TO_FILTER')) . '\'');
        }

        return Db::getInstance()->ExecuteS($sql);
    }

    public function initFormStep2()
    {
        $order_state = [];
        if (Configuration::get('GLS_PACKING_LIST_ORDER_STATE_FILTER')) {
            $order_state = explode(',', Configuration::get('GLS_PACKING_LIST_ORDER_STATE_FILTER'));
        }

        if (is_array($order_state) && count($order_state) > 0) {
            foreach ($this->order_state as $status) {
                $this->statuses_array[$status['id_order_state']] = $status['name'];
            }

            $data_package = $this->getPackages();
            if (!empty($data_package)) {
                $this->fields_list = [
                    'shipping_number' => [
                        'title' => $this->l('Tracking ID'),
                    ],
                    'weight' => [
                        'title' => $this->l('Package weight'),
                    ],
                    'caname' => [
                        'title' => $this->l('Carrier'),
                    ],
                    'date_add' => [
                        'title' => $this->l('Package date'),
                        'type' => 'datetime',
                        'filter_key' => 'gl!date_add',
                    ],
                    'id_order' => [
                        'title' => $this->l('Order ID'),
                        'align' => 'text-center',
                        'class' => 'fixed-width-xs',
                    ],
                    'reference' => [
                        'title' => $this->l('Order Reference'),
                    ],
                    'customer' => [
                        'title' => $this->l('Customer'),
                        'havingFilter' => true,
                    ],
                    'osname' => [
                        'title' => $this->l('Status'),
                        'type' => 'select',
                        'color' => 'color',
                        'list' => $this->statuses_array,
                        'filter_key' => 'os!id_order_state',
                        'filter_type' => 'int',
                        'order_key' => 'osname',
                    ],
                    'order_date' => [
                        'title' => $this->l('Order date'),
                        'type' => 'datetime',
                        'filter_key' => 'o!date_add',
                    ],
                ];

                $helper = new HelperList();
                $helper->module = $this->module;
                $helper->identifier = 'id_gls_label';
                $helper->shopLinkType = '';
                $helper->simple_header = true;
                $helper->show_toolbar = true;
                $helper->bulk_actions = [
                    'divider' => [
                        'text' => 'divider',
                    ],
                ];
                $helper->force_show_bulk_actions = true;
                $helper->toolbar_btn = [
                    'print' => [
                        'desc' => $this->l('Print'),
                        'href' => $this->context->link->getAdminLink('Admin' . $this->name),
                    ],
                    'back' => [
                        'href' => $this->context->link->getAdminLink('Admin' . $this->name),
                        'desc' => $this->l('Cancel'),
                    ],
                ];
                $helper->no_link = true;
                $helper->title = $this->l('Packages');
                $helper->table = 'gls_label';
                $helper->token = Tools::getAdminTokenLite('Admin' . $this->name);
                $helper->currentIndex = $this->context->link->getAdminLink('Admin' . $this->name);

                return $helper->generateList($data_package, $this->fields_list);
            }
        }

        $this->errors[] = $this->l('No packages has been found, please check your search filters.');
    }

    public function initToolbarTitle()
    {
        $this->toolbar_title = $this->l('GLS packing list');
    }

    public function postProcess()
    {
        if ((bool) Tools::isSubmit('generatePackingListStep2') === true) {
            $form_values = $this->getConfigFormValues();
            foreach ($form_values as $key => $value) {
                if (strpos($key, 'GLS_PACKING_LIST_ORDER_STATE_FILTER') === false) {
                    Configuration::updateValue($key, trim($value));
                }
            }

            $order_state_selected = [];
            foreach ($this->order_state as $value) {
                if (Tools::getValue('GLS_PACKING_LIST_ORDER_STATE_FILTER_' . $value['id_order_state'])) {
                    $order_state_selected[] = $value['id_order_state'];
                }
            }
            Configuration::updateValue('GLS_PACKING_LIST_ORDER_STATE_FILTER', implode(',', array_map('intval', $order_state_selected)));

            if (count($order_state_selected) <= 0) {
                $this->errors[] = $this->l('Please select one or more order status.');
            }
        } elseif ((bool) Tools::isSubmit('printPackingList') === true) {
            if (!Tools::getIsset('gls_labelBox') || (
                Tools::getIsset('gls_labelBox') && (
                    !is_array(Tools::getValue('gls_labelBox'))
                    || (is_array(Tools::getValue('gls_labelBox')) && count(Tools::getValue('gls_labelBox')) <= 0)))
            ) {
                $this->errors[] = $this->l('Please select at least one package.');
            } else {
                $packages = [];

                $sql = new DbQuery();
                $sql->select('gl.*,
                    o.`reference` as `order_reference`,
                    o.`date_add` AS `order_date`,
                    CONCAT(c.`firstname`, \' \', c.`lastname`) AS `customer`,
                    a.address1,
                    a.address2,
                    a.postcode,
                    a.city,
                    co.`iso_code`
                    ')
                    ->from('gls_label', 'gl')
                    ->innerJoin('orders', 'o', 'gl.id_order=o.id_order')
                    ->leftJoin('gls_cart_carrier', 'gls', 'gls.`id_cart` = o.`id_cart` AND gls.`id_customer` = o.`id_customer`')
                    ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
                    ->leftJoin('address', 'a', 'a.`id_address` = o.`id_address_delivery`')
                    ->leftJoin('country', 'co', 'a.`id_country` = co.`id_country`')
                    ->orderBy('gl.id_order ASC, gl.id_gls_label ASC');

                if (Tools::getValue('gls_labelBox')) {
                    $sql->where('gl.`id_gls_label` IN (' . implode(',', array_map('intval', Tools::getValue('gls_labelBox'))) . ')');
                }

                if (Shop::isFeatureActive() && Shop::getContextShopID()) {
                    $sql->where('o.id_shop = ' . Shop::getContextShopID());
                }

                $packages = Db::getInstance()->ExecuteS($sql);

                $pdf = new PDF(
                    [$packages],
                    'GlsPackingList',
                    $this->context->smarty,
                    'L'
                );
                $pdf->render('I');
            }
        }

        return parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
    }
}
