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

include_once(_CDCGTM_DIR_.'/classes/CdcGtmDataLayer.php');
include_once(_CDCGTM_DIR_.'/cdc_googletagmanager.php');

class AdminCdcGoogletagmanagerDatalayerController extends ModuleAdminController
{

	protected $statuses_array = array();
	protected $shop_id = null;

	/**
	 * 
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = CdcGtmDataLayer::$definition['table'];
		$this->identifier = CdcGtmDataLayer::$definition['primary'];
		$this->className = 'CdcGtmDataLayer';
		$this->lang = false;
		$this->addRowAction('view');
		$this->explicitSelect = true;
		$this->allow_export = true;
		$this->deleted = false;
		$this->context = Context::getContext();

		parent::__construct();

		$this->_orderWay = 'DESC';
		$this->_orderBy = CdcGtmDataLayer::$definition['primary'];


		$this->fields_list = array(
			'id_cdc_gtm_datalayer' => array(
				'title' => $this->l('ID'),
				'align' => 'text-center',
				'class' => 'fixed-width-xs'
			),
			'event' => array(
				'title' => $this->l('Event'),
				'align' => 'text-center'
			),
            'uri' => array(
                'title' => $this->l('URI'),
                'align' => 'text-center'
            ),
			'date_add' => array(
				'title' => $this->l('Date'),
				'type' => 'datetime',
			),
		);

        if (Shop::isFeatureActive()) {
            $this->fields_list['id_shop'] = array(
                'title' => $this->l('Shop'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
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
		$id = (int)Tools::getValue('id_cdc_gtm_datalayer');
        $cdcGtmDataLayer = new CdcGtmDataLayer($id);

		if (!Validate::isLoadedObject($cdcGtmDataLayer)) {
            $cdcGtmDataLayer = null;
        }

		// display view
		$this->context->smarty->assign(array(
			'gtm_datalayer' => $cdcGtmDataLayer,
            'link_cdcgtm_datalayer_logs' => Context::getContext()->link->getAdminLink('AdminCdcGoogletagmanagerDatalayer')
		));
		return parent::renderView();
	}



}
