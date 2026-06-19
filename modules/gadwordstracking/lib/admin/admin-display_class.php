<?php
/**
 * admin-display_class.php file defines method to display content tabs of admin page
 */


class BT_AdminDisplay implements BT_IAdmin
{
    /**
     * @var array $aFlagIds : array for all flag ids used in option translation
     */
    protected $aFlagIds = array();

    /**
     * display all configured data admin tabs
     *
     * @param array $aParam
     *
     * @return array
     */
    public function run(array $aParam = null)
    {
        // set variables
        $aDisplayInfo = array();

        // get type
        $aParam['sType'] = empty($aParam['sType']) ? 'tabs' : $aParam['sType'];

        switch ($aParam['sType']) {
            case 'tabs' : // use case - display first page with all tabs
            case 'check' : // use case - display technical check page
            case 'advice' : // use case - display advice page
            case 'basic' : // use case - display basic settings page
            case 'advanced' : // use case - display advanced settings page
            case 'javascript' : // use case - display javascript example page
                // execute match function
                $aDisplayInfo = call_user_func_array(array($this, 'display' . ucfirst($aParam['sType']),), array($aParam));
                break;
            default :
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
        $iSupportToUse = _GACT_SUPPORT_BT;

        // set smarty variables
        $aAssign = array(
            'sURI'             => BT_GactModuleTools::truncateUri(array('&sAction')),
            'sCtrlParamName'   => _GACT_PARAM_CTRL_NAME,
            'sController'      => _GACT_ADMIN_CTRL,
            'aQueryParams'     => $GLOBALS['GACT_REQUEST_PARAMS'],
            'iCurrentLang'     => intval(GAdwordsTracking::$iCurrentLang),
            'sCurrentLang'     => GAdwordsTracking::$sCurrentLang,
            'sFaqLang'         => (GAdwordsTracking::$sCurrentLang == 'fr' ? 'fr' : 'en'),
            'sTs'              => time(),
            'bVersion16'       => GAdwordsTracking::$bCompare16,
            'sLoadingImg'      => _GACT_URL_IMG . 'admin/loader.gif',
            'sHeaderInclude'   => BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_HEADER),
            'sErrorInclude'    => BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_ERROR),
            'sConfirmInclude'  => BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_CONFIRM),
            'bDisplayAdvice'   => GAdwordsTracking::$conf['GACT_DISPLAY_ADVICE'],
            'sDocUri'          => _MODULE_DIR_ . _GACT_MODULE_SET_NAME . '/',
            'sDocName'         => 'readme_' . ((GAdwordsTracking::$sCurrentLang == 'fr') ? 'fr' : 'en') . '.pdf',
            'sCurrentIso'      => Language::getIsoById(GAdwordsTracking::$iCurrentLang),
            'sContactUs'       => !empty($iSupportToUse) ? _GACT_SUPPORT_URL . ((GAdwordsTracking::$sCurrentLang == 'fr') ? 'fr/contactez-nous' : 'en/contact-us') : _GACT_SUPPORT_URL . ((GAdwordsTracking::$sCurrentLang == 'fr') ? 'fr/ecrire-au-developpeur?id_product=' . _GACT_SUPPORT_ID : 'en/write-to-developper?id_product=' . _GACT_SUPPORT_ID),
            'sRateUrl'         => !empty($iSupportToUse) ? _GACT_SUPPORT_URL . ((GAdwordsTracking::$sCurrentLang == 'fr') ? 'fr/modules-prestashop-google-et-publicite/42-google-adwords-conversion-tracking-pro-0656272469382.html' : 'en/google-and-advertising-modules-for-prestashop/42-google-adwords-conversion-tracking-pro-0656272469382.html') : _GACT_SUPPORT_URL . ((GAdwordsTracking::$sCurrentLang == 'fr') ? '/fr/ratings.php' : '/en/ratings.php'),
            'sCrossSellingUrl' => !empty($iSupportToUse) ? _GACT_SUPPORT_URL . '?utm_campaign=internal-module-ad&utm_source=banniere&utm_medium=' . _GACT_MODULE_SET_NAME : _GACT_SUPPORT_URL . GAdwordsTracking::$sCurrentLang . '/6_business-tech',
            'sCrossSellingImg' => (GAdwordsTracking::$sCurrentLang == 'fr') ? _GACT_URL_IMG . 'admin/module_banner_cross_selling_FR.jpg' : _GACT_URL_IMG . 'admin/module_banner_cross_selling_EN.jpg',
        );

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
            'bHideConfiguration' => BT_GactWarning::create()->bStopExecution,
        );

        // use case - get display data of basic settings
        $aData = $this->displayBasic($aPost);
        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of advance settings
        $aData = $this->displayAdvanced($aPost);
        $aAssign = array_merge($aAssign, $aData['assign']);

        // assign all included templates files
        $aAssign['sBasicInclude'] = BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_BASIC_SETTINGS);
        $aAssign['sAdvancedInclude'] = BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_ADVANCED_SETTINGS);
        $aAssign['sModuleVersion'] = GAdwordsTracking::$oModule->version;
        $aAssign['sWelcome'] = BT_GactModuleTools::getTemplatePath(_GACT_PATH_TPL_NAME . _GACT_TPL_ADMIN_PATH . _GACT_TPL_WELCOME);

        // set css and js use
        $GLOBALS['GACT_USE_JS_CSS']['bUseJqueryUI'] = true;

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_BODY,
            'assign' => array_merge($aAssign, $GLOBALS['GACT_USE_JS_CSS']),
        );
    }


    /**
     * displays advice settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayAdvice(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        $aAssign = array(
            'sGoogleImageUrl' => _GACT_URL_IMG . 'admin/google-logo.png',
        );

        // force xhr mode activated
        GAdwordsTracking::$sQueryMode = 'xhr';

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_ADVICE,
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
        $aAssign = array('iConversionId' => GAdwordsTracking::$conf['GACT_CONVERSION_ID'],
            'sConversionLabel' => GAdwordsTracking::$conf['GACT_CONVERSION_LABEL'],
        );

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_BASIC_SETTINGS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays advanced settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayAdvanced(array $aPost)
    {
        // set smarty variables
        $aAssign = array(
            'bUseTax'        => GAdwordsTracking::$conf['GACT_USE_TAX'],
            'bUseShipping'   => GAdwordsTracking::$conf['GACT_USE_SHIPPING'],
            'bUseWrapping'   => GAdwordsTracking::$conf['GACT_USE_WRAPPING'],
        );

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_ADVANCED_SETTINGS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays basic settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayJavascript(array $aPost)
    {
        // clean headers
        ob_end_clean();

        // set smarty variables
        $aAssign = array('iConversionId' => GAdwordsTracking::$conf['GACT_CONVERSION_ID'],
            'sConversionLabel' => GAdwordsTracking::$conf['GACT_CONVERSION_LABEL'],
            'sCurrency' => BT_GactModuleTools::getCurrency('iso_code')
        );

        $aAssign['sGoogleJs'] = htmlentities(GAdwordsTracking::$oModule->displayModule(_GACT_TPL_ADMIN_PATH . _GACT_TPL_GOOGLE_JAVASCRIPT, $aAssign));

        // force xhr mode activated
        GAdwordsTracking::$sQueryMode = 'xhr';

        return array(
            'tpl'    => _GACT_TPL_ADMIN_PATH . _GACT_TPL_RENDERING_GOOGLE,
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
