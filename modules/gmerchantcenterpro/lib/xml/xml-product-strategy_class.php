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

require_once('base-product-strategy_class.php');

class BT_ProductXmlStrategy extends BT_BaseProductStrategy
{
    /**
     * store into the matching object the product and combination
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     * @return array
     */
    public function setProductData(&$oData, $oProduct, $aCombination)
    {
        $this->oCurrentProd->setProductData($oData, $oProduct, $aCombination);
    }


    /**
     * construct the XML content
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     */
    public function buildProductXml($oData, $oProduct, $aCombination)
    {
        // load the product and combination into the matching object
        $this->setProductData($oData, $oProduct, $aCombination);

        // build the common and specific part between different type of export
        if ($this->oCurrentProd->buildProductXml()) {
            if (!empty($this->bOutput)) {
                echo $this->oCurrentProd->buildXmlTags();
            } else {
                $this->sContent .= $this->oCurrentProd->buildXmlTags();
            }

            if ($this->oCurrentProd->hasProductProcessed()) {
                $this->iCounter++;
            }
        }
    }


    /**
     * creates singleton
     *
     * @param string $sType
     * @param array $aParams
     * @return obj
     */
    public static function create($sType, array $aParams = array())
    {
        static $oXml;

        if (null === $oXml) {
            $oXml = new BT_ProductXmlStrategy($sType, $aParams);
        }
        return $oXml;
    }
}
