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

class BT_GmcProExclusionRender
{

    // the current lang
    private $iLang = '';

    /**
     * method display all configured data admin tabs
     *
     * @param string $sType => define which method to execute
     * @param array $aParam
     * @return array
     */
    public function render($sType, array $aParam = null, $aDataRules = null)
    {
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        $this->iLang = GMerchantCenterpro::$iCurrentLang;

        if (!empty($sType)) {
            return call_user_func_array(array($this, 'render' . ucfirst($sType)), array($aParam, $aDataRules));
        }

    }

    /**
     * method return the suppliers values
     *
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderSupplier($aParam = null, $aDataRules = null)
    {

        $aSuppliers = Supplier::getSuppliers();
        $aIndexedSuppliers = array();
        $iRuleId = Tools::getValue('iRuleId');

        if (!empty($iRuleId)) {
            $aData = BT_GmcExclusionDao::getExclusionRulesById((int)$iRuleId);
            $aRuleData = unserialize($aData['exclusion_value']);
            $aIndexedSuppliers = $aRuleData['exclusionData'];
        }

        $aFirst = array();

        $aData['aFormatSuppliers'] = BT_GmcProModuleTools::recursiveSupplierTree($aSuppliers, $aIndexedSuppliers,
            $aFirst, 0);

        if (empty($aData['aFormatSuppliers'])) {
            $aData['sSupplierMessage'] = 1;
        }

        return $aData;
    }

    /**
     * method return the word values
     *
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderWord($aParam = null, $aDataRules = null)
    {
        $aData = array();

        if (!empty($aDataRules)) {
            //get the exclusion value one
            $aTmpData = unserialize($aDataRules['exclusion_value']);
            $aData['sExclusionOn'] = $aTmpData['exclusionOn'];
            $aData['iExclusionData'] = $aTmpData['exclusionData'];
            $aData['bDisplayField'] = true;
        }

        //To manage the refresh element on the form
        $bRefresh = !empty($aParam['bRefresh']) ? true : false;

        if (empty($bRefresh)) {
            $aData['aWordExlusionTypeWord'] = $GLOBALS[_GMCP_MODULE_NAME . '_EXCLUSION_TYPE_WORD'];
        } else {
            $aData['bDisplayField'] = true;
        }

        return $aData;
    }

    /**
     * method return the feature values
     *
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderFeature($aParam = null, $aDataRules = null)
    {
        $aData = array();

        if (!empty($aDataRules)) {
            //get the exclusion value one
            $aTmpData = unserialize($aDataRules['exclusion_value']);
            $aData['iExclusionData'] = $aTmpData['exclusionData'];
        }

        //To manage the refresh element on the form
        $bRefresh = !empty($aParam['bRefresh']) ? true : false;

        if (empty($bRefresh)) {
            $aData['aFeatures'] = Feature::getFeatures($this->iLang);
        } else {
            $aFeatureData = !empty($aParam['iFeatureId']) ? FeatureValue::getFeatureValuesWithLang($this->iLang, (int)$aParam['iFeatureId']) : array();
            $aData['aFeaturesValues'] = $aFeatureData;
            $aData['bEmptyFeatureValue'] = empty($aFeatureData) ? true : false;
        }

        return $aData;
    }

    /**
     * method return the attribute values
     *
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderAttribute($aParam = null, $aDataRules = null)
    {
        $aData = array();

        if (!empty($aDataRules)) {
            //get the exclusion value one
            $aTmpData = unserialize($aDataRules['exclusion_value']);
            $aData['iExclusionData'] = $aTmpData['exclusionData'];
        }

        //To manage the refresh element on the form
        $bRefresh = !empty($aParam['bRefresh']) ? true : false;

        if (empty($bRefresh)) {
            $aData['aAttributes'] = AttributeGroup::getAttributesGroups($this->iLang);
        } else {
            $aAttributeData = !empty($aParam['iAttributeId']) ? AttributeGroup::getAttributes($this->iLang,
                (int)$aParam['iAttributeId']) : array();
            $aData['aAttributeValues'] = $aAttributeData;
            $aData['bEmptyAttributeValue'] = empty($aAttributeData) ? true : false;
        }

        return $aData;
    }

    /**
     * method return the word values
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderSpecificProduct($aParam = null, $aDataRules = null)
    {
        $aData = array();

        $aData['bSpecifiqueProduct'] = true;

        return $aData;
    }

    /**
     * method return the current rules configuration
     *
     * @param array $aParam
     * @return array
     */
    private function renderRules($aParam = null)
    {
        // Use to fill out the tmp table
        require_once('exclusion-tools_class.php');

        $bNeedUpdate = false;

        // To force the update rules data when we don't manage the delete
        if (!empty($aParam['sTmpRules']) && empty($aParam['sDelete'])) {
            $bNeedUpdate = true;
        }

        //Use case for the update simuation of the ajax
        if ($aParam['sTmpRules'] == 'false') {
            $bNeedUpdate = false;
        }

        $aData = BT_GmcProExclusionTools::extractTmpRulesData($aParam, $bNeedUpdate);
        $aOutputData = array();

        //Format the output data
        foreach ($aData as $sKey => $sValue) {
            $aOutputData[$sKey]['id'] = $sValue['id'];
            $aOutputData[$sKey]['sType'] = $sValue['type'];
            $aOutputData[$sKey]['data'] = BT_GmcProExclusionTools::getRulesLabel($sValue['type']);
            $aOutputData[$sKey]['filter'] = BT_GmcProExclusionTools::getRulesDetail($sValue['type'], unserialize($sValue['exclusion_values']));

            // Use case to get the attribute id to manage the good values on the product name + combination
            if ($sValue['type'] == 'attribute') {
                $aOutputData[$sKey]['attributeId'] = unserialize($sValue['exclusion_values'])['filter_2'];
            }
        }

        return $aOutputData;
    }

    /**
     * method return the current rules configuration
     *
     * @param array $aParam
     * @param array $aDataRules
     * @return array
     */
    private function renderProducts($aParam = null, $aDataRules = null)
    {
        // Use to fill out the tmp table
        require_once('exclusion-tools_class.php');

        $aOutputDataProduct = array();

        foreach ($aDataRules as $aDataRule) {
            // For all cases except attribute because the behavior can be different
            if (empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                $aProducts = array_unique(BT_GmcProExclusionTools::getProductFromRules());
                foreach ($aProducts as $sKey => $aProductIds) {
                    //Init product data to get details
                    $oProduct = new Product((int)$aProductIds, true, (int)GMerchantCenterpro::$iCurrentLang);
                    if (is_object($oProduct)) {
                        $aOutputDataProduct[$sKey]['id'] = $oProduct->id;
                        $aOutputDataProduct[$sKey]['name'] = $oProduct->name;
                    }
                }
            } else {
                $aProducts = empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS']) ? array_unique(BT_GmcProExclusionTools::getProductFromRules()) : BT_GmcProExclusionTools::getProductFromRules();
                foreach ($aProducts as $sPropductKey => $aProductId) {
                    $oProduct = new Product((int)$aProductId['id_product'], true, (int)GMerchantCenterpro::$iCurrentLang);
                    $aCombinationAttrData = BT_GmcProModuleTools::getProductCombinationName($aProductId['id_product_attribute'], GMerchantCenterpro::$iCurrentLang, GMerchantCenterpro::$iShopId);
                    $aOutputDataProduct[$sPropductKey]['id'] = $aProductId['id_product'];
                    $aOutputDataProduct[$sPropductKey]['name'] = $oProduct->name . ' ' . $aCombinationAttrData;
                }
            }
        }

        return $aOutputDataProduct;
    }
}

