<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;
if (!defined('_ETS_AC_IMG_DIR_')) {
    define('_ETS_AC_IMG_DIR_', _PS_IMG_DIR_ . 'ets_abandonedcart');
}
if (!defined('_ETS_AC_MAIL_UPLOAD_DIR_')) {
    define('_ETS_AC_MAIL_UPLOAD_DIR_', _PS_IMG_DIR_ . 'ets_abandonedcart/mails');
}
if (!defined('_ETS_AC_MAIL_UPLOAD_')) {
    define('_ETS_AC_MAIL_UPLOAD_', _PS_IMG_ . 'ets_abandonedcart/mails');
}
require_once(dirname(__FILE__) . '/classes/EtsAbancartCore.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartTools.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartMail.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartIndex.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartIndexCustomer.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartCampaign.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartReminder.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartEmailTemplate.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartTracking.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartDisplayTracking.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartReminderForm.php');

require_once(dirname(__FILE__) . '/classes/EtsAbancartShoppingCart.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartUnsubscribers.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartDefines.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartForm.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartFormSubmit.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartField.php');
require_once(dirname(__FILE__) . '/classes/EtsAbancartFieldValue.php');

class Ets_abandonedcart extends Module
{
    const prefix = 'ets_abancart_';
    const DEFAULT_MAX_SIZE = 104857600;
    const ETS_ABC_UPLOAD_IMG_DIR = 'ets_abandonedcart/img/';
    public $is17;
    public $ajax = 0;
    public static $slugTab = 'AdminEtsAC';
    public static $trans = [];
    public static $pattern_ignore_files = array(
        '/^email[1-5]\.jpg$/i',
        '/^customer[0-9]{1,2}\.jpg$/i',
        '/^image\.jpg$/i',
        '/^index\.php$/i',
        '/^fileType$/i'
    );
    public $base_link;

    public function __construct()
    {
        $this->name = 'ets_abandonedcart';
        $this->tab = 'front_office_features';
        $this->version = '4.3.1';
        $this->author = 'ETS-Soft';
        $this->need_instance = 0;
        $this->module_key = 'cc52f288974c9a2cc5b732cd6f06b9ca';
        $this->secure_key = Tools::hash($this->name);
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Abandoned Cart Reminder + Auto Email');
        $this->description = $this->l('Must-have PrestaShop abandoned cart reminder module to recover your lost shopping carts, send auto emails, retain existing customers and increase your sales by 50%');
$this->refs = 'https://prestahero.com/';
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->is17 = version_compare(_PS_VERSION_, '1.7', '>=');

        $this->base_link = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' . $this->context->shop->domain_ssl : 'http://' . $this->context->shop->domain) . $this->context->shop->getBaseURI();
    }

