<?php

/**
 * Google Dynamic Remarketing
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 * @version 1.6.5
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

class GRemarketing extends Module
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
        require_once(_PS_MODULE_DIR_ . 'gremarketing/conf/common.conf.php');

        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;

        require_once(_GR_PATH_LIB . 'warnings_class.php');
        require_once(_GR_PATH_LIB . 'module-tools_class.php');

        // get shop id
        self::$iShopId = $this->context->shop->id;
        // get current  lang id
        self::$iCurrentLang = $this->context->cookie->id_lang;
        // get current lang iso
        self::$sCurrentLang = BT_GRModuleTools::getLangIso();
        // get cookie obj
        self::$oCookie = $this->context->cookie;

        $this->name = 'gremarketing';
        $this->module_key = '57351395933b2b431837d356593634a4';
        $this->tab = 'advertising_marketing';
        $this->version = '1.6.5';
        $this->author = 'Business Tech';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Google Dynamic Remarketing');
        $this->description = $this->l('Implement Google Adwords\' Remarketing and Dynamic Remarketing tools, and generate highly dynamic, targeted and animated advertising banners with your products in them');
        $this->confirmUninstall = $this->l('Are you sure you want to remove it ? Your Google Remarketing will no longer work. Be careful, all your configuration and your data will be lost');

        // compare PS version
        self::$bCompare16 = version_compare(_PS_VERSION_, '1.6', '>=');
        self::$bCompare17 = version_compare(_PS_VERSION_, '1.7', '>=');

        // stock itself obj
        self::$oModule = $this;

        if (!empty(self::$bCompare17) || !empty(self::$bCompare17)) {
            $this->bootstrap = true;
        }

        // update module version
        $GLOBALS['GR_CONFIGURATION']['GR_MODULE_VERSION'] = $this->version;

        // set base of URI
        self::$sBASE_URI = $this->_path;

        // get configuration options
        BT_GRModuleTools::getConfiguration();

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
        require_once(_GR_PATH_CONF . 'install.conf.php');
        require_once(_GR_PATH_LIB_INSTALL . 'install-ctrl_class.php');

        // set return
        $bReturn = true;

        if (
            !parent::install()
            || !BT_InstallCtrl::run('install', 'config')
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
        require_once(_GR_PATH_CONF . 'install.conf.php');
        require_once(_GR_PATH_LIB_INSTALL . 'install-ctrl_class.php');

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
        require_once(_GR_PATH_CONF . 'admin.conf.php');
        require_once(_GR_PATH_LIB_ADMIN . 'admin-ctrl_class.php');

        // set
        $aUpdateModule = array();

        try {
            // update new module keys
            BT_GRModuleTools::updateConfiguration();

            // get configuration options
            BT_GRModuleTools::getConfiguration();

            // set js msg translation
            BT_GRModuleTools::translateJsMsg();

            // instantiate admin controller object
            $oAdmin = new BT_AdminCtrl();

            // defines type to execute
            // use case : no key sAction sent in POST mode (no form has been posted => first page is displayed with admin-display.class.php)
            // use case : key sAction sent in POST mode (form or ajax query posted ).
            $sAction = (!Tools::getIsset('sAction') || (Tools::getIsset('sAction') && 'display' == Tools::getValue('sAction'))) ? (Tools::getIsset('sAction') ? Tools::getValue('sAction') : 'display') : Tools::getValue('sAction');

            // make module update only in case of display general admin page
            if ($sAction == 'display' && !Tools::getIsset('sType')) {
                // update module if necessary
                $aUpdateModule = $this->updateModule();
            }

            // execute good action in admin
            // only displayed with key : tpl and assign in order to display matching smarty template
            $aDisplay = $oAdmin->run($sAction, array_merge($_GET, $_POST));

            if (!empty($aDisplay)) {
                $aDisplay['assign'] = array_merge($aDisplay['assign'], array(
                    'aUpdateErrors' => $aUpdateModule,
                    'oJsTranslatedMsg' => BT_GRModuleTools::jsonEncode($GLOBALS['GR_JS_MSG']),
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
     * displays customized module content on footer
     *
     * @return string
     */
    public function hookDisplayHeader()
    {
        return $this->execHook('display', 'header');
    }


    /**
     * displays selected hook content
     *
     * @param string $sHookType
     * @param array $aParams
     * @return string
     */
    private function execHook($sHookType, $sAction, array $aParams = null)
    {
        // include
        require_once(_GR_PATH_CONF . 'hook.conf.php');
        require_once(_GR_PATH_LIB_HOOK . 'hook-ctrl_class.php');

        try {
            // define which hook class is executed in order to display good content in good zone in shop
            $oHook = new BT_GRHookCtrl($sHookType, $sAction);

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
            $this->aErrors[] = array('msg' => $e->getMessage(), 'code' => $e->getCode());

            return $this->displayErrorModule();
        }
    }


    /**
     * displays view
     *
     * @throws Exception
     * @param string $sTplName
     * @param array $aAssign
     * @return string html
     */
    public function displayModule($sTplName, $aAssign)
    {
        if (file_exists(_GR_PATH_TPL . $sTplName) && is_file(_GR_PATH_TPL . $sTplName)) {
            // set assign module name
            $aAssign = array_merge($aAssign, array(
                'sModuleName' => Tools::strtolower(_GR_MODULE_NAME),
                'bDebug' => _GR_DEBUG,
            ));

            $this->smarty->assign($aAssign);

            return $this->display(__FILE__, _GR_PATH_TPL_NAME . $sTplName);
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
        $this->smarty->assign(
            array(
                'sHomeURI' => BT_GRModuleTools::truncateUri(),
                'aErrors' => $this->aErrors,
                'sModuleName' => Tools::strtolower(_GR_MODULE_NAME),
                'bDebug' => _GR_DEBUG,
            )
        );

        return $this->display(__FILE__, _GR_PATH_TPL_NAME . _GR_TPL_ERROR);
    }

    /**
     * updates module as necessary
     *
     * @return array
     */
    private function updateModule()
    {
        require(_GR_PATH_LIB . 'module-update_class.php');

        // check if update templates
        BT_GRModuleUpdate::create()->run(array('sType' => 'configuration'));

        // check if update tables
        BT_GRModuleUpdate::create()->run(array('sType' => 'tables'));

        // check if update fields
        BT_GRModuleUpdate::create()->run(array('sType' => 'fields'));

        // check if update hooks
        BT_GRModuleUpdate::create()->run(array('sType' => 'hooks'));

        // check if update templates
        BT_GRModuleUpdate::create()->run(array('sType' => 'templates'));

        return BT_GRModuleUpdate::create()->aErrors;
    }
}
