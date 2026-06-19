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

class BT_AdminDisplay implements BT_IAdmin
{
    /*
     * display all configured data admin tabs
     *
     * @param string $sType => define which method to execute
     * @param array $aParam
     * @return array
     */
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

    public function run($sType, array $aParam = null)
    {
        // set variables
        $aDisplayData = array();

        if (empty($sType)) {
            $sType = 'tabs';
        }

        switch ($sType) {
            case 'tabs': // use case - display first page with all tabs
            case 'stepPopup': // use case - advice basics settings page
            case 'basics': // use case - display basics settings page
            case 'prerequisites': // use case - display prerequisites
            case 'feed': // use case - display feed settings page
            case 'advancedFeed': // use case - display feed settings page
            case 'google': // use case - display google settings page
            case 'googleCategories': // use case - display google categories settings page
            case 'customLabel': // use case - display google custom label settings popup
            case 'customLabelProduct': // use case - display google custom label settings popup
            case 'autocomplete': // use case - display autocomplete for google categories
            case 'feedList': // use case - display feed list settings page
            case 'reporting': // use case - display reporting settings page
            case 'reportingBox': // use case - display reporting fancybox
            case 'searchProduct': // use case - handle products autocomplete
            case 'exclusionRule': // use case - handle the rules exclusion
            case 'excludeValue': // use case - the exclusion rules values
            case 'rulesSummary': // use case - exclusion rules summary
            case 'exclusionRuleProducts': // use case - the product concerned by an exclusion rules
            case 'inventory': // use case - for iventory feed
                // include
                require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

                // execute match function
                $aDisplayData = call_user_func_array(array($this, 'display' . ucfirst($sType)), array($aParam));
                break;
            case 'tag': // use case - display adult tag settings page
                // include
                require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

                // execute match function
                $aDisplayData = call_user_func_array(array($this, 'displayAdvancedTagCategory'), array($aParam));
                break;
            default:
                break;
        }
        // use case - generic assign
        if (!empty($aDisplayData)) {
            $aDisplayInfo['assign']['bMultiShop'] = BT_GmcProModuleTools::checkGroupMultiShop();
            $aDisplayData['assign'] = array_merge($aDisplayData['assign'], $this->assign());
        }

        return $aDisplayData;
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
            'sURI' => BT_GmcProModuleTools::truncateUri(array('&sAction')),
            'sCtrlParamName' => _GMCP_PARAM_CTRL_NAME,
            'sController' => _GMCP_ADMIN_CTRL,
            'aQueryParams' => $GLOBALS['GMCP_REQUEST_PARAMS'],
            'sDisplay' => Tools::getValue('sDisplay'),
            'iCurrentLang' => intval(GMerchantCenterPro::$iCurrentLang),
            'sCurrentLang' => GMerchantCenterPro::$sCurrentLang,
            'sCurrentIso' => Language::getIsoById(GMerchantCenterPro::$iCurrentLang),
            'sFaqLang' => BT_GmcProModuleTools::getFaqLang(GMerchantCenterPro::$sCurrentLang),
            'sTs' => time(),
            'bAjaxMode' => (GMerchantCenterPro::$sQueryMode == 'xhr' ? true : false),
            'bCompare16' => GMerchantCenterPro::$bCompare16,
            'bPsVersion1606' => GMerchantCenterPro::$bCompare1606,
            'bCompare1608' => GMerchantCenterPro::$bCompare1608,
            'sLoadingImg' => _GMCP_URL_IMG . _GMCP_LOADER_GIF,
            'sHeaderInclude' => BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_HEADER),
            'sErrorInclude' => BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR),
            'sConfirmInclude' => BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_CONFIRM),
            'bCompare17' => GMerchantCenterPro::$bCompare17,
            'bConfigureStep1' => GMerchantCenterPro::$conf['GMCP_CONF_STEP_1'],
            'bConfigureStep2' => GMerchantCenterPro::$conf['GMCP_CONF_STEP_2'],
            'bConfigureStep3' => GMerchantCenterPro::$conf['GMCP_CONF_STEP_3'],
        );

        return $aAssign;
    }

    /**
     *  method displays advice form
     *
     * @param array $aPost
     * @return array
     */
    private function displayStepPopup(array $aPost = null)
    {
        $aAssign = array();

        // clean headers
        @ob_end_clean();

        // force xhr mode activated
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_STEP_POPUP,
            'assign' => $aAssign,
        );
    }

    /**
     * displays admin's first page with all tabs
     *
     * @param array $aPost
     * @return array
     */
    private function displayTabs(array $aPost = null)
    {
        $iSupportToUse = _GMCP_SUPPORT_BT;
        // set smarty variables
        $aAssign = array(
            'sDocUri' => _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/',
            'sDocName' => 'readme_' . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? 'fr' : 'en') . '.pdf',
            'sCurrentIso' => Language::getIsoById(GMerchantCenterPro::$iCurrentLang),
            'sCrossSellingUrl' => !empty($iSupportToUse) ? _GMCP_SUPPORT_URL . '?utm_campaign=internal-module-ad&utm_source=banniere&utm_medium=' . _GMCP_MODULE_SET_NAME : _GMCP_SUPPORT_URL . GMerchantCenterPro::$sCurrentLang . '/6_business-tech',
            'sCrossSellingImg' => (GMerchantCenterPro::$sCurrentLang == 'fr') ? _GMCP_URL_IMG . 'admin/module_banner_cross_selling_FR.jpg' : _GMCP_URL_IMG . 'admin/module_banner_cross_selling_EN.jpg',
            'sContactUs' => !empty($iSupportToUse) ? _GMCP_SUPPORT_URL . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? 'fr/contactez-nous' : 'en/contact-us') : _GMCP_SUPPORT_URL . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? 'fr/ecrire-au-developpeur?id_product=' . _GMCP_SUPPORT_ID : 'en/write-to-developper?id_product=' . _GMCP_SUPPORT_ID),
            'sRateUrl' => !empty($iSupportToUse) ? _GMCP_SUPPORT_URL . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? 'fr/modules-prestashop-google-et-publicite/45-google-merchant-center-pro-module-pour-prestashop-0656272492397.html' : 'en/google-and-advertising-modules-for-prestashop/45-google-merchant-center-pro-module-for-prestashop-0656272492397.html') : _GMCP_SUPPORT_URL . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? '/fr/ratings.php' : '/en/ratings.php'),
        );

        // check curl_init and file_get_contents to get the distant Google taxonomy file
        BT_GmcProWarning::create()->run('directive', 'allow_url_fopen', array(), true);
        $bTmpStopExec = BT_GmcProWarning::create()->bStopExecution;
        BT_GmcProWarning::create()->bStopExecution = false;
        BT_GmcProWarning::create()->run('function', 'curl_init', array(), true);

        if ($bTmpStopExec && BT_GmcProWarning::create()->bStopExecution) {
            $aAssign['bCurlAndContentStopExec'] = true;
        }

        // check if multi-shop configuration
        if (
            version_compare(_PS_VERSION_, '1.5', '>')
            && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')
            && strpos(Context::getContext()->cookie->shopContext, 'g-') !== false
        ) {
            $aAssign['bMultishopGroupStopExec'] = true;
        }

        // check if shipping weight unit
        $sWeightUnit = Configuration::get('PS_WEIGHT_UNIT');
        if (
            !empty($sWeightUnit)
            && !in_array(Tools::strtolower($sWeightUnit), $GLOBALS['GMCP_WEIGHT_UNITS'])
        ) {
            $aAssign['bWeightUnitStopExec'] = true;
        }

        // check if we hide the config
        if (
            !empty($aAssign['bFileStopExec'])
            || !empty($aAssign['bCurlAndContentStopExec'])
            || !empty($aAssign['bMultishopGroupStopExec'])
            || !empty($aAssign['bWeightUnitStopExec'])
        ) {
            $aAssign['bHideConfiguration'] = true;
        }

        $aAssign['autocmp_js'] = __PS_BASE_URI__ . 'js/jquery/plugins/autocomplete/jquery.autocomplete.js';
        $aAssign['autocmp_css'] = __PS_BASE_URI__ . 'js/jquery/plugins/autocomplete/jquery.autocomplete.css';

        // use case - get display prerequisites
        $aData = $this->displayPrerequisites($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of basics settings
        $aData = $this->displayBasics($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of feed data settings
        $aData = $this->displayFeed($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of discount settings
        $aData = $this->displayAdvancedFeed($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of google settings
        $aData = $this->displayGoogle($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of feed list settings
        $aData = $this->displayFeedList($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of feed list settings
        $aData = $this->displayReporting($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of feed list settings
        $aData = $this->displayInventory($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);

        // use case - get display data of feed list settings
        $aData = $this->displayInventoryFeed($aPost);

        $aAssign = array_merge($aAssign, $aData['assign']);


        // assign all included templates files
        $aAssign['sWelcome'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_WELCOME);
        $aAssign['sPrerequisitesInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_PREREQUISITES);
        $aAssign['sShoppingAction'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GSA);
        $aAssign['sBasicsInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_BASICS);
        $aAssign['sFeedInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_SETTINGS);
        $aAssign['sGoogleInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_SETTINGS);
        $aAssign['sAdvanceFeed'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ADVANCED_SETTINGS);
        $aAssign['sLocalInventoryFeed'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_INVENTORY_FEED);
        $aAssign['sFeedListInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_LIST);
        $aAssign['sFeedListLiaInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_LIA_LIST);
        $aAssign['sReportingInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_REPORTING);
        $aAssign['sTopBar'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_TOP);
        $aAssign['sModuleVersion'] = GMerchantCenterPro::$oModule->version;

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_BODY,
            'assign' => $aAssign,
        );
    }

    /**
     * displays prerequisites
     *
     * @param array $aPost
     * @return array
     */
    private function displayPrerequisites(array $aPost = null)
    {
        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ADMIN_PATH,
            'assign' => array(),
        );
    }

    /**
     * displays basic settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayBasics(array $aPost = null)
    {
        $aAssign = array(
            'sDocUri' => _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/',
            'sDocName' => 'readme_' . ((GMerchantCenterPro::$sCurrentLang == 'fr') ? 'fr' : 'en') . '.pdf',
            'sLink' => (!empty(GMerchantCenterPro::$conf['GMCP_LINK']) ? GMerchantCenterPro::$conf['GMCP_LINK'] : GMerchantCenterPro::$sHost),
            'sPrefixId' => GMerchantCenterPro::$conf['GMCP_ID_PREFIX'],
            'iProductPerCycle' => GMerchantCenterPro::$conf['GMCP_AJAX_CYCLE'],
            'sImgSize' => GMerchantCenterPro::$conf['GMCP_IMG_SIZE'],
            'bAddImages' => GMerchantCenterPro::$conf['GMCP_ADD_IMAGES'],
            'aHomeCatLanguages' => GMerchantCenterPro::$conf['GMCP_HOME_CAT'],
            'iHomeCatId' => GMerchantCenterPro::$conf['GMCP_HOME_CAT_ID'],
            'bAddCurrency' => GMerchantCenterPro::$conf['GMCP_ADD_CURRENCY'],
            'iAdvancedProductName' => GMerchantCenterPro::$conf['GMCP_ADV_PRODUCT_NAME'],
            'iAdvancedProductTitle' => GMerchantCenterPro::$conf['GMCP_ADV_PROD_TITLE'],
            'sFeedToken' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'],
            'aImageTypes' => ImageType::getImagesTypes('products'),
            'sCondition' => GMerchantCenterPro::$conf['GMCP_COND'],
            'aAvailableCondition' => BT_GmcProModuleTools::getConditionType(),
            'sProductTitle' => GMerchantCenterPro::$conf['GMCP_P_TITLE'],
            'bSimpleId' => GMerchantCenterPro::$conf['GMCP_SIMPLE_PROD_ID'],
            'bIdentifierExist' => GMerchantCenterPro::$conf['GMCP_FORCE_IDENTIFIER'],
        );

        $aCategories = Category::getCategories(intval(GMerchantCenterPro::$iCurrentLang), false);
        $aAssign['aHomeCat'] = BT_GmcProModuleTools::recursiveCategoryTree($aCategories, array(), current(current($aCategories)), 1);

        // get all active languages in order to loop on field form which need to manage translation
        $aAssign['aLangs'] = Language::getLanguages();

        // use case - detect if home category name has been filled
        $aAssign['aHomeCatLanguages'] = $this->getDefaultTranslations('GMCP_HOME_CAT', 'HOME_CAT_NAME');
        $aAssign['aProdNamePrefix'] = !empty(GMerchantCenterPro::$conf['GMCP_ADV_PROD_NAME_PREFIX']) ? $this->getDefaultTranslations('GMCP_ADV_PROD_NAME_PREFIX', '') : array();
        $aAssign['aProdNameSuffix'] = !empty(GMerchantCenterPro::$conf['GMCP_ADV_PROD_NAME_SUFFIX']) ? $this->getDefaultTranslations('GMCP_ADV_PROD_NAME_SUFFIX', '') : array();

        foreach ($aAssign['aLangs'] as $aLang) {
            if (!isset($aAssign['aHomeCatLanguages'][$aLang['id_lang']])) {
                $aAssign['aHomeCatLanguages'][$aLang['id_lang']] = $GLOBALS['GMCP_HOME_CAT_NAME']['en'];
            }
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_BASICS,
            'assign' => $aAssign,
        );
    }

    /**
     * returns the matching requested translations
     *
     * @param string $sSerializedVar
     * @param string $sGlobalVar
     * @return array
     */
    private function getDefaultTranslations($sSerializedVar, $sGlobalVar)
    {
        $aTranslations = array();

        if (!empty(GMerchantCenterPro::$conf[strtoupper($sSerializedVar)])) {
            $aTranslations = unserialize(GMerchantCenterPro::$conf[strtoupper($sSerializedVar)]);
        } else {
            foreach ($GLOBALS['GMCP_' . strtoupper($sGlobalVar)] as $sIsoCode => $sTranslation) {
                $iLangId = BT_GmcProModuleTools::getLangId($sIsoCode);

                if ($iLangId) {
                    // get Id by iso
                    $aTranslations[$iLangId] = $sTranslation;
                }
            }
        }

        return $aTranslations;
    }

    /**
     * displays feeds settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayFeed(array $aPost = null)
    {
        if (GMerchantCenterPro::$sQueryMode == 'xhr') {
            // clean headers
            @ob_end_clean();
        }

        $aAssign = array(
            'bExportMode' => GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'],
            'bExportOOS' => GMerchantCenterPro::$conf['GMCP_EXPORT_OOS'],
            'bExcludeNoEan' => GMerchantCenterPro::$conf['GMCP_EXC_NO_EAN'],
            'bExcludeNoMref' => GMerchantCenterPro::$conf['GMCP_EXC_NO_MREF'],
            'iMinPrice' => GMerchantCenterPro::$conf['GMCP_MIN_PRICE'],
            'iMaxWeight' => GMerchantCenterPro::$conf['GMCP_MAX_WEIGHT'],
            'bProductOosOrder' => GMerchantCenterPro::$conf['GMCP_EXPORT_PROD_OOS_ORDER'],
            'bProductCombos' => GMerchantCenterPro::$conf['GMCP_P_COMBOS'],
            'iDescType' => GMerchantCenterPro::$conf['GMCP_P_DESCR_TYPE'],
            'aDescriptionType' => BT_GmcProModuleTools::getDescriptionType(),
            'iIncludeStock' => GMerchantCenterPro::$conf['GMCP_INC_STOCK'],
            'bIncludeTagAdult' => GMerchantCenterPro::$conf['GMCP_INC_TAG_ADULT'],
            'bIncludeTagCost' => GMerchantCenterPro::$conf['GMCP_INC_COST'],
            'bIncludeSize' => GMerchantCenterPro::$conf['GMCP_INC_SIZE'],
            'aAttributeGroups' => AttributeGroup::getAttributesGroups((int) GMerchantCenterPro::$oContext->cookie->id_lang),
            'aFeatures' => Feature::getFeatures((int) GMerchantCenterPro::$oContext->cookie->id_lang),
            'aSizeOptions' => GMerchantCenterPro::$conf['GMCP_SIZE_OPT'],
            'sIncludeColor' => GMerchantCenterPro::$conf['GMCP_INC_COLOR'],
            'bIncludeMaterial' => GMerchantCenterPro::$conf['GMCP_INC_MATER'],
            'bIncludePattern' => GMerchantCenterPro::$conf['GMCP_INC_PATT'],
            'bIncludeGender' => GMerchantCenterPro::$conf['GMCP_INC_GEND'],
            'bIncludeAge' => GMerchantCenterPro::$conf['GMCP_INC_AGE'],
            'bIncludeEnergy' => GMerchantCenterPro::$conf['GMCP_INC_ENERGY'],
            'bExcludedDest' => GMerchantCenterPro::$conf['GMCP_EXCLUDED_DEST'],
            'bIncludeShippingLabel' => GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'],
            'bIncludeUnitpricingMeasure' => GMerchantCenterPro::$conf['GMCP_INC_UNIT_PRICING'],
            'bIncludeUnitBasepricingMeasure' => GMerchantCenterPro::$conf['GMCP_INC_B_UNIT_PRICING'],
            'bShippingUse' => GMerchantCenterPro::$conf['GMCP_SHIPPING_USE'],
            'bDimensionUse' => GMerchantCenterPro::$conf['GMCP_DIMENSION'],
            'aExcludedProducts' => GMerchantCenterPro::$conf['GMCP_PROD_EXCL'],
            'sGtinPreference' => GMerchantCenterPro::$conf['GMCP_GTIN_PREF'],
            'aShippingCarriers' => array(),
            'bSizeSystem' => GMerchantCenterPro::$conf['GMCP_SIZE_SYSTEM'],
            'bSizeType' => GMerchantCenterPro::$conf['GMCP_SIZE_TYPE'],
            'aFreeShippingProducts' => GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD'],
            'sIncludeSize' => GMerchantCenterPro::$conf['GMCP_INC_SIZE'],
            'bRewriteNumAttrValues' => GMerchantCenterPro::$conf['GMCP_URL_NUM_ATTR_REWRITE'],
            'bUrlInclAttrId' => GMerchantCenterPro::$conf['GMCP_URL_ATTR_ID_INCL'],
            'sComboSeparator' => GMerchantCenterPro::$conf['GMCP_COMBO_SEPARATOR'],
            'bPS16013' => GMerchantCenterPro::$bCompare16013,
            'bExcludedCountry' => GMerchantCenterPro::$conf['GMCP_EXCLUDED_COUNTRY'],
        );

        // handle product IDs and Names list to format them for the autocomplete feature
        if (!empty($aAssign['aExcludedProducts'])) {
            $sProdIds = '';
            $sProdNames = '';

            foreach ($aAssign['aExcludedProducts'] as $iKey => $sProdId) {
                $aProdIds = explode('¤', $sProdId);
                $oProduct = new Product($aProdIds[0], false, GMerchantCenterPro::$iCurrentLang);

                // check if we export with combinations
                if (!empty($aProdIds[0])) {
                    $oProduct->name .= BT_GmcProModuleTools::getProductCombinationName($aProdIds[1], GMerchantCenterPro::$iCurrentLang, GMerchantCenterPro::$iShopId);

                    $sProdIds .= $sProdId . '-';
                    $sProdNames .= $oProduct->name . '||';

                    $aAssign['aProducts'][] = array(
                        'id' => $sProdId,
                        'name' => $oProduct->name,
                        'attrId' => $aProdIds[1],
                        'stringIds' => $sProdId
                    );
                }
            }
            $aAssign['sProductIds'] = $sProdIds;
            $aAssign['sProductNames'] = $sProdNames;
        }

        // handle product IDs and Names list for export product free shipping
        if (!empty($aAssign['aFreeShippingProducts'])) {
            $sProdIds = '';
            $sProdNames = '';

            foreach ($aAssign['aFreeShippingProducts'] as $iKey => $sProdId) {
                $aProdIds = explode('¤', $sProdId);
                $oProduct = new Product($aProdIds[0], false, GMerchantCenterPro::$iCurrentLang);

                // check if we export with combinations
                if (!empty($aProdIds[1])) {
                    $oProduct->name .= BT_GmcProModuleTools::getProductCombinationName($aProdIds[1], GMerchantCenterPro::$iCurrentLang, GMerchantCenterPro::$iShopId);
                }

                $sProdIds .= $sProdId . '-';
                $sProdNames .= $oProduct->name . '||';

                $aAssign['aProductsFreeShipping'][] = array(
                    'id' => $sProdId,
                    'name' => $oProduct->name,
                    'attrId' => $aProdIds[1],
                    'stringIds' => $sProdId
                );
            }
            $aAssign['sProductFreeShippingIds'] = $sProdIds;
            $aAssign['sProductFreeShippingNames'] = str_replace('"', '', $sProdNames);
        }

        if (isset(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['attribute'])) {
            $aAssign['aColorOptions']['attribute'] = !empty(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['attribute']) ? GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['attribute'] : array(0);
        }
        if (isset(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['feature'])) {
            $aAssign['aColorOptions']['feature'] = !empty(GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['feature']) ? GMerchantCenterPro::$conf['GMCP_COLOR_OPT']['feature'] : array(0);
        }

        if (isset(GMerchantCenterPro::$conf['aSizeOptions']['attribute'])) {
            $aAssign['aSizeOptions']['attribute'] = !empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['attribute']) ? GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['attribute'] : array(0);
        }
        if (isset(GMerchantCenterPro::$conf['aSizeOptions']['feature'])) {
            $aAssign['aSizeOptions']['feature'] = !empty(GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['feature']) ? GMerchantCenterPro::$conf['GMCP_SIZE_OPT']['feature'] : array(0);
        }

        // get available categories and manufacturers
        $aCategories = Category::getCategories(intval(GMerchantCenterPro::$iCurrentLang), false);
        $aBrands = Manufacturer::getManufacturers();

        $aStartCategories = current($aCategories);
        $aFirst = current($aStartCategories);
        $iStart =  (int) Category::getRootCategory()->id;

        // get registered categories and brands
        $aIndexedCategories = array();
        $aIndexedBrands = array();

        // use case - get categories or brands according to the export mode
        if (GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'] == 1) {
            $aIndexedBrands = BT_GmcProModuleDao::getGmcBrands(GMerchantCenterPro::$iShopId);
        } else {
            $aIndexedCategories = BT_GmcProModuleDao::getGmcCategories(GMerchantCenterPro::$iShopId);
        }

        // format categories and brands
        $aAssign['aFormatCat'] = BT_GmcProModuleTools::recursiveCategoryTree($aCategories, $aIndexedCategories, $aFirst, $iStart, null, true);
        $aAssign['aFormatBrands'] = BT_GmcProModuleTools::recursiveBrandTree($aBrands, $aIndexedBrands, $aFirst, $iStart);

        $aAssign['iShopCatCount'] = count($aAssign['aFormatCat']);
        $aAssign['iMaxPostVars'] = ini_get('max_input_vars');

        if (!empty(GMerchantCenterPro::$aAvailableLangCurrencyCountry)) {
            foreach (GMerchantCenterPro::$aAvailableLangCurrencyCountry as $aData) {
                // handle price with tax or not
                $aAssign['aFeedTax'][] = array(
                    'tax' => BT_GmcProModuleTools::isTax($aData['langIso'], $aData['countryIso']),
                    'country' => $aData['countryIso'],
                    'lang' => $aData['langIso'],
                    'langId' => $aData['langId'],
                );
            }
        }

        foreach ($GLOBALS['GMCP_AVAILABLE_COUNTRIES'] as $sLang => $aCountries) {
            if (BT_GmcProModuleDao::checkActiveLanguage($sLang)) {
                foreach ($aCountries as $sCountry => $aLocaleData) {
                    $iCountryId = Country::getByIso($sCountry);
                    if (!empty($iCountryId)) {
                        $iCountryZone = Country::getIdZone($iCountryId);
                        if (!empty($iCountryZone)) {
                            $aCarriers = BT_GmcProModuleDao::getAvailableCarriers((int) $iCountryZone);
                            foreach ($aLocaleData['currency'] as $sCurrency) {
                                if (Currency::getIdByIsoCode($sCurrency)) {
                                    if (!empty($aCarriers) && Currency::getIdByIsoCode($sCurrency)) {
                                        if (!array_key_exists($sCountry, $aAssign['aShippingCarriers'])) {
                                            $aAssign['aShippingCarriers'][$sCountry] = array(
                                                'name' => $sCountry,
                                                'carriers' => $aCarriers,
                                                'shippingCarrierId' => (!empty(GMerchantCenterPro::$conf['GMCP_SHIP_CARRIERS'][$sCountry]) ? GMerchantCenterPro::$conf['GMCP_SHIP_CARRIERS'][$sCountry] : 0),
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // use case for the exclusion rules value
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        $aExclusionRules = BT_GmcProExclusionDao::getExclusionRules();
        $aAssign['aExclusionRules'] = BT_GmcProModuleTools::getExclusionRulesName($aExclusionRules);

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_SETTINGS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays advancedFeed settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayAdvancedFeed(array $aPost = null)
    {

        if (GMerchantCenterPro::$sQueryMode == 'xhr') {
            // clean headers
            @ob_end_clean();
        }
        $aCartRulesChannel = array();

        // to get information for preview display
        require_once(_GMCP_PATH_LIB_DAO . 'cart-rules-dao_class.php');


        // get discount information for preview
        $aDisplayDiscount = BT_GmcProCartRulesDao::getCartRules(
            (string) GMerchantCenterPro::$conf['GMCP_DSC_NAME'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_DATE_FROM'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_DATE_TO'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_MIN_AMOUNT'],
            GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MIN'],
            GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MAX'],
            GMerchantCenterPro::$conf['GMCP_DSC_TYPE'],
            GMerchantCenterPro::$conf['GMCP_DSC_CUMULABLE']
        );

        // Handle the channel value
        if (is_array($aDisplayDiscount) && !empty($aDisplayDiscount)) {
            foreach ($aDisplayDiscount as $aData) {
                if (!empty($aData['id_cart_rule'])) {

                    $sChannel = BT_GmcProCartRulesDao::getGoogleChannel($aData['id_cart_rule']);
                    $aCartRulesChannel[$aData['id_cart_rule']] = $sChannel;
                }
            }
        }

        $aAssign = array(
            'bFilterName' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_NAME'],
            'bFilterDate' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_DATE'],
            'bFilterMinAmount' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_MIN_AMOUNT'],
            'bFilterValue' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_VALUE'],
            'bFilterType' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_TYPE'],
            'bFilterCumulable' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_CUMU'],
            'bFilterFor' => GMerchantCenterPro::$conf['GMCP_DSC_FILT_FOR'],
            'sDiscountName' => GMerchantCenterPro::$conf['GMCP_DSC_NAME'],
            'sDiscountDateFrom' => GMerchantCenterPro::$conf['GMCP_DSC_DATE_FROM'],
            'sDiscountDateTo' => GMerchantCenterPro::$conf['GMCP_DSC_DATE_TO'],
            'sDiscountMinAmount' => GMerchantCenterPro::$conf['GMCP_DSC_MIN_AMOUNT'],
            'sDiscountValueMin' => GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MIN'],
            'sDiscountValueMax' => GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MAX'],
            'bDiscountType' => GMerchantCenterPro::$conf['GMCP_DSC_TYPE'],
            'sDiscountCumulable' => GMerchantCenterPro::$conf['GMCP_DSC_CUMULABLE'],
            'aDiscountAvailable' => $aDisplayDiscount,
            'aCartRulesChannel' => $aCartRulesChannel,
            'aDiscountChannel' => $GLOBALS['GMCP_DISCOUNT_CHANNEL'],
            'bInvPrice' => GMerchantCenterPro::$conf['GMCP_INV_PRICE'],
            'bInvStock' => GMerchantCenterPro::$conf['GMCP_INV_STOCK'],
            'bSalePrice' => GMerchantCenterPro::$conf['GMCP_INV_SALE_PRICE'],
            'bGsnippetsReviews' => BT_GmcProModuleTools::isInstalled('gsnippetsreviews'),
            'bProductComment' => BT_GmcProModuleTools::isInstalled('productcomments'),
            'aPromotionDestination' =>  unserialize(GMerchantCenterPro::$conf['GMCP_PROMO_DEST']),
        );

        $aDataForbidden = unserialize(GMerchantCenterPro::$conf['GMCP_FORBIDDEN_WORDS']);
        $aAssign['sForbiddenWords'] = '';
        if (!empty($aDataForbidden)) {
            if (is_array($aDataForbidden)) {
                foreach ($aDataForbidden as $sDataForbidden) {
                    $aAssign['sForbiddenWords'] .= $sDataForbidden;

                    if ($sDataForbidden != end($aDataForbidden)) {
                        $aAssign['sForbiddenWords'] .= ',';
                    }
                }
            }
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ADVANCED_SETTINGS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays Google settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayGoogle(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

        if (GMerchantCenterPro::$sQueryMode == 'xhr') {
            // clean headers
            @ob_end_clean();
        }

        $aAssign = array(
            'aCountryTaxonomies' => BT_GmcProModuleDao::getAvailableTaxonomyCountries($GLOBALS['GMCP_AVAILABLE_COUNTRIES']),
            'sGoogleCatListInclude' => BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CATEGORY_LIST),
            'aTags' => BT_GmcProCustomLabelDao::getGmcTags(GMerchantCenterPro::$iShopId),
            'sUtmCampaign' => GMerchantCenterPro::$conf['GMCP_UTM_CAMPAIGN'],
            'sUtmSource' => GMerchantCenterPro::$conf['GMCP_UTM_SOURCE'],
            'sUtmMedium' => GMerchantCenterPro::$conf['GMCP_UTM_MEDIUM'],
            'bUtmContent' => GMerchantCenterPro::$conf['GMCP_UTM_CONTENT'],
        );

        foreach ($aAssign['aCountryTaxonomies'] as $sIsoCode => &$aTaxonomy) {
            $aTaxonomy['countryList'] = implode(', ', $aTaxonomy['countries']);
            $aTaxonomy['updated'] = BT_GmcProModuleDao::checkTaxonomyUpdate($sIsoCode);
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_SETTINGS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays feed list
     *
     * @param array $aPost
     * @return array
     */
    private function displayFeedList(array $aPost = null)
    {
        if (GMerchantCenterPro::$sQueryMode == 'xhr') {
            // clean headers
            @ob_end_clean();
        }

        require_once(_GMCP_PATH_LIB_DAO . 'cart-rules-dao_class.php');
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        $aAssign = array(
            'iShopId' => GMerchantCenterPro::$iShopId,
            'sGmcLink' => GMerchantCenterPro::$conf['GMCP_LINK'],
            'bReporting' => GMerchantCenterPro::$conf['GMCP_REPORTING'],
            'iTotalProductToExport' => BT_GmcProModuleDao::getProductIds(GMerchantCenterPro::$iShopId, (int) GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'], true),
            'iTotalDiscountToExport' => BT_GmcProCartRulesDao::getCartRulesId(),
            'iTotalProduct' => BT_GmcProModuleDao::countProducts(GMerchantCenterPro::$iShopId, (int) GMerchantCenterPro::$conf['GMCP_P_COMBOS']),
            'aFeedFileList' => array(),
            'aFeedFileListReviews' => array(),
            'aFlyFileList' => array(),
            'bExcludedProduct' => BT_GmcProExclusionDao::isExcludedProduct(),
        );
        $aAssign['aCronLangProduct'] = (!empty(GMerchantCenterPro::$conf['GMCP_CHECK_EXPORT']) ? GMerchantCenterPro::$conf['GMCP_CHECK_EXPORT'] : array());
        $aAssign['aCronLangReviews'] = (!empty(GMerchantCenterPro::$conf['GMCP_CHECK_EXPORT_REVIEWS']) ? GMerchantCenterPro::$conf['GMCP_CHECK_EXPORT_REVIEWS'] : array());

        // handle data feed file name
        if (!empty($aAssign['sGmcLink'])) {
            $sFileSuffix = '';
            //handle type of dat feed file name and data feed for on-the-fly-output
            foreach ($GLOBALS['GMCP_DATA_FEED_TYPE'] as $sType) {
                $aAssign['aFeedFileList' . ucfirst($sType)] = array();
                $aAssign['aCronList' . ucfirst($sType)] = array();
                $aAssign['aFlyFileList' . ucfirst($sType)] = array();

                // handle manual xml file and on-the-fly output
                if (!empty(GMerchantCenterPro::$aAvailableLangCurrencyCountry)) {

                    // Use case - Cron per country
                    foreach (GMerchantCenterPro::$aAvailableLangCurrencyCountry as $sKey => $aData) {

                        // SET THE XML FILE SUFFIX
                        $sFileSuffix = BT_GmcProModuleTools::buildFileSuffix($aData['langIso'], $aData['countryIso'], $aData['currencyIso'], 0, $sType);

                        $sFileName = GMerchantCenterPro::$sFilePrefix . '.' . $sFileSuffix . '.xml';

                        // use case - for all data feed except the discount
                        if ($sType != 'discount' && $sType != 'reviews') {
                            if (is_file(_GMCP_SHOP_PATH_ROOT . $sFileName)) {
                                // Array of XML file list
                                $aAssign['aFeedFileList' . ucfirst($sType)][] = array(
                                    'link' => $aAssign['sGmcLink'] . __PS_BASE_URI__ . $sFileName,
                                    'filename' => $sFileName,
                                    'filemtime' => date("d-m-Y H:i:s", filemtime(_GMCP_SHOP_PATH_ROOT . $sFileName)),
                                    'checked' => (in_array($aData['langIso'] . '_' . $aData['countryIso'] . '_' . $aData['currencyIso'], $aAssign['aCronLang' . ucfirst($sType)]) ? true : false),
                                    'country' => $aData['countryIso'],
                                    'countryName' => $aData['countryName'],
                                    'lang' => $aData['langIso'],
                                    'langName' => $aData['langName'],
                                    'currencyIso' => $aData['currencyIso'],
                                    'currencySign' => $aData['currencySign'],
                                    'langId' => $aData['langId'],
                                );

                                $aAssign['aCronList' . ucfirst($sType)][] = array(
                                    'currencyIsoCron' => $aData['currencyIso'],
                                    'country' => $aData['countryIso'],
                                    'lang' => $aData['langIso'],
                                    'link' => Context::getContext()->link->getModuleLink(_GMCP_MODULE_SET_NAME, _GMCP_CTRL_CRON, array('id_shop' =>  GMerchantCenterPro::$iShopId, 'gmcp_lang_id' => $aData['langId'], 'country' => $aData['countryIso'], 'currency_iso' => $aData['currencyIso'], 'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'], 'sType' => 'cron', 'feed_type' => $sType)),
                                    'currencySign' => $aData['currencySign'],
                                    'countryName' => $aData['countryName'],
                                    'langName' => $aData['langName'],
                                );
                            }
                        }
                    }

                    // FLY OUTPUT
                    foreach (GMerchantCenterPro::$aAvailableLangCurrencyCountry as $sKey => $aData) {
                        $aAssign['aFlyFileList' . ucfirst($sType)][] = array(
                            'currencyIso' => $aData['currencyIso'],
                            'iso_code' => $aData['langIso'],
                            'countryIso' => $aData['countryIso'],
                            'link' => Context::getContext()->link->getModuleLink(_GMCP_MODULE_SET_NAME, _GMCP_CTRL_FLY, array('id_shop' =>  GMerchantCenterPro::$iShopId, 'gmcp_lang_id' => $aData['langId'], 'country' => $aData['countryIso'], 'currency_iso' => $aData['currencyIso'], 'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'], 'sType' => 'flyOutput', 'feed_type' => $sType)),
                            'currencySign' => $aData['currencySign'],
                            'countryName' => $aData['countryName'],
                            'langName' => $aData['langName'],
                        );
                    }
                }

                // handle the cron URL for each data feed type
                $aAssign['sCronUrl' . ucfirst($sType)] = Context::getContext()->link->getModuleLink(_GMCP_MODULE_SET_NAME, _GMCP_CTRL_CRON, array('id_shop' => GMerchantCenterPro::$iShopId, 'feed_type' => $sType, 'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN']));
            }
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_LIST,
            'assign' => $aAssign,
        );
    }

    /**
     * displays reporting settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayReporting(array $aPost = null)
    {
        $aAssign = array(
            'aLangCurrencies' => BT_GmcProModuleTools::getGeneratedReport(),
            'bReporting' => GMerchantCenterPro::$conf['GMCP_REPORTING'],
        );

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_REPORTING,
            'assign' => $aAssign,
        );
    }

    /**
     * displays Fancybox Google categories
     *
     * @param array $aPost
     * @return array
     */
    private function displayGoogleCategories(array $aPost = null)
    {

        // clean headers
        @ob_end_clean();

        $aAssign = array(
            'iLangId' => Tools::getValue('iLangId'),
            'sLangIso' => Tools::getValue('sLangIso'),
            'sCurrentIso' => Language::getIsoById(GMerchantCenterPro::$iCurrentLang),
        );

        // get shop categories
        $aShopCategories = BT_GmcProModuleDao::getShopCategories(GMerchantCenterPro::$iShopId, $aAssign['iLangId'], GMerchantCenterPro::$conf['GMCP_HOME_CAT_ID']);

        foreach ($aShopCategories as &$aCategory) {
            // get google taxonomy
            $aGoogleCat = BT_GmcProModuleDao::getGoogleCategories(GMerchantCenterPro::$iShopId, $aCategory['id_category'], $aAssign['sLangIso']);
            // assign the current taxonomy
            $aCategory['google_category_name'] = is_array($aGoogleCat) && isset($aGoogleCat['txt_taxonomy']) ? $aGoogleCat['txt_taxonomy'] : '';
        }

        $aAssign['aShopCategories'] = $aShopCategories;
        $aAssign['iShopCatCount'] = count($aShopCategories);
        $aAssign['iMaxPostVars'] = ini_get('max_input_vars');

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CATEGORY_POPUP,
            'assign' => $aAssign,
        );
    }

    /**
     * displays autocomplete google categories
     *
     * @param array $aPost
     * @return array
     */
    private function displayAutocomplete(array $aPost = null)
    {
        // clean headers
        @ob_end_clean();

        // set
        $sOutput = '';

        $sLangIso = Tools::getValue('sLangIso');
        $sQuery = Tools::getValue('q');

        // explode query string
        $aWords = explode(' ', $sQuery);

        // get matching query
        $aItems = BT_GmcProModuleDao::autocompleteSearch($sLangIso, $aWords);

        if (
            !empty($aItems)
            && is_array($aItems)
        ) {
            foreach ($aItems as $aItem) {
                $sOutput .= trim($aItem['value']) . "\n";
            }
        }
        echo $sOutput;
        exit(0);
    }

    /**
     * displays custom labels
     *
     * @param array $aPost
     * @return array
     */
    private function displayCustomLabel(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

        // clean headers
        @ob_end_clean();

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        $aAssign = array(
            'aCustomLabelType' => $GLOBALS['GMCP_CUSTOM_LABEL_TYPE'],
            'aCustomBestType' => $GLOBALS['GMCP_CUSTOM_LABEL_BEST_TYPE'],
            'aCustomBestPeriodType' => $GLOBALS['GMCP_CUSTOM_LABEL_BEST_PERIOD_TYPE'],
            'aFeatureAvailable' => BT_GmcProModuleDao::getFeature(),
            'sCurrency' => Currency::getDefaultCurrency()->sign,
            'sUriAutoComplete' => 'ajax_products_list.php',
        );

        // get available categories and manufacturers
        $aCategories = Category::getCategories(intval(GMerchantCenterPro::$iCurrentLang), false);
        $aBrands = Manufacturer::getManufacturers();
        $aSuppliers = Supplier::getSuppliers();

        $aStartCategories = current($aCategories);
        $aFirst = current($aStartCategories);
        $iStart =  (int) Category::getRootCategory()->id;

        // get registered categories and brands and suppliers
        $aIndexedCategories = array();
        $aIndexedBrands = array();
        $aIndexedSuppliers = array();

        // use case - get categories or brands or suppliers according to the id tag
        $iTagId = Tools::getValue('iTagId');
        $aTag = array();

        if (!empty($iTagId)) {
            $aTag = BT_GmcProCustomLabelDao::getGmcTags(GMerchantCenterPro::$iShopId, $iTagId);

            //manage categories association for each type tag using categories
            $aClManualIndexedCategories = BT_GmcProCustomLabelDao::getGmcTags(null, $iTagId, 'cats', 'category');
            $aClDynamicIndexedCategories = BT_GmcProCustomLabelDao::getDynamicCat($iTagId);

            //merge result for return good check box for each categories
            $aIndexedCategories = array_merge($aClManualIndexedCategories, $aClDynamicIndexedCategories);

            $aIndexedBrands = BT_GmcProCustomLabelDao::getGmcTags(null, $iTagId, 'brands', 'brand');
            $aIndexedSuppliers = BT_GmcProCustomLabelDao::getGmcTags(null, $iTagId, 'suppliers', 'supplier');
            $aIndexedProducts = BT_GmcProCustomLabelDao::getGmcTagsProduct($iTagId);

            // handle product IDs and Names list to format them for the autocomplete feature
            if (!empty($aIndexedProducts)) {
                $sProdIds = '';
                $sProdNames = '';
                foreach ($aIndexedProducts as $iKey => $iProdId) {
                    if (!empty($iProdId)) {
                        $sProdIds .= $iProdId['id_product'] . '-';
                        $sProdNames .= $iProdId['product_name'] . '||';
                        $aAssign['aProducts'][] = array(
                            'id' => $iProdId['id_product'],
                            'name' => $iProdId['product_name']
                        );
                    }
                }
                $aAssign['sProductIds'] = $sProdIds;
                $aAssign['sProductNames'] = $sProdNames;
            }

            $aFeatureSelected = BT_GmcProCustomLabelDao::getFeatureSave($iTagId);
            $sDateNewProduct = BT_GmcProCustomLabelDao::getDynamicNew($iTagId);
            $aBestSales = BT_GmcProCustomLabelDao::getDynamicBestSales($iTagId);
            $aPriceRange = BT_GmcProCustomLabelDao::getDynamicPriceRange($iTagId);

            $aAssign['bActive'] = $aTag[0]['active'];
            $aAssign['sDate'] = $aTag[0]['end_date'];
            $aAssign['iFeatureId'] = $aFeatureSelected['id_feature'];
            $aAssign['aProductIds'] = $aIndexedProducts;
            $aAssign['sDateNewPoduct'] = $sDateNewProduct['from_date'];

            //Use case for best sale
            $aAssign['fAmount'] = $aBestSales['amount'];
            $aAssign['sUnit'] = $aBestSales['unit'];

            if ($aBestSales['start_date'] != "0000-00-00 00:00:00") {
                $aAssign['sStartDate'] = $aBestSales['start_date'];
            }

            if ($aBestSales['end_date'] != "0000-00-00 00:00:00") {
                $aAssign['sEndDate'] = $aBestSales['end_date'];
            }

            //Use case for price range CL
            $aAssign['fPriceMin'] = $aPriceRange['price_min'];
            $aAssign['fPriceMax'] = $aPriceRange['price_max'];
        }

        // format categories and brands and suppliers
        $aAssign['aTag'] = (count($aTag) == 1 && isset($aTag[0])) ? $aTag[0] : $aTag;
        $aAssign['aFormatCat'] = BT_GmcProModuleTools::recursiveCategoryTree($aCategories, $aIndexedCategories, $aFirst, $iStart);
        $aAssign['aFormatBrands'] = BT_GmcProModuleTools::recursiveBrandTree($aBrands, $aIndexedBrands, $aFirst, $iStart);
        $aAssign['aFormatSuppliers'] = BT_GmcProModuleTools::recursiveSupplierTree($aSuppliers, $aIndexedSuppliers, $aFirst, $iStart);
        $aAssign['iShopCatCount'] = count($aAssign['aFormatCat']);
        $aAssign['iMaxPostVars'] = ini_get('max_input_vars');

        //manage autocomplete
        $aProduct = Product::getSimpleProducts(GMerchantCenterPro::$iShopId);

        foreach ($aProduct as $key => $value) {
            // set the string for autocomplete
            $sProduct[$key] = $value;
        }

        $aAssign['sProduct'] = $sProduct;

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CUSTOM_LABEL,
            'assign' => $aAssign,
        );
    }

    /**
     * displays products are associated to the CL
     *
     * @param array $aPost
     * @return array
     */
    private function displayCustomLabelProduct(array $aPost = null)
    {
        // clean headers
        @ob_end_clean();

        $aAssign = array();

        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

        $iTagId = Tools::getValue('iTagId');

        foreach ($GLOBALS['GMCP_CUSTOM_LABEL_PRODUCT_FILTER'] as $aFilter) {
            $aProductIds = BT_GmcProCustomLabelDao::getCustomLabelProductIds($iTagId, $aFilter);

            if (!empty($aProductIds)) {
                foreach ($aProductIds as $aProductId) {
                    if (is_array($aProductId)) {
                        $oProduct = new Product((int) $aProductId['id_product'], true, GMerchantCenterPro::$iCurrentLang);
                        $aAssign['aProduct'][(int) $aProductId['id_product']] = array('id' => $oProduct->id, 'name' => $oProduct->name);
                    }
                }
            }
        }

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CUSTOM_LABEL_PRODUCTS,
            'assign' => $aAssign,
        );
    }

    /**
     * displays reporting fancybox
     *
     * @param array $aPost
     * @return array
     */
    private function displayReportingBox(array $aPost = null)
    {
        // clean headers
        @ob_end_clean();


        $aAssign = array();
        $aTmp = array();

        // get the current lang ID
        $sCurrencyIso = Tools::getValue('sCurrencyIso');

        $sLang = !empty($sCurrencyIso) ? Tools::getValue('lang') . '_' . $sCurrencyIso : Tools::getValue('lang');

        $iProductCount = Tools::getValue('count');
        $sFeedType = Tools::getIsset('feed_type') ? Tools::getValue('feed_type') : 'product';

        if (
            !empty($sLang)
            && strstr($sLang, '_')
        ) {
            list($sLangIso, $sCountryIso, $sCurrencyIso) = explode('_', $sLang);

            // get the identify lang ID
            $iLangId = BT_GmcProModuleTools::getLangId($sLangIso);

            // include
            require_once(_GMCP_PATH_LIB . 'module-reporting_class.php');

            // set reporting object
            BT_GmcProReporting::create(true)->setFileName(_GMCP_REPORTING_DIR . 'reporting-' . $sLangIso . '-' . Tools::strtolower($sCountryIso) . '-' . Tools::strtoupper($sCurrencyIso) . '-' . Tools::strtolower($sFeedType) . '.txt');

            // get the current report
            $aReporting = BT_GmcProReporting::create()->get();

            if (!empty($aReporting)) {
                static $aTmpProduct = array();

                // get the language name
                $aLanguage = Language::getLanguage($iLangId);
                $sLanguageName = $aLanguage['name'];
                // get the country name
                $sCountryName = Country::getNameById($iLangId, Country::getByIso(Tools::strtolower($sCountryIso)));

                // check if exists counter key in the reporting
                if (!empty($aReporting['counter'][0])) {
                    if (empty($iProductCount)) {
                        $iProductCount = $aReporting['counter'][0]['products'];
                    }
                    unset($aReporting['counter']);
                }

                // load google tags
                $aGoogleTags = BT_GmcProModuleTools::loadGoogleTags();

                foreach ($aReporting as $sTagName => &$aGTag) {
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['count'] = count($aGTag);
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['label'] = (isset($aGoogleTags[$sTagName]) ? $aGoogleTags[$sTagName]['label'] : '');
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['msg'] = (isset($aGoogleTags[$sTagName]) ? $aGoogleTags[$sTagName]['msg'] : '');
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['faq_id'] = (isset($aGoogleTags[$sTagName]) ? (int) ($aGoogleTags[$sTagName]['faq_id']) : 0);
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['anchor'] = (isset($aGoogleTags[$sTagName]) ? $aGoogleTags[$sTagName]['anchor'] : '');
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['mandatory'] = (isset($aGoogleTags[$sTagName]) ? $aGoogleTags[$sTagName]['mandatory'] : false);

                    // detect the old format system and the new format
                    if (
                        isset($aGTag[0]['productId'])
                        && strstr($aGTag[0]['productId'], '_')
                    ) {
                        foreach ($aGTag as $iKey => &$aProdValue) {
                            list($iProdId, $iAttributeId) = explode('_', $aProdValue['productId']);
                            if (empty($aTmpProduct[$aProdValue['productId']])) {
                                // get the product obj
                                $oProduct = new Product((int) $iProdId, true, (int) $iLangId);
                                $oCategory = new Category((int) ($oProduct->id_category_default), (int) $iLangId);

                                // set the product URL
                                $aProdValue['productUrl'] = BT_GmcProModuleTools::getProductLink($oProduct, $iLangId, $oCategory->link_rewrite);
                                // set the product name
                                $aProdValue['productName'] = $oProduct->name;

                                // if combination
                                if (!empty($iAttributeId)) {
                                    // get the combination URL
                                    $aProdValue['productUrl'] = Context::getContext()->link->getProductLink($oProduct, $iLangId, Tools::strtolower($oCategory->link_rewrite));

                                    // get the combination attributes to format the product name
                                    $aCombinationAttr = BT_GmcProModuleDao::getProductComboAttributes($iAttributeId, $iLangId, GMerchantCenterPro::$iShopId);

                                    if (!empty($aCombinationAttr)) {
                                        $sExtraName = '';
                                        foreach ($aCombinationAttr as $c) {
                                            $sExtraName .= ' ' . Tools::stripslashes($c['name']);
                                        }
                                        $aProdValue['productName'] .= $sExtraName;
                                    }
                                }

                                $aTmpProduct[$aProdValue['productId']] = array(
                                    'productId' => $iProdId,
                                    'productAttrId' => $iAttributeId,
                                    'productUrl' => $aProdValue['productUrl'],
                                    'productName' => $aProdValue['productName'],
                                );
                            }
                            $aProdValue = $aTmpProduct[$aProdValue['productId']];
                        }
                    }
                    $aTmp[$aGoogleTags[$sTagName]['type']][$sTagName]['data'] = $aGTag;
                }
                $aTmpProduct = array();
                ksort($aTmp);

                $aAssign = array(
                    'sLangName' => $sLanguageName,
                    'sCountryName' => $sCountryName,
                    'aReport' => $aTmp,
                    'iProductCount' => (int) $iProductCount,
                    'sPath' => _GMCP_PATH_ROOT,
                    'sFaqURL' => _GMCP_BT_FAQ_MAIN_URL,
                    'sFaqLang' => $sLangIso,
                    'sToken' => Tools::getAdminTokenLite('AdminProducts'),
                    'sProductLinkController' => $_SERVER['SCRIPT_URI'] . '?controller=AdminProducts',
                    'sProductAction' => '&updateproduct',
                );
            } else {
                $aAssign['aErrors'][] = array(
                    'msg' => GMerchantCenterPro::$oModule->l('There isn\'t any report for this language and country', 'admin-display_class.php') . ' : ' . $sLangIso . ' - ' . $sCountryIso,
                    'code' => 190
                );
            }
        } else {
            $aAssign['aErrors'][] = array(
                'msg' => GMerchantCenterPro::$oModule->l('Language ISO and country ISO aren\'t well formatted', 'admin-display_class.php'),
                'code' => 191
            );
        }

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_REPORTING_BOX,
            'assign' => $aAssign,
        );
    }

    /**
     * displays advanced tag category settings
     *
     * @param array $aPost
     * @return array
     */
    private function displayAdvancedTagCategory(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB . 'module-tools_class.php');
        // clean headers
        @ob_end_clean();

        $aCountriesToHandle = array();
        $aAvailableLanguage = array();
        $aShopCategories = BT_GmcProModuleDao::getShopCategories(GMerchantCenterPro::$iShopId, GMerchantCenterPro::$iCurrentLang, GMerchantCenterPro::$conf['GMCP_HOME_CAT_ID']);

        // Handle the country to handle in according to Google available language
        $aAvailableLanguage = BT_GmcProModuleTools::getAvailableLanguages(GMerchantCenterPro::$iShopId);
        $aCountries = BT_GmcProModuleTools::getLangCurrencyCountry($aAvailableLanguage, $GLOBALS[_GMCP_MODULE_NAME . '_AVAILABLE_COUNTRIES']);

        foreach ($aCountries as $key => $aCountry) {

            $aCountriesToHandle[] =  $aCountry['countryIso'];
        }


        foreach ($aShopCategories as &$aCat) {
            // get feature by category Id
            $aFeatures = BT_GmcProModuleDao::getFeaturesByCategory($aCat['id_category'], GMerchantCenterPro::$iShopId);

            if (!empty($aFeatures)) {
                $aCat['material'] = $aFeatures['material'];
                $aCat['pattern'] = $aFeatures['pattern'];
                $aCat['agegroup'] = $aFeatures['agegroup'];
                $aCat['gender'] = $aFeatures['gender'];
                $aCat['adult'] = $aFeatures['adult'];
                $aCat['sizeType'] = $aFeatures['sizeType'];
                $aCat['sizeSystem'] = $aFeatures['sizeSystem'];
                $aCat['energy'] = $aFeatures['energy'];
                $aCat['energy_min'] = $aFeatures['energy_min'];
                $aCat['energy_max'] = $aFeatures['energy_max'];
                $aCat['shipping_label'] = $aFeatures['shipping_label'];
                $aCat['unit_pricing_measure'] = $aFeatures['unit_pricing_measure'];
                $aCat['base_unit_pricing_measure'] = $aFeatures['base_unit_pricing_measure'];
                $aCat['excluded_destination'] = !empty($aFeatures['excluded_destination']) ? explode(' ', $aFeatures['excluded_destination']) : '';
                $aCat['excluded_country'] = !empty($aFeatures['excluded_country']) ? explode(' ', $aFeatures['excluded_country']) : '';
            } else {
                $aCat['material'] = '';
                $aCat['pattern'] = '';
                $aCat['agegroup'] = '';
                $aCat['gender'] = '';
                $aCat['adult'] = '';
                $aCat['adult'] = '';
                $aCat['sizeSystem'] = '';
                $aCat['energy'] = '';
                $aCat['energy_min'] = '';
                $aCat['energy_max'] = '';
                $aCat['shipping_label'] = '';
                $aCat['unit_pricing_measure'] = '';
                $aCat['base_unit_pricing_measure'] = '';
                $aCat['exluded_destination'] = '';
            }
        }

        $aAssign = array(
            'aShopCategories' => $aShopCategories,
            'aCountries' => array_unique($aCountriesToHandle),
            'aFeatures' => Feature::getFeatures(GMerchantCenterPro::$iCurrentLang),
            'sUseTag' => Tools::getValue('sUseTag'),
            'bMaterial' => GMerchantCenterPro::$conf['GMCP_INC_MATER'],
            'bPattern' => GMerchantCenterPro::$conf['GMCP_INC_PATT'],
            'bGender' => GMerchantCenterPro::$conf['GMCP_INC_GEND'],
            'bAgeGroup' => GMerchantCenterPro::$conf['GMCP_INC_AGE'],
            'bTagAdult' => GMerchantCenterPro::$conf['GMCP_INC_TAG_ADULT'],
            'bSizeType' => GMerchantCenterPro::$conf['GMCP_SIZE_TYPE'],
            'bSizeSystem' => GMerchantCenterPro::$conf['GMCP_SIZE_SYSTEM'],
            'bEnergy' => GMerchantCenterPro::$conf['GMCP_INC_ENERGY'],
            'bShippingLabel' => GMerchantCenterPro::$conf['GMCP_INC_SHIPPING_LABEL'],
            'bUnitpricingMeasure' => GMerchantCenterPro::$conf['GMCP_INC_UNIT_PRICING'],
            'bUnitBasepricingMeasure' => GMerchantCenterPro::$conf['GMCP_INC_B_UNIT_PRICING'],
            'bExcludedDest' => GMerchantCenterPro::$conf['GMCP_EXCLUDED_DEST'],
            'aExcludedDest' => $GLOBALS['GMCP_EXCLUDED_DEST_VALUE'],
            'bExcludedCountry' => GMerchantCenterPro::$conf['GMCP_EXCLUDED_COUNTRY'],
        );

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ADVANCED_TAG_CATEGORY,
            'assign' => $aAssign,
        );
    }

    /**
     * displays search product name for autocomplete
     *
     * @param array $aPost
     * @return array
     */
    private function displaySearchProduct(array $aPost = null)
    {
        // clean headers
        @ob_end_clean();

        // set
        $sOutput = '';

        // get the query to search
        $sSearch = Tools::getValue('q');

        // get if we are in the case for CL
        $bCustomLabel = Tools::getValue('isCustomLabel');
        $sExcludedList = Tools::getValue('excludeIds');

        $bUseCombo = !empty($bCustomLabel) ? false : (int) GMerchantCenterPro::$conf['GMCP_P_COMBOS'];

        if (!empty($sSearch)) {
            $aMatchingProducts = BT_GmcProModuleDao::searchProducts($sSearch, $bUseCombo, $sExcludedList);

            if (!empty($aMatchingProducts)) {
                foreach ($aMatchingProducts as $aProduct) {
                    // check if we export with combinations
                    if (!empty($aProduct['id_product_attribute'])) {
                        $aCombinations = BT_GmcProModuleDao::getProductComboAttributes($aProduct['id_product_attribute'], GMerchantCenterPro::$iCurrentLang, GMerchantCenterPro::$iShopId);

                        if (!empty($aCombinations)) {
                            $sExtraName = '';
                            foreach ($aCombinations as $c) {
                                $sExtraName .= ' ' . Tools::stripslashes($c['name']);
                            }
                            $aProduct['name'] .= $sExtraName;
                        }
                    }
                    $sOutput .= trim($aProduct['name']) . '|' . (int) $aProduct['id_product'] . '|' . (!empty($aProduct['id_product_attribute']) ? $aProduct['id_product_attribute'] : '0') . "\n";
                }
            }
        }

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return (array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_PROD_SEARCH,
            'assign' => array('json' => $sOutput),
        ));
    }

    /**
     *  method displays the affected products by the rule
     *
     * @param array $aPost
     * @return array
     */
    private function displayExclusionRuleProducts(array $aPost = null)
    {
        // clean headers
        @ob_end_clean();

        $aAssign = array();
        $aExcludedProducts = array();

        $iRuleId = Tools::getValue('iRuleId');

        // need to use the DAO class for exclusion
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        if (!empty($iRuleId)) {
            $aProducts = BT_GmcProExclusionDao::getProductExcludedById($iRuleId);

            foreach ($aProducts as $aProduct) {
                $oProduct = new Product($aProduct['id_product'], true, GMerchantCenterpro::$iCurrentLang);

                if (is_object($oProduct)) {
                    $sProductName = $oProduct->name;

                    //Use case manage the name with Combo value
                    if (!empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                        $sComboName = BT_GmcProModuleTools::getProductCombinationName($aProduct['id_product_attribute'], GMerchantCenterpro::$iCurrentLang, GMerchantCenterpro::$iShopId);
                        $sProductName .= ' ' . $sComboName;
                    }

                    $aExcludedProducts[] = array(
                        'id' => $oProduct->id,
                        'name' => $sProductName,
                    );
                }
            }
        }

        unset($oProduct);

        $aAssign['aProductsData'] = $aExcludedProducts;

        // force xhr mode
        GMerchantCenterpro::$sQueryMode = 'xhr';

        return (array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_EXCLUDED_PRODUCTS,
            'assign' => $aAssign,
        ));
    }

    /**
     * method displays custom rules
     *
     * @param array $aPost
     * @return array
     */
    private function displayExcludeValue(array $aPost)
    {
        // clean headers
        @ob_end_clean();
        $iRuleId = Tools::getValue('iRuleId');
        $bAddTmpRules = Tools::getValue('bUpdate');

        $aAssign = array();

        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-render_class.php');

        // Init the render object
        $oRender = new BT_GmcProExclusionRender();

        $aAssign = $oRender->render($aPost['sExclusionType']);
        // Use case for update rule
        if (!empty($iRuleId) && !empty($bAddTmpRules)) {
            // need to use the DAO class for exclusion
            require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

            $aData = BT_GmcProExclusionDao::getExclusionRulesById((int) $iRuleId);
            $aAssign['aDataRule'] = unserialize($aData['exclusion_value']);
            $aAssign['sType'] = $aData['type'];
            $aAssign['iRuleId'] = $aData['id'];

            //Use case for to add on the tmp rules for update display
            $aTmpData = unserialize($aData['exclusion_value']);
            foreach ($aTmpData as $sKey => $aRuleDetailData) {
                //Use case for a rules detail
                if ($sKey == 'aRulesDetail') {
                    foreach ($aRuleDetailData as $aRuleDetailFilter) {
                        if (!BT_GmcProExclusionDao::addTmpDataRules(GMerchantCenterpro::$iShopId, $aData['type'], $aRuleDetailFilter)) {
                            throw new Exception(GMerchantCenterpro::$oModule->l('Could not add tmp rules', 'admin-update_class') . '.', 700);
                        }
                    }
                }
            }
        }

        // Use case for feature values
        if (!empty($aPost['iFeatureId']) || $aPost['sExclusionType'] == 'feature') {
            $aAssign = $oRender->render('feature', $aPost, $aData);
        }

        // Use case for attribute values on ajax request
        if ($aPost['sExclusionType'] == 'attribute' || !empty($aPost['iAttributeId'])) {
            $aAssign = $oRender->render('attribute', $aPost, $aData);
        }
        // Use case for words values
        if ($aPost['sExclusionType'] == 'word') {
            $aAssign = $oRender->render('word', $aPost, $aData);
        }

        // Use case for category values
        if ($aPost['sExclusionType'] == 'category') {
            $aAssign = $oRender->render('category', $aPost, $aData);
        }

        // Use case for manufacturer values
        if ($aPost['sExclusionType'] == 'manufacturer') {
            $aAssign = $oRender->render('manufacturer', $aPost, $aData);
        }

        // Use case for supplier values
        if ($aPost['sExclusionType'] == 'supplier') {
            $aAssign = $oRender->render('supplier', $aPost, $aData);
        }

        //Force XHR
        GMerchantCenterpro::$sQueryMode = 'xhr';
        return (array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_EXCLUSION_VALUES,
            'assign' => $aAssign,
        ));
    }

    /**
     * method displays custom rules
     *
     * @param array $aPost
     * @return array
     */
    private function displayRulesSummary(array $aPost)
    {

        // clean headers
        @ob_end_clean();

        $aAssign = array();

        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-render_class.php');
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');

        if (!empty($aPost['sTmpRules'])) {
            // Init the render object
            $oRender = new BT_GmcProExclusionRender();

            //Use case for the delete
            if (!empty($aPost['sDelete']) && !empty($aPost['iRuleId'])) {
                BT_GmcProExclusionDao::deleteTmpRules($aPost['iRuleId']);
            }

            $aRulesData = $oRender->render('Rules', $aPost);

            if (!empty($aRulesData)) {
                $aAssign['aTmpRules'] = $aRulesData;
                $aAssign['aProducts'] = $oRender->render('Products', null, $aRulesData);
            }
        }

        GMerchantCenterpro::$sQueryMode = 'xhr';

        return (array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_RULES_SUMMARY,
            'assign' => $aAssign,
        ));
    }

    /**
     * method displays custom rules form configuration
     *
     * @param array $aPost
     * @return array
     */
    private function displayExclusionRule(array $aPost = null)
    {
        // Clean the database with tmp rules
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');
        BT_GmcProExclusionDao::cleanTmpRules();
        BT_GmcProExclusionDao::resetIncrement();

        $aAssign = array();
        // clean headers
        @ob_end_clean();
        $iRuleId = Tools::getValue('iRuleId');
        $aDataRules = BT_GmcProExclusionDao::getExclusionRulesById((int) $iRuleId);

        $aAssign['bRefreshRules '] = false;

        //Use case for the refresh rules
        $aAssign = array(
            'aExclusionType' => $GLOBALS['GMCP_EXCLUSION_TYPE'],
            'aExclusionWordType' => $GLOBALS['GMCP_EXCLUSION_TYPE_WORD'],
            'aFeatures' => Feature::getFeatures(GMerchantCenterpro::$iCurrentLang),
            'aAttributes' => AttributeGroup::getAttributesGroups(GMerchantCenterpro::$iCurrentLang),
            'iRuleId' => !empty($iRuleId) ? $iRuleId : '',
        );

        // Use case for update rule
        if (!empty($iRuleId)) {
            $aAssign['aDataRule'] =  $aDataRules;
        }

        // force xhr mode
        GMerchantCenterpro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_EXECLUSION_RULES,
            'assign' => $aAssign,
        );
    }

    /**
     * returns ids used for PrestaShop flags displaying
     *
     * @return string
     */
    private function getFlagIds()
    {
        // set
        $sFlagIds = '';

        if (!empty($this->aFlagIds)) {
            // loop on each ids
            foreach ($this->aFlagIds as $sId) {
                $sFlagIds .= $sId . '¤';
            }

            $sFlagIds = substr($sFlagIds, 0, (strlen($sFlagIds) - 2));
        }

        return $sFlagIds;
    }

    /**
     * displays inventory
     *
     * @param array $aPost
     * @return array
     */
    private function displayInventory(array $aPost = null)
    {
        $aAssign = array(
            'sStoreCode' => GMerchantCenterPro::$conf['GMCP_STORE_CODE'],
            'aLiaPickup' => array(
                array(
                    'value' => 'buy',
                    'label' => GMerchantCenterpro::$oModule->l('Buy', 'admin-update_class'),
                ),
                array(
                    'value' => 'reserve',
                    'label' => GMerchantCenterpro::$oModule->l('Reserve', 'admin-update_class'),
                ),
                array(
                    'value' => 'ship to store',
                    'label' => GMerchantCenterpro::$oModule->l('Ship to store', 'admin-update_class'),
                ),
                array(
                    'value' => 'not supported',
                    'label' => GMerchantCenterpro::$oModule->l('Not supported', 'admin-update_class'),
                ),
            ),
            'sLiaPikcup' => GMerchantCenterPro::$conf['GMCP_LIA_PICKUP'],
            'aLiaPickupSla' => array(
                array(
                    'value' => 'same day',
                    'label' => GMerchantCenterpro::$oModule->l('Same day', 'admin-update_class'),
                ),
                array(
                    'value' => 'next day',
                    'label' => GMerchantCenterpro::$oModule->l('Next day', 'admin-update_class'),
                ),
                array(
                    'value' => '2-day',
                    'label' => GMerchantCenterpro::$oModule->l('2 days', 'admin-update_class'),
                ),
                array(
                    'value' => '3-day',
                    'label' => GMerchantCenterpro::$oModule->l('3 days', 'admin-update_class'),
                ),
                array(
                    'value' => '4-day',
                    'label' => GMerchantCenterpro::$oModule->l('4 days', 'admin-update_class'),
                ),
                array(
                    'value' => '5-day',
                    'label' => GMerchantCenterpro::$oModule->l('5 days', 'admin-update_class'),
                ),
                array(
                    'value' => '6-day',
                    'label' => GMerchantCenterpro::$oModule->l('6 days', 'admin-update_class'),
                ),
                array(
                    'value' => 'multi-week',
                    'label' => GMerchantCenterpro::$oModule->l('More than one week', 'admin-update_class'),
                ),
            ),
            'sLiaPikcupSla' => GMerchantCenterPro::$conf['GMCP_LIA_PICKUP_SLA'],
        );
        
        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_INVENTORY_FEED,
            'assign' => $aAssign,
        );
    }

    /**
     * displays inventory feed
     *
     * @param array $aPost
     * @return array
     */
    private function displayInventoryFeed(array $aPost = null)
    {
        $aAssign = array(
            'iShopId' => GMerchantCenterPro::$iShopId,
            'sGmcLink' => GMerchantCenterPro::$conf['GMCP_LINK'],
            'bReporting' => GMerchantCenterPro::$conf['GMCP_REPORTING'],
            'iTotalProductToExport' => BT_GmcProModuleDao::getProductIds(GMerchantCenterPro::$iShopId, (int)GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'], true),
            'iTotalDiscountToExport' => BT_GmcProCartRulesDao::getCartRulesId(),
            'iTotalProduct' => BT_GmcProModuleDao::countProducts(GMerchantCenterPro::$iShopId, (int)GMerchantCenterPro::$conf['GMCP_P_COMBOS']),
        );

        if (!empty($aAssign['sGmcLink'])) {
            if (!empty(GMerchantCenterPro::$aAvailableLangCurrencyCountry)) {
                foreach (GMerchantCenterPro::$aAvailableLangCurrencyCountry as $aData) {
                    foreach (GMerchantCenterPro::$aAvailableLangCurrencyCountry as $sKey => $aData) {
                        $aAssign['aFlyFileListLocal'][] = array(
                            'currencyIso' => $aData['currencyIso'],
                            'iso_code' => $aData['langIso'],
                            'countryIso' => $aData['countryIso'],
                            'link' => Context::getContext()->link->getModuleLink(_GMCP_MODULE_SET_NAME, _GMCP_CTRL_FLY, array('id_shop' =>  GMerchantCenterPro::$iShopId, 'gmcp_lang_id' => $aData['langId'], 'country' => $aData['countryIso'], 'currency_iso' => $aData['currencyIso'], 'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'], 'sType' => 'flyOutput', 'feed_type' => 'local')),
                            'currencySign' => $aData['currencySign'],
                            'countryName' => $aData['countryName'],
                            'langName' => $aData['langName'],
                        );
                    }
                }
            }
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_LIA_LIST,
            'assign' => $aAssign,
        );
    }


    /**
     * sets ids used for PrestaShop flags displaying
     */
    private function setFlagIds()
    {
        // set
        $sFlagIds = '';

        $this->aFlagIds = array(
            strtolower(_GMCP_MODULE_NAME) . 'Title',
        );
    }
}
