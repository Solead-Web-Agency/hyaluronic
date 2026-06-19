<?php
/**
 * module-tools_class.php file defines all tools method in module - transverse
 */

class BT_GactModuleTools
{
    /**
     * detectCurrentPage() method returns current page type
     */
    public static function detectCurrentPage()
    {
        $sCurrentTypePage = '';

        // use case - home page
        if ((version_compare(_PS_VERSION_, '1.5', '>')
            && Tools::getValue('controller') == 'index')
            || (version_compare(_PS_VERSION_, '1.5', '<')
            && strstr($_SERVER['SCRIPT_NAME'], '/index.php'))
        ) {
            $sCurrentTypePage = 'home';

        } elseif ((version_compare(_PS_VERSION_, '1.5', '>')
             && Tools::getValue('controller') == 'search')
            || (version_compare(_PS_VERSION_, '1.5', '<')
            && strstr($_SERVER['SCRIPT_NAME'], '/search.php'))
        ) {
            // use case - search results page
            $sCurrentTypePage = 'search';

        }
        elseif ((version_compare(_PS_VERSION_, '1.5', '>')
            && Tools::getValue('controller') == 'order'
            && Tools::getValue('step') == false)
            || (version_compare(_PS_VERSION_, '1.5', '<')
            && strstr($_SERVER['SCRIPT_NAME'], '/order.php')
            && Tools::getValue('step') == false)
        ) {
            // use case - order page
            $sCurrentTypePage = 'cart';

        } elseif ((version_compare(_PS_VERSION_, '1.5', '>')
            && Tools::getValue('controller') == 'orderconfirmation'
            && Tools::getValue('id_order') != false)
            || (version_compare(_PS_VERSION_, '1.5', '<')
            && strstr($_SERVER['SCRIPT_NAME'], '/order-confirmation.php'))
        ) {
            // use case - order confirmation page
            $sCurrentTypePage = 'purchase';

        } elseif (Tools::getvalue('id_category')) {
            // use case - category page
            $sCurrentTypePage = 'category';

        } elseif (Tools::getvalue('id_product')) {
            // use case - product page
            $sCurrentTypePage = 'product';

        } else {
            // other
            $sCurrentTypePage = 'other';
        }

        return $sCurrentTypePage;
    }


    /**
     * returns good translated errorsn
     */
    public static function translateJsMsg()
    {
        $GLOBALS['GACT_JS_MSG']['conversionid'] = GAdwordsTracking::$oModule->l('You have not filled out a numeric for your Google conversion ID option', 'module-tools_class');
        $GLOBALS['GACT_JS_MSG']['conversionlabel'] = GAdwordsTracking::$oModule->l('You have not filled out your Google Conversion Label', 'module-tools_class');
    }


    /**
     * update new keys in new module version
     */
    public static function updateConfiguration()
    {
        // check to update new module version
        foreach ($GLOBALS['GACT_CONFIGURATION'] as $sKey => $mVal) {
            // use case - not exists
            if (Configuration::get($sKey) === false) {
                // update key/ value
                Configuration::updateValue($sKey, $mVal);
            }
        }
    }

    /**
     * set all constant module in ps_configuration
     *
     * @param int $iShopId
     */
    public static function getConfiguration($iShopId = null)
    {
        // get configuration options
        if (null !== $iShopId && is_numeric($iShopId)) {
            GAdwordsTracking::$conf = Configuration::getMultiple(array_keys($GLOBALS['GACT_CONFIGURATION']), null, null, $iShopId);
        } else {
            GAdwordsTracking::$conf = Configuration::getMultiple(array_keys($GLOBALS['GACT_CONFIGURATION']));
        }
    }

    /**
     * set good iso lang
     *
     * @return string
     */
    public static function getLangIso($iLangId = null)
    {
        if (null === $iLangId) {
            $iLangId = GAdwordsTracking::$iCurrentLang;
        }

        // get iso lang
        $sIsoLang = Language::getIsoById($iLangId);

        if (false === $sIsoLang) {
            $sIsoLang = 'en';
        }

        return $sIsoLang;
    }

    /**
     * return Lang id from iso code
     *
     * @param string $sIsoCode
     *
     * @return int
     */
    public static function getLangId($sIsoCode, $iDefaultId = null)
    {
        // get iso lang
        $iLangId = Language::getIdByIso($sIsoCode);

        if (empty($iLangId) && $iDefaultId !== null) {
            $iLangId = $iDefaultId;
        }

        return $iLangId;
    }

