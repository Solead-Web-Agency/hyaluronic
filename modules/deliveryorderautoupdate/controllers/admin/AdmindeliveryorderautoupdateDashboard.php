<?php
/**
* 2007-2018 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2018 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/../../deliveryorderautoupdate.php');
require_once(dirname(__FILE__).'/../../classes/emailHelper.php');
class AdmindeliveryorderautoupdateDashboardController extends ModuleAdminController
{
    const DEFAULT_LANG = 'EN';
    public function __construct()
    {
        $this->name = 'deliveryorderautoupdate';
        $this->tab = 'front_office_features';
        $this->bootstrap = true;
        $this->lang = true;
        $this->context = Context::getContext();
        $this->secure_key = Tools::hash($this->name);
        parent::__construct();
        $this->url = EmailHelper::getUrl();
        $this->v17 = preg_match_all('/^1.7/', _PS_VERSION_);
        // Db::getInstance()->execute('DELETE FROM ps_hl_tracking_history');
    }
/*     public function initPageHeaderToolbar()
    {
        $config_url = $this->context->link->getAdminLink('AdminModules', true).'&configure='
        .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

        $this->page_header_toolbar_btn['cogs'] = array(
            'short' => 'go_config',
            'href' => $config_url,
            'desc' => $this->l('Configuration'),
            'confirm' => 1
        );
        $this->page_header_toolbar_btn['modules-list'] = array(
            'short' => 'go_config',
            'target' => '_blank',
            'href' => 'https://addons.prestashop.com/en/2_community-developer?contributor=351842',
            'desc' => $this->l('View our modules'),
            'confirm' => 1
        );
        parent::initPageHeaderToolbar();
    } */
    public function checkModuleKey()
    {
        $module = Module::getInstanceByName('deliveryorderautoupdate');
        $module_key = $module->module_key;
        if (!$module_key) {
            $installed_date = new DateTime(Configuration::get('HL_TC_INSTALL_DATE'));
            $current_date = new DateTime();
            $diff = $installed_date->diff($current_date);
            $days = $diff->format('%a');
            if ($days > 15) {
                return false;
            }
        }
        return true;
    }
    public function renderList()
    {
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/swal.js');
        $this->tpl_folder = 'deliveryorderautoupdateform/';
        if (!$this->checkModuleKey()) {
            $tpl = $this->createTemplate('trial_expire.tpl');
            $tpl->assign(array(
                'lang' => Language::getIsoById($this->context->employee->id_lang)
            ));
            return $tpl->fetch();
        }
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/style.css', 'all');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/header.css', 'all');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/stepBackoffice.css', 'all');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/daterangepicker.css');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/dateinput.css');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/autocomplete.css');
        $this->context->controller->addCSS($this->url.'modules/deliveryorderautoupdate/views/css/slider.css');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/autocomplete.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/searchinput.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/module_conf.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/admin.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/scrollLoad.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/tab_event.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/moment.min.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/daterangepicker.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/date_filter.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/delay_filter.js');
        $this->context->controller->addJS($this->url.'modules/deliveryorderautoupdate/views/js/slider.js');
        return $this->renderAddForm();
    }
    public function renderAddForm()
    {
        $this->multiple_fieldsets = true;
        $this->fields_form = array(
            'progress' => array(
                'form' => array(
                    'input' => array(
                        array(
                            'type' => 'shipments_info',
                            'name' => 'active',
                        ),
                    ),
                ),
            ),
            // 'delivered' => array(
            //     'form' => array(
            //         'input' => array(
            //             array(
            //                 'type' => 'loading',
            //                 'name' => 'loading',
            //             ),
            //         ),
            //     ),
            // ),
            'return' => array(
                'form' => array(
                    'input' => array(
                        array(
                            'type' => 'loading_return',
                            'name' => 'loading_return',
                        ),
                    ),
                ),
            ),
            'issue' => array(
                'form' => array(
                    'input' => array(
                        array(
                            'type' => 'loading_issue',
                            'name' => 'loading_issue',
                        ),
                    ),
                ),
            ),

        );
        $this->fields_value = $this->getFieldsValues();
        return adminController::renderForm();
    }
    private function getStatus()
    {
        $context = Context::getContext();
        $helper = new EmailHelper();
        $shopContext = $context->cookie->shopContext;
        $querycurrent_state = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM'));
        $state_exclude = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_EXCLUDE'));
        $date_ = Configuration::get('DELIVERY_ORDER_DATE');
        $id_shop = (int)$helper->getIdShop($shopContext, $context->shop->id);
        $sql = 'SELECT cs.id_status as event_code
        FROM '._DB_PREFIX_.'orders o
        INNER JOIN '._DB_PREFIX_.'order_carrier oc ON o.id_order = oc.id_order
        INNER JOIN '._DB_PREFIX_.'order_history oh ON o.id_order = oh.id_order
        LEFT JOIN '._DB_PREFIX_.'hl_tracking_current_status cs ON oc.id_order_carrier = cs.id_order_carrier
        WHERE '.($id_shop ? 'o.id_shop IN ('.(int)$id_shop.') AND ' : '').
        ($querycurrent_state?'oh.id_order_state IN ('.$querycurrent_state.') AND ':'').
        ($state_exclude?'o.current_state NOT IN ('.$state_exclude.') AND ':'').
        ' '.($date_ ? 'o.date_add >="'.pSQL($date_).'" ' : '' ).'
        GROUP BY cs.id_status';
        $statuses = Db::getInstance()->executeS($sql);
        return array_map(function ($s) {
            return $s['event_code'];
        }, $statuses);
    }
    public function getFieldsValues()
    {
        $prefix = _DB_PREFIX_;
        $carrier = array();
        $fields = array();
        // $date_ = pSQL(Configuration::get('DELIVERY_ORDER_DATE'));
        $link = new Link();
        $helper = new EmailHelper();
        $delivery_order_status_from = pSQL(Configuration::get('DELIVERY_ORDER_STATUS_FROM'));
        // $shopContext = Context::getContext()->cookie->shopContext;
        // $id_shop = (int)$helper->getIdShop($shopContext, $this->context->shop->id);
        $carrier_2 = Db::getInstance()->executeS(
            "SELECT id_carrier, name
            FROM {$prefix}carrier
            WHERE deleted = 0 ORDER BY active DESC, name ASC "
        );
        $allCarriers = Db::getInstance()->executeS(
            "SELECT id_carrier, name
            FROM {$prefix}carrier"
        );
        $carrier = Db::getInstance()->executeS('SELECT id, name FROM '._DB_PREFIX_.'hl_carrier ORDER BY id');
        $connector_return = Db::getInstance()->executeS(
            'SELECT c.id, c.name FROM '._DB_PREFIX_.'hl_carrier c
            INNER JOIN '._DB_PREFIX_.'hl_tracking_return tr ON c.id = tr.id_connector
            GROUP BY c.id ORDER BY c.id'
        );
        $state_return = Db::getInstance()->executeS(
            'SELECT orsl.id_order_return_state, orsl.name as state_name
            FROM `'._DB_PREFIX_.'hl_tracking_return` tr
            LEFT JOIN '._DB_PREFIX_.'order_return ort ON tr.id_order_return=ort.id_order_return
            LEFT JOIN '._DB_PREFIX_.'order_return_state_lang orsl ON ort.state=orsl.id_order_return_state
            AND orsl.id_lang='.$this->context->language->id.'
			WHERE ort.id_order_return is not null
            GROUP BY ort.state
            ORDER BY orsl.id_order_return_state'
        );

        $path_issue = _PS_MODULE_DIR_.'deliveryorderautoupdate/views/xml/issues.xml';
        $xml_issue = json_decode(
            json_encode(simplexml_load_file($path_issue, 'SimpleXMLElement', LIBXML_NOCDATA)),
            true
        );
        $array_issue = array();
        $array_issue_status = array();
        $iso_code = $this->context->language->iso_code;
        foreach ($xml_issue['issues']['issue'] as $issue) {
            $array_issue[] = array(
                'id' => $issue['id_issue'],
                'name' => isset($issue[$iso_code])?$issue[$iso_code]:$issue[Tools::strtolower(self::DEFAULT_LANG)],
            );
        }
        foreach ($xml_issue['statuses']['status'] as $status) {
            $array_issue_status[] = array(
                'id' => $status['id_status'],
                'name' => isset($status[$iso_code])?$status[$iso_code]:$status[Tools::strtolower(self::DEFAULT_LANG)],
            );
        }
        // $sql ="SELECT hlc.id, hlc.name, o.id_order
        // FROM {$prefix}orders o
        // INNER JOIN {$prefix}order_carrier oc ON o.id_order=oc.id_order
        // LEFT JOIN {$prefix}carrier c ON oc.id_carrier = c.id_carrier
        // LEFT JOIN {$prefix}hl_carrier_matching hlcm ON c.id_reference=hlcm.id_carrier_ps
        // LEFT JOIN {$prefix}hl_carrier hlc on hlcm.id_carrier_hl=hlc.id
        // WHERE o.id_shop = 1
        // GROUP BY hlc.id";
        // $carrier = Db::getInstance()->executeS($sql);

        $shipping_status = array();
        $history_status =$this->getStatus();

        date_default_timezone_set('Europe/Paris');
        header('Content-Type: text/html; charset=utf-8');

        $file_webcarrier = _PS_MODULE_DIR_.'deliveryorderautoupdate/statuses.xml';
        $webxml_crr = json_decode(
            json_encode(simplexml_load_file($file_webcarrier, 'SimpleXMLElement', LIBXML_NOCDATA))
        );
        foreach ($history_status as $s) {
            foreach ($webxml_crr->status as $step) {
                if ($step->id_status == $s) {
                    $iso_code = Tools::strtoupper($this->context->language->iso_code);
                    if (!isset($step->$iso_code)) {
                        $iso_code = self::DEFAULT_LANG;
                    }
                    if ((!array_key_exists($step->$iso_code, $shipping_status)) && ($step->id_status != 1)) {
                        $shipping_status[$step->id_status] = $step->$iso_code;
                    }
                    break;
                }
            }
        }
        $date_from = Configuration::get('DELIVERY_ORDER_DATE');
        $statuses = array();
        foreach ($webxml_crr->status as $status) {
            $statuses[$status->id_status] = $status;
        }
        // $defaultStatus = $helper->getDefaultStatus($webxml_crr);
        $filter = array(
            'date' => array(
                'key' => 'range',
                'range' => array(
                    'start' => date('Y-m-d', strtotime($date_from)),
                    'end' => date('Y-m-d')
                )
            ),
            'status' => 'not_delivered'
        );
        $carrierStat = $helper->getCarrierStat($filter);
        $deliveryStatus = $helper->getDeliveryStatus($filter);
        $orders = $helper->listOrder(0, $filter);
        $overview = $helper->getOverview($filter);
        $slider = $helper->getSliderValue($filter);
        $emp = new Language($this->context->employee->id_lang);
        $date_format = EmailHelper::convertDate($emp->date_format_lite);
        $total = $helper->getTotalOrder($filter);
        $itemsPerPage = 20;
        $pagenb = ceil($total/$itemsPerPage)-1;
        $status = Db::getInstance()->executeS(
            'SELECT name
            FROM '._DB_PREFIX_.'order_state_lang
            WHERE id_lang = '.(int)$this->context->language->id.
            ($delivery_order_status_from?' AND id_order_state IN ('.$delivery_order_status_from.')':'').'
            GROUP BY id_order_state'
        );
        $emp_lang = Language::getIsoById($this->context->employee->id_lang);
        $fields['DELIVERY_ORDER_DATE'] = Configuration::get('DELIVERY_ORDER_DATE');
        $fields['url'] = $this->url;
        $langs = count(Language::getLanguages());
        $meta = Meta::getMetaByPage('module-deliveryorderautoupdate-orders', $this->context->employee->id_lang);
        $fields['front_url'] = Deliveryorderautoupdate::returnFrontUrl().
        ($langs > 1?$emp_lang.'/':'').$meta['url_rewrite'];
        $fields['ajax_url'] = $link->getAdminLink('AdmindeliveryorderautoupdateAjax');
        $fields['secure_key'] = $this->secure_key;
        $fields['config_url'] = $link->getAdminLink('AdminModules')
        .'&configure=deliveryorderautoupdate&module_name=deliveryorderautoupdate';
        $fields['state'] = $webxml_crr->status;
        $fields['orders'] = $orders;
        $fields['pagenb'] = $pagenb;
        $fields['DL_HISTORY_TAB_ACTIVE'] = 'event';//Configuration::get('DL_HISTORY_TAB_ACTIVE');
        $fields['status'] = $status;
        $fields['statuses'] = $statuses;
        $fields['DELIVERY_EVENT_CODE_MAIL_ADMIN'] = explode(',', Configuration::get('DELIVERY_EVENT_CODE_MAIL_ADMIN'));
        $fields['carrier'] = $carrier;
        $fields['issue'] = $array_issue;
        $fields['issue_status'] = $array_issue_status;
        $fields['connector_return'] = $connector_return;
        $fields['state_return'] = $state_return;
        $fields['allCarriers'] = $allCarriers;
        $fields['carrier_2'] = $carrier_2;
        $fields['shipping_status'] = $shipping_status;
        $fields['onlyshow'] = Tools::getValue('onlyshow');
        $fields['token'] = Tools::getValue('token');
        $fields['total_order'] = null;
        $fields['error_message'] = null;
        $fields['orderLink'] = $link->getAdminLink('AdminOrders');
        $fields['guide'] = Deliveryorderautoupdate::returnFrontUrl().'modules/'.$this->name.'/guide/';
        $fields['shop_active'] = (int)Configuration::get('PS_SHOP_ENABLE');
        $fields['v17'] = $this->v17;
        $fields['id_lang'] = Tools::strtoupper($this->context->language->iso_code);
        $fields['overview'] = $overview;
        $fields['slider'] = $slider;
        $fields['carrierStat'] = $carrierStat;
        $fields['deliveryStatus'] = $deliveryStatus;
        $fields['date_from'] = $date_from;
        $fields['date_format'] = $date_format;
        $fields['updatecheck'] = $helper->updateCheck();
        $fields['translate'] = $this->module->getTranslation();
        return $fields;
    }
}
