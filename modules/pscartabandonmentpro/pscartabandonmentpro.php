<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class pscartabandonmentpro extends Module
{
    public $name;
    public $tab;
    public $version;
    public $author;
    public $module_key;
    public $author_address;
    public $bootstrap;
    public $displayName;
    public $description;
    public $ps174;
    public $ps16;
    public $doc_path;
    public $css_path;
    public $js_path;
    public $lib_path;
    public $img_path;
    public $logo_path;
    public $mails_path;
    public $controllers;
    public $ps_url;
    public $ps_host;
    public $cron_token;
    public $isReady;

    public function __construct()
    { 
        $this->name = 'pscartabandonmentpro';
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.11';
        $this->author = 'PrestaShop';
        $this->module_key = '011df651e7ac1913166469984d0cf519';
        $this->author_address = '0x64aa3c1e4034d07015f639b0e171b0d7b27d01aa';
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Cart Abandonment Pro');
        $this->description = $this->l('Send an automatic mail to customers that abandoned their shopping cart.');
        $this->ps174 = version_compare(_PS_VERSION_, '1.7.4.0', '>=');
        $this->ps16 = version_compare(_PS_VERSION_, '1.7.0.0', '<');
        $this->doc_path = $this->_path.'docs/';
        $this->css_path = $this->_path.'views/css/';
        $this->js_path = $this->_path.'views/js/';
        $this->img_path = $this->_path.'views/img/';
        $this->lib_path = $this->_path.'views/lib/';
        $this->logo_path = $this->_path.'logo.png';
        $this->mails_path = $this->local_path.'mails/';
        $this->controllers = array(
            'listing' => 'AdminCAPListing',
            'target' => 'AdminCAPTarget',
            'discount' => 'AdminCAPDiscount',
            'template' => 'AdminCAPTemplate',
            'reminder' => 'AdminCAPReminder',
            'statistics' => 'AdminCAPStatistics',
            'emailTest' => 'AdminCAPEmailTest',
            'cronJob' => 'FrontCAPCronJob',
            'unsubscribeJob' => 'FrontCAPUnsubscribeJob',
            'emailVisualize' => 'FrontCAPEmailVisualize',
            'clickShopUrl' => 'FrontCAPEmailClickShopUrl',
            'clickCartUrl' => 'FrontCAPEmailClickCartUrl',
            'clickProductUrl' => 'FrontCAPEmailClickProductUrl',
        );
        $this->cron_token = sha1($this->name.__PS_BASE_URI__);
        $this->ps_host = Tools::getCurrentUrlProtocolPrefix().htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8');
        $this->ps_url = $this->ps_host.__PS_BASE_URI__;
        $this->isReady = (getenv('PLATEFORM') === 'PSREADY')? true : false;
    }

    /**
     * install()
     *
     * @param none
     * @return bool
     */
    public function install()
    {
        Configuration::updateValue(
            'PSCARTABANDONMEDPRO_INSTALL_DATE', 
            date('Y-m-d')
        );

        include(dirname(__FILE__).'/sql/install.php');

        return (
            parent::install() &&
            $this->installTabs()
        );
    }

    /**
     * uninstall()
     *
     * @param none
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName('PSCARTABANDONMEDPRO_INSTALL_DATE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return (
            parent::uninstall() &&
            $this->uninstallTabs()
        );
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @param none
     * @return bool
     */
    private function installTabs()
    {
        $bReturnedValue = true;
        $tab = new Tab();

        foreach ($this->controllers as $sShortName => $sControllerName) {
            $tab->class_name = $sControllerName;
            $tab->active = 1;
            $tab->name = array();
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->name;
            }
            $tab->id_parent = -1;
            $tab->module = $this->name;
            $bReturnedValue &= $tab->add();
        }

        return $bReturnedValue;
    }

    /**
     * uninstall tabs
     *
     * @param none
     * @return bool
     */
    private function uninstallTabs()
    {
        $bReturnedValue = true;

        foreach ($this->controllers as $sShortName => $sControllerName) {
            $id_tab = (int)Tab::getIdFromClassName($sControllerName);
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $bReturnedValue &= $tab->delete();
            }
        }

        return $bReturnedValue;
    }

    /**
     * Check if the documentation exists for the current language and return the right documentation link
     *
     * @return string
     */
    private function getDocumentationByLanguage()
    {
        $sDocumentationLink = _PS_MODULE_DIR_.$this->name.'/docs/doc_cart_abandoned_'.$this->context->language->iso_code.'.pdf';
        
        $bDocumentationExists = file_exists($sDocumentationLink);

        if (!$bDocumentationExists) {
            return $this->doc_path.'doc_cart_abandoned_en.pdf';
        }

        return $this->doc_path.'doc_cart_abandoned_'.$this->context->language->iso_code.'.pdf';
    }

    /**
     * FAQ API
     */
    private function loadFaq()
    {
        $oAPI = new APIFAQ();
        return $oAPI->getData($this->module_key, $this->version);
    }

    /**
     * Loads asset resources
     *
     * @return void
     */
    public function loadAsset()
    {
        $this->addJsDefList();

        $aJs = array(
            $this->lib_path.'jquery.growl/js/jquery.growl.js',
            $this->lib_path.'ckeditor4/ckeditor.js',
            $this->lib_path.'pickr/js/pickr.js',

            $this->js_path.'admin/general.js',

            $this->js_path.'admin/tabs/faq.js',

            $this->js_path.'admin/tabs/reminder_plan/general.js',
            $this->js_path.'admin/tabs/reminder_plan/listing.js',
            $this->js_path.'admin/tabs/reminder_plan/email_test.js',
            $this->js_path.'admin/tabs/reminder_plan/target_frequency.js',
            $this->js_path.'admin/tabs/reminder_plan/ckeditor.js',

            $this->js_path.'admin/tabs/reminder_plan/steps/template_show.js',
            $this->js_path.'admin/tabs/reminder_plan/steps/template_email.js',
            $this->js_path.'admin/tabs/reminder_plan/steps/discount.js',

            $this->js_path.'admin/tabs/cron_task/general.js',
            $this->js_path.'admin/tabs/statistics/general.js',
        );

        $aCss = array(
            $this->lib_path.'jquery.growl/css/jquery.growl.css',
            $this->lib_path.'pickr/css/pickr.min.css',
            $this->lib_path.'pickr/css/pickr-override.css',
            $this->css_path.'admin/tabs/help/faq.css',

            $this->css_path.'admin/tabs/reminder_plan/template.css',
            $this->css_path.'admin/tabs/reminder_plan/general.css',
            $this->css_path.'admin/tabs/reminder_plan/listing.css',
            $this->css_path.'admin/tabs/reminder_plan/email_test.css',

            $this->css_path.'admin/tabs/cron_task/general.css', 

            $this->css_path.'admin/tabs/statistics/general.css',
            $this->css_path.'admin/tabs/statistics/datepicker.css',
        );

        if ($this->ps16) {
            $aCss[] = $this->css_path.'admin/versions/16_style.css';
            $aCss[] = '//fonts.googleapis.com/icon?family=Material+Icons';
        }

        if (!$this->ps16 && !$this->ps174) {
            $aCss[] = $this->css_path.'admin/versions/17_older_style.css';
        }

        $aCss[] =  $this->css_path.'admin/versions/17_style.css';

        $this->context->controller->addJS($aJs);
        $this->context->controller->addCSS($aCss, 'all');
    }

    /**
     * addJsDefList
     *
     * @return void
     */
    protected function addJsDefList()
    {
        $controllers_url = array(
            'listing' => $this->context->link->getAdminLink($this->controllers['listing']),
            'target' => $this->context->link->getAdminLink($this->controllers['target']),
            'discount' => $this->context->link->getAdminLink($this->controllers['discount']),
            'template' => $this->context->link->getAdminLink($this->controllers['template']),
            'reminder' => $this->context->link->getAdminLink($this->controllers['reminder']),
            'statistics' => $this->context->link->getAdminLink($this->controllers['statistics']),
            'emailTest' => $this->context->link->getAdminLink($this->controllers['emailTest']),
        );

        Media::addJsDef(array(
            'cap_controller_listing' => $this->controllers['listing'],
            'cap_controller_target' => $this->controllers['target'],
            'cap_controller_discount' => $this->controllers['discount'],
            'cap_controller_template' => $this->controllers['template'],
            'cap_controller_statistics' => $this->controllers['statistics'],
            'cap_controller_email_test' => $this->controllers['emailTest'],
            'cap_controller_reminder' => $this->controllers['reminder'],

            'cap_controller_listing_url' => $controllers_url['listing'],
            'cap_controller_target_url' => $controllers_url['target'],
            'cap_controller_discount_url' => $controllers_url['discount'],
            'cap_controller_template_url' => $controllers_url['template'],
            'cap_controller_reminder_url' => $controllers_url['reminder'],
            'cap_controller_statistics_url' => $controllers_url['statistics'],
            'cap_controller_email_test_url' => $controllers_url['emailTest'],

            'cap_template_demo_first_name' => 'John',
            'cap_template_demo_last_name' => 'Doe',
            'cap_template_demo_gender' => $this->l('Mr.'),
            'cap_template_demo_nb_product' => '3',
            'cap_template_demo_cart_link' => '<a href="'.$this->context->link->getPageLink('cart').'">'.$this->l('Cart link').'</a>',
            'cap_template_demo_discount_code' => '<span class="primary_color-textcolor discount-code">CODE'.date('y').'</span>',
            'cap_template_demo_discount_value' => Tools::displayPrice('25'),
            'cap_template_demo_discount_validity' => date('d/m/Y'),
            'cap_template_demo_shop_link' => '<a href="'.$this->ps_url.'">'.$this->l('Shop link').'</a>',       
            'cap_template_demo_unsubscribe' => '<a href="#" class="unsubscribe_link">'.$this->l('Unsubscribe').'</a>',
            'cap_template_demo_unsubscribe_default_text' => $this->l('Unsubscribe'),
            
            'reminder_updated' => $this->l('The reminder have been updated'),
            'active_error' => $this->l('An error occured'),
        ));
    }

    /**
     * Manage the email folders by languages
     *
     * @return bool
     */
    private function manageEmailFolders()
    {
        $languages = Language::getLanguages(true);
        
        $createEmailFolders = new CartReminderCreateEmailByLang;
        $canCreateNewFolders = $createEmailFolders->checkIfWeCanCreateFolders($this->mails_path);

        if ($canCreateNewFolders) {
            $createEmailFolders->intializeEmailTemplatesByLanguage($this->mails_path, $languages);
        }

        return $canCreateNewFolders;
    }

    /**
     * getContent
     *
     * 
     * @return template
    */
    public function getContent()
    {
        $this->loadAsset();

        $aReminderList = new CartReminderInfo();
        $oCheckOlderModule = new CartReminderCheckOlderVersion();

        $showRateModule = \DateTime::createFromFormat('Y-m-d', \Configuration::get('PSCARTABANDONMEDPRO_INSTALL_DATE'));
        $now = new \DateTime('now');
        $numberOfDays = (int)$now->diff($showRateModule)->format('%a');
        $showRateModule = $numberOfDays > 7 && $numberOfDays < 92;

        $this->context->smarty->assign(array(
            'isReady' => $this->isReady,
            'logo_path' => $this->logo_path,
            'olderModuleInstalled' => $oCheckOlderModule->isOlderModuleVersionEnabled(),
            'sOlderModuleURL' => $oCheckOlderModule->getOlderModuleUrlConfiguration(),
            'navGetLink' => Tools::getValue('cartnav'),
            'guide_link' => $this->getDocumentationByLanguage(),
            'mail_is_creatable' => $this->manageEmailFolders(),
            'apifaq' => $this->loadFaq(),
            'reminderList' => $aReminderList->getReminderList(),
            'cron_url' => $this->context->link->getModulelink($this->name, $this->controllers['cronJob'], array('token' =>  $this->cron_token)),
            'currency' => $this->context->currency->sign,
            'reminder_test_email' => Configuration::get('ADMINCAP_EMAIL_TEST', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'showRateModule' => $showRateModule,
            'currentLangIsoCode' => $this->context->language->iso_code,
            'img_path' => $this->_path.'views/img/',

        ));

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }
}
