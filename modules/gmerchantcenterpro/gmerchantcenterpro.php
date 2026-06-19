<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2021 - https://www.businesstech.fr
 * @license   Commercial
 * @version 1.7.17
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

if (!defined('_PS_VERSION_')) {
    exit(1);
}

class GMerchantCenterPro extends Module
{
    /**
     * @var array $conf : array of set configuration
     */
    public static $conf = array();

    /**
     * @var int $iCurrentLang : store id of default lang
     */
    public static $iCurrentLang = null;

    /**
     * @var int $sCurrentLang : store iso of default lang
     */
    public static $sCurrentLang = null;

    /**
     * @var obj $oCookie : store cookie obj
     */
    public static $oCookie = null;

    /**
     * @var obj $oModule : obj module itself
     */
    public static $oModule = array();

    /**
     * @var string $sQueryMode : query mode - detect XHR
     */
    public static $sQueryMode = null;

    /**
     * @var string $sBASE_URI : base of URI in prestashop
     */
    public static $sBASE_URI = null;

    /**
     * @var string $sHost : store the current domain
     */
    public static $sHost = '';

    /**
     * @var int $iShopId : shop id used for 1.5 and for multi shop
     */
    public static $iShopId = 1;

    /**
     * @var bool $bCompare1550 : get compare version for PS 1.5.5.0
     */
    public static $bCompare1550 = false;

    /**
     * @var bool $bCompare16 : get compare version for PS 1.6
     */
    public static $bCompare16 = false;

    /**
     * @var bool $bCompare1606 : get compare version for PS 1.6
     */
    public static $bCompare1606 = false;

    /**
     * @var bool $bCompare1608 : get compare version for PS 1.6
     */
    public static $bCompare1608 = false;

    /**
     * @var bool $bCompare16013 : get compare version for PS 1.6
     */
    public static $bCompare16013 = false;

    /**
     * @var bool $bCompare17 : get compare version for PS 1.7
     */
    public static $bCompare17 = false;

    /**
     * @var bool $bCompare1730 : get compare version for PS 1.7.3.0
     */
    public static $bCompare1730 = false;

     /**
     * @var bool $bCompare1770 : get compare version for PS 1.7.7.0
     */
    public static $bCompare1770 = false;

    /**
     * @var obj $oContext : get context object
     */
    public static $oContext;

    /**
     * @var array $aAvailableLanguages : store the available languages
     */
    public static $aAvailableLanguages = array();

    /**
     * @var bool $bAdvancedPack : check advanced pack module installation
     */
    public static $bAdvancedPack = false;

    /**
     * @var array $aAvailableLangCurrencyCountry : store the available related languages / countries / currencies
     */
    public static $aAvailableLangCurrencyCountry = array();

    /**
     * @var string $sFilePrefix : store the XML file's prefix
     */
    public static $sFilePrefix = '';


    /**
     * @var array $aErrors : array get error
     */
    public $aErrors = null;


