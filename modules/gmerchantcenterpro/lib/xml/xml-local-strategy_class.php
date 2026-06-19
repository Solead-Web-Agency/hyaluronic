<?php

/**
 * xml-product-strategy_class.php file defines method to manage XML files
 */

require_once('base-product-strategy_class.php');

class BT_LocalXmlStrategy extends BT_BaseProductStrategy
{
    /**
     * @var bool $bProductProcess : define if the product has well added
     */
    protected $bProductProcess = false;

    /**
     * hasProductProcessed() method define if the current product has been processed or refused for some not requirements matching
     *
     * @return bool
     */
    public function hasProductProcessed()
    {
        return $this->bProductProcess;
    }

    /**
     * setProductData() method store into the matching object the product and combination
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     * @return array
     */
    public function setProductData(&$oData, $oProduct, $aCombination)
    {
        $this->data->p = $oProduct;
        $this->data->c = $aCombination;
    }


    /**
     * buildProductXml() method construct the XML content
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     */
    public function buildProductXml($oData, $oProduct, $aCombination)
    {
        // load the product and combination into the matching object
        $this->setProductData($oData, $oProduct, $aCombination);

        // handle both price and discounted price
        if (isset($this->aParams['bUseTax'])) {
            $bUseTax = !empty($this->aParams['bUseTax']) ? true : false;
        } else {
            $bUseTax = true;
        }
        $this->data->p->price_raw = Product::getPriceStatic((int)$this->data->p->id, $bUseTax, null, 6);
        $this->data->p->price_raw_no_discount = Product::getPriceStatic((int)$this->data->p->id, $bUseTax, null, 6, null, false, false);
        $this->data->p->price = number_format(BT_GmcProModuleTools::round($this->data->p->price_raw), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        $this->data->p->price_no_discount = number_format(BT_GmcProModuleTools::round($this->data->p->price_raw_no_discount), 2, '.', '') . ' ' . $this->data->currency->iso_code;

        $sContent = '';

        $sContent .= "\t" . '<item>' . "\n";

        if (!empty(GMerchantCenterPro::$conf['GMCP_STORE_CODE'])) {
            $sContent .= "\t\t" . '<g:store_code>' . GMerchantCenterPro::$conf['GMCP_STORE_CODE'] . '</g:store_code>' . "\n";
        }

        if (!empty(GMerchantCenterPro::$conf['GMCP_P_COMBOS'])) {

            if (!empty($this->data->c['id_product_attribute'])) {
                if (empty(GMerchantCenterPro::$conf['GMCP_SIMPLE_PROD_ID'])) {
                    $sContent .= "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->p->id . 'v' . $this->data->c['id_product_attribute'] . '</g:id>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:id>' . $this->data->p->id . 'v' . $this->data->c['id_product_attribute'] . '</g:id>' . "\n";
                }
            } else {
                if (empty(GMerchantCenterPro::$conf['GMCP_SIMPLE_PROD_ID'])) {
                    $sContent .= "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->p->id . '</g:id>' . "\n";
                } else {
                    $sContent .= "\t\t" . '<g:id>'  . $this->data->p->id . '</g:id>' . "\n";
                }
            }
        } else {
            $sContent .= "\t\t" . '<g:id>' . Tools::strtoupper(GMerchantCenterPro::$conf['GMCP_ID_PREFIX']) . $this->aParams['sCountryIso'] . $this->data->p->id . '</g:id>' . "\n";
        }

        if ($this->data->p->price_raw < $this->data->p->price_raw_no_discount) {
            $sContent .= "\t\t" . '<g:price>' . $this->data->p->price_no_discount . '</g:price>' . "\n";
            $sContent .= "\t\t" . '<g:sale_price>' . $this->data->p->price . '</g:sale_price>' . "\n";
        } else {
            $sContent .= "\t\t" . '<g:price>' . $this->data->p->price . '</g:price>' . "\n";
        }

        $iQty = is_array($this->data->c) ? $this->data->c['combo_quantity'] : $this->data->p->quantity;
        $sContent .= "\t\t" . '<g:quantity>' . $iQty . '</g:quantity>' . "\n";

        if ($iQty > 0) {
            $sContent .= "\t\t" . '<g:availability> in stock </g:availability>' . "\n";
        } else {
            $sContent .= "\t\t" . '<g:availability>out of stock</g:availability>' . "\n";
        }

        if (!empty(GMerchantCenterPro::$conf['GMCP_LIA_PICKUP'])) {
            $sContent .= "\t\t" . '<g:pickup_method>' . GMerchantCenterPro::$conf['GMCP_LIA_PICKUP'] . '</g:pickup_method>' . "\n";
        }

        if (!empty(GMerchantCenterPro::$conf['GMCP_LIA_PICKUP_SLA'])) {
            $sContent .= "\t\t" . '<g:pickup_sla>' . GMerchantCenterPro::$conf['GMCP_LIA_PICKUP_SLA'] . '</g:pickup_sla>' . "\n";
        }

        $sContent .= "\t" . '</item>' . "\n";

        // increase counter
        $this->iCounter++;

        // manage output parameters
        if (!empty($this->bOutput)) {
            echo $sContent;
        } else {
            return $this->sContent .= $sContent;
        }
    }


    /**
     * create() method creates singleton
     *
     * @param string $sType
     * @param array $aParams
     * @return obj
     */
    public static function create($sType, array $aParams = array())
    {
        static $oXml;

        if (null === $oXml) {
            $oXml = new BT_LocalXmlStrategy($sType, $aParams);
        }
        return $oXml;
    }
}
