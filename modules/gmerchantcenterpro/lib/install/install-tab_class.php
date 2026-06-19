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

class BT_InstallTab implements BT_IInstall
{
    /**
     * install of module
     *
     * @param mixed $mParam
     * @return bool $bReturn : true => validate install, false => invalidate install
     */
    public static function install($mParam = null)
    {
        // declare return
        $bReturn = true;

        static $oTab;

        // instantiate
        if (null === $oTab) {
            $oTab = new Tab();
        }

        // log jam to debug appli
        if (defined('_GMCP_LOG_JAM_CONFIG') && _GMCP_LOG_JAM_CONFIG) {
            $bReturn = _GMCP_LOG_JAM_CONFIG;
        } else {
            // set variables
            $aTmpLang = array();

            // get available languages
            $aLangs = Language::getLanguages(true);

            // loop on each admin tab
            foreach ($GLOBALS['GMCP_TABS'] as $sAdminClassName => $aTab) {
                foreach ($aLangs as $aLang) {
                    $aTmpLang[$aLang['id_lang']] = array_key_exists($aLang['iso_code'],
                        $aTab['lang']) ? $aTab['lang'][$aLang['iso_code']] : $aTab['lang']['en'];
                }
                $oTab->name = $aTmpLang;
                $oTab->class_name = $sAdminClassName;
                $oTab->module = GMerchantCenterPro::$oModule->name;
                $oTab->id_parent = Tab::getIdFromClassName($aTab['parent']);

                // use case - copy icon tab
                if (file_exists(_PS_MODULE_DIR_ . $oTab->module . _GMCP_PATH_VIEWS . _GMCP_PATH_IMG . _GMCP_TPL_ADMIN_PATH . $sAdminClassName . '.gif')) {
                    @copy(_PS_MODULE_DIR_ . $oTab->module . _GMCP_PATH_VIEWS . _GMCP_PATH_IMG . _GMCP_TPL_ADMIN_PATH . $sAdminClassName . '.gif',
                        _PS_IMG_DIR_ . 't/' . $sAdminClassName . '.gif');
                }

                // save admin tab
                if (false == $oTab->save()) {
                    $bReturn = false;
                }
            }
        }

        return $bReturn;
    }

    /**
     * uninstall of module
     *
     * @param mixed $mParam
     * @return bool $bReturn : true => validate uninstall, false => invalidate uninstall
     */
    public static function uninstall($mParam = null)
    {
        // set return execution
        $bReturn = true;

        // loop on each admin tab
        foreach ($GLOBALS['GMCP_TABS'] as $sAdminClassName => $aTab) {
            // get ID
            $iTabId = Tab::getIdFromClassName($sAdminClassName);

            if (!empty($iTabId)) {
                // instantiate
                $oTab = new Tab($iTabId);

                // use case - check delete
                if (false == $oTab->delete()) {
                    $bReturn = false;
                } else {
                    if (!defined('_PS_IMG_DIR')) {
                        define('_PS_IMG_DIR', _PS_ROOT_DIR_ . '/img/');
                    }
                    if (file_exists(_PS_IMG_DIR . 't/' . $sAdminClassName . '.gif')) {
                        @unlink(_PS_IMG_DIR . 't/' . $sAdminClassName . '.gif');
                    }
                }
            }
        }

        return $bReturn;
    }
}
