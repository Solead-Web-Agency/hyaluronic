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

class BT_AdminGenerate implements BT_IAdmin
{
    /**
     * @var object $oCurrentObj : current object generate by _generateXml
     */
    public $oCurrentObj = null;

    /**
     * generate data feed content
     *
     * @param string $sType => define which method to execute
     * @param array $aParam
     * @return array
     */
    public function run($sType, array $aParam = null)
    {
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        // set variables
        $aData = array();

        switch ($sType) {
            case 'xml': // use case - generate XML file
            case 'flyOutput': // use case - generate XML file on fly output
            case 'cron': // use case - generate XML file via the cron execution
                // execute match function
                $aData = call_user_func_array(array($this, 'generate' . ucfirst($sType)), array($aParam));
                break;
            default:
                break;
        }

        return $aData;
    }

    /**
     * generate an XML file
     *
     * @param array $aPost
     * @throws
     * @return array
     */
    private function generateXml($aParams = null)
    {
        $sType = strtolower(Tools::getValue('sFeedType'));

        if (empty($sType)) {
            $sType = strtolower(Tools::getValue('feed_type'));
        }

        // if valid controller
        if (file_exists(_GMCP_PATH_LIB_XML . 'xml-generate-' . $sType . '_class.php')) {
            // require
            require_once(_GMCP_PATH_LIB_XML . 'xml-generate-' . $sType . '_class.php');

            // set class name
            $sClassName = 'BT_XmlGenerate' . ucfirst($sType);
            try {
                $oReflection = new ReflectionClass($sClassName);

                if ($oReflection->isInstantiable()) {
                    $this->oCurrentObj = $oReflection->newInstance($sType, $aParams);
                    return $this->oCurrentObj->generate();
                } else {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => object isn\'t instantiable', 'xml-generate_class'), 1000);
                }
            } catch (ReflectionException $e) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => invalid object', 'xml-generate_class'), 1001);
            }
        } else {
            throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => the object file doesn\'t exist', 'xml-generate_class'), 1002);
        }
    }

    /**
     * generate the XML feed by the fly output
     *
     * @param array $aPost
     * @return array
     */

    private function generateFlyOutput(array $aPost = null)
    {
        $aAssign = array();

        try {
            // get the token
            $sToken = Tools::getValue('token');

            if (
                !empty(GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'])
                && $sToken != GMerchantCenterPro::$conf['GMCP_FEED_TOKEN']
            ) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Invalid security token', 'admin-generate_class') . '.', 810);
            }

            // get data feed params
            $_POST['iShopId'] = !empty(Tools::getValue('id_shop')) ? (int)Tools::getValue('id_shop') : GMerchantCenterPro::$iShopId;
            $_POST['iLangId'] = !empty(Tools::getValue('gmcp_lang_id'))  ? Tools::getValue('gmcp_lang_id') : Tools::getValue('id_lang');
            $_POST['sLangIso'] = BT_GmcProModuleTools::getLangIso($_POST['iLangId']);
            $_POST['sCountryIso'] = Tools::getValue('country');
            $_POST['sCurrencyIso'] = Tools::getValue('currency_iso');
            $_POST['iFloor'] = 0;
            $_POST['iTotal'] = 0;
            $_POST['iStep'] = 0;
            $_POST['iProcess'] = 0;
            $_POST['bOutput'] = 1;
            $_POST['sFeedType'] = Tools::getValue('feed_type');
            $_POST['bExcludedProduct'] = BT_GmcProExclusionDao::isExcludedProduct();
           
            // set the filename
            $sFileSuffix = BT_GmcProModuleTools::buildFileSuffix($_POST['sLangIso'], $_POST['sCountryIso'], $_POST['sCurrencyIso'], 0, $_POST['sType']);
            $_POST['sFilename'] = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffix . '.xml';
           
            // execute the generate XML function
            $this->generateXml($_POST['sType']);
  
        } catch (Exception $e) {
            $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_GENERATE_OUTPUT,
            'assign' => $aAssign,
        );
    }

    /**
     * generate the XML feed by the cron execution
     *
     * @param array $aPost
     * @return array
     */
    private function generateCron(array $aPost = null)
    {
        $aAssign = array();
        $aLang = array();
        $aLocalisation = array();

        try {
            // get the token
            $sToken = Tools::getValue('token');
            $sType = Tools::getValue('feed_type') != false ? Tools::getValue('feed_type') : Tools::getValue('sFeedType');
            // use case - individual data feed cron
            $sCountry = Tools::getValue('country');
            $iLang = Tools::getValue('gmcp_lang_id');
            $sCurrency = Tools::getValue('currency_iso');
            $sUrlSuffix = $sType;

            // get the token if necessary
            if (
                !empty(GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'])
                && $sToken != GMerchantCenterPro::$conf['GMCP_FEED_TOKEN']
            ) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Invalid security token', 'admin-generate_class') . '.', 820);
            }

            // check if this is the first time execution of the CRON
            $_POST['aLangIds'] = Tools::getValue('aLangIds');
            $_POST['iShopId'] = !empty(Tools::getValue('id_shop')) ? (int)Tools::getValue('id_shop') : GMerchantCenterPro::$iShopId;
            $_POST['sFeedType'] = $sType;

            // first execution
            if (empty($_POST['aLangIds'])) {
                // use case - individual data feed cron
                if (
                    !empty($sCountry)
                    && !empty($iLang)
                ) {
                    $aDataFeedCron[] = BT_GmcProModuleTools::getLangIso($iLang) . '_' . $sCountry . '_' . $sCurrency;
                } // use case - the general data feed cron URL
                else {
                    // get selected data feed
                    $aDataFeedCron = GMerchantCenterPro::$conf['GMCP_CHECK_EXPORT'];
                }

                foreach ($aDataFeedCron as $iKey => &$sLangIso) {
                    $sLangIso = Tools::strtolower($sLangIso);
                }

                // set the available data feed
                foreach (GMerchantCenterPro::$aAvailableLanguages as $aLanguage) {
                    // set the cookie id lang to get the good language
                    Context::getContext()->cookie->id_lang = $aLanguage['id_lang'];

                    // get the matching languages
                    foreach ($GLOBALS['GMCP_AVAILABLE_COUNTRIES'][$aLanguage['iso_code']] as $sCountryIso => $aLocaleData) {
                        // Only if currency is installed
                        foreach ($aLocaleData['currency'] as $sCurrency) {
                            if (
                                in_array(Tools::strtolower($aLanguage['iso_code'] . '_' . $sCountryIso . '_' . $sCurrency), $aDataFeedCron)
                                && Currency::getIdByIsoCode($sCurrency)
                            ) {
                                $aLocalisation[] = $aLanguage['iso_code'] . '_' . $sCountryIso . '_' . $sCurrency;
                            }
                        }
                    }
                }
                require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

                if (!empty($aLocalisation[0])) {
                    list($sLangIso, $sCountryIso, $sCurrencyIso) = explode('_', $aLocalisation[0]);
                    $_POST['iLangId'] = BT_GmcProModuleTools::getLangId($sLangIso);
                    $_POST['iCurrentLang'] = 0;
                    $_POST['sLangIso'] = $sLangIso;
                    $_POST['sCountryIso'] = $sCountryIso;
                    $_POST['sCurrencyIso'] = $sCurrencyIso;
                    $_POST['iStep'] = GMerchantCenterPro::$conf['GMCP_AJAX_CYCLE'];
                    $_POST['iFloor'] = 0;
                    $_POST['iProcess'] = 0;
                    $_POST['bExcludedProduct'] = BT_GmcProExclusionDao::isExcludedProduct();
                }

                // get the total products to export
                $_POST['iTotal'] = BT_GmcProModuleDao::getProductIds($_POST['iShopId'], (int) GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'], true);

                // set the filename
                $sFileSuffix = BT_GmcProModuleTools::buildFileSuffix($_POST['sLangIso'], $_POST['sCountryIso'], $_POST['sCurrencyIso'], $_POST['iShopId'], $sUrlSuffix);
                $_POST['sFilename'] = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffix . '.xml';

                // get lang
                $_POST['aLangIds'] = $aLocalisation;
            } else {
                $_POST['iCurrentLang'] = Tools::getValue('iCurrentLang');
                $_POST['aLangIds'] = Tools::getValue('aLangIds');

                list($sLangIso, $sCountryIso, $sCurrencyIso) = explode('_', $_POST['aLangIds'][$_POST['iCurrentLang']]);

                if (!empty($aDataFeedTax)) {
                    $bUseTax = in_array($sLangIso . '_' . $sCountryIso . '_' . $sCurrencyIso, $aDataFeedTax) ? 1 : 0;
                }

                // get data feed params
                $_POST['iLangId'] = BT_GmcProModuleTools::getLangId($sLangIso);
                $_POST['sLangIso'] = $sLangIso;
                $_POST['sCountryIso'] = $sCountryIso;
                $_POST['iFloor'] = Tools::getValue('iFloor');
                $_POST['sCurrencyIso'] = $sCurrencyIso;
                $_POST['iTotal'] = Tools::getValue('iTotal');
                $_POST['iStep'] = Tools::getValue('iStep');
                $_POST['iProcess'] = Tools::getValue('iProcess');
                $_POST['bExcludedProduct'] = Tools::getValue('bExcludedProduct');

                // set the filename
                $sFileSuffix = BT_GmcProModuleTools::buildFileSuffix($_POST['sLangIso'], $_POST['sCountryIso'], $_POST['sCurrencyIso'], $_POST['iShopId'], $sUrlSuffix);
                $_POST['sFilename'] = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffix . '.xml';
            }

            // execute the generate XML function
            $aContent = $this->generateXml($sType);

            if (empty($aContent['assign']['aErrors'])) {
                // handle the cron URL
                $sCronUrl = Context::getContext()->link->getModuleLink(_GMCP_MODULE_SET_NAME, _GMCP_CTRL_CRON, array('id_shop' =>  GMerchantCenterPro::$iShopId));

                // check if the feed protection is activated
                if (!empty($sToken)) {
                    $sCronUrl .= '&token=' . $sToken;
                }
                if (!empty($sType)) {
                    $sCronUrl .= '&sFeedType=' . $sType;
                }

                // set the base cron URL
                $sCronUrl .= '&aLangIds[]=' . implode('&aLangIds[]=', $_POST['aLangIds']) . '&iTotal=' . (int) $_POST['iTotal'] . '&iStep=' . (int) $_POST['iStep'] . '&bExcludedProduct=' . $_POST['bExcludedProduct'];

                if (
                    !empty($aContent['assign']['bContinueStatus'])
                    && empty($aContent['assign']['bFinishStatus'])
                ) {
                    $_POST['iFloor'] += $_POST['iStep'];
                    $_POST['iProcess'] = $aContent['assign']['process'];
                    // header location
                    header("Location: " . $sCronUrl . '&iCurrentLang=' . $_POST['iCurrentLang'] . '&iFloor=' . $_POST['iFloor'] . '&iProcess=' . $_POST['iProcess']);
                    exit(0);
                } elseif (
                    empty($aContent['assign']['bContinueStatus'])
                    && !empty($aContent['assign']['bFinishStatus'])
                    && isset($_POST['aLangIds'][$_POST['iCurrentLang'] + 1])
                ) {
                    // header location
                    header("Location: " . $sCronUrl . '&iCurrentLang=' . ($_POST['iCurrentLang'] + 1) . '&iFloor=0&iProcess=0');
                    exit(0);
                }
            }
        } catch (Exception $e) {
            $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_GENERATE_OUTPUT,
            'assign' => $aAssign,
        );
    }

    /**
     * set singleton
     *
     * @return obj
     */
    public static function create()
    {
        static $oGenerate;

        if (null === $oGenerate) {
            $oGenerate = new BT_AdminGenerate();
        }
        return $oGenerate;
    }
}
