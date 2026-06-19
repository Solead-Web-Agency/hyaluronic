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

class AdminEtsACUnsubscribedController extends AdminEtsACFormController
{
    static $status_mail = [];

    public function __construct()
    {
        $this->table = 'ets_abancart_unsubscribers';
        $this->identifier = 'id_customer';
        $this->list_id = $this->table;
        $this->className = 'EtsAbancartUnsubscribers';
        $this->lang = false;
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;
        $this->allow_export = false;

        parent::__construct();

        $this->addRowAction('delete');

        $this->_select = '
            CONCAT(c.`firstname`, " ", c.`lastname`) `customer_name`
            , c.`email`
        ';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = a.id_customer)';

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where = 'AND c.id_shop = ' . (int)$this->context->shop->id;

        $this->fields_list = array(
            'id_customer' => array(
                'title' => $this->l('Customer ID', 'AdminEtsACUnSubscribedController'),
                'type' => 'int',
                'filter_key' => 'a!id_customer',
                'class' => 'fixed-width-xs center'
            ),
            'customer_name' => array(
                'title' => $this->l('Customer', 'AdminEtsACUnSubscribedController'),
                'type' => 'text',
                'filter_key' => 'customer_name',
                'callback' => 'displayCustomerName',
                'havingFilter' => true,
            ),
            'email' => array(
                'title' => $this->l('Email', 'AdminEtsACUnSubscribedController'),
                'type' => 'text',
                'filter_key' => 'c!email',
            ),
            'date_add' => array(
                'title' => $this->l('Date added', 'AdminEtsACUnSubscribedController'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add',
                'class' => 'fixed-width-lg'
            ),
        );
    }

    protected function getWhereClause()
    {
        if ($this->_filter)
            $this->_filter = preg_replace('/\s+AND\s+([a-z0-9A-Z]+)\.`(customer_name)`\s+LIKE\s+\'%(.+?)%\'/', ' AND ($1.`$2` LIKE \'%$3%\' OR $1.`id_customer`=\'$3\') ', $this->_filter);

        return parent::getWhereClause();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function displayCustomerName($customer_name, $tr)
    {
        if (!isset($tr['id_customer']) || (int)$tr['id_customer'] < 1 || trim($customer_name) == '')
            return null;
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
}