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

require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

class BT_GmcpLabelTools
{
    /**
     * Handle the check and insert for basics and dynamic product custom label
     * 
     * @param int $iTagId 
     * @param string $sLabelType
     * @param array $aSpecificProducts
     */
    public static function handleDefautTag($iTagId, $sLabelType, $aSpecificProducts = array())
    {
        foreach ($GLOBALS['GMCP_LABEL_LIST'] as $sTableName => $sFieldType) {
            if (Tools::getIsset('bt_' . $sFieldType . '-box')) {
                $aSelectedIds = Tools::getValue('bt_' . $sFieldType . '-box');
                foreach ($aSelectedIds as $iSelectedId) {
                    BT_GmcProCustomLabelDao::insertGmcCatTag($iTagId, $iSelectedId, $sTableName, $sFieldType, $sLabelType);
                }
            }
        }
        if (!empty($aSpecificProducts)) {

            foreach ($aSpecificProducts as $key => $aProduct) {
                $oProduct = new Product((int) $aProduct, true, GMerchantCenterPro::$iCurrentLang);

                if (Validate::isLoadedObject($oProduct)) {

                    $sProductName = $oProduct->name;
                    BT_GmcProCustomLabelDao::insertGmcpProductTag($iTagId, (int) $aProduct, $sProductName);
                }
            }
        }
    }

    /**
     * Handle the check and insert custom label based on feature
     * 
     * @param int $iTagId
     * @param int $iFeatureId 
     */
    public static function handleFeatureTag($iTagId, $iFeatureId)
    {
        BT_GmcProCustomLabelDao::insertGmcpDynFeatureTag($iTagId, $iFeatureId);
    }

    /**
     * Handle the check and insert custom label based on dynmamic cat
     * 
     * @param int $iTagId
     * @param array $aCategories
     */
    public static function handleCatDynmaicTag($iTagId, $aCategories)
    {
        foreach ($aCategories as $iSelectedId) {
            BT_GmcProCustomLabelDao::insertDynamicCat($iTagId, $iSelectedId);
        }
    }

