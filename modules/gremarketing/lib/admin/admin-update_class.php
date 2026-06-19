<?php

/**
 * Google Dynamic Remarketing
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
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
        $aParam['sType'] = !empty($aParam['sType']) ? $aParam['sType'] : '';

        switch ($aParam['sType']) {
            case 'basic': // use case - update basic settings
            case 'dynamic': // use case - update dynamic settings
                $aDisplayInfo = call_user_func_array(array($this, 'update' . ucfirst($aParam['sType'])), array($aParam));
                break;
            default:
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
            // use case - check if stock is displayed
            $iGoogleId = Tools::getIsset('bt_google-id') ? Tools::getValue('bt_google-id') : false;

            if (!empty($iGoogleId)) {
                if (!Configuration::updateValue('GR_REMARKETING_ID', $iGoogleId)) {
                    throw new Exception(GRemarketing::$oModule->l('An error occurred during google id update', 'admin-update_class') . '.', 110);
                }
            } else {
                throw new Exception(GRemarketing::$oModule->l('Google ID has not been filled out or is not a numeric', 'admin-update_class') . '.', 111);
            }

            $bUserId = Tools::getValue('bt_user_id');
            if (!Configuration::updateValue('GR_USER_ID', $bUserId)) {
                throw new Exception(gremarketing::$oModule->l('An error occurred during activate user id update', 'admin-update_class') . '.', 113);
            }
        } catch (Exception $e) {
            $aUpdateInfo['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GRModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GR_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basic settings updated
        $aInfo = BT_AdminDisplay::create()->run(array('sType' => 'basic'));

        // use case - empty error and updating status
        $aInfo['assign'] = array_merge($aInfo['assign'], array(
            'bAjaxMode' => GRemarketing::$sQueryMode,
            'iActiveTab' => 2,
            'bUpdate' => (empty($aUpdateInfo['aErrors']) ? true : false),
        ), $aUpdateInfo);

        return $aInfo;
    }


    /**
     * update dynamic settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateDynamic(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aUpdateInfo = array();

        try {
            // use case - check if activate dynamic remarketing
            $bDynamicRemarketing = Tools::getValue('bt_activate-dynamic');
            if (!Configuration::updateValue('GR_REMARKETING_DYNAMIC', $bDynamicRemarketing)) {
                throw new Exception(gremarketing::$oModule->l('An error occurred during activate dynamic remarketing update', 'admin-update_class') . '.', 120);
            }
            // if dynamic activated
            if ($bDynamicRemarketing) {
                // use case - check if prefix is filled out
                $sPrefix = Tools::getValue('bt_google-prefix');
                if (!empty($sPrefix)) {
                    if (!Configuration::updateValue('GR_GOOGLE_PREFIX', $sPrefix)) {
                        throw new Exception(gremarketing::$oModule->l('An error occurred during google prefix update', 'admin-update_class') . '.', 121);
                    }
                }

                $sSeparator = Tools::getValue('bt_separator');
                if (!empty($sSeparator)) {
                    if (!Configuration::updateValue('GR_COMBO_SEPARATOR', $sSeparator)) {
                        throw new Exception(gremarketing::$oModule->l('An error occurred during google separator update', 'admin-update_class') . '.', 122);
                    }
                }
            }
        } catch (Exception $e) {
            $aUpdateInfo['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GRModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GR_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basic settings updated
        $aInfo = BT_AdminDisplay::create()->run(array('sType' => 'dynamic'));

        // use case - empty error and updating status
        $aInfo['assign'] = array_merge($aInfo['assign'], array(
            'bAjaxMode' => GRemarketing::$sQueryMode,
            'iActiveTab' => 3,
            'bUpdate' => (empty($aUpdateInfo['aErrors']) ? true : false),
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