    /**
     * assigns few information about module and instantiate parent class
     */
    public function __construct()
    {
        require_once(_PS_MODULE_DIR_ . 'gmerchantcenterpro/conf/common.conf.php');
        require_once(_GMCP_PATH_LIB . 'module-tools_class.php');

        $this->name = 'gmerchantcenterpro';
        $this->module_key = '742dd70356f9527ea97f65dd7e3c2c41';
        $this->tab = 'seo';
        $this->version = '1.7.17';
        $this->author = 'Business Tech';

        parent::__construct();

        $this->displayName = $this->l('Google Merchant Center PRO (Google Shopping)');
        $this->description = $this->l('The PRO version of Google Merchant Center: even more control on product data, export of reviews and special offers.');
        $this->confirmUninstall = $this->l('Are you sure you want to remove Google Merchant Center ?');

        // compare PS version
        self::$bCompare1550 = version_compare(_PS_VERSION_, '1.5.5.0', '>=');
        self::$bCompare16 = version_compare(_PS_VERSION_, '1.6', '>=');
        self::$bCompare1606 = version_compare(_PS_VERSION_, '1.6.0.6', '>=');
        self::$bCompare1608 = version_compare(_PS_VERSION_, '1.6.0.8', '>=');
        self::$bCompare16013 = version_compare(_PS_VERSION_, '1.6.0.13', '>=');
        self::$bCompare17 = version_compare(_PS_VERSION_, '1.7.0.0', '>=');
        self::$bCompare1730 = version_compare(_PS_VERSION_, '1.7.3.0', '>=');
        self::$bCompare1770 = version_compare(_PS_VERSION_, '1.7.7.0', '>=');
        self::$bAdvancedPack = BT_GmcProModuleTools::isInstalled('pm_advancedpack');

        self::$oContext = $this->context;
        // get shop id
        self::$iShopId = self::$oContext->shop->id;

        // get cookie obj
        self::$oCookie = $this->context->cookie;

        // get current  lang id
        self::$iCurrentLang = self::$oContext->cookie->id_lang;

        // get current lang iso
        self::$sCurrentLang = BT_GmcProModuleTools::getLangIso();

        // stock itself obj
        self::$oModule = $this;

        //set bootstrap
        if (
            !empty(self::$bCompare16)
            || !empty(self::$bCompare17)
        ) {
            $this->bootstrap = true;
        }

        // set base of URI
        self::$sBASE_URI = $this->_path;
        self::$sHost = BT_GmcProModuleTools::setHost();

        // get configuration options
        BT_GmcProModuleTools::getConfiguration(array('GMCP_COLOR_OPT','GMCP_SIZE_OPT','GMCP_SHIP_CARRIERS','GMCP_CHECK_EXPORT','GMCP_CHECK_EXPORT_STOCK','GMCP_FEED_TAX',));

        // get available languages
        self::$aAvailableLanguages = BT_GmcProModuleTools::getAvailableLanguages(self::$iShopId);

        // get available languages / currencies / countries
        self::$aAvailableLangCurrencyCountry = BT_GmcProModuleTools::getLangCurrencyCountry(self::$aAvailableLanguages, $GLOBALS['GMCP_AVAILABLE_COUNTRIES']);

        // get call mode - Ajax or dynamic - used for clean headers and footer in ajax request
        self::$sQueryMode = Tools::getValue('sMode');
    }

    /**
     * installs all mandatory structure (DB or Files) => sql queries and update values and hooks registered
     *
     * @return bool
     */
    public function install()
    {
        require_once(_GMCP_PATH_CONF . 'install.conf.php');
        require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // set return
        $bReturn = true;

        if (
            !parent::install()
            || !BT_InstallCtrl::run('install', 'sql', _GMCP_PATH_SQL . _GMCP_INSTALL_SQL_FILE)
            || !BT_InstallCtrl::run('install', 'config', array('bConfigOnly' => true))
        ) {
            $bReturn = false;
        }

        return $bReturn;
    }

