<?php

/**
 * Google Merchant Center
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class GsaProducts extends GsaBase
{
	/**
	 * execute action
	 *
	 * @param array $aParams
	 * @return array
	 */
	public function run(array $aParams = null)
	{
		require_once(_GMC_PATH_CONF . 'admin.conf.php');
		require_once(_GMC_PATH_LIB_GSA . 'gsa-client_class.php');
		require_once(_GMC_PATH_LIB_GSA . 'gsa-tools_class.php');
		require_once(_GMC_PATH_LIB_GSA . 'gsa-dao_class.php');

		// set variables
		$aActions = array();
		switch ($this->sAction) {
			case 'checkProductTest':
				// use case - check if the product test alreayd exist
				$aActions = call_user_func_array(array($this, 'checkProductTest'), array($aParams));
				break;
			case 'createProductTest':
				// use case - create test product
				$aActions = call_user_func_array(array($this, 'createProductTest'), array($aParams));
				break;
			default:
				break;
		}

		return $aActions;
	}

	/**
	 * Check the product test required for Google shopping integration
	 *
	 * @param array $aParams
	 * @return array
	 */
	private function checkProductTest(array $aParams = null)
	{
		$aProductOutput = array();

		// Loop on produc test received from Octopus and chek if the product already exist on the shop
		foreach ($aParams['data'] as $data) {

			// Use case if we have unmacthing data we store it to send it to the API
			if (empty(BT_GmcGsaDao::getProductIdFromGsa($data['gtin'], $data['mpn']))) {
				$aProductOutput[] =  array('name' => $data['name'], 'gtin' => $data['gtin'], 'mpn' => $data['mpn'], 'price' => $data['price']);
			}
		}

		// Send the product data from the shop
		$aProductData = array(
			'products' => $aProductOutput,
		);

		return $aProductData;
	}


	/**
	 * Create the product on the shop if we need 
	 *
	 * @param array $aParams
	 * @return array
	 */
	private function createProductTest(array $aParams = null)
	{
		$bCreated = false;
		$aProductOutput = array();

		// Use case if we have product to create
		if (!empty($aParams['data'])) {
			// Loop on product to create
			foreach ($aParams['data'] as $product) {

				$oProduct = new Product();
				$oProduct->reference = $product['mpn'];
				$oProduct->ean13 = $product['gtin'];
				$oProduct->active = 0;
				$oProduct->price = (float) $product['price'];
				// Force with no taxe because the product test are generated from USA 
				$oProduct->id_tax_rules_group = 0;

				// Use case for PS 1.6 
				if (empty(GMerchantCenter::$bCompare17)) {
					$oProduct->name =  array((int) (Configuration::get('PS_LANG_DEFAULT')) => $product['name']);
					$oProduct->link_rewrite = array((int) Configuration::get('PS_LANG_DEFAULT') =>  Tools::str2url($product['name']));
				} else {
					$oProduct->name = $product['name'];
				}
				

				// Add the product
				if ($oProduct->add()) {
					// Add stock for the test
					if (StockAvailable::updateQuantity((int) $oProduct->id, 0, 99, GMerchantCenter::$iShopId)) {

						// Set all product values created to return it on dashboard
						$aProductOutput[] = array('id' => (int) $oProduct->id, 'name' => $oProduct->name, 'reference' => $oProduct->reference, 'ean' => $oProduct->ean13);
						$bCreated = true;
					}
				}
			}
		}

		// Set the return data for dashboard if the product are created
		$aProductData = array(
			'products' => !empty($bCreated) ? $aProductOutput : array(),
		);

		return $aProductData;
	}
}
