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

class AdminEtsACCartController extends AdminEtsACFormController
{
    /**
     * @var Ets_abandonedcart
     */
    public $module;

    public function __construct()
    {
        $this->table = 'cart';
        $this->className = 'Cart';
        $this->list_id = $this->table;
        $this->show_form_cancel_button = false;
        $this->_redirect = false;
        $this->list_no_link = true;

        $this->addRowAction('sendmail');
        $this->addRowAction('viewcart');
        $this->addRowAction('reminderlog');
        if(Module::isEnabled('ets_trackingcustomer'))
        {
            $this->addRowAction('session');
        }
        $this->allow_export = false;
        $this->_orderBy = 'a.id_cart';
        $this->_orderWay = 'DESC';

        parent::__construct();

        $this->tpl_folder = 'common/';

        //, ca.name carrier
        $this->_select = '
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) `customer_name`
            , c.id_customer
            , c.email
            , NULL as `reminders`
            , a.id_cart `total`
            , o.id_order
            , IF (IFNULL(o.id_order, \'' . pSQL($this->l('Non ordered', 'AdminEtsACCartController')) . '\') = \'' . pSQL($this->l('Non ordered', 'AdminEtsACCartController')) . '\', IF(TIME_TO_SEC(TIMEDIFF(\'' . pSQL(date('Y-m-d H:i:00', time())) . '\', a.`date_add`)) > 86400, \'' . pSQL($this->l('Abandoned cart', 'AdminEtsACCartController')) . '\', \'' . pSQL($this->l('Non ordered', 'AdminEtsACCartController')) . '\'), o.id_order) AS status
            , IF(o.id_order, 1, 0) badge_success
            , IF(o.id_order, 0, 1) badge_danger
            , IF(co.id_guest, 1, 0) id_guest
            , a.date_add
            , a.id_cart `sending_time`
            , 0 `sendmail_state`
		    , a.id_cart `next_mail_time`
        ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = a.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'currency` cu ON (cu.id_currency = a.id_currency)
            LEFT JOIN `' . _DB_PREFIX_ . 'carrier` ca ON (ca.id_carrier = a.id_carrier)
            LEFT JOIN `' . _DB_PREFIX_ . 'address` ad ON (ad.id_address = a.' . pSQL(Configuration::get('PS_TAX_ADDRESS_TYPE')) . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = a.id_cart)
            LEFT JOIN (
                SELECT `id_guest`
                FROM `' . _DB_PREFIX_ . 'connections`
                WHERE
                    TIME_TO_SEC(TIMEDIFF(\'' . pSQL(date('Y-m-d H:i:00', time())) . '\', `date_add`)) < 1800
                LIMIT 1
           ) AS co ON co.`id_guest` = a.`id_guest`
       ';

        if (($action = Tools::getValue('action')) && Validate::isCleanHtml($action) && trim($action) === 'filterOnlyAbandonedCarts') {
            $this->_having = 'status = \'' . pSQL($this->l('Abandoned cart', 'AdminEtsACCartController')) . '\'';
        } else {
            $this->_use_found_rows = false;
        }

        $this->_where = '
            AND (ad.id_address is NOT NULL OR a.' . pSQL(Configuration::get('PS_TAX_ADDRESS_TYPE')) . ' = 0) 
            AND o.id_order is NULL AND a.id_shop = ' . (int)$this->context->shop->id . ' 
            AND a.id_cart IN (SELECT cp.id_cart FROM `' . _DB_PREFIX_ . 'cart_product` cp WHERE cp.id_cart=a.id_cart)
        ';

        //$this->_group = 'GROUP BY cart.id_cart';

        $this->fields_list = array(
            'id_cart' => array(
                'title' => $this->l('ID', 'AdminEtsACCartController'),
                'type' => 'int',
                'class' => 'fixed-width-xs center',
                'filter_key' => 'a!id_cart',
            ),
            'status' => array(
                'title' => $this->l('Order ID', 'AdminEtsACCartController'),
                'align' => 'text-center',
                'badge_danger' => true,
                'havingFilter' => true,
            ),
            'customer_name' => array(
                'title' => $this->l('Customer', 'AdminEtsACCartController'),
                'havingFilter' => true,
                'callback' => 'displayCustomerName'
            ),
            'email' => array(
                'title' => $this->l('Email', 'AdminEtsACCartController'),
                'havingFilter' => true,
                'callback' => 'displayCustomerName'
            ),
            'total' => array(
                'title' => $this->l('Cart total', 'AdminEtsACCartController'),
                'callback' => 'getOrderTotalUsingTaxCalculationMethod',
                'orderby' => false,
                'search' => false,
                'align' => 'text-right',
                'badge_success' => true,
            ),
            'reminders' => [
                'title' => $this->l('Reminders', 'AdminEtsACCartController'),
                'class' => 'fixed-width-xs center',
                'filterHaving' => 'reminders',
                'callback' => 'displayReminders'
            ],
            'date_add' => array(
                'title' => $this->l('Date', 'AdminEtsACCartController'),
                'align' => 'text-left',
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'filter_key' => 'a!date_add',
            ),
            'next_mail_time' => array(
                'title' => $this->l('Next time to send email', 'AdminEtsACCartController'),
                'type' => 'datetime',
                'align' => 'center',
                'class' => 'fixed-width-lg',
                'havingFilter' => true,
                'callback' => 'displayNextMailTime',
            ),
            'sending_time' => array(
                'title' => $this->l('Last email sent at', 'AdminEtsACCartController'),
                'type' => 'datetime',
                'class' => 'fixed-width-lg ets_abancart_send_date',
                'havingFilter' => true,
                'callback' => 'displaySendingTime',
            ),
        );

        if (Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
            $this->fields_list['id_guest'] = array(
                'title' => $this->l('Online', 'AdminEtsACCartController'),
                'align' => 'text-center',
                'type' => 'bool',
                'havingFilter' => true,
                'class' => 'fixed-width-xs ets_abancart_online_status',
                'callback' => 'displayOnline',
            );
        }
    }

    public function displayNextMailTime($id_cart, $tr)
    {
        if ((int)$id_cart > 0 && (!isset($tr['id_order'])) || (int)$tr['id_order'] <= 0) {
            $next_mail_time = EtsAbancartReminder::getNextMailTime((int)$id_cart);

            return $next_mail_time ? EtsAbancartTools::formatDateStr($next_mail_time, true) : null;
        }
        return null;
    }

    public function displayReminders($reminders, $tr)
    {
        if (isset($tr['id_cart']) && $tr['id_cart'] > 0) {
            $id_cart = $tr['id_cart'];
            $this->context->smarty->assign([
                'remindersLink' => self::$currentIndex . '&id_cart=' . (int)$id_cart . '&reminderlog' . '&token=' . $this->token,
                'reminders' => count(EtsAbancartTracking::getLogs($id_cart)),
            ]);

            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/reminder-log.tpl');
        }

        return $reminders;
    }

    public function displaySendingTime($id_cart)
    {
        $dq = new DbQuery();
        $dq
            ->select('display_times `sending_time`, IF(at.delivered=1, 1, IF(qu.id_ets_abancart_email_queue > 0, 0, 2)) as `sendmail_state`')
            ->from('ets_abancart_tracking', 'at')
            ->leftJoin('ets_abancart_email_queue', 'qu', 'qu.id_cart = at.id_cart')
            ->leftJoin('ets_abancart_reminder', 'ar', 'ar.id_ets_abancart_reminder=at.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_campaign', 'ac', 'ac.id_ets_abancart_campaign=ar.id_ets_abancart_campaign')
            ->where('at.id_cart=' . (int)$id_cart)
            ->where('ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->orderBy('at.display_times DESC');

        $res = Db::getInstance()->getRow($dq);
        if (!$res)
            return null;
        $sending_time = $res['sending_time'] !== '0000-00-00 00:00:00' ? $res['sending_time'] : null;
        if ($sending_time == null)
            return $sending_time;
        $sendmail_state = (int)$res['sendmail_state'];
        $this->context->smarty->assign(array(
            'value' => $sending_time,
            'badge' => $sendmail_state == 1 ? 'success' : ($sendmail_state == 2 ? 'danger' : ''),
            'title' => $sendmail_state == 1 ? $this->l('Email sent successfully', 'AdminEtsACCartController') : ($sendmail_state == 2 ? $this->l('Email was failed to send', 'AdminEtsACCartController') : $this->l('Mail is in queue', 'AdminEtsACCartController'))
        ));
        return $this->createTemplate('sending-time.tpl')->fetch();
    }

    public function displayCustomerName($customer_name, $tr)
    {
        if (!isset($tr['id_customer']) || !(int)$tr['id_customer'])
            return $customer_name;
        if ($this->module->is17) {
            try {
                $href = $this->context->link->getAdminLink('AdminCustomers', true, array('route' => 'admin_customers_view', 'customerId' => (int)$tr['id_customer']), array('viewcustomer' => '', 'id_customer' => (int)$tr['id_customer']));
            } catch (Exception $ex) {
                $href = $this->context->link->getAdminLink('AdminCustomers', true, array(), array('viewcustomer' => '', 'id_customer' => (int)$tr['id_customer']));
            }
        } else {
            $href = $this->context->link->getAdminLink('AdminCustomers', true) . '?viewcustomer&id_customer=' . (int)$tr['id_customer'];
        }
        $this->context->smarty->assign([
            'btn' => [
                'href' => $href,
                'target' => '_bank',
                'title' => $customer_name,
                'class' => 'ets_ab_customer_link',
            ]
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
    }

    public function displayOnline($value)
    {
        $this->context->smarty->assign(array(
            'value' => $value ? $this->l('Yes', 'AdminEtsACCartController') : $this->l('No', 'AdminEtsACCartController'),
            'badge' => $value ? 'success' : 'danger',
        ));
        return $this->createTemplate('badge.tpl')->fetch();
    }

    public function initToolbarTitle()
    {
        if (!$this->display || $this->display == 'view') {
            $this->toolbar_title = array($this->l('Abandoned carts', 'AdminEtsACCartController', null, null, false));
            // Only add entry if the meta title was not forced.
            if (is_array($this->meta_title)) {
                $this->meta_title = array($this->l('Abandoned carts', 'AdminEtsACCartController', null, null, false));
            }
            if ($filter = $this->addFiltersToBreadcrumbs()) {
                $this->toolbar_title[] = $filter;
            }
        } else {
            parent::initToolbarTitle();
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme); // TODO: Change the autogenerated stub

        $this->addJS(array(
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_ . 'jquery/plugins/autocomplete/jquery.autocomplete.js',
        ));
    }

    protected function loadObject($opt = false)
    {
        if (!isset($this->className) || empty($this->className)) {
            return true;
        }

        $id = (int)Tools::getValue($this->identifier);
        $this->object = new $this->className($id);
        unset($opt);

        return $this->object;
    }

    public function initProcess()
    {
        parent::initProcess();

        // Send mail to Customer.
        if (($action = Tools::getValue('action')) && Validate::isCleanHtml($action) && trim($action) === 'sendMail' || (bool)Tools::isSubmit('sendmail')) {
            if ($this->access('edit')) {
                $this->fields_form = array(
                    'legend' => array(
                        'title' => $this->l('Abandoned cart', 'AdminEtsACCartController'),
                        //'icon' => 'icon-evelop',
                    ),
                    'submit' => array(
                        'title' => $this->l('Save', 'AdminEtsACCartController'),
                    ),
                    'input' => array(
                        //hidden.
                        'id_cart' => array(
                            'name' => 'id_cart',
                            'label' => $this->l('Id', 'AdminEtsACCartController'),
                            'type' => 'hidden',
                            'default_value' => $this->id_object,
                        ),
                        //discount.
                        'discount_option' => array(
                            'name' => 'discount_option',
                            'label' => $this->l('Discount options', 'AdminEtsACCartController'),
                            'type' => 'radios',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'no',
                                        'name' => $this->l('No discount', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'fixed',
                                        'name' => $this->l('Fixed discount code', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'auto',
                                        'name' => $this->l('Generate discount code automatically', 'AdminEtsACCartController')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default_value' => 'auto',
                            'form_group_class' => 'abancart form_discount discount_option is_parent1',
                        ),
                        'quantity' => array(
                            'name' => 'quantity',
                            'label' => $this->l('Total available', 'AdminEtsACCartController'),
                            'hint' => $this->l('The cart rule will be applied to the first', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '2',
                            'validate' => 'isCleanHtml',
                            'form_group_class' => 'abancart form_discount discount_option auto ets_ac_discount_qty',
                            'default_value' => 1,
                        ),
                        'quantity_per_user' => array(
                            'name' => 'quantity_per_user',
                            'label' => $this->l('Total available for each user', 'AdminEtsACCartController'),
                            'hint' => $this->l('A customer will only be able to use the cart rule', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '2',
                            'validate' => 'isCleanHtml',
                            'form_group_class' => 'abancart form_discount discount_option auto ets_ac_discount_qty',
                            'default_value' => 1,
                        ),
                        'discount_code' => array(
                            'name' => 'discount_code',
                            'label' => $this->l('Discount code', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '2',
                            'required' => true,
                            'validate' => 'isCleanHtml',
                            'form_group_class' => 'abancart form_discount discount_option fixed isCleanHtml required',
                        ),
                        'free_shipping' => array(
                            'name' => 'free_shipping',
                            'label' => $this->l('Free shipping', 'AdminEtsACCartController'),
                            'type' => 'switch',
                            'default_value' => 1,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes', 'AdminEtsACCartController')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No', 'AdminEtsACCartController')
                                ),
                            ),
                            'form_group_class' => 'abancart form_discount discount_option auto is_parent2',
                        ),
                        'apply_discount' => array(
                            'name' => 'apply_discount',
                            'label' => $this->l('Apply a discount', 'AdminEtsACCartController'),
                            'type' => 'radios',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'percent',
                                        'name' => $this->l('Percentage (%)', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'amount',
                                        'name' => $this->l('Amount', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'off',
                                        'name' => $this->l('None', 'AdminEtsACCartController')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default_value' => 'off',
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount is_parent2',
                        ),
                        'reduction_amount' => array(
                            'name' => 'reduction_amount',
                            'label' => $this->l('Amount', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '6',
                            'default_value' => '0',
                            'currencies' => Currency::getCurrencies(),
                            'tax' => array(
                                array(
                                    'id_option' => 0,
                                    'name' => $this->l('Tax excluded', 'AdminEtsACCartController')
                                ),
                                array(
                                    'id_option' => 1,
                                    'name' => $this->l('Tax included', 'AdminEtsACCartController')
                                ),
                            ),
                            'required' => true,
                            'validate' => 'isUnsignedFloat',
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount amount isUnsignedFloat required',
                        ),
                        'discount_name' => array(
                            'name' => 'discount_name',
                            'label' => $this->l('Discount name', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'lang' => true,
                            'required' => true,
                            'col' => 6,
                            'validate' => 'isCleanHtml',
                            'form_group_class' => 'abancart form_discount discount_option auto isCleanHtml required'
                        ),
                        'reduction_percent' => array(
                            'name' => 'reduction_percent',
                            'label' => $this->l('Discount percentage', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'suffix' => '%',
                            'col' => '2',
                            'required' => true,
                            'validate' => 'isPercentage',
                            'desc' => $this->l('Does not apply to the shipping costs', 'AdminEtsACCartController'),
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent isPercentage required',
                        ),
                        'apply_discount_to' => array(
                            'name' => 'apply_discount_to',
                            'label' => $this->l('Apply a discount to', 'AdminEtsACCartController'),
                            'type' => 'radios',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'order',
                                        'name' => $this->l('Order (without shipping)', 'AdminEtsACCartController'),
                                    ),
                                    array(
                                        'id_option' => 'specific',
                                        'name' => $this->l('Specific product', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'cheapest',
                                        'name' => $this->l('Cheapest product', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 'selection',
                                        'name' => $this->l('Selected product(s)', 'AdminEtsACCartController')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default_value' => 'order',
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount ets_ac_apply_discount',
                        ),
                        'reduction_product' => array(
                            'name' => 'reduction_product',
                            'label' => $this->l('Product', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '2',
                            'specific_product' => true,
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount ets_ac_specific_product_group',
                        ),
                        'selected_product' => array(
                            'name' => 'selected_product',
                            'label' => $this->l('Search product', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'col' => '2',
                            'search_product' => true,
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent ets_ac_selected_product_group',
                        ),
                        'reduction_exclude_special' => array(
                            'name' => 'reduction_exclude_special',
                            'label' => $this->l('Exclude discounted products', 'AdminEtsACCartController'),
                            'type' => 'switch',
                            'default_value' => 1,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes', 'AdminEtsACCartController')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No', 'AdminEtsACCartController')
                                ),
                            ),
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent',
                        ),
                        'free_gift' => array(
                            'name' => 'free_gift',
                            'label' => $this->l('Send a free gift', 'AdminEtsACCartController'),
                            'type' => 'switch',
                            'default_value' => 0,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes', 'AdminEtsACCartController')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('No', 'AdminEtsACCartController')
                                ),
                            ),
                            'form_group_class' => 'abancart form_discount discount_option auto',
                        ),
                        'product_gift' => array(
                            'name' => 'product_gift',
                            'label' => $this->l('Search a product', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'suffix' => $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'ets_abandonedcart/views/templates/hook/icon_search.tpl'),
                            'col' => '2',
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount percent amount off ets_ac_gift_product_filter_group',
                        ),

                        'id_currency' => array(
                            'name' => 'id_currency',
                            'label' => $this->l('Id currency', 'AdminEtsACCartController'),
                            'type' => 'select',
                            'options' => array(
                                'query' => Currency::getCurrencies(),
                                'id' => 'id_currency',
                                'name' => 'name',
                            ),
                            'default_value' => $this->context->currency->id,
                            'form_group_class' => 'abancart form_discount'
                        ),
                        'reduction_tax' => array(
                            'name' => 'reduction_tax',
                            'label' => $this->l(''),
                            'type' => 'select',
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 0,
                                        'name' => $this->l('Tax excluded', 'AdminEtsACCartController')
                                    ),
                                    array(
                                        'id_option' => 1,
                                        'name' => $this->l('Tax included', 'AdminEtsACCartController')
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                            'default_value' => '0',
                            'form_group_class' => 'abancart form_discount'
                        ),
                        'apply_discount_in' => array(
                            'name' => 'apply_discount_in',
                            'label' => $this->l('Discount availability', 'AdminEtsACCartController'),
                            'type' => 'text',
                            'required' => 'true',
                            'suffix' => $this->l('days', 'AdminEtsACCartController'),
                            'validate' => 'isUnsignedInt',
                            'col' => '2',
                            'default_value' => 7,
                            'form_group_class' => 'abancart form_discount discount_option auto apply_discount is_parent2 isUnsignedInt required',
                        ),
                        'allow_multi_discount' => array(
                            'name' => 'allow_multi_discount',
                            'label' => $this->l('Can use with other voucher in the same shopping cart?', 'AdminEtsACReminderEmailController'),
                            'type' => 'switch',
                            'default_value' => 0,
                            'values' => array(
                                array(
                                    'id' => 'enable_multi_discount_on',
                                    'value' => 1,
                                    'label' => $this->l('Yes', 'AdminEtsACReminderEmailController')
                                ),
                                array(
                                    'id' => 'enable_multi_discount_off',
                                    'value' => 0,
                                    'label' => $this->l('No', 'AdminEtsACReminderEmailController')
                                ),
                            ),
                            'form_group_class' => 'abancart form_discount discount_option fixed auto ets_ac_discount_qty',
                        ),
                        //end discount
                        //template.
                        'hidden_reminder_id' => [
                            'name' => 'hidden_reminder_id',
                            'type' => 'hidden',
                            'label' => $this->l('Reminder', 'AdminEtsACCartController'),
                            'default_value' => 0,
                        ],
                        'id_ets_abancart_email_template' => array(
                            'name' => 'id_ets_abancart_email_template',
                            'label' => $this->l('Email templates', 'AdminEtsACCartController'),
                            'type' => 'hidden',
                        ),
                        'title' => array(
                            'name' => 'title',
                            'label' => $this->l('Subject', 'AdminEtsACCartController'),
                            'type' => 'text',
                            //'lang' => true,
                            'required' => true,
                            'validate' => 'isMailSubject',
                            'default' => [
                                'origin' => 'You left something in your cart!',
                                'trans' => $this->l('You left something in your cart!', 'AdminEtsACCartController'),
                            ],
                            'form_group_class' => 'abancart form_message isMailSubject required'
                        ),
                        'content' => array(
                            'name' => 'content',
                            'label' => $this->l('Email content', 'AdminEtsACCartController'),
                            'type' => 'textarea',
                            'autoload_rte' => true,
                            //'lang' => true,
                            'required' => true,
                            'desc_type' => 'cart',
                            'validate' => 'isCleanHtml',
                            'form_group_class' => 'abancart content form_message isCleanHtml required'
                        ),
                        //end template.
                        'enabled' => array(
                            'type' => 'hidden',
                            'name' => 'enabled',
                            'label' => $this->l('Send email now?', 'AdminEtsACCartController'),
                            'default_value' => 1,
                            'form_group_class' => 'abancart form_confirm_information form_abandoned_cart'
                        )
                    )
                );
                $this->action = $this->display = 'sendmail';
            } else {
                $this->errors[] = $this->l('You do not have permission to send this email.', 'AdminEtsACCartController');
            }
        }
    }

    public function ajaxProcessSendMail()
    {
        $this->loadObject(true);
        $context = Context::getContext();
        // Backup:
        $keeps = [
            'currency' => $context->currency,
            'shop' => $context->shop,
            'cart' => $context->cart,
            'customer' => $context->customer,
        ];

        $cart = new Cart((int)$this->id_object);
        $context->cart = $cart;
        $context->shop = new Shop($cart->id_shop);
        if (!Validate::isLoadedObject($cart)) {
            $this->errors[] = $this->l('Cart does not exist!', 'AdminEtsACCartController');
        }
        $jsonData = array();
        if (count($this->errors) < 1) {

            // Object reminder:
            $object = new EtsAbancartReminder();
            $this->validateRules('EtsAbancartReminder');
            $this->copyFromPost($object, 'ets_abancart_reminder');
            // End:

            $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
            $language = new Language((int)$cart->id_lang);
            if (!$language->id) {
                $language = new Language($id_lang_default);
            }
            if ($customer = new Customer($cart->id_customer)) {
                $this->context->customer = $customer;
            }

            $template = 'abandoned_cart';
            if (!$customer->id) {
                $this->errors[] = $this->l('Customer does not exist!', 'AdminEtsACCartController');
            } elseif (!@glob(($destTempMail = $this->module->getLocalPath() . 'mails/' . $language->iso_code . '/' . $template . '*[.txt|.html]'))) {
                if ($this->module->_installMail($language) && !glob($destTempMail))
                    $this->errors[] = sprintf($this->l('Error - The following email template is missing: %s', 'AdminEtsACCartController'), $template);
            } elseif (!Validate::isEmail($customer->email)) {
                $this->errors[] = $this->l('Error: invalid email address', 'AdminEtsACCartController');
            }

            if (count($this->errors) < 1) {
                $cart_rule = new CartRule();
                if (($discount_option = Tools::getValue('discount_option')) && trim($discount_option) === 'auto') {
                    $cart_rule = $this->module->addCartRule($object, $customer->id);
                    if (!is_object($cart_rule) || !$cart_rule instanceof CartRule) {
                        $this->errors[] = implode($this->module->displayText('', 'br'), $cart_rule);
                    }
                } elseif ($discount_option != 'no' && ($discount_code = Tools::getValue('discount_code')) && Validate::isCleanHtml($discount_code)) {
                    $cartRule = CartRule::getCartsRuleByCode($discount_code, $language->id);
                    if (!$cartRule) {
                        $this->errors[] = $this->l('Discount code does not exist', 'AdminEtsACCartController');
                    } else
                        $cart_rule = new CartRule((int)$cartRule['id_cart_rule']);
                }

                if (count($this->errors) < 1) {
                    $templateVars = array(
                        'cart' => $cart,
                        'campaign_type' => 'cart',
                        'content' => $object->content,
                        'customer' => $customer,
                        'cart_rule' => $cart_rule,
                    );
                }

                if ((int)Tools::getValue('preview')) {
                    $this->toJson([
                        'preview' => $this->module->doShortCode($templateVars['content'], 'cart', $cart_rule, $context)
                    ]);
                }
                if ($object->enabled) {
                    if (count($this->errors) < 1 && !EtsAbancartMail::Send(
                            $language->id,
                            $template,
                            $object->title,
                            array(
                                '{tracking}' => $context->link->getModuleLink($this->module->name, 'image', array('c' => $cart->id), Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) . '&' . md5(time()),
                                '{context}' => $this->module->doShortCode($templateVars['content'], 'cart', $cart_rule, $context)
                            ),
                            $customer->email,
                            $customer->firstname . ' ' . $customer->lastname,
                            null, null, null, null, $this->module->getLocalPath() . 'mails/')
                    ) {
                        $this->errors[] = $this->l('Sending email failed.', 'AdminEtsACCartController');
                    }
                } else {
                    $this->toJson(array(
                        'msg' => $this->l('No email to send', 'AdminEtsACCartController'),
                        'errors' => false
                    ));
                }

                $tracking = new EtsAbancartTracking();
                $tracking->id_cart = (int)$context->cart->id;
                $tracking->id_customer = $context->customer->id;
                $tracking->email = $context->customer->email;
                $tracking->id_shop = $context->shop->id;
                $tracking->display_times = date('Y-m-d H:i:s');
                $tracking->total_execute_times = 1;
                $tracking->id_ets_abancart_reminder = -1;
                $tracking->ip_address = ($ip_address = Tools::getRemoteAddr()) && $ip_address == '::1' ? '127.0.0.1' : $ip_address;
                $tracking->delivered = count($this->errors) > 0 ? 0 : 1;
                if ($tracking->save(true) && $cart_rule->id > 0 && trim($discount_option) === 'auto') {
                    EtsAbancartTracking::trackingDiscount($tracking->id, $cart_rule->id, (int)Tools::getValue('allow_multi_discount', 0) ? 1 : 0);
                }
            }

            if (!$this->errors) {
                // Return.
                $jsonData = array(
                    'msg' => $this->l('Sent email successfully', 'AdminEtsACCartController'),
                    'date_upd' => $tracking->date_upd,
                    'list' => $this->renderList(),
                );
            }
        }
        // Restore:
        foreach ($keeps as $key => $keep) {
            $context->{$key} = $keep;
        }
        $jsonData['errors'] = $this->errors ? $this->module->displayError($this->errors) : false;
        $this->toJson($jsonData);
    }

    public function ajaxProcessFormSendMail()
    {
        if ($this->access('edit')) {
            $this->tpl_form_vars = array(
                'email_templates' => EtsAbancartEmailTemplate::getTemplates(null, 'email', null, $this->context),
                'menus' => EtsAbancartReminderForm::getInstance()->getReminderSteps(),
                'lead_forms' => EtsAbancartForm::getAllForms(false, true),
                'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
                'baseUri' => __PS_BASE_URI__,
                'field_types' => EtsAbancartField::getInstance()->getFieldType(),
                'module_dir' => _PS_MODULE_DIR_ . $this->module->name,
                'is17Ac' => $this->module->is17,
                'image_url' => $this->context->shop->getBaseURL(true) . 'img/' . $this->module->name . '/img/',
                'short_codes' => EtsAbancartDefines::getInstance()->getFields('short_codes'),
            );

            $this->toJson(array(
                'html' => $this->renderForm()
            ));
        }
    }

    public function ajaxProcessSelectTemplate()
    {
        if ($this->access('edit')) {
            $object = new EtsAbancartEmailTemplate((int)Tools::getValue('id_ets_abancart_email_template'));
            $languages = Language::getLanguages(false);
            $mailContent = array();
            $idLangDefault = Configuration::get('PS_LANG_DEFAULT');
            $mailDirDefault = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($object->folder_name ?: $object->id) . '/' . $object->temp_path[$idLangDefault];

            foreach ($languages as $lang) {
                $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_ . '/' . ($object->folder_name ?: $object->id) . '/' . $object->temp_path[$lang['id_lang']];
                if (file_exists($mailDir)) {
                    $mailContent[$lang['id_lang']] = EtsAbancartEmailTemplate::getBodyMailTemplate($mailDir, $this);
                } elseif (file_exists($mailDirDefault)) {
                    $mailContent[$lang['id_lang']] = EtsAbancartEmailTemplate::getBodyMailTemplate($mailDirDefault, $this);
                } else {
                    $mailContent[$lang['id_lang']] = '';
                }
            }
            $this->toJson(array(
                'html' => $object->id > 0 ? $mailContent : '',
            ));
        }
    }

    public function ajaxProcessReminderLog()
    {
        if ($this->access('edit')) {

            $this->loadObject();

            $id_cart = (int)Tools::getValue($this->identifier);
            $id_customer = (int)Tools::getValue('id_customer');
            $tpl_vars = [];

            if ($logs = EtsAbancartTracking::getLogs($id_cart, $id_customer)) {
                $idCurrency = 0;
                if ($id_cart > 0) {
                    $cart = new Cart($id_cart);
                    $idLang = $cart->id_lang;
                    $idCurrency = $cart->id_currency;
                } else {
                    $customer = new Customer($id_customer);
                    $idLang = $customer->id_lang;
                }
                $LOGs = array();
                foreach ($logs as &$log) {
                    $LOGs[] = EtsAbancartReminderForm::getInstance()->propertiesTracking($log['id_ets_abancart_reminder'], $log['reminder_name'], $log['id_cart_rule'], $idLang, $idCurrency, $log['template_name'], $log['display_times']);
                }
                $tpl_vars['LOGs'] = $LOGs;
            }
            $tpl_vars['next_mails_time'] = EtsAbancartReminder::getNextMailTime($id_cart, false);

            if ($tpl_vars)
                $this->context->smarty->assign($tpl_vars);

            $this->toJson(array(
                'html' => $this->createTemplate('logs.tpl')->fetch(),
            ));
        }
    }

    public function renderForm()
    {
        if (!$this->loadObject(true)) {
            //return;
        }
        $id_cart = (int)Tools::getValue('id_cart');
        $cart = new Cart($id_cart);
        $lang = new Language($cart->id_lang);
        if ($lang->iso_code) {
            $this->fields_form['input']['title']['default_value'] = $this->module->getTextLang($this->fields_form['input']['title']['default']['origin'], $lang, 'AdminEtsACCartController');
            $this->tpl_form_vars['id_lang_default'] = $lang->id;
            $this->tpl_form_vars['PS_LANG_DEFAULT'] = Configuration::get('PS_LANG_DEFAULT');
        }
        $this->fields_form['buttons'] = array(
            'back' => array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'title' => $this->l('Back to list', 'AdminEtsACCartController'),
                'icon' => 'process-icon-back',
                'class' => 'ets_abancart_process_back',
            ),
        );

        self::$currentIndex .= (Tools::isSubmit('add' . $this->list_id) ? '&add' . $this->list_id : '') . (Tools::isSubmit('update' . $this->list_id) ? '&update' . $this->list_id : '');

        return parent::renderForm();
    }

    public static function replaceZeroByShopName($echo, $tr)
    {
        unset($tr);
        return $echo == '0' ? Carrier::getCarrierNameFromShopName() : $echo;
    }

    public function displayViewCartLink($token, $id)
    {
        if (!isset(self::$cache_lang['view'])) {
            self::$cache_lang['view'] = $this->l('View cart', 'AdminEtsACCartController');
        }
        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminLink('AdminCarts', true) .
                '&id_cart=' . $id .
                '&viewcart',
            'action' => self::$cache_lang['view'],
        ));
        unset($token);
        return $this->createTemplate('helpers/list/list_action_view.tpl')->fetch();
    }

    public function displaySendMailLink($token, $id)
    {
        $cart = new Cart($id);
        if (!isset(self::$cache_lang['sendmail'])) {
            self::$cache_lang['sendmail'] = $this->l('Send reminder', 'AdminEtsACCartController');
        }
        $this->context->smarty->assign(array(
            'href' => $cart->id_customer ? self::$currentIndex . '&' . $this->identifier . '=' . $id . '&sendmail&token=' . ($token != null ? $token : $this->token) : 'javascript:void(0)',
            'action' => self::$cache_lang['sendmail'],
            'class' => 'ets_abancart_sendmail btn btn-default' . (!$cart->id_customer ? ' disabled' : ''),
            'disabled' => !$cart->id_customer,
        ));

        return $this->createTemplate('helpers/list/list_action_sendmail.tpl')->fetch();
    }

    public function displayReminderLogLink($token, $id)
    {
        if (!isset(self::$cache_lang['reminder_log'])) {
            self::$cache_lang['reminder_log'] = $this->l('View reminder log', 'AdminEtsACCartController');
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex .
                '&' . $this->identifier . '=' . $id .
                '&reminderlog&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['reminder_log'],
            'class' => 'ets_abancart_reminder_log',
        ));

        return $this->createTemplate('helpers/list/list_action_reminder_log.tpl')->fetch();
    }
    public function displaySessionLink($token, $id)
    {
        $this->context->smarty->assign(array(
            'href' => $this->context->link->getAdminLink('AdminTrackingCustomerSession').
                '&' . $this->identifier . '=' . $id .
                '&current_tab=customer_session',
            'action' => $this->l('View session'),
            'class' => 'ets_view_session',
            'token' => $token,
        ));

        return $this->createTemplate('helpers/list/list_action_session.tpl')->fetch();
    }
}