    /**
     * Handle the check and insert of new product for custom label dynamic
     * 
     * @param int $iTagId 
     * @param string $sNewProductDate 
     */
    public static function handleDynamicNewProduct($iTagId, $sNewProductDate)
    {
        $aProductIds = BT_GmcProCustomLabelDao::getNewProducts($sNewProductDate);
        $aSelectedIds = array();
        $aProductCategories = array();

        if (!empty($aProductIds)) {
            foreach ($aProductIds as $aProduct) {
                foreach ($GLOBALS['GMCP_LABEL_LIST'] as $sTableName => $sFieldType) {
                    if (Tools::getIsset('bt_' . $sFieldType . '-box')) {
                        $aSelectedIds[$sFieldType] = Tools::getValue('bt_' . $sFieldType . '-box');
                    }
                }

                // Use case when an element is 
                if (!empty($aSelectedIds)) {
                    // Loop on selected element to extract the assocation
                    foreach ($aSelectedIds as $key => $aItesmSelect) {
                        if ($key == 'category') {
                            $aProductCategories = Product::getProductCategories((int)$aProduct['id_product']);
                            foreach ($aItesmSelect as $item) {
                                if (!empty($item)) {
                                    // Check  if the category is one category of the product 
                                    if (in_array($item, $aProductCategories)) {
                                        BT_GmcProCustomLabelDao::insertDynamicNew($iTagId, $sNewProductDate, $aProduct['id_product']);
                                    }
                                }
                            }
                        }
                        if ($key == 'supplier') {
                            $oProduct = new Product($aProduct['id_product']);
                            // Loop and get the product associated to a supllier
                            foreach ($aItesmSelect as $item) {
                                if ($item == $oProduct->id_supplier) {
                                    BT_GmcProCustomLabelDao::insertDynamicNew($iTagId, $sNewProductDate, $aProduct['id_product']);
                                }
                            }
                        }
                        if ($key == 'brand') {
                            $oProduct = new Product($aProduct['id_product']);
                            // Loop and get the product associated to a manufacturer
                            foreach ($aItesmSelect as $item) {
                                if ($item == $oProduct->id_manufacturer) {
                                    BT_GmcProCustomLabelDao::insertDynamicNew($iTagId, $sNewProductDate, $aProduct['id_product']);
                                }
                            }
                        }
                    }
                } else {
                    BT_GmcProCustomLabelDao::insertDynamicNew($iTagId, $sNewProductDate, $aProduct['id_product']);
                }
            }
        } else {
            $aAssign['aErrors'][] = array('msg' => $GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'][GMerchantCenterPro::$sCurrentLang], 'code' => '100');
        }
    }

    /**
     * Handle the check and insert best sales for the custom label
     * 
     * @param int $iTagId 
     * @param string $sBestSaleType 
     * @param float $fBestSaleAmount
     * @param string $sBestSaleStartDate 
     * @param string $sBestSalesEndDate 
     */
    public static function handleDynamicBestSales($iTagId, $sBestSaleType, $fBestSaleAmount, $sBestSaleStartDate, $sBestSalesEndDate)
    {
        //getProductIds for selected parameters in best sales form
        $aProductIds = BT_GmcProCustomLabelDao::getProductBestSales($sBestSaleType, $fBestSaleAmount, $sBestSaleStartDate, $sBestSalesEndDate);

        if (!empty($aProductIds)) {
            foreach ($aProductIds as $aProduct) {
                if (!empty($aProduct['product_id'])) {
                    BT_GmcProCustomLabelDao::insertDynamicBestSales($iTagId, $fBestSaleAmount, $sBestSaleType, $sBestSaleStartDate, $sBestSalesEndDate, $aProduct['product_id']);
                } elseif (!empty($aProduct['id_product'])) {
                    BT_GmcProCustomLabelDao::insertDynamicBestSales($iTagId, $fBestSaleAmount, $sBestSaleType, $sBestSaleStartDate, $sBestSalesEndDate, $aProduct['id_product']);
                }
            }
        } else {
            $aAssign['aErrors'][] = array('msg' => $GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'][GMerchantCenterPro::$sCurrentLang], 'code' => '');
        }
    }

    /**
     * Handle the check and insert best sales for the custom label
     * 
     * @param int $iTagId 
     * @param float $fPriceMin
     * @param float $fPriceMax 
     */
    public static function handleDynamicPriceRange($iTagId, $fPriceMin, $fPriceMax)
    {
        // Get product according to the re
        $aProductIds = BT_GmcProCustomLabelDao::getPriceRangeProduct($fPriceMin, $fPriceMax);

        if (!empty($aProductIds)) {
            foreach ($aProductIds as $aProduct) {
                BT_GmcProCustomLabelDao::insertDynamicPriceRange($iTagId, $fPriceMin, $fPriceMax, $aProduct['id_product']);
            }
        } else {
            $aAssign['aErrors'][] = array('msg' => $GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'][GMerchantCenterPro::$sCurrentLang], 'code' => '');
        }
    }

    /**
     * Handle the check and insert last ordered product label
     * 
     * @param int $iTagId 
     * @param string $sLastOrderedStart 
     * @param string $sLastOrderedEnd  
     */
    public static function handleDynamicLastOrdered($iTagId, $sLastOrderedStart, $sLastOrderedEnd)
    {
        $aOrders = Order::getOrdersIdByDate($sLastOrderedStart, $sLastOrderedEnd);
        // Loop on orders for the available period
        foreach ($aOrders as $iOrderId) {

            $oOrder = new Order((int)$iOrderId);
            $aOrderDetails = $oOrder->getProducts(false, false, false, false);

            foreach ($aOrderDetails as $aDetails) {
                $aProductIds[] = $aDetails['product_id'];
            }
        }

        if (!empty($aProductIds)) {

            //Removed duplicate values 
            $aProductIds = array_unique($aProductIds);

            foreach ($aProductIds as $iProductId) {
                BT_GmcProCustomLabelDao::insertDynamicLastProductOrdered($iTagId, $sLastOrderedStart, $sLastOrderedEnd, $iProductId);
            }
        } else {
            $aAssign['aErrors'][] = array('msg' => $GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'][GMerchantCenterPro::$sCurrentLang], 'code' => '');
        }
    }

    /**
     * Handle the check and insert of promotion for the custom label
     * 
     * @param int $iTagId 
     * @param string $sLastOrderedStart 
     * @param string $sLastOrderedEnd
     */
    public static function handleDynamicPromotion($iTagId, $sLastOrderedStart, $sLastOrderedEnd)
    {
        // Get products in promotions
        $aProducts = Product::getPricesDrop(GMerchantCenterPro::$sCurrentLang, 0, 100000, false, null, null);

        foreach ($aProducts as $aDetail) {
            $aProductIds[] = $aDetail['id_product'];
        }

        //Removed duplicate values 
        $aProductIds = array_unique($aProductIds);

        if (!empty($aProductIds)) {
            foreach ($aProductIds as $iProductId) {
                BT_GmcProCustomLabelDao::insertDynamicPromotion($iTagId, $sLastOrderedStart, $sLastOrderedEnd, $iProductId);
            }
        } else {
            $aAssign['aErrors'][] = array('msg' => $GLOBALS['GMCP_CL_PRODUCT_ASSOCIATION'][GMerchantCenterPro::$sCurrentLang], 'code' => '');
        }
    }

    /**
     * Clean tag on table before insert again the value 
     * 
     * @param int $iTagId 
     * @param string $sLabelType 
     */
    public static function cleanTag($iTagId, $sLabelType)
    {
        if ($sLabelType == "custom_label") {
            foreach ($GLOBALS['GMCP_LABEL_LIST'] as $sTableName => $sFieldType) {
                // delete related tables
                BT_GmcProCustomLabelDao::deleteGmcCatTag($iTagId, $sTableName, $sLabelType);
            }
            BT_GmcProCustomLabelDao::deleteGmcpProductTag($iTagId);
        }

        if ($sLabelType == "dynamic_features_list") {
            BT_GmcProCustomLabelDao::deleteFeatureSave($iTagId);
        }

        if ($sLabelType == "dynamic_categorie") {
            BT_GmcProCustomLabelDao::deleteDynamicCat($iTagId);
        }

        if ($sLabelType == "dynamic_new_product") {
            BT_GmcProCustomLabelDao::deleteDynamicNew($iTagId);
        }

        if ($sLabelType == "dynamic_best_sale") {
            BT_GmcProCustomLabelDao::deleteDynamicBestSales($iTagId);
        }

        if ($sLabelType == "dynamic_price_range") {
            BT_GmcProCustomLabelDao::deleteDynamicPriceRange($iTagId);
        }

        if ($sLabelType == "dynamic_last_order") {
            BT_GmcProCustomLabelDao::deleteDynamicLastProductOrdered($iTagId);
        }

        if ($sLabelType == "dynamic_promotion") {
            BT_GmcProCustomLabelDao::deleteDynamicPromotion($iTagId);
        }
    }

    /**
     * Check and assign again custom label to product during data feed process.
     */
    public static function updateCustomLabelFeedProcess()
    {
        // Get active tag ready for data feed process
        $aActiveTags = BT_GmcProCustomLabelDao::getActiveTag(GMerchantCenterPro::$iShopId);

        if (!empty($aActiveTags)) {
            foreach ($aActiveTags as $aTag) {

                if ($aTag['type'] == 'dynamic_categorie') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicCat((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleCatDynmaicTag((int)$aTag['id_tag'], (array)$aTagDataSaved);
                    }
                }

                if ($aTag['type'] == 'dynamic_features_list') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getFeatureSave((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleFeatureTag((int)$aTag['id_tag'], (int)$aTagDataSaved['id_feature']);
                    }
                }

                if ($aTag['type'] == 'dynamic_new_product') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicNew((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleDynamicNewProduct((int)$aTag['id_tag'], (string)$aTagDataSaved['from_date']);
                    }
                }

                if ($aTag['type'] == 'dynamic_best_sale') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicBestSales((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleDynamicBestSales((int)$aTag['id_tag'], (int)$aTagDataSaved['unit'], (string)$aTagDataSaved['amount'], (string)$aTagDataSaved['start_date'], (string)$aTagDataSaved['end_date']);
                    }
                }

                if ($aTag['type'] == 'dynamic_price_range') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicPriceRange((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleDynamicPriceRange((int)$aTag['id_tag'], (string)$aTagDataSaved['price_min'], (string)$aTagDataSaved['price_max']);
                    }
                }

                if ($aTag['type'] == 'dynamic_last_order') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicLastProductOrdered((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleDynamicLastOrdered((int)$aTag['id_tag'], (string)$aTagDataSaved['start_date'], (string)$aTagDataSaved['end_date']);
                    }
                }

                if ($aTag['type'] == 'dynamic_promotion') {
                    if (!empty($aTag['id_tag'])) {
                        $aTagDataSaved = BT_GmcProCustomLabelDao::getDynamicLastDynamicPromotion((int)$aTag['id_tag']);
                        self::cleanTag((int)$aTag['id_tag'], $aTag['type']);
                        self::handleDynamicPromotion((int)$aTag['id_tag'], (string)$aTagDataSaved['start_date'], (string)$aTagDataSaved['end_date']);
                    }
                }
            }
        }
    }
}
