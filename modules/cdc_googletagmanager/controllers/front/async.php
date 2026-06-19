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

if (!defined('_PS_VERSION_')) { exit; }

class cdc_googletagmanagerAsyncModuleFrontController extends ModuleFrontController
{
	private $dataLayer = null;
	private $cdc_gtm = null;

	public function __construct()
	{
		// if page is called in https, force ssl
		if (Tools::usingSecureMode()) {
			$this->ssl = true;
		}
		return parent::__construct();
	}


	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
	    $action = Tools::getValue('action');
	    if(!empty($action)) {
            $this->cdc_gtm = new cdc_googletagmanager();
            $this->dataLayer = new Gtm_DataLayer($this->cdc_gtm);

	        switch ($action) {
                case 'user':
                    $this->dataLayer = $this->cdc_gtm->addUserInfosToDatalayer($this->dataLayer);
                    break;
                case 'cart-add':
                case 'cart-remove':
                    $this->dataLayer = $this->cdc_gtm->getDataLayerCartAction(
                        (int) Tools::getValue('id'),
                        (int) Tools::getValue('id_attribute'),
                        $action == 'cart-remove' ? 'remove' : 'add',
                        (int) Tools::getValue('qtity')
                    );
                    break;
                case 'product-click':
                    $this->dataLayer = $this->cdc_gtm->productClick(
                        (int) Tools::getValue('id'),
                        (int) Tools::getValue('id_attribute')
                    );
                    break;
                case 'category-display':
                    $id_products = json_decode(Tools::getValue('id_products'));
                    if(!empty($id_products)) {
                        $this->dataLayer = $this->cdc_gtm->displayCategoryAsync($id_products);
                    }
                    break;
            }
        }
	}


	public function display()
	{
	    if($this->dataLayer) {
	        $dataLayerJs = $this->dataLayer->toJson();

            // datalayer debug backup
            $this->cdc_gtm->logDataLayerInDb($this->dataLayer->event, $dataLayerJs);

            echo $dataLayerJs;
        } else {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            Tools::redirect(Context::getContext()->link->getBaseLink(), null, null, [$protocol . ' 301 Moved Permanently']);
            exit();
        }
	}

}