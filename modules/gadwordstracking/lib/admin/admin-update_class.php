<?php
/**
 * admin-update_class.php file defines method to add or update content for basic settings / FILL ALL update data type
 */


class BT_AdminUpdate implements BT_IAdmin
{
    /**
     * update all tabs content of admin page
     *
     * @param array $aParam
     * @return array
     */
    public function run(array $aParam = null)
    {
        // set variables
        $aDisplayInfo = array();

        // get type
        $aParam['sType'] = ! empty($aParam['sType']) ? $aParam['sType'] : '';

        switch ($aParam['sType']) {
            case 'advice' : // use case - update advice
            case 'basic' : // use case - update basics settings
            case 'advanced' : // use case - update advance settings
                // execute match function
                $aDisplayInfo = call_user_func_array(array($this, 'update' . ucfirst($aParam['sType'])), array($aParam));
                break;
            default :
                break;
        }

        return $aDisplayInfo;
    }

    /**
     * update basic settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateBasic(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aUpdateInfo = array();

        try {
            // use case - check if Google ID is matching
            $iConversionId = Tools::getIsset('bt_conversion-id') ? Tools::getValue('bt_conversion-id') : false;

            if (!empty($iConversionId)) {
                if (!Configuration::updateValue('GACT_CONVERSION_ID', $iConversionId)) {
                    throw new Exception(GAdwordsTracking::$oModule->l('An error occurred during google conversion id update', 'admin-update_class') . '.', 110);
                }
            } else {
                throw new Exception(GAdwordsTracking::$oModule->l('Google Conversion ID has not been filled out or is not a numeric', 'admin-update_class') . '.', 111);
            }

            // use case - check if stock is displayed
            $sConversionLabel = Tools::getIsset('bt_conversion-label') ? Tools::getValue('bt_conversion-label') : false;

            if (!empty($sConversionLabel)) {
                if (!Configuration::updateValue('GACT_CONVERSION_LABEL', $sConversionLabel)) {
                    throw new Exception(GAdwordsTracking::$oModule->l('An error occurred during google conversion label update', 'admin-update_class') . '.', 112);
                }
            } else {
                throw new Exception(GAdwordsTracking::$oModule->l('Google Conversion Label has not been filled out or is not alpha-numeric', 'admin-update_class') . '.', 113);
            }
        } catch (Exception $e) {
            $aUpdateInfo['aErrors'][] = array('msg'  => $e->getMessage(), 'code' => $e->getCode(),);
        }

        // get configuration options
        BT_GactModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GACT_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basic settings updated
        $aInfo = BT_AdminDisplay::create()->run(array('sType' => 'basic'));

        // use case - empty error and updating status
        $aInfo['assign'] = array_merge($aInfo['assign'], array(
            'bAjaxMode' => GAdwordsTracking::$sQueryMode,
            'bUpdate'   => (empty($aUpdateInfo['aErrors']) ? true : false),
        ), $aUpdateInfo);

        return $aInfo;
    }

    /**
     * update basic settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateAdvice(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {
            // use case - still display advice screen or not
            $bDisplayAdvice = (Tools::getIsset('bt_show-screen') && Tools::getValue('bt_show-screen') == 'on') ? false : true;

            if (!Configuration::updateValue('GACT_DISPLAY_ADVICE', $bDisplayAdvice)) {
                throw new Exception(GAdwordsTracking::$oModule->l('An error occurred during displaying advice screen update', 'admin-update_class') . '.', 170);
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg'  => $e->getMessage(), 'code' => $e->getCode());
        }

        // force xhr mode
        GAdwordsTracking::$sQueryMode = 'xhr';

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_CONFIRM,
            'assign' => $aAssign,
        );
    }


    /**
     * update advance settings
     *
     * @param array $aPost
     *
     * @return array
     */
    private function updateAdvanced(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aUpdateInfo = array();

        try {
            $bUseTax = Tools::getValue('bt_use-tax');
            if (!Configuration::updateValue('GACT_USE_TAX', $bUseTax)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during tax use tax update', 'admin-update_class') . '.', 300);
            }

            $bUseShipping = Tools::getValue('bt_use-shipping');
            if (!Configuration::updateValue('GACT_USE_SHIPPING', $bUseShipping)
            ) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during tax use shipping update', 'admin-update_class') . '.', 301);
            }

            $bUseWrapping = Tools::getValue('bt_use-wrapping');
            if (!Configuration::updateValue('GACT_USE_WRAPPING', $bUseWrapping)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during tax use shipping update', 'admin-update_class') . '.', 302);
            }

            $bDisplayFunnel = Tools::getValue('bt_display-funnel');
            if ( ! Configuration::updateValue('GACT_DISPLAY_FUNNEL', $bDisplayFunnel)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during display code in funnel update', 'admin-update_class') . '.', 303);
            }
        } catch (Exception $e) {
            $aUpdateInfo['aErrors'][] = array(
                'msg'  => $e->getMessage(),
                'code' => $e->getCode(),
            );
        }

        // get configuration options
        BT_GactModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GACT_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basic settings updated
        $aInfo = BT_AdminDisplay::create()->run(array('sType' => 'advanced'));

        // use case - empty error and updating status
        $aInfo['assign'] = array_merge($aInfo['assign'], array(
            'bAjaxMode' => GAdwordsTracking::$sQueryMode,
            'bUpdate'   => (empty($aUpdateInfo['aErrors']) ? true : false),
        ), $aUpdateInfo);

        return $aInfo;
    }


    /**
     * set singleton
     *
     * @return obj
     */
    public static function create()
    {
        static $oUpdate;

        if (null === $oUpdate) {
            $oUpdate = new BT_AdminUpdate();
        }

        return $oUpdate;
    }
}
