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

class BT_AdminDelete implements BT_IAdmin
{
    /**
     * delete content
     *
     * @param string $sType => define which method to execute
     * @param array $aParam
     * @return array
     */
    public function run($sType, array $aParam = null)
    {
        // set variables
        $aDisplayData = array();

        switch ($sType) {
            case 'label': // use case - delete custom label
            case 'exclusionRule': // use case - delete custom label
                // execute match function
                $aDisplayData = call_user_func_array(array($this, 'delete' . ucfirst($sType)), array($aParam));
                break;
            default:
                break;
        }
        return $aDisplayData;
    }

    /**
     * delete one tag label
     *
     * @param array $aPost
     * @return array
     */
    private function deleteLabel(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();
        $sDeleteType = Tools::getValue('sDeleteType');
        $bContinu = false;

        try {
            if (!empty($sDeleteType)) {
                if ($sDeleteType == 'one') {
                    $iTagId = Tools::getValue('iTagId');
                    $bContinu = true;
                } elseif ($sDeleteType == 'bulk') {
                    $aIdsDelete = explode(",", Tools::getValue('iTagIds'));
                    $bContinu = true;
                }
            }

            if ($bContinu == false) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Your Custom label ID(s) are not valid', 'admin-update_class') . '.', 700);
            } else {
                // include
                require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');
                require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

               
                if ($sDeleteType == 'one') {
                    if (!empty($iTagId)) {
                        BT_GmcProCustomLabelDao::deleteGmcpProductTag($iTagId);
                        BT_GmcProCustomLabelDao::deleteFeatureSave($iTagId);
                        BT_GmcProCustomLabelDao::deleteDynamicCat($iTagId);
                        BT_GmcProCustomLabelDao::deleteDynamicNew($iTagId);
                        BT_GmcProCustomLabelDao::deleteDynamicBestSales($iTagId);
                        BT_GmcProCustomLabelDao::deleteDynamicPriceRange($iTagId);
                        BT_GmcProCustomLabelDao::deleteGmcTag($iTagId, $GLOBALS['GMCP_LABEL_LIST']);

                    }
                } elseif ($sDeleteType == 'bulk') {
                    foreach ($aIdsDelete as $aCurrentClId) {
                        if (!empty($aCurrentClId)) {
                            BT_GmcProCustomLabelDao::deleteGmcpProductTag($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteFeatureSave($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteDynamicCat($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteDynamicNew($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteDynamicBestSales($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteDynamicPriceRange($aCurrentClId);
                            BT_GmcProCustomLabelDao::deleteGmcTag($aCurrentClId, $GLOBALS['GMCP_LABEL_LIST']);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('google');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     *  method delete exclusion rules
     *
     * @param array $aPost
     * @return array
     */
    private function deleteExclusionRule(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            $iRuleId = Tools::getValue('iRuleId');
            $sType = Tools::getValue('sDeleteType');

            if (empty($iRuleId) || empty($sType)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Your rule ID isn\'t valid or the type of deletion is no valide', 'admin-update_class') . '.', 100);
            } else {
                // include
                require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

                if (!BT_GmcProExclusionDao::deleteExclusionRules($iRuleId, $sType)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while deleting the rule', 'admin-update_class') . '.', 101);
                }

                if (!BT_GmcProExclusionDao::deleteProductExcluded($iRuleId)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while deleting excluded products', 'admin-update_class') . '.', 102);
                }
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('feed');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * set singleton
     *
     * @return obj
     */
    public static function create()
    {
        static $oDelete;

        if (null === $oDelete) {
            $oDelete = new BT_AdminDelete();
        }
        return $oDelete;
    }
}