    /**
     * uninstalls all mandatory structure (DB or Files)
     *
     * @return bool
     */
    public function uninstall()
    {
        require_once(_GMCP_PATH_CONF . 'install.conf.php');
        require_once(_GMCP_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // set return
        $bReturn = true;

        if (
            !parent::uninstall()
            || !BT_InstallCtrl::run('uninstall', 'config')
        ) {
            $bReturn = false;
        }

        return $bReturn;
    }

    /**
     * manages all data in Back Office
     *
     * @return string
     */
    public function getContent()
    {
        require_once(_GMCP_PATH_CONF . 'admin.conf.php');
        require_once(_GMCP_PATH_CONF . 'google.conf.php');
        require_once(_GMCP_PATH_LIB_ADMIN . 'base-ctrl_class.php');
        require_once(_GMCP_PATH_LIB_ADMIN . 'admin-ctrl_class.php');

        try {
            // transverse execution
            self::$sFilePrefix = BT_GmcProModuleTools::setXmlFilePrefix();

            // get controller type
            $sControllerType = (!Tools::getIsset(_GMCP_PARAM_CTRL_NAME) || (Tools::getIsset(_GMCP_PARAM_CTRL_NAME) && 'admin' == Tools::getValue(_GMCP_PARAM_CTRL_NAME))) ? (Tools::getIsset(_GMCP_PARAM_CTRL_NAME) ? Tools::getValue(_GMCP_PARAM_CTRL_NAME) : 'admin') : Tools::getValue(_GMCP_PARAM_CTRL_NAME);

            // instantiate matched controller object
            $oCtrl = BT_GmcBaseCtrl::get($sControllerType);

            // execute good action in admin
            // only displayed with key : tpl and assign in order to display good smarty template
            $aDisplay = $oCtrl->run(array_merge($_GET, $_POST));

            if (!empty($aDisplay)) {
                $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
                    'oJsTranslatedMsg' => BT_GmcProModuleTools::jsonEncode($GLOBALS['GMCP_JS_MSG']),
                    'bAddJsCss' => true
                ));

                // get content
                $sContent = $this->displayModule($aDisplay['tpl'], $aDisplay['assign']);

                if (!empty(self::$sQueryMode)) {
                    echo $sContent;
                } else {
                    return $sContent;
                }
            } else {
                throw new Exception('action returns empty content', 110);
            }
        } catch (Exception $e) {
            $this->aErrors[] = array('msg' => $e->getMessage(), 'code' => $e->getCode());

            // get content
            $sContent = $this->displayErrorModule();

            if (!empty(self::$sQueryMode)) {
                echo $sContent;
            } else {
                return $sContent;
            }
        }
        // exit clean with XHR mode
        if (!empty(self::$sQueryMode)) {
            exit(0);
        }
    }

    /**
     * executes hook module route for old url redirect
     *
     * @param array $aParams
     * @return string
     */
    public function hookModuleRoutes(array $aParams)
    {
        $sMyRoute = array(
            'module-gmerchantcenterpro-fly' => array(
                'controller' => 'fly',
                'rule' => 'modules/gmerchantcenterpro/fly.php',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'gmerchantcenterpro',
                    'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'],
                ),
            ),
            'module-gmerchantcenterpro-cron' => array(
                'controller' => 'cron',
                'rule' => 'modules/gmerchantcenterpro/cron.php',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'gmerchantcenterpro',
                    'token' => GMerchantCenterPro::$conf['GMCP_FEED_TOKEN'],
                ),
            )
        );

        return $sMyRoute;

    }

    /**
     * displays selected hook content
     *
     * @param string $sHookType
     * @param string $sAction
     * @param array $aParams
     * @return string
     */
    private function execHook($sHookType, $sAction, array $aParams = null)
    {
        // include
        require_once(_GMCP_PATH_CONF . 'hook.conf.php');
        require_once(_GMCP_PATH_LIB_HOOK . 'hook-ctrl_class.php');

        // set
        $aDisplay = array();

        try {
            // use cache or not
            if (
                !empty($aParams['cache'])
                && !empty($aParams['template'])
                && !empty($aParams['cacheId'])
            ) {
                $bUseCache = !$this->isCached($aParams['template'], $this->getCacheId($aParams['cacheId'])) ? false : true;

                if ($bUseCache) {
                    $aDisplay['tpl'] = $aParams['template'];
                    $aDisplay['assign'] = array();
                }
            } else {
                $bUseCache = false;
            }

            // detect cache or not
            if (!$bUseCache) {
                // define which hook class is executed in order to display good content in good zone in shop
                $oHook = new BT_GmcProHookCtrl($sHookType, $sAction);

                // displays good block content
                $aDisplay = $oHook->run($aParams);
            }

            // execute good action in admin
            // only displayed with key : tpl and assign in order to display good smarty template
            if (!empty($aDisplay)) {
                return $this->displayModule($aDisplay['tpl'], $aDisplay['assign'], $bUseCache, (!empty($aParams['cacheId']) ? $aParams['cacheId'] : null));
            } else {
                throw new Exception('Chosen hook returned empty content', 110);
            }
        } catch (Exception $e) {
            $this->aErrors[] = array('msg' => $e->getMessage(), 'code' => $e->getCode());

            return $this->displayErrorModule();
        }
    }


    /**
     * manages module error
     *
     * @param string $sTplName
     * @param array $aAssign
     */
    public function setErrorHandler($iErrno, $sErrstr, $sErrFile, $iErrLine, $aErrContext)
    {
        switch ($iErrno) {
            case E_USER_ERROR:
                $this->aErrors[] = array(
                    'msg' => 'Fatal error <b>' . $sErrstr . '</b>',
                    'code' => $iErrno,
                    'file' => $sErrFile,
                    'line' => $iErrLine,
                    'context' => $aErrContext
                );
                break;
            case E_USER_WARNING:
                $this->aErrors[] = array(
                    'msg' => 'Warning <b>' . $sErrstr . '</b>',
                    'code' => $iErrno,
                    'file' => $sErrFile,
                    'line' => $iErrLine,
                    'context' => $aErrContext
                );
                break;
            case E_USER_NOTICE:
                $this->aErrors[] = array(
                    'msg' => 'Notice <b>' . $sErrstr . '</b>',
                    'code' => $iErrno,
                    'file' => $sErrFile,
                    'line' => $iErrLine,
                    'context' => $aErrContext
                );
                break;
            default:
                $this->aErrors[] = array(
                    'msg' => 'Unknow error <b>' . $sErrstr . '</b>',
                    'code' => $iErrno,
                    'file' => $sErrFile,
                    'line' => $iErrLine,
                    'context' => $aErrContext
                );
                break;
        }
        return ($this->displayErrorModule());
    }

    /**
     * displays views
     *
     * @throws Exception
     * @param string $sTplName
     * @param array $aAssign
     * @param bool $bUseCache
     * @param int $iICacheId
     * @return string html
     */
    public function displayModule($sTplName, $aAssign, $bUseCache = false, $iICacheId = null)
    {
        if (file_exists(_GMCP_PATH_TPL . $sTplName) && is_file(_GMCP_PATH_TPL . $sTplName)) {
            $aAssign = array_merge(
                $aAssign,
                array('sModuleName' => Tools::strtolower(_GMCP_MODULE_NAME), 'bDebug' => _GMCP_DEBUG)
            );

            // use cache
            if (!empty($bUseCache) && !empty($iICacheId)) {
                return $this->display(__FILE__, $sTplName, $this->getCacheId($iICacheId));
            } // not use cache
            else {
                self::$oContext->smarty->assign($aAssign);
                return $this->display(__FILE__, _GMCP_PATH_TPL_NAME . $sTplName);
            }
        } else {
            throw new Exception('Template "' . $sTplName . '" doesn\'t exists', 120);
        }
    }

    /**
     * displays view with error
     *
     * @param string $sTplName
     * @param array $aAssign
     * @return string html
     */
    public function displayErrorModule()
    {
        self::$oContext->smarty->assign(
            array(
                'sHomeURI' => BT_GmcProModuleTools::truncateUri(),
                'aErrors' => $this->aErrors,
                'sModuleName' => Tools::strtolower(_GMCP_MODULE_NAME),
                'bDebug' => _GMCP_DEBUG,
            )
        );

        return $this->display(__FILE__, _GMCP_PATH_TPL_NAME . _GMCP_TPL_HOOK_PATH . _GMCP_TPL_ERROR);
    }

    /**
     * updates module as necessary
     * @return array
     */
    public function updateModule()
    {
        BT_GmcProWarning::create()->run('module', 'gmerchantcenter', array(), true);

        require(_GMCP_PATH_LIB . 'module-update_class.php');

        // check if update tables
        BT_GmcProModuleUpdate::create()->run('tables');

        // check if update fields
        BT_GmcProModuleUpdate::create()->run('fields');

        // check if update templates
        BT_GmcProModuleUpdate::create()->run('templates');

        // check if update hook
        BT_GmcProModuleUpdate::create()->run('hooks');

        // check if the module is updated
        BT_GmcProModuleUpdate::create()->run('module_update');

        // check if update some configuration options
        BT_GmcProModuleUpdate::create()->run('configuration', array('languages'));
        BT_GmcProModuleUpdate::create()->run('configuration', array('color'));
        BT_GmcProModuleUpdate::create()->run('configuration', array('size'));

        //check if update from GMC module
        //BT_GmcProModuleUpdate::create()->run('fromGmc');

        $aErrors = BT_GmcProModuleUpdate::create()->getErrors();

        // initialize XML files
        BT_GmcProModuleUpdate::create()->run(
            'xmlFiles',
            array('aAvailableData' => GMerchantCenterPro::$aAvailableLangCurrencyCountry)
        );
        $aErrors = BT_GmcProModuleUpdate::create()->getErrors();

        // initialize XML files
        BT_GmcProModuleUpdate::create()->run('xmlFiles', array('aAvailableData' => GMerchantCenterPro::$aAvailableLangCurrencyCountry));

        if (
            empty($aErrors)
            && BT_GmcProModuleUpdate::create()->getErrors()
        ) {
            BT_GmcProWarning::create()->bStopExecution = true;
        }

        return BT_GmcProModuleUpdate::create()->getErrors();
    }
}
