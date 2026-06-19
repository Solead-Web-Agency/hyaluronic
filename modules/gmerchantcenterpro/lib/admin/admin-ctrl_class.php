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

class BT_AdminCtrl extends BT_GmcBaseCtrl
{
    /**
     * @param array $aParams
     */
    public function __construct(array $aParams = null)
    {
        // defines type to execute
        // use case : no key sAction sent in POST mode (no form has been posted => first page is displayed with admin-display.class.php)
        // use case : key sAction sent in POST mode (form or ajax query posted ).
        $sAction = (!Tools::getIsset('sAction') || (Tools::getIsset('sAction') && 'display' == Tools::getValue('sAction'))) ? (Tools::getIsset('sAction') ? Tools::getValue('sAction') : 'display') : Tools::getValue('sAction');

        // set action
        $this->setAction($sAction);

        // set type
        $this->setType();
    }

    /**
     * execute abstract derived admin object
     *
     * @param array $aRequest : request
     * @return array $aDisplay : empty => false / not empty => true
     */
    public function run($aRequest)
    {
        // set
        $aDisplay = array();
        $aParams = array();

        // include interface
        require_once(_GMCP_PATH_LIB_ADMIN . 'i-admin.php');

        switch (self::$sAction) {
            case 'display':
                // include admin display object
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');
                require_once(_GMCP_PATH_LIB . 'warning_class.php');

                $oAdminType = BT_AdminDisplay::create();

                // update new module keys
                BT_GmcProModuleTools::updateConfiguration();

                // get configuration options
                BT_GmcProModuleTools::getConfiguration(array(
                    'GMCP_COLOR_OPT',
                    'GMCP_SIZE_OPT',
                    'GMCP_SHIP_CARRIERS',
                    'GMCP_CHECK_EXPORT',
                    'GMCP_CHECK_EXPORT_STOCK',
                    'GMCP_PROD_EXCL',
                    'GMCP_FEED_TAX',
                    'GMCP_FREE_SHIP_PROD',
                ));

                // set js msg translation
                BT_GmcProModuleTools::translateJsMsg();

                // set params
                $aParams['oJsTranslatedMsg'] = BT_GmcProModuleTools::jsonEncode($GLOBALS['GMCP_JS_MSG']);

                // use case - type not define => first page requested
                if (empty(self::$sType)) {
                    // update module version
                    Configuration::updateValue('GMCP_VERSION', GMerchantCenterPro::$oModule->version);

                    // update module if necessary
                    $aParams['aUpdateErrors'] = GMerchantCenterPro::$oModule->updateModule();
                }

                break;
            case 'update':
                // include admin update object
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-update_class.php');
                $oAdminType = BT_AdminUpdate::create();
                break;
            case 'delete':
                // include admin delete object
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-delete_class.php');
                $oAdminType = BT_AdminDelete::create();
                break;
            case 'generate':
                // include admin generate object
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-generate_class.php');
                $oAdminType = BT_AdminGenerate::create();
                break;
            case 'send':
                // include admin send object
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-send_class.php');
                $oAdminType = BT_AdminSend::create();
                break;
            default:
                $oAdminType = false;
                break;
        }

        // process data to use in view (tpl)
        if (!empty($oAdminType)) {
            // execute good action in admin
            // only displayed with key : tpl and assign in order to display good smarty template
            $aDisplay = $oAdminType->run(parent::$sType, $aRequest);

            if (!empty($aDisplay)) {
                $aDisplay['assign'] = array_merge($aDisplay['assign'], $aParams, array('bAddJsCss' => true));
            }
        }

        return $aDisplay;
    }
}
