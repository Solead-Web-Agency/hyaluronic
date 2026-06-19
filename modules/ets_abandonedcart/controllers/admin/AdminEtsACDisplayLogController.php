<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__) . '/AdminEtsACFormController.php');

class AdminEtsACDisplayLogController extends AdminEtsACFormController
{
    public function __construct()
    {
        $this->table = 'ets_abancart_display_log';
        $this->identifier = 'id_ets_abancart_display_log';
        $this->list_id = $this->table;
        $this->lang = false;
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;
        $this->allow_export = false;

        parent::__construct();

        $this->_select = 'IF(a.id_ets_abancart_reminder > 0, acl.`name`, "' . pSQL($this->l('Leaving website campaign', 'AdminEtsACDisplayLogController')) . '") `campaign`
            , IF(a.id_ets_abancart_reminder > 0, arl.`title`, "' . pSQL($this->l('Leaving website reminder', 'AdminEtsACDisplayLogController')) . '") `reminder`
            , IF(a.id_customer > 0, a.customer_name, CONCAT("#", a.id_guest, " (' . pSQL($this->l('Guest', 'AdminEtsACDisplayLogController')) . ')")) `customer`
            , cr.code `voucher_code`
        ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (a.id_ets_abancart_reminder = ar.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl ON (acl.id_ets_abancart_campaign = ar.id_ets_abancart_campaign AND acl.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (a.id_cart_rule = cr.id_cart_rule)
        ';

        //$this->addRowAction('view');

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where = 'AND a.id_shop = ' . (int)$this->context->shop->id;

        $this->fields_list = array(
            'id_ets_abancart_display_log' => array(
                'title' => $this->l('ID', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'a!id_ets_abancart_display_log',
                'havingFilter' => true,
                'class' => 'fixed-width-xs center'
            ),
            'campaign' => array(
                'title' => $this->l('Campaign', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'campaign',
                'havingFilter' => true,
            ),
            'reminder' => array(
                'title' => $this->l('Reminder', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'reminder',
                'havingFilter' => true,
            ),
            'customer' => array(
                'title' => $this->l('Customer', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'customer',
                'havingFilter' => true,
                'callback' => 'displayCustomerName'
            ),
            'email' => array(
                'title' => $this->l('Email', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'a!email',
            ),
            'voucher_code' => array(
                'title' => $this->l('Discount', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'voucher_code',
                'havingFilter' => true,
                'class' => 'fixed-width-lg center',
            ),
            'display_time' => array(
                'title' => $this->l('Display times', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'a!display_time',
                'class' => 'fixed-width-xs center',
            ),
            'closed_time' => array(
                'title' => $this->l('Closed times', 'AdminEtsACDisplayLogController'),
                'type' => 'text',
                'filter_key' => 'a!closed_time',
                'class' => 'fixed-width-xs center',
            ),
            'last_display_time' => array(
                'title' => $this->l('Last display time', 'AdminEtsACDisplayLogController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!last_display_time',
                'class' => 'fixed-width-lg',
            )
        );

        $this->_conf[1] = $this->l('Clean log successfully');
    }

    protected function getWhereClause()
    {
        if ($this->_filter) {
            $this->_filter = preg_replace('/\s+AND\s+([a-z0-9A-Z]+)\.`(campaign|reminder)`\s+LIKE\s+\'%(.+?)%\'/', ' AND ($1.`$2` LIKE \'%$3%\' OR $1.`id_ets_abancart_$2`=\'$3\') ', $this->_filter);
            $this->_filter = preg_replace('/\s+AND\s+([a-z0-9A-Z]+)\.`(customer)`\s+LIKE\s+\'%(.+?)%\'/', ' AND ($1.`$2` LIKE \'%$3%\' OR a.`id_customer`=\'$3\' OR OR a.`id_guest`=\'$3\') ', $this->_filter);
        }

        return parent::getWhereClause();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function displayCustomerName($customer_name, $tr)
    {
        if (trim($customer_name) == '' || empty($tr['id_customer']) && empty($tr['id_guest']))
            return null;
        if (empty($tr['id_customer']) && $tr['id_guest'] > 0)
            return $customer_name;
        $this->context->smarty->assign([
            'btn' => [
                'href' => $this->context->link->getAdminLink('AdminCustomers', true, $this->module->is17 ? ['route' => 'admin_customers_view', 'customerId' => (int)$tr['id_customer']] : [], ['viewcustomer' => '', 'id_customer' => (int)$tr['id_customer']]),
                'target' => '_bank',
                'title' => $customer_name,
                'class' => 'ets_ab_customer_link',
            ]
        ]);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
    }

    public function renderList()
    {
        return $this->renderButtonClean() . parent::renderList();
    }

    public function processCleanLog()
    {
        if (!EtsAbancartDisplayTracking::cleanDisplayLog())
            $this->errors[] = $this->l('Clean log failed!', 'AdminEtsACDisplayLogController');
        if (!$this->errors) {
            $this->redirect_after = self::$currentIndex . '&conf=1&token=' . $this->token;
        }
    }

    public function renderButtonClean()
    {
        $this->context->smarty->assign([
            'href' => self::$currentIndex . '&action=cleanLog&token=' . $this->token,
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/btn-clean-log.tpl');
    }
}