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

require_once('base-xml_class.php');

abstract class BT_BaseProductStrategy extends BT_BaseXml
{
    /**
     * @var array $aParamsForXml : array for all parameters provided to generate XMl files
     */
    protected static $aParamsForXml = array();

    /**
     * @var string $sType : stock the strategy type
     */
    protected $sType = '';

    /**
     * @var string $sContent : store the XML content
     */
    public $sContent = '';

    /**
     * @var array $aParams : array of params
     */
    public $aParams = array();

    /**
     * @var int $iCounter : count the number of product processed
     */
    public $iCounter = 0;

    /**
     * @var obj $oCurrentProd : store the current obj to handle
     */
    protected $oCurrentProd = null;

    /**
     * @var bool $bExport : define the export mode
     */
    protected $bExport = null;

    /**
     * @var obj $data : store currency / shipping / zone / carrier
     */
    public $data = null;


    /**
     *
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
        require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');

        $this->data = new stdClass();
        $this->sContent = '';
        $this->aParams = $aParams;
        $this->iCounter = 0;
        $this->bExport = isset($aParams['bExport']) ? $aParams['bExport'] : 0;
        $this->bOutput = isset($aParams['bOutput']) ? $aParams['bOutput'] : 0;

        if (!empty($aParams['type'])) {
            $this->sType = $aParams['type'];
        }
    }

    /**
     * store into the matching object the product and combination
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     * @return array
     */
    abstract public function setProductData(&$oData, $oProduct, $aCombination);


    /**
     * construct the XML content
     *
     * @param obj $oData
     * @param obj $oProduct
     * @param array $aCombination
     */
    abstract public function buildProductXml($oData, $oProduct, $aCombination);


