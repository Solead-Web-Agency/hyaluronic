<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

define('AR_CONTACTUS_DEBUG', false);

include_once dirname(__FILE__).'/classes/ArContactUsGeneralConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsButtonConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsButtonMobileConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsMenuConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsMenuMobileConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsCallbackConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsTable.php';
include_once dirname(__FILE__).'/classes/ArContactUsPromptTable.php';
include_once dirname(__FILE__).'/classes/ArContactUsCallback.php';
include_once dirname(__FILE__).'/classes/ArContactUsGeneralConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsPromptConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsPromptMobileConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsLiveChatConfig.php';
include_once dirname(__FILE__).'/classes/ArContactUsTools.php';


class ArContactUs extends Module
{
    const REMIND_TO_RATE = 259200; // 3 days
    const ADDONS_ID = 32669;
    
    protected $html;
    protected $configModel;
    protected $buttonMobileConfig;
    protected $buttonConfig;
    protected $menuConfig;
    protected $menuMobileConfig;
    protected $callbackConfig;
    protected $promptConfig;
    protected $promptMobileConfig;
    protected $liveChatConfig;


    protected $securityKey;

    protected $models = null;

    protected $rendered = false;
    
    public $max_image_size;
    
    public $_languages = array();
    public $allow_employee_form_lang;

    public function __construct()
    {
        $this->name = 'arcontactus';
        $this->tab = 'front_office_features';
        $this->version = '1.9.9';
        $this->author = 'Azelab';
        $this->controllers = array('ajax');
        $this->need_instance = 0;
        $this->bootstrap = true;
        if ($this->is17()) {
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        }
        $this->module_key = '899d786e5dec1985a762a1a0faa4b1a2';
        $this->author_address = '0x45c659C9b74aBcDf503f434b8e72FD20c3643bE5';
        parent::__construct();

        $this->displayName = $this->l('All-in-One Messaging');
        $this->description = $this->l('Display contact us button with menu on every page.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete all data?');
        
        $this->configModel = new ArContactUsGeneralConfig($this, 'arcu_');
        $this->buttonConfig = new ArContactUsButtonConfig($this, 'arcub_');
        $this->buttonMobileConfig = new ArContactUsButtonMobileConfig($this, 'arcubm_');
        $this->menuConfig = new ArContactUsMenuConfig($this, 'arcum_');
        $this->menuMobileConfig = new ArContactUsMenuMobileConfig($this, 'arcumm_');
        $this->callbackConfig = new ArContactUsCallbackConfig($this, 'arcuc_');
        $this->promptConfig = new ArContactUsPromptConfig($this, 'arcupr_');
        $this->promptMobileConfig = new ArContactUsPromptMobileConfig($this, 'arcuprm_');
        $this->liveChatConfig = new ArContactUsLiveChatConfig($this, 'arcul_');
        
        $this->configModel->loadFromConfig();
    }
    
    public function getAddonsId()
    {
        return self::ADDONS_ID;
    }
    
    public function getConfigModel()
    {
        if (!$this->configModel->isLoaded()) {
            $this->configModel->loadFromConfig();
        }
        return $this->configModel;
    }
    
    public function hookDisplayBackOfficeOrderActions($params)
    {
        if ($btns = $this->getAdminOrderButtons($params)) {
            return $this->render('admin_order_btn.tpl', array(
                'btns' => $btns
            ));
        }
    }
    
    public function getAdminOrderButtons($params)
    {
        $id_order = $params['id_order'];
        $order = new Order($id_order);
        $delivery = new Address($order->id_address_delivery);
        $invoice = new Address($order->id_address_invoice);
        $idCountry = 0;
        if ($delivery->phone_mobile) {
            $idCountry = $delivery->id_country;
            $phone = $delivery->phone_mobile;
        } elseif ($delivery->phone) {
            $idCountry = $delivery->id_country;
            $phone = $delivery->phone;
        } elseif ($invoice->phone_mobile) {
            $idCountry = $invoice->id_country;
            $phone = $invoice->phone_mobile;
        } elseif ($invoice->phone) {
            $idCountry = $invoice->id_country;
            $phone = $invoice->phone;
        }
        $phone = preg_replace('/W+/is', '', $phone);
        if ($phone && $idCountry) {
            $country = new Country($idCountry);
            if (strpos($phone, $country->call_prefix) !== 0) {
                $phone = $country->call_prefix . $phone;
            }
        }
        if ($phone) {
            return array(
                array(
                    'url' => 'https://wa.me/' . $phone,
                    'icon' => 'icon-whatsapp',
                    'title' => $this->l('Chat via WhatsApp')
                )
            );
        }
    }
    
    /**
     *
     * @return ArContactUsCallbackConfig
     */
    public function getCallbackConfigModel()
    {
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        return $this->callbackConfig;
    }
    
    public function install()
    {
        Configuration::updateValue('ARCU_INSTALL_TS', time());
        $this->updateSecurityKey();
        if (!parent::install()
                || !$this->registerHook('displayHeader')
                || !$this->registerHook('displayFooter')
                || !$this->registerHook('displayAzlCustomFooter')
                || !$this->registerHook('displayProductButtons')
                || !$this->registerHook('displayProductAdditionalInfo')
                || !$this->registerHook('displayAdminNavBarBeforeEnd')
                || !$this->registerHook('displayBeforeBodyClosingTag')
                || !$this->registerHook('displayBackOfficeOrderActions')
                || !$this->installDB()
                || !$this->installTab()
                || !$this->installDefaults()
                || !$this->installMenu()
                || !$this->installPrompts()
            ) {
            return false;
        }
        $this->compilleDesktopCss();
        $this->compilleMobileCss();
        return true;
    }
    
    public function hookDisplayAdminNavBarBeforeEnd($params)
    {
        $moduleConfig = false;
        $controller = $this->context->controller;
        if ($controller instanceof AdminModulesController || $controller instanceof AdminModulesControllerCore) {
            if (Tools::getValue('configure') == $this->name) {
                $moduleConfig = true;
            }
        }
        $adminOrderButtons = array();
        if ($controller instanceof AdminOrdersController || $controller instanceof AdminOrdersControllerCore) {
            $adminOrderButtons = $this->getAdminOrderButtons(array(
                'id_order' => Tools::getValue('id_order')
            ));
        }
        return $this->render('admin_head.tpl', array(
            'path' => $this->getPath(),
            'orderButtons' => $adminOrderButtons,
            'moduleConfig' => $moduleConfig,
            'is16' => $this->is16(),
            'is17' => $this->is17()
        ));
        return null;
    }
    
