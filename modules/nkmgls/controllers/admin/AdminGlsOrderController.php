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

class AdminGlsOrderController extends ModuleAdminController
{
    private static $tab_lang = ['fr' => 'GLS'];

    public $order_state = [];

    public $importDirectory = null;
    public $exportDirectory = null;
    public $fromCron = false;

    public function __construct($fromCron = false)
    {
        $this->bootstrap = true;
        $this->display = 'view';

        if ($fromCron) {
            $this->module = new NkmGls();
            $this->context = Context::getContext();
            $this->fromCron = true;
        } else {
            parent::__construct();
        }

        $this->name = 'GlsOrder';

        $this->order_state = OrderState::getOrderStates($this->context->language->id);

        $this->importDirectory = 'import' . DIRECTORY_SEPARATOR . 'winexpe';
        $this->exportDirectory = 'export';
    }

    public static function installInBO()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGlsOrder';

        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            if (isset(self::$tab_lang[$lang['iso_code']])) {
                $tab->name[(int) $lang['id_lang']] = self::$tab_lang[$lang['iso_code']];
            } else {
                $tab->name[(int) $lang['id_lang']] = 'GLS';
            }
        }

        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = 'nkmgls';

        return $tab->add();
    }

    public static function removeFromBO()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminGlsOrder');
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
        $statuses = [];
        foreach ($this->order_state as $value) {
            if ($value['id_order_state'] != _PS_OS_CANCELED_ && $value['id_order_state'] != _PS_OS_ERROR_) {
                $statuses[] = ['id_option' => $value['id_order_state'], 'name' => $value['name'], 'val' => $value['id_order_state']];
            }
        }

        $new_statuses = $statuses;
        array_unshift($new_statuses, ['id_option' => '0', 'name' => $this->l('(No change)'), 'val' => '']);

        if ((bool) Tools::isSubmit('exportOrder') === true && Tools::getValue('exportOrderStep2')) {
            return $this->initFormExportWinexpeStep2();
        }

        return $this->initFormExportWinexpe($statuses, $new_statuses) . $this->initFormImportWinexpe($new_statuses);
    }

    public function initToolbarTitle()
    {
        $this->toolbar_title = $this->l('Export & import of GLS orders');
    }

    public function initFormExportWinexpe($statuses, $new_statuses)
    {
        $this->fields_value = $this->getConfigFormValues('export');

        $cronPath = _PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . $this->exportDirectory . DIRECTORY_SEPARATOR;
        if (Configuration::get('GLS_CUSTOM_EXPORT_PATH_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)
            && Configuration::get('GLS_CUSTOM_EXPORT_PATH_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id) != '') {
            $cronPath = Configuration::get('GLS_CUSTOM_EXPORT_PATH', null, $this->context->shop->id_shop_group, $this->context->shop->id);
        }

        $tpl = $this->createTemplate('cron_help.tpl');
        $tpl->assign([
            'cron_uri' => $this->context->link->getModuleLink('nkmgls', 'winexpe', ['secure_key' => Configuration::get('GLS_SECURE_KEY'), 'action' => 'export']),
            'cron_path' => $cronPath,
            'title' => sprintf($this->l('You can set a cron job that will %s using the following URL:'), $this->l('export orders')),
            'btn_title' => sprintf($this->l('Run the automatic %s now'), $this->l('export')),
            'title_path' => $this->l('Here is the path to the export folder:'),
        ]);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Export Orders'),
                'icon' => 'icon-upload',
            ],
            'description' => Tools::nl2br(
                $this->l('Export your GLS orders by choosing one or more order statuses and click on the export button.') .
                "\n" . $this->l('The file can be directly integrated on the software Winexpe by GLS.') .
                "\n" . $this->l('It\'s possibe to change automatically the order status after exportation.')
            ),
            'input' => [
                [
                    'type' => 'checkbox',
                    'label' => $this->l('Order statuses'),
                    'name' => 'GLS_EXPORT_ORDER_STATE',
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
                    'label' => $this->l('Change order status to'),
                    'name' => 'GLS_EXPORT_NEW_ORDER_STATE',
                    'require' => false,
                    'options' => [
                        'query' => $new_statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable automation'),
                    'name' => 'GLS_EXPORT_AUTOMATION',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'lang' => false,
                    'hint' => $this->l('The automation allows the integration of your GLS orders directly into the Winexpe software.'),
                ],
            ],
            'desc' => $tpl->fetch(),
            'buttons' => [
                0 => [
                    'type' => 'submit',
                    'title' => $this->l('Check & Export'),
                    'id' => 'exportOrderStep2',
                    'name' => 'exportOrderStep2',
                    'class' => 'pull-right',
                    'icon' => 'process-icon-next',
                    'js' => '$(this).val(\'1\')',
                ],
                1 => [
                    'type' => 'submit',
                    'title' => $this->l('Export now'),
                    'id' => 'exportOrder',
                    'name' => 'exportOrder',
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-upload',
                ],
                2 => [
                    'type' => 'submit',
                    'title' => $this->l('Save'),
                    'id' => 'saveExportOrder',
                    'name' => 'saveExportOrder',
                    'class' => 'pull-right',
                    'icon' => 'process-icon-save',
                    'js' => '$(this).val(\'1\')',
                ],
            ],
        ];

        $this->submit_action = 'exportOrder';
        $this->show_toolbar = false;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    public function ajaxProcessExportWinexpeStep2()
    {
        $return = $this->initFormExportWinexpeStep2();
        if (empty($this->errors)) {
            die(json_encode($return));
        } else {
            return false;
        }
    }

    public function initFormExportWinexpeStep2()
    {
        $export_order_state = [];
        if (Configuration::get('GLS_EXPORT_ORDER_STATE')) {
            $export_order_state = explode(',', Configuration::get('GLS_EXPORT_ORDER_STATE'));
        }

        if (is_array($export_order_state) && count($export_order_state) > 0) {
            foreach ($this->order_state as $status) {
                $this->statuses_array[$status['id_order_state']] = $status['name'];
            }

            $sql = new DbQuery();
            $sql->select('o.*, o.`id_order` AS `id`, CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`, osl.`name` AS `osname`, os.`color`, oc.`weight` as `order_weight`')
                ->from('orders', 'o')
                ->leftJoin('gls_cart_carrier', 'gls', 'gls.`id_cart` = o.`id_cart` AND gls.`id_customer` = o.`id_customer`')
                ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
                ->leftJoin('order_carrier', 'oc', 'o.`id_order` = oc.`id_order`')
                ->leftJoin('carrier', 'ca', 'o.`id_carrier` = ca.`id_carrier`')
                ->leftJoin('order_state', 'os', 'os.`id_order_state` = o.`current_state`')
                ->leftJoin('order_state_lang', 'osl', 'os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $this->context->language->id)
                ->where('ca.id_carrier IS NOT NULL')
                ->where('ca.`external_module_name` = \'nkmgls\'')
                ->where('o.current_state IN (' . Configuration::get('GLS_EXPORT_ORDER_STATE') . ')')
                ->orderBy('o.id_order ASC');

            if (Shop::isFeatureActive() && Shop::getContextShopID()) {
                $sql->where('o.id_shop = ' . Shop::getContextShopID());
            }

            $data_order = Db::getInstance()->ExecuteS($sql);

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
                    'align' => 'text-right',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add',
                ],
                'order_weight' => [
                    'title' => $this->trans('Weight', [], 'Admin.Global') . ' (kg)',
                    'type' => 'editable',
                    'filter_key' => 'oc!weight',
                    'filter_type' => 'float',
                    'order_key' => 'order_weight',
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
            $helper->title = $this->l('GLS orders to export');
            $helper->table = 'order';
            $helper->token = Tools::getAdminTokenLite('Admin' . $this->name);
            $helper->currentIndex = $this->context->link->getAdminLink('Admin' . $this->name);

            return $helper->generateList($data_order, $this->fields_list);
        } else {
            $this->errors[] = $this->l('Please select one or more order status.');
        }
    }

    public function initFormImportWinexpe($new_statuses)
    {
        $this->fields_value = $this->getConfigFormValues('import');

        $tpl = $this->createTemplate('cron_help.tpl');
        $tpl->assign([
            'cron_uri' => $this->context->link->getModuleLink('nkmgls', 'winexpe', ['secure_key' => Configuration::get('GLS_SECURE_KEY'), 'action' => 'import']),
            'cron_path' => _PS_MODULE_DIR_ . $this->module->name . DIRECTORY_SEPARATOR . $this->importDirectory . DIRECTORY_SEPARATOR,
            'title' => sprintf($this->l('You can set a cron job that will %s using the following URL:'), $this->l('import tracking numbers')),
            'btn_title' => sprintf($this->l('Run the automatic %s now'), $this->l('import')),
            'title_path' => $this->l('Here is the path to the import folder:'),
        ]);

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Import tracking numbers'),
                'icon' => 'icon-download',
            ],
            'description' => $this->l('Import the CSV file provided by the Winexpe software to update the tracking numbers and keep your customers informed about their order\'s delivery.'),
            'input' => [
                [
                    'type' => 'file',
                    'label' => $this->l('GLS File'),
                    'name' => 'winexpeFile',
                    'required' => true,
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Change order status to'),
                    'name' => 'GLS_IMPORT_NEW_ORDER_STATE',
                    'require' => false,
                    'options' => [
                        'query' => $new_statuses,
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Enable automation'),
                    'name' => 'GLS_IMPORT_AUTOMATION',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'lang' => false,
                    'hint' => $this->l('The automation allows the integration of tracking numbers in your GLS orders and automatically notify customers.'),
                ],
            ],
            'desc' => $tpl->fetch(),
            'buttons' => [0 => [
                'type' => 'submit',
                'title' => $this->l('Save'),
                'id' => 'saveImportOrder',
                'name' => 'saveImportOrder',
                'class' => 'pull-right',
                'icon' => 'process-icon-save',
                'js' => '$(this).val(\'1\')',
            ]],
            'submit' => [
                'title' => $this->l('Import now'),
                'id' => 'importOrder',
                'class' => 'btn btn-default pull-right',
                'icon' => 'process-icon-download',
            ],
        ];

        $this->submit_action = 'importOrder';
        $this->show_toolbar = false;
        $this->show_form_cancel_button = false;

        return parent::renderForm();
    }

    public function postProcess()
    {
        if ((bool) Tools::isSubmit('exportOrder') === true) {
            $form_values = $this->getConfigFormValues('export');
            foreach ($form_values as $key => $value) {
                if (strpos($key, 'GLS_EXPORT_ORDER_STATE') === false) {
                    Configuration::updateValue($key, trim($value));
                }
            }

            $order_state_selected = [];
            foreach ($this->order_state as $value) {
                if (Tools::getValue('GLS_EXPORT_ORDER_STATE_' . $value['id_order_state'])) {
                    $order_state_selected[] = $value['id_order_state'];
                }
            }
            Configuration::updateValue('GLS_EXPORT_ORDER_STATE', implode(',', array_map('intval', $order_state_selected)));

            if (!Tools::getValue('ajax')) {
                if (!Tools::getValue('saveExportOrder') && !Tools::getValue('exportOrderStep2')) {
                    $this->exportWinexpe();
                } else {
                    $this->confirmations[] = $this->l('Configuration successfully updated');
                }
            }
        } elseif ((bool) Tools::isSubmit('exportOrderSelected')) {
            if (Tools::getValue('orderBox') && is_array(Tools::getValue('orderBox')) && count(Tools::getValue('orderBox')) > 0) {
                $this->exportWinexpe(Tools::getValue('orderBox'));
            } else {
                $this->errors[] = $this->l('Please select at least one order.');
            }
        } elseif ((bool) Tools::isSubmit('importOrder') === true) {
            $form_values = $this->getConfigFormValues('import');
            foreach ($form_values as $key => $value) {
                Configuration::updateValue($key, trim($value));
            }

            if (!Tools::getValue('saveImportOrder')) {
                $this->importWinexpe();
            } else {
                $this->confirmations[] = $this->l('Configuration successfully updated');
            }
        }

        return parent::postProcess();
    }

    public function getConfigFormValues($type)
    {
        $importAutomation = Tools::getValue('GLS_IMPORT_AUTOMATION', Configuration::get('GLS_IMPORT_AUTOMATION'));
        if (empty($importAutomation)) {
            $importAutomation = 0;
        }
        $exportAutomation = Tools::getValue('GLS_EXPORT_AUTOMATION', Configuration::get('GLS_EXPORT_AUTOMATION'));
        if (empty($exportAutomation)) {
            $exportAutomation = 0;
        }

        if ($type == 'import') {
            return [
                'GLS_IMPORT_NEW_ORDER_STATE' => Tools::getValue('GLS_IMPORT_NEW_ORDER_STATE', Configuration::get('GLS_IMPORT_NEW_ORDER_STATE')),
                'GLS_IMPORT_AUTOMATION' => $importAutomation,
            ];
        } elseif ($type == 'export') {
            $fieds = [
                'GLS_EXPORT_NEW_ORDER_STATE' => Tools::getValue('GLS_EXPORT_NEW_ORDER_STATE', Configuration::get('GLS_EXPORT_NEW_ORDER_STATE')),
                'GLS_EXPORT_AUTOMATION' => $exportAutomation,
            ];
            foreach ($this->order_state as $value) {
                if (in_array($value['id_order_state'], explode(',', Configuration::get('GLS_EXPORT_ORDER_STATE')))) {
                    $fieds['GLS_EXPORT_ORDER_STATE_' . $value['id_order_state']] = true;
                } else {
                    $fieds['GLS_EXPORT_ORDER_STATE_' . $value['id_order_state']] = false;
                }
            }

            return $fieds;
        }
    }

    public function exportWinexpe($_orders = [])
    {
        $export_order_state = [];
        if (Configuration::get('GLS_EXPORT_ORDER_STATE')) {
            $export_order_state = explode(',', Configuration::get('GLS_EXPORT_ORDER_STATE'));
        }

        if (Shop::isFeatureActive()) {
            $new_order_status = Configuration::get('GLS_EXPORT_NEW_ORDER_STATE', null, $this->context->shop->id_shop_group, $this->context->shop->id);
        } else {
            $new_order_status = Configuration::get('GLS_EXPORT_NEW_ORDER_STATE');
        }

        if ((is_array($export_order_state) && count($export_order_state) > 0) || count($_orders) > 0) {
            $sql = new DbQuery();
            $sql->select('o.*, gls.gls_product, gls.parcel_shop_id, gls.customer_phone_mobile, oc.`weight` as `order_weight`, c.`email` as `customer_email`')
                ->from('orders', 'o')
                ->leftJoin('gls_cart_carrier', 'gls', 'gls.`id_cart` = o.`id_cart` AND gls.`id_customer` = o.`id_customer`')
                ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
                ->leftJoin('order_carrier', 'oc', 'o.`id_order` = oc.`id_order`')
                ->leftJoin('carrier', 'ca', 'o.`id_carrier` = ca.`id_carrier`')
                ->where('ca.id_carrier IS NOT NULL')
                ->where('ca.`external_module_name` = \'nkmgls\'');

            if (Shop::isFeatureActive() && Shop::getContextShopID()) {
                $sql->where('o.id_shop = ' . Shop::getContextShopID());
            }

            if (is_array($_orders) && count($_orders) > 0) {
                $sql->where('o.`id_order` IN (' . implode(',', array_map('intval', $_orders)) . ')');
            } else {
                $sql->where('o.current_state IN (' . pSQL(Configuration::get('GLS_EXPORT_ORDER_STATE')) . ')');
            }

            $data_order = Db::getInstance()->ExecuteS($sql);

            if ($data_order) {
                $export_header = [
                    'ORDERID' => 'ORDERID',
                    'ORDERNAME' => 'ORDERNAME',
                    'PRODUCTNO' => 'PRODUCTNO',
                    'ORDERWEIGHTOT' => 'ORDERWEIGHTOT',
                    'CONSID' => 'CONSID',
                    'CONTACT' => 'CONTACT',
                    'CONTACTMAIL' => 'CONTACTMAIL',
                    'CONTACTMOBILE' => 'CONTACTMOBILE',
                    'CONTACTPHONE' => 'CONTACTPHONE',
                    'STREET1' => 'STREET1',
                    'STREET2' => 'STREET2',
                    'STREET3' => 'STREET3',
                    'COUNTRYCODE' => 'COUNTRYCODE',
                    'CITY' => 'CITY',
                    'ZIPCODE' => 'ZIPCODE',
                    'REFPR' => 'REFPR', ];

                $csv_file = new NkmCsv();
                $csv_file->createTemplate($export_header);
                $csv_file->csvDelimeter = ';';

                foreach ($data_order as $row) {
                    if (Shop::isFeatureActive()) {
                        Shop::setContext(Shop::CONTEXT_SHOP, $row['id_shop']);
                        $this->context->shop->id_shop_group = Shop::getContextShopGroupID();
                        $this->context->shop->id = $row['id_shop'];
                    }

                    $customer_delivery_address = new Address($row['id_address_delivery']);
                    $delivery_country_iso = Country::getIsoById($customer_delivery_address->id_country);

                    $is_gls_relais_product = (
                        $row['gls_product'] == '17' ||
                        $row['gls_product'] == '26'
                    );

                    if (strpos(Tools::strtoupper($row['customer_email']), 'MESSAGE.MANOMANO.COM') !== false) {
                        $row['gls_product'] = $this->module->getGlsProductCode(
                            $row['id_carrier'],
                            $delivery_country_iso
                        );
                        if (
                            $is_gls_relais_product &&
                            preg_match('/^(2500[[:alnum:]]{6})[[:space:]]-[[:space:]].*$/', $customer_delivery_address->address1, $matches)) {
                            if (is_array($matches) && isset($matches[1])) {
                                $row['parcel_shop_id'] = $matches[1];
                            }
                        }
                    }

                    if (
                        !empty($row['parcel_shop_id']) &&
                        $is_gls_relais_product
                    ) {
                        $customer_address = new Address($row['id_address_invoice']);
                    } else {
                        $customer_address = $customer_delivery_address;
                    }

                    $customer_country_iso = Country::getIsoById($customer_address->id_country);

                    $csv_line = [];

                    $csv_line['ORDERID'] = '';
                    if (Configuration::get('GLS_ORDER_PREFIX_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                        if (Configuration::get('GLS_ORDER_PREFIX', null, $this->context->shop->id_shop_group, $this->context->shop->id) != '') {
                            $csv_line['ORDERID'] = Configuration::get('GLS_ORDER_PREFIX', null, $this->context->shop->id_shop_group, $this->context->shop->id) . '-';
                        } else {
                            $csv_line['ORDERID'] = $row['id_shop'] . '-';
                        }
                    }
                    if (Configuration::get('GLS_EXPORT_ORDER_REFERENCE_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                        $csv_line['ORDERID'] .= $row['reference'];
                    } else {
                        $csv_line['ORDERID'] .= $row['id_order'];
                    }

                    $company_anum = preg_replace('/[^[:alnum:]]/', '', $customer_address->company);
                    if (!empty($customer_address->company) && !empty($company_anum)) {
                        $csv_line['ORDERNAME'] = Tools::strtoupper(nkmStripAccents($customer_address->company));
                    } else {
                        $csv_line['ORDERNAME'] = Tools::strtoupper(nkmStripAccents($customer_address->firstname . ' ' . $customer_address->lastname));
                    }

                    if (!empty($row['gls_product'])) {
                        $csv_line['PRODUCTNO'] = $row['gls_product'];
                    } else {
                        $csv_line['PRODUCTNO'] = $this->module->getGlsProductCode(
                            $row['id_carrier'],
                            $customer_country_iso
                        );
                    }

                    if (Tools::getValue('order_weight_' . $row['id_order']) !== false) {
                        $csv_line['ORDERWEIGHTOT'] = Tools::ps_round(str_replace(',', '.', Tools::getValue('order_weight_' . $row['id_order'])), 3);
                    } else {
                        $csv_line['ORDERWEIGHTOT'] = Tools::ps_round($row['order_weight'], 3);
                    }

                    $csv_line['CONSID'] = $row['id_customer'];

                    $csv_line['CONTACT'] = Tools::strtoupper(nkmStripAccents($customer_delivery_address->firstname . ' ' . $customer_delivery_address->lastname));

                    $csv_line['CONTACTMAIL'] = $row['customer_email'];

                    if (!empty($row['customer_phone_mobile'])) {
                        $csv_line['CONTACTMOBILE'] = preg_replace('/[^0-9\+]/', '', $row['customer_phone_mobile']);
                    } else {
                        $csv_line['CONTACTMOBILE'] = preg_replace('/[^0-9\+]/', '', $customer_address->phone_mobile);
                    }

                    $contactPhone = preg_replace('/[^0-9\+]/', '', $customer_address->phone);
                    if (empty($contactPhone)) {
                        $contactPhone = $csv_line['CONTACTMOBILE'];
                    }
                    $csv_line['CONTACTPHONE'] = $contactPhone;

                    if (empty($csv_line['CONTACTMOBILE'])) {
                        $csv_line['CONTACTMOBILE'] = $csv_line['CONTACTPHONE'];
                    }

                    $tab_adresse = nkmCutSentenceMulti($customer_address->address1, 35);
                    $csv_line['STREET1'] = Tools::strtoupper(nkmStripAccents($tab_adresse[0]));

                    if (isset($tab_adresse[1])) {
                        $csv_line['STREET2'] = Tools::strtoupper(nkmStripAccents($tab_adresse[1]));
                        $csv_line['STREET3'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->address2)), 0, 35);
                    } else {
                        $csv_line['STREET2'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->address2)), 0, 35);
                        $csv_line['STREET3'] = '';
                    }

                    $csv_line['COUNTRYCODE'] = $customer_country_iso;
                    $csv_line['CITY'] = Tools::substr(Tools::strtoupper(nkmStripAccents($customer_address->city)), 0, 35);
                    $csv_line['ZIPCODE'] = $customer_address->postcode;
                    if (!empty($row['parcel_shop_id'])) {
                        $csv_line['REFPR'] = $row['parcel_shop_id'];
                    } else {
                        $csv_line['REFPR'] = '';
                    }
                    $csv_file->addEntry($csv_line);

                    if (Shop::isFeatureActive()) {
                        Shop::setContext(Shop::CONTEXT_ALL);
                    }

                    if ((int) Tools::getValue('GLS_EXPORT_NEW_ORDER_STATE', $new_order_status) > 0
                        && (int) Tools::getValue('GLS_EXPORT_NEW_ORDER_STATE', $new_order_status) != (int) $row['current_state']
                    ) {
                        $order = new Order($row['id_order']);
                        $order->setCurrentState(
                            Tools::getValue('GLS_EXPORT_NEW_ORDER_STATE', $new_order_status),
                            (int) $this->context->employee->id
                        );
                    }
                }

                if ($this->fromCron) {
                    $directory = _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . $this->exportDirectory . DIRECTORY_SEPARATOR;
                    if (Configuration::get('GLS_CUSTOM_EXPORT_PATH_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)
                        && Configuration::get('GLS_CUSTOM_EXPORT_PATH_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id) != '') {
                        $directory = $exportPath = Configuration::get('GLS_CUSTOM_EXPORT_PATH', null, $this->context->shop->id_shop_group, $this->context->shop->id);
                        if (Tools::substr($exportPath, -1, 1) != DIRECTORY_SEPARATOR) {
                            $directory .= DIRECTORY_SEPARATOR;
                        }
                    }

                    $filename = Tools::replaceAccentedChars(Tools::getShopDomain());
                    $filename = 'GlsCmd_' . preg_replace('/[^a-zA-Z0-9]/', '_', $filename) . '_' . NkmUdate('YmdHisu') . '.csv';
                    file_put_contents($directory . $filename, $csv_file->buildDoc());

                    return true;
                } else {
                    $filename = Tools::replaceAccentedChars(Tools::getShopDomain());
                    $filename = 'GlsCmd_' . preg_replace('/[^a-zA-Z0-9]/', '_', $filename) . '_' . NkmUdate('YmdHisu') . '.csv';
                    header('Content-type: text/csv');
                    header('Content-Type: application/force-download; charset=ISO-8859-1');
                    header('Cache-Control: no-store, no-cache');
                    header('Content-disposition: attachment; filename="' . $filename . '"');

                    exit($csv_file->buildDoc());
                }
            } else {
                $this->errors[] = $this->l('No order has been found.');
            }
        } else {
            $this->errors[] = $this->l('Please select one or more order status.');
        }
    }

    public function importWinexpe()
    {
        $files = [];
        if ($this->fromCron) {
            $directory = _PS_MODULE_DIR_ . DIRECTORY_SEPARATOR . $this->module->name . DIRECTORY_SEPARATOR . $this->importDirectory . DIRECTORY_SEPARATOR;
            $files = self::getCsvFiles($directory);
        } else {
            $extension = ['.txt', '.csv'];
            $file_uploaded = Tools::fileAttachment('winexpeFile', false);

            if (!is_null($file_uploaded)
                && !empty($file_uploaded['tmp_name'])
                && $file_uploaded['error'] == 0
                && !empty($file_uploaded['name']) && in_array(Tools::strtolower(Tools::substr($file_uploaded['name'], -4)), $extension)
            ) {
                $files[] = $file_uploaded['tmp_name'];
            }
        }

        if (count($files) > 0) {
            $csv_file = new NkmCSVReader();

            foreach ($files as $file_path) {
                $csv_content = $csv_file->parse_file($file_path, false, true);

                if (!$csv_content) {
                    $this->errors[] = $this->l('File content empty.');
                } else {
                    $verifyTrackingUrl = [];

                    $sendInTransitEmail = [];

                    $orderImported = [];

                    $orderPrefix = Configuration::get('GLS_ORDER_PREFIX', null, $this->context->shop->id_shop_group, $this->context->shop->id);

                    foreach ($csv_content as $line) {
                        if (Configuration::get('GLS_ORDER_PREFIX_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                            $order_id = explode('-', $line[4]);
                            if (count($order_id) == 2) {
                                if ($orderPrefix != '' && $order_id[0] != $orderPrefix) {
                                    continue;
                                } elseif (!$this->fromCron && $orderPrefix == '' && $order_id[0] != $this->context->shop->id) {
                                    continue;
                                }
                                $order_id = $order_id[1];
                            } else {
                                continue;
                            }
                        } else {
                            $order_id = $line[4];
                        }

                        if (Configuration::get('GLS_EXPORT_ORDER_REFERENCE_ENABLE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                            $order = Order::getByReference($order_id)->getFirst();
                        } else {
                            $order = new Order($order_id);
                        }

                        if (Validate::isLoadedObject($order)) {
                            $old_tracking_number = $order->getWsShippingNumber();
                            $line[17] = trim($line[17]);

                            if (empty($line[17]) || (!empty($line[17])
                                && (empty($old_tracking_number) || !empty($old_tracking_number) && strpos($old_tracking_number, $line[17]) === false))
                            ) {
                                if (!empty($line[17])) {
                                    $id_order_carrier = $order->getIdOrderCarrier();
                                    $id_carrier = $order->id_carrier;
                                    if (!empty($old_tracking_number)) {
                                        $shippingNumbers = explode(',', $old_tracking_number);
                                        $shippingNumbers[] = $line[17];
                                        $tracking_number = implode(',', $shippingNumbers);
                                    } else {
                                        $tracking_number = $line[17];
                                    }

                                    if (!isset($verifyTrackingUrl[$id_carrier])) {
                                        if (Validate::isLoadedObject($carrier = new Carrier($id_carrier))) {
                                            $verifyTrackingUrl[$id_carrier] = true;
                                            if (empty($carrier->url)) {
                                                $carrier->url = pSQL(NkmGls::$trackingUrl);
                                                $carrier->update();
                                            }
                                        }
                                    }

                                    $order_carrier = new OrderCarrier($id_order_carrier);
                                    if (!Validate::isLoadedObject($order_carrier)) {
                                        $this->errors[] = $this->l('The order carrier ID is invalid.');
                                        continue;
                                    } elseif (!empty($tracking_number) && !Validate::isTrackingNumber($tracking_number)) {
                                        $this->errors[] = $this->l('The tracking number is incorrect.');
                                        continue;
                                    } else {
                                        if (property_exists($order, 'shipping_number')) {
                                            $order->shipping_number = pSQL($tracking_number);
                                            $order->update();
                                        }

                                        $order_carrier->tracking_number = pSQL($tracking_number);
                                        if ($order_carrier->update()) {
                                            if ($old_tracking_number != $tracking_number) {
                                                $sendInTransitEmail[$order->id] = $order;
                                            }
                                        } else {
                                            $this->errors[] = $this->l('The order carrier cannot be updated.');
                                            continue;
                                        }
                                    }
                                }

                                if ((int) Tools::getValue('GLS_IMPORT_NEW_ORDER_STATE', Configuration::get('GLS_IMPORT_NEW_ORDER_STATE')) > 0
                                    && (int) Tools::getValue('GLS_IMPORT_NEW_ORDER_STATE', Configuration::get('GLS_IMPORT_NEW_ORDER_STATE')) != (int) $order->current_state
                                ) {
                                    $order->setCurrentState(
                                        Tools::getValue('GLS_IMPORT_NEW_ORDER_STATE', Configuration::get('GLS_IMPORT_NEW_ORDER_STATE')),
                                        $this->fromCron ? 0 : (int) $this->context->employee->id
                                    );
                                }
                            } else {
                                $this->errors[] = sprintf($this->l('Tracking number is empty or already integrated for order %s.'), $line[4]);
                                continue;
                            }

                            $orderImported[$order->id] = $order->id;
                        }
                    }

                    if (count($orderImported) > 0) {
                        foreach ($sendInTransitEmail as $value) {
                            $id_order_carrier = $value->getIdOrderCarrier();
                            $order_carrier = new OrderCarrier($id_order_carrier);

                            if (!$this->module->sendInTransitEmail($order_carrier, $value)) {
                                $this->errors[] = $this->l('An error occurred while sending an email to the customer.');
                            }
                        }

                        $this->confirmations[] = sprintf($this->l('%d Order(s) successfully processed.'), count($orderImported));
                        $gls_log = new GlsLogClass();
                        $gls_log->log(sprintf($this->l('Tracking numbers successfully imported for order(s) %s'), implode(', ', array_map('intval', $orderImported))));
                    }
                }

                if ($this->fromCron) {
                    unlink($file_path);
                }
            }
        } elseif (!$this->fromCron) {
            $this->errors[] = $this->l('File not found. Make sure you upload a file on the form or on the synchronisation folder.');
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/admin-order.css');

        Media::addJsDef([
            'back_url' => $this->context->link->getAdminLink('Admin' . $this->name),
            'ajax_uri' => $this->context->link->getAdminLink('Admin' . $this->name) . '&ajax=1&action=exportWinexpeStep2',
        ]);
        $this->addJs(_PS_MODULE_DIR_ . $this->module->name . '/views/js/admin-order.js');
    }

    protected static function getCsvFiles($directory)
    {
        $files = [];

        $dir = opendir($directory);
        while (false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $fileInfo = pathinfo($directory . $file);
            if (is_file($directory . $file) && Tools::strtoupper($fileInfo['extension']) == 'CSV') {
                $files[] = $directory . $file;
            }
        }
        closedir($dir);

        return $files;
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($this->fromCron) {
            return $string;
        } else {
            return parent::l($string, $class, $addslashes, $htmlentities);
        }
    }

    public function initModal()
    {
        $tpl = $this->createTemplate('modal.tpl');

        parent::initModal();
        $this->modals[] = [
            'modal_id' => 'modalExportOrderStep2',
            'modal_class' => 'modal-lg',
            'modal_title' => null,
            'modal_content' => $tpl->fetch(),
        ];
    }
}
