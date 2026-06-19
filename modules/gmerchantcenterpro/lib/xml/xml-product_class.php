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

class BT_XmlProduct extends BT_BaseProductXml
{
    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        parent::__construct($aParams);
    }

    /**
     * load products combination
     *
     * @param int $iProductId
     * @param bool $bExcludedProduct
     * @return array
     */
    public function hasCombination($iProductId, $bExcludedProduct = false)
    {
        return array($iProductId);
    }

    /**
     * build product XML tags
     *
     * @return array
     */
    public function buildDetailProductXml()
    {
        // get weight
        $this->data->step->weight = (float)$this->data->p->weight;

        // handle different prices and shipping fees
        $this->data->step->price_default_currency_no_tax = Tools::convertPrice(Product::getPriceStatic((int)$this->data->p->id, false, null), $this->data->currency, false);

        // Exclude based on min price
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_MIN_PRICE'])
            && ((float)$this->data->step->price_default_currency_no_tax < (float)GMerchantCenterPro::$conf['GMCP_MIN_PRICE'])
        ) {
            BT_GmcProReporting::create()->set('_no_export_min_price', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // Exclude based on max weight
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_MAX_WEIGHT'])
            && ((float)$this->data->step->weight > (float)GMerchantCenterPro::$conf['GMCP_MAX_WEIGHT'])
        ) {
            BT_GmcProReporting::create()->set('_no_export_max_weight', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // handle both price and discounted price
        if (isset($this->aParams['bUseTax'])) {
            $bUseTax = !empty($this->aParams['bUseTax']) ? true : false;
        } else {
            $bUseTax = true;
        }
        $this->data->step->price_raw = Product::getPriceStatic((int)$this->data->p->id, $bUseTax, null, 6);
        $this->data->step->price_raw_no_discount = Product::getPriceStatic((int)$this->data->p->id, $bUseTax, null, 6, null, false, false);
        $this->data->step->price = number_format(BT_GmcProModuleTools::round($this->data->step->price_raw), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        $this->data->step->price_no_discount = number_format(BT_GmcProModuleTools::round($this->data->step->price_raw_no_discount), 2, '.', '') . ' ' . $this->data->currency->iso_code;

        // Use case override the price with the pack price
        if (
            GMerchantCenterPro::$bAdvancedPack && AdvancedPack::isValidPack($this->data->p->id)
        ) {
            $oPack = new AdvancedPack($this->data->p->id);
            $this->data->step->price_raw_no_discount = AdvancedPack::getPackPrice($oPack->id, $bUseTax, false);
            $this->data->step->price_raw = AdvancedPack::getPackPrice($oPack->id);
            $this->data->step->price_no_discount = AdvancedPack::getPackPrice($oPack->id, $bUseTax, false);
            $this->data->step->price = AdvancedPack::getPackPrice($oPack->id);
        }

        // Available date
        $this->data->step->availabilty_date = "";

        if ($this->data->p->available_date != "0000-00-00") {
            $this->data->step->availabilty_date = $this->data->p->available_date;
        }

        // Cost price
        if (!empty((int)$this->data->p->wholesale_price)) {
            $this->data->step->cost_price = number_format(BT_GmcProModuleTools::round($this->data->p->wholesale_price), 2, '.', '') . ' ' . $this->data->currency->iso_code;
        }

        // shipping fees
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_SHIPPING_USE'])
            && empty($this->aParams['sFreeShipping'][$this->data->p->id])
        ) {
            $fPrice = number_format((float)$this->getProductShippingFees((float)BT_GmcProModuleTools::round($this->data->step->price_raw)), 2, '.', '');
        } else {
            $fPrice = number_format((float)0, 2, '.', '');
        }
        $this->data->step->shipping_fees = $fPrice . ' ' . $this->data->currency->iso_code;

        // get images
        $this->data->step->images = $this->getImages($this->data->p);

        // quantity
        // Do not export if the quantity is 0 for the combination and export out of stock setting is not On
        if (
            (int)$this->data->p->quantity < 1
            && (int)GMerchantCenterPro::$conf['GMCP_EXPORT_OOS'] == 0
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_stock', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // quantity
        $this->data->step->quantity = (int)$this->data->p->quantity;

        //Manage GTIN code
        $this->data->step->gtin = BT_GmcProModuleTools::getGtin(GMerchantCenterPro::$conf['GMCP_GTIN_PREF'], (array)$this->data->p);

        // Exclude without EAN
        if (
            GMerchantCenterPro::$conf['GMCP_EXC_NO_EAN']
            && empty($this->data->step->gtin)
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_ean_upc', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        // supplier reference
        $this->data->step->mpn = $this->getSupplierReference($this->data->p->id, $this->data->p->id_supplier, $this->data->p->supplier_reference, $this->data->p->reference);

        // exclude if mpn is empty
        if (
            !empty(GMerchantCenterPro::$conf['GMCP_EXC_NO_MREF'])
            && !GMerchantCenterPro::$conf['GMCP_INC_ID_EXISTS']
            && empty($this->data->step->mpn)
        ) {
            BT_GmcProReporting::create()->set('_no_export_no_supplier_ref', array('productId' => $this->data->step->id_reporting));
            return false;
        }

        //handle the specific price feature
        if (!empty($this->data->p->specificPrice['from'])) {
            $this->data->step->specificPriceFrom = $this->data->p->specificPrice['from'];
        }
        if (!empty($this->data->p->specificPrice['to'])) {
            $this->data->step->specificPriceTo = $this->data->p->specificPrice['to'];
        }

        $this->data->step->visibility = $this->data->p->visibility;

        if ($this->data->p->minimal_quantity > 1) {
            $this->data->multipack = $this->data->p->minimal_quantity;
        } else {
            $this->data->multipack = 0;
        }

        if (!empty(GMerchantCenterPro::$conf['GMCP_DIMENSION'])) {
            $aDataDimension = BT_GmcProModuleTools::getDimension($this->data->p->width, $this->data->p->height, $this->data->p->depth);
            if (!empty($aDataDimension)) {
                $this->data->step->shipping_width =  $aDataDimension['shipping_width'];
                $this->data->step->shipping_height = $aDataDimension['shipping_height'];
                $this->data->step->shipping_length  = $aDataDimension['shipping_length'];
            }
        }

        return true;
    }

    /**
     * format the product name
     *
     * @param int $iAdvancedProdName
     * @param int $iAdvancedProdTitle
     * @param string $sProdName
     * @param string $sCatName
     * @param string $sManufacturerName
     * @param int $iLength
     * @param int $iProdAttrId
     * @param int $iLangId
     * @return string
     */
    public function formatProductName($iAdvancedProdName, $iAdvancedProdTitle, $sProdName, $sCatName, $sManufacturerName, $iLength, $iProdAttrId = null, $iLangId = null, $sPrefix = null, $sSuffix = null)
    {
        $sProdName = BT_GmcProModuleTools::truncateProductTitle($iAdvancedProdName, $sProdName, $sCatName, $sManufacturerName, $iLength, $this->aParams['iLangId'], $sPrefix, $sSuffix);

        return BT_GmcProModuleTools::formatProductTitle($sProdName, $iAdvancedProdTitle);
    }

    /**
     * get images of one product or one combination
     *
     * @param obj $oProduct
     * @param int $iProdAttributeId
     * @return array
     */
    public function getImages(Product $oProduct, $iProdAttributeId = null)
    {
        // set vars
        $aResultImages = array();
        $iCounter = 1;

        // get cover
        $aImage = Product::getCover($oProduct->id);

        // Additional images
        $aOtherImages = $oProduct->getImages(GMerchantCenterPro::$iCurrentLang);
        if (!empty($aOtherImages) && is_array($aOtherImages)) {
            foreach ($aOtherImages as $aImg) {
                if ((int)$aImg['id_image'] != (int)$aImage['id_image'] && $iCounter <= _GMCP_IMG_LIMIT && $aImg['cover'] != 1) {
                    $aResultImages[] = array('id_image' => (int)$aImg['id_image']);
                    $iCounter++;
                }
            }
        }

        return array('image' => $aImage, 'others' => $aResultImages);
    }

    /**
     * get supplier reference
     *
     * @param int $iProdId
     * @param int $iSupplierId
     * @param string $sSupplierRef
     * @param string $sProductRef
     * @param int $iProdAttributeId
     * @param string $sCombiSupplierRef
     * @param string $sCombiRef
     * @return string
     */
    public function getSupplierReference($iProdId, $iSupplierId, $sSupplierRef = null, $sProductRef = null, $iProdAttributeId = 0, $sCombiSupplierRef = null, $sCombiRef = null)
    {
        if (empty(GMerchantCenterPro::$bCompare1770)) {
            // detect the MPN type
            $sReturnRef = BT_GmcProModuleDao::getProductSupplierReference($iProdId, $iSupplierId);
        } else {
            $oProduct = new Product($iProdId);
            $sReturnRef = $oProduct->mpn;
        }

        return $sReturnRef;
    }
}
