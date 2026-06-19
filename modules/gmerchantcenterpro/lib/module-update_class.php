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

class BT_GmcProModuleUpdate
{
    /**
     * @var $aErrors : store errors
     */
    protected $aErrors = array();

    /**
     * execute required function
     *
     * @param string $sType
     * @param array $aParam
     */
    public function run($sType, array $aParam = null)
    {
        // get type
        $sType = empty($sType) ? 'tables' : $sType;

        switch ($sType) {
            case 'tables': // use case - update tables
            case 'fields': // use case - update fields
            case 'hooks': // use case - update hooks
            case 'templates': // use case - update templates
            case 'moduleAdminTab': // use case - update old module admin tab version
            case 'xmlFiles': // use case - initialize XML files
            case 'orderState':
                // execute match function
                call_user_func_array(array($this, 'update' . ucfirst($sType)), array($aParam));
                break;
            case 'configuration': // use case - update configuration
                // execute match function
                call_user_func(array($this, 'update' . ucfirst($sType)), $aParam);
                break;
            case 'fromGmc':
                // execute match function
                call_user_func(array($this, 'update' . ucfirst($sType)), $aParam);
                break;
            case 'resyncGsa':
                // execute match function
                call_user_func(array($this, 'update' . ucfirst($sType)), $aParam);
                break;
            default:
                break;
        }
    }


    /**
     * update tables if required
     *
     * @param array $aParam
     */
    private function updateTables(array $aParam = null)
    {
        // set transaction
        Db::getInstance()->Execute('BEGIN');

        if (!empty($GLOBALS['GMCP_SQL_UPDATE']['table'])) {
            $iCount = 1;
            // loop on each elt to update SQL
            foreach ($GLOBALS['GMCP_SQL_UPDATE']['table'] as $sTable => $sSqlFile) {
                // execute query
                $bResult = Db::getInstance()->ExecuteS('SHOW TABLES LIKE "' . _DB_PREFIX_ . strtolower(_GMCP_MODULE_NAME) . '_' . $sTable . '"');

                // if empty - update
                if (empty($bResult)) {
                    require_once(_GMCP_PATH_CONF . 'install.conf.php');
                    require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

                    // use case - KO update
                    if (!BT_InstallCtrl::run('install', 'sql', _GMCP_PATH_SQL . $sSqlFile)) {
                        $this->aErrors[] = array(
                            'msg' => GMerchantCenterPro::$oModule->l('There is an error around the SQL table update', 'module-update_class'),
                            'code' => intval(190 + $iCount),
                            'file' => $sSqlFile,
                            'context' => GMerchantCenterPro::$oModule->l(
                                'Issue around table update for: ',
                                'module-update_class'
                            ) . $sTable
                        );
                        ++$iCount;
                    }
                }
            }
        }

        if (empty($this->aErrors)) {
            Db::getInstance()->Execute('COMMIT');
        } else {
            Db::getInstance()->Execute('ROLLBACK');
        }
    }


    /**
     * update fields if required
     *
     * @param array $aParam
     */
    private function updateFields(array $aParam = null)
    {
        // set transaction
        Db::getInstance()->Execute('BEGIN');

        if (!empty($GLOBALS['GMCP_SQL_UPDATE']['field'])) {
            $iCount = 1;
            // loop on each elt to update SQL
            foreach ($GLOBALS['GMCP_SQL_UPDATE']['field'] as $sFieldName => $aOption) {
                // execute query
                $bResult = Db::getInstance()->ExecuteS('SHOW COLUMNS FROM ' . _DB_PREFIX_ . strtolower(_GMCP_MODULE_NAME) . '_' . $aOption['table'] . ' LIKE "' . $sFieldName . '"');

                // if empty - update
                if (empty($bResult)) {
                    require_once(_GMCP_PATH_CONF . 'install.conf.php');
                    require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

                    // use case - KO update
                    if (!BT_InstallCtrl::run('install', 'sql', _GMCP_PATH_SQL . $aOption['file'])) {
                        $aErrors[] = array(
                            'field' => $sFieldName,
                            'linked' => $aOption['table'],
                            'file' => $aOption['file']
                        );
                        $this->aErrors[] = array(
                            'msg' => GMerchantCenterPro::$oModule->l(
                                'There is an error around the SQL field update!',
                                'module-update_class'
                            ),
                            'code' => intval(180 + $iCount),
                            'file' => $aOption['file'],
                            'context' => GMerchantCenterPro::$oModule->l('Issue around field update for: ', 'module-update_class') . $sFieldName
                        );
                        ++$iCount;
                    }
                }
            }
        }

        if (empty($this->aErrors)) {
            Db::getInstance()->Execute('COMMIT');
        } else {
            Db::getInstance()->Execute('ROLLBACK');
        }
    }