    public function hookActionDeleteGDPRCustomer($customer)
    {
        $id_customer = null;
        if (isset($customer['id'])) {
            $id_customer = $customer['id'];
        } elseif (isset($customer['id_customer'])) {
            $id_customer = $customer['id_customer'];
        }
        if ($id_customer) {
            $sql = "DELETE FROM `"._DB_PREFIX_."arcontactus_callback` WHERE id_user = '".((int)$id_customer)."'";
            if (Db::getInstance()->Execute($sql)) {
                return ArContactUsTools::jsonEncode(true);
            }
            return ArContactUsTools::jsonEncode($this->l('Contact Us module : Unable to delete customer.'));
        }
    }
    
    public function hookActionExportGDPRData($customer)
    {
        $id_customer = null;
        if (isset($customer['id'])) {
            $id_customer = $customer['id'];
        } elseif (isset($customer['id_customer'])) {
            $id_customer = $customer['id_customer'];
        }
        if ($id_customer) {
            $sql = "SELECT * FROM `"._DB_PREFIX_."arcontactus_callback` WHERE id_user = '".((int)$id_customer)."'";
            if ($res = Db::getInstance()->ExecuteS($sql)) {
                return ArContactUsTools::jsonEncode($res);
            }
            return ArContactUsTools::jsonEncode($this->l('Contact Us module : Unable to export customer data.'));
        }
    }
    
