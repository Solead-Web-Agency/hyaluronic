<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('_CDCGTM_DIR_'))
	define('_CDCGTM_DIR_', dirname(__FILE__).'/../..');

include_once(_CDCGTM_DIR_.'/classes/CdcGtmOrderLog.php');
include_once(_CDCGTM_DIR_.'/cdc_googletagmanager.php');

class AdminCdcGoogletagmanagerOrdersController extends ModuleAdminController
{

	protected $statuses_array = array();
	protected $shop_id = null;

	/**
	 * 
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = CdcGtmOrderLog::$definition['table'];
		$this->identifier = CdcGtmOrderLog::$definition['primary'];
		$this->className = 'CdcGtmOrderLog';
		$this->lang = false;
		$this->addRowAction('view');
		$this->explicitSelect = true;
		$this->allow_export = true;
		$this->deleted = false;
		$this->context = Context::getContext();

		parent::__construct();

		$this->_orderWay = 'DESC';
		$this->_orderBy = 'o.id_order';
		$this->_join = '
			LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_order = a.id_order)
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = o.`current_state`)
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$this->context->language->id.')';
		/*$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'orders` sav ON (sav.`id_product` = a.`id_ps_product` AND sav.`id_product_attribute` = 0
		'.StockAvailable::addSqlShopRestriction(null, null, 'sav').') ';*/
		$this->_select = '
		    IF(a.datalayer = \'\', \'x\', 1) AS datalayerExists,
		    IF(a.datalayer = \'\', "#aa0000", "") AS color_datalayerExists,
			a.sent,	IF(a.sent, 1, 0) badge_success,
			a.resent, IF(a.resent > 0, "#aa00aa", "") color_resent,
			a.refund, IF(a.refund = "all", "#ff0000", IF(a.refund != "", "#ff9900", "")) color_refund,
			osl.`name` AS `osname`,
			os.`color` AS `oscolor`,';

		if(Shop::isFeatureActive()) {
			$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'shop` sh ON (sh.`id_shop` = o.`id_shop`)';
			$this->_select .= ' sh.name as shop_name,';
		}

		$statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

		$this->fields_list = array(
			'id_order' => array(
				'title' => $this->l('Order ID'),
				'align' => 'text-center',
				'filter_key' => 'o!id_order',
				'class' => 'fixed-width-xs'
			),
			'reference' => array(
				'title' => $this->l('Reference'),
				'align' => 'text-center'
			),
			'id_cdc_gtm_order_log' => array(
				'title' => $this->l('GTM ID'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs'
			),
            'datalayerExists' => array(
                'title' => $this->l('Dl OK'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'orderby' => false,
                'search' => false,
                'color' => 'color_datalayerExists'
            ),
			'sent' => array(
				'title' => $this->l('Sent'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs',
				'orderby' => false,
				'badge_success' => true
			),
			'resent' => array(
				'title' => $this->l('Re-sent'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs',
				'orderby' => false,
				'color' => 'color_resent'
			),
			'refund' => array(
				'title' => $this->l('Refund'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs',
				'orderby' => false,
				'color' => 'color_refund'
			),
			'total_paid_tax_incl' => array(
				'title' => $this->l('Total'),
				'align' => 'text-right',
				'class' => 'fixed-width-sm',
				'type' => 'price',
				'currency' => true
			),
			'payment' => array(
				'title' => $this->l('Payment'),
				'align' => 'text-center'
			),
			'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'oscolor',
                'list' => $this->statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
			'order_date_add' => array(
				'title' => $this->l('Date order'),
				'type' => 'datetime',
				'filter_key' => 'o!date_add'
			),
			'gtm_date_upd' => array(
				'title' => $this->l('Last Update GTM'),
				'type' => 'datetime',
				'filter_key' => 'a!date_upd'
			),
		);

        if (Shop::isFeatureActive()) {
            $this->fields_list['shop_name'] = array(
                'title' => $this->l('Shop'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'filter_key' => 'sh!name'
            );
        }
	}


	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
	}


	public function renderView()
	{
		$id_gtm_order_log = (int)Tools::getValue('id_cdc_gtm_order_log');
		$gtm_order_log = new CdcGtmOrderLog($id_gtm_order_log);
        $this->context->smarty->assign(array(
            'link_cdcgtm_order_logs' => Context::getContext()->link->getAdminLink('AdminCdcGoogletagmanagerOrders')
        ));

		if (!Validate::isLoadedObject($gtm_order_log)) {

            // display view
            $this->context->smarty->assign(array(
                'gtm_order_log' => null
            ));
            return parent::renderView();
        }


		// handle force resend order
		$force_resend = false;
		if(Tools::getIsset('force_resend') && !$gtm_order_log->resent) {
			cdc_googletagmanager::addOrderResend($gtm_order_log->id_order, $gtm_order_log->id_shop);
			$force_resend = true;
		}

        // handle action create datalayer
        $action_created = false;
        if(Tools::getIsset('cdcGtm_createDlBo')) {
            // this param is handled by the function 'recreateOrderConfirmationFromBO'
            // in the main module file
            $action_created = true;
        }

		// display view
		$this->context->smarty->assign(array(
			'gtm_order_log' => $gtm_order_log,
			'action_resend' => Context::getContext()->link->getAdminLink('AdminCdcGoogletagmanagerOrders')
				."&id_cdc_gtm_order_log=".$id_gtm_order_log."&viewcdc_gtm_order_log&force_resend",
            'action_create' => Context::getContext()->link->getAdminLink('AdminCdcGoogletagmanagerOrders')
                ."&id_cdc_gtm_order_log=".$id_gtm_order_log."&order_id=".$gtm_order_log->id_order."&viewcdc_gtm_order_log&cdcGtm_createDlBo",
			'force_resend' => $force_resend,
            'action_created' => $action_created
		));
		return parent::renderView();
	}



}