    /**
     * update hooks if required
     *
     * @param array $aParam
     */
    private function updateHooks(array $aParam = null)
    {
        require_once(_GMCP_PATH_CONF . 'install.conf.php');
        require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // use case - hook register ko
        if (!BT_InstallCtrl::run('install', 'config', array('bHookOnly' => true))) {
            $this->aErrors[] = array(
                'msg' => GMerchantCenterPro::$oModule->l(
                    'There is an error around the HOOKS update!',
                    'module-update_class'
                ),
                'code' => 170,
                'file' => GMerchantCenterPro::$oModule->l(
                    'see the variable $GLOBALS[\'GMCP_HOOKS\'] in the conf/common.conf.php file',
                    'module-update_class'
                ),
                'context' => GMerchantCenterPro::$oModule->l('Issue around hook update', 'module-update_class')
            );
        }
    }


    /**
     * update templates if required
     *
     * @param array $aParam
     */
    private function updateTemplates(array $aParam = null)
    {
        require_once(_GMCP_PATH_LIB_COMMON . 'dir-reader.class.php');

        // get templates files
        $aTplFiles = BT_GmcProDirReader::create()->run(array(
            'path' => _GMCP_PATH_TPL,
            'recursive' => true,
            'extension' => 'tpl',
            'subpath' => true
        ));

        if (!empty($aTplFiles)) {
            $smarty = Context::getContext()->smarty;

            if (method_exists($smarty, 'clearCompiledTemplate')) {
                $smarty->clearCompiledTemplate();
            } elseif (method_exists($smarty, 'clear_compiled_tpl')) {
                foreach ($aTplFiles as $aFile) {
                    $smarty->clear_compiled_tpl($aFile['filename']);
                }
            }
        }
    }


    /**
     * update module admin tab in case of an update
     *
     * @param array $aParam
     */
    private function updateModuleAdminTab(array $aParam = null)
    {
        foreach ($GLOBALS['GMCP_TABS'] as $sModuleTabName => $aTab) {
            if (isset($aTab['oldName'])) {
                if (Tab::getIdFromClassName($aTab['oldName']) != false) {
                    // include install ctrl class
                    require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

                    // use case - if uninstall succeeded
                    if (BT_InstallCtrl::run('uninstall', 'tab', array('name' => $aTab['oldName']))) {
                        // install new admin tab
                        BT_InstallCtrl::run('install', 'tab', array('name' => $sModuleTabName));
                    }
                }
            }
        }
    }

    /**
     * initialize XML files
     *
     * @param array $aParam
     */
    private function updateXmlFiles(array $aParam = null)
    {
        //check the date availability
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-update_class.php');

        $oUpdate = BT_AdminUpdate::create();
        $oUpdate->run('customLabelDate');

        if (
            !empty($aParam['aAvailableData'])
            && is_array($aParam['aAvailableData'])
        ) {
            // require
            require_once(_GMCP_PATH_LIB_COMMON . 'file.class.php');

            $iCount = 1;

            foreach ($aParam['aAvailableData'] as $aData) {

                // check if file exist
                $sFileSuffix = BT_GmcProModuleTools::buildFileSuffix($aData['langIso'], $aData['countryIso'], $aData['currencyIso'], GMerchantCenterPro::$iShopId, 'product');
                $sFilePath = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffix . '.xml';
                $sFileSuffixReviews = BT_GmcProModuleTools::buildFileSuffix($aData['langIso'], $aData['countryIso'], $aData['currencyIso'], GMerchantCenterPro::$iShopId, 'reviews');
                $sFilePathReviews = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffixReviews . '.xml';
                $sFileSuffixLocal = BT_GmcProModuleTools::buildFileSuffix($aData['langIso'], $aData['countryIso'], $aData['currencyIso'], GMerchantCenterPro::$iShopId, 'local');
                $sFilePathLocal = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffixLocal . '.xml';
                $bLocalFileExists = true;

                if (
                    !is_file(_GMCP_SHOP_PATH_ROOT . $sFilePath)
                    && !is_file(_GMCP_SHOP_PATH_ROOT . $sFilePathReviews)
                    && !is_file(_GMCP_SHOP_PATH_ROOT . $sFilePathLocal)
                ) {
                    try {
                        BT_File::create()->write(_GMCP_SHOP_PATH_ROOT . $sFilePath, '');
                        BT_File::create()->write(_GMCP_SHOP_PATH_ROOT . $sFilePathReviews, '');
                        BT_File::create()->write(_GMCP_SHOP_PATH_ROOT . $sFilePathLocal, '');

                        // test if file exists
                        $bProductFileExists = is_file(_GMCP_SHOP_PATH_ROOT . $sFilePath);
                        $bReviewsFileExists = is_file(_GMCP_SHOP_PATH_ROOT . $sFilePathReviews);
                        $bReviewsFileExists = is_file(_GMCP_SHOP_PATH_ROOT . $sFilePathLocal);
                    } catch (Exception $e) {
                        $bProductFileExists = false;
                        $bReviewsFileExists = false;
                        $bLocalFileExists = false;
                    }

                    if (
                        !$bProductFileExists
                        || !$bReviewsFileExists
                        || !$bLocalFileExists
                    ) {
                        $aError = array(
                            'msg' => GMerchantCenterPro::$oModule->l('There is an error around the creation of the data feed XML file in the shop\'s root directory', 'module-update_class'),
                            'code' => intval(160 + $iCount),
                            'file' => _GMCP_SHOP_PATH_ROOT . $sFilePath,
                            'context' => GMerchantCenterPro::$oModule->l(
                                'Issue around the xml files which have to be generated in the shop\'s root directory',
                                'module-update_class'
                            ),
                            'howTo' => GMerchantCenterPro::$oModule->l('Please follow our FAQ about problems when creating XML files at the root of your shop', 'module-update_class') . '&nbsp;=>&nbsp;<i class="icon-question-sign"></i>&nbsp;<a href="' . _GMCP_BT_FAQ_MAIN_URL . 'faq.php?id=21" target="_blank">FAQ</a>'
                        );
                        $this->aErrors[] = $aError;
                        $iCount++;
                    }
                }
            }
        }
    }


