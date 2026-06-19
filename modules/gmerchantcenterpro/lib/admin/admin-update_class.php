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

class BT_AdminUpdate implements BT_IAdmin
{
    /**
     * update all tabs content of admin page
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
            case 'stepPopup': // use case - step by step bar
            case 'basic': // use case - update basic settings
            case 'gsa': // use case - update gsa
            case 'gsaOauth': // use case - update gsa Oauth
            case 'shopLink': // use case for shop link management
            case 'feed': // use case - update feed settings
            case 'advancedfeed': // use case - configure advanced feed for export
            case 'feedList': // use case - update feed list settings
            case 'tag': // use case - update advanced tag settings
            case 'label': // use case - update custom label settings
            case 'labelState': // use case - update custom label statut active or not
            case 'customLabelList': // use case - update customlabelList with bulk action
            case 'position': // use case - update position with bulk action
            case 'customLabelDate': // use case - update custome label date with bulk action
            case 'customCheck': // use case - associate product to custom label during the data feed udpdate
            case 'google': // use case - update google campaign settings
            case 'googleCategoriesMatching': // use case - update google categories matching settings
            case 'reporting': // use case - update reporting settings
            case 'googleCategoriesSync': // use case - update google categories sync action
            case 'xml': // use case - update the xml filecase 'xml'
            case 'exclusionRule': // use case - update exclusion rules
            case 'rulesList': // use case - update exclusion rules from list
            case 'inventory': // use case - update exclusion rules from list
                // execute match function
                $aDisplayData = call_user_func_array(array($this, 'update' . ucfirst($sType)), array($aParam));
                break;
            default:
                break;
        }
        return $aDisplayData;
    }


    /**
     * method update advice settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateStepPopup(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        Configuration::updateValue('GMCP_CONF_STEP_3', 1);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_BODY,
            'assign' => $aAssign,
        );
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
        $aData = array();

        try {
            // register title
            $sShopLink = Tools::getValue('bt_link');

            // clean the end slash if exists
            if (substr($sShopLink, -1) == '/') {
                $sShopLink = substr($sShopLink, 0, strlen($sShopLink) - 1);
            }
            if (!Configuration::updateValue('GMCP_LINK', str_replace(' ', '', $sShopLink))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during shop link update', 'admin-update_class') . '.', 100);
            }

            // Use case for the lang id on the prefix
            if (Tools::getIsset('bt_simple_id')) {
                $bSimpleProduct = Tools::getValue('bt_simple_id');
                if (!Configuration::updateValue('GMCP_SIMPLE_PROD_ID', $bSimpleProduct)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during lang id update', 'admin-update_class') . '.', 101);
                }
            }

            // register prefix
            $sPrefix = Tools::getValue('bt_prefix-id');
            if (!Configuration::updateValue('GMCP_ID_PREFIX', BT_GmcProModuleTools::cleanUpPrefix($sPrefix))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during shop prefix ID update', 'admin-update_class') . '.', 102);
            }

            // register home category name in all active languages
            $this->updateLang($aPost, 'bt_home-cat-name', 'GMCP_HOME_CAT', false, GMerchantCenterPro::$oModule->l('type of product sold', 'admin-update_class'));

            // register ajax cycle
            $iAjaxCycle = Tools::getValue('bt_ajax-cycle');
            if (!Configuration::updateValue('GMCP_AJAX_CYCLE', $iAjaxCycle)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during ajax cycle update', 'admin-update_class') . '.', 103);
            }

            // register image type
            $sImageType = Tools::getValue('bt_image-size');
            if (!Configuration::updateValue('GMCP_IMG_SIZE', $sImageType)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during image size update', 'admin-update_class') . '.', 104);
            }

            // register home category ID
            $iHomeCatId = Tools::getValue('bt_home-cat-id');
            if (!Configuration::updateValue('GMCP_HOME_CAT_ID', $iHomeCatId)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during home category ID update', 'admin-update_class') . '.', 105);
            }

            // Register add additionnal images option
            if (!Configuration::updateValue('GMCP_ADD_IMAGES', Tools::getValue('bt_add_images'))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during additional images update', 'admin-update_class') . '.', 106);
            }

            // Register force identifier exist
            if (!Configuration::updateValue('GMCP_FORCE_IDENTIFIER', Tools::getValue('bt_identifier_exist'))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during identifier exists forcing update', 'admin-update_class') . '.', 107);
            }

            // register if add currency or not
            $bAddCurrency = Tools::getValue('bt_add-currency');
            if (!Configuration::updateValue('GMCP_ADD_CURRENCY', $bAddCurrency)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during currency adding update', 'admin-update_class') . '.', 108);
            }

            // register product condition
            $sProductCondition = Tools::getValue('bt_product-condition');
            if (!Configuration::updateValue('GMCP_COND', $sProductCondition)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during product condition update', 'admin-update_class') . '.', 109);
            }

            //Use case for the title option
            if (Tools::getIsset('bt_prod-title')) {
                // how to export products
                $bProductTitle = Tools::getValue('bt_prod-title');
                if (!Configuration::updateValue('GMCP_P_TITLE', $bProductTitle)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about export by product or combinations', 'admin-update_class') . '.', 110);
                }
            }

            // register advanced product name
            $sAdvancedProdName = Tools::getValue('bt_advanced-prod-name');
            if (!Configuration::updateValue('GMCP_ADV_PRODUCT_NAME', $sAdvancedProdName)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during advanced name format update', 'admin-update_class') . '.', 111);
            }

            // Update the values if the option is for free field option
            if ($sAdvancedProdName == 5) {
                // the prefix for advanced prefix product name
                $this->updateLang($aPost, 'bt_advanced_prefix_name', 'GMCP_ADV_PROD_NAME_PREFIX', false, GMerchantCenterPro::$oModule->l('product name prefix', 'admin-update_class'), false);

                // the suffix for advanced suffix product name
                $this->updateLang($aPost, 'bt_advanced_suffix_name', 'GMCP_ADV_PROD_NAME_SUFFIX', false, GMerchantCenterPro::$oModule->l('product name suffix', 'admin-update_class'), false);
            }

            // register protection mode
            if (!Configuration::updateValue('GMCP_FEED_PROTECTION', 1)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during protection mode update', 'admin-update_class') . '.', 112);
            }

            // register feed token
            $sFeedToken = Tools::getValue('bt_feed-token');
            if (!Configuration::updateValue('GMCP_FEED_TOKEN', $sFeedToken)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during feed token update', 'admin-update_class') . '.', 113);
            }

            // register advanced product title
            $sAdvancedProdTitle = Tools::getValue('bt_advanced-prod-title');
            if (!Configuration::updateValue('GMCP_ADV_PROD_TITLE', $sAdvancedProdTitle)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during title format update', 'admin-update_class') . '.', 114);
            }

            // Update stepper
            Configuration::updateValue('GMCP_CONF_STEP_1', 1);
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('basics');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return $aDisplay;
    }

    /**
     * update gsa configuration
     *
     * @param array $aPost
     * @return array
     */
    private function updateGsa(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        require_once(_GMCP_PATH_LIB_GSA . 'gsa-client_class.php');

        try {

            //Use case to build the module configuration return URL
            if (empty(GMerchantCenterPro::$bCompare17)) {
                $sAdminFolder = array_pop((array_slice(explode('/', _PS_ADMIN_DIR_), -1)));
            }
            $oTokenData = json_decode(Configuration::get('GMCP_OAUTH'));

            // Update merchant ID
            $sMerchantId = Tools::getValue('bt_merchant-id');

            if (!Configuration::updateValue('GMCP_MERCHANT_ID', $sMerchantId)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during Merchant Center ID update', 'admin-update_class') . '.', 101);
            }

            //Customer group update
            if (Tools::getIsset('bt_default-group')) {
                $iDefaultCustGroup = Tools::getValue('bt_default-group');

                if (is_numeric($iDefaultCustGroup)) {
                    if (!Configuration::updateValue('GMCP_GSA_CUSTOMER_GROUP', $iDefaultCustGroup)) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during default customer group update', 'admin-update_class') . '.', 102);
                    }
                } else {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Default customer group is not a numeric', 'admin-update_class') . '.', 103);
                }
            }

            // Update gsa carrier ID
            $iCarrierId = Tools::getValue('bt_gsa-carrier-default');
            if (!Configuration::updateValue('GMCP_GSA_DEFAULT_CARRIER', (int)$iCarrierId)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during carrier ID update', 'admin-update_class') . '.', 104);
            }

            // Update the gsa carrier mapping
            if (Tools::getIsset('bt_gsa-carrier')) {
                $aShippingCarriers = array();
                $aPostShippingCarriers = Tools::getValue('bt_gsa-carrier');

                if (
                    !empty($aPostShippingCarriers)
                    && is_array($aPostShippingCarriers)
                ) {
                    foreach ($aPostShippingCarriers as $iKey => $mVal) {
                        $aShippingCarriers[$iKey] = $mVal;
                    }
                    $sShippingCarriers = serialize($aShippingCarriers);
                } else {
                    $sShippingCarriers = '';
                }
                if (!Configuration::updateValue('GMCP_GSA_CARRIERS_MAP', $sShippingCarriers)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during carriers matching update', 'admin-update_class') . '.', 102);
                }
            }

            //Update min handling time
            // $iMinHandlingTime = !empty(Tools::getValue('bt_min-handling-time')) ? Tools::getValue('bt_min-handling-time') : 2;
            // if (!Configuration::updateValue('GMCP_MIN_HANDLING_TIME', (int)$iMinHandlingTime)) {
            //     throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during min handling time update', 'admin-update_class') . '.', 105);
            // }

            // //Update min handling time
            // $iMaxHandlingTime = !empty(Tools::getValue('bt_max-handling-time')) ? Tools::getValue('bt_max-handling-time') : 4;
            // if (!Configuration::updateValue('GMCP_MAX_HANDLING_TIME', (int)$iMaxHandlingTime)) {
            //     throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during max handling time update', 'admin-update_class') . '.', 106);
            // }

            //Update percentage of stock to export
            $iPercentExport = Tools::getValue('bt_percent_stock');
            if ($iPercentExport > 0) {
                if (!Configuration::updateValue('GMCP_GSA_STOCK', (int)$iPercentExport)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during stock percentage update', 'admin-update_class') . '.', 107);
                }
            }

            $sFundedPromo = Tools::getValue('bt_funded_promotion');
            if (!Configuration::updateValue('GMCP_FUNDED_PROMO', $sFundedPromo)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during Google promotions eligibility update', 'admin-update_class') . '.', 108);
            }

            // Mangage data send to API
            $aConf = array(
                'merchant_id' => GMerchantCenterPro::$conf['GMCP_MERCHANT_ID'],
                'module_name' => GMerchantCenterPro::$oModule->name,
                'module_url' => !empty(GMerchantCenterPro::$bCompare17) ? Context::getContext()->link->getAdminLink('AdminModules') . '&configure=' . GMerchantCenterPro::$oModule->name : _PS_BASE_URL_ . __PS_BASE_URI__ . $sAdminFolder . '/' . Context::getContext()->link->getAdminLink('AdminModules') . '&configure=' . GMerchantCenterPro::$oModule->name,
                'backoffice_url' =>  !empty(GMerchantCenterPro::$bCompare17) ? Context::getContext()->link->getAdminLink('AdminModules') : _PS_BASE_URL_ . __PS_BASE_URI__ . $sAdminFolder . '/' . Context::getContext()->link->getAdminLink('AdminDashboard'),
                'backoffice_orders_url' => !empty(GMerchantCenterPro::$bCompare17) ? Context::getContext()->link->getAdminLink('AdminModules') : _PS_BASE_URL_ . __PS_BASE_URI__ . $sAdminFolder . '/' . Context::getContext()->link->getAdminLink('AdminOrders'),
                'module_conf' => GMerchantCenterPro::$conf,
                'module_version' => GMerchantCenterPro::$conf['GMCP_VERSION'],
                'ApiToken' => $oTokenData->accessToken,
            );

            //Send the configuration and create the shop on the API token is ok
            if (!empty($oTokenData->accessToken)) {
                GsaClient::updateModuleConfigurationForGsa($oTokenData->accessToken, $aConf);
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('gsa');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return $aDisplay;
    }

    /**
     * update gsa oauth 
     *
     * @param array $aPost
     * @return array
     */
    private function updateGsaOauth(array $aPost)
    {
        // set
        $aData = array();

        require_once(_GMCP_PATH_LIB_GSA . 'gsa-client_class.php');
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        if (empty(BT_GmcProModuleTools::isOauthed())) {
            // If we have the code and state params in the URL, we are handling the post authorization logic
            $moduleConfiguration['state'] = GsaClient::codeVerifierGen(40);
            $moduleConfiguration['codeVerifier'] = GsaClient::codeVerifierGen();

            // Save the generated codes
            Configuration::updateValue('GMCP_OAUTH', json_encode($moduleConfiguration));

            // Build the query params for the authorization request
            $oAuthQueryParams = http_build_query([
                'redirect_uri' => BT_GmcProModuleTools::generatePublicModuleUrl(),
                'response_type' => 'code',
                'code_challenge' => GsaClient::createCodeChallengeFromVerifier($moduleConfiguration['codeVerifier']),
                'code_challenge_method' => 'S256',
                'scope' => '',
                'state' => $moduleConfiguration['state'],
                'cms_name' => 'PrestaShop',
                'shop_url' => rtrim((string) BT_GmcProModuleTools::getBaseLink(), '/'),
            ]);

            Context::getContext()->smarty->assign(array(
                'generatedLink' => GsaClient::API_URL . 'oauth/authorize?' . $oAuthQueryParams,
                'linkTitle' => GMerchantCenterPro::$oModule->l('Link my subscription', 'admin-update_class'),
                'isError' => false,
                'bLinkBlock' => true,
            ));

            // Display the onboarding page to let the merchant click on the generated authorize link
            $aDisplay = BT_AdminDisplay::create()->run('onBoarding');

            // use case - empty error and updating status
            $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
                'bUpdate' => (empty($aData['aErrors']) ? true : false),
            ), $aData);

            // force xhr mode
            GMerchantCenterPro::$sQueryMode = 'xhr';

            return $aDisplay;
        }
    }


    /**
     * update shop link association
     *
     * @param array $aPost
     * @return array
     */
    private function updateShopLink(array $aPost)
    {
        // clean headers
        @ob_end_clean();
        $aData = array();
        require_once(_GMCP_PATH_LIB_GSA . 'gsa-client_class.php');

        $bActivate = Tools::getValue('bLink');

        if (!empty(BT_GmcProModuleTools::isOauthed())) {
            if (empty($bActivate)) {
                if (GsaClient::disableShop(json_decode(GMerchantCenterPro::$conf['GMCP_OAUTH'])->accessToken)) {
                    Configuration::updateValue('GMCP_SHOP_LINK_API', 0);
                }
            } else {
                if (GsaClient::enableShop(json_decode(GMerchantCenterPro::$conf['GMCP_OAUTH'])->accessToken)) {
                    Configuration::updateValue('GMCP_SHOP_LINK_API', 1);
                }
            }
        }


        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('gsa');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * update feed management settings
     *
     * @throws
     * @param array $aPost
     * @return array
     */
    private function updateFeed(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

            /* USE CASE - update categories and brands to export */
            if (Tools::getIsset('bt_export')) {
                $bExportMode = Tools::getValue('bt_export');
                if (!Configuration::updateValue('GMCP_EXPORT_MODE', $bExportMode)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during export mode update', 'admin-update_class') . '.', 200);
                }

                // handle categories and brands to export
                if ($bExportMode == 0) {
                    $aCategoryBox = Tools::getValue('bt_category-box');

                    if (empty($aCategoryBox)) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred because you must select one category at least', 'admin-update_class') . '.', 201);
                    } else {
                        // delete previous categories
                        $bResult = BT_GmcProModuleDao::deleteCategories(GMerchantCenterPro::$iShopId);

                        foreach ($aCategoryBox as $iCatId) {
                            // insert
                            $bResult = BT_GmcProModuleDao::insertCategory($iCatId, GMerchantCenterPro::$iShopId);
                        }
                    }
                } else {
                    $aBrandBox = Tools::getValue('bt_brand-box');

                    if (empty($aBrandBox)) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred because you must select one brand at least', 'admin-update_class') . '.', 202);
                    } else {
                        // delete previous brands
                        BT_GmcProModuleDao::deleteBrands(GMerchantCenterPro::$iShopId);

                        foreach ($aBrandBox as $iBrandId) {
                            // insert
                            BT_GmcProModuleDao::insertBrand($iBrandId, GMerchantCenterPro::$iShopId);
                        }
                    }
                }
            }


            // Update timeline update
            Configuration::updateValue('GMCP_CONF_STEP_2', 1);

            /* USE CASE - update exclusion rules */
            // handle if we export or not products out of stock
            if (Tools::getIsset('bt_export-oos')) {
                $bExportOOSMode = Tools::getValue('bt_export-oos');
                if (!Configuration::updateValue('GMCP_EXPORT_OOS', $bExportOOSMode)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about out of stock product export', 'admin-update_class') . '.', 203);
                }

                if ($bExportOOSMode) {
                    $bProductOosOrder = Tools::getValue('bt_product-oos-order');
                    if (!Configuration::updateValue('GMCP_EXPORT_PROD_OOS_ORDER', $bProductOosOrder)) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during out of stock product update', 'admin-update_class') . '.', 204);
                    }
                }
            }
            // handle if we export or not products without EAN code
            if (Tools::getIsset('bt_excl-no-ean')) {
                $bExportNoEan = Tools::getValue('bt_excl-no-ean');
                if (!Configuration::updateValue('GMCP_EXC_NO_EAN', $bExportNoEan)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about the export of product without EAN', 'admin-update_class') . '.', 205);
                }
            }
            // handle if we export or not products without manufacturer code
            if (Tools::getIsset('bt_excl-no-mref')) {
                $bExportNoMref = Tools::getValue('bt_excl-no-mref');
                if (!Configuration::updateValue('GMCP_EXC_NO_MREF', $bExportNoMref)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about the export of product without MPN', 'admin-update_class') . '.', 206);
                }
            }
            // handle if we export products over a min price
            if (Tools::getIsset('bt_min-price')) {
                $fMinPrice = Tools::getValue('bt_min-price');
                if (!Configuration::updateValue(
                    'GMCP_MIN_PRICE',
                    (!empty($fMinPrice) ? number_format(str_replace(',', '.', $fMinPrice), 2) : 0.00)
                )) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about the min price', 'admin-update_class') . '.', 207);
                }
            }
            // handle if we export products over a weight
            if (Tools::getIsset('bt_max-weight')) {
                $fMaxWeight = Tools::getValue('bt_max-weight');
                if (!Configuration::updateValue(
                    'GMCP_MAX_WEIGHT',
                    (!empty($fMaxWeight) ? number_format(str_replace(',', '.', $fMaxWeight), 2) : 0.00)
                )) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about the max weight', 'admin-update_class') . '.', 208);
                }
            }
            /* USE CASE - update feed data options */
            if (Tools::getIsset('bt_prod-title')) {
                // how to export products
                $bProductTitle = Tools::getValue('bt_prod-title');
                if (!Configuration::updateValue('GMCP_P_TITLE', $bProductTitle)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about export by product or combinations', 'admin-update_class') . '.', 209);
                }
            }

            /* USE CASE - update combo export */
            if (Tools::getIsset('bt_prod-combos')) {
                // how to export products
                $bProductCombos = Tools::getValue('bt_prod-combos');
                if (!Configuration::updateValue('GMCP_P_COMBOS', $bProductCombos)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about export by product or combinations', 'admin-update_class') . '.', 210);
                }
                if (!empty($bProductCombos)) {
                    // use case - options around the combination URLs for the export each combination as a single product
                    if (Tools::getIsset('bt_rewrite-num-attr')) {
                        $bRewriteNumAttr = Tools::getValue('bt_rewrite-num-attr');
                        if (!Configuration::updateValue('GMCP_URL_NUM_ATTR_REWRITE', $bRewriteNumAttr)) {
                            throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about numeric attributes rewriting', 'admin-update_class') . '.', 211);
                        }
                    }
                    if (Tools::getIsset('bt_incl-attr-id')) {
                        $bInclAttrId = Tools::getValue('bt_incl-attr-id');
                        if (!Configuration::updateValue('GMCP_URL_ATTR_ID_INCL', $bInclAttrId)) {
                            throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about attribute ID adding', 'admin-update_class') . '.', 212);
                        }
                    }
                    if (Tools::getIsset('bt_combo-separator')) {
                        $sComboSeparator = Tools::getValue('bt_combo-separator');
                        if (!Configuration::updateValue('GMCP_COMBO_SEPARATOR', $sComboSeparator)) {
                            throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of the option about combination separator', 'admin-update_class') . '.', 213);
                        }
                    }
                }
            }

            // how to use the product desc
            if (Tools::getIsset('bt_prod-desc-type')) {
                $iProdDescType = Tools::getValue('bt_prod-desc-type');
                if (!Configuration::updateValue('GMCP_P_DESCR_TYPE', $iProdDescType)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during desc type update', 'admin-update_class') . '.', 213);
                }
            }

            // product availability
            if (Tools::getIsset('bt_incl-stock')) {
                $bInclStock = Tools::getValue('bt_incl-stock');
                if (!Configuration::updateValue('GMCP_INC_STOCK', $bInclStock)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during product availability update', 'admin-update_class') . '.', 214);
                }
            }

            // include adult tag
            if (Tools::getIsset('bt_incl-tag-adult')) {
                $bInclAdultTag = Tools::getValue('bt_incl-tag-adult');
                if (!Configuration::updateValue('GMCP_INC_TAG_ADULT', $bInclAdultTag)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during adult tag update', 'admin-update_class') . '.', 215);
                }
            }
            // include cost of good sold
            if (Tools::getIsset('bt_incl-tag-cost')) {
                if (!Configuration::updateValue('GMCP_INC_COST', Tools::getValue('bt_incl-tag-cost'))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during cost of goods sold tag update', 'admin-update_class') . '.', 216);
                }
            }

            // include size tag
            if (Tools::getIsset('bt_incl-size')) {
                $sInclSize = Tools::getValue('bt_incl-size');
                $aSizeIds = Tools::getValue('bt_size-opt');
                if (!Configuration::updateValue('GMCP_INC_SIZE', $sInclSize)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during size tag update', 'admin-update_class') . '.', 217);
                }

                // update attributes and the feature for size tag
                if (!empty($sInclSize) && !empty($aSizeIds)) {
                    if (!Configuration::updateValue('GMCP_SIZE_OPT', serialize($aSizeIds))) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during size IDs update', 'admin-update_class') . '.', 218);
                    }
                }
            }

            // include color tag
            if (Tools::getIsset('bt_incl-color')) {
                $sInclColor = Tools::getValue('bt_incl-color');
                $aColorIds = Tools::getValue('bt_color-opt');
                if (!Configuration::updateValue('GMCP_INC_COLOR', $sInclColor)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during color tag update', 'admin-update_class') . '.', 219);
                }
                // update attributes and the feature for color tag
                if (!empty($sInclColor) && !empty($aColorIds)) {
                    if (!Configuration::updateValue('GMCP_COLOR_OPT', serialize($aColorIds))) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during color IDs update', 'admin-update_class') . '.', 220);
                    }
                }
            }

            /* USE CASE - update apparel feed options */
            // include material tag
            if (Tools::getIsset('bt_incl-material')) {
                $bInclMaterial = Tools::getValue('bt_incl-material');
                if (!Configuration::updateValue('GMCP_INC_MATER', $bInclMaterial)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during material tag update', 'admin-update_class') . '.', 221);
                }
            }

            // include pattern tag
            if (Tools::getIsset('bt_incl-pattern')) {
                $bInclPattern = Tools::getValue('bt_incl-pattern');
                if (!Configuration::updateValue('GMCP_INC_PATT', $bInclPattern)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during pattern tag update', 'admin-update_class') . '.', 222);
                }
            }

            // include gender tag
            if (Tools::getIsset('bt_incl-gender')) {
                $bInclGender = Tools::getValue('bt_incl-gender');
                if (!Configuration::updateValue('GMCP_INC_GEND', $bInclGender)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during gender tag update', 'admin-update_class') . '.', 223);
                }
            }

            // include age group tag
            if (Tools::getIsset('bt_incl-age')) {
                $bInclAge = Tools::getValue('bt_incl-age');
                if (!Configuration::updateValue('GMCP_INC_AGE', $bInclAge)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during age group tag update', 'admin-update_class') . '.', 224);
                }
            }

            // include size type
            if (Tools::getIsset('bt_incl-size_type')) {
                $bInclSizeType = Tools::getValue('bt_incl-size_type');
                if (!Configuration::updateValue('GMCP_SIZE_TYPE', $bInclSizeType)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during size type tag update', 'admin-update_class') . '.', 225);
                }
            }

            // include size system
            if (Tools::getIsset('bt_incl-size_system')) {
                $bInclSizeSystem = Tools::getValue('bt_incl-size_system');
                if (!Configuration::updateValue('GMCP_SIZE_SYSTEM', $bInclSizeSystem)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during size system tag update', 'admin-update_class') . '.', 226);
                }
            }

            /* USE case for advanced tag */
            if (Tools::getIsset('bt_incl-energy')) {
                $bInclEnergy = Tools::getValue('bt_incl-energy');
                if (!Configuration::updateValue('GMCP_INC_ENERGY', $bInclEnergy)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during energy efficiency class tag update', 'admin-update_class') . '.', 227);
                }
            }

            if (Tools::getIsset('bt_excl_dest')) {
                $bExclDest = Tools::getValue('bt_excl_dest');
                if (!Configuration::updateValue('GMCP_EXCLUDED_DEST', $bExclDest)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during exluded destination tag update', 'admin-update_class') . '.', 227);
                }
            }

            // include exclusion destination
            if (Tools::getIsset('bt_excl_country')) {
                $bExclCountry = Tools::getValue('bt_excl_country');
                if (!Configuration::updateValue('GMCP_EXCLUDED_COUNTRY', $bExclCountry)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during excluded country tag update', 'admin-update_class') . '.', 230);
                }
            }

            if (Tools::getIsset('bt_incl-shipping-label')) {
                if (!Configuration::updateValue('GMCP_INC_SHIPPING_LABEL', Tools::getValue('bt_incl-shipping-label'))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during shipping label tag update', 'admin-update_class') . '.', 228);
                }
            }

            if (Tools::getIsset('bt_incl_unit_pricing_measure')) {
                if (!Configuration::updateValue(
                    'GMCP_INC_UNIT_PRICING',
                    Tools::getValue('bt_incl_unit_pricing_measure')
                )) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during unit pricing measure tag update', 'admin-update_class') . '.', 229);
                }
            }

            if (Tools::getIsset('bt_incl_unit_base_pricing_measure')) {
                if (!Configuration::updateValue(
                    'GMCP_INC_B_UNIT_PRICING',
                    Tools::getValue('bt_incl_unit_base_pricing_measure')
                )) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during unit base pricing measure tag update', 'admin-update_class') . '.', 230);
                }
            }

            /* USE CASE - update tax and shipping fees options */
            if (Tools::getIsset('bt_manage-shipping')) {
                $bShippingUse = Tools::getValue('bt_manage-shipping');
                if (!Configuration::updateValue('GMCP_SHIPPING_USE', $bShippingUse)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during shipping management option update', 'admin-update_class') . '.', 231);
                }
            }

            if (Tools::getIsset('bt_manage-dimension')) {
                $bDimension = Tools::getValue('bt_manage-dimension');
                if (!Configuration::updateValue('GMCP_DIMENSION', $bDimension)) {
                    throw new Exception(GMerchantCenter::$oModule->l('An error occurred during package dimensions option update', 'admin-update_class') . '.', 541);
                }
            }

            if (Tools::getIsset('bt_ship-carriers')) {
                $aShippingCarriers = array();
                $aPostShippingCarriers = Tools::getValue('bt_ship-carriers');

                if (
                    !empty($aPostShippingCarriers)
                    && is_array($aPostShippingCarriers)
                ) {
                    foreach ($aPostShippingCarriers as $iKey => $mVal) {
                        $aShippingCarriers[$iKey] = $mVal;
                    }
                    $sShippingCarriers = serialize($aShippingCarriers);
                } else {
                    $sShippingCarriers = '';
                }
                if (!Configuration::updateValue('GMCP_SHIP_CARRIERS', $sShippingCarriers)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during shipping carriers option update', 'admin-update_class') . '.', 232);
                }
            }

            // update attributes and the feature for size tag
            if (Tools::getIsset('hiddenProductIds')) {
                $sExcludedIds = Tools::getValue('hiddenProductIds');

                // get an array of
                $aExcludedIds = !empty($sExcludedIds) ? explode('-', $sExcludedIds) : array();

                if (!empty($aExcludedIds)) {
                    array_pop($aExcludedIds);
                }

                if (!Configuration::updateValue('GMCP_PROD_EXCL', serialize($aExcludedIds))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during excluded product IDs update', 'admin-update_class') . '.', 233);
                }
            }

            if (Tools::getIsset('hiddenProductFreeShippingIds')) {
                $sFreeShippingProductIds = Tools::getValue('hiddenProductFreeShippingIds');

                // get an array of
                $aIdsFreeShipping = !empty($sFreeShippingProductIds) ? explode('-', $sFreeShippingProductIds) : array();

                if (!empty($sFreeShippingProductIds)) {
                    array_pop($aIdsFreeShipping);
                }

                if (!Configuration::updateValue('GMCP_FREE_SHIP_PROD', serialize($aIdsFreeShipping))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the update of IDs of products with free shipping', 'admin-update_class') . '.', 234);
                }
            }

            // select the order to check the EAN-13 or UPC
            if (Tools::getIsset('bt_gtin-pref')) {
                $sGtinPref = Tools::getValue('bt_gtin-pref');
                if (!Configuration::updateValue('GMCP_GTIN_PREF', $sGtinPref)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during GTIN preferences update', 'admin-update_class') . '.', 235);
                }
            }

            if (Tools::getValue('sDisplay') == 'tax') {
                // update feed tax
                $aTmpFeedTax = Tools::getValue('bt_feed-tax') != false ? Tools::getValue('bt_feed-tax') : array();
                $aFeedTaxHidden = Tools::getValue('bt_feed-tax-hidden');

                foreach ($aFeedTaxHidden as $sFeed) {
                    $aFeedTax[$sFeed] = in_array($sFeed, $aTmpFeedTax) ? 1 : 0;
                }

                if (!Configuration::updateValue('GMCP_FEED_TAX', serialize($aFeedTax))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during feed tax selection', 'admin-update_class') . '.', 236);
                }
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration(array(
            'GMCP_COLOR_OPT',
            'GMCP_SIZE_OPT',
            'GMCP_SHIP_CARRIERS',
            'GMCP_PROD_EXCL',
            'GMCP_FEED_TAX',
            'GMCP_FREE_SHIP_PROD'
        ));

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with feed management settings updated
        $aDisplay = BT_AdminDisplay::create()->run('feed');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * update advancedfeed setting
     *
     * @throws
     * @param array $aPost
     * @return array
     */

    private function updateAdvancedfeed(array $aPost)
    {
        // set
        $aData = array();

        try {
            //manage configuration for filter
            $bFilterName = Tools::getValue('bt_option-name') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_NAME', $bFilterName)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the name', 'admin-update_class') . '.', 300);
            }

            $bFilterDate = Tools::getValue('bt_option-date') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_DATE', $bFilterDate)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the date', 'admin-update_class') . '.', 301);
            }

            $bFilterMinAmount = Tools::getValue('bt_option-min-amount') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_MIN_AMOUNT', $bFilterMinAmount)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the min amount', 'admin-update_class') . '.', 302);
            }

            $bFilterValue = Tools::getValue('bt_option-value') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_VALUE', $bFilterValue)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the value', 'admin-update_class') . '.', 303);
            }

            $bFilterType = Tools::getValue('bt_option-type') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_TYPE', $bFilterType)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the type', 'admin-update_class') . '.', 304);
            }

            $bFilterCumulable = Tools::getValue('bt_option-cumulable') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_CUMU', $bFilterCumulable)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the option about cumulation', 'admin-update_class') . '.', 305);
            }

            $bFilterFor = Tools::getValue('bt_option-for') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_DSC_FILT_FOR', $bFilterFor)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the "for" option', 'admin-update_class') . '.', 306);
            }

            //Discount name
            $sDiscountName = Tools::getValue('bt_discount-name');
            if (!Configuration::updateValue('GMCP_DSC_NAME', $sDiscountName)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the discount name', 'admin-update_class') . '.', 307);
            }

            //Discount date from
            $sDateFrom = Tools::getValue('bt_discount-date-from');
            if (!Configuration::updateValue('GMCP_DSC_DATE_FROM', $sDateFrom)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the discount start date', 'admin-update_class') . '.', 308);
            }

            //Discount date to
            $sDateTo = Tools::getValue('bt_discount-date-to');
            if (!Configuration::updateValue('GMCP_DSC_DATE_TO', $sDateTo)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the discount end date', 'admin-update_class') . '.', 309);
            }

            //Value min for export
            $fValueMin = Tools::getValue('bt_discount-value-min');
            if (!Configuration::updateValue('GMCP_DSC_VALUE_MIN', $fValueMin)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the voucher min value', 'admin-update_class') . '.', 310);
            }

            //Value min for export
            $fValueMax = Tools::getValue('bt_discount-value-max');
            if (!Configuration::updateValue('GMCP_DSC_VALUE_MAX', $fValueMax)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the voucher max value', 'admin-update_class') . '.', 311);
            }

            //Discount min amount
            $fMinAmount = Tools::getValue('bt_discount-min-amount');
            if (!Configuration::updateValue('GMCP_DSC_MIN_AMOUNT', $fMinAmount)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the min purchase amount', 'admin-update_class') . '.', 312);
            }

            //Type of discount to expert  date to
            $sDiscountType = Tools::getValue('bt_discount-type');
            if (!Configuration::updateValue('GMCP_DSC_TYPE', $sDiscountType)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the discount type', 'admin-update_class') . '.', 313);
            }

            //Discount min amount
            $bCumulable = Tools::getValue('bt_discount-cumulable');
            if (!Configuration::updateValue('GMCP_DSC_CUMULABLE', $bCumulable)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the option about cumulation of vouchers', 'admin-update_class') . '.', 314);
            }

            //Handle the save of channel for promotion feed
            $aDiscountChannel = Tools::getValue('bt-discount_channel');
            if (!Configuration::updateValue('GMCP_PROMO_DEST', serialize($aDiscountChannel))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the promotion destination tag', 'admin-update_class') . '.', 319);
            }

            //Use case for review feed
            $sWord = Tools::getValue('bt_words-review-forbidden');
            $aWords = array();

            if (!empty($sWord)) {
                // Use case if we have 2 expression or more
                $strPos = strpos($sWord, ',');
                if (!empty($strPos)) {
                    $aWords = explode(',', $sWord);
                } else {
                    $aWords[0] = $sWord;
                }
            }

            if (!Configuration::updateValue('GMCP_FORBIDDEN_WORDS', serialize($aWords))) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the forbidden words', 'admin-update_class') . '.', 315);
            }

            $bPrice = Tools::getValue('bt_inventory-price') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_INV_PRICE', $bPrice)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the price', 'admin-update_class') . '.', 316);
            }

            $bStock = Tools::getValue('bt_inventory-stock') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_INV_STOCK', $bStock)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the stock', 'admin-update_class') . '.', 317);
            }

            $bSalePrice = Tools::getValue('bt_inventory-sale-price') == 'true' ? true : false;
            if (!Configuration::updateValue('GMCP_INV_SALE_PRICE', $bSalePrice)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred when updating the sale price', 'admin-update_class') . '.', 318);
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with feed management settings updated
        $aDisplay = BT_AdminDisplay::create()->run('advancedFeed');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * update feed list settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateFeedList(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            $sDisplay = Tools::getValue('sDisplay');

            // update cron export
            if ($sDisplay == 'data') {
                $aCronExport = Tools::getValue('bt_cron-export');

                if (!Configuration::updateValue('GMCP_CHECK_EXPORT', serialize($aCronExport))) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during export checking', 'admin-update_class') . '.', 400);
                }
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration(array(
            'GMCP_CHECK_EXPORT',
            'GMCP_FEED_TAX',
        ));

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with feed management settings updated
        $aDisplay = BT_AdminDisplay::create()->run('feedList');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * update advanced tag settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateTag(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();
        $aCategoryList = array();

        try {
            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

            /* USE CASE - handle all tags configured */
            foreach ($GLOBALS['GMCP_TAG_LIST'] as $sTagType) {
                if (
                    !empty($aPost[$sTagType])
                    && is_array($aPost[$sTagType])
                ) {
                    if ($sTagType != 'excluded_destination' && $sTagType != 'excluded_country') {
                        foreach ($aPost[$sTagType] as $iCatId => $mVal) {
                            $aCategoryList[$iCatId][$sTagType] = strip_tags($mVal);
                        }
                    } else { // Use for excluded destination this a multiple selectt
                        if ($sTagType == 'excluded_destination') {
                            foreach ($aPost['excluded_destination'] as $iCatId => $mVal) {
                                $aCategoryList[$iCatId][$sTagType] = strip_tags(implode(' ', $mVal));
                            }
                        }

                        if ($sTagType == 'excluded_country') {
                            foreach ($aPost['excluded_country'] as $iCatId => $mVal) {
                                $aCategoryList[$iCatId][$sTagType] = strip_tags(implode(' ', $mVal));
                            }
                        }
                    }
                }
            }

            // delete all features
            BT_GmcProModuleDao::deleteFeatureByCat(GMerchantCenterPro::$iShopId);

            if (!empty($aCategoryList)) {
                foreach ($aCategoryList as $iCatId => $aValues) {
                    BT_GmcProModuleDao::insertFeatureByCat($iCatId, $aValues, GMerchantCenterPro::$iShopId);
                }
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // check update OK
        $aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
        $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ADVANCED_TAG_UPD,
            'assign' => $aAssign,
        );
    }

    /**
     * update custom label settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateLabel(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {
            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');
            require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');
            require_once(_GMCP_PATH_LIB . 'label-tools_class.php');

            // get the label name
            $sLabelName = Tools::getValue('bt_label-name');
            $iTagId = Tools::getValue('bt_tag-id');
            $sLabelType = Tools::getValue('bt_cl-type');
            $bActivateTag = Tools::getValue('bt_cl-statut');
            $sDateEnd = Tools::getValue('bt_cl_date_end');
            $sDateNewProduct = Tools::getValue('bt_cl_dyn_date_start');

            // update attributes and the feature for size tag
            $sProductSpecific = Tools::getValue('hiddenProductIds-cl');

            // get an array of
            $aProductSpecific = !empty($sProductSpecific) ? explode('-', $sProductSpecific) : array();

            //get option for best sales from form
            $sBestSaleType = Tools::getValue('dynamic_best_sales_unit');
            $fBestSaleAmount = Tools::getValue('bt_cl_dyn_amount');
            $sBestSaleStartDate = Tools::getValue('bt_dyn_best_sale_start');
            $sBestSalesEndDate = Tools::getValue('bt_dyn_best_sale_end');

            //get the option for price range option
            $fPriceMin = Tools::getValue('bt_dyn_min_price');
            $fPriceMax = Tools::getValue('bt_dyn_max_price');

            //get data for last ordered product
            $sLastOrderedStart = Tools::getValue('bt_dyn_last_order_start');
            $sLastOrderedEnd = Tools::getValue('bt_dyn_last_order_end');

            $iLastId = (int) BT_GmcProCustomLabelDao::getLastId();
            $iNextId = $iLastId + 1;

            if (empty($sLabelName)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('You haven\'t filled out the label name', 'admin-update_class') . '.', 560);
            } else {
                // USE CASE - The tag is already saved
                if (!empty($iTagId)) {

                    // get the postion save for the tag
                    $iPositionTag = BT_GmcProCustomLabelDao::getTagPosition($iTagId);
                    BT_GmcProCustomLabelDao::updateGmcTag($iTagId, $sLabelName, $sLabelType, $bActivateTag, $iPositionTag, $sDateEnd);

                    // Clean the tag
                    BT_GmcpLabelTools::cleanTag($iTagId, $sLabelType);
                } // use case - create tag
                else {
                    $iTagId = BT_GmcProCustomLabelDao::insertGmcTag(GMerchantCenterPro::$iShopId, $sLabelName, $sLabelType, $bActivateTag, $iNextId, $sDateEnd);
                }

                if ($sLabelType == "custom_label" || $sLabelType == "dynamic_new_product") {
                    BT_GmcpLabelTools::handleDefautTag($iTagId, $sLabelType, $aProductSpecific);
                }

                if ($sLabelType == "dynamic_features_list") {
                    BT_GmcpLabelTools::handleFeatureTag($iTagId, (int) Tools::getValue('dynamic_features_list'));
                }

                if ($sLabelType == "dynamic_categorie") {
                    BT_GmcpLabelTools::handleCatDynmaicTag($iTagId, Tools::getValue('bt_category-box'));
                }

                // USE CASE - Dynamic new product
                if ($sLabelType == "dynamic_new_product") {
                    BT_GmcpLabelTools::handleDynamicNewProduct($iTagId, $sDateNewProduct);
                }

                // USE CASE - Dynamic best sales
                if ($sLabelType == "dynamic_best_sale") {
                    BT_GmcpLabelTools::handleDynamicBestSales($iTagId, $sBestSaleType, $fBestSaleAmount, $sBestSaleStartDate, $sBestSalesEndDate);
                }

                //Use case dynamic price range
                if ($sLabelType == "dynamic_price_range") {
                    BT_GmcpLabelTools::handleDynamicPriceRange($iTagId, $fPriceMin, $fPriceMax);
                }

                // Use case handle last ordered products
                if ($sLabelType == "dynamic_last_order") {
                    BT_GmcpLabelTools::handleDynamicLastOrdered($iTagId, $sLastOrderedStart, $sLastOrderedEnd);
                }

                // Use case handle product in promotion
                if ($sLabelType == "dynamic_promotion") {
                    BT_GmcpLabelTools::handleDynamicPromotion($iTagId, $sLastOrderedStart, $sLastOrderedEnd);
                }
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // check update OK
        $aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
        $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CUSTOM_LABEL_UPD,
            'assign' => $aAssign,
        );
    }

    /**
     * update custom label activation from list
     *
     * @param array $aPost
     * @return array
     */
    private function updateLabelState(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();
        $sDeleteType = Tools::getValue('sDeleteType');
        $iTagId = Tools::getValue('iTagId');
        $aTagIds = Tools::getValue('iTagIds');

        try {
            if (in_array($sDeleteType, array('one', 'bulk'))) {
                // include
                require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');
                require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

                if (
                    $sDeleteType == 'one'
                    && !empty($iTagId)
                ) {
                    BT_GmcProCustomLabelDao::updateTagActivation($iTagId, (int) Tools::getValue('bActive'));
                } elseif (
                    $sDeleteType == 'bulk'
                    && !empty($aTagIds)
                ) {
                    $aIdsDelete = explode(",", $aTagIds);

                    foreach ($aIdsDelete as $aCurrentClId) {
                        BT_GmcProCustomLabelDao::updateTagActivation($aCurrentClId, (int) Tools::getValue('bActive'));
                    }
                }
            } else {
                throw new Exception(GMerchantCenterPro::$oModule->l('Your custom label ID is not valid or some parameters are wrong', 'admin-update_class') . '.', 600);
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
     * update custom label activation from list
     *
     * @param array $aPost
     * @return array
     */
    private function updatePosition(array $aPost)
    {

        GMerchantCenterPro::$sQueryMode = 'xhr';

        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');

        $iTagIdMoveToNewPos = Tools::getValue('iTagIdMoveToNewPos');
        $iNewPosition = Tools::getValue('iNewPosition');
        $iTagIdMoveToOldPos = Tools::getValue('iTagIdMoveToOldPos');
        $iOldPosition = Tools::getValue('iOldPosition');

        // update the new tag position
        BT_GmcProCustomLabelDao::updatePositionTag($iTagIdMoveToNewPos, $iNewPosition);
        // update the old tag position
        BT_GmcProCustomLabelDao::updatePositionTag($iTagIdMoveToOldPos, $iOldPosition);

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
     * update custom label date when check or data feed is generated
     *
     * @param array $aPost
     * @return array
     */
    private function updateCustomLabelDate(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB_DAO . 'custom-label-dao_class.php');
        require_once(_GMCP_PATH_LIB . 'module-tools_class.php');

        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        $sDateToday = date("Y-m-d");;

        //get all tag information id and date
        $aTags = BT_GmcProCustomLabelDao::getTagDate(GMerchantCenterPro::$iShopId);

        // make the process for each tag with date
        foreach ($aTags as $aTag) {
            $iDateCompare = BT_GmcProModuleTools::dateCompare($sDateToday, (string) $aTag['end_date']);
            $iPositionTag = BT_GmcProCustomLabelDao::getTagPosition((int) $aTag['id_tag']);

            // made update tag statut if date is over
            if ($iDateCompare == 1) {
                //update tag statut
                BT_GmcProCustomLabelDao::updateProcessDate((int) $aTag['id_tag'], 0, $iPositionTag['position']);
            }
        }

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
     * update google settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateGoogle(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            // add google UTM campaign
            $sUtmCampaign = Tools::getValue('bt_utm-campaign');
            if (!Configuration::updateValue('GMCP_UTM_CAMPAIGN', $sUtmCampaign)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during utm campaign update', 'admin-update_class') . '.', 700);
            }

            // add google UTM source
            $sUtmSource = Tools::getValue('bt_utm-source');
            if (!Configuration::updateValue('GMCP_UTM_SOURCE', $sUtmSource)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during utm source update', 'admin-update_class') . '.', 701);
            }

            // add google UTM medium
            $sUtmMedium = Tools::getValue('bt_utm-medium');
            if (!Configuration::updateValue('GMCP_UTM_MEDIUM', $sUtmMedium)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during utm medium update', 'admin-update_class') . '.', 702);
            }

            // add google UTM content
            $sUtmContent = Tools::getValue('bt_utm_content');
            if (!Configuration::updateValue('GMCP_UTM_CONTENT', $sUtmContent)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during utm content update', 'admin-update_class') . '.', 703);
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration(array('GMCP_COLOR_OPT', 'GMCP_SIZE_OPT', 'GMCP_SHIP_CARRIERS'));

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with feed management settings updated
        $aDisplay = BT_AdminDisplay::create()->run('google');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }

    /**
     * update google categories matching
     *
     * @param array $aPost
     * @return array
     */
    private function updateGoogleCategoriesMatching(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {
            $iLangId = Tools::getValue('iLangId');
            $sLangIso = Tools::getValue('sLangIso');
            $aGoogleCategory = Tools::getValue('bt_google-cat');

            if (
                empty($sLangIso)
                || !Language::getIsoById((int) $iLangId)
            ) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Invalid language parameters', 'admin-update_class') . '.', 800);
            }
            if (!is_array($aGoogleCategory)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Your matching Google categories is not a valid array', 'admin-update_class') . '.', 801);
            }
            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

            // delete previous google matching categories
            if (BT_GmcProModuleDao::deleteGoogleCategory(GMerchantCenterPro::$iShopId, $sLangIso)) {
                foreach ($aGoogleCategory as $iShopCatId => $sGoogleCat) {
                    if (!empty($sGoogleCat)) {
                        // insert each category
                        BT_GmcProModuleDao::insertGoogleCategory(GMerchantCenterPro::$iShopId, $iShopCatId, $sGoogleCat, $sLangIso);
                    }
                }
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // check update OK
        $aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
        $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CATEGORY_UPD,
            'assign' => $aAssign,
        );
    }

    /**
     * update reporting settings
     *
     * @param array $aPost
     * @return array
     */
    private function updateReporting(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            // register reporting mode
            $bReporting = Tools::getValue('bt_reporting');
            if (!Configuration::updateValue('GMCP_REPORTING', $bReporting)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during reporting update', 'admin-update_class') . '.', 900);
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with feed management settings updated
        $aDisplay = BT_AdminDisplay::create()->run('reporting');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        return $aDisplay;
    }


    /**
     * update the google categories by sync action
     *
     * @throws
     * @param array $aPost
     * @return array
     */
    private function updateGoogleCategoriesSync(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {
            // include
            require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

            $sLangIso = Tools::getValue('sLangIso');

            if ($sLangIso != false) {
                // Get and check content is here
                $sContent = BT_GmcProModuleTools::getGoogleFile(_GMCP_GOOGLE_TAXONOMY_URL . 'taxonomy.' . $sLangIso . '.txt');

                // use case - the Google file content is KO
                if (!$sContent || Tools::strlen($sContent) == 0) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('An error occurred during the Google file get content', 'admin-update_class') . '.', 1000);
                } else {
                    // Convert to array and check all is still OK
                    $aLines = explode("\n", trim($sContent));

                    // use case - wrong format
                    if (!$aLines || !is_array($aLines)) {
                        throw new Exception(GMerchantCenterPro::$oModule->l('The Google taxonomy file content is not formatted well', 'admin-update_class') . '.', 1001);
                    } else {
                        // Delete past data
                        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'gmcp_taxonomy` WHERE `lang` = "' . pSQL($sLangIso) . '"');

                        // Re-insert
                        foreach ($aLines as $index => $sLine) {
                            // First line is the version number, so skip it
                            if ($index > 0) {
                                $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . 'gmcp_taxonomy` (`value`, `lang`) VALUES ("' . pSQL($sLine) . '", "' . pSQL($sLangIso) . '")';
                                Db::getInstance()->Execute($sQuery);
                            }
                        }
                    }
                }
                $aAssign['aCountryTaxonomies'] = BT_GmcProModuleDao::getAvailableTaxonomyCountries($GLOBALS['GMCP_AVAILABLE_COUNTRIES']);

                foreach ($aAssign['aCountryTaxonomies'] as $sIsoCode => &$aTaxonomy) {
                    $aTaxonomy['countryList'] = implode(', ', $aTaxonomy['countries']);
                    $aTaxonomy['currentUpdated'] = $sLangIso == $sIsoCode ? true : false;
                    $aTaxonomy['updated'] = BT_GmcProModuleDao::checkTaxonomyUpdate($sIsoCode);
                }
            } else {
                throw new Exception(GMerchantCenterPro::$oModule->l('The server has returned an unsecure request error (wrong parameters)!', 'admin-update_class') . '.', 1002);
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // check update OK
        $aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
        $aAssign['sURI'] = BT_GmcProModuleTools::truncateUri(array('&sAction'));
        $aAssign['sCtrlParamName'] = _GMCP_PARAM_CTRL_NAME;
        $aAssign['sController'] = _GMCP_ADMIN_CTRL;
        $aAssign['aQueryParams'] = $GLOBALS['GMCP_REQUEST_PARAMS'];
        $aAssign['iCurrentLang'] = intval(GMerchantCenterPro::$iCurrentLang);
        $aAssign['sCurrentLang'] = GMerchantCenterPro::$sCurrentLang;

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_GOOGLE_CATEGORY_LIST,
            'assign' => $aAssign,
        );
    }

    /**
     * update the XML file
     *
     * @param array $aPost
     * @return array
     */
    private function updateXml(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {
            $iShopId = Tools::getValue('iShopId');
            $sFilename = Tools::getValue('sFilename');
            $iLangId = Tools::getValue('iLangId');
            $sLangIso = Tools::getValue('sLangIso');
            $sCountryIso = Tools::getValue('sCountryIso');
            $iFloor = Tools::getValue('iFloor');
            $iTotal = Tools::getValue('iTotal');
            $iProcess = Tools::getValue('iProcess');
            $sDataFeedType = Tools::getValue('feed_type');

            if (($iShopId != false && is_numeric($iShopId))
                && ($sFilename != false && is_string($sFilename))
                && ($iLangId != false && is_numeric($iLangId))
                && ($sLangIso != false && is_string($sLangIso))
                && ($sCountryIso != false && is_string($sCountryIso))
                && ($iFloor !== false && is_numeric($iFloor))
                && ($iTotal != false && is_numeric($iTotal))
                && ($iProcess !== false && is_numeric($iProcess))
            ) {
                $_POST['iShopId'] = $iShopId;
                $_POST['sFilename'] = $sFilename;
                $_POST['iLangId'] = $iLangId;
                $_POST['sLangIso'] = $sLangIso;
                $_POST['sCountryIso'] = Tools::strtoupper($sCountryIso);
                $_POST['iFloor'] = $iFloor;
                $_POST['iStep'] = GMerchantCenterPro::$conf['GMCP_AJAX_CYCLE'];
                $_POST['iTotal'] = $iTotal;
                $_POST['iProcess'] = $iProcess;
                $_POST['feed_type'] = $sDataFeedType;

                // require admin configure class - to factorise
                require_once(_GMCP_PATH_LIB_ADMIN . 'admin-generate_class.php');

                // exec the generate class to generate the XML files
                $aGenerate = BT_AdminGenerate::create()->run('xml', array('reporting' => GMerchantCenterPro::$conf['GMCP_REPORTING']));

                if (empty($aGenerate['assign']['aErrors'])) {
                    $aAssign['status'] = 'ok';
                    $aAssign['counter'] = $iFloor + $_POST['iStep'];
                    $aAssign['process'] = $aGenerate['assign']['process'];
                } else {
                    $aAssign['status'] = 'ko';
                    $aAssign['error'] = $aGenerate['assign']['aErrors'];
                }
            } else {
                $sMsg = GMerchantCenterPro::$oModule->l(
                    'The server has returned an unsecure request error (wrong parameters)! Please check each parameter by comparing type and value below!',
                    'admin-update_class'
                ) . '.' . "<br/>";
                $sMsg .= GMerchantCenterPro::$oModule->l('Shop ID', 'admin-update_class') . ': ' . $iShopId . "<br/>" . GMerchantCenterPro::$oModule->l('File name', 'admin-update_class') . ': ' . $sFilename . "<br/>"
                    . GMerchantCenterPro::$oModule->l('Language ID', 'admin-update_class') . ': ' . $iLangId . "<br/>"
                    . GMerchantCenterPro::$oModule->l('Language ISO', 'admin-update_class') . ': ' . $sLangIso . "<br/>" . GMerchantCenterPro::$oModule->l('country ISO', 'admin-update_class') . ': ' . $sCountryIso . "<br/>"
                    . GMerchantCenterPro::$oModule->l('Step', 'admin-update_class') . ': ' . $iFloor . "<br/>" . GMerchantCenterPro::$oModule->l('Total products to process', 'admin-update_class') . ': ' . $iTotal . "<br/>"
                    . GMerchantCenterPro::$oModule->l('Total products to process (without counting combinations)', 'admin-update_class') . ': ' . $iTotal . "<br/>"
                    . GMerchantCenterPro::$oModule->l('Stock the real number of products to process', 'admin-update_class') . ': ' . $iProcess . "<br/>";

                throw new Exception($sMsg, 594);
            }
        } catch (Exception $e) {
            $aAssign['status'] = 'ko';
            $aAssign['error'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_GENERATE,
            'assign' => array('json' => BT_GmcProModuleTools::jsonEncode($aAssign)),
        );
    }

    /**
     * method update the exclusion rules
     *
     * @param array $aPost
     * @return array
     * @throws
     */
    private function updateExclusionRule(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aAssign = array();

        try {

            //To use DAO for rules management
            require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');
            //To use the Exclusion tools
            require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-tools_class.php');

            //get the form parameters general
            $bActive = Tools::getValue('bt_excl-rule-active');
            $sExclusionName = Tools::getValue('bt-exclusion-name');
            $sExclusionType = Tools::getValue('bt-exclusion-type');
            $iExclusionId = Tools::getValue('bt-exclusion-id');

            // get the form exclusion around the word
            $sExclusionWordType = Tools::getValue('bt-exclusion-word-type');
            $sExclusionWord = Tools::getValue('word-exclusion-value');

            //get the exclusion around feature and atrribute
            $sExclusionFeature = Tools::getValue('bt-exclusion-feature');
            $sExclusionFeatureValue = Tools::getValue('bt-feature-value');
            $sExclusionAttribute = Tools::getValue('bt-exclusion-attribute');
            $sExclusionAttributeValue = Tools::getValue('bt-attribute-value');

            $sExclusionCategories = Tools::getValue('bt_category-box');
            $sExclusionManufacturer = Tools::getValue('bt_brand-box');
            $sExclusionSupplier = Tools::getValue('bt_supplier-box');

            $sProductSpecificExclusion = Tools::getValue('hiddenProductIds');

            //Use case to build the exlusion rule when it is a word type
            if ($sExclusionType == 'word') {
                if (!empty($sExclusionWordType) && !empty($sExclusionWord)) {
                    $aRulevalue = array(
                        'exclusionOn' => $sExclusionWordType,
                        'exclusionData' => $sExclusionWord,
                    );
                }
            } elseif ($sExclusionType == 'feature') {
                $aRulevalue = array(
                    'exclusionOn' => $sExclusionFeature,
                    'exclusionData' => $sExclusionFeatureValue,
                );
            } elseif ($sExclusionType == 'attribute') {
                $aRulevalue = array(
                    'exclusionOn' => $sExclusionAttribute,
                    'exclusionData' => $sExclusionAttributeValue,
                );
            } elseif ($sExclusionType == 'specificProduct') {
                $aRulevalue = array(
                    'exclusionOn' => '',
                    'exclusionData' => $sProductSpecificExclusion,
                );
            } elseif ($sExclusionType == 'category') {

                $aRulevalue = array(
                    'exclusionOn' => '',
                    'exclusionData' => $sExclusionCategories,
                );
            } elseif ($sExclusionType == 'manufacturer') {
                $aRulevalue = array(
                    'exclusionOn' => '',
                    'exclusionData' => $sExclusionManufacturer,
                );
            } elseif ($sExclusionType == 'supplier') {
                $aRulevalue = array(
                    'exclusionOn' => '',
                    'exclusionData' => $sExclusionSupplier,
                );
            }

            //Use case to manage the product ids according to the rules values
            $aRulevalue['aProductIds'] = BT_GmcProExclusionTools::getProductFromRules();
            $aRuleDetails = BT_GmcProExclusionDao::getTmpRules();

            //Stock the rules preferences
            foreach ($aRuleDetails as $aRuleDetail) {
                $aRulevalue['aRulesDetail'][] = $aRuleDetail['exclusion_values'];
            }
            $sExclusionValue = serialize($aRulevalue);

            //use case for add rules
            if (empty($iExclusionId)) {
                if (!BT_GmcProExclusionDao::addExclusionRule($bActive, GMerchantCenterPro::$iShopId, $sExclusionName, $sExclusionType, $sExclusionValue)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding the rule', 'admin-update_class') . '.', 1100);
                } else {
                    // Use case for the product exclusion tab according to the option active
                    if (!empty($bActive)) {
                        $aLastRule = BT_GmcProExclusionDao::getLastRuleId();
                        foreach ($aRulevalue['aProductIds'] as $aProductData) {
                            if (!empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                                if (!BT_GmcProExclusionDao::addProductExcluded($aLastRule['last_id'], $aProductData['id_product'], $aProductData['id_product_attribute'])) {
                                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding the rule', 'admin-update_class') . '.', 1101);
                                }
                            } else {
                                if (!BT_GmcProExclusionDao::addProductExcluded($aLastRule['last_id'], $aProductData, 0)) {
                                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding the rule', 'admin-update_class') . '.', 1102);
                                }
                            }
                        }
                    }
                }
            } else {
                if (!BT_GmcProExclusionDao::updateExclusionRule($bActive, GMerchantCenterPro::$iShopId, $sExclusionName, $sExclusionType, $sExclusionValue, $iExclusionId)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while updating the rule', 'admin-update_class') . '.', 1103);
                } else {
                    // Use case for the product exclusion tab according to the option active
                    if (empty($bActive)) {
                        if (!BT_GmcProExclusionDao::deleteProductExcluded($iExclusionId)) {
                            throw new Exception(GMerchantCenterPro::$oModule->l('Error while deleting product exclusions', 'admin-update_class') . '.', 1104);
                        }
                    } else {
                        foreach ($aRulevalue['aProductIds'] as $aProductData) {
                            if (!empty(GMerchantCenterpro::$conf['GMCP_P_COMBOS'])) {
                                if (!BT_GmcProExclusionDao::addProductExcluded($iExclusionId, $aProductData['id_product'], $aProductData['id_product_attribute'])) {
                                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding product exclusions', 'admin-update_class') . '.', 1105);
                                }
                            } else {
                                if (!BT_GmcProExclusionDao::addProductExcluded($iExclusionId, $aProductData, 0)) {
                                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding product exclusions', 'admin-update_class') . '.', 1106);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $aAssign['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // check update OK
        $aAssign['bUpdate'] = empty($aAssign['aErrors']) ? true : false;
        $aAssign['sErrorInclude'] = BT_GmcProModuleTools::getTemplatePath(_GMCP_PATH_TPL_NAME . _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_ERROR);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return (array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_EXCLUSION_RULES_UPD,
            'assign' => $aAssign,
        ));
    }

    /**
     * method update the rule list
     *
     * @param array $aPost
     * @return array
     */
    private function updateRulesList(array $aPost)
    {
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-tools_class.php');
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            $iRuleId = Tools::getValue('iRuleId');
            $sType = Tools::getValue('sUpdateType');
            $bActivate = Tools::getValue('bActivate');

            if (empty($iRuleId) || empty($sType)) {
                throw new Exception(GMerchantCenterPro::$oModule->l('The rule ID or the update type is missing', 'admin-update_class') . '.', 1200);
            } else {
                // include


                if (!BT_GmcProExclusionDao::updateRulesStatus($iRuleId, $sType, $bActivate)) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Error while updating the rule status', 'admin-update_class') . '.', 1201);
                } else {
                    if (!empty($bActivate)) {
                        $aProducts = BT_GmcProExclusionTools::getProductFromRules();
                        foreach ($aProducts as $aProductData) {
                            if (!BT_GmcProExclusionDao::addProductExcluded($iRuleId, $aProductData['id_product'], $aProductData['id_product_attribute'])) {
                                throw new Exception(GMerchantCenterPro::$oModule->l('Error while adding product exclusions', 'admin-update_class') . '.', 1202);
                            }
                        }
                    } else {
                        if (!BT_GmcProExclusionDao::deleteProductExcluded($iRuleId)) {
                            throw new Exception(GMerchantCenterPro::$oModule->l('Error while deleting product exclusions', 'admin-update_class') . '.', 1203);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // force xhr mode
        //        GMerchantCenterPro::$sQueryMode = 'xhr';

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('feed');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        // destruct
        unset($aData);

        return $aDisplay;
    }

    /**
     * check and update lang of multi-language fields
     *
     * @param array $aPost : params
     * @param string $sFieldName : field name linked to the translation value
     * @param string $sGlobalName : name of GLOBAL variable to get value
     * @param bool $bCheckOnly
     * @param bool $bNeedAllVal
     * @param string $sErrorDisplayName
     * @return array
     */
    private function updateLang(array $aPost, $sFieldName, $sGlobalName, $bCheckOnly = false, $sErrorDisplayName = '', $bNeedAllVal = true)
    {
        // check title in each active language
        $aLangs = array();

        foreach (Language::getLanguages() as $nKey => $aLang) {
            if (empty($aPost[$sFieldName . '_' . $aLang['id_lang']]) && !empty($bNeedAllVal)) {
                $sException = GMerchantCenterPro::$oModule->l('One title of', 'admin-update_class') . ' " ' . (!empty($sErrorDisplayName) ? $sErrorDisplayName : $sFieldName) . ' " ' . GMerchantCenterPro::$oModule->l('have not been filled', 'admin-update_class') . '.';
                throw new Exception($sException, 1300);
            } else {
                $aLangs[$aLang['id_lang']] = strip_tags($aPost[$sFieldName . '_' . $aLang['id_lang']]);
            }
        }
        if (!$bCheckOnly) {
            // update titles
            if (!Configuration::updateValue($sGlobalName, serialize($aLangs))) {
                $sException = GMerchantCenterPro::$oModule->l('An error occurred during', 'admin-update_class') . ' " ' . $sGlobalName . ' " ' . GMerchantCenterPro::$oModule->l('update', 'admin-update_class') . '.';
                throw new Exception($sException, 1301);
            }
        }
        return $aLangs;
    }

    /**
     * update custom label product association durong the data feed update
     * @param array $aPost
     * @return array
     */
    private function updateCustomCheck(array $aPost = null)
    {
        require_once(_GMCP_PATH_LIB . 'label-tools_class.php');
        BT_GmcpLabelTools::updateCustomLabelFeedProcess();
    }

    /**
     * update iventory settings 
     *
     * @param array $aPost
     * @return array
     */
    private function updateInventory(array $aPost)
    {
        // clean headers
        @ob_end_clean();

        // set
        $aData = array();

        try {
            // Update configuration values
            Configuration::updateValue('GMCP_STORE_CODE', Tools::getValue('bt_store_code'));
            Configuration::updateValue('GMCP_LIA_PICKUP', Tools::getValue('bt_lia_pickup'));
            Configuration::updateValue('GMCP_LIA_PICKUP_SLA', Tools::getValue('bt_lia_pickup_sla'));


        } catch (Exception $e) {
            $aData['aErrors'][] = array('msg' => $e->getMessage(), 'code' => $e->getCode());
        }

        // get configuration options
        BT_GmcProModuleTools::getConfiguration();

        // require admin configure class - to factorise
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-display_class.php');

        // get run of admin display in order to display first page of admin with basics settings updated
        $aDisplay = BT_AdminDisplay::create()->run('inventory');

        // use case - empty error and updating status
        $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
            'bUpdate' => (empty($aData['aErrors']) ? true : false),
        ), $aData);

        // force xhr mode
        GMerchantCenterPro::$sQueryMode = 'xhr';

        return $aDisplay;
    }

    /**
     * create() method set singleton
     *
     * @category admin collection
     * @param
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
