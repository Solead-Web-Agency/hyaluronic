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

class AdminEtsACDiscountsController extends AdminEtsACFormController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'cart_rule';
        $this->className = 'CartRule';
        $this->lang = true;
        $this->_orderWay = 'DESC';
        $this->list_no_link = true;

        parent::__construct();

        $this->_select = '
            IF(ac.id_ets_abancart_campaign > 0, acl.name, IF(at.id_ets_abancart_reminder = -1, \'' . pSQL($this->l('Manual abandoned carts emails campaign', 'AdminEtsACDiscountsController')) . '\',IF(at2.id_ets_abancart_reminder=0,\'' . pSQL($this->l('Leaving website campaign', 'AdminEtsACDiscountsController')) . '\', acl2.name))) `campaign`
            , IF(ar.id_ets_abancart_reminder > 0, arl.title, IF(at.id_ets_abancart_reminder = -1, \'' . pSQL($this->l('Manual abandoned carts emails reminder', 'AdminEtsACDiscountsController')) . '\',IF(at2.id_ets_abancart_reminder=0,\'' . pSQL($this->l('Leaving website reminder', 'AdminEtsACDiscountsController')) . '\', arl2.title))) `reminder`
            , b.name `cart_rule_name`
            , IF(ar.id_ets_abancart_campaign is NOT NULL, ar.id_ets_abancart_campaign, IF(at2.id_ets_abancart_reminder = 0, 0, ar2.id_ets_abancart_campaign)) `id_ets_abancart_campaign`
            , IF(ar.id_ets_abancart_reminder is NOT NULL, ar.id_ets_abancart_reminder, IF(at2.id_ets_abancart_reminder = 0, 0, ar2.id_ets_abancart_reminder)) `id_ets_abancart_reminder`
            , a.id_cart_rule `cart_rule_id`
        ';
        $shopAll = Shop::getContext() !== Shop::CONTEXT_ALL;
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_cart_rule = a.id_cart_rule)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` at ON (at.id_ets_abancart_tracking = d.id_ets_abancart_tracking)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = at.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign' . ($shopAll ? ' AND ac.id_shop=' . (int)$this->context->shop->id : '') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl ON (acl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND acl.id_lang=' . (int)$this->context->language->id . ')
            
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` dt ON (dt.id_cart_rule = a.id_cart_rule)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` at2 ON (at2.id_ets_abancart_display_tracking = dt.id_ets_abancart_display_tracking)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar2 ON (ar2.id_ets_abancart_reminder = at2.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl2 ON (arl2.id_ets_abancart_reminder = ar2.id_ets_abancart_reminder AND arl2.id_lang=' . (int)$this->context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac2 ON (ac2.id_ets_abancart_campaign = ar2.id_ets_abancart_campaign' . ($shopAll ? ' AND ac2.id_shop=' . (int)$this->context->shop->id : '') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl2 ON (acl2.id_ets_abancart_campaign = ac2.id_ets_abancart_campaign AND acl2.id_lang=' . (int)$this->context->language->id . ')
        ';
        $this->_where = 'AND ((ar.id_ets_abancart_reminder > 0 OR at.id_ets_abancart_reminder = -1) OR (at2.id_ets_abancart_reminder = 0 OR ar2.id_ets_abancart_reminder > 0))';

        $this->fields_list = [
            'id_cart_rule' => ['title' => $this->l('ID', 'AdminEtsACDiscountsController'), 'align' => 'center', 'class' => 'fixed-width-xs', 'filter_key' => 'a!id_cart_rule'],
            'cart_rule_name' => ['title' => $this->l('Name', 'AdminEtsACDiscountsController'), 'havingFilter' => true, 'filter_key' => 'cart_rule_name'],
            'reminder' => ['title' => $this->l('Reminder name', 'AdminEtsACDiscountsController'), 'havingFilter' => true, 'filter_key' => 'reminder'],
            'campaign' => ['title' => $this->l('Campaign name', 'AdminEtsACDiscountsController'), 'havingFilter' => true, 'filter_key' => 'campaign'],
            'priority' => ['title' => $this->l('Priority', 'AdminEtsACDiscountsController'), 'align' => 'center', 'class' => 'fixed-width-xs', 'filter_key' => 'a!priority'],
            'code' => ['title' => $this->l('Code', 'AdminEtsACDiscountsController'), 'class' => 'fixed-width-sm', 'filter_key' => 'a!code'],
            'quantity' => ['title' => $this->l('Quantity', 'AdminEtsACDiscountsController'), 'align' => 'center', 'class' => 'fixed-width-xs', 'filter_key' => 'a!quantity'],
            'date_to' => ['title' => $this->l('Expiration date', 'AdminEtsACDiscountsController'), 'type' => 'datetime', 'class' => 'fixed-width-lg', 'filter_key' => 'a!date_to'],
            'active' => ['title' => $this->l('Status', 'AdminEtsACDiscountsController'), 'active' => 'status', 'type' => 'bool', 'align' => 'center', 'class' => 'fixed-width-xs', 'orderby' => false, 'filter_key' => 'a!active'],
        ];
    }

    public function getHavingClause()
    {
        if (trim($this->_filterHaving) !== ''
            && preg_match('/\s+AND\s+`(reminder|campaign)`\s+LIKE\s+\'%(.+?)%\'/', $this->_filterHaving, $matches)
            && !empty($matches[2])
            && Validate::isUnsignedInt($matches[2])
        ) {
            $this->_filterHaving = preg_replace('/\s+AND\s+`(reminder|campaign)`\s+LIKE\s+\'%(.+?)%\'/', ' AND `id_ets_abancart_$1`=\'$2\' ', $this->_filterHaving);
        }
        return parent::getHavingClause();
    }

    public function ajaxProcessStatus()
    {
        if (Tools::isSubmit('status' . $this->table)) {
            $object = $this->loadObject();
            if (Validate::isLoadedObject($object)) {
                if (property_exists($object, 'active')) {
                    $object->active = !(int)$object->active;
                }
                if (!$object->update()) {
                    $this->errors[] = $this->l('An error occurred while updating the status.', 'AdminEtsACCampaignController');
                }
            } else {
                $this->errors[] = $this->l('An error occurred while updating the status for an object.', 'AdminEtsACCampaignController');
            }
            $hasError = count($this->errors) > 0;
            $this->toJson(array(
                'hasError' => $hasError,
                'enabled' => $object->active,
                'msg' => $hasError ? $this->module->displayError($this->errors) : $this->l('Update status successfully', 'AdminEtsACCampaignController'),
            ));
        }
    }

    public function setHelperDisplay(Helper $helper)
    {
        $this->bulk_actions = [];
        unset($this->toolbar_btn['new']);
        parent::setHelperDisplay($helper);
    }
}