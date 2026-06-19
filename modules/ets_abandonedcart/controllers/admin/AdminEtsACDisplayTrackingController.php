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

class AdminEtsACDisplayTrackingController extends AdminEtsACFormController
{
    static $cache_campaign_type = [];

    public function __construct()
    {
        $this->table = 'ets_abancart_display_tracking';
        $this->className = 'EtsAbancartDisplayTracking';
        $this->list_id = $this->table;
        $this->lang = false;
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        parent::__construct();

        $this->_select = '
            ac.id_ets_abancart_campaign
            , IF(ar.id_ets_abancart_reminder > 0, ac.campaign_type, \'leave\') `campaign_type`
            , IF(a.id_ets_abancart_reminder > 0, CONCAT(\'#\', ac.id_ets_abancart_campaign, \' - \', acl.name), \'' . pSQL($this->l('Leaving website', 'AdminEtsACDisplayTrackingController')) . '\') `campaign_name`
            , IF(a.id_ets_abancart_reminder > 0, CONCAT(\'#\', a.id_ets_abancart_reminder, \' - \', arl.title), \'' . pSQL($this->l('Leaving website', 'AdminEtsACDisplayTrackingController')) . '\') `reminder_title`
            , SUM(ddt.`number_of_discount`) `discounts`
            , SUM(a.number_of_displayed) `display_times`
        ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl ON (acl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND acl.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN (
                SELECT COUNT(ddt.id_cart_rule) `number_of_discount`, ddt.id_ets_abancart_display_tracking
                FROM `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` ddt
                LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (crl.id_cart_rule = ddt.id_cart_rule AND crl.id_lang = ' . (int)$this->context->language->id . ')
                WHERE ddt.id_cart > 0
                GROUP BY ddt.id_ets_abancart_display_tracking
            ) ddt ON (ddt.id_ets_abancart_display_tracking = a.id_ets_abancart_display_tracking)
        ';

        $this->_where = 'AND ac.deleted = 0 AND ar.deleted = 0';

        $this->_group = 'GROUP BY ar.id_ets_abancart_reminder';

        if (($id_ets_abancart_reminder = Tools::getValue('id_ets_abancart_reminder')) !== '' && Validate::isUnsignedInt($id_ets_abancart_reminder))
            $this->_filter .= ' AND ar.id_ets_abancart_reminder=' . (int)$id_ets_abancart_reminder;

        if (($id_ets_abancart_campaign = Tools::getValue('id_ets_abancart_campaign')) !== '' && Validate::isUnsignedInt($id_ets_abancart_campaign))
            $this->_filter .= ' AND ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign;

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where = ' AND a.id_shop=' . (int)$this->context->shop->id;

        if (!self::$cache_campaign_type) {
            $menus = EtsAbancartDefines::getInstance()->getSubMenus();
            if ($menus) {
                foreach ([EtsAbancartCampaign::CAMPAIGN_TYPE_POPUP, EtsAbancartCampaign::CAMPAIGN_TYPE_BAR, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER, EtsAbancartCampaign::CAMPAIGN_TYPE_LEAVE] as $campaign_type) {
                    self::$cache_campaign_type[$campaign_type] = isset($menus[$campaign_type]['label']) ? $menus[$campaign_type]['label'] : null;
                }
            }
        }

        $this->fields_list = array(
            'campaign_type' => array(
                'title' => $this->l('Display type', 'AdminEtsACDisplayTrackingController'),
                'type' => 'select',
                'list' => self::$cache_campaign_type,
                'filter_key' => 'campaign_type',
                'havingFilter' => true,
                'callback' => 'displayCampaignType',
                'class' => 'fixed-width-lg center',
            ),
            'campaign_name' => array(
                'title' => $this->l('Display campaign', 'AdminEtsACDisplayTrackingController'),
                'type' => 'text',
                'filter_key' => 'campaign_name',
                'havingFilter' => true,
            ),
            'reminder_title' => array(
                'title' => $this->l('Reminder', 'AdminEtsACDisplayTrackingController'),
                'type' => 'text',
                'filter_key' => 'reminder_title',
                'havingFilter' => true,
            ),
            'discounts' => array(
                'title' => $this->l('Discounts', 'AdminEtsACDisplayTrackingController'),
                'type' => 'text',
                'filter_key' => 'discounts',
                'callback' => 'displayDiscounts'
            ),
            'display_times' => array(
                'title' => $this->l('Display times', 'AdminEtsACDisplayTrackingController'),
                'type' => 'int',
                'align' => 'center',
                'filter_key' => 'display_times',
                'havingFilter' => true,
                'class' => 'fixed-width-lg',
            ),
        );
    }

    public function initToolbar()
    {
        parent::initToolbar(); // TODO: Change the autogenerated stub

        unset($this->toolbar_btn['new']);
    }

    public function displayCampaignType($campaign_type)
    {
        $path_uri_image = $this->module->getPathUri() . ($path = 'views/img/origin/' . $campaign_type);
        $image_dir = $this->module->getLocalPath() . $path;
        $this->context->smarty->assign([
            'name' => isset(self::$cache_campaign_type[$campaign_type]) && self::$cache_campaign_type[$campaign_type] ? self::$cache_campaign_type[$campaign_type] : '--',
            'path_uri_image' => @file_exists($image_dir . '.png') ? $path_uri_image . '.png' : (file_exists($image_dir . '.jpg') ? $path_uri_image . '.png' : ''),
        ]);
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/bo-campaign-type.tpl');
    }

    public function displayDiscounts($discounts, $tr)
    {
        if (!isset($tr['id_ets_abancart_reminder']) || trim($tr['id_ets_abancart_reminder']) == '' || !$discounts || (int)$discounts < 1)
            return null;
        $this->context->smarty->assign([
            'voucher_name' => $discounts,
            'voucher_link' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Discounts', true, [], ['submitFiltercart_rule' => 1, 'cart_ruleFilter_reminder' => $tr['id_ets_abancart_reminder']])
        ]);

        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/voucher-row-link.tpl');
    }
}