    /**
     * returns current currency sign or id
     *
     * @param string $sField      : field name has to be returned
     * @param string $iCurrencyId : currency id
     * @return mixed : string or array
     */
    public static function getCurrency($sField = null, $iCurrencyId = null)
    {
        // set
        $mCurrency = null;

        // get currency id
        if (null === $iCurrencyId) {
            $iCurrencyId = Configuration::get('PS_CURRENCY_DEFAULT');
        }

        $aCurrency = Currency::getCurrency($iCurrencyId);

        if ($sField !== null) {
            switch ($sField) {
                case 'id_currency' :
                    $mCurrency = $aCurrency['id_currency'];
                    break;
                case 'name' :
                    $mCurrency = $aCurrency['name'];
                    break;
                case 'iso_code' :
                    $mCurrency = $aCurrency['iso_code'];
                    break;
                case 'iso_code_num' :
                    $mCurrency = $aCurrency['iso_code_num'];
                    break;
                case 'sign' :
                    $mCurrency = $aCurrency['sign'];
                    break;
                case 'conversion_rate' :
                    $mCurrency = $aCurrency['conversion_rate'];
                    break;
                case 'format' :
                    $mCurrency = $aCurrency['format'];
                    break;
                default:
                    $mCurrency = $aCurrency;
                    break;
            }
        }

        return $mCurrency;
    }


    /**
     * returns formatted URI for page name type
     *
     * @return mixed
     */
    public static function getPageName()
    {
        $sScriptName = '';

        // use case - script name filled
        if (!empty($_SERVER['SCRIPT_NAME'])) {
            $sScriptName = $_SERVER['SCRIPT_NAME'];
        } // use case - php_self filled
        elseif ($_SERVER['PHP_SELF']) {
            $sScriptName = $_SERVER['PHP_SELF'];
        } // use case - default script name
        else {
            $sScriptName = 'index.php';
        }

        return substr(basename($sScriptName), 0, strpos(basename($sScriptName), '.'));
    }

    /**
     * returns price by considering the merchant option in the back office
     *
     * @param array $aParams
     * @param bool  $bUseTax
     * @param bool  $bUseShippings
     * @param bool  $bUseWrapping
     * @return float
     */
    public static function getOrderPrice(
        $aParams,
        $bUseTax,
        $bUseShipping,
        $bUseWrapping
    ) {
        $fOderAmount = 0.0;

        if (!empty($aParams)) {
            //case with tax
            if (!empty($bUseTax)) {
                if (!empty($bUseShipping) && ! empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_paid;
                } elseif (empty($bUseShipping) && ! empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products_wt
                                   + $aParams->total_wrapping_tax_incl;
                } elseif (!empty($bUseShipping) && empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products_wt
                                   + $aParams->total_shipping_tax_incl;
                } elseif (empty($bUseShipping) && empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products_wt;
                }
            } //case without tax
            elseif (empty($bUseTax)) {
                if (!empty($bUseShipping) && ! empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_paid_tax_excl;
                } elseif (empty($bUseShipping) && ! empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products
                                   + $aParams->total_wrapping_tax_excl;
                } elseif (!empty($bUseShipping) && empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products
                                   + $aParams->total_shipping_tax_excl;
                } elseif (empty($bUseShipping) && empty($bUseWrapping)) {
                    $fOderAmount = $aParams->total_products;
                }
            }
        }

        return $fOderAmount;
    }


    /**
     * returns template path
     *
     * @param string $sTemplate
     * @return string
     */
    public static function getTemplatePath($sTemplate)
    {
        // set
        $mTemplatePath = null;

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $mTemplatePath = GAdwordsTracking::$oModule->getTemplatePath($sTemplate);
        } else {
            if (file_exists(_PS_THEME_DIR_ . 'modules/' . GAdwordsTracking::$oModule->name . '/' . $sTemplate)) {
                $mTemplatePath = _PS_THEME_DIR_ . 'modules/' . GAdwordsTracking::$oModule->name . '/' . $sTemplate;
            } elseif (file_exists(_PS_MODULE_DIR_ . GAdwordsTracking::$oModule->name . '/' . $sTemplate)) {
                $mTemplatePath = _PS_MODULE_DIR_ . GAdwordsTracking::$oModule->name . '/' . $sTemplate;
            }
        }

