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

class BT_GmcProExclusionTools
{

    /**
     * method returns the exclusion rules     *
     * @return mixed :
     */
    public static function getExclusionRules()
    {
        $sQuery = 'SELECT * ' . ' FROM `' . _DB_PREFIX_ . 'gmc_advanced_exclusion` ';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * extract the option data do add it on tmp rules tables
     *
     * @param array $aData
     * @param bool $bNeedUpdate
     * @return bool
     */
    public static function extractTmpRulesData($aData, $bNeedUpdate)
    {
        require_once('exclusion-dao_class.php');

        $aOutputData = array();

        switch ($aData['sTypeValue']) {
            case 'word' : // use case - rules based on word
                $aOutputData = array(
                    'filter_1' => $aData['sWordType'],
                    'filter_2' => $aData['sWordValue'],
                );
                break;
            case 'feature' : // use case - rules based on feature
                $aOutputData = array(
                    'filter_1' => $aData['sFeature'],
                    'filter_2' => $aData['sFeatureValue'],
                );
                break;
            case 'attribute' : // use case - rules based on attribute
                $aOutputData = array(
                    'filter_1' => $aData['sAttribute'],
                    'filter_2' => $aData['sAttributeValue'],
                );
                break;
            case 'specificProduct' : // use case - rules based on attribute

                $aProductIds = array();
                $sExcludedIds = $aData['sProductIds'];
                $aExcludedIds = !empty($sExcludedIds) ? explode('-', $sExcludedIds) : array();

                if (!empty($aExcludedIds)) {
                    array_pop($aExcludedIds);
                }

                // Loop to manage product ids
                foreach ($aExcludedIds as $sProductId) {
                    list($iProdId, $iAttrId) = explode('¤', $sProductId);
                    $aProductIds[$iProdId] = $iAttrId;
                }

                $aOutputData = array(
                    'filter_1' => $aProductIds,
                );
                break;
            case 'supplier' : // use case - rules based on supplier
                $aProductIds = explode(',', $aData['aSuppliers']);

                $aOutputData = array(
                    'filter_2' => $aProductIds,
                );
                break;
            default :
                break;
        }

        //Use case when we don't use delete tmp rules
        if (!empty($bNeedUpdate)) {
            BT_GmcProExclusionDao::addTmpDataRules(GMerchantCenterpro::$iShopId, $aData['sTypeValue'], serialize($aOutputData));
        }

        $aTmpRules = BT_GmcProExclusionDao::getTmpRules();

        return $aTmpRules;

    }


    /**
     * get the good label for rules
     *
     * @param string $sData
     * @param string $sType
     * @return bool
     */
    public static function getRulesLabel($sData)
    {
        $sLang = (GMerchantCenterpro::$sCurrentLang == 'en' || GMerchantCenterpro::$sCurrentLang == 'fr' || GMerchantCenterpro::$sCurrentLang == 'es' || GMerchantCenterpro::$sCurrentLang == 'it') ? GMerchantCenterpro::$sCurrentLang : 'en';

        $sRulesName = $GLOBALS[_GMCP_MODULE_NAME . '_RULES_LABEL_TYPE'][$sData][$sLang];

        return $sRulesName;

    }

    /**
     * get rules detail
     * @param string $sType
     * @param array $sData
     * @return array
     */
    public static function getRulesDetail($sType, $aData)
    {
        $sLang = (GMerchantCenterpro::$sCurrentLang == 'en' || GMerchantCenterpro::$sCurrentLang == 'fr' || GMerchantCenterpro::$sCurrentLang == 'es' || GMerchantCenterpro::$sCurrentLang == 'it') ? GMerchantCenterpro::$sCurrentLang : 'en';
        $aOutputData = array();

        if (is_array($aData)) {
            $aProducts = array();

            switch ($sType) {
                case 'supplier' : // use case - rules based on supplier

                    if (is_array($aData['filter_2'])
                        && !empty($aData['filter_2'])) {
                        $aProducts = BT_GmcProExclusionDao::getProductFromSuppliers($aData['filter_2'],
                            GMerchantCenterpro::$iShopId);
                    }
                    // Get the supplier name for the rules summary display
                    foreach ($aData['filter_2'] as $iSupplierId) {
                        $oSupplier = new Supplier($iSupplierId);
                        $aOutputDataSupplierName[] = $oSupplier->name;
                    }
                    unset($oSupplier);

                    //Manage the numbers of checked element
                    $aOutputData['sType'] = $sType;
                    $aOutputData['iCheckedTreeElem'] = count($aData['filter_2']);
                    $aOutputData['iNumberOfProducts'] = count($aProducts);
                    $aOutputData['iSupplierId'] = $aData['filter_2'];
                    $aOutputData['aSupplierName'] = $aOutputDataSupplierName;
                    break;
                case 'word' : // use case - rules based on word
                    if (is_string($aData['filter_2'])
                        && !empty($aData['filter_2'])
                        && !empty($aData['filter_1'])
                    ) {
                        $aProducts = BT_GmcProExclusionDao::getProductFromWords($aData['filter_1'], $aData['filter_2']);
                    }

                    $aOutputData = array(
                        'filter_1' => $GLOBALS[_GMCP_MODULE_NAME . '_RULES_WORD_TYPE'][$aData['filter_1']][$sLang],
                        'filter_2' => $aData['filter_2'],
                        'iNumberOfProducts' => count($aProducts),
                    );
                    break;
                case 'feature' : // use case - rules based on feature
                    $aOutputData = array();
                    // Get all features values
                    $aFeaturesValues = FeatureValue::getFeatureValuesWithLang(GMerchantCenterpro::$iCurrentLang, (int)$aData['filter_1']);

                    //Set the 1st filter
                    $aOutputData['filter_1'] = Feature::getFeature(GMerchantCenterpro::$iCurrentLang,
                        (int)$aData['filter_1'])['name'];

                    // Search the good value nane
                    foreach ($aFeaturesValues as $aFeaturesValue) {
                        if ($aFeaturesValue['id_feature_value'] == $aData['filter_2']) {
                            $aOutputData['filter_2'] = $aFeaturesValue['value'];
                        }

                    }
                    $aOutputData['sType'] = $sType;
                    $aOutputData['iNumberOfProducts'] = count(BT_GmcProModuleDao::getProductIdsByFeature((int)$aData['filter_2']));

                    break;
                case 'attribute' : // use case - rules based on attribute
                    $aAttributes = AttributeGroup::getAttributesGroups(GMerchantCenterpro::$iCurrentLang);
                    $aAttributesValues = Attribute::getAttributes(GMerchantCenterpro::$iCurrentLang);

                    foreach ($aAttributes as $aAttribute) {
                        if ($aAttribute['id_attribute_group'] == $aData['filter_1']) {
                            $aOutputData['filter_1'] = $aAttribute['public_name'];
                        }
                    }

                    foreach ($aAttributesValues as $aAttributesValue) {
                        if ($aAttributesValue['id_attribute'] == $aData['filter_2']) {
                            $aOutputData['filter_2'] = $aAttributesValue['name'];
                        }

                    }
                    $aOutputData['sType'] = $sType;
                    $aOutputData['iNumberOfProducts'] = count(BT_GmcProModuleDao::getProductsIdFromAttribute((int)$aData['filter_2']));
                    break;
                case 'specificProduct' : // use case - rules based on specific product

                    $aOutputData['sType'] = $sType;
                    $aOutputData['iNumberOfProducts'] = count($aData['filter_1']);
                    break;
                default :
                    break;
            }
        }

        return $aOutputData;

    }

    /**
     * get the product according to the rules filter values
     *
     * @return array
     */
    public static function getProductFromRules()
    {
        //To use DAO for rules management
        require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');
        //To use the Exclusion tools
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        $aRules = BT_GmcProExclusionDao::getTmpRules();

        // To stock the product ids from rules condition
        $aProductIdsToExclude = array();

        foreach ($aRules as $sKey => $aRule) {

            //Get the filter values
            $aFilterValues = is_string($aRule['exclusion_values']) ? unserialize($aRule['exclusion_values']) : $aRule['exclusion_values'];

            //Use case on supplier
            if ($aRule['type'] == 'supplier') {
                $aProductIds = BT_GmcProExclusionDao::getProductFromSuppliers($aFilterValues['filter_2'], GMerchantCenterpro::$iShopId);

                if (!empty($aProductIds)) {
                    foreach ($aProductIds as $aProductId) {
                        //Use case for exportation without the combination
                        if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                            $aProductIdsToExclude[] = $aProductId['id_product'];
                        } else {
                            $oProduct = new Product($aProductId['id_product'], GMerchantCenterPro::$iCurrentLang);
                            $aAttributes = $oProduct->getAttributeCombinations(GMerchantCenterPro::$iCurrentLang);

                            if (!empty($aAttributes)) {
                                foreach ($aAttributes as $aAttribute) {
                                    $aProductIdsToExclude[] = array(
                                        'id_product' => $aProductId['id_product'],
                                        'id_product_attribute' => $aAttribute['id_product_attribute']
                                    );
                                }
                            } else {
                                $aProductIdsToExclude[] = array(
                                    'id_product' => $aProductId['id_product'],
                                    'id_product_attribute' => $aProductId['id_product_attribute']
                                );
                            }
                        }
                    }
                }
            }

            //Use case on word
            if ($aRule['type'] == 'word') {
                $aProductIds = BT_GmcProExclusionDao::getProductFromWords($aFilterValues['filter_1'],
                    $aFilterValues['filter_2']);

                if (!empty($aProductIds)) {
                    foreach ($aProductIds as $aProductId) {
                        //Use case for exportation without the combination
                        if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                            $aProductIdsToExclude[] = $aProductId['id_product'];
                        } else {
                            $oProduct = new Product($aProductId['id_product'], GMerchantCenterPro::$iCurrentLang);
                            $aAttributes = $oProduct->getAttributeCombinations(GMerchantCenterPro::$iCurrentLang);

                            if (!empty($aAttributes)) {
                                foreach ($aAttributes as $aAttribute) {
                                    $aProductIdsToExclude[] = array(
                                        'id_product' => $aProductId['id_product'],
                                        'id_product_attribute' => $aAttribute['id_product_attribute']
                                    );
                                }
                            } else {
                                $aProductIdsToExclude[] = array(
                                    'id_product' => $aProductId['id_product'],
                                    'id_product_attribute' => $aProductId['id_product_attribute']
                                );
                            }
                        }
                    }
                }
            }

            //Use case on feature
            if ($aRule['type'] == 'feature') {
                $aProductIds = BT_GmcProModuleDao::getProductIdsByFeature($aFilterValues['filter_2']);

                if (!empty($aProductIds)) {
                    foreach ($aProductIds as $aProductId) {
                        //Use case for exportation without the combination
                        if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                            $aProductIdsToExclude[] = $aProductId['id_product'];
                        } else {
                            $oProduct = new Product($aProductId['id_product'], GMerchantCenterPro::$iCurrentLang);
                            $aAttributes = $oProduct->getAttributeCombinations(GMerchantCenterPro::$iCurrentLang);

                            if (!empty($aAttributes)) {
                                foreach ($aAttributes as $aAttribute) {
                                    $aProductIdsToExclude[] = array(
                                        'id_product' => $aProductId['id_product'],
                                        'id_product_attribute' => $aAttribute['id_product_attribute']
                                    );
                                }
                            } else {
                                $aProductIdsToExclude[] = array(
                                    'id_product' => $aProductId['id_product'],
                                    'id_product_attribute' => $aProductId['id_product_attribute']
                                );
                            }
                        }
                    }
                }

            }

            //Use case on attribute
            if ($aRule['type'] == 'attribute') {

                $aProductIds = BT_GmcProModuleDao::getProductsIdFromAttribute($aFilterValues['filter_2']);
                if (!empty($aProductIds)) {
                    foreach ($aProductIds as $aProductId) {
                        //Use case for exportation without the combination
                        if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                            $aProductIdsToExclude[] = $aProductId['id_product'];
                        } else {
                            $aProductIdsToExclude[] = array(
                                'id_product' => $aProductId['id_product'],
                                'id_product_attribute' => $aProductId['id_product_attribute']
                            );
                        }
                    }
                }
            }

            //Use case for specific products
            if ($aRule['type'] == 'specificProduct') {
                $aProductIds = $aFilterValues['filter_1'];

                if (!empty($aProductIds)) {
                    foreach ($aProductIds as $iProductId => $iAttrId) {
                        //Use case for exportation without the combination
                        if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                            $aProductIdsToExclude[] = $iProductId;
                        } else {
                            $aProductIdsToExclude[] = array(
                                'id_product' => $iProductId,
                                'id_product_attribute' => $iAttrId
                            );
                        }
                    }
                }
            }

            unset($oProduct);

            return $aProductIdsToExclude;
        }
    }

}