    public function installDB()
    {
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus` (
                `id_contactus` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `icon` TEXT NULL DEFAULT NULL,
                `color` VARCHAR(10) NULL DEFAULT NULL,
                `type` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `display` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `link` VARCHAR(255) NULL DEFAULT NULL,
                `integration` VARCHAR(50) NULL DEFAULT NULL,
                `js` TEXT NULL,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `registered_only` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `target` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `product_page` TINYINT(3) UNSIGNED NULL DEFAULT "0",
                `always` TINYINT(3) UNSIGNED NULL DEFAULT "1",
                `d1` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d2` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d3` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d4` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d5` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d6` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `d7` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `time_from` TIME NULL DEFAULT NULL,
                `time_to` TIME NULL DEFAULT NULL,
                `position` INT(10) UNSIGNED NULL DEFAULT "0",
                `id_shop` INT(10) UNSIGNED NULL DEFAULT "0",
                `data` TEXT NULL,
                PRIMARY KEY (`id_contactus`),
                INDEX `position` (`position`),
                INDEX `id_shop` (`id_shop`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_callback` (
                `id_callback` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_user` INT(10) UNSIGNED NULL DEFAULT NULL,
                `phone` VARCHAR(50) NULL DEFAULT NULL,
                `name` VARCHAR(255) NULL DEFAULT NULL,
                `email` VARCHAR(255) NULL DEFAULT NULL,
                `referer` VARCHAR(255) NULL DEFAULT NULL,
                `created_at` DATETIME NULL DEFAULT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `comment` TEXT NULL,
                `id_shop` INT(10) UNSIGNED NULL DEFAULT "0",
                `checked` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id_callback`),
                INDEX `id_user` (`id_user`),
                INDEX `phone` (`phone`),
                INDEX `id_shop` (`id_shop`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_lang` (
                `id_contactus` INT(11) NOT NULL,
                `id_lang` INT(11) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `subtitle` VARCHAR(255) NULL DEFAULT NULL,
                PRIMARY KEY (`id_contactus`, `id_lang`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt` (
                `id_prompt` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `status` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
                `position` INT(10) UNSIGNED NULL DEFAULT NULL,
                `id_shop` INT(10) UNSIGNED NULL DEFAULT "0",
                PRIMARY KEY (`id_prompt`),
                INDEX `id_shop` (`id_shop`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'arcontactus_prompt_lang` (
                `id_prompt` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_lang` INT(11) UNSIGNED NOT NULL,
                `message` TEXT NULL,
                PRIMARY KEY (`id_prompt`, `id_lang`)
            )
            ENGINE=' . _MYSQL_ENGINE_ . ' COLLATE=utf8_general_ci;');
        
        return true;
    }
    
    public function installMenu()
    {
        $menu = array(
            array(
                'icon' => 'facebook-messenger',
                'color' => '#0084ff',
                'link' => 'https://m.me/areamaDevelopment',
                'title' => $this->l('Facebook messenger'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'whatsapp',
                'color' => '#1ebea5',
                'link' => 'https://web.whatsapp.com/send?l=en&phone=34640563252',
                'title' => $this->l('WhatsApp'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'viber',
                'color' => '#7c529d',
                'link' => 'viber://pa?chatURI=areama',
                'title' => $this->l('Viber'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'telegram-plane',
                'color' => '#2ca5e0',
                'link' => 'https://t.me/areama',
                'title' => $this->l('Telegram'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'skype',
                'color' => '#31c4ed',
                'link' => 'https://join.skype.com/bot/80924817-9809-4b5a-8941-8474cbd414',
                'title' => $this->l('Skype'),
                'display' => 1,
                'always' => 1,
                'status' => 0,
                'type' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'envelope',
                'color' => '#ff8400',
                'link' => 'mailto:support@myshop.com',
                'title' => $this->l('Send an email'),
                'display' => 1,
                'always' => 1,
                'type' => 0,
                'status' => 0,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'comments',
                'color' => '#7eb105',
                'link' => '{contact}',
                'title' => $this->l('Message to contact form'),
                'display' => 1,
                'target' => 1,
                'always' => 1,
                'type' => 0,
                'status' => 1,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            ),
            array(
                'icon' => 'phone',
                'color' => '#54cd81',
                'link' => '{callback}',
                'title' => $this->l('Call me back'),
                'display' => 1,
                'always' => 1,
                'type' => 3,
                'status' => 1,
                'registered_only' => 0,
                'data' => '{"enable_qr":0,"qr_title":{"1":""},"qr_link":"","icon_type":"builtin","icon_svg":"","no_container":0}'
            )
        );
        
        foreach ($menu as $k => $item) {
            $model = new ArContactUsTable();
            $model->icon = $item['icon'];
            $model->color = $item['color'];
            $model->link = $item['link'];
            $model->title = $item['title'];
            $model->status = $item['status'];
            $model->registered_only = $item['registered_only'];
            $model->display = $item['display'];
            $model->always = $item['always'];
            $model->type = $item['type'];
            $model->target = isset($item['target'])? $item['target'] : 0;
            $model->position = $k;
            $model->data = $item['data'];
            $model->save();
        }
        return true;
    }
    
    public function installPrompts()
    {
        $items = array(
            array(
                'message' => $this->l('Hello!'),
                'status' => 0
            ),
            array(
                'message' =>  $this->l('Have a questions?'),
                'status' => 0
            ),
            array(
                'message' =>  $this->l('Please use this button to contact us!'),
                'status' => 0
            ),
        );
        
        foreach ($items as $k => $item) {
            $model = new ArContactUsPromptTable();
            $model->message = $item['message'];
            $model->status = $item['status'];
            $model->position = $k;
            $model->save();
        }
        return true;
    }
    
    public function uninstallDb()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_lang`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_callback`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_prompt`');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'arcontactus_prompt_lang`');
        return true;
    }
    
    public function installTab()
    {
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminArContactUs';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminArContactUs';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        $res = $tab->add();
        
        // Prepare tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminArContactUsPrompt';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminArContactUsPrompt';
        }
        $tab->id_parent = -1;
        $tab->module = $this->name;
        $res = $res && $tab->add();
        
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminArCu';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Callbacks';
        }
        if ($this->is17()) {
            $parentId = Tab::getIdFromClassName('CONFIGURE');
            $tab->id_parent = $parentId;
            if (property_exists($tab, 'icon')) {
                $tab->icon = 'link';
            }
        } else {
            $tab->id_parent = 0;
        }
        $tab->module = $this->name;
        return $res && $tab->add();
    }
    
    public function reset()
    {
        return $this->installDefaults();
    }
    
    public function uninstall()
    {
        return parent::uninstall() && $this->clearConfig() && $this->uninstallDb();
    }
    
    protected function clearConfig()
    {
        foreach ($this->getForms() as $model) {
            $model->clearConfig();
        }
        return true;
    }
    
    public function getModels($id_lang, $day, $time, $product_page = false)
    {
        $id_shop = Context::getContext()->shop->id;
        if ($product_page) {
            if (Context::getContext()->customer->id) {
                $logged = true;
            } else {
                $logged = false;
            }
            return ArContactUsTable::getAll($id_lang, false, $day, $time, $logged, $product_page, $id_shop);
        }
        if ($this->models === null) {
            if (Context::getContext()->customer->id) {
                $logged = true;
            } else {
                $logged = false;
            }
            $this->models = ArContactUsTable::getAll($id_lang, true, $day, $time, $logged, $product_page, $id_shop);
        }
        return $this->models;
    }
    
    public function hookDisplayAzlCustomFooter($params)
    {
        return $this->renderButton($params);
    }
    
    public function hookBlockFooter1($params)
    {
        return $this->renderButton($params);
    }
    
    public function hookFooterBlock1($params)
    {
        return $this->renderButton($params);
    }
    
    public function composeURL($url)
    {
        $replacements = array();
        if (preg_match_all('/{(.*?)}/is', $url, $variables)) {
            if (isset($variables[1])) {
                foreach ($variables[1] as $var) {
                    $replacements["{{$var}}"] = $this->replaceContent($var, Context::getContext());
                }
            }
        }
        $res = strtr($url, $replacements);
        return trim($res);
    }
    
    public function replaceContent($source, $params)
    {
        $variable = null;
        $default = "{{$source}}";
        if (strpos($source, '|') !== false) {
            $data = explode('|', $source);
            $source = $data[0];
            $default = $data[1];
        }
        if (strpos($source, '.') !== false) {
            $var = explode('.', $source);
            $variable = null;
            
            foreach ($var as $k => $v) {
                if ($k == 0 && isset($params->$v)) {
                    $variable = $params->$v;
                } elseif ($k == 0 && !isset($params->$v)) {
                    return $default;
                } elseif ($k > 0 && $variable && is_array($variable) && isset($variable[$v])) {
                    $variable = $variable[$v];
                } elseif ($k > 0 && $variable && is_array($variable) && !isset($variable[$v])) {
                    return $default;
                } elseif ($k > 0 && $variable && is_object($variable) && isset($variable->$v)) {
                    $variable = $variable->$v;
                } elseif ($k > 0 && $variable && is_object($variable) && !isset($variable->$v)) {
                    return $default;
                }
            }
            return $variable;
        } elseif (isset($params->$source) && $params->$source) {
            return $params[$source];
        } else {
            $methodName = 'urlVar' . Tools::ucfirst($source);
            if (method_exists($this, $methodName)) {
                return $this->$methodName();
            }
        }
        return $default;
    }
    
    public function urlVarCurrentUrlNoEncode()
    {
        return Tools::getHttpHost(true) . str_replace('//', '/', __PS_BASE_URI__ . $_SERVER['REQUEST_URI']);
    }
    
    public function urlVarCurrentUrl()
    {
        return urlencode(Tools::getHttpHost(true) . str_replace('//', '/', __PS_BASE_URI__ . $_SERVER['REQUEST_URI']));
    }
    
    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->hookDisplayProductButtons($params);
    }
    
    public function hookDisplayAfterProductThumbs($params)
    {
        return $this->hookDisplayProductButtons($params);
    }

    public function hookDisplayProductButtons($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        $id_lang = Context::getContext()->language->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time, true);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        if (!$this->configModel->desktop && !$this->isMobile()) {
            return null;
        }
        $items = array();
        foreach ($models as $model) {
            if ($model['display'] == 1 || ($model['display'] == 2 && !$this->isMobile()) || ($model['display'] == 3 && $this->isMobile())) {
                $item = array(
                    'href' => $model['link'] == '{callback}'? 'callback' : $this->composeURL($model['url']),
                    'target' => $model['target'] == '0'? '_blank' : '_self',
                    'color' => $model['color'],
                    'title' => $model['title'],
                    'subtitle' => $model['subtitle'],
                    'type' => $model['type'],
                    'integration' => $model['integration'],
                    'id' => 'msg-item-' . $model['id_contactus'],
                    'class' => 'msg-item-' . $model['icon'] . ' ' . ArContactUsTable::getDisplayClass($model['display']),
                    'js' => str_replace('\\', '', $model['js']),
                    'icon' => ArContactUsAbstract::getIcon($model['icon'])
                );
                $items[] = $item;
            }
        }
        $this->smarty->assign(array(
            'path' => $this->getPath(),
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'securityKey' => Configuration::get('arcukey'),
            'items' => $items
        ));
        return $this->display(__FILE__, 'product_actions.tpl');
    }

    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if ($this->configModel->button_hook == 'displayBeforeBodyClosingTag') {
            return $this->renderButton($params);
        }
        return null;
    }
    
    public function hookFooter($params)
    {
        if ($this->configModel->button_hook == 'displayFooter') {
            return $this->renderButton($params);
        }
        return null;
    }
    
    public function renderButton($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        if (!$this->configModel->desktop && !$this->isMobile()) {
            return null;
        }
        
        $this->callbackConfig->message[$id_lang] = nl2br($this->callbackConfig->message[$id_lang]);
        $this->callbackConfig->success_message[$id_lang] = nl2br($this->callbackConfig->success_message[$id_lang]);
        $this->callbackConfig->fail_message[$id_lang] = nl2br($this->callbackConfig->fail_message[$id_lang]);
        $this->callbackConfig->proccess_message[$id_lang] = nl2br($this->callbackConfig->proccess_message[$id_lang]);
        
        $prompts = ArContactUsPromptTable::getAll($id_lang, true, $id_shop);
        $messages = array();
        foreach ($prompts as $prompt) {
            $messages[] = nl2br($prompt['message']);
        }
        $items = array();
        
        $facebook = false;
        $vk = false;
        $intercom = false;
        $tawkto = false;
        $crisp = false;
        $skype = false;
        $zopim = false;
        $zalo = false;
        $lhc = false;
        $ss = false;
        $lc = false;
        $tidio = false;
        $lcp = false;
        $liveZilla = false;
        $jivosite = false;
        $zoho = false;
        $freshChat = false;
        $phpLive = false;
        $paldesk = false;
        $hubspot = false;
        $socialintents = false;
        $botmake = false;
        $currentGroup = Group::getCurrent();
        
        foreach ($models as $model) {
            if ($model['display'] == 1 || ($model['display'] == 2 && !$this->isMobile()) || ($model['display'] == 3 && $this->isMobile())) {
                $adData = ArContactUsTools::jsonDecode($model['data'], true);
                if (isset($adData['groups']) && !empty($adData['groups']) && is_array($adData['groups']) && !in_array($currentGroup->id, $adData['groups'])) {
                    continue;
                }
                $link = $model['link'] == '{callback}'? 'callback' : $this->composeURL($model['url']);
                $item = array(
                    'href' => $link,
                    'target' => $model['target'] == '0'? '_blank' : '_self',
                    'color' => $model['color'],
                    'title' => $model['title'],
                    'subtitle' => $model['subtitle'],
                    'type' => $model['type'],
                    'integration' => $model['integration'],
                    'id' => 'msg-item-' . $model['id_contactus'],
                    'class' => 'msg-item-' . $model['icon'] . ' ' . ArContactUsTable::getDisplayClass($model['display']),
                    'js' => str_replace('\\', '', $model['js']),
                    'icon' => ArContactUsTable::getIcon($model['icon'], $model['data']),
                    'enable_qr' => $adData['enable_qr'],
                    'qr_link' => empty($adData['qr_link'])? $link : $adData['qr_link'],
                    'qr_title' => empty($adData['qr_title'][$id_lang])? $model['title'] : $adData['qr_title'][$id_lang],
                    'no_container' => isset($adData['no_container'])? (int)$adData['no_container'] : 0
                );
                $items[] = $item;
                if ($model['type'] == ArContactUsTable::TYPE_INTEGRATION) {
                    switch ($model['integration']) {
                        case 'facebook':
                            $facebook = true;
                            break;
                        case 'vk':
                            $vk = true;
                            break;
                        case 'tawkto':
                            $tawkto = true;
                            break;
                        case 'crisp':
                            $crisp = true;
                            break;
                        case 'intercom':
                            $intercom = true;
                            break;
                        case 'zopim':
                            $zopim = true;
                            break;
                        case 'skype':
                            $skype = true;
                            break;
                        case 'zalo':
                            $zalo = true;
                            break;
                        case 'lhc':
                            $lhc = true;
                            break;
                        case 'smartsupp':
                            $ss = true;
                            break;
                        case 'livechat':
                            $lc = true;
                            break;
                        case 'tidio':
                            $tidio = true;
                            break;
                        case 'livechatpro':
                            $lcp = true;
                            break;
                        case 'livezilla':
                            $liveZilla = true;
                            break;
                        case 'jivosite':
                            $jivosite = true;
                            break;
                        case 'zoho':
                            $zoho = true;
                            break;
                        case 'fc':
                            $freshChat = true;
                            break;
                        case 'phplive':
                            $phpLive = true;
                            break;
                        case 'paldesk':
                            $paldesk = true;
                            break;
                        case 'hubspot':
                            $hubspot = true;
                            break;
                        case 'socialintents':
                            $socialintents = true;
                            break;
                        case 'botmake':
                            $botmake = true;
                            break;
                    }
                }
            }
        }
        
        $mode = 'regular';
        if ($this->isMobile()) {
            if (($this->buttonMobileConfig->mode == 'regular' || empty($this->buttonMobileConfig->mode)) && count($items) == 1) {
                $mode = 'single';
            } else {
                $mode = $this->buttonMobileConfig->mode? $this->buttonMobileConfig->mode : 'regular';
            }
        } else {
            if (($this->buttonConfig->mode == 'regular' || empty($this->buttonConfig->mode)) && count($items) == 1) {
                $mode = 'single';
            } else {
                $mode = $this->buttonConfig->mode? $this->buttonConfig->mode : 'regular';
            }
        }
        
        $this->smarty->assign(array(
            'config' => $this->configModel,
            'buttonConfig' => $this->isMobile()? $this->buttonMobileConfig : $this->buttonConfig,
            'menuConfig' => $this->isMobile()? $this->menuMobileConfig : $this->menuConfig,
            'popupConfig' => $this->callbackConfig,
            'promptConfig' => $this->isMobile()? $this->promptMobileConfig : $this->promptConfig,
            'liveChatConfig' => $this->liveChatConfig,
            'tawkToIntegrated' => $this->liveChatConfig->isTawkToIntegrated() && $tawkto,
            'crispIntegrated' => $this->liveChatConfig->isCrispIntegrated() && $crisp,
            'intercomIntegrated' => $this->liveChatConfig->isIntercomIntegrated() && $intercom,
            'facebookIntegrated' => $this->liveChatConfig->isFacebookChatIntegrated() && $facebook,
            'vkIntegrated' => $this->liveChatConfig->isVkIntegrated() && $vk,
            'zopimIntegrated' => $this->liveChatConfig->isZopimIntegrated() && $zopim,
            'isZendesk' => $this->liveChatConfig->isZendeskChat(),
            'skypeIntegrated' => $this->liveChatConfig->isSkypeIntegrated() && $skype,
            'zaloIntegrated' => $this->liveChatConfig->isZaloIntegrated() && $zalo,
            'lhcIntegrated' => $this->liveChatConfig->isLhcIntegrated() && $lhc,
            'ssIntegrated' => $this->liveChatConfig->isSmartsuppIntegrated() && $ss,
            'lcIntegrated' => $this->liveChatConfig->isLiveChatIntegrated() && $lc,
            'tidioIntegrated' => $this->liveChatConfig->isTidioIntegrated() && $tidio,
            'botmake' => $this->liveChatConfig->isBotmaketIntegrated() && $botmake,
            'lcp' => $this->liveChatConfig->isLiveChatProIntegrated() && $lcp,
            'liveZilla' => $this->liveChatConfig->isLiveZillaIntegrated() && $liveZilla,
            'jivosite' => $this->liveChatConfig->isJivositeIntegrated() && $jivosite,
            'zoho' => $this->liveChatConfig->isZohoIntegrated() && $zoho,
            'freshChat' => $this->liveChatConfig->isFreshChatIntegrated() && $freshChat,
            'phplive' => $this->liveChatConfig->isPhpLiveIntegrated() && $phpLive,
            'paldesk' => $this->liveChatConfig->isPaldeskIntegrated() && $paldesk,
            'hubspot' => $this->liveChatConfig->isHubSpotIntegrated() && $hubspot,
            'socialintents' => $this->liveChatConfig->isSocialintentsIntegrated() && $socialintents,
            
            'buttonIcon' => ArContactUsAbstract::getIcon($this->isMobile()? $this->buttonMobileConfig->button_icon : $this->buttonConfig->button_icon),
            'messages' => ArContactUsTools::jsonEncode($messages),
            'messagesCount' => count($messages),
            'id_lang' => $id_lang,
            'items' => $items,
            'modelsCount' => count($items),
            'mode' => $mode,
            'path' => $this->getPath(),
            'uploadsUrl' => $this->getUploadsUrl(),
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'securityKey' => Configuration::get('arcukey'),
            'customer' => Context::getContext()->customer
        ));
        return $this->display(__FILE__, 'footer.tpl');
    }
    
    public function isReCaptchaIntegrated()
    {
        return $this->getCallbackConfigModel()->recaptcha && $this->getCallbackConfigModel()->key && $this->getCallbackConfigModel()->secret;
    }
    
    public function hookAzlAmpHeader($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        $id_lang = Context::getContext()->language->id;
        
        $js = array();
        $css = array();
        
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time);
        if (count($models) == 0) {
            return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        $useReCaptcha = false;
        $this->callbackConfig->loadFromConfig();
        if ($this->isReCaptchaIntegrated()) {
            $js[] = 'https://www.google.com/recaptcha/api.js?render=' . $this->callbackConfig->key;
            $useReCaptcha = true;
        }
        $this->callbackConfig->loadFromConfig();
        if ($this->callbackConfig->phone_mask_on) {
            $js[] = $this->_path.'views/js/jquery.maskedinput.min.js';
        }
        
        $css[] = $this->_path.'views/css/jquery.contactus.min.css';
        
        if ($this->isMobile()) {
            $css[] = $this->_path.'views/css/generated-mobile.css';
        } else {
            $css[] = $this->_path.'views/css/generated-desktop.css';
        }
        $this->buttonConfig->loadFromConfig();
        if ($this->isMobile()) {
            $this->menuMobileConfig->loadFromConfig();
            $this->promptMobileConfig->loadFromConfig();
        } else {
            $this->menuConfig->loadFromConfig();
            $this->promptConfig->loadFromConfig();
        }
        
        if ($this->menuConfig->menu_style == 1) {
            $js[]= $this->_path.'views/js/snap.svg-min.js';
        }
        $js[]= $this->_path.'views/js/jquery.contactus.js';
        $js[] = $this->_path.'views/js/scripts.js';
        
        $this->liveChatConfig->loadFromConfig();
        
        $this->smarty->assign(array(
            'config' => $this->configModel,
            'buttonConfig' => $this->buttonConfig,
            'menuConfig' => $this->isMobile()? $this->menuMobileConfig : $this->menuConfig,
            'callbackConfig' => $this->callbackConfig,
            'promptConfig' => $this->isMobile()? $this->promptMobileConfig : $this->promptConfig,
            'liveChatConfig' => $this->liveChatConfig,
            'id_lang' => Context::getContext()->language->id,
            'models' => $models,
            'modelsCount' => count($models),
            'path' => $this->getPath(),
            'useReCaptcha' => $useReCaptcha,
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'ajaxUrl' => $this->getFrontAjaxUrl(),
            'amp' => true,
            'js' => $js,
            'css' => $css
        ));
        
        return $this->display(__FILE__, 'head.tpl', $this->getCacheId());
    }
    
    public function hookDisplayHeader($params)
    {
        if ($this->configModel->sandbox) {
            $ips = explode("\r\n", $this->configModel->allowed_ips);
            if (!in_array($this->configModel->getCurrentIP(), $ips)) {
                return null;
            }
        }
        $id_lang = Context::getContext()->language->id;
        $day = date('N');
        $time = date('H:i:s');
        $models = $this->getModels($id_lang, $day, $time);
        if (count($models) == 0) {
            //return null;
        }
        if (!$this->configModel->mobile && $this->isMobile()) {
            return null;
        }
        if (!$this->configModel->desktop && !$this->isMobile()) {
            return null;
        }
        $useReCaptcha = false;
        $this->callbackConfig->loadFromConfig();
        if ($this->isReCaptchaIntegrated()) {
            if ($this->is17() && method_exists(Context::getContext()->controller, 'registerJavascript')) {
                Context::getContext()->controller->registerJavascript(
                    'remote-g-recaptcha',
                    'https://www.google.com/recaptcha/api.js?render=' . $this->callbackConfig->key,
                    array('server' => 'remote', 'position' => 'bottom', 'priority' => 50)
                );
            } else {
                Context::getContext()->controller->addJS('https://www.google.com/recaptcha/api.js?render=' . $this->callbackConfig->key);
            }
            $useReCaptcha = true;
        }
        $this->callbackConfig->loadFromConfig();
        if ($this->callbackConfig->phone_mask_on && $this->callbackConfig->maskedinput) {
            Context::getContext()->controller->addJS($this->_path.'views/js/jquery.maskedinput.min.js');
        }
        
        if ($this->configModel->font_awesome) {
            Context::getContext()->controller->addCSS($this->_path.'views/css/fontawesome-all.min.css');
        }
        
        Context::getContext()->controller->addCSS($this->_path.'views/css/jquery.contactus.min.css');
        if ($this->isMobile()) {
            Context::getContext()->controller->addCSS($this->_path.'views/css/generated-mobile.css');
        } else {
            Context::getContext()->controller->addCSS($this->_path.'views/css/generated-desktop.css');
        }
        
        if ($this->isMobile()) {
            $this->menuMobileConfig->loadFromConfig();
            $this->buttonMobileConfig->loadFromConfig();
            if ($this->buttonMobileConfig->drag) {
                Context::getContext()->controller->addJqueryUI('ui.draggable');
            }
            $this->promptMobileConfig->loadFromConfig();
        } else {
            $this->buttonConfig->loadFromConfig();
            $this->menuConfig->loadFromConfig();
            if ($this->buttonConfig->drag) {
                Context::getContext()->controller->addJqueryUI('ui.draggable');
            }
            $this->promptConfig->loadFromConfig();
        }
        
        if ($this->menuConfig->menu_style == 1) {
            Context::getContext()->controller->addJS($this->_path.'views/js/snap.svg-min.js');
        }
        
        Context::getContext()->controller->addJS($this->_path.'views/js/jquery.contactus.js');
        Context::getContext()->controller->addJS($this->_path.'views/js/scripts.js');
        
        $this->liveChatConfig->loadFromConfig();
        
        $this->smarty->assign(array(
            'config' => $this->configModel,
            'buttonConfig' => $this->isMobile()? $this->buttonMobileConfig : $this->buttonConfig,
            'menuConfig' => $this->isMobile()? $this->menuMobileConfig : $this->menuConfig,
            'callbackConfig' => $this->callbackConfig,
            'promptConfig' => $this->isMobile()? $this->promptMobileConfig : $this->promptConfig,
            'liveChatConfig' => $this->liveChatConfig,
            'id_lang' => Context::getContext()->language->id,
            'models' => $models,
            'modelsCount' => count($models),
            'path' => $this->getPath(),
            'useReCaptcha' => $useReCaptcha,
            'isMobile' => $this->isMobile(),
            'is17' => $this->is17(),
            'ajaxUrl' => $this->getFrontAjaxUrl(),
            'amp' => false
        ));
        
        $html = $this->display(__FILE__, 'head.tpl', $this->getCacheId());
        
        if ($this->configModel->button_hook == 'displayHeader') {
            $html .= $this->renderButton($params);
        }
        
        return $html;
    }
    
    protected function getFrontAjaxUrl()
    {
        return Context::getContext()->link->getModuleLink($this->name, 'ajax');
    }


    protected function getCacheId($name = null)
    {
        $id = parent::getCacheId($name);
        return $id . '|' . $this->isMobile();
    }

    public function isMobile()
    {
        return Context::getContext()->getMobileDetect()->isMobile() || Context::getContext()->getMobileDetect()->isTablet();
    }
    
    protected function installDefaults()
    {
        foreach ($this->getForms() as $model) {
            $model->loadDefaults();
            $model->saveToConfig(false);
        }
        return true;
    }
    
    public function getForms()
    {
        return array(
            $this->configModel,
            $this->buttonConfig,
            $this->buttonMobileConfig,
            $this->menuConfig,
            $this->menuMobileConfig,
            $this->callbackConfig,
            $this->promptConfig,
            $this->promptMobileConfig,
            $this->liveChatConfig
        );
    }
    
    public function getContent()
    {
        if ($this->isSubmit()) {
            if ($this->postValidate()) {
                $this->postProcess();
            }
        }
        Context::getContext()->controller->addJqueryPlugin('tablednd');
        Context::getContext()->controller->addJS($this->_path.'views/js/jquery.maskedinput.min.js');
        Context::getContext()->controller->addJS($this->_path.'views/js/moment.min.js');
        Context::getContext()->controller->addCss($this->_path.'views/css/admin.css');
        Context::getContext()->controller->addCss($this->_path.'views/css/fontawesome-all.min.css');
        Context::getContext()->controller->addJS($this->_path.'views/js/admin.js');
        $this->html .= $this->renderForm();
        return $this->html;
    }
    
    public function isSubmit()
    {
        foreach ($this->getAllowedSubmits() as $submit) {
            if (Tools::isSubmit($submit)) {
                return true;
            }
        }
    }
    
    public function getAllowedSubmits()
    {
        $submits = array();
        foreach ($this->getForms() as $model) {
            $submits[] = get_class($model);
        }
        return $submits;
    }
    
    public function postProcess()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->populate();
                if ($model->saveToConfig()) {
                    $this->compilleDesktopCss();
                    $this->compilleMobileCss();
                    $this->html .= $this->displayConfirmation($this->l('Settings updated'));
                } else {
                    $this->postValidate();
                }
            }
        }
        $this->updateSecurityKey();
        $this->clearCache();
    }
    
    public function updateSecurityKey()
    {
        Configuration::updateValue('arcukey', Tools::passwdGen(8));
    }
    
    public function postValidate()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                $model->loadFromConfig();
                $model->populate();
                if (!$model->validate()) {
                    foreach ($model->getErrors() as $errors) {
                        foreach ($errors as $error) {
                            $this->html .= $this->displayError($error);
                        }
                    }
                    return false;
                }
                return true;
            }
        }
    }
    
    public function compilleDesktopCss()
    {
        if (!$this->menuConfig->isLoaded()) {
            $this->menuConfig->loadFromConfig();
        }
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        if (!$this->buttonConfig->isLoaded()) {
            $this->buttonConfig->loadFromConfig();
        }
        if (!$this->configModel->isLoaded()) {
            $this->configModel->loadFromConfig();
        }
        $content = $this->render('styles.tpl', array(
            'menuConfig' => $this->menuConfig,
            'callbackConfig' => $this->callbackConfig,
            'buttonConfig' => $this->buttonConfig,
            'isMobile' => false,
            'customCss' => $this->configModel->desktop_css
        ));
        $content = preg_replace('/\s+/is', ' ', $content);
        $content = str_replace(array('; }'), '}', $content);
        $content = str_replace(array('{ '), '{', $content);
        
        if (is_writable($this->getPath(true) . 'views/css/generated-desktop.css')) {
            file_put_contents($this->getPath(true) . 'views/css/generated-desktop.css', $content);
            Configuration::updateValue('arcu_css_generated', time());
            $this->clearCache();
        }
    }
    
    public function compilleMobileCss()
    {
        if (!$this->menuMobileConfig->isLoaded()) {
            $this->menuMobileConfig->loadFromConfig();
        }
        if (!$this->callbackConfig->isLoaded()) {
            $this->callbackConfig->loadFromConfig();
        }
        if (!$this->buttonMobileConfig->isLoaded()) {
            $this->buttonMobileConfig->loadFromConfig();
        }
        if (!$this->configModel->isLoaded()) {
            $this->configModel->loadFromConfig();
        }
        $content = $this->render('styles.tpl', array(
            'menuConfig' => $this->menuMobileConfig,
            'callbackConfig' => $this->callbackConfig,
            'buttonConfig' => $this->buttonMobileConfig,
            'isMobile' => true,
            'customCss' => $this->configModel->mobile_css
        ));
        $content = preg_replace('/\s+/is', ' ', $content);
        $content = str_replace(array('; }'), '}', $content);
        $content = str_replace(array('{ '), '{', $content);
        if (is_writable($this->getPath(true) . 'views/css/generated-mobile.css')) {
            file_put_contents($this->getPath(true) . 'views/css/generated-mobile.css', $content);
            Configuration::updateValue('arcu_css_generated', time());
            $this->clearCache();
        }
    }
    
    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->getLanguages(),
            'id_language' => $this->context->language->id,
            'path' => $this->getPath(),
        );
        $helper->base_folder =  dirname(__FILE__);
        $helper->base_tpl = '/views/templates/admin/arcontactus/helpers/form/form.tpl';
        $id_lang = Context::getContext()->language->id;
        
        $models = ArContactUsTable::getAll($id_lang);
        
        foreach ($models as $k => $v) {
            $models[$k]['data'] = ArContactUsTools::jsonDecode($v['data'], true);
        }
        
        $iconImageUploader = new HelperUploader('arcontactus_uploaded_img');
        $iconImageUploader
                ->setMultiple(false)
                ->setTitle($this->l('Select file'))
                ->setUseAjax(true)
                ->setMaxFiles(1)
                ->setAcceptTypes(array('jpeg', 'gif', 'png', 'jpg', 'svg'))
                ->setTemplateDirectory($this->getPath(true) . 'views/templates/admin/arcontactus/helpers/uploader/')
                ->setUrl(Context::getContext()->link->getAdminLink('AdminArContactUs').'&ajax=1&action=uploadCustomImage');
        
        $this->smarty->assign(array(
            'form' => $helper,
            'models' => $models,
            'groups' => Group::getGroups($id_lang),
            'promptModels' => ArContactUsPromptTable::getAll($id_lang),
            'formParams' => array($this->getForm($this->configModel)),
            'buttonFormParams' => array($this->getForm($this->buttonConfig)),
            'buttonMobileFormParams' => array($this->getForm($this->buttonMobileConfig)),
            'menuFormParams' => array($this->getForm($this->menuConfig)),
            'menuMobileFormParams' => array($this->getForm($this->menuMobileConfig)),
            'callbackFormParams' => array($this->getForm($this->callbackConfig)),
            'liveChatFormParams' => array($this->getForm($this->liveChatConfig)),
            'promptFormParams' => array($this->getForm($this->promptConfig)),
            'promptMobileFormParams' => array($this->getForm($this->promptMobileConfig)),
            'languages' => $this->getLanguages(),
            'defaultFormLanguage' => (int)(Configuration::get('PS_LANG_DEFAULT')),
            'icons' => ArContactUsAbstract::getIcons(),
            'onesignalInstalled' => $this->isOnesignalInstalled(),
            'onesignalConfigLink' => $this->context->link->getAdminLink('AdminModules', true).'&configure='
                .'onesignal'.'&tab_module='.$this->tab.'&module_name=onesignal',
            'callbacks' => array(),
            'integrations' => $this->liveChatConfig->getIntegrations(),
            'link' => $this->context->link,
            'path' => $this->getPath(),
            'name' => $this->displayName,
            'currentTime' => date('H:i:s'),
            'version' => $this->version,
            'active_tab' => $this->getActiveTab(),
            'shops' => Shop::getShops(false),
            'multishop' => Shop::isFeatureActive(),
            'link' => Context::getContext()->link,
            'iconImageUploader' => $iconImageUploader->render(),
            'max_image_size' => (int)$this->max_image_size,
            'maxImageSize' => $this->formatBytes((int)$this->max_image_size),
        ));
        return $this->display(__FILE__, 'config.tpl');
    }
    
    public function isOnesignalInstalled()
    {
        if (Module::isInstalled('onesignal')) {
            $module = Module::getInstanceByName('onesignal');
            $enabled = Module::isEnabled('onesignal');
            if ($enabled && version_compare($module->version, '1.5.0', '>=')) {
                return true;
            }
        }
        return false;
    }
    
    public function getActiveTab()
    {
        foreach ($this->getForms() as $model) {
            if (Tools::isSubmit(get_class($model))) {
                return get_class($model);
            }
        }
        return Tools::getValue('activeTab', null);
    }
    
    public function getFormConfigs()
    {
        $configs = array();
        foreach ($this->getForms() as $form) {
            $configs[] = $this->getForm($form);
        }
        return $configs;
    }
    
    public function getForm($model)
    {
        $model->populate();
        $model->validate(false);
        $config = $model->getFormHelperConfig();
        return array(
            'form' => array(
                'name' => get_class($model),
                'legend' => array(
                    'title' => $model->getFormTitle(),
                    'icon' => $model->getFormIcon()
                ),
                'input' => $config,
                'submit' => array(
                    'name' => get_class($model),
                    'class' => $this->is15()? 'button' : null,
                    'title' => $this->l('Save'),
                )
            )
        );
    }
    
    public function getConfigFieldsValues()
    {
        $values = array();
        foreach ($this->getForms() as $model) {
            $model->loadFromConfig();
            $model->populate();
            foreach ($model->getAttributes() as $attr => $value) {
                $values[$model->getConfigAttribueName($attr)] = $value;
            }
        }
        return $values;
    }
    
    public function renderTable()
    {
        $id_lang = Context::getContext()->language->id;
        $models = ArContactUsTable::getAll($id_lang);
        foreach ($models as $k => $v) {
            $models[$k]['data'] = ArContactUsTools::jsonDecode($v['data'], true);
        }
        return $this->render('_table.tpl', array(
            'models' => $models,
            'shops' => Shop::getShops(false),
            'multishop' => Shop::isFeatureActive(),
            'link' => Context::getContext()->link
        ));
    }
    
    public function renderPromptTable()
    {
        $id_lang = Context::getContext()->language->id;
        $models = ArContactUsPromptTable::getAll($id_lang);
        
        return $this->render('_prompt_table.tpl', array(
            'promptModels' => $models,
            'shops' => Shop::getShops(false),
            'multishop' => Shop::isFeatureActive(),
            'link' => Context::getContext()->link
        ));
    }
    
    public function renderCallbackTable()
    {
        $models = ArContactUsCallback::getAll();
        foreach ($models as $k => $model) {
            if ($model['id_user']) {
                $customer = new Customer($model['id_user']);
                if (Validate::isLoadedObject($customer)) {
                    $models[$k]['customer'] = $customer;
                }
            }
        }
        return $this->render('_callbacks_table.tpl', array(
            'callbacks' => $models,
            'shops' => Shop::getShops(false),
            'multishop' => Shop::isFeatureActive(),
            'link' => Context::getContext()->link
        ));
    }
    
    public function render($template, $params = array())
    {
        $this->smarty->assign($params);
        return $this->display(__FILE__, $template);
    }
    
    public function sendPush($phone, $name, $referer, $mail)
    {
        if (file_exists(_PS_MODULE_DIR_ . 'onesignal/classes/AzlOsSubscriber.php')) {
            require_once _PS_MODULE_DIR_ . 'onesignal/classes/AzlOsSubscriber.php';
            $module = Module::getInstanceByName('onesignal');
            $id_lang = Context::getContext()->language->id;
            $admins = AzlOsSubscriber::getAdmins(array());
            $playerIDs = array();

            foreach ($admins as $admin) {
                $playerIDs[] = $admin['player_id'];
            }
            $message = strtr($this->getCallbackConfigModel()->onesignal_message[$id_lang], array(
                '{site}' => $this->getBaseURL(),
                '{phone}' => $phone,
                '{name}' => $name,
                '{email}' => $mail,
                '{referer}' => $referer,
            ));
            $title = strtr($this->getCallbackConfigModel()->onesignal_title[$id_lang], array(
                '{site}' => $this->getBaseURL(),
                '{phone}' => $phone,
                '{name}' => $name,
                '{email}' => $mail,
                '{referer}' => $referer,
            ));
            if ($playerIDs) {
                $ico = Configuration::get('AZL_OS_ADMIN_ICO');
                $res = $module->getApi()->sendMessage($playerIDs, array(
                    'en' => $message
                ), array(
                    'en' => $title
                ), $ico);
            }

            return $res;
        }
        return null;
    }
    
    public function getBaseURL()
    {
        return parse_url($this->getModuleBaseUrl(), PHP_URL_HOST);
    }
    
    public function sendEmail($phone, $name, $referer, $mail)
    {
        $templateVars = array(
            '{phone}' => $phone,
            '{name}' => $name,
            '{referer}' => $referer,
            '{mail}' => $mail,
            '{site}' => $this->getBaseURL(),
            '{date}'  => date('Y-m-d H:i:s')
        );
        
        $res = explode("\r\n", $this->callbackConfig->email_list);
        $res = array_unique($res);
        if (empty($res)) {
            return false;
        }
        $emails = array();
        foreach ($res as $email) {
            if (Validate::isEmail(trim($email))) {
                $emails[] = $email;
            }
        }
        if (empty($emails)) {
            return false;
        }
        $valid = 0;
        foreach ($emails as $email) {
            /* Email sending */
            $valid += Mail::Send(
                (int)Context::getContext()->language->id,
                'callback',
                $this->l('New callback request'),
                $templateVars,
                $email,
                null,
                null,
                null,
                null,
                null,
                dirname(__FILE__).'/mails/'
            );
        }
        return $valid;
    }
    
    public function getReCaptchaErrors()
    {
        return array(
            'missing-input-secret' => $this->l('The secret parameter is missing. Please check your reCaptcha Secret.'),
            'invalid-input-secret' => $this->l('The secret parameter is invalid or malformed. Please check your reCaptcha Secret.'),
            'missing-input-response' => $this->l('Bot activity detected! Empty captcha value.'),
            'invalid-input-response' => $this->l('Bot activity detected! Captcha value is invalid.'),
            'bad-request' => $this->l('The request is invalid or malformed.'),
        );
    }
    
    public function is15()
    {
        if ((version_compare(_PS_VERSION_, '1.5.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.6.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is16()
    {
        if ((version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.7.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is17()
    {
        if ((version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function getPath($abs = false)
    {
        if ($abs) {
            return _PS_MODULE_DIR_ . $this->name . '/';
        }
        return $this->_path;
    }
    
    public function getModuleBaseUrl()
    {
        return Tools::getShopDomainSsl(true, true).__PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }
    
    public function clearCache()
    {
        $this->_clearCache('head.tpl');
        $this->_clearCache('footer.tpl');
    }
    
    public function getUploadPath()
    {
        $path = dirname(__FILE__) . '/uploads/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $path;
    }
    
    public function getUploadsUrl()
    {
        return $this->getModuleBaseUrl() . 'uploads/';
    }
    
    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public function getLanguages($active = true)
    {
        $cookie = $this->context->cookie;
        $this->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        if ($this->allow_employee_form_lang && !$cookie->employee_form_lang) {
            $cookie->employee_form_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }

        $lang_exists = false;
        $this->_languages = Language::getLanguages($active);
        foreach ($this->_languages as $lang) {
            if (isset($cookie->employee_form_lang) && $cookie->employee_form_lang == $lang['id_lang']) {
                $lang_exists = true;
            }
        }

        $this->default_form_language = $lang_exists ? (int)$cookie->employee_form_lang : (int)Configuration::get('PS_LANG_DEFAULT');

        foreach ($this->_languages as $k => $language) {
            $this->_languages[$k]['is_default'] = (int)($language['id_lang'] == $this->default_form_language);
        }

        return $this->_languages;
    }
}
