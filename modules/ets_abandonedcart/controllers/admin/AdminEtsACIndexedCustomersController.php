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

require_once(dirname(__FILE__) . '/AdminEtsACController.php');

class AdminEtsACIndexedCustomersController extends AdminEtsACController
{
    public function __construct()
    {
        $this->table = 'ets_abancart_index_customer';
        $this->list_id = $this->table;
        $this->lang = false;
        $this->_orderBy = 'id_ets_abancart_index_customer';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;
        $this->allow_export = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->_select = '
            ar.*
            , IF(a.firstname !=\'\' AND a.lastname != \'\', CONCAT(a.firstname, \' \', a.lastname), NULL) as `customer_name`
            , rl.title as `reminder_name`
            , IF(last_date_order is NOT NULL, last_date_order, IF(last_login_time is NOT NULL, last_login_time, IF(newsletter_date_add is NOT NULL, newsletter_date_add, customer_date_add))) as date_to_send
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` rl ON (ar.id_ets_abancart_reminder = rl.id_ets_abancart_reminder AND rl.id_lang=' . (int)$this->context->language->id . ')
        ';

        $this->_where = 'AND a.id_shop = ' . (int)$this->context->shop->id;

        $this->fields_list = array(
            'id_ets_abancart_index_customer' => array(
                'title' => $this->l('ID', 'AdminEtsACIndexedCartsController'),
                'type' => 'int',
                'filter_key' => 'a!id_ets_abancart_index_customer',
                'class' => 'fixed-width-xs center',
            ),
            'reminder_name' => array(
                'title' => $this->l('Reminder', 'AdminEtsACIndexedCartsController'),
                'type' => 'text',
                'filter_key' => 'rl!title',
            ),
            'customer_name' => array(
                'title' => $this->l('Customer name', 'AdminEtsACIndexedCartsController'),
                'type' => 'text',
                'havingFilter' => true,
                'callback' => 'displayCustomerName',
            ),
            'email' => array(
                'title' => $this->l('Email', 'AdminEtsACIndexedCartsController'),
                'type' => 'text',
                'filter_key' => 'a!email',
                'callback' => 'displayCustomerName'
            ),
            'date_to_send' => array(
                'title' => $this->l('Date to send', 'AdminEtsACIndexedCartsController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'date_to_send',
                'havingFilter' => true,
                'class' => 'fixed-width-lg',
                'callback' => 'displayDateToSend'
            ),
            'customer_date_add' => array(
                'title' => $this->l('Customer added', 'AdminEtsACIndexedCartsController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!customer_date_add',
                'class' => 'fixed-width-lg',
            ),
            'last_login_time' => array(
                'title' => $this->l('Last visit time', 'AdminEtsACIndexedCartsController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!last_login_time',
                'class' => 'fixed-width-lg',
            ),
            'newsletter_date_add' => array(
                'title' => $this->l('Newsletter added', 'AdminEtsACIndexedCartsController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!newsletter_date_add',
                'class' => 'fixed-width-lg',
            ),
            'last_date_order' => array(
                'title' => $this->l('Last order added', 'AdminEtsACIndexedCartsController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!last_date_order',
                'class' => 'fixed-width-lg',
            )
        );
    }

    public function displayDateToSend($date_to_send, $tr)
    {
        if ($date_to_send !== '') {
            return date($this->context->language->date_format_full, strtotime($date_to_send) + (float)$tr['day'] * 86400 + (float)$tr['hour'] * 3600);
        }

        return $date_to_send;
    }

    public function displayCustomerName($customer_name, $tr)
    {
        if (!isset($tr['id_customer']) || !(int)$tr['id_customer'])
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

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);
    }
}