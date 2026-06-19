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

class AdminEtsACMailLogController extends AdminEtsACFormController
{
    static $status_mail = [];

    public function __construct()
    {
        $this->table = 'ets_abancart_mail_log';
        $this->identifier = 'id_ets_abancart_email_queue';
        $this->list_id = $this->table;
        $this->lang = false;
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;
        $this->allow_export = false;

        parent::__construct();

        $this->addRowAction('view');

        $this->_select = 'o.id_order';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = a.id_cart)';

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where = 'AND a.id_shop = ' . (int)$this->context->shop->id;

        if (!self::$status_mail) {
            self::$status_mail = [
                EtsAbancartMail::SEND_MAIL_FAILED => $this->l('Failed', 'AdminEtsACMailLogController'),
                EtsAbancartMail::SEND_MAIL_DELIVERED => $this->l('Delivered', 'AdminEtsACMailLogController'),
                EtsAbancartMail::SEND_MAIL_TIMEOUT => $this->l('Timeout', 'AdminEtsACMailLogController')
            ];
        }

        $this->fields_list = array(
            'id_cart' => array(
                'title' => $this->l('Cart ID', 'AdminEtsACMailQueueController'),
                'type' => 'int',
                'filter_key' => 'a!id_cart',
                'class' => 'fixed-width-xs center',
                'callback' => 'displayIdCart'
            ),
            'id_order' => array(
                'title' => $this->l('Order ID', 'AdminEtsACMailQueueController'),
                'type' => 'int',
                'filter_key' => 'o!id_order',
                'class' => 'fixed-width-xs center',
                'callback' => 'displayIdOrder'
            ),
            'subject' => array(
                'title' => $this->l('Title', 'AdminEtsACMailQueueController'),
                'type' => 'text',
                'filter_key' => 'a!subject',
            ),
            'content' => array(
                'title' => $this->l('Content', 'AdminEtsACMailQueueController'),
                'type' => 'text',
                'filter_key' => 'a!content',
                'callback' => 'displayContent'
            ),
            'customer_name' => array(
                'title' => $this->l('Customer', 'AdminEtsACMailQueueController'),
                'type' => 'text',
                'filter_key' => 'a!customer_name',
                'callback' => 'displayCustomerName'
            ),
            'email' => array(
                'title' => $this->l('Email', 'AdminEtsACMailQueueController'),
                'type' => 'text',
                'filter_key' => 'a!email',
            ),
            'sent_time' => array(
                'title' => $this->l('Time sent', 'AdminEtsACMailQueueController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!sent_time',
                'class' => 'fixed-width-lg',
            ),
            'status' => array(
                'title' => $this->l('Status', 'AdminEtsACMailQueueController'),
                'type' => 'select',
                'list' => self::$status_mail,
                'align' => 'center',
                'filter_key' => 'a!status',
                'callback' => 'displayStatus',
                'class' => 'fixed-width-lg',
            ),
        );
    }

    public function renderList()
    {
        return $this->renderButtonClean() . parent::renderList();
    }

    public function processCleanLog()
    {
        if (!EtsAbancartTracking::cleanDisplayLog())
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

    public function displayIdCart($id_cart)
    {
        if ($id_cart) {
            $this->context->smarty->assign([
                'btn' => [
                    'href' => $this->context->link->getAdminLink('AdminCarts', true, [], ['viewcart' => '', 'id_cart' => $id_cart]),
                    'target' => '_bank',
                    'title' => $id_cart,
                    'class' => 'ets_ab_cart_link',
                ]
            ]);

            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
        }
    }

    public function displayIdOrder($id_order)
    {
        if ($id_order) {
            $this->context->smarty->assign([
                'btn' => [
                    'href' => $this->context->link->getAdminLink('AdminOrders', true, version_compare(_PS_VERSION_, '1.7.7.0', '>=') ? ['route' => 'admin_orders_view', 'orderId' => $id_order] : [], ['vieworder' => '', 'id_order' => $id_order]),
                    'target' => '_bank',
                    'title' => $id_order,
                    'class' => 'ets_ab_order_link',
                ]
            ]);

            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
        }
    }

    public function ajaxProcessRenderView()
    {
        die(json_encode([
            'html' => $this->renderView(),
        ]));
    }

    public function renderView()
    {
        $id = Tools::getValue($this->identifier);
        if ($id < 1)
            return '';
        $object = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_abancart_mail_log` WHERE `id_ets_abancart_email_queue`=' . (int)$id);
        if (isset($object['content']) && $object['content'] !== '') {
            $idShop = isset($object['id_shop']) && $object['id_shop'] ? (int)$object['id_shop'] : $this->context->shop->id;
            $idLang = isset($object['id_lang']) && $object['id_lang'] ? (int)$object['id_lang'] : $this->context->language->id;
            $logo = '';
            if (false !== Configuration::get('PS_LOGO_MAIL') && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
                $logo = _PS_IMG_ . Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
            } elseif (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
                $logo = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
            }
            $shop_url = $this->context->link->getPageLink('index', true, $idLang, null, false, $idShop);
            $object['content'] = str_replace(['{shop_name}', '{shop_logo}', '{shop_url}'], [Configuration::get('PS_SHOP_NAME'), $logo, $shop_url], $object['content']);
        }
        $this->context->smarty->assign([
            'object' => $object,
        ]);
        return parent::renderView();
    }

    public function displayStatus($status)
    {
        if (!$status || !isset(self::$status_mail[$status]))
            return null;
        $this->context->smarty->assign([
            'status' => $status,
            'title' => self::$status_mail[$status],
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . '/views/templates/admin/status-mail.tpl');
    }

    protected function getWhereClause()
    {
        if ($this->_filter)
            $this->_filter = preg_replace('/\s+AND\s+([a-z0-9A-Z]+)\.`(customer_name)`\s+LIKE\s+\'%(.+?)%\'/', ' AND ($1.`$2` LIKE \'%$3%\' OR $1.`id_customer`=\'$3\') ', $this->_filter);

        return parent::getWhereClause();
    }

    public function displayContent($html)
    {
        if (trim($html) == '')
            return null;

        $this->context->smarty->assign([
            'html_strip_tags' => $html,
        ]);
        return $this->createTemplate('html.tpl')->fetch();
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