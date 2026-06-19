<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

class BT_XmlGenerateProduct
{
    /**
     * @var array $aParamsForXml : array for all parameters provided to generate XMl files
     */
    protected static $aParamsForXml = array();

    /**
     * @param array $aParams
     */
    public function __construct($sType = null, $aParams){}

    /**
     * generate get the XML for current data feed type
     */
    public function generate(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB_XML . 'base-product-strategy_class.php');

        // detect the floor step
        $iFloor = Tools::getValue('iFloor');

        if ($iFloor == 0) {
            // to force check
            require_once(_GMCP_PATH_LIB_ADMIN . 'admin-update_class.php');

            $oUpdate = BT_AdminUpdate::create();
            $oUpdate->run('customLabelDate');
            $oUpdate->run('customCheck');
        }

        return BT_BaseProductStrategy::get('product', array('type' => 'product'))->generate(array('reporting' => Tools::getValue('bReporting')));
    }
}
