<?php
/**
 * gadwordstracking.php file defines main class of module
 *
 * @author    Business Tech SARL <http://www.businesstech.fr/en/contact-us>
 * @copyright 2003-2018 Business Tech SARL
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @version   2.2.9
 * @date      10/12/2018
 */

if ( ! defined('_PS_VERSION_')) {
    exit(1);
}

class GAdwordsTracking extends Module
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
     * @var array $aErrors : array get error
     */
    public $aErrors = null;

    /**
     * @var int $iShopId : shop id used for 1.5 and for multi shop
     */
    public static $iShopId = 1;

    /**
     * @var bool $bCompare16 : get compare version for PS 1.6
     */
    public static $bCompare16 = false;

    /**
     * @var bool $bCompare17 : get compare version for PS 1.7
     */
    public static $bCompare17 = false;

    /**
     * Magic Method __construct assigns few information about module and instantiate parent class
     */
    public function __construct()
    {
        require_once(dirname(__FILE__) . '/conf/common.conf.php');
        require_once(_GACT_PATH_LIB . 'module-tools_class.php');

        // get shop id
        self::$iShopId = Context::getContext()->shop->id;
        // get current  lang id
        self::$iCurrentLang = Context::getContext()->cookie->id_lang;
        // get current lang iso
        self::$sCurrentLang = BT_GactModuleTools::getLangIso();
        // get cookie obj
        self::$oCookie = Context::getContext()->cookie;

        $this->name = 'gadwordstracking';
        $this->module_key = '645f13ea66f496469e10014a2ee80ce1';
        $this->tab = 'advertising_marketing';
        $this->version = '2.2.9';
        $this->author = 'Business Tech';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Google Adwords Conversion Tracking');
        $this->description = $this->l('Implement Google Adwords\' Conversion Tracking tool, and make sure your orders / conversions get correctly sent to Google Adwords, so you can have a clear view on campaigns\' profitability');
        $this->confirmUninstall = $this->l('Are you sure you want to remove it ? Your Google Adwords Conversion Tracking will no longer work. Be careful, all your configuration and your data will be lost');

        // compare PS version
        self::$bCompare16 = version_compare(_PS_VERSION_, '1.6', '>=');
        self::$bCompare17 = version_compare(_PS_VERSION_, '1.7', '>=');

        if (!empty(self::$bCompare17)
            || self::$bCompare16
        ) {
            $this->bootstrap = true;
        }

        // stock itself obj
        self::$oModule = $this;

        // update module version
        $GLOBALS['GACT_CONFIGURATION']['GACT_MODULE_VERSION'] = $this->version;

        // set base of URI
        self::$sBASE_URI = $this->_path;

        // get configuration options
        BT_GactModuleTools::getConfiguration();

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
        require_once(_GACT_PATH_CONF . 'install.conf.php');
        require_once(_GACT_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // set return
        $bReturn = true;

        if ( ! parent::install()
             || ! BT_InstallCtrl::run('install', 'config')
             || ! BT_InstallCtrl::run('install', 'sql', _GACT_PATH_SQL . _GACT_INSTALL_SQL_FILE)
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
        require_once(_GACT_PATH_CONF . 'install.conf.php');
        require_once(_GACT_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // set return
        $bReturn = true;

        if ( ! parent::uninstall()
             || ! BT_InstallCtrl::run('uninstall', 'config')
             || ! BT_InstallCtrl::run('uninstall', 'sql', _GACT_PATH_SQL . _GACT_UNINSTALL_SQL_FILE)
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
        require_once(_GACT_PATH_CONF . 'admin.conf.php');
        require_once(_GACT_PATH_LIB_ADMIN . 'admin-ctrl_class.php');
        require_once(_GACT_PATH_LIB . 'warning_class.php');

        // set
        $aUpdateModule = array();

        try {
            // update new module keys
            BT_GactModuleTools::updateConfiguration();

            // get configuration options
            BT_GactModuleTools::getConfiguration();

            // set js msg translation
            BT_GactModuleTools::translateJsMsg();

            // instantiate admin controller object
            $oAdmin = new BT_AdminCtrl();

            // defines type to execute
            // use case : no key sAction sent in POST mode (no form has been posted => first page is displayed with admin-display.class.php)
            // use case : key sAction sent in POST mode (form or ajax query posted ).
            $sAction = ( ! Tools::getIsset('sAction') || (Tools::getIsset('sAction') && 'display' == Tools::getValue('sAction'))) ? (Tools::getIsset('sAction') ? Tools::getValue('sAction') : 'display') : Tools::getValue('sAction');

            // make module update only in case of display general admin page
            if ($sAction == 'display' && ! Tools::getIsset('sType')) {
                // update module if necessary
                $aUpdateModule = $this->updateModule();
            }

            // execute good action in admin
            // only displayed with key : tpl and assign in order to display good smarty template
            $aDisplay = $oAdmin->run($sAction, array_merge($_GET, $_POST));

            if (!empty($aDisplay)) {
                $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
                    'aUpdateErrors'    => $aUpdateModule,
                    'oJsTranslatedMsg' => BT_GactModuleTools::jsonEncode($GLOBALS['GACT_JS_MSG']),
                    'bAddJsCss'        => true,
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
            $this->aErrors[] = array(
                'msg'  => $e->getMessage(),
                'code' => $e->getCode(),
            );

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
     * displays header and google tag
     *
     * @return string
     */
    public function hookDisplayHeader()
    {
        return $this->execHook('display', 'header');
    }

    /**
     * hookDisplayFooter() method displays customized module content on footer
     *
     * @return string
     */
    public function hookActionValidateOrder($aData)
    {
        return (
        $this->execHook('action', 'validateOrder', $aData)
        );
    }

    /**
     * displays selected hook content
     *
     * @param string $sHookType
     * @param array  $aParams
     *
     * @return string
     */
    private function execHook($sHookType, $sAction, array $aParams = null)
    {
        // include
        require_once(_GACT_PATH_CONF . 'hook.conf.php');
        require_once(_GACT_PATH_LIB_HOOK . 'hook-ctrl_class.php');

        try {
            // define which hook class is executed in order to display good content in good zone in shop
            $oHook = new BT_GactHookCtrl($sHookType, $sAction);

            // displays good block content
            $aDisplay = $oHook->run($aParams);

            // execute good action in admin
            // only displayed with key : tpl and assign in order to display good smarty template
            if (!empty($aDisplay)) {
                return $this->displayModule($aDisplay['tpl'], $aDisplay['assign']);
            } else {
                throw new Exception('Chosen hook returned empty content', 110);
            }
        } catch (Exception $e) {
            $this->aErrors[] = array(
                'msg'  => $e->getMessage(),
                'code' => $e->getCode(),
            );

            return $this->displayErrorModule();
        }
    }



    /**
     * displays view
     *
     * @param string $sTplName
     * @param array  $aAssign
     *
     * @return string html
     */
    public function displayModule($sTplName, $aAssign)
    {
        if (file_exists(_GACT_PATH_TPL . $sTplName)
            && is_file(_GACT_PATH_TPL . $sTplName)
        ) {
            // set assign module name
            $aAssign = array_merge($aAssign, array(
                'sModuleName' => Tools::strtolower(_GACT_MODULE_NAME),
                'bDebug'      => _GACT_DEBUG,
            ));

            $this->context->smarty->assign($aAssign);

            return $this->display(__FILE__, _GACT_PATH_TPL_NAME . $sTplName);
        } else {
            throw new Exception('Template "' . $sTplName . '" doesn\'t exists', 120);
        }
    }

    /**
     * displays view with error
     *
     * @param string $sTplName
     * @param array  $aAssign
     *
     * @return string html
     */
    public function displayErrorModule()
    {
        $this->context->smarty->assign(
            array(
                'sHomeURI'    => BT_GactModuleTools::truncateUri(),
                'aErrors'     => $this->aErrors,
                'sModuleName' => Tools::strtolower(_GACT_MODULE_NAME),
                'bDebug'      => _GACT_DEBUG,
            )
        );

        return $this->display(__FILE__, _GACT_PATH_TPL_NAME . _GACT_TPL_ERROR);
    }

    /**
     * updates module as necessary
     *
     * @return array
     */
    private function updateModule()
    {
        require(_GACT_PATH_LIB . 'module-update_class.php');

        // check if update tables
        BT_GactModuleUpdate::create()->run(array('sType' => 'tables'));

        // check if update fields
        BT_GactModuleUpdate::create()->run(array('sType' => 'fields'));

        // check if update hooks
        BT_GactModuleUpdate::create()->run(array('sType' => 'hooks'));

        // check if update templates
        BT_GactModuleUpdate::create()->run(array('sType' => 'templates'));

        return (BT_GactModuleUpdate::create()->aErrors);
    }
}
