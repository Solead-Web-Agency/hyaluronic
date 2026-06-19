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

if (!defined('_CDCGTM_DIR_'))
    define('_CDCGTM_DIR_', dirname(__FILE__).'/../..');

include_once(_CDCGTM_DIR_.'/classes/AbstractDataLayerObject.php');

/**
 * Represent GTM Ecommerce
 */
class Gtm_Ecommerce extends AbstractDataLayerObject
{
    public $currency;
    public $value;
    public $item_list_id;
    public $item_list_name;
    public $items;
    public $total_tax_exc;
    public $total_tax_inc;
    public $tax;
    public $products;
    public $products_tax_exc;
    public $shipping;
    public $shipping_tax_exc;
    public $discounts;
    public $discounts_tax_exc;
    public $refund;


    /**
     * Gtm_Ecommerce constructor.
     * @param Gtm_DataLayer $datalayer
     * @param null $currencyCode
     */
	public function __construct($currencyCode = null)
	{
        $this->currency = $currencyCode;
    }

}