    /**
     * update specific configuration options
     *
     * @param string $sType
     */
    private function updateConfiguration($sType)
    {
        switch ($sType) {
            case 'languages':
                $aHomeCat = Configuration::get('GMCP_HOME_CAT');
                if (empty($aHomeCat)) {
                    $aHomeCat = array();
                    foreach (GMerchantCenterPro::$aAvailableLanguages as $aLanguage) {
                        $aHomeCat[$aLanguage['id_lang']] = !empty($GLOBALS['GMCP_HOME_CAT_NAME'][$aLanguage['iso_code']]) ? $GLOBALS['GMCP_HOME_CAT_NAME'][$aLanguage['iso_code']] : '';
                    }
                    // update
                    Configuration::updateValue('GMCP_HOME_CAT', serialize($aHomeCat));
                } elseif (is_array(GMerchantCenterPro::$conf['GMCP_HOME_CAT'])) {
                    // update
                    Configuration::updateValue('GMCP_HOME_CAT', serialize(GMerchantCenterPro::$conf['GMCP_HOME_CAT']));
                }
                break;
            case 'color':
                if (!empty(GMerchantCenterPro::$conf['GMCP_COLOR_OPT'])) {
                    if (is_numeric(GMerchantCenterPro::$conf['GMCP_COLOR_OPT'])) {
                        GMerchantCenterPro::$conf['GMCP_COLOR_OPT'] = array(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']);

                        $aAttributeIds = array();
                        foreach (GMerchantCenterPro::$conf['GMCP_COLOR_OPT'] as $iAttributeId) {
                            $aAttributeIds['attribute'][] = $iAttributeId;
                        }
                        Configuration::updateValue('GMCP_COLOR_OPT', serialize($aAttributeIds));
                    }
                }
                break;
            case 'size':
                if (!empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT'])) {
                    if (is_numeric(GMerchantCenterPro::$conf['GMCP_SIZE_OPT'])) {
                        GMerchantCenterPro::$conf['GMCP_SIZE_OPT'] = array(GMerchantCenterPro::$conf['GMCP_SIZE_OPT']);

                        $aAttributeIds = array();
                        foreach (GMerchantCenterPro::$conf['GMCP_SIZE_OPT'] as $iAttributeId) {
                            $aAttributeIds['attribute'][] = $iAttributeId;
                        }
                        Configuration::updateValue('GMCP_SIZE_OPT', serialize($aAttributeIds));
                    }
                }
                break;
            default:
                break;
        }
    }


    /**
     * update configuration from GMC
     *
     * @throws
     * @param string $sType
     */
    private function updateFromGmc()
    {
        $bGmcInstalled = BT_GmcProModuleTools::isInstalled('gmerchantcenter', array(), false, true);
        $bUpdate = GMerchantCenterPro::$conf['GMCP_IMPORT_FROM_GMC'];

        //test if the module gmerchantcenter is installed for importation tool
        if (
            !empty($bGmcInstalled)
            && !empty($bUpdate)
        ) {
            $bSucess = 0;

            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

            foreach ($GLOBALS['GMCP_IMPORT_TABLE_GMC'] as $sKey => $sValue) {
                if (BT_GmcProModuleDao::updateFromGmc($sValue['newTable'], $sValue['oldTable'])) {
                    $bSucess = 1;
                }
            }

            if (!empty($bSucess)) {
                if (!Configuration::updateValue('GMCP_IMPORT_FROM_GMC', 0)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during configuration import', 'admin-update_class') . '.', 700);
                }
            }
        }
    }

    /**
     * returns errors
     *
     * @return array
     */
    public function getErrors()
    {
        return empty($this->aErrors) ? false : $this->aErrors;
    }

    /**
     * manages singleton
     *
     * @return array
     */
    public static function create()
    {
        static $oModuleUpdate;

        if (null === $oModuleUpdate) {
            $oModuleUpdate = new BT_GmcProModuleUpdate();
        }
        return $oModuleUpdate;
    }
}
