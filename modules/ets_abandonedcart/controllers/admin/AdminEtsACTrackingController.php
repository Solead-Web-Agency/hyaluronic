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

class AdminEtsACTrackingController extends AdminEtsACFormController
{
    static $bool = array();
    public $reminderType = array();
    public $campaignType = null;

    public function __construct()
    {
        $this->table = 'ets_abancart_tracking';
        $this->className = 'EtsAbancartTracking';
        $this->list_id = $this->table;
        $this->lang = false;
        //$this->_orderBy = 'date_upd';
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        $this->addRowAction('reminderlog');

        parent::__construct();

        self::$bool = [
            1 => $this->l('Yes', 'AdminEtsACTrackingController'),
            0 => $this->l('No', 'AdminEtsACTrackingController'),
        ];

        $this->_select = ' 
            IF(a.display_times is NOT NULL AND a.display_times != \'0000-00-00 00:00:00\', a.display_times, NULL) as `display_times`, 
            IF(a.id_ets_abancart_reminder > 0, ac.campaign_type, \'cart\') `campaign_type`, 
            (@rank:=@rank + ' . (isset($this->context->cookie->{$this->list_id . '_start'}) && $this->context->cookie->{$this->list_id . '_start'} ? (int)$this->context->cookie->{$this->list_id . '_start'} : 0) . ' + 1) as `reminder_order`,
            a.id_ets_abancart_tracking `discount_code`,
            IF(a.id_customer is NULL, CONCAT(c1.firstname, \' \', c1.lastname), CONCAT(c2.firstname, \' \', c2.lastname)) as `customer_name`,
            IF(a.id_customer is NULL, c1.id_customer, c2.id_customer) as `id_customer`,
            a.email as `customer_email`,
            IF((ac.campaign_type = \'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\' OR a.id_ets_abancart_reminder < 1) AND a.read=1,\'read\', IF((ac.campaign_type = \'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\' OR a.id_ets_abancart_reminder < 1) AND a.delivered=1, \'delivered\', \'timeout\')) execute_status,
            acl.`name`,
            ac.`id_ets_abancart_campaign`,
            IF(o.id_order > 0 AND os.paid = 1 AND a.delivered = 1 AND (a.id_ets_abancart_reminder > 0 AND ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR a.id_ets_abancart_reminder = -1), 1, 0) `recovered_cart`,
            IF(a.id_ets_abancart_reminder > 0, acl.name, \'' . pSQL($this->l('Manually abandoned cart emails', 'AdminEtsACTrackingController')) . '\') `campaign_name`,
            arl.title `reminder`
        ';

        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (a.id_ets_abancart_reminder = ar.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang = ' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl ON (acl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND acl.id_lang = ' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'cart` cart ON (cart.id_cart = a.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c1 ON (cart.id_customer = c1.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c2 ON (a.id_customer = c2.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = cart.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = o.`current_state`)
            , (SELECT @rank:=0) y
        ';

        $this->_where = 'AND a.deleted = 0 AND ac.deleted = 0 AND ar.deleted = 0';

        if (($id_ets_abancart_reminder = Tools::getValue('id_ets_abancart_reminder')) !== '' && Validate::isUnsignedInt($id_ets_abancart_reminder))
            $this->_filter .= ' AND a.id_ets_abancart_reminder=' . (int)$id_ets_abancart_reminder;

        if (($id_ets_abancart_campaign = Tools::getValue('id_ets_abancart_campaign')) !== '' && Validate::isUnsignedInt($id_ets_abancart_campaign))
            $this->_filter .= ' AND ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign;

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where .= ' AND a.id_shop=' . (int)$this->context->shop->id;

        if (!$this->reminderType) {
            $menus = EtsAbancartDefines::getInstance()->getSubMenus();
            if ($menus) {
                $this->reminderType = [
                    EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL => $menus[EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL]['label'],
                    EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER => $menus[EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]['label'],
                    EtsAbancartCampaign::CAMPAIGN_TYPE_CART => $this->l('Manually abandoned cart emails'),
                ];
            }
        }

        $this->fields_list = array(
            'id_ets_abancart_tracking' => array(
                'title' => $this->l('Tracking ID', 'AdminEtsACTrackingController'),
                'type' => 'int',
                'filter_key' => 'a!id_ets_abancart_tracking',
                'class' => 'fixed-width-xs center',
            ),
            'campaign_type' => array(
                'title' => $this->l('Reminder type', 'AdminEtsACTrackingController'),
                'type' => 'select',
                'list' => $this->reminderType,
                'havingFilter' => true,
                'filter_key' => 'campaign_type',
                'callback' => 'displayCampaignType',
            ),
            'reminder' => array(
                'title' => $this->l('Reminder', 'AdminEtsACTrackingController'),
                'type' => 'text',
                'havingFilter' => 'reminder',
                'callback' => 'displayReminder',
            ),
            'customer_name' => array(
                'title' => $this->l('Customer', 'AdminEtsACTrackingController'),
                'type' => 'text',
                'align' => 'center',
                'havingFilter' => true,
                'filter_key' => 'customer_name',
                'class' => 'fixed-width-xs center',
                'callback' => 'displayCustomerName',
            ),
            'customer_email' => array(
                'title' => $this->l('Customer email', 'AdminEtsACTrackingController'),
                'type' => 'text',
                'align' => 'center',
                'havingFilter' => true,
                'filter_key' => 'customer_email',
                'class' => 'fixed-width-xs center',
            ),
            'id_cart' => array(
                'title' => $this->l('Shopping cart ID', 'AdminEtsACTrackingController'),
                'type' => 'text',
                'align' => 'center',
                'filter_key' => 'a!id_cart',
                'class' => 'fixed-width-xs center',
                'callback' => 'displayCart',
            ),
            'recovered_cart' => array(
                'title' => $this->l('Recovered cart', 'AdminEtsACTrackingController'),
                'type' => 'select',
                'list' => self::$bool,
                'filter_key' => 'recovered_cart',
                'havingFilter' => 'recovered_cart',
                'class' => 'fixed-width-xs center',
                'callback' => 'displayIsRecoveredCart',
            ),
            'discount_code' => array(
                'title' => $this->l('Discount code', 'AdminEtsACTrackingController'),
                'type' => 'text',
                'align' => 'center',
                'filter_key' => 'discount_code',
                'havingFilter' => true,
                'callback' => 'displayDiscountCode'
            ),
            'execute_status' => array(
                'title' => $this->l('Execute status', 'AdminEtsACTrackingController'),
                'type' => 'select',
                'list' => array(
                    'timeout' => $this->l('Timed out', 'AdminEtsACTrackingController'),
                    'read' => $this->l('Read', 'AdminEtsACTrackingController'),
                    'delivered' => $this->l('Delivered', 'AdminEtsACTrackingController'),
                ),
                'align' => 'center',
                'filter_key' => 'execute_status',
                'havingFilter' => true,
                'callback' => 'displayExecuteStatus',
            ),
            'total_execute_times' => array(
                'title' => $this->l('Total execute times', 'AdminEtsACTrackingController'),
                'type' => 'int',
                'align' => 'center',
                'filter_key' => 'a!total_execute_times',
            ),
            'display_times' => array(
                'title' => $this->l('Last execution', 'AdminEtsACTrackingController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!display_times',
            ),
        );

        $this->campaignType = trim(Tools::getValue('campaign_type'));
        if ($this->campaignType) {
            if ($this->context->cookie->__get('submitFilter' . $this->list_id))
                $this->processResetFilters($this->list_id);
            $prefix = $this->getCookieFilterPrefix();
            if ($this->campaignType)
                $this->context->cookie->{$prefix . $this->list_id . 'Filter_campaign_type'} = $this->campaignType;
            $this->processFilter();
        }
    }

    public function processFilter()
    {
        parent::processFilter();

        $prefix = $this->getCookieFilterPrefix();
        $campaign_type = $prefix . $this->list_id . 'Filter_customer_name';
        if (isset($this->context->cookie->$campaign_type) && $this->context->cookie->$campaign_type !== '' && Validate::isCatalogName($this->context->cookie->$campaign_type))
            $this->_filterHaving = ' AND `campaign_type`=\'' . pSQL($this->context->cookie->$campaign_type) . '\'';
    }

    public function displayDiscountCode($id_ets_abancart_tracking)
    {
        if ($id_ets_abancart_tracking) {
            $dq = new DbQuery();
            $dq
                ->select('cr.code')
                ->from('ets_abancart_discount', 'd')
                ->leftJoin('cart_rule', 'cr', 'cr.id_cart_rule = d.id_cart_rule')
                ->where('d.id_ets_abancart_tracking=' . (int)$id_ets_abancart_tracking);
            return Db::getInstance()->getValue($dq);
        }

        return null;
    }

    public function ajaxProcessReminderLog()
    {
        if ($this->access('edit')) {

            $tracking_id = (int)Tools::getValue($this->identifier);
            $tpl_vars = [];
            $tracking = new EtsAbancartTracking($tracking_id);

            if ($tracking->id > 0) {
                $idCurrency = 0;
                if ($tracking->id_cart > 0) {
                    $cart = new Cart($tracking->id_cart);
                    $idLang = $cart->id_lang;
                    $idCurrency = $cart->id_currency;
                } else {
                    $customer = new Customer($tracking->id_customer);
                    $idLang = $customer->id_lang;
                }
                $reminder = new EtsAbancartReminder($tracking->id_ets_abancart_reminder, $idLang);
                $template = new EtsAbancartEmailTemplate($reminder->id_ets_abancart_email_template, $idLang);
                $id_cart_rule = EtsAbancartTracking::getDiscountByTrackingId($tracking->id);
                $tpl_vars['LOGs'] = [EtsAbancartReminderForm::getInstance()->propertiesTracking($reminder->id, $reminder->title, $id_cart_rule, $idLang, $idCurrency, $template->name, $tracking->display_times)];
            }
            $tpl_vars['tracking'] = true;
            if ($tpl_vars)
                $this->context->smarty->assign($tpl_vars);

            $this->toJson(array(
                'html' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/etsac_cart/logs.tpl'),
            ));
        }
    }

    public function displayReminderLogLink($token, $id)
    {
        if (!isset(self::$cache_lang['reminder_log'])) {
            self::$cache_lang['reminder_log'] = $this->l('View reminder log', 'AdminEtsACTrackingController');
        }
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&id_ets_abancart_tracking=' . $id . '&reminderlog&token=' . $this->token,
            'action' => self::$cache_lang['reminder_log'],
            'class' => 'ets_abancart_reminder_log',
            'token' => $token
        ));

        return $this->createTemplate('helpers/list/list_action_reminder_log.tpl')->fetch();
    }

    public function displayReminder($reminder, $tr)
    {
        if (!isset($tr['id_ets_abancart_campaign']) || !isset($tr['id_ets_abancart_reminder']))
            return $reminder;
        $this->context->smarty->assign([
            'btn' => [
                'href' => $this->context->link->getAdminLink('AdminEtsACReminderEmail', true, [], ['updateets_abancart_campaign' => '', 'id_ets_abancart_campaign' => (int)$tr['id_ets_abancart_campaign']]),
                'target' => '_bank',
                'title' => ($tr['id_ets_abancart_reminder'] > 0 ? '#' . $tr['id_ets_abancart_reminder'] . ' - ' : '') . $reminder,
                'class' => 'ets_ab_campaign_link',
            ]
        ]);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
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

    public function displayCampaignType($value, $tr)
    {
        $path_uri_image = $this->module->getPathUri() . ($path = 'views/img/origin/' . $value);
        $image_dir = $this->module->getLocalPath() . $path;
        $campaignName = isset($this->reminderType[$value]) && $this->reminderType[$value] ? $this->reminderType[$value] : '--';
        $this->context->smarty->assign([
            'campaign_name' => isset($tr['campaign_name']) ? $tr['campaign_name'] : '',
            'campaign_title' => $campaignName,
            'path_uri_image' => @file_exists($image_dir . '.png') ? $path_uri_image . '.png' : (file_exists($image_dir . '.jpg') ? $path_uri_image . '.png' : ''),
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-campaign-type.tpl');
    }

    public function displayCart($id_cart)
    {
        if (!$id_cart)
            return;
        $this->context->smarty->assign([
            'btn' => [
                'href' => $this->context->link->getAdminLink('AdminCarts', true, [], ['viewcart' => '', 'id_cart' => (int)$id_cart]),
                'target' => '_bank',
                'title' => $id_cart,
                'class' => 'ets_ab_cart_link',
            ]
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-link.tpl');
    }

    public function displayExecuteStatus($value)
    {
        if (!$value) {
            return '--';
        }
        $this->context->smarty->assign(array(
            'executeStatus' => $value
        ));

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name . '/views/templates/hook/tracking_execute_status.tpl');
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function initToolbarTitle()
    {
        if (!$this->display || $this->display == 'view') {
            $this->toolbar_title = array($this->l('Campaign tracking', 'AdminEtsACTrackingController', null, null, false));
            // Only add entry if the meta title was not forced.
            if (is_array($this->meta_title)) {
                $this->meta_title = array($this->l('Campaign tracking', 'AdminEtsACTrackingController', null, null, false));
            }
            if ($filter = $this->addFiltersToBreadcrumbs()) {
                $this->toolbar_title[] = $filter;
            }
        } else {
            parent::initToolbarTitle();
        }
    }

    public function renderList()
    {
        $campaign = null;
        if ($idReminder = (int)Tools::getValue('id_ets_abancart_reminder')) {
            $reminder = new EtsAbancartReminder($idReminder);
            if ($reminder && $reminder->id) {
                $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);
            }
        } elseif ($idCampaign = (int)Tools::getValue('id_ets_abancart_campaign')) {
            $campaign = new EtsAbancartCampaign($idCampaign);
        }
        if ($campaign && $campaign->id) {
            $this->context->cookie->etsactrackingets_abancart_trackingFilter_campaign_type = $campaign->campaign_type;
            $this->_where .= " AND ac.id_ets_abancart_campaign=" . (int)$campaign->id;
            if (isset($idReminder) && $idReminder) {
                $this->context->cookie->{'etsactrackingets_abancart_trackingFilter_a!id_ets_abancart_reminder'} = $idReminder;
                $this->_where .= " AND a.id_ets_abancart_reminder=" . (int)$idReminder;
            }
        }
        return parent::renderList();
    }

    public function postProcess()
    {
        if (isset($this->context->cookie->ets_ac_tracking_success) && $this->context->cookie->ets_ac_tracking_success) {
            $this->confirmations[] = $this->context->cookie->ets_ac_tracking_success;
            $this->context->cookie->ets_ac_tracking_success = null;
        } elseif (isset($this->context->cookie->ets_ac_tracking_error) && $this->context->cookie->ets_ac_tracking_error) {
            $this->errors[] = $this->context->cookie->ets_ac_tracking_error;
            $this->context->cookie->ets_ac_tracking_error = null;
        }
        if (Tools::isSubmit('clearTracking')) {
            if (EtsAbancartTracking::setDelete()) {
                $this->context->cookie->ets_ac_tracking_success = $this->l('Clear tracking successfully', 'AdminEtsACTrackingController');
            } else
                $this->context->cookie->ets_ac_tracking_error = $this->l('Clear tracking failed', 'AdminEtsACTrackingController');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminEtsACTracking'));
        }
        parent::postProcess();
    }
}