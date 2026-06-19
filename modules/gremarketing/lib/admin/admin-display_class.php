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

class BT_AdminDisplay implements BT_IAdmin
{
    /**
     * display all configured data admin tabs
     *
     * @param array $aParam
     * @return array
     */
    public function run(array $aParam = null)
    {
        // set variables
        $aDisplayInfo = array();

        // get type
        $aParam['sType'] = empty($aParam['sType']) ? 'tabs' : $aParam['sType'];

        switch ($aParam['sType']) {
            case 'tabs': // use case - display first page with all tabs
            case 'check': // use case - display technical check page
            case 'basic': // use case - display basic settings page
            case 'dynamic': // use case - display dynamic remarketing settings page
                // execute match function
                $aDisplayInfo = call_user_func_array(array($this, 'display' . ucfirst($aParam['sType'])), array($aParam));
                break;
            default:
                break;
        }
        // use case - generic assign
        if (!empty($aDisplayInfo)) {
            $aDisplayInfo['assign'] = array_merge($aDisplayInfo['assign'], $this->assign());
        }

        return $aDisplayInfo;
    }

    /**
     * assigns transverse data
     *
     * @return array
     */
    private function assign()
    {
        // set smarty variables
        $aAssign = array(
            'sURI' => BT_GRModuleTools::truncateUri(array('&iPage', '&sAction')),
            'aQueryParams' => $GLOBALS['GR_REQUEST_PARAMS'],
            'iCurrentLang' => intval(GRemarketing::$iCurrentLang),
            'sCurrentLang' => GRemarketing::$sCurrentLang,
            'sFaqLang' => (GRemarketing::$sCurrentLang == 'fr' ? 'fr' : 'en'),
            'sTs' => time(),
            'bCompare16' => GRemarketing::$bCompare16,
            'bCompare17' => GRemarketing::$bCompare17,
            'oModuleGoogle' => false,
            'bGoogleDynamic' => GRemarketing::$conf['GR_REMARKETING_DYNAMIC'],
            'sLoadingImg' => _GR_URL_IMG . 'admin/' . _GR_LOADER_GIF,
            'sBigLoadingImg' => _GR_URL_IMG . 'admin/' . _GR_LOADER_GIF_BIG,
            'sHeaderInclude' => BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_HEADER),
            'sErrorInclude' => BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_ERROR),
            'sConfirmInclude' => BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_CONFIRM),
            'sFaqLink' => 'https://faq.businesstech.fr/faq.php?id=134'
        );

        $oGmc = BT_GRModuleTools::isInstalled('gmerchantcenter', [], true);
        $oGmcPro = BT_GRModuleTools::isInstalled('gmerchantcenterpro', [], true);

        if (
            !empty($oGmc)
            || !empty($oGmcPro)
        ) {
            $aAssign['oModuleGoogle'] = true;

            if (
                empty($aAssign['bCompare17'])
                && !empty(GRemarketing::$conf['GR_REMARKETING_DYNAMIC'])
                && (Configuration::get('GMERCHANTCENTER_P_COMBOS') == 1
                    || Configuration::get('GMCP_P_COMBOS') == 1)
            ) {
                if (
                    !empty($oGmcPro)
                    && version_compare($oGmcPro->version, '1.6.14', '<')
                ) {
                    $aAssign['bWrongModuleGoogleVersion'] = true;
                } elseif (
                    !empty($oGmc)
                    && version_compare($oGmc->version, '4.7.11', '<')
                ) {
                    $aAssign['bWrongModuleGoogleVersion'] = true;
                }
            }
        }

        return $aAssign;
    }

    /**
     * displays admin's first page with all tabs
     *
     * @param array $aPost
     * @return array
     */
    private function displayTabs(array $aPost)
    {
        // set smarty variables
        $aAssign = array(
            'sDocUri' => _MODULE_DIR_ . _GR_MODULE_SET_NAME . '/',
            'sDocName' => 'readme_' . ((GRemarketing::$sCurrentLang == 'fr') ? 'fr' : 'en') . '.pdf',
            'sCurrentIso' => Language::getIsoById(GRemarketing::$iCurrentLang),
            'bHideConfiguration' => BT_GRWarnings::create()->bStopExecution,
            'sContactUs' => !empty($iSupportToUse) ? _GR_SUPPORT_URL . ((GRemarketing::$sCurrentLang == 'fr') ? 'fr/contactez-nous' : 'en/contact-us') : _GR_SUPPORT_URL . ((GRemarketing::$sCurrentLang == 'fr') ? 'fr/ecrire-au-developpeur?id_product=' . _GR_SUPPORT_ID : 'en/write-to-developper?id_product=' . _GR_SUPPORT_ID),
            'sRateUrl' => !empty($iSupportToUse) ? _GR_SUPPORT_URL . ((GRemarketing::$sCurrentLang == 'fr') ? 'fr/modules-prestashop-google-et-publicite/39-module-google-remarketing-dynamique-0656272937270.html' : 'en/google-and-advertising-modules-for-prestashop/39-module-google-remarketing-dynamic-0656272937270.html') : _GR_SUPPORT_URL . ((GRemarketing::$sCurrentLang == 'fr') ? '/fr/ratings.php' : '/en/ratings.php'),
            'sCrossSellingUrl' => !empty($iSupportToUse) ? _GR_SUPPORT_URL . '?utm_campaign=internal-module-ad&utm_source=banniere&utm_medium=' . _GR_MODULE_SET_NAME : _GR_SUPPORT_URL . GRemarketing::$sCurrentLang . '/6_business-tech',
            'sCrossSellingImg' => (GRemarketing::$sCurrentLang == 'fr') ? _GR_URL_IMG . 'admin/module_banner_cross_selling_FR.jpg' : _GR_URL_IMG . 'admin/module_banner_cross_selling_EN.jpg',
        );

        // use case - get display data of check page
        $aData = $this->displayCheck($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of basic settings
        $aData = $this->displayBasic($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of dynamic settings
        $aData = $this->displayDynamic($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // assign all included templates files
        $aAssign['sCheckInclude'] = BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_TECH_CHECK);
        $aAssign['sBasicInclude'] = BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_BASIC_SETTINGS);
        $aAssign['sDynamicInclude'] = BT_GRModuleTools::getTemplatePath(_GR_PATH_TPL_NAME . _GR_TPL_ADMIN_PATH . _GR_TPL_DYNAMIC_SETTINGS);
        $aAssign['sModuleVersion'] = GRemarketing::$oModule->version;

        // set css and js use
        $GLOBALS['GR_USE_JS_CSS']['bUseJqueryUI'] = true;

        return array(
            'tpl' => _GR_TPL_ADMIN_PATH . _GR_TPL_BODY,
            'assign' => array_merge($aAssign, $GLOBALS['GR_USE_JS_CSS']),
        );
    }


    /**
     * displays check settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayCheck(array $aPost)
    {
        if (GRemarketing::$sQueryMode == 'xhr') {
            // clean headers
            @ob_end_clean();
        }

        // set smarty variables
        $aAssign = array(
            'sValidImgUrl' => _GR_URL_IMG . 'admin/icon-valid.png',
            'sInfoImgUrl' => _GR_URL_IMG . 'admin/icon-info.png',
            'sAttentionImgUrl' => _GR_URL_IMG . 'admin/icon-attention.png',
            'sInvalidImgUrl' => _GR_URL_IMG . 'admin/icon-attention.png',
            'bHtmlCompression' => (BT_GRWarnings::create()->run('configuration', 'PS_HTML_THEME_COMPRESSION') ? false : true),
            'bJsCompression' => (BT_GRWarnings::create()->run('configuration', 'PS_JS_HTML_THEME_COMPRESSION') ? false : true),
        );

        return array(
            'tpl' => _GR_TPL_ADMIN_PATH . _GR_TPL_TECH_CHECK,
            'assign' => $aAssign,
        );
    }


    /**
     * displays basic settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayBasic(array $aPost)
    {
        // set smarty variables
        $aAssign = array(
            'iGoogleId' => GRemarketing::$conf['GR_REMARKETING_ID'],
            'bUserId' => GRemarketing::$conf['GR_USER_ID'],
        );

        return array(
            'tpl' => _GR_TPL_ADMIN_PATH . _GR_TPL_BASIC_SETTINGS,
            'assign' => $aAssign,
        );
    }


    /**
     * displays dynamic remarketing settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayDynamic(array $aPost)
    {
        // detect if GMC PRO is installed
        $sGoogleModulePrefix = BT_GRModuleTools::isInstalled('gmerchantcenterpro') ? 'GMCP' : 'GMERCHANTCENTER';

        // set smarty variables
        $aAssign = array(
            'bGoogleDynamic' => GRemarketing::$conf['GR_REMARKETING_DYNAMIC'],
            'sGooglePrefix' => GRemarketing::$conf['GR_GOOGLE_PREFIX'],
            'sGmcPrefix' => ((!BT_GRWarnings::create()->run('configuration', $sGoogleModulePrefix . '_ID_PREFIX')) ? Configuration::get($sGoogleModulePrefix . '_ID_PREFIX') : ''),
            'sSeparator' => GRemarketing::$conf['GR_COMBO_SEPARATOR'],
        );

        return array(
            'tpl' => _GR_TPL_ADMIN_PATH . _GR_TPL_DYNAMIC_SETTINGS,
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
        static $oDisplay;

        if (null === $oDisplay) {
            $oDisplay = new BT_AdminDisplay();
        }
        return $oDisplay;
    }
}