    /**
     * load Products for XML
     *
     * bool $bExportCombination
     * bool $bExcludedProduct
     * @return array
     */
    public function loadProduct($bExportCombination = false, $bExcludedProduct = false)
    {
        // get currency ISO
        $sCurrencyIso = $GLOBALS['GMCP_AVAILABLE_COUNTRIES'][$this->aParams['sLangIso']][$this->aParams['sCountryIso']]['currency'];

        // set different vars required to calculate some things
        $this->data->currencyId = Currency::getIdByIsoCode(Tools::strtolower($this->aParams['sCurrencyIso']));
        $this->data->currency = new stdClass();
        $this->data->currency = new Currency($this->data->currencyId);

        // store the current carrier
        $this->data->currentCarrier = new stdClass();
        if (!empty(GMerchantCenterPro::$conf['GMCP_SHIP_CARRIERS'][Tools::strtoupper($this->aParams['sCountryIso'])])) {
            $this->data->currentCarrier = new Carrier((int) GMerchantCenterPro::$conf['GMCP_SHIP_CARRIERS'][Tools::strtoupper($this->aParams['sCountryIso'])]);
        }
        $this->data->countryId = Country::getByIso($this->aParams['sCountryIso']);
        $this->data->currentZone = new stdClass();
        $this->data->currentZone = new Zone((int) Country::getIdZone((int) $this->data->countryId));
        $this->data->shippingConfig = Configuration::getMultiple(array('PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_FREE_WEIGHT', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD'));

        Context::getContext()->currency = new Currency((int) $this->data->currencyId);
        Context::getContext()->cookie->id_country = $this->data->countryId;
        Context::getContext()->cookie->id_currency = $this->data->currencyId;

        return BT_GmcProModuleDao::getProductIds($this->aParams['iShopId'], $this->bExport, false, $this->aParams['iFloor'], $this->aParams['iStep'], $bExportCombination, $bExcludedProduct);
    }

    /**
     * generate get the XML for current data feed type
     */
    public function setParams(array $aParams)
    {
        $this->aParams = $aParams;
        $this->bExport = isset($aParams['bExport']) ? $aParams['bExport'] : 0;
        $this->bOutput = isset($aParams['bOutput']) ? $aParams['bOutput'] : 0;
    }


    /**
     * check if combinations and return them
     *
     * @param int $iProdId
     * @param bool $bExcludedProduct
     * @return bool
     */
    public function hasCombination($iProdId, $bExcludedProduct)
    {
        // check if combinations
        return $this->oCurrentProd->hasCombination($iProdId, $bExcludedProduct);
    }

    /**
     * the number of products processed
     *
     * @return int
     */
    public function getProcessedProduct()
    {
        return (int) $this->iCounter;
    }

    /**
     * generate get the XML for current data feed type
     *
     * @params array $aParams
     * @return array
     */
    public function generate(array $aParams = null)
    {
        // include
        require_once(_GMCP_PATH_LIB . 'module-reporting_class.php');
        require_once(_GMCP_PATH_LIB_EXCLUSION . 'exclusion-dao_class.php');
        require_once(_GMCP_PATH_LIB_DAO . 'module-dao_class.php');
        require_once(_GMCP_PATH_LIB_COMMON . 'file.class.php');

        // set
        $aAssign = array();

        if (empty(self::$aParamsForXml)) {
            self::$aParamsForXml = $GLOBALS['GMCP_PARAM_FOR_XML'];
        }

        try {
            foreach (self::$aParamsForXml as $sParamName) {
                $mValue = Tools::getValue($sParamName);
                if ($mValue !== false) {
                    $$sParamName = $mValue;
                } else {
                    throw new Exception(GMerchantCenterPro::$oModule->l('One or more of the required parameters are not provided, please check the list in the current class', 'base-product-strategy_class') . '.', 800);
                }
            }

            // detect if we force the reporting or not
            $bForceReporting = !empty($aParams['reporting']) ? $aParams['reporting'] : false;
            $bForceReporting = ($bForceReporting !== false) ? $bForceReporting : GMerchantCenterPro::$conf['GMCP_REPORTING'];

            $aFreeShippingProducts = array();

            if (!empty(GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD'])) {
                if (is_string(GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD'])) {
                    GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD'] = unserialize(GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD']);
                }
                foreach (GMerchantCenterPro::$conf['GMCP_FREE_SHIP_PROD'] as $sProdIds) {
                    list($iProdId, $iAttrId) = explode('¤', $sProdIds);
                    $aFreeShippingProducts[$iProdId][] = $iAttrId;
                }
            }
            // set params
            $aParams = array(
                'bExport' => GMerchantCenterPro::$conf['GMCP_EXPORT_MODE'],
                'iShopId' => (int) $iShopId,
                'iLangId' => (int) $iLangId,
                'sLangIso' => $sLangIso,
                'sCountryIso' => $sCountryIso,
                'sGmcLink' => GMerchantCenterPro::$conf['GMCP_LINK'],
                'sCurrencyIso' => $sCurrencyIso,
                'iFloor' => (int) $iFloor,
                'iStep' => (int) $iStep,
                'iTotal' => (int) $iTotal,
                'iProcess' => (int) $iProcess,
                'bOutput' => Tools::getValue('bOutput'),
                'sType' => $sFeedType,
                'sFreeShipping' => $aFreeShippingProducts,
                'bUseTax' => BT_GmcProModuleTools::isTax($sLangIso, $sCountryIso)
            );

            // get the XMl strategy
            $this->setParams($aParams);

            // composition of File Obj into XMlStrategy
            $this->setFile(BT_File::create());

            // check if reporting is activated
            BT_GmcProReporting::create($bForceReporting)->setFileName(_GMCP_REPORTING_DIR . 'reporting-' . $sLangIso . '-' . Tools::strtolower($sCountryIso) . '-' . $sCurrencyIso . '-' . $this->sType . '.txt');

            // detect if this is the first step
            if ((int) $iFloor == 0) {
                // reset the reporting file
                BT_GmcProReporting::create()->writeFile('', 'w');

                // reset the XMl file
                $this->write(_GMCP_SHOP_PATH_ROOT . $sFilename, '');

                // create header
                $this->header($aParams);
            }

            // load products
            $aProducts = $this->loadProduct(GMerchantCenterPro::$conf['GMCP_P_COMBOS'], $bExcludedProduct);

            foreach ($aProducts as $aProduct) {
                // get the instance of the product
                $oProduct = new Product((int) ($aProduct['id']), true, (int) $iLangId);

                // check if validate product
                if (
                    Validate::isLoadedObject($oProduct)
                    && $oProduct->active
                    && ((isset($oProduct->available_for_order)
                        && $oProduct->available_for_order)
                        || empty($oProduct->available_for_order))
                ) {
                    // define the strategy
                    $sXmlProductType = $oProduct->hasAttributes() && !empty(GMerchantCenterPro::$conf['GMCP_P_COMBOS']) ? 'Combination' : 'Product';

                    // set the matching object
                    $this->getProdType($sXmlProductType, $aParams);

                    // check if combinations
                    $aCombinations = $this->hasCombination($oProduct->id, $bExcludedProduct);

                    foreach ($aCombinations as $aCombination) {
                        $this->buildProductXml($this->data, $oProduct, $aCombination);
                    }
                }
            }

            // get the number of products really processed
            $aAssign['process'] = (int) ($iProcess + $this->getProcessedProduct());

            // detect if the last step
            if (((int) $iFloor + (int) $iStep) >= $iTotal) {
                $this->footer($aParams);

                // store the nb of products really processed by the export action
                BT_GmcProReporting::create()->set('counter', array('products' => $aAssign['process']));

                // define the status of the feed generation
                $aAssign['bContinueStatus'] = false;
                $aAssign['bFinishStatus'] = true;
            } else {
                // define the status of the feed generation
                $aAssign['bContinueStatus'] = true;
                $aAssign['bFinishStatus'] = false;
            }

            // write
            $this->write(_GMCP_SHOP_PATH_ROOT . $sFilename, $this->sContent, false, true);

            // merge reporting file's content + current reporting
            $aReporting = BT_GmcProReporting::create()->mergeData();

            // write reporting file by country and currency
            if (!empty($aReporting)) {
                $bWritten = BT_GmcProReporting::create()->writeFile($aReporting, 'w');
            }
        } catch (Exception $e) {
            $aErrorParam = array('msg' => $e->getMessage(), 'code' => $e->getCode());

            if (_GMCP_DEBUG) {
                $aErrorParam['file'] = $e->getFile();
                $aErrorParam['trace'] = $e->getTraceAsString();
            }
            $aAssign['aErrors'][] = $aErrorParam;
        }

        return array(
            'tpl' => _GMCP_TPL_ADMIN_PATH . _GMCP_TPL_FEED_GENERATE_OUTPUT,
            'assign' => $aAssign,
        );
    }

    /**
     * instantiate matched strategy object
     *
     * @throws Exception
     * @param string $sStrategyType
     * @param array $aParams
     * @return obj ctrl type
     */
    public static function get($sStrategyType, array $aParams = null)
    {
        $sStrategyType = strtolower($sStrategyType);
        // if valid controller
        if (file_exists(_GMCP_PATH_LIB_XML . 'xml-' . $sStrategyType . '-strategy_class.php')) {
            // require
            require_once('xml-' . $sStrategyType . '-strategy_class.php');

            // set class name
            $sClassName = 'BT_' . ucfirst($sStrategyType) . 'XmlStrategy';

            try {
                $oReflection = new ReflectionClass($sClassName);

                if ($oReflection->isInstantiable()) {
                    return $oReflection->newInstance($aParams);
                } else {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => object isn\'t instantiable', 'base-product-strategy_class'), 1000);
                }
            } catch (ReflectionException $e) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => invalid object', 'base-product-strategy_class'), 1001);
            }
        } else {
            throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => the object file doesn\'t exist', 'base-product-strategy_class'), 1002);
        }
    }


    /**
     * instantiate matched product object
     *
     * @throws Exception
     * @param string $sProductType
     * @param array $aParams
     * @return obj ctrl type
     */
    public function getProdType($sProductType, array $aParams = null)
    {
        $sProductType = strtolower($sProductType);

        // if valid controller
        if (file_exists(_GMCP_PATH_LIB_XML . 'xml-' . $sProductType . '_class.php')) {
            // require
            require_once('base-product-xml_class.php');
            require_once('xml-' . $sProductType . '_class.php');

            // set class name
            $sClassName = 'BT_Xml' . ucfirst($sProductType);

            try {
                $oReflection = new ReflectionClass($sClassName);

                if ($oReflection->isInstantiable()) {
                    $this->oCurrentProd = $oReflection->newInstance($aParams);
                } else {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => object isn\'t instantiable', 'base-xml_class'), 1000);
                }
            } catch (ReflectionException $e) {
                throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => invalid object', 'base-xml_class'), 1001);
            }
        } else {
            throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => the object file doesn\'t exist', 'base-xml_class'), 1002);
        }
    }
}