    public function hookActionAdminBefore($params)
    {
        if (isset($params['controller'])
            && $params['controller'] instanceof AdminAccessController
            && (int)Tools::getValue('submitAddAccess') == 1
            && trim(Tools::getValue('action')) == 'updateAccess'
            && ($id_profile = (int)Tools::getValue('id_profile')) > 0
            && !$this->is17
        ) {
            $id_tab = (int)Tools::getValue('id_tab');
            $perm = trim(Tools::getValue('perm'));
            $enabled = Tools::getValue('enabled') ? 1 : 0;
            $perms = [];
            $perms['view'] = $perms['add'] = $perms['edit'] = $perms['delete'] = ($perm == 'all' ? $enabled : 0);
            if ($perm !== 'all') {
                $parent = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'access` WHERE `id_tab`=' . (int)$id_tab . ' AND `id_profile`=' . (int)$id_profile);
                if ($parent) {
                    foreach ($parent as $key => $val) {
                        if (isset($perms[$key]))
                            $perms[$key] = $val;
                    }
                }
                $perms[$perm] = $enabled;
            }
            $duplicateOn = [];
            if ($perms) {
                foreach ($perms as $field => $val)
                    $duplicateOn[] = '`' . pSQL($field) . '`=' . pSQL($val);
            }
            $query = '';
            $tabs = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent`=' . (int)$id_tab);
            if ($tabs)
                $this->recursePerms($id_profile, $perms, $query, implode(',', $duplicateOn), $tabs);
            if (trim($query) !== '')
                Db::getInstance()->execute(rtrim($query, ';'));
        }
    }

    public function recursePerms($id_profile, $perms, &$query, $duplicateOn, $tabs)
    {
        if ($tabs) {
            foreach ($tabs as $tab) {
                if ($tabs2 = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'tab` WHERE `id_parent`=' . (int)$tab['id_tab'])) {
                    $this->recursePerms($id_profile, $perms, $query, $duplicateOn, $tabs2);
                } else {
                    $query .= 'INSERT INTO `' . _DB_PREFIX_ . 'access`(`id_profile`, `id_tab`, `view`, `add`, `edit`, `delete`) VALUES (' . (int)$id_profile . ', ' . (int)$tab['id_tab'] . ',' . $perms['view'] . ',' . $perms['add'] . ',' . $perms['edit'] . ',' . $perms['delete'] . ') ON DUPLICATE KEY UPDATE ' . $duplicateOn . ';' . PHP_EOL;
                }
            }
        }

        return $query;
    }

    static $iso_code_copyright = [];

    public function getCopyRight($iso_code)
    {
        if (!self::$iso_code_copyright || !isset(self::$iso_code_copyright[$iso_code]) || !self::$iso_code_copyright[$iso_code]) {
            self::$iso_code_copyright[$iso_code] = Tools::file_get_contents(dirname(__FILE__) . '/views/img/copyright/' . $iso_code . '.html');
        }

        return self::$iso_code_copyright[$iso_code];
    }

    public function buildEmailTemplate($dir)
    {
        if (trim($dir) == '' || !is_dir($dir) || !file_exists($dir))
            return false;

        require_once dirname(__FILE__) . '/classes/simple_html_dom';
        $files = [];
        $this->scanFilesOnDir($dir, $files);
        if ($files) {
            foreach ($files as $file) {
                $html = call_user_func('file_get_html', $file);
                if (preg_match('/\/(?:(en|es|fr|it)\/|index_(en|es|fr|it)\.html)/', $file, $matches) && !$html->find('.ets_abancart_copyright', 0)) {
                    $iso_code = !empty($matches[1]) ? $matches[1] : (!empty($matches[2]) ? $matches[2] : 'en');
                    if ($html->find('table.ets_abancart_preview_info', 0)) {
                        $html->find('table.ets_abancart_preview_info', 0)->parent()->innertext .= $this->getCopyRight($iso_code);
                    } else
                        $html->find('.ets_abancart_preview_info', 0)->innertext .= $this->getCopyRight($iso_code);
                    $html->save($file);
                }
            }
        }
    }

    public function scanFilesOnDir($dir, &$files)
    {
        if (is_dir($dir)) {
            $filesOnDir = scandir($dir);
            if ($filesOnDir) {
                foreach ($filesOnDir as $file) {
                    if ($file == '.' || $file == '..')
                        continue;
                    $file = rtrim($dir, '/') . '/' . $file;
                    if (is_dir($file)) {
                        $this->scanFilesOnDir($file, $files);
                    } else {
                        $info = pathinfo($file);
                        if (isset($info['extension']) && $info['extension'] == 'html')
                            $files[] = $file;
                    }
                }
            }
        }
    }

    public function install()
    {
        Configuration::updateValue('ETS_ABANCART_INSTALL_DATETIME', date('Y-m-d H:i:s'));

        include(dirname(__FILE__) . '/sql/install.php');

        self::_clearLogByCronjob();
        $this->beforeInstallOrUninstallOverride();

        return parent::install()
            && $this->_configHooks()
            && $this->_installTab()
            && $this->_installConfigs(EtsAbancartDefines::getInstance()->getFields('menus'))
            && $this->copyTrans()
            && $this->_installMail()
            && $this->_installPageCached()
            && EtsAbancartDefines::getInstance()->installDefaultConfig()
            && EtsAbancartDefines::getInstance()->installDefaultLeadConfigs()
            && $this->installLinkDefault();
    }

    public function beforeInstallOrUninstallOverride()
    {
        if (@file_exists(($dest = dirname(__FILE__) . '/override/classes/CartRule.php'))) {
            @file_put_contents($dest, preg_replace('/(public\s+(?:.+?)\s+function\s+autoRemoveFromCart\()(Context\s+)?(\$(?:.+?)\))/', '$1' . ($this->is17 ? 'Context ' : '') . '$3', Tools::file_get_contents($dest)));
        }
    }

    public function copyTrans()
    {
        $languages = Language::getLanguages(false);
        $configs = EtsAbancartDefines::getInstance()->getMailTrans();
        if ($languages && $configs) {
            foreach ($languages as $l) {
                $file_trans = dirname(__FILE__) . '/translations/' . $l['iso_code'] . '.php';
                if (file_exists($file_trans)) {
                    $content_trans = Tools::file_get_contents($file_trans);
                    if ($content_trans && preg_match('#\$_MODULE\[\'([^\']*?)\'\]#', $content_trans)) {
                        foreach ($configs as $key => $config) {
                            if (isset($config['trans']) && trim($config['trans']) != '' && preg_match('#\$_MODULE\[\'(?:[^\']+?)' . md5($config['trans']) . '\'\]\s*=\s*\'(.+?)\'\s*\;#', $content_trans, $matches)) {
                                Configuration::updateValue($key, [(int)$l['id_lang'] => $matches[1]]);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public static function _clearLogByCronjob()
    {
        Configuration::deleteByName('ETS_ABANCART_LAST_CRONJOB');
        $dest = _PS_CACHE_DIR_ . '/ets_abandonedcart/cronjob.log';
        if (@file_exists($dest))
            @unlink($dest);
    }

    public function _addTab($tab = array())
    {
        if (!$tab || !isset($tab['class']) && isset($tab['id_parent']) && (int)$tab['id_parent'])
            return 0;

        $tab['class'] = self::$slugTab . (isset($tab['class']) ? $tab['class'] : '');
        $tabId = (int)Tab::getIdFromClassName($tab['class']);
        if (!$tabId) {
            $tabId = null;
        }
        $t = new Tab($tabId);
        $t->active = 1;
        $t->class_name = trim($tab['class']);
        $t->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $t->name[$lang['id_lang']] = isset($tab['origin']) ? self::getTranslateByLang($tab['origin'], $lang, 'EtsAbancartDefines') : '';
        }
        $t->id_parent = isset($tab['id_parent']) ? (int)$tab['id_parent'] : 0;
        $t->module = $this->name;

        return $t->save() ? $t->id : 0;
    }

    public function _upgradeTab($class_name)
    {
        if ($id = Tab::getIdFromClassName(self::$slugTab . $class_name)) {
            $findTabInMenu = array();
            if ($menus = EtsAbancartDefines::getInstance()->getFields('menus')) {
                foreach ($menus as $menu) {
                    if (isset($menu['class']) && $menu['class'] == $class_name) {
                        $findTabInMenu = $menu;
                    }
                }
            }
            if ($findTabInMenu) {
                $tab = new Tab($id);
                foreach (Language::getLanguages(false) as $lang) {
                    $tab->name[$lang['id_lang']] = isset($findTabInMenu['origin']) ? self::getTranslateByLang($findTabInMenu['origin'], $lang, 'EtsAbancartDefines') : '';
                }
                $tab->save();
            }
        }
    }

    public function _addTabs($id_parent, $tabs)
    {
        if ($id_parent && $tabs) {
            foreach ($tabs as $tab) {
                $tab['id_parent'] = (int)$id_parent;
                if (!($idTab = $this->_addTab($tab)))
                    return false;
                if (isset($tab['sub_menus']) && $tab['sub_menus']) {
                    if (!$this->_addTabs($idTab, $tab['sub_menus']))
                        return false;
                }
            }
        }

        return true;
    }

    public function _installTab()
    {
        if ($id_parent = $this->_addTab(['label' => $this->l('Customer reminders'), 'origin' => 'Customer reminders'])) {
            $tabs = EtsAbancartDefines::getInstance()->getFields('menus');
            if (!$this->_addTabs($id_parent, $tabs))
                return false;
        }

        return true;
    }

    public static function getTranslateByLang($origin, $lang, $specific = null)
    {
        $module = 'ets_abandonedcart';

        if (is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif (is_object($lang))
            $iso_code = $lang->iso_code;
        else {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }

        $files_by_priority = _PS_MODULE_DIR_ . $module . '/translations/' . $iso_code . '.' . 'php';

        if (!@file_exists($files_by_priority)) {
            return Tools::stripslashes($origin);
        }

        $string = preg_replace("/\\\*'/", "\'", $origin);
        $key = md5($string);
        $new_key = Tools::strtolower('<{' . $module . '}prestashop>' . ($specific ?: $module)) . '_' . $key;

        preg_match('/(\$_MODULE\[\'' . preg_quote($new_key) . '\'\]\s*=\s*\')(.*)(\';)/', Tools::file_get_contents($files_by_priority), $matches);

        if ($matches && isset($matches[2])) {
            return Tools::stripslashes($matches[2]);
        }
        return Tools::stripslashes($origin);
    }

    public function _installPageCached()
    {
        if (EtsAbancartTools::isPageCachedEnabled()) {
            return (int)EtsAbancartTools::addModuleToPagecache($this->id);
        }
        return true;
    }

    public function _uninstallPageCached()
    {
        if (EtsAbancartTools::isPageCachedEnabled()) {
            return (int)EtsAbancartTools::deleteModuleFromPagecache($this->id);
        }
        return true;
    }

    public function isOverride($classname)
    {
        if (Module::isEnabled('ets_affiliatemarketing') && $classname == 'CartRule') {
            return true;
        }
    }

    public function addOverride($classname)
    {
        return $this->isOverride($classname) ?: parent::addOverride($classname);
    }

    public function removeOverride($classname)
    {
        return $this->isOverride($classname) ?: parent::removeOverride($classname);
    }

    public function _installConfigs($menus = array())
    {
        if ($menus) {
            foreach ($menus as $menu) {
                if (!isset($menu['sub_menus']) && isset($menu['object']) && !(int)$menu['object'] && isset($menu['entity']) && $menu['entity']) {
                    $this->_installConfig($menu['entity']);

                } elseif (isset($menu['sub_menus']) && $menu['sub_menus']) {
                    $this->_installConfigs($menu['sub_menus']);

                }
            }
        }
        return true;
    }

    public function _installConfig($entity = null)
    {
        if (!$entity ||
            !Validate::isCleanHtml($entity)
        ) {
            return false;
        }
        $fields = EtsAbancartDefines::getInstance()->getFields($entity);
        if (is_array($fields) && count($fields) > 0) {
            $languages = Language::getLanguages(false);
            foreach ($fields as $key => $config) {
                $global = isset($config['global']) && $config['global'] ? 1 : 0;
                if (isset($config['lang']) && $config['lang']) {
                    $values = array();
                    foreach ($languages as $lang) {
                        $values[$lang['id_lang']] = isset($config['default']) ? $config['default'] : '';
                    }
                    $this->setFields($key, $global, $values, true);
                } else {
                    $this->setFields($key, $global, isset($config['default']) ? $config['default'] : '', true);
                }
            }
        }
        return true;
    }

    public function setFields($key, $global = false, $values, $html = false)
    {
        return $global ? Configuration::updateGlobalValue($key, $values, $html) : Configuration::updateValue($key, $values, $html);
    }

    public function _configHooks($install = true)
    {
        $hooks = array(
            'displayHeader',
            'displayFooter',
            'displayBackOfficeHeader',
            'actionAuthentication',
            'actionCartSave',
            'actionValidateOrder',
            'displayShoppingCartFooter',
            'displayCustomerAccount',
            'displayCustomerShoppingCart',
            'cartRuleCheckValidity',
            'displayCronjobInfo',
            'actionObjectCustomerAddAfter',
            'actionObjectCustomerUpdateAfter',
            'displayBoFormTestMail',
            'displayBoPurchasedProduct',
            'actionAdminControllerSetMedia',
            'actionNewsletterRegistrationAfter',
            'moduleRoutes',
            'actionFrontControllerInitAfter',
            'actionFrontControllerAfterInit',
            'actionObjectLanguageAddAfter',
            'actionAdminUpdateAccessAfter',
            'actionAdminBefore',
        );
        foreach ($hooks as $hook) {
            if (!($install ? $this->registerHook($hook) : $this->unregisterHook($hook)))
                return false;
        }
        return true;
    }

    public function _installMail(Language $language = null)
    {
        EtsAbancartTools::createMailUploadFolder();
        if ($language !== null) {
            $this->recurseCopy(dirname(__FILE__) . '/mails/' . ($language->is_rtl ? 'en' : 'he'), dirname(__FILE__) . '/mails/' . $language->iso_code);
        } elseif (($languages = Language::getLanguages(false))) {
            foreach ($languages as $language) {
                $path_mail_iso_code = ($path_email = dirname(__FILE__) . '/mails/') . $language['iso_code'];
                if (!@file_exists($path_mail_iso_code) || !glob($path_mail_iso_code . '/*')) {
                    $this->recurseCopy($path_email . ($language['is_rtl'] ? 'en' : 'he'), $path_mail_iso_code);
                }
            }
        }
        return true;
    }

    public function recurseCopy($src, $dst)
    {
        if (!@file_exists($src)) {
            return false;
        }
        $dir = opendir($src);
        if (!@mkdir($dst)) {
            return false;
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    @copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function uninstall()
    {
        Configuration::deleteByName('ETS_ABANCART_INSTALL_DATETIME');

        include(dirname(__FILE__) . '/sql/uninstall.php');

        self::_clearLogByCronjob();
        $this->recursiveUnlink(_PS_IMG_DIR_ . Ets_abandonedcart::ETS_ABC_UPLOAD_IMG_DIR);

        $this->beforeInstallOrUninstallOverride();

        return parent::uninstall()
            && $this->_configHooks(false)
            && $this->_uninstallConfigs(EtsAbancartDefines::getInstance()->getFields('menus'))
            && $this->_uninstallPageCached()
            && $this->_uninstallTab()
            && $this->removeEmailTemplates()
            && EtsAbancartDefines::uninstallAllConfigs()
            && $this->unInstallLinkDefault();
    }

    public function recursiveUnlink($dir)
    {
        if (@is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                if (is_dir(($each = $dir . DIRECTORY_SEPARATOR . $file))) {
                    $this->recursiveUnlink($each);
                } elseif (self::$pattern_ignore_files) {
                    $flag = 1;
                    foreach (self::$pattern_ignore_files as $pattern) {
                        if (preg_match($pattern, $file)) {
                            $flag = 0;
                            break;
                        }
                    }
                    if ($flag)
                        @unlink($each);
                } else
                    @unlink($each);
            }
            //@rmdir($dir);
        }

        return true;
    }

    public function _uninstallConfigs($menus = array())
    {
        if ($menus) {
            foreach ($menus as $menu) {
                if (!isset($menu['sub_menus']) && isset($menu['object']) && !(int)$menu['object'] && !empty($menu['entity']))
                    $this->_uninstallConfig($menu['entity']);
                elseif (!empty($menu['sub_menus'])) {
                    $this->_uninstallConfigs($menu['sub_menus']);
                }
            }
        }

        return true;
    }

    public function _uninstallConfig($entity)
    {
        if (($fields = EtsAbancartDefines::getInstance()->getFields($entity))) {
            foreach ($fields as $key => $config) {
                if ($entity != 'mail_configs' || $key == 'ETS_ABANCART_MAIL_SERVICE') {
                    Configuration::deleteByName($key);
                } elseif (EtsAbancartDefines::$mail_options) {
                    foreach (EtsAbancartDefines::$mail_options as $id_option => $mail_option) {
                        if ($id_option != 'default') {
                            Configuration::deleteByName($key . '_' . Tools::strtoupper($mail_option['id_option']));
                        }
                    }
                }
            }
            unset($config);
        }
        return true;
    }

    private function _deleteTab($class = '')
    {
        $tabId = (int)Tab::getIdFromClassName(self::$slugTab . $class);
        if (!$tabId) {
            return true;
        }
        $tab = new Tab($tabId);

        return $tab->delete();
    }

    private function _deleteTabs($tabs)
    {
        if ($tabs) {
            foreach ($tabs as $tab) {
                if (!isset($tab['class']) || !$tab['class'])
                    continue;
                if (!$this->_deleteTab($tab['class']))
                    return false;
                if (isset($tab['sub_menus']) && $tab['sub_menus']) {
                    if (!$this->_deleteTabs($tab['sub_menus']))
                        return false;
                }
            }
        }

        return true;
    }

    public function _uninstallTab()
    {
        if ($this->_deleteTab()) {
            if ($tabs = EtsAbancartDefines::getInstance()->getFields('menus')) {
                if (!$this->_deleteTabs($tabs))
                    return false;
            }
        }
        return true;
    }

    public function removeEmailTemplates()
    {
        if (is_dir(_PS_IMG_DIR_ . $this->name)) {
            EtsAbancartTools::deleteAllDataInFolder(_PS_IMG_DIR_ . $this->name);
        }
        return true;
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        if (isset($params['object']) && Validate::isLoadedObject($params['object'])) {
            $this->_installMail($params['object']);
            EtsAbancartEmailTemplate::addNewLanguage($params['object']);
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        // Icon
        $this->context->controller->addJquery();
        $this->context->controller->addCSS(array($this->_path . 'views/css/icon-admin.css'), 'all');
        if ($controller == 'AdminEtsACLeads') {
            $this->context->controller->addJqueryUI('ui.sortable');
        }
        // Css backend module.
        $configure = ($configure = Tools::getValue('configure')) && Validate::isCleanHtml($configure) ? $configure : '';
        $html = '';
        if ($configure == $this->name || preg_match('/^AdminEtsAC([\w+])/', $controller)) {
            Media::addJsDef([
                'ETS_ABANCART_MSG_WARNING_CONTENT' => $this->l('Warning: Your content is going to be changed!'),
                'ETS_ABANCART_DELETE_TITLE' => $this->l('Delete'),
                'ETS_ABANCART_CLEAN_LOG_CONFIRM' => $this->l('Do you want to clear all mail logs?'),
            ]);
            $this->context->controller->addCSS($this->_path . 'views/css/abancart-admin.css');
            $this->smarty->assign(array(
                'img_dir' => $this->context->shop->getBaseURL(true) . $this->_path . 'views/img/origin/'
            ));
            $html .= $this->display(__FILE__, 'bo-head.tpl');
        }
        $this->smarty->assign(array(
            'linkReminderEmail' => $this->context->link->getAdminLink('AdminEtsACReminderEmail'),
            'linkCampaignTracking' => $this->context->link->getAdminLink('AdminEtsACTracking'),
            'logoLink' => $this->base_link . 'img/' . Configuration::get('PS_LOGO'),
            'fullBaseUrl' => $this->base_link,
            'imgModuleDir' => $this->context->shop->getBaseURL(true) . 'modules/' . $this->name . '/views/img/origin/',
        ));
        return $this->display(__FILE__, 'admin_head.tpl') . $html;
    }

    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJS($this->_path . 'views/js/admin_all.js');
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminEtsACDashboard'));
    }

    public function getAdminLink($args = array())
    {
        return $this->context->link->getAdminLink('AdminModules', (isset($args['token']) ? $args['token'] : true)) . (isset($args['conf']) ? '&conf=' . $args['conf'] : '') . '&configure=' . $this->name;
    }

    public function hookDisplayCronjobInfo()
    {
        $install_datetime = Configuration::get('ETS_ABANCART_INSTALL_DATETIME');
        if (!$install_datetime) {
            $install_datetime = Configuration::updateValue('ETS_ABANCART_INSTALL_DATETIME', date('Y-m-d H:i:s'));
        }
        $last_cronjob = Configuration::getGlobalValue('ETS_ABANCART_LAST_CRONJOB');
        $overtime = $last_cronjob ? (time() - (strtotime($last_cronjob) + 43200)) : (time() - (strtotime($install_datetime) + 86400));
        if ($last_cronjob && ($seconds = (time() - strtotime($last_cronjob))) <= 86400) {
            $dt1 = new DateTime("@0");
            $dt2 = new DateTime("@" . $seconds);
            if ($seconds > 3600)
                $format = $this->l('%h hours, %i minutes and %s seconds');
            elseif ($seconds > 60)
                $format = $this->l('%i minutes and %s seconds');
            else
                $format = $this->l('%s seconds');
            $last_cronjob = $dt1->diff($dt2)->format($format);
        }
        $this->smarty->assign(array(
            'ETS_ABANCART_LAST_CRONJOB' => $last_cronjob,
            'ETS_ABANCART_OVERTIME' => $overtime,
            'automationLink' => $this->context->link->getAdminLink(self::$slugTab . 'Configs', true)
        ));
        return $this->display(__FILE__, 'bo-cronjob.tpl');
    }

    public function hookDisplayBoPurchasedProduct($params)
    {
        $products = [];
        if (isset($params['ids']) && ($ids = $params['ids']) !== '') {
            $ids = explode(',', $ids);
            if (Validate::isArrayWithIds($ids)) {
                foreach ($ids as $id) {
                    $p = new Product($id, false, $this->context->language->id);
                    if ($p->id) {
                        $cover = Product::getCover($p->id, $this->context);
                        $products[] = [
                            'id' => $id,
                            'name' => $p->name,
                            'ref' => $p->reference,
                            'image' => $this->context->link->getImageLink($p->link_rewrite, (int)$cover['id_image'], EtsAbancartTools::getImageType('home')),
                            'link' => $this->context->link->getProductLink($p, null, null, null, $this->context->language->id)
                        ];
                    }
                }
            }
        }
        $this->smarty->assign([
            'products' => $products,
            'wrapper' => !isset($params['wrapper']) || (int)$params['wrapper'] > 0 ? 1 : 0,
            'name' => isset($params['name']) && trim($params['name']) !== '' ? $params['name'] : '',
        ]);
        return $this->display(__FILE__, 'bo-purchased-product.tpl');
    }

    public function hookDisplayBoFormTestMail()
    {
        $this->smarty->assign([
            'action' => $this->context->link->getAdminLink('AdminEtsACMailConfigs', true),
        ]);
        return $this->display(__FILE__, 'bo-test-mail.tpl');
    }

    /*-------------------END BACK OFFICE------------------*/

    /*----------------FRONTEND.---------------*/

    /*----------HOOKS----------*/
    public function hookDisplayCustomerShoppingCart($params)
    {
        $this->smarty->assign($params);
        return $this->display(__FILE__, 'fo-shopping-cart-list.tpl');
    }

    public function hookDisplayShoppingCartFooter()
    {
        if ((int)Configuration::get('ETS_ABANCART_SAVE_SHOPPING_CART') && $this->context->cart->getLastProduct() && !EtsAbancartShoppingCart::itemExist($this->context->cart->id)) {
            return $this->display(__FILE__, 'fo-shopping-cart.tpl');
        }
    }

    public function hookDisplayHeader()
    {
        //die(dump($this->context->cookie->getAll()));
        $jss = array(
            $this->_path . 'views/js/jquery.countdown.min.js',
            $this->_path . 'views/js/abancart.js',
            $this->_path . 'views/js/jquery.growl.js',
            $this->_path . 'views/js/captcha.js',
        );
        $css = array($this->_path . 'views/css/abancart.css');
        if ((int)Configuration::get('ETS_ABANCART_BROWSER_TAB_ENABLED')) {
            $jss[] = $this->_path . 'views/js/favico.js';
        }
        $this->context->controller->addJqueryPlugin('ui.datepicker');
        $this->context->controller->addCSS(_PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.css');
        if (method_exists($this->context->controller, 'registerJavascript')) {
            //$this->context->controller->registerJavascript('ets-js-jquery-plugins-timepicker', 'js/jquery/plugins/timepicker/jquery-ui-timepicker-addon.js', ['position' => 'bottom', 'priority' => 151]);
        } else {
            //$this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js');
        }
        $this->context->controller->addJS($jss);
        $this->context->controller->addCSS($css);

        $controller = trim(Tools::getValue('controller'));
        $moduleName = trim(Tools::getValue('module'));
        $urlAlias = trim(Tools::getValue('url_alias'));
        $linkCaptcha = null;

        $this->setMedia($controller, $moduleName);

        if ($controller == 'lead' && $moduleName == $this->name && $urlAlias) {
            $formItem = EtsAbancartForm::getFormByAlias($urlAlias, $this->context->language->id, true);
            if ($formItem && $formItem['enable_captcha'] && $formItem['captcha_type'] == 'v2') {
                $linkCaptcha = 'https://www.google.com/recaptcha/api.js?onload=etsAcOnLoadRecaptcha&render=explicit';
            } elseif ($formItem && $formItem['enable_captcha'] && $formItem['captcha_type'] == 'v3') {
                $linkCaptcha = 'https://www.google.com/recaptcha/api.js?onload=etsAcOnLoadRecaptcha&render=' . $formItem['captcha_site_key_v3'];
            }
        }
        $this->smarty->assign(array(
            'linkCaptcha' => $linkCaptcha
        ));

        return $this->display(__FILE__, 'head.tpl');
    }

    public function setMedia($controller = null, $moduleName = null)
    {
        if ($controller == null)
            $controller = trim(Tools::getValue('controller'));
        if ($moduleName == null)
            $moduleName = trim(Tools::getValue('module'));

        $jsDef = [
            'ETS_AC_LINK_SUBMIT_LEAD_FORM' => $this->context->link->getModuleLink($this->name, 'lead', array('url_alias' => '')),
            'ETS_ABANCART_LINK_AJAX' => $this->context->link->getModuleLink($this->name, 'request', array(), (int)Configuration::get('PS_SSL_ENABLED_EVERYWHERE')),
            'ETS_ABANCART_LINK_SHOPPING_CART' => $this->context->link->getModuleLink($this->name, 'cart', array(), (int)Configuration::get('PS_SSL_ENABLED_EVERYWHERE')),
            'ETS_ABANCART_COPIED_MESSAGE' => $this->l('Copied'),
        ];
        if (
            $controller !== 'order'
            && $controller !== 'order-opc'
            && ($controller !== 'cart' || $moduleName === $this->name)
            && ($controller != 'lead' || $moduleName != $this->name)
            && $controller !== 'orderconfirmation'
            && $controller !== 'authentication'
            && $controller !== 'productfeed'
        ) {
            $campaignReDisplay = [];
            $campaignsIsRun = [];
            $abandonedCookies = isset($this->context->cookie->ets_abancart_reminders) ? json_decode($this->context->cookie->ets_abancart_reminders, true) : [];//die(dump($this->context->cookie->unsetFamily('ets_abancart_reminders')));
            $resetCookie = false;
            if ($abandonedCookies) {
                foreach ($abandonedCookies as $type => $reminders) {
                    if (count($reminders) > 0) {
                        $reminders = array_reverse(EtsAbancartTools::quickSort($reminders, 'id_ets_abancart_reminder'));
                        foreach ($reminders as &$reminder) {
                            if (isset($reminder['id_ets_abancart_reminder']) && (int)$reminder['id_ets_abancart_reminder'] > 0 && (isset($reminder['deleted']) && (int)$reminder['deleted'] > 0 || EtsAbancartReminder::isInvalid($reminder['id_ets_abancart_reminder']) || isset($reminder['id_ets_abancart_campaign']) && (int)$reminder['id_ets_abancart_campaign'] > 0 && !EtsAbancartCampaign::isValid($reminder['id_ets_abancart_campaign'], $this->context))) {
                                unset($abandonedCookies[$type][$reminder['id_ets_abancart_reminder']]);
                                $resetCookie = true;
                            } elseif (isset($reminder['lifetime']) || $reminder['redisplay'] !== -1 && (!isset($reminder['closed']) || $reminder['closed'] < 1)) {
                                if (isset($reminder['lifetime'])) {
                                    $reminder['lifetime'] = EtsAbancartReminder::getLifeTime($reminder['id_ets_abancart_reminder'], $reminder['id_ets_abancart_campaign'], $this->context);
                                    $campaignsIsRun[] = $reminder;
                                } elseif (!isset($campaignReDisplay[$type])) {
                                    $estimateTime = $reminder['redisplay'] - (time() - $reminder['time']);
                                    $reminder['redisplay'] = max($estimateTime, 0);
                                    $campaignReDisplay[$type] = $reminder;
                                }
                            }
                        }
                    }
                }
                if ($resetCookie)
                    $this->context->cookie->ets_abancart_reminders = @json_encode($abandonedCookies);
                if (count($campaignReDisplay) > 0)
                    foreach ($campaignReDisplay as $rem)
                        $campaignsIsRun[] = $rem;
            }

            $jsDef['ETS_ABANCART_COOKIE_CAMPAIGNS'] = $campaignsIsRun;
            $jsDef['ETS_ABANCART_CAMPAIGNS'] = EtsAbancartCampaign::getCampaignsFrontEnd($this->context);//die(dump($jsDef));
            $jsDef['ETS_ABANCART_HAS_BROWSER'] = (int)EtsAbancartReminder::campaignValid(EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER) && (int)Configuration::get('ETS_ABANCART_ALLOW_NOTIFICATION');
            $jsDef['ETS_ABANCART_CLOSE_TITLE'] = $this->l('Close');

            $last_product = $this->context->cart ? $this->context->cart->getLastProduct() : array();
            if ((int)Configuration::get('ETS_ABANCART_SAVE_SHOPPING_CART')
                && $last_product
                && !EtsAbancartShoppingCart::itemExist($this->context->cart->id)
                && $this->context->cookie->off_cart != $this->context->cart->id
            ) {
                $jsDef['ETS_ABANCART_LIFE_TIME'] = 3600 * (int)Configuration::get('ETS_ABANCART_HOURS') + 60 * (int)Configuration::get('ETS_ABANCART_MINUTES') + (int)Configuration::get('ETS_ABANCART_SECONDS');
            }

            if ((int)Configuration::get('ETS_ABANCART_BROWSER_TAB_ENABLED')) {
                $jsDef['ETS_ABANCART_BROWSER_TAB_ENABLED'] = (int)Configuration::get('ETS_ABANCART_BROWSER_TAB_ENABLED');
                $jsDef['ETS_ABANCART_TEXT_COLOR'] = Configuration::get('ETS_ABANCART_TEXT_COLOR');
                $jsDef['ETS_ABANCART_BACKGROUND_COLOR'] = Configuration::get('ETS_ABANCART_BACKGROUND_COLOR');
                $jsDef['ETS_ABANCART_PRODUCT_TOTAL'] = (int)$this->context->cart->nbProducts();
            }
        }

        if ($jsDef)
            Media::addJsDef($jsDef);
    }

    public function hookActionFrontControllerInitAfter()
    {
        EtsAbancartTools::getInstance()->checkCartRuleValidity();
    }

    public function hookActionObjectCustomerUpdateAfter($params)
    {
        $params['afterUpdateCustomer'] = 1;
        $this->hookActionObjectCustomerAddAfter($params);
    }

    public function hookActionObjectCustomerAddAfter($params)
    {
        if (isset($params['object'])
            && $params['object'] instanceof Customer
            && $params['object']->id > 0
            && EtsAbancartReminder::campaignValid(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER)
            && !EtsAbancartUnsubscribers::isUnsubscribe($params['object']->id)
        ) {
            /** @var Customer $customer */
            $customer = $params['object'];

            // Last create order:
            $afterOrder = isset($params['afterOrder']) && $params['afterOrder'];
            $afterUpdateCustomer = isset($params['afterUpdateCustomer']) && $params['afterUpdateCustomer'];

            // afterCreateOrder
            if ($afterOrder)
                EtsAbancartIndexCustomer::addCustomerIndex($customer, 0, false, false, true);
            elseif (!$afterUpdateCustomer) {
                // afterRegister
                EtsAbancartIndexCustomer::addCustomerIndex($customer, 0, false, true);
                // afterSubscribe
                if ($customer->newsletter && !EtsAbancartUnsubscribers::isSubscribeByEmail($customer->email)) {
                    EtsAbancartIndexCustomer::addCustomerIndex($customer, 0, false, false, false, true);
                }
            }
        }
    }

    public function hookActionCartSave($params)
    {
        if (!empty($params['cookie'])
            && isset($params['cookie']->id_customer)
            && ((isset($params['cookie']->id_cart) && ($id_cart = $params['cookie']->id_cart)) || (isset($params['cart']) && isset($params['cart']->id) && ($id_cart = $params['cart']->id)))
            && EtsAbancartReminder::campaignValid(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL)
            && ($cart = new Cart((int)$id_cart))
            && $cart->id > 0
        ) {
            $context = Context::getContext();
            if (!isset($context->cart) || !isset($context->cart->id) || $context->cart->id <= 0) {
                Context::getContext()->cart = $cart;
            }
            if ($cart->getLastProduct()
                && ($customer = new Customer((int)$params['cookie']->id_customer))
                && $customer->id > 0
                && !EtsAbancartUnsubscribers::isUnsubscribe((int)$params['cookie']->id_customer)
            ) {
                EtsAbancartIndex::addCartIndex($cart, $customer, 0, 0, true);
            }
        }
    }

    public function hookActionValidateOrder($params)
    {
        if (!empty($params['cart'])
            && $params['cart'] instanceof Cart
            && (int)$params['cart']->id > 0
            && ($cart = new Cart((int)$params['cart']->id))
            && $cart->id > 0
            && $cart->id_customer > 0
        ) {
            // Remove cart in index
            EtsAbancartIndex::deleteIndex(0, 0, $cart->id);
            // Clear cart is ordered:
            EtsAbancartTools::cleanQueueOrdered($cart->id);
            // Reindex customer
            $customer = new Customer((int)$cart->id_customer);
            if ($customer->id) {
                $orderId = isset($params['order']) && isset($params['order']->id) && $params['order']->id ? $params['order']->id : 0;
                $this->hookActionObjectCustomerAddAfter(array('object' => $customer, 'afterOrder' => $orderId));
            }
        }
    }

    /**
     * Detected cart when customer click button checkout from email:
     * @param $params
     */
    public function hookActionAuthentication($params)
    {
        if (isset($this->context->cookie->recover_cart_id) && $this->context->cookie->recover_cart_id && isset($params['customer']) && $params['customer']->id) {
            $cart = new Cart((int)$this->context->cookie->recover_cart_id);
            if (Validate::isLoadedObject($cart)) {
                $customer = new Customer((int)$cart->id_customer);
                if (trim($params['customer']->email) == trim($customer->email)) {
                    $this->context->cart = $cart;
                    $this->context->cookie->id_cart = (int)$this->context->cart->id;
                    $this->context->cart->autosetProductAddress();
                    // Login information have changed, so we check if the cart rules still apply
                    CartRule::autoRemoveFromCart($this->context);
                    CartRule::autoAddToCart($this->context);
                    unset($this->context->cookie->recover_cart_id);
                    $this->context->cookie->write();

                }
            }
        }
    }

    /*
     * hookDisplayFooter in previous version
     * */
    public function hookDisplayFooter()
    {
        $moduleName = trim(Tools::getValue('module'));
        $controller = trim(Tools::getValue('controller'));

        $hasProductInCart = Configuration::get('ETS_ABANCART_HAS_PRODUCT_IN_CART');
        $accept = true;
        if ($hasProductInCart == 1) {
            $accept = false;
            if ($this->context->cart && $this->context->cart->getLastProduct() && (int)$this->context->cart->id) {
                $accept = true;
            }
        } elseif ($hasProductInCart == 0) {
            $accept = false;
            if (!$this->context->cart || !$this->context->cart->getLastProduct() || !(int)$this->context->cart->id) {
                $accept = true;
            }
        }

        if (($controller != 'lead' || ($controller == 'lead' && $moduleName != $this->name)) && !$this->hasCookieOffLeave() && (int)Configuration::get('ETS_ABANCART_LEAVE_WEBSITE_ENABLED') && $accept && ($msg = Configuration::get('ETS_ABANCART_CONTENT', (int)$this->context->language->id))) {
            $assigns = array(
                'campaign_type' => 'leave',
                'content' => $msg,
                'cart' => $this->context->cart,
            );
            $this->smarty->assign(array(
                'html' => $this->doShortCode($assigns['content'], $assigns['campaign_type'], null, $this->context, -1, $this->context->cart->id),
            ));

            return $this->display(__FILE__, 'fo-leave.tpl');
        }
    }

    public function hasCookieOffLeave()
    {
        if (empty($this->context->cookie->offLeave)) {
            return false;
        }
        $time = $this->context->cookie->offLeave;
        if ($time == 1) {
            return false;
        }
        if ($lastTimeSaveLeave = (int)Configuration::get('ETS_ABANCART_LEAVE_TIME_UPDATE')) {
            return (int)$time >= $lastTimeSaveLeave;
        }

        return false;
    }

    public function hookDisplayCustomerAccount()
    {
        if ((int)Configuration::get('ETS_ABANCART_SAVE_SHOPPING_CART')) {
            $this->smarty->assign(array(
                'link' => $this->context->link->getModuleLink($this->name, 'cart', array(), (int)Configuration::get('PS_SSL_ENABLED_EVERYWHERE')),
                'is17' => $this->is17
            ));
            return $this->display(__FILE__, 'fo-block.tpl');
        }
    }

    public function hookCartRuleCheckValidity($params)
    {
        $display_error = isset($params['display_error']) && $params['display_error'];
        $id_cart_rule = isset($params['id_cart_rule']) && $params['id_cart_rule'] ? (int)$params['id_cart_rule'] : 0;

        $cartRuleId = EtsAbancartTracking::isCartRuleUsed($id_cart_rule) ?: EtsAbancartDisplayTracking::isCartRuleUsed($id_cart_rule, $this->context);
        $voucherCode = null;

        if ($this->context->cart->id &&
            (
                $cartRuleId > 0 && (EtsAbancartTracking::hasCartRules($id_cart_rule) || EtsAbancartDisplayTracking::hasCartRules($id_cart_rule)) ||
                EtsAbancartTracking::onCartRule($this->context) || EtsAbancartDisplayTracking::onCartRule($this->context)
            )
        ) {
            return !$display_error ? false : $this->l('You cannot use this voucher');
        } elseif (!EtsAbancartTools::canUseCartRule($this->context->cart->id, $id_cart_rule, $voucherCode)) {
            return !$display_error ? false : sprintf($this->l('Cannot use voucher code %s with others voucher code'), $voucherCode);
        }

        if (!$display_error) {
            return true;
        }
    }

    public function hookActionNewsletterRegistrationAfter($params)
    {
        $email = isset($params['email']) && $params['email'] ? $params['email'] : null;
        if (trim($email) !== '') {
            $customers = Customer::getCustomersByEmail($email);
            if (!$customers) {
                $c = new Customer();
                $c->email = $email;
                $c->id_shop = $this->context->shop->id;
                $c->id_lang = $this->context->language->id;
                if (EtsAbancartUnsubscribers::isSubscribeByEmail($email))
                    EtsAbancartIndexCustomer::addCustomerIndex($c, 0, false, false, false, true);
            } else {
                foreach ($customers as $item) {
                    if (isset($item['id_customer']) && (int)$item['id_customer']
                        && ($customer = new Customer((int)$item['id_customer']))
                        && !EtsAbancartUnsubscribers::isUnsubscribe($customer->id)
                        && $customer->newsletter
                    ) {
                        EtsAbancartIndexCustomer::addCustomerIndex($customer, 0, false, false, false, true);
                    }
                }
            }
        }
    }

    /*----------END HOOKS----------*/

    /**
     * @param EtsAbancartReminder $reminder
     * @param int $id_customer
     * @return bool|CartRule
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function addCartRule(EtsAbancartReminder $reminder, $id_customer = 0)
    {
        if (!$reminder ||
            $id_customer > 0 && !Validate::isUnsignedInt($id_customer)
        ) {
            return false;
        }
        $cart_rule = new CartRule();
        $cart_rule->id_customer = $id_customer;
        if ($languages = Language::getLanguages(false)) {
            $defaultName = $this->l('Discount code automatically generated by this reminder');
            foreach ($languages as $l) {
                $cart_rule->name[$l['id_lang']] = $reminder->discount_name ? (is_array($reminder->discount_name) && isset($reminder->discount_name[$l['id_lang']]) ? $reminder->discount_name[$l['id_lang']] : (!is_array($reminder->discount_name) && $reminder->discount_name ? $reminder->discount_name : $defaultName)) : $defaultName;
            }
        }

        $cart_rule->free_shipping = $reminder->free_shipping;
        $cart_rule->code = Tools::strtoupper(Tools::passwdGen(8));
        $cart_rule->reduction_currency = $reminder->id_currency;
        $cart_rule->reduction_percent = 0;
        $cart_rule->reduction_amount = 0;
        if ($reminder->apply_discount == 'percent')
            $cart_rule->reduction_percent = $reminder->reduction_percent;
        if ($reminder->apply_discount == 'amount')
            $cart_rule->reduction_amount = $reminder->reduction_amount;
        $cart_rule->reduction_tax = $reminder->reduction_tax;
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->date_to = $reminder->apply_discount_in ? date('Y-m-d H:i:s', strtotime('+' . $reminder->apply_discount_in . ' days')) : date('Y-m-d H:i:s', strtotime('+30 days'));
        $cart_rule->quantity = $reminder->quantity;
        $cart_rule->quantity_per_user = $reminder->quantity_per_user;
        $cart_rule->gift_product = $reminder->gift_product;
        $cart_rule->gift_product_attribute = $reminder->gift_product_attribute;
        $cart_rule->reduction_product = $reminder->reduction_product;
        $cart_rule->reduction_exclude_special = $reminder->reduction_exclude_special;

        if ($reminder->id_ets_abancart_campaign) {
            $campaign = new EtsAbancartCampaign($reminder->id_ets_abancart_campaign);

            if ($campaign && $campaign->id) {
                if ($campaign->min_total_cart) {
                    $cart_rule->minimum_amount = $campaign->min_total_cart;
                    $cart_rule->minimum_amount_tax = 0;
                }
            }
        }
        $success = $cart_rule->add();

        if (($errors = $cart_rule->validateFields(false, true)) !== true) {
            $this->_errors[] = $errors !== false ? $errors : $this->l('Unknown error happened');
        } elseif (!$success) {
            $this->_errors[] = $this->l('Creating cart rule failed.');
        }
        if ($success && $cart_rule->id) {
            $ids = null;
            if ($cart_rule->reduction_product == -2 && $reminder->selected_product)
                $ids = explode(',', $reminder->selected_product);
            if ((int)$cart_rule->reduction_product > 0)
                $ids = array((int)$cart_rule->reduction_product);
            if ($ids) {
                $ids = array_map('intval', $ids);
                EtsAbancartTools::addCartRules($ids, $cart_rule->id);
            }

        }
        return is_array($this->_errors) && count($this->_errors) > 0 ? false : $cart_rule;
    }

    /**
     * @param $content
     * @param $campaign_type
     * @param CartRule|null $cart_rule
     * @param Context|null $context
     * @return bool|string|string[]|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     */
    public function doShortCode($content, $campaign_type, CartRule $cart_rule = null, Context $context = null, $id_ets_abancart_reminder = null)
    {
        if (!$content ||
            (!is_array($context) && !Validate::isCleanHtml($content)) ||
            !$campaign_type ||
            !in_array($campaign_type, explode(',', EtsAbancartCampaign::_CAMPAIGN_TYPE_))
        ) {
            return $content;
        }
        if (!$context) {
            $context = Context::getContext();
        }
        $ssl = (int)Configuration::get('PS_SSL_ENABLED_EVERYWHERE');

        $shop_name = '{shop_name}';
        $shop_logo = $this->doSmarty(array('logo' => true));
        if (!in_array($campaign_type, array('email', 'cart', 'customer'))) {
            $shop_name = $context->shop->name ?: Configuration::get('PS_SHOP_NAME');
            $shop_logo = $this->doSmarty(array('shop_logo' => $this->base_link . 'img/' . Configuration::get('PS_LOGO')));
        }
        $content = preg_replace('/(\[logo\]|\[shop_logo\])/is', $shop_logo, $content);
        $content = $this->regexColor('shop_name', $content, array('short_code_content' => $shop_name));

        $currency = Currency::getCurrencyInstance(isset($context->cart->id_currency) && $context->cart->id_currency ?: Configuration::get('PS_CURRENCY_DEFAULT'));
        $id_group = isset($context->customer->id) && $context->customer->id ? Customer::getDefaultGroupId((int)$context->customer->id) : (int)Group::getCurrent()->id;
        $group = new Group($id_group);
        $useTax = $group->price_display_method ? false : true;
        if (!$cart_rule) {
            $cart_rule = new CartRule();
        }
        if ($campaign_type != 'customer') {
            $checkoutURL = in_array($campaign_type, array('email', 'cart')) && isset($context->cart->id) && $context->cart->id ? ($context->link->getModuleLink($this->name, 'cart', array('id_cart' => $context->cart->id), $ssl) . '&checkout&verify=' . $this->encrypt($context->cart->id)) : ($context->link->getPageLink($this->is17 ? 'cart' : (Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order'), $ssl) . ($this->is17 ? '?action=show' : ''));
            $content = $this->regexColor(
                ['checkout_button']
                , $content
                , ['checkout_button' => $checkoutURL]
                , isset($context->cart->id_lang) && $context->cart->id_lang ? (int)$context->cart->id_lang : Configuration::get('PS_LANG_DEFAULT')
            );
        }

        if (!in_array($campaign_type, array('bar', 'browser', 'leave'))) {
            $content = $this->regexColor('product_list', $content, array('id_list' => rand(1111, 99999), 'product_list' => $this->doProductSmarty($context->cart->getProducts(), $context)));
        }

        $content = $this->addProductGrid($content, $campaign_type, $context);
        if (!in_array($campaign_type, array('customer', 'leave'))) {
            // Total cart
            $cartLang = isset($context->cart->id_lang) && $context->cart->id_lang ? (int)$context->cart->id_lang : Configuration::get('PS_LANG_DEFAULT');
            $total_cart = $context->cart->getOrderTotal($useTax);
            $content = $this->regexColor(array('total_cart'), $content, array('short_code_content' => Tools::displayPrice($total_cart, $currency)), $cartLang);

            // Total product cost
            $content = $this->regexColor('total_products_cost', $content, array('short_code_content' => Tools::displayPrice($context->cart->getOrderTotal($useTax, Cart::ONLY_PRODUCTS), $currency)), $cartLang);

            // Total shipping cost
            $shipping_code = $context->cart->getOrderTotal($useTax, Cart::ONLY_SHIPPING);
            $content = $this->regexColor('total_shipping_cost', $content, array('short_code_content' => Tools::displayPrice($shipping_code, $currency)), $cartLang);

            // Total tax
            $content = $this->regexColor('total_tax', $content, array('short_code_content' => Tools::displayPrice($useTax ? ($total_cart - $context->cart->getOrderTotal(false)) : 0.00, $currency)));

            // Money saved
            $money_saved = ($cart_rule->free_shipping ? $shipping_code : 0) + $cart_rule->getContextualValue($useTax, $context, CartRule::FILTER_ACTION_REDUCTION);
            $content = $this->regexColor('money_saved', $content, array('short_code_content' => Tools::displayPrice($money_saved, $currency)));

            // Total payment after discount
            $content = $this->regexColor('total_payment_after_discount', $content, array('short_code_content' => Tools::displayPrice(max($total_cart - $money_saved, 0.00), $currency)));
        }
        // First name
        $content = $this->regexColor('firstname', $content, array('short_code_content' => $context->customer->firstname));
        // Last name
        $content = $this->regexColor('lastname', $content, array('short_code_content' => $context->customer->lastname));

        $content = $this->regexColor('shop_button', $content, array('shop_button' => $this->context->shop->getBaseURL(false)), isset($context->language->id) ? $context->language->id : Configuration::get('PS_LANG_DEFAULT'));
        // Link unsubscribe
        if (isset($context->customer) && isset($context->customer->email)) {
            $linkUnsubscribe = $context->link->getModuleLink($this->name, 'unsubscribe', array('email' => $context->customer->email, 'verify' => $this->encrypt($context->customer->email)), $ssl);
            $content = $this->regexColor('unsubscribe', $content, array('unsubscribe' => $linkUnsubscribe), isset($context->cart->id_lang) ? (int)$context->cart->id_lang : Configuration::get('PS_LANG_DEFAULT'));
        } else {
            $content = $this->regexColor('unsubscribe', $content, array('unsubscribe' => ''), isset($context->cart->id_lang) ? (int)$context->cart->id_lang : Configuration::get('PS_LANG_DEFAULT'));
        }
        // Count down clock
        if (in_array($campaign_type, array('popup', 'bar'))) {
            $content = $this->regexColor('discount_count_down_clock', $content, array('discount_count_down_clock' => '1', 'date_to' => $cart_rule->date_to));
        }
        $content = $this->addCountdownClock($content, $campaign_type);
        // Button discount and no thanks
        if (in_array($campaign_type, array('popup', 'leave', 'bar'))) {
            $content = $this->regexColor(
                ['show_discount_box', 'button_no_thanks']
                , $content
                , []
                , (int)$context->cart->id_lang
            );
        }

        $content = $this->addLeadForm($content, $campaign_type, $id_ets_abancart_reminder);
        $content = $this->addCustomButton($content);
        // Type leave:
        if ($campaign_type == 'leave') {
            return $content;
        }
        $reduction = 0;
        if ((float)$cart_rule->reduction_percent) {
            // Percent:
            $reduction = Tools::displayNumber($cart_rule->reduction_percent, $currency) . '%';
            $content = $this->regexColor('reduction', $content, array('short_code_content' => $reduction));
        } elseif ((float)$cart_rule->reduction_amount) {
            // Mount
            $reduction_amount = Tools::convertPrice($cart_rule->reduction_amount, $cart_rule->reduction_currency, false);
            $reduction = Tools::displayPrice(Tools::ps_round(Tools::convertPrice($reduction_amount, $currency), 2), $currency) . ' ' . ($cart_rule->reduction_tax ? $this->l('(tax incl.)') : $this->l('(tax excl.)'));
            $content = $this->regexColor('reduction', $content, array('short_code_content' => $reduction));
        } elseif ($cart_rule->free_shipping) {
            // Free shipping
            $content = $this->regexColor('reduction', $content, array('short_code_content' => $this->l('Free shipping')));
        } else {
            $content = $this->regexColor('reduction', $content, array('short_code_content' => $this->l('None')));
        }

        if (!in_array($campaign_type, array('email', 'cart'))) {

            $content = $this->regexColor(
                'button_add_discount'
                , $content
                , [
                    'campaign_type' => $campaign_type,
                    'discount_code' => $cart_rule->code,
                    'reduction' => $reduction,
                ]
                , (int)$context->cart->id_lang
            );
        }

        // Discount code
        $content = $this->regexColor('discount_code', $content, array('short_code_content' => $cart_rule->code));
        $content = $this->regexColor('discount_from', $content, array('short_code_content' => $cart_rule->id ? date($context->language->date_format_lite, strtotime($cart_rule->date_from)) : ''));
        $content = $this->regexColor('discount_to', $content, array('short_code_content' => $cart_rule->id ? date($context->language->date_format_lite, strtotime($cart_rule->date_to)) : ''));

        if ($campaign_type == 'customer') {
            if (isset($context->customer) && isset($context->customer->id)) {
                $content = $this->regexColor('registration_date', $content, array('short_code_content' => date('d-m-Y', strtotime($context->customer->date_add))));
                $lastOrder = EtsAbancartTools::getLastOrderCustomer($context->customer->id);
                if ($lastOrder) {
                    $content = $this->regexColor('last_order_id', $content, array('short_code_content' => $lastOrder['id_order']));
                    $content = $this->regexColor('last_order_reference', $content, array('short_code_content' => $lastOrder['reference']));
                    $content = $this->regexColor('last_order_total', $content, array('short_code_content' => Tools::displayPrice((float)$lastOrder['total_paid_tax_incl'] * (float)$lastOrder['conversion_rate'])));
                }
                $content = $this->regexColor('order_total', $content, array('short_code_content' => Tools::displayPrice(EtsAbancartTools::getTotalOrder($context->customer->id))));

                if ($lastLoginTime = EtsAbancartTools::getLastLoginTime($context->customer->id))
                    $content = $this->regexColor('last_time_login_date', $content, array('short_code_content' => date('d-m-Y', strtotime($lastLoginTime))));

            }

        }

        return $content;
    }

    public function addProductGrid($content, $campaign_type, $context)
    {
        if ($campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL || $campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER || $campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CART) {
            preg_match_all('/\[product_grid id="([0-9\,]*)?"(\s+[^\]]*)?\]/i', $content, $matches);
            if ($matches && isset($matches[1]) && is_array($matches[1])) {
                foreach ($matches[1] as $item) {
                    $item = trim($item);
                    $ids = array();
                    if ($item) {
                        $ids = explode(',', $item);
                        if ($ids) {
                            $ids = array_map('intval', $ids);
                        }
                    }
                    if (!$ids) {
                        $ids = EtsAbancartTools::getRandomIdProduct(3, $context);
                    }

                    $content = $this->regexColor('product_grid id="' . $item . '"', $content, array('grid_id' => rand(1111, 99999), 'product_grid' => $this->getProductGridItem($ids, $context)));
                }
            }
        } else {
            $content = preg_replace('/\[product_grid[^\]]*\]/', '', $content);
        }
        return $content;
    }

    public function getProductGridItem($ids, $context)
    {
        $products = array();
        if (!$context) {
            $context = Context::getContext();
        };
        if ($context->cart && $context->cart->id)
            $currency = Currency::getCurrencyInstance($context->cart->id_currency);
        else {
            $currency = Context::getContext()->currency;
        }
        $currency_from = isset($context->currency) ? $context->currency : Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $id_group = isset($context->customer) && $context->customer->id ? Customer::getDefaultGroupId((int)$context->customer->id) : (int)Group::getCurrent()->id;
        $group = new Group($id_group);
        $useTax = $group->price_display_method ? false : true;

        foreach ($ids as $id) {
            $product = array('id_product_attribute' => 0);
            $p = new Product($id, true, ($context->cart && $context->cart->id ? $context->cart->id_lang : $context->language->id), ($context->cart && $context->cart->id ? $context->cart->id_shop : $context->shop->id));
            if ($p->id) {
                // Price:
                $id_customization = EtsAbancartTools::getCustomizationId($context->cart->id, $p->id);
                $price = Tools::convertPrice(Customization::getCustomizationPrice($id_customization), $currency);
                $product['price'] = $p->getPrice($useTax, $product['id_product_attribute'] ?: null) + $price;
                $oldPrice = $p->getPriceWithoutReduct(!$useTax, $product['id_product_attribute'] ?: null) + $price;
                if ($oldPrice && $oldPrice != $product['price']) {
                    $product['old_price'] = Tools::displayPrice(Tools::convertPriceFull((float)$oldPrice, $currency_from, $currency), $currency);
                }
                $product['link'] = $context->link->getProductLink($p, null, null, null, null, null, $product['id_product_attribute'] ? $product['id_product_attribute'] : 0);
                $product['name'] = $p->name;
                // Image:
                $image = ($product['id_product_attribute'] && ($image = $this->getCombinationImageById($product['id_product_attribute'], $context->cart->id_lang))) ? $image : Product::getCover($id);

                $product['image'] = $context->link->getImageLink($p->link_rewrite, isset($image['id_image']) ? $image['id_image'] : 0, $this->getFormattedName('cart'));

                // Attribute:
                if (!empty($product['id_product_attribute'])) {
                    $p->id_product_attribute = $product['id_product_attribute'];
                    $product['attributes'] = $p->getAttributeCombinationsById($product['id_product_attribute'], $context->cart->id_lang);
                }
                // Other:
                $product['is_available'] = $p->checkQty(1);
                $product['allow_oosp'] = Product::isAvailableWhenOutOfStock($p->out_of_stock);
                $product['price'] = Tools::displayPrice(Tools::convertPriceFull((float)$product['price'], $currency_from, $currency), $currency);
                $products[] = $product;
            }
        }

        $this->smarty->assign(array(
            'product_grid' => $products,
        ));

        return $this->display(__FILE__, 'product_grid.tpl');
    }

    public function addCustomButton($content)
    {
        preg_match_all('/\[custom_button href="([^"]*)"\s+text="([^"]*)"(\s+[^\]]*)?\]/i', $content, $matches);
        if ($matches && isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $k => $item) {
                $content = $this->regexColor('custom_button href="[^\"]*" text="[^\"]*"', $content, array('custom_button_href' => $item, 'custom_button_text' => $matches[2][$k]), 0);
            }
        }
        return $content;
    }

    public function addLeadForm($content, $campaign_type, $id_ets_abancart_reminder)
    {
        preg_match_all('/\[lead_form id=(\d+)(\s+[^\]]*)?\]/i', $content, $matches);
        $context = $this->context;
        if ($matches && isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $item) {
                if (EtsAbancartForm::getLeadFormCookie($item, $this->context)) {
                    $content = preg_replace('/\[lead_form id=' . $item . '(\s+[^\]]*)?\]/i', '', $content);
                } else {
                    $content = $this->regexColor('lead_form id=' . $item, $content, array('lead_form' => $this->getLeadForm($item, $campaign_type, $id_ets_abancart_reminder, isset($context->customer) ? $context->customer->id : null, 0)), 0);
                }
            }
        }
        return $content;
    }

    public function addCountdownClock($content, $type)
    {
        if ($type == 'customer' || $type == 'email') {
            return preg_replace('/\[countdown_clock([^\]]*)?\]/i', '', $content);
        }
        preg_match_all('/\[countdown_clock\s+endtime="(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})"([^\]]*)?\]/i', $content, $matches);
        if ($matches && isset($matches[1]) && is_array($matches[1])) {
            foreach ($matches[1] as $item) {
                $content = $this->regexColor('countdown_clock endtime="' . $item . '"', $content, array('countdown_clock' => 1, 'endtime' => strtotime($item)), 0);
            }
        }
        return $content;
    }

    public function getLeadForm($idForm, $type, $idReminder = null, $idCustomer = null, $idCart = null, $idLang = null)
    {
        $leadForm = EtsAbancartForm::getFormById($idForm);
        if (!(int)$leadForm['enable']) {
            return '';
        }
        if ($leadForm && isset($leadForm['link'])) {
            $queryParams = array(
                'idReminder' => $idReminder ?: '',
                'idCustomer' => $idCustomer ?: '',
                'idCart' => $idCart ?: '',
                'idLang' => $idLang ?: '',
            );

            if (strpos($leadForm['link'], '?') !== false) {
                $leadForm['link'] .= '&' . http_build_query($queryParams);
            } else {
                $leadForm['link'] .= '?' . http_build_query($queryParams);
            }
        }

        $this->smarty->assign(array(
            'lead_form' => $leadForm,
            'field_types' => EtsAbancartField::getInstance()->getFieldType(),
            'reminderType' => $type,
            'addTagForm' => true,
            'idReminder' => $idReminder,
            'idCart' => $idCart,
            'enableCaptcha' => (int)$leadForm['enable_captcha'],
            'captchaType' => $leadForm['captcha_type'],
            'maxSizeUpload' => Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE'),
            'customerIsLogged' => $this->context->customer ? $this->context->customer->isLogged() : null,
            'captchaSiteKey' => $leadForm['captcha_type'] == 'v2' ? $leadForm['captcha_site_key_v2'] : $leadForm['captcha_site_key_v3'],
        ));

        return $this->display(__FILE__, 'lead_form_short_code.tpl');
    }

    public function regexColor($short_codes, $content, $tpl_vars = [], $id_lang = 0)
    {
        if (!$short_codes ||
            !is_array($short_codes) && !Validate::isCleanHtml($short_codes)
        ) {
            return false;
        }

        if (!is_array($short_codes))
            $short_codes = array($short_codes);

        foreach ($short_codes as $short_code) {
            $pattern = '/\[' . $short_code . '([^\]]*)?\]/i';

            if (preg_match_all($pattern, $content, $matches) && isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0) {
                foreach ($matches[1] as $match) {
                    $styles = [];
                    if (preg_match_all('/(?:color|font|background|border|padding|margin)[0-9a-z\-]*\:\s*[0-9a-zA-Z#]+/i', $match, $attributes) && isset($attributes[0]) && is_array($attributes[0]) && count($attributes[0]) > 0) {
                        foreach ($attributes[0] as $attribute) {
                            $styles[] = trim($attribute);
                        }
                    }

                    $content = preg_replace($pattern, str_replace("$", "\\$", $this->doSmarty(array_merge(
                        [
                            $short_code => true,
                            'style' => implode(';', $styles),
                        ],
                        $tpl_vars
                    ), $id_lang)), $content);

                }
            }

        }
        return $content;
    }

    public function doProductSmarty($products, Context $context = null)
    {
        if (!$products
            || !is_array($products) ||
            !$context->cart->id
        ) {
            return '';
        }

        if (!$context) {
            $context = Context::getContext();
        };
        $currency = Currency::getCurrencyInstance($context->cart->id_currency);
        $currency_from = isset($context->currency) ? $context->currency : Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $id_group = isset($context->customer) && $context->customer->id ? Customer::getDefaultGroupId((int)$context->customer->id) : (int)Group::getCurrent()->id;
        $group = new Group($id_group);
        $useTax = $group->price_display_method ? false : true;
        $ik = 0;
        foreach ($products as &$product) {
            $p = new Product($product['id_product'], true, $context->cart->id_lang, $context->cart->id_shop);
            if ($p->id) {
                // Price:
                $id_customization = EtsAbancartTools::getCustomizationId($context->cart->id, $p->id);
                $price = Tools::convertPrice(Customization::getCustomizationPrice($id_customization), $currency);
                $product['price'] = $p->getPrice($useTax, $product['id_product_attribute'] ?: null) + $price;
                $oldPrice = $p->getPriceWithoutReduct(!$useTax, $product['id_product_attribute'] ?: null) + $price;
                if ($oldPrice && $oldPrice != $product['price']) {
                    $product['old_price'] = Tools::displayPrice(Tools::convertPriceFull((float)$oldPrice, $currency_from, $currency), $currency);
                }
                $product['link'] = $context->link->getProductLink($product, null, null, null, null, null, $product['id_product_attribute'] ? $product['id_product_attribute'] : 0);
                $product['name'] = $p->name;
                // Image:
                $image = ($product['id_product_attribute'] && ($image = $this->getCombinationImageById($product['id_product_attribute'], $context->cart->id_lang))) ? $image : Product::getCover($product['id_product']);
                $product['image'] = $context->link->getImageLink($p->link_rewrite, isset($image['id_image']) ? $image['id_image'] : 0, $this->getFormattedName('cart'));
                // Attribute:
                if (!empty($product['id_product_attribute'])) {
                    $p->id_product_attribute = $product['id_product_attribute'];
                    $product['attributes'] = $p->getAttributeCombinationsById($product['id_product_attribute'], $context->cart->id_lang);
                }
                // Other:
                $product['is_available'] = $p->checkQty(1);
                $product['allow_oosp'] = Product::isAvailableWhenOutOfStock($p->out_of_stock);
                $product['product_total'] = Tools::displayPrice(Tools::convertPriceFull((float)$product['price'] * $product['cart_quantity'], $currency_from, $currency), $currency);
                $product['price'] = Tools::displayPrice(Tools::convertPriceFull((float)$product['price'], $currency_from, $currency), $currency);
            } else
                unset($products[$ik]);
            $ik++;
        }
        $this->smarty->assign(array('products' => $products));
        return $this->display(__FILE__, 'bo-products-mini.tpl');
    }

    public function getCombinationImageById($id_product_attribute, $id_lang)
    {
        if (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            if (!Combination::isFeatureActive() || !$id_product_attribute)
                return false;
            $result = EtsAbancartTools::getCombinationImages($id_product_attribute, $id_lang);
            if (!$result)
                return false;
            return $result[0];
        } else
            return Product::getCombinationImageById($id_product_attribute, $id_lang);
    }

    public function getFormattedName($name = false)
    {
        return $this->is17 ? ImageType::getFormattedName($name) : ImageType::getFormatedName($name);
    }

    public function doSmarty($assign = array(), $idLang = 0)
    {
        $tpl_vars = array(
            'option' => $assign,
        );
        if ($idLang) {
            $tpl_vars['trans'] = EtsAbancartDefines::getInstance()->getTrans($idLang);
        }
        $this->smarty->assign($tpl_vars);
        return $this->display(__FILE__, 'bo-fo-smarty.tpl');
    }

    public function encrypt($key)
    {
        $key = md5($key);
        return Tools::substr($key, 5, 5)
            . Tools::substr($key, 3, 3)
            . Tools::substr($key, 4, 4)
            . Tools::substr($key, 20, 3)
            . Tools::substr($key, 15, 2)
            . Tools::substr($key, 23, 3)
            . Tools::substr($key, 29, 2);
    }

    public function getCDN($filepath)
    {
        return $this->context->link->getMediaLink($filepath);
    }

    /**
     * @return false|string
     */
    public function displayText($content = null, $tag, $class = null, $id = null, $href = null, $blank = false, $src = null, $name = null, $value = null, $type = null, $data_id_product = null, $rel = null, $attr_datas = null)
    {
        $this->smarty->assign(
            array(
                'content' => $content,
                'tag' => $tag,
                'class' => $class,
                'id' => $id,
                'href' => $href,
                'blank' => $blank,
                'src' => $src,
                'name' => $name,
                'value' => $value,
                'type' => $type,
                'data_id_product' => $data_id_product,
                'attr_datas' => $attr_datas,
                'rel' => $rel,
            )
        );
        return $this->display(__FILE__, 'html.tpl');
    }

    /*---------------END FRONTEND.---------------*/

    public function hookModuleRoutes()
    {
        $routes = array(
            'module-ets_abandonedcart-lead-empty' => array(
                'controller' => 'lead',
                'rule' => 'lead',
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ets_abandonedcart',
                ),
            ),
            'module-ets_abandonedcart-lead' => array(
                'controller' => 'lead',
                'rule' => 'lead/{url_alias}',
                'keywords' => array(
                    'url_alias' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ets_abandonedcart',
                ),
            ),
            'module-ets_abandonedcart-thank-empty' => array(
                'controller' => 'thank',
                'rule' => 'thank',
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ets_abandonedcart',
                ),
            ),
            'module-ets_abandonedcart-thank' => array(
                'controller' => 'thank',
                'rule' => 'thank/{url_alias}',
                'keywords' => array(
                    'url_alias' => array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'url_alias'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'ets_abandonedcart',
                ),
            )
        );

        return $routes;
    }

    public function getBreadCrumb()
    {
        $nodes = array(
            array(
                'title' => $this->l('Home'),
                'url' => $this->context->link->getPageLink('index', true),
            )
        );

        $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
        $module = ($module = Tools::getValue('module')) && Validate::isCleanHtml($module) ? $module : '';
        $fc = ($fc = Tools::getValue('fc')) && Validate::isCleanHtml($fc) ? $fc : '';
        $alias = ($alias = Tools::getValue('url_alias')) && Validate::isCleanHtml($alias) ? $alias : '';
        if ($controller == 'lead' && $module == $this->name && $fc == 'module' && $alias && ($formItem = EtsAbancartForm::getFormByAlias($alias, $this->context->language->id, true))) {
            $nodes[] = array(
                'title' => $formItem['name'],
                'url' => EtsAbancartForm::getLeadFormUrl(null, $formItem['alias']),
            );
        }
        if ($controller == 'thank' && $module == $this->name && $fc == 'module' && $alias && ($formItem = EtsAbancartForm::getThankyouPageByAlias($alias, $this->context->language->id, true, true))) {
            $nodes[] = array(
                'title' => $formItem['thankyou_page_title'],
                'url' => EtsAbancartForm::getThankyouPageUrl(null, $formItem['thankyou_page_alias']),
            );
        }
        if ($this->is17)
            return array('links' => $nodes, 'count' => count($nodes));
        return $this->displayBreadcrumb($nodes);
    }

    public function displayBreadcrumb($nodes)
    {
        $this->smarty->assign(array('nodes' => $nodes));
        return $this->display(__FILE__, 'breadcrumb_nodes.tpl');
    }

    public function installLinkDefault()
    {
        $metas = array(
            array(
                'controller' => 'cart',
                'title' => $this->l('My shopping carts'),
                'tabname' => 'My shopping cart',
                'url_rewrite' => 'my-shopping-carts',
                'url_rewrite_lang' => $this->l('my-shopping-carts'),
            ),
        );
        $languages = Language::getLanguages(false);
        foreach ($metas as $meta) {
            if (!EtsAbancartTools::getMetaByRewrite($meta['url_rewrite']) && !EtsAbancartTools::getMetaByControllerModule($this->name, $meta['controller'])) {
                $meta_class = new Meta();
                $meta_class->page = 'module-' . $this->name . '-' . $meta['controller'];
                $meta_class->configurable = 1;
                foreach ($languages as $language) {
                    $meta_class->title[$language['id_lang']] = $this->getTextLang($meta['tabname'], $language) ?: $meta['title'];
                    $meta_class->url_rewrite[$language['id_lang']] = ($link_rewrite = $this->getTextLang($meta['url_rewrite_lang'], $language)) ? Tools::link_rewrite($link_rewrite) : $meta['url_rewrite'];
                }
                $meta_class->add();
            }
        }
        return true;
    }

    public function unInstallLinkDefault()
    {
        $metas = array(
            array(
                'controller' => 'cart',
                'title' => $this->l('My shopping carts'),
                'url_rewrite' => 'my-shopping-carts'
            ),
        );
        foreach ($metas as $meta) {
            if ($id_meta = EtsAbancartTools::getMetaIdByControllerModule($this->name, $meta['controller'])) {
                $meta_class = new Meta($id_meta);
                $meta_class->delete();
            }
        }
        return true;
    }

    public function getTextLang($text, $lang, $file_name = '')
    {
        if (is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif (is_object($lang))
            $iso_code = $lang->iso_code;
        else {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
        $modulePath = rtrim(_PS_MODULE_DIR_, '/') . '/' . $this->name;
        $fileTransDir = $modulePath . '/translations/' . $iso_code . '.' . 'php';
        if (!@file_exists($fileTransDir)) {
            return $text;
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $text_tras = preg_replace("/\\\*'/", "\'", $text);
        $strMd5 = md5($text_tras);
        $keyMd5 = '<{' . $this->name . '}prestashop>' . ($file_name ?: $this->name) . '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if ($matches && isset($matches[2])) {
            return $matches[2];
        }
        return $text;
    }

    public function getTextTrans($text)
    {
        $trans = array(
            'Send test mail' => $this->l('Send test mail'),
        );
        if (isset($trans[$text])) {
            return $trans[$text];
        }

        return $text;
    }
}