        return $mTemplatePath;
    }


    /**
     * detects and returns available URI - resolve Prestashop compatibility
     *
     * @param string $sURI
     * @return mixed
     */
    public static function detectHttpUri($sURI)
    {
        // use case - only with relative URI
        if (!strstr($sURI, 'http')) {
            $sURI = 'http' . (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $sURI;
        }

        return $sURI;
    }

    /**
     * truncate current request_uri in order to delete params : sAction and sType
     *
     * @param mixed: string or array $mNeedle
     * @return mixed
     */
    public static function truncateUri($mNeedle = '&sAction')
    {
        // set tmp
        $aQuery = is_array($mNeedle) ? $mNeedle : array($mNeedle);

        // get URI
        $sURI = $_SERVER['REQUEST_URI'];

        foreach ($aQuery as $sNeedle) {
            $sURI = strstr($sURI, $sNeedle) ? substr($sURI, 0,
                strpos($sURI, $sNeedle)) : $sURI;
        }

        return $sURI;
    }

    /**
     * detects available method and apply json encode
     *
     * @param array $aData
     * @return string
     */
    public static function jsonEncode($aData)
    {
        if (function_exists('json_encode')) {
            $aData = json_encode($aData);
        } elseif (method_exists('Tools', 'jsonEncode')) {
            $aData = Tools::jsonEncode($aData);
        } else {
            if (is_null($aData)) {
                return 'null';
            }
            if ($aData === false) {
                return 'false';
            }
            if ($aData === true) {
                return 'true';
            }
            if (is_scalar($aData)) {
                $aData = addslashes($aData);
                $aData = str_replace("\n", '\n', $aData);
                $aData = str_replace("\r", '\r', $aData);
                $aData = preg_replace('{(</)(script)}i', "$1'+'$2", $aData);

                return "'$aData'";
            }
            $isList = true;
            for ($i = 0, reset($aData); $i < count($aData); $i++, next($aData))
            {
                if (key($aData) !== $i) {
                    $isList = false;
                    break;
                }
            }
            $result = array();

            if ($isList) {
                foreach ($aData as $v) {
                    $result[] = self::json_encode($v);
                }
                $aData = '[ ' . join(', ', $result) . ' ]';
            } else {
                foreach ($aData as $k => $v) {
                    $result[] = self::json_encode($k) . ': '
                                . self::json_encode($v);
                }
                $aData = '{ ' . join(', ', $result) . ' }';
            }
        }

        return $aData;
    }

    /**
     * detects available method and apply json decode
     *
     * @param array $aData
     *
     * @return mixed
     */
    public static function jsonDecode($aData)
    {
        if (function_exists('json_decode')) {
            $aData = json_decode($aData);
        } elseif (method_exists('Tools', 'jsonDecode')) {
            $aData = Tools::jsonDecode($aData);
        }

        return $aData;
    }

    /**
     * isInstalled() method check if specific module and module's vars are available
     *
     * @param int   $sModuleName
     * @param array $aCheckedVars
     * @param bool  $bObjReturn
     * @param bool  $bOnlyInstalled
     * @return mixed : true or false or obj
     */
    public static function isInstalled(
        $sModuleName,
        array $aCheckedVars = array(),
        $bObjReturn = false,
        $bOnlyInstalled = false
    ) {
        $mReturn = false;

        // use case - check module is installed in DB
        if (Module::isInstalled($sModuleName)) {
            if (!$bOnlyInstalled) {
                $oModule = Module::getInstanceByName($sModuleName);

                if (!empty($oModule)) {
                    // check if module is activated
                    $aActivated = Db::getInstance()->ExecuteS('SELECT id_module as id, active FROM ' . _DB_PREFIX_ . 'module WHERE name = "' . pSQL($sModuleName) . '" AND active = 1');

                    if (!empty($aActivated[0]['active'])) {
                        $mReturn = true;

                        if (version_compare(_PS_VERSION_, '1.5', '>')) {
                            $aActivated = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'module_shop WHERE id_module = ' . pSQL($aActivated[0]['id']) . ' AND id_shop = ' . Context::getContext()->shop->id);

                            if (empty($aActivated)) {
                                $mReturn = false;
                            }
                        }

                        if ($mReturn) {
                            if (!empty($aCheckedVars)) {
                                foreach ($aCheckedVars as $sVarName) {
                                    $mVar = Configuration::get($sVarName);

                                    if (empty($mVar)) {
                                        $mReturn = false;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($mReturn && $bObjReturn) {
                    $mReturn = $oModule;
                }
            } else {
                $mReturn = true;
            }
        }

        return $mReturn;
    }


    /**
     * round on numeric
     *
     * @param float $fVal
     * @param int   $iPrecision
     * @return float
     */
    public static function round($fVal, $iPrecision = 2)
    {
        if (method_exists('Tools', 'ps_round')) {
            $fVal = Tools::ps_round($fVal, $iPrecision);
        } else {
            $fVal = round($fVal, $iPrecision);
        }

        return $fVal;
    }
}
