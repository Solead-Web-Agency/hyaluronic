<?php
/**
 * 2007-2021 ETS-Soft
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
 * @copyright  2007-2021 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

if (!defined('_PS_VERSION_')) {
    exit;
}
if (!defined('_ETS_SEO_MODULE_')) {
    define('_ETS_SEO_MODULE_', 'ets_seo');
}

define('ETS_TOTAL_SEO_RULE_SCORE', 23);
define('ETS_TOTAL_READABILITY_RULE_SCORE', 8);

if(file_exists(_PS_ROOT_DIR_.'/app/AppKernel.php'))
    require_once(_PS_ROOT_DIR_.'/app/AppKernel.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoProduct.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoCms.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoCmsCategory.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoMeta.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoCategory.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoRedirect.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoManufacturer.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoSupplier.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoRating.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoUpdating.php');
require_once(dirname(__FILE__) . '/classes/Ets_Seo_Sitemap.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoSetting.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoTranslation.php');
require_once(dirname(__FILE__) . '/classes/EtsImportTranslation.php');
require_once(dirname(__FILE__) . '/classes/EtsSeoAnalysis.php');
require_once(dirname(__FILE__) . '/defines.php');

class Ets_Seo extends Module
{
    /**
     * @var bool
     */
    public $is176;
    /**
     * @var bool
     */
    public $is178;
    /**
     * @var bool
     */
    public $is175;

    /**
     * @var string
     */
    public $_html;

    /**
     * @var string
     */
    public $template_dir;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->name = 'ets_seo';
        $this->tab = 'front_office_features';
        $this->version = '2.4.2';
        $this->author = 'ETS-Soft';
        $this->secure_key = Tools::hash($this->name); // PS9: Tools::encrypt() supprimé
        $this->bootstrap = true;
        $this->module_key = '94b6a05e5e754eced6bfd9e2d22d4b60';
        parent::__construct();

        $this->displayName = $this->l('SEO Audit');
        $this->description = $this->l('Make SEO easy for everyone! All you need for On-Page SEO including SEO analysis with up-to-date rank math, SEO-friendly URL (remove IDs), ratings & snippet, social media, auto sitemap, RSS, meta template and more!');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->is176 = version_compare(_PS_VERSION_, '1.7.6.0', '>=');
        $this->is175 = version_compare(_PS_VERSION_, '1.7.5.0', '>=');
        $this->is178 = version_compare(_PS_VERSION_, '1.7.8.0', '>=');
        $this->_html = '';
        $this->template_dir = '../../../../modules/' . $this->name . '/views/templates/';
        $this->shortlink = 'https://mf.short-link.org/';
        if (Tools::getValue('configure') == $this->name && (int)Tools::isSubmit('othermodules')) {
            $this->displayRecommendedModules();
        }

    }

    public function copyTranslations()
    {
        $ps_translations_dir = _PS_ROOT_DIR_ . '/app/Resources/translations/';
        $tempDir = dirname(__FILE__) . '/views/templates/admin/_configure/templates/';
        $copy_trans = array();
        $this->copyAllFiles($copy_trans, $tempDir, $tempDir);

        if (($languages = Language::getLanguages(false)) && $copy_trans) {

            foreach ($languages as $language) {
                if (!@file_exists(($trans_file = dirname(__FILE__) . '/translations/' . $language['iso_code'] . '.php'))) {
                    @file_put_contents($trans_file, "<?php\n\nglobal \$_MODULE;\n\$_MODULE = array();\n");
                }
                if (!is_writable($trans_file)) {
                    $this->displayWarning($this->l('This file must be writable:') . $trans_file);
                }

                $str_write = Tools::file_get_contents($trans_file);
                $_MODULE = array();
                include $trans_file;

                if ($order_trans = @glob($ps_translations_dir . $language['locale'] . DIRECTORY_SEPARATOR . '*.' . $language['locale'] . '.xlf')) {
                    foreach ($order_trans as $trans) {
                        if (($dataXML = @simplexml_load_file($trans)) && !empty($dataXML->file)) {
                            foreach ($dataXML->file as $file) {
                                if ($this->fileInArray((string)$file['original'], $copy_trans) && ($array_trans = (array)$file->body) && !empty($array_trans['trans-unit'])) {
                                    foreach ((array)$array_trans['trans-unit'] as $trans_unit) {
                                        if (!empty($trans_unit['id']) && isset($trans_unit->target)) {
                                            $keyMd5 = '<{' . $this->name . '}prestashop>' . basename((string)$file['original'], '.tpl') . '_' . (string)$trans_unit['id'];
                                            if (empty($_MODULE[$keyMd5])) {
                                                $str_write .= "\$_MODULE['" . $keyMd5 . "'] = '" . pSQL($trans_unit->target) . "';\n";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                @file_put_contents($trans_file, $str_write);
            }
        }

        return true;
    }

    public function fileInArray($original, $copy_trans)
    {
        if (empty($copy_trans))
            return false;
        foreach ($copy_trans as $file) {
            if (@strpos($original, $file) !== false) {
                return true;
            }
        }
        return false;
    }

    public function copyAllFiles(&$copy_trans, $path, $cutPath)
    {
        if ($files = glob($path . '/*')) {
            foreach ($files as $file) {
                if (!@is_dir($file) && basename($file, '.php') != 'index') {
                    $copy_trans[] = str_replace($cutPath, '', $file);
                } else
                    $this->copyAllFiles($copy_trans, $file, $cutPath);

            }
            unset($files);
        }
    }

    /**
     * install
     *
     * @return bool
     */
    public function install()
    {
        if(self::isInstalled('ets_awesomeurl')){
            throw new PrestaShopException($this->l("The module ets_awesomeurl has been installed"));
        }
        return parent::install()
            && $this->registerHook('displayAdminProductsSeoStepBottom')
            && $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayAdminAfterHeader')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayOverrideTemplate')

            && $this->registerHook('actionProductSave')
            && $this->registerHook('actionObjectAddBefore')
            && $this->registerHook('actionObjectUpdateBefore')
            && $this->registerHook('actionObjectAddAfter')
            && $this->registerHook('actionObjectUpdateAfter')

            && $this->registerHook('actionAdminProductsListingFieldsModifier')
            && $this->registerHook('actionAdminProductsListingResultsModifier')
            && $this->registerHook('actionAdminCmsListingResultsModifier')
            && $this->registerHook('actionAdminCmsCategoriesListingResultsModifier')
            && $this->registerHook('actionAdminMetaListingResultsModifier')
            && $this->registerHook('actionAdminCategoriesListingResultsModifier')
            && $this->registerHook('actionAdminManufacturersListingResultsModifier')
            && $this->registerHook('actionAdminSuppliersListingResultsModifier')
            // >=1760
            && $this->registerHook('actionCategoryGridQueryBuilderModifier')
            && $this->registerHook('actionCategoryGridDefinitionModifier')
            && $this->registerHook('actionCategoryGridDataModifier')

            && $this->registerHook('actionCmsPageGridQueryBuilderModifier')
            && $this->registerHook('actionCmsPageGridDefinitionModifier')
            && $this->registerHook('actionCmsPageGridDataModifier')

            && $this->registerHook('actionCmsPageCategoryGridQueryBuilderModifier')
            && $this->registerHook('actionCmsPageCategoryGridDefinitionModifier')
            && $this->registerHook('actionCmsPageCategoryGridDataModifier')

            && $this->registerHook('actionMetaGridQueryBuilderModifier')
            && $this->registerHook('actionMetaGridDefinitionModifier')
            && $this->registerHook('actionMetaGridDataModifier')

            && $this->registerHook('actionManufacturerGridQueryBuilderModifier')
            && $this->registerHook('actionManufacturerGridDefinitionModifier')
            && $this->registerHook('actionManufacturerGridDataModifier')

            && $this->registerHook('actionSuppliersGridQueryBuilderModifier')
            && $this->registerHook('actionSupplierGridDefinitionModifier')
            && $this->registerHook('actionSupplierGridDataModifier')

            && $this->registerHook('actionDispatcherBefore')
            && $this->registerHook('actionAdminEtsSeoUrlRedirectFormModifier')
            && $this->registerHook('actionMetaPageSave')

            && $this->registerHook('actionBeforeCreateCategoryFormHandler')
            && $this->registerHook('actionBeforeUpdateCategoryFormHandler')
            && $this->registerHook('actionCategoryFormBuilderModifier')

            && $this->registerHook('actionBeforeCreateRootCategoryFormHandler')
            && $this->registerHook('actionBeforeUpdateRootCategoryFormHandler')
            && $this->registerHook('actionRootCategoryFormBuilderModifier')

            && $this->registerHook('actionBeforeCreateCmsPageFormHandler')
            && $this->registerHook('actionBeforeUpdateCmsPageFormHandler')
            && $this->registerHook('actionCmsPageFormBuilderModifier')

            && $this->registerHook('actionBeforeCreateCmsPageCategoryFormHandler')
            && $this->registerHook('actionBeforeUpdateCmsPageCategoryFormHandler')
            && $this->registerHook('actionCmsPageCategoryFormBuilderModifier')

            && $this->registerHook('actionBeforeCreateMetaFormHandler')
            && $this->registerHook('actionBeforeUpdateMetaFormHandler')
            && $this->registerHook('actionMetaFormBuilderModifier')
            && $this->registerHook('actionAdminShopParametersMetaControllerPostProcessUrlSchemaBefore')

            && $this->installDb()
            && $this->__installTabs()
            && $this->setDefaultConfig()
            && $this->_installOverried()
            && $this->setRootSeoUrlConfig()
            && $this->setSitemap()
            && $this->copyTranslations()
            && $this->importNewTranslation();
    }


    /**
     * uninstall
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && $this->dropTables()
            && $this->restoreSeoUrlConfig()
            && $this->_uninstallOverried()
            && $this->__uninstallTabs()
            && $this->removeAllConfigs()
            && $this->removeSitemap()
            && $this->removeNewTranslation();
    }

    public function disable($force_all = false)
    {

        return parent::disable($force_all)
            && $this->_uninstallOverried()
            && $this->restoreSeoUrlConfig()
            && $this->removeSitemap();
    }

    public function enable($force_all = false)
    {

        return parent::enable($force_all)
            && $this->_installOverried()
            && $this->setSitemap();
    }

    /**
     * installDb
     *
     * @return bool
     */
    public function installDb()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        return $seoDef->installDb();
    }

    /**
     * dropTables
     *
     * @return bool
     */
    public function dropTables()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        return $seoDef->uninstallDb();
    }

    /**
     * _installOverried
     *
     * @return bool
     */
    public function _installOverried()
    {

        $this->copy_directory(dirname(__FILE__) . '/views/templates/admin/_configure/templates', _PS_OVERRIDE_DIR_ . 'controllers/admin/templates');
        return true;
    }

    /**
     * _unInstallOverried
     *
     * @return bool
     */
    public function _uninstallOverried()
    {
        $this->delete_directory(_PS_OVERRIDE_DIR_ . 'controllers/admin/templates');
        return true;
    }

    /**
     * __installTabs
     *
     * @return bool
     */
    public function __installTabs()
    {
        $languages = Language::getLanguages(false);
        $tab = new Tab();
        $tab->class_name = 'AdminEtsSeo';
        $tab->module = $this->name;
        $tab->id_parent = 0;
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = ($tabName = Ets_Seo_Define::getTextLang('SEO Audit', $lang, 'defines')) ? $tabName : $this->l('SEO Audit');
        }
        $tab->save();
        $seoTabId = Tab::getIdFromClassName('AdminEtsSeo');
        if ($seoTabId) {
            $seoDef = Ets_Seo_Define::getInstance();

            //Set tab Traffic seo
            $idTabParentMeta = Tab::getIdFromClassName('AdminParentMeta');

            //Disable parent meta tab
            if ($idTabParentMeta) {
                $parentMeta = new Tab($idTabParentMeta);
                $parentMeta->active = false;
                $parentMeta->save();
            }


            //SEO tabs

            foreach ($seoDef->get_menus() as $tabArg) {
                //Set parent for each meta tab

                if (!Tab::getIdFromClassName($tabArg['controller'])) {
                    $id_parent = $seoTabId;
                    if (isset($tabArg['parent_controller']) && $tabArg['parent_controller']) {
                        $id_parent = Tab::getIdFromClassName($tabArg['parent_controller']);

                    }

                    $tab = new Tab();
                    $tab->class_name = $tabArg['controller'];
                    $tab->module = $this->name;
                    $tab->id_parent = $id_parent;
                    $tab->icon = $tabArg['icon'];
                    foreach ($languages as $lang) {
                        $tab->name[$lang['id_lang']] = isset($tabArg['origin']) && ($tabName = Ets_Seo_Define::getTextLang($tabArg['origin'], $lang, 'defines')) ? $tabName : $tabArg['title'];
                    }
                    $tab->save();
                }

                if ($tabArg['controller'] == 'AdminEtsSeoUrlAndRemoveId') {
                    $urlTabId = Tab::getIdFromClassName('AdminEtsSeoUrlAndRemoveId');
                    $idTabMeta = Tab::getIdFromClassName('AdminMeta');
                    if ($urlTabId && $idTabMeta) {
                        $trafficSeo = new Tab($idTabMeta);
                        $trafficSeo->id_parent = $urlTabId;
                        $oldNames = array();
                        foreach ($languages as $lang) {
                            $oldNames[$lang['id_lang']] = $trafficSeo->name[$lang['id_lang']];
                            $trafficSeo->name[$lang['id_lang']] = ($tabName = Ets_Seo_Define::getTextLang('URL structure and remove IDs', $lang)) ? $tabName : $this->l('URL structure and remove IDs');
                        }
                        $trafficSeo->save();
                        Configuration::updateValue('ETS_SEO_SEO_AND_URL_NAME', $oldNames);
                    }

                } elseif ($tabArg['controller'] == 'AdminEtsSeoTraffic') {
                    $traffic = Tab::getIdFromClassName('AdminEtsSeoTraffic');

                    $idTabMeta = Tab::getIdFromClassName('AdminSearchEngines');
                    if ($traffic && $idTabMeta) {
                        $trafficSeo = new Tab($idTabMeta);
                        $trafficSeo->id_parent = $traffic;
                        $trafficSeo->save();
                    }

                    $idTabMeta = Tab::getIdFromClassName('AdminReferrers');
                    if ($traffic && $idTabMeta) {
                        $trafficSeo = new Tab($idTabMeta);
                        $trafficSeo->id_parent = $traffic;
                        $trafficSeo->save();
                    }
                }
            }

            $tabAjaxId = Tab::getIdFromClassName('AdminEtsSeoAjax');
            if(!(int)$tabAjaxId){
                $tabAjax = new Tab('AdminEtsSeoAjax');
                $tabAjax->class_name = 'AdminEtsSeoAjax';
                $tabAjax->id_parent = 0;
                $tabAjax->module = $this->name;
                $tabAjax->active = 0;
                foreach ($languages as $lang){
                    $tabAjax->name[$lang['id_lang']] = 'Seo ajax';
                }
                $tabAjax->save();
            }

        }
        return true;
    }

    /**
     * __uninstallTabs
     *
     * @return bool
     */
    public function __uninstallTabs()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $languages = Language::getLanguages(false);
        $menus = $seoDef->get_menus();
        //Set tab Traffic seo
        $idTabParentMeta = Tab::getIdFromClassName('AdminParentMeta');

        if ($idTabParentMeta) {
            //Enable meta tab
            $parentMeta = new Tab($idTabParentMeta);
            $parentMeta->active = true;
            $parentMeta->save();

            foreach ($seoDef->traffic_seo_tabs() as $t) {
                $idTabMeta = Tab::getIdFromClassName($t);
                if ($idTabMeta) {
                    $trafficSeo = new Tab($idTabMeta);
                    $trafficSeo->id_parent = $idTabParentMeta;
                    if ($t == 'AdminMeta') {
                        foreach ($languages as $lang) {
                            $oldName = Configuration::get('ETS_SEO_SEO_AND_URL_NAME', $lang['id_lang']);
                            $trafficSeo->name[$lang['id_lang']] = $oldName ? $oldName : $this->l('SEO and URLs');
                        }
                    }


                    $trafficSeo->save();
                }
            }
        }

        foreach ($menus as $key => $tabItem) {
            if ($tabItem) {
                if ($tabId = Tab::getIdFromClassName($key)) {
                    $tab = new Tab($tabId);
                    if ($tab) {
                        $tab->delete();
                    }
                }
            }

        }
        $tabAjaxId = Tab::getIdFromClassName('AdminEtsSeoAjax');
        if((int)$tabAjaxId){
            $tabAjax = new Tab($tabAjaxId);
            if ($tabAjax && $tabAjax->id)
                $tabAjax->delete();
        }
        //Remove tabs seo
        if ($tabSeo = Tab::getIdFromClassName('AdminEtsSeo')) {
            $tab = new Tab($tabSeo);
            if ($tab)
                $tab->delete();
        }
        return true;
    }

    public function importNewTranslation()
    {
        if(@file_exists(_PS_MODULE_DIR_.'ets_seo/translations/translation.xml'))
        {
            $trans = new EtsImportTranslation($this->name, _PS_MODULE_DIR_.'ets_seo/translations/translation.xml');
            $trans->import();
        }
        return true;
    }

    public function removeNewTranslation()
    {
        $trans = new EtsImportTranslation($this->name, _PS_MODULE_DIR_.'ets_seo/translations/translation.xml');
        $trans->removeTranslation();
        return true;
    }


    /**
     * getContent
     *
     * @return void
     */
    public function getContent()
    {
        $moduleLink = $this->context->link->getAdminLink('AdminEtsSeoGeneralDashboard');
        Tools::redirectAdmin($moduleLink);
    }

    /* Use new translate system*/
    public function isUsingNewTranslationSystem()
    {
        if($this->is176)
            return (int)Configuration::get('ETS_SEO_ENABLE_NEW_TRANS');
        return false;
    }

    /**
     * hookActionProductSave
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionProductSave($params)
    {
        $seoSetting = EtsSeoSetting::getInstance();
        $seoSetting->updateSeoProduct($params);
    }

    /**
     * displayBackOfficeHeader
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $currentController = Tools::getValue('controller');
        $seoDef = Ets_Seo_Define::getInstance();
        $this->context->controller->addCSS($this->_path . 'views/css/all.css');
        Media::addJsDef([
            'ets_seo_is178' => $this->is178,
        ]);
        if (strpos($currentController, 'AdminEtsSeo') !== false || in_array($currentController, $seoDef->listControllerAction())) {
            $this->context->controller->addJquery();
            $this->context->controller->addCSS($this->_path . 'views/css/tagify.css');
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addCSS($this->_path . 'views/css/other.css');
            $this->context->controller->addJS($this->_path . 'views/js/tagify.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/tagify.polyfills.min.js');

            if (!$this->getRequestContainer()) {
                $this->context->controller->addCSS($this->_path . 'views/css/select2.min.css');
            }
            if(strpos($currentController, 'AdminEtsSeo') !== false)
            {
                $this->context->controller->addCSS($this->_path . 'views/css/admin_module.css');
            }
        }

        if ($currentController == 'AdminEtsSeoGeneralDashboard') {
            $this->context->controller->addCSS($this->_path . 'views/css/nv.d3.css');
            $this->context->controller->addCSS($this->_path . 'views/css/Chart.min.css');
            $this->context->controller->addCSS($this->_path . 'views/css/dashboard.css');
            $this->context->controller->addJS($this->_path . 'views/js/Chart.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/d3.v3.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/nv.d3.min.js');
        }
        //$this->context->controller->addJqueryPlugin('tagify', null, true);

        $languages = Language::getLanguages(false);
        $ets_languages = array();
        foreach ($languages as $lang) {
            $ets_languages[$lang['iso_code']] = $lang['id_lang'];
        }
        if ($this->is176) {
            $is_cms_category = 0;
            $sfContainer = $this->getSfContainer();

            if (null !== $sfContainer && null !== $sfContainer->get('request_stack')->getCurrentRequest()) {
                $request = $sfContainer->get('request_stack')->getCurrentRequest();
                if ($request->get('_route') == 'admin_cms_pages_category_create' || $request->get('_route') == 'admin_cms_pages_category_edit') {
                    $is_cms_category = 1;
                }
            }
        } else {
            $is_cms_category = Tools::getIsset('updatecms_category') || Tools::getIsset('addcms_category') ? 1 : 0;
        }

        $linkRewriteRules = array();
        if (Tools::getValue('controller') == 'AdminMeta') {
            $linkRewriteRules = $seoDef->url_rules();
        }
        $controller = ($controller = Tools::getValue('controller', '')) && Validate::isCleanHtml($controller) ? $controller : '';

        $this->smarty->assign(
            array(
                'ets_seo_defined' => array(
                    'is176' => (int)$this->is176,
                    'is175' => (int)$this->is175,
                    'isSf' => $this->getRequestContainer(),
                    'seo_analysis_rules' => array_merge($seoDef->seo_analysis_rules($controller, $is_cms_category), $seoDef->seo_analysis_rules_meta()),
                    'readability_rules' => $seoDef->readability_rules(),
                    'transition_words' => $seoDef->transition_words(),
                    'id_current_page' => $this->getIdCurrentPage(),
                    'meta_tamplate_configured' => EtsSeoSetting::isMetaTemplateConfigured($controller, $is_cms_category),
                    'placeholder_meta' => $seoDef->getPlaceholderPage($controller, $is_cms_category)
                ),
                'link_admin_js' => $this->_path . 'views/js/admin.js',
                'link_admin_all_js' => $this->_path . 'views/js/admin_js_all.js',
                'link_other_js' => $this->_path . 'views/js/other.js',
                'link_analysis_js' => $this->_path . 'views/js/analysis.js',
                'link_select2_js' => $this->_path . 'views/js/select2.min.js',
                'link_page_js' => $this->_path . 'views/js/page.js',
                'controller' => $controller,
                'is_cms_category' => $is_cms_category,
                'link_ajax_bo' => $this->context->link->getAdminLink('AdminEtsSeoAjax'),
                'ets_languages' => $ets_languages,
                'link_module' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name,
                'meta_codes' => $this->getMetaCodes(),
                'current_lang_id' => $this->context->language->id,
                'current_lang_selected' => $controller == 'AdminProducts' ? $languages[0]['id_lang'] : Configuration::get('PS_LANG_DEFAULT'),
                'is_multilang_active' => Language::isMultiLanguageActivated(),
                'link_rewrite_rules' => $linkRewriteRules,
                'is_no_referrer' => in_array($controller, $this->whiteListControllers()),
                'is_use_module' => strpos($currentController, 'AdminEtsSeo') !== false || in_array($currentController, $seoDef->listControllerAction()),
            ));
            
        $this->context->smarty->assign(array(
            'ets_seo_link_img' => $this->context->shop->getBaseURL(true, true) . 'img/social/',
        ));
        if ($this->getRequestContainer()) {
            $this->addTwigVar('ETS_SEO_TWIG_TRANS', $this->transTwig());
        }
        if ($controller == 'AdminCmsContent') {
            if ($this->isCmsCategoryPage()) {
                $this->seo_cms_category_html();
            } else {
                $this->seo_cms_html();
            }

        }
        if ($controller == 'AdminMeta') {
            $this->seo_meta_html();
        } elseif ($controller == 'AdminCategories') {
            $this->seo_category_html();
        } elseif ($controller == 'AdminManufacturers') {
            $this->seo_manufacturer_html();
        } elseif ($controller == 'AdminSuppliers') {
            $this->seo_supplier_html();
        } elseif ($controller == 'AdminProducts') {
            $this->seo_product_html();

        }

        if ($errorLinkRewrite = $this->context->cookie->__get('ets_seo_error_link_rewrite')) {
            $this->context->controller->errors = array($errorLinkRewrite);
            $this->context->cookie->__unset('ets_seo_error_link_rewrite');
        }

        return $this->display(__FILE__, 'admin_head.tpl');
        
    }

    public function whiteListControllers()
    {
        return array('AdminProducts', 'AdminMeta', 'AdminCategories', 'AdminCmsContent', 'AdminManufacturers', 'AdminSuppliers');
    }

    public function getMetaCodes()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $result = array(
            'title' => array(),
            'desc' => array(),
        );
        if (($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller)) {
            $request = $this->getRequestContainer();
            $id_lang = $this->context->language->id;
            if ($controller == 'AdminProducts') {
                if ($request) {
                    $id_product = (int)$request->get('id');
                    $product = $id_product ? new Product($id_product, null, $id_lang) : null;
                    $cate = $product ? new Category($product->id_category_default, $id_lang) : '';
                    $result['title'] = $seoDef->get_meta_codes('product', array(
                        'post_title' => $product ? $product->name : '',
                        'price' => $product ? number_format($product->price, 1, '.', '') : '',
                        'category' => $product ? $cate->name : '',
                        'is_title' => true
                    ));
                    $result['desc'] = $seoDef->get_meta_codes('product', array(
                        'post_title' => $product ? $product->name : '',
                        'price' => $product ? number_format($product->price, 2, '.', '') : '',
                        'category' => $product ? $cate->name : '',
                        'description' => $product ? $product->description_short : '',
                    ));
                }
            } elseif ($controller == 'AdminCategories') {
                $id_category = (int)Tools::getValue('id_category');
                if ($request) {
                    $id_category = (int)$request->get('categoryId');
                }
                $cate = $id_category ? new Category($id_category, $id_lang) : null;
                $result['title'] = $seoDef->get_meta_codes('category', array(
                    'post_title' => $cate ? $cate->name : '',
                    'is_title' => true
                ));
                $result['desc'] = $seoDef->get_meta_codes('category', array(
                    'post_title' => $cate ? $cate->name : '',
                    'description' => $cate ? $cate->description : '',
                ));
            } elseif ($controller == 'AdminCmsContent') {
                if (!$this->isCmsCategoryPage()) {
                    $id_cms = (int)Tools::getValue('id_cms');
                    if ($request) {
                        $id_cms = (int)$request->get('cmsPageId');
                    }
                    $cms = $id_cms ? new CMS($id_cms, $id_lang) : null;
                    $cmsCategory = $cms ? new CMSCategory($cms->id_cms_category, $id_lang) : null;
                    $result['title'] = $seoDef->get_meta_codes('cms', array(
                        'post_title' => $cms && isset($cms->head_seo_title) ? $cms->head_seo_title : '',
                        'category' => $cmsCategory ? $cmsCategory->name : '',
                        'is_title' => true
                    ));
                    $result['desc'] = $seoDef->get_meta_codes('cms', array(
                        'post_title' => $cms && isset($cms->head_seo_title) ? $cms->head_seo_title : '',
                        'description' => $cms ? $cms->meta_description : '',
                    ));
                } else {
                    $id_cms_category = (int)Tools::getValue('id_cms_category');
                    if ($request) {
                        $id_cms_category = (int)$request->get('cmsCategoryId');
                    }
                    $cmsCate = $id_cms_category ? new CMSCategory($id_cms_category, $id_lang) : null;
                    $result['title'] = $seoDef->get_meta_codes('cms_category', array(
                        'post_title' => $cmsCate ? $cmsCate->name : '',
                        'is_title' => true
                    ));
                    $result['desc'] = $seoDef->get_meta_codes('cms_category', array(
                        'post_title' => $cmsCate ? $cmsCate->name : '',
                        'description' => $cmsCate ? $cmsCate->description : '',
                    ));
                }
            } elseif ($controller == 'AdminMeta') {
                $id_meta = (int)Tools::getValue('id_meta');
                if ($request) {
                    $id_meta = (int)$request->get('metaId');
                }

                $meta = $id_meta ? new Meta($id_meta, $id_lang) : null;
                $result['title'] = $seoDef->get_meta_codes('meta', array(
                    'post_title' => $meta ? $meta->title : '',
                    'is_title' => true
                ));
                $result['desc'] = $seoDef->get_meta_codes('meta', array(
                    'post_title' => $meta ? $meta->title : '',
                    'description' => $meta ? $meta->description : '',
                ));
            } elseif ($controller == 'AdminManufacturers') {
                $id_manufacturer = (int)Tools::getValue('id_manufacturer');
                if ($request) {
                    $id_manufacturer = (int)$request->get('manufacturerId');
                }
                $manufacturer = $id_manufacturer ? new Manufacturer($id_manufacturer, $id_lang) : null;
                $result['title'] = $seoDef->get_meta_codes('manufacturer', array(
                    'post_title' => $manufacturer ? $manufacturer->name : '',
                    'is_title' => true
                ));
                $result['desc'] = $seoDef->get_meta_codes('manufacturer', array(
                    'post_title' => $manufacturer ? $manufacturer->name : '',
                    'description' => $manufacturer ? $manufacturer->short_description : '',
                    'description2' => $manufacturer ? $manufacturer->description : '',
                ));
            } elseif ($controller == 'AdminSuppliers') {
                $id_supplier = (int)Tools::getValue('id_supplier');
                if ($request) {
                    $id_supplier = (int)$request->get('supplierId');
                }
                $supplier = $id_supplier ? new Supplier($id_supplier, $id_lang) : null;
                $result['title'] = $seoDef->get_meta_codes('supplier', array(
                    'post_title' => $supplier ? $supplier->name : '',
                    'is_title' => true
                ));
                $result['desc'] = $seoDef->get_meta_codes('supplier', array(
                    'post_title' => $supplier ? $supplier->name : '',
                    'description' => $supplier ? $supplier->description : '',
                ));
            }
        }
        return $result;
    }

    /**
     * seo_cms_html
     *
     * @return void
     */
    public function seo_cms_html()
    {
        $this->assignPageParams('cms');
        $enableSeo = 1;
        if ($this->getRequestContainer()) {
            if (Tools::getValue('controller') == 'AdminCmsContent') {
                $this->addTwigVar('ets_cms_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_cms_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
            }

        } else {
            $this->context->smarty->assign(array(
                'ets_cms_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_cms_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }

    }

    public function seo_cms_category_html()
    {
        $this->assignPageParams('cms_category');
        $enableSeo = 1;
        if ($this->getRequestContainer()) {
            if (Tools::getValue('controller') == 'AdminCmsContent') {
                $this->addTwigVar('ets_cms_category_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_cms_category_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
            }
        } else {
            $this->context->smarty->assign(array(
                'ets_cms_category_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_cms_category_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }
    }

    /**
     * assignPageParams
     *
     * @param  string $type : cms, meta, category
     *
     * @return void
     */
    public function assignPageParams($type)
    {
        $enableRating = false;
        $ratingConfig = Configuration::get('ETS_SEO_RATING_PAGES') ? explode(',', Configuration::get('ETS_SEO_RATING_PAGES')) : array();
        $metaTitleConfigName = '';
        $metaDescConfigName = '';
        $forceUseMetaTemplate = 0;
        $pageTitleTrans = '';
        switch ($type) {
            case 'cms':
                $idKey = 'id_cms';
                $idKey176 = 'cmsPageId';
                $objPage = 'CMS';
                $enableRating = in_array('cms', $ratingConfig);
                $metaTitleConfigName = 'ETS_SEO_CMS_META_TILE';
                $metaDescConfigName = 'ETS_SEO_CMS_META_DESC';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CMS_FORCE_USE_META_TEMPLATE');
                $pageTitleTrans = EtsSeoTranslation::trans('cms_title');
                break;

            case 'cms_category':
                $idKey = 'id_cms_category';
                $idKey176 = 'cmsCategoryId';
                $objPage = 'CMSCategory';
                $enableRating = in_array('cms_category', $ratingConfig);
                $metaTitleConfigName = 'ETS_SEO_CMS_CATE_META_TILE';
                $metaDescConfigName = 'ETS_SEO_CMS_CATE_META_DESC';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CMS_CATE_FORCE_USE_META_TEMPLATE');
                $pageTitleTrans = EtsSeoTranslation::trans('cms_category_title');
                break;

            case 'meta':
                $idKey = 'id_meta';
                $idKey176 = 'metaId';
                $objPage = 'Meta';
                $enableRating = in_array('meta', $ratingConfig);
                $pageTitleTrans = EtsSeoTranslation::trans('meta_title');
                break;

            case 'category':
                $idKey = 'id_category';
                $idKey176 = 'categoryId';
                $objPage = 'Category';
                $metaTitleConfigName = 'ETS_SEO_CATEGORY_META_TILE';
                $metaDescConfigName = 'ETS_SEO_CATEGORY_META_DESC';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CATEGORY_FORCE_USE_META_TEMPLATE');
                $enableRating = in_array('category', $ratingConfig);
                $pageTitleTrans = EtsSeoTranslation::trans('category_name');
                break;
            case 'manufacturer':
                $idKey = 'id_manufacturer';
                $idKey176 = 'manufacturerId';
                $objPage = 'Manufacturer';
                $metaTitleConfigName = 'ETS_SEO_MANUFACTURER_META_TITLE';
                $metaDescConfigName = 'ETS_SEO_MANUFACTURER_META_DESC';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_MANUFACTURER_FORCE_USE_META_TEMPLATE');
                $enableRating = in_array('manufacturer', $ratingConfig);
                $pageTitleTrans = EtsSeoTranslation::trans('manufacturer_name');
                break;
            case 'supplier':
                $idKey = 'id_supplier';
                $idKey176 = 'supplierId';
                $objPage = 'Supplier';
                $enableRating = in_array('supplier', $ratingConfig);
                $metaTitleConfigName = 'ETS_SEO_SUPPLIER_META_TILE';
                $metaDescConfigName = 'ETS_SEO_SUPPLIER_META_DESC';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_SUPPLIER_FORCE_USE_META_TEMPLATE');
                $pageTitleTrans = EtsSeoTranslation::trans('supplier_name');
                break;

        }

        $languages = Language::getLanguages(false);
        $metaConfig = array();
        foreach ($languages as $lang) {
            $metaConfig[$lang['id_lang']] = array(
                'title' => $metaTitleConfigName ? (string)Configuration::get($metaTitleConfigName, $lang['id_lang']) : '',
                'desc' => $metaDescConfigName ? (string)Configuration::get($metaDescConfigName, $lang['id_lang']) : '',
            );
        }

        $current_lang = array(
            'id' => $this->context->language->id,
            'iso_code' => $this->context->language->iso_code
        );
        if(count($languages)){
            $langDefault = Language::getLanguage((int)Configuration::get('PS_LANG_DEFAULT'));
            $current_lang = array(
                'id' => $langDefault ? $langDefault['id_lang'] : $languages[0]['id_lang'],
                'iso_code' => $langDefault ? $langDefault['iso_code'] : $languages[0]['iso_code']
            );
        }
        $seoDef = Ets_Seo_Define::getInstance();

        $seo_cms = array(
            'link' => array(),
            'link_rewrite' => array(),
            'meta_description' => array(),
            'meta_title' => array(),
            'key_phrase' => array()
        );
        $page_name = '';
        $id = (int)Tools::getValue($idKey, null);

        if ($request = $this->getRequestContainer()) {
            $id = (int)$request->get($idKey176);
        }

        if ($id) {
            foreach ($languages as $lang) {
                $page = new $objPage($id, $lang['id_lang']);
                if ($type == 'meta') {
                    $link = $page->url_rewrite ? $this->getPageLink($page->page,(int)$lang['id_lang'])  : '';
                } elseif ($type == 'cms') {
                    $link = $this->context->link->getCMSLink($page, null, null, (int)$lang['id_lang']);
                } elseif ($type == 'cms_category') {
                    $link =  $this->context->link->getCMSCategoryLink($page, null, null, (int)$lang['id_lang']);
                } elseif ($type == 'category') {
                    $link = $this->context->link->getCategoryLink($page, $page->link_rewrite,$lang['id_lang']);
                } elseif ($type == 'manufacturer') {
                    $link = $this->context->link->getManufacturerLink($page, null, (int)$lang['id_lang'], $this->context->shop->id);
                } elseif ($type == 'supplier') {
                    $link = $this->context->link->getSupplierLink($page, null, (int)$lang['id_lang'], $this->context->shop->id);
                }

                $seo_cms['link'][$lang['id_lang']] = $link ? (is_array($link) ? $link[0]['link'] : $link) : '';
                $seo_cms['link_rewrite'][$lang['id_lang']] = isset($page->link_rewrite) ? $page->link_rewrite : $page->url_rewrite;
                $seo_cms['meta_title'][$lang['id_lang']] = isset($page->meta_title) && $page->meta_title ? $page->meta_title : (isset($page->title) ? $page->title : '');
                $seo_cms['meta_description'][$lang['id_lang']] = isset($page->meta_description) && $page->meta_description ? $page->meta_description : (isset($page->description) ? $page->description : '');
                if (!$seo_cms['meta_title'][$lang['id_lang']] && isset($page->name) && $page->name) {
                    $seo_cms['meta_title'][$lang['id_lang']] = $page->name;
                }
                if ($type == 'manufacturer' && !$page->meta_description) {
                    $seo_cms['meta_description'][$lang['id_lang']] = $page->short_description;
                }

                if (isset($page->name)) {
                    $page_name = $page->name;
                } elseif (isset($page->title)) {
                    $page_name = $page->title;
                } elseif (isset($page->meta_title)) {
                    $page_name = $page->meta_title;
                }
            }
        }

        $seo_cms['meta_title'] = $this->formatSeoMeta($seo_cms['meta_title'], array('post_title' => $page_name, 'is_title' => true, 'description' => '', 'description2' => '', 'category' => '', 'price' => ''), $type);
        $seo_cms['meta_description'] = $this->formatSeoMeta($seo_cms['meta_description'], array('post_title' => $page_name, 'description' => '', 'description2' => '', 'category' => '', 'price' => ''), $type);

        $this->smarty->assign(array(
            'ets_seo_languages' => $languages,
            'tmp_dir' => dirname(__FILE__) . '/views/templates',
            'seo_data' => $seoDef->key_phrase_input($type, $id, $this->context),
            'current_lang' => $current_lang,
            'seo_cms' => $seo_cms,
            'seo_advanced' => $seoDef->seo_advanced($type, $id, $this->context),
            'analysis_types' => $seoDef->analysis_types(),
            'seo_enabled' => 1,
            'enable_force_rating' => $enableRating,
            'readability_enabled' => 1,
            'is_new_theme' => $this->getRequestContainer() ? true : false,
            'enable_rating' => $this->isInstalledRatingModule(),
            'rating_config' => EtsSeoRating::getRatingConfig($type, $id),
            'rating_setting' => EtsSeoRating::getRating($type, $id),
            'meta_config' => $metaConfig,
            'forceUseMetaTemplate' => $forceUseMetaTemplate,
            'show_friendly_url' => $type == 'manufacturer' || $type == 'supplier' ? 0 : 1,
            'message_explain' => EtsSeoTranslation::getAllTrans(),
            'page_title_trans' => $pageTitleTrans,
            'show_readability' => $type == 'meta' ? false : true,
            'seo_score_data' => EtsSeoSetting::getAnalysisScore($type, $id),
            'isAutoAnalysis' => (int)Configuration::get('ETS_SEO_ENABLE_AUTO_ANALYSIS')
        ));
    }

    /**
     * seo_cms_html
     *
     * @return void
     */
    public function seo_meta_html()
    {
        $this->assignPageParams('meta');
        $enableSeo = 1;

        if ($this->getRequestContainer()) {
            if (Tools::getValue('controller') == 'AdminMeta') {
                $this->addTwigVar('ets_meta_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_meta_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_meta_seo_meta_title', $enableSeo ? $this->display(__FILE__, 'page/meta_title.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
                $this->addTwigVar('ETS_SEO_ENABLE_REMOVE_ID_IN_URL', (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL'));
                $this->addTwigVar('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS', (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS'));
                $this->addTwigVar('ETS_SEO_ENABLE_REDRECT_NOTFOUND', (int)Configuration::get('ETS_SEO_ENABLE_REDRECT_NOTFOUND'));
                $this->addTwigVar('ETS_SEO_REDIRECT_STATUS_CODE', (int)Configuration::get('ETS_SEO_REDIRECT_STATUS_CODE'));
                $this->addTwigVar('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL', (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL'));
                $this->addTwigVar('titleRemoveLangCode', $this->l('Remove ISO code in URL for default language'));
                $this->addTwigVar('titleRemoveAttrAlias', $this->l('Remove attribute alias in URL'));
                $this->addTwigVar('ets_seo_is178', $this->is178);
            }

        } else {
            $this->context->smarty->assign(array(
                'ets_meta_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_meta_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_meta_seo_meta_title' => $enableSeo ? $this->display(__FILE__, 'page/meta_title_b3.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }

    }

    public function seo_category_html()
    {
        $this->assignPageParams('category');
        $enableSeo = 1;
        if ($this->getRequestContainer()) {
            if (Tools::getValue('controller') == 'AdminCategories') {
                $this->addTwigVar('ets_category_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_category_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
            }

        } else {
            $this->context->smarty->assign(array(
                'ets_category_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_category_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }
    }

    public function seo_manufacturer_html()
    {
        $this->assignPageParams('manufacturer');
        $enableSeo = 1;
        if ($this->getRequestContainer()) {
            if (Tools::getValue('controller') == 'AdminManufacturers') {
                $this->addTwigVar('ets_manufacturer_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_manufacturer_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
            }

        } else {
            $this->context->smarty->assign(array(
                'ets_manufacturer_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_manufacturer_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }
    }

    public function seo_supplier_html()
    {
        $this->assignPageParams('supplier');
        $enableSeo = 1;
        if($this->getRequestContainer()){
            if (Tools::getValue('controller') == 'AdminSuppliers') {
                $this->addTwigVar('ets_supplier_seo_setting_html', $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '');
                $this->addTwigVar('ets_supplier_seo_analysis_html', $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '');
                $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));
            }
        }
        else{
            $this->context->smarty->assign(array(
                'ets_supplier_seo_setting_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_setting.tpl') : '',
                'ets_supplier_seo_analysis_html' => $enableSeo ? $this->display(__FILE__, 'page/seo_analysis.tpl') : '',
                'ets_seo_preview_analysis' => $enableSeo ? $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl') : '',
            ));
        }

    }

    public function seo_product_html()
    {
        $request = $this->getRequestContainer();
        $id_product = 0;
        if ($request) {
            $id_product = $request->get('id');
        }

        $languages = Language::getLanguages(true);
        $seo_product = array(
            'link' => array(),
            'link_rewrite' => array(),
            'meta_description' => array(),
            'meta_title' => array(),
            'key_phrase' => array()
        );
        $images = array();
        $metaConfig = array();
        foreach ($languages as $lang) {
            $product = new Product($id_product, null, $lang['id_lang']);
            $seo_product['link'][$lang['id_lang']] = $this->context->link->getProductLink($product, null, null,null, $lang['id_lang']);
            $seo_product['link_rewrite'][$lang['id_lang']] = $product->link_rewrite;
            $seo_product['meta_title'][$lang['id_lang']] = $product->meta_title ? $product->meta_title : $product->name;
            $seo_product['meta_description'][$lang['id_lang']] = $product->meta_description ? $product->meta_description : $product->description_short;
            $images[$lang['id_lang']] = $product->getImages($lang['id_lang'], $this->context);
            $metaConfig[$lang['id_lang']] = array(
                'title' => (string)Configuration::get('ETS_SEO_PROD_META_TILE', $lang['id_lang']),
                'desc' => (string)Configuration::get('ETS_SEO_PROD_META_DESC', $lang['id_lang']),
            );
        }
        $current_lang = array(
            'id' => $this->context->language->id,
            'iso_code' => $this->context->language->iso_code
        );
        if(count($languages)){
            $current_lang = array(
                'id' => $languages[0]['id_lang'],
                'iso_code' => $languages[0]['iso_code']
            );
        }
        $ratingConfig = Configuration::get('ETS_SEO_RATING_PAGES') ? explode(',', Configuration::get('ETS_SEO_RATING_PAGES')) : array();
        $seoDef = Ets_Seo_Define::getInstance();
        $this->smarty->assign(array(
            'ets_seo_languages' => $languages,
            'tmp_dir' => dirname(__FILE__) . '/views/templates',
            'seo_data' => $seoDef->key_phrase_input('product', $id_product, $this->context),
            'current_lang' => $current_lang,
            'analysis_types' => $seoDef->analysis_types(),
            'seo_cms' => $seo_product,
            'id_product' => $id_product,
            'is_new_theme' => $this->getRequestContainer() ? true : false,
            'seo_enabled' => 1,
            'enable_force_rating' => in_array('product', $ratingConfig) ? true : false,
            'readability_enabled' => 1,
            'enable_rating' => $this->isInstalledRatingModule(),
            'rating_config' => EtsSeoRating::getRatingConfig('product', $id_product),
            'in_product_page' => true,
            'comment_product_data' => EtsSeoProduct::getCommentProductData($id_product),
            'product_image_data' => $images,
            'meta_config' => $metaConfig,
            'forceUseMetaTemplate' => (int)Configuration::get('ETS_SEO_PROD_FORCE_USE_META_TEMPLATE'),
            'show_friendly_url' => true,
            'message_explain' => EtsSeoTranslation::getAllTrans(),
            'page_title_trans' => EtsSeoTranslation::trans('product_name'),
            'seo_score_data' => EtsSeoSetting::getAnalysisScore('product', $id_product),
            'isAutoAnalysis' => (int)Configuration::get('ETS_SEO_ENABLE_AUTO_ANALYSIS'),
        ));

        $ets_seo_seo_enabled = 1;
        $ets_seo_readability_enabled = Module::isEnabled($this->name);
        if (!(int)Tools::getValue('ajax') && $this->getRequestContainer()) {

            $this->addTwigVar('ets_seo_is175', $this->is175);
            $this->addTwigVar('ets_seo_seo_enabled', $ets_seo_seo_enabled);
            $this->addTwigVar('ets_seo_readability_enabled', $ets_seo_readability_enabled);
            $this->addTwigVar('ets_seo_product_seo_analysis', $this->display(__FILE__, 'page/seo_analysis.tpl'));
            $this->addTwigVar('ets_seo_preview_analysis', $this->display(__FILE__, 'parts/_preview_seo_analysis.tpl'));

        }
    }

    /**
     * hookDisplayAdminProductsSeoStepBottom
     *
     * @param  mixed $params
     *
     * @return void
     */
    public function hookDisplayAdminProductsSeoStepBottom($params)
    {
        $id_product = isset($params['id_product']) ? $params['id_product'] : '';
        $languages = Language::getLanguages(true);
        $current_lang = array(
            'id' => $this->context->language->id,
            'iso_code' => $this->context->language->iso_code
        );

        $seoDef = Ets_Seo_Define::getInstance();
        $ratingConfig = Configuration::get('ETS_SEO_RATING_PAGES') ? explode(',', Configuration::get('ETS_SEO_RATING_PAGES')) : array();
        $this->smarty->assign(array(
            'languages' => $languages,
            'current_lang' => $current_lang,
            'analysis_types' => $seoDef->analysis_types(),
            'seo_advanced' => $seoDef->seo_advanced('product', $id_product, $this->context),
            'seo_enabled' => 1,
            'enable_force_rating' => in_array('product', $ratingConfig) ? true : false,
            'enable_rating' => $this->isInstalledRatingModule(),
            'rating_config' => EtsSeoRating::getRatingConfig('product', $id_product),
            'rating_setting' => EtsSeoRating::getRating('product', $id_product),
        ));
        return $this->display(__FILE__, 'page/seo_setting.tpl');
    }

    public function isInstalledRatingModule()
    {
        if (Module::isInstalled('productcomments') && Module::isEnabled('productcomments')) {
            return true;
        }

        return false;
    }


    /**
     * uploadImage
     *
     * @param  string $key
     * @param  string $type : img or cache
     * @param  int $id
     * @param  int $id_lang
     *
     * @return void
     */
    public function uploadImage($key, $type = 'img')
    {
        if ($type) {

        }
        if (isset($_FILES[$key])) {
            $allowExtentions = array('png', 'jpg', 'jpeg', 'gif');
            $ext = Tools::strtolower(Tools::substr(strrchr($_FILES[$key]['name'], '.'), 1));
            if ($_FILES[$key]['error'] <= 0 && in_array($ext, $allowExtentions) && in_array($ext, $allowExtentions)) {
                $img_name = time() . rand(1111, 99999) . '.' . $ext;
                $img_path = '/img/social/';

                if (!ImageManager::validateUpload($_FILES[$key], 2097152)) {
                    if (move_uploaded_file($_FILES[$key]['tmp_name'], _PS_ROOT_DIR_ . $img_path . $img_name)) {
                        return __PS_BASE_URI__ . ltrim($img_path, '/') . $img_name;
                    }
                }


            }
        }
        return false;
    }

    /**
     * hookActionObjectAddAfter
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionObjectAddAfter($params)
    {
        $seoSetting = EtsSeoSetting::getInstance();
        $seoSetting->updateSeoCms($params);
        $seoSetting->updateSeoMeta($params);
        $seoSetting->updateSeoCategory($params);
        $seoSetting->updateSeoCmsCategory($params);
        $seoSetting->updateSeoManufacturer($params);
        $seoSetting->updateSeoSupplier($params);
    }


    /**
     * hookActionObjectUpdateAfter
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionObjectUpdateAfter($params)
    {
        $seoSetting = EtsSeoSetting::getInstance();
        $seoSetting->updateSeoCms($params);
        $seoSetting->updateSeoMeta($params);
        $seoSetting->updateSeoCategory($params);
        $seoSetting->updateSeoCmsCategory($params);
        $seoSetting->updateSeoManufacturer($params);
        $seoSetting->updateSeoSupplier($params);

        if (!$this->getRequestContainer() && (int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
        $this->processAfterSaveConfig();
    }
    }

    public function hookActionObjectAddBefore($params)
    {
        if(!defined('_PS_ADMIN_DIR_'))
        {
            return;
        }
        if ($this->getRequestContainer()) {
            //Removed
        } else {
            $this->validateLinkRewrite($params);
        }

    }

    public function hookActionObjectUpdateBefore($params)
    {
        if(!defined('_PS_ADMIN_DIR_'))
        {
            return;
        }
        if ($this->getRequestContainer() ) {
            if (isset($params['object']) && $params['object'] instanceof Product && preg_match('/sell\/catalog\/products/', $_SERVER['REQUEST_URI'])) {
                if ($error = EtsSeoSetting::validateLinkRewrite('product', $params['object']->link_rewrite, (int)$params['object']->id, $this->context)) {
                    throw new PrestaShopException($this->l('The Friendly url "' . $error . '" ' . $this->l('has been taken')));
                }
                $error = null;
                $seoAdvanced = ($seoAdvanced = Tools::getValue('ets_seo_advanced')) && is_array($seoAdvanced) ? $seoAdvanced : array();
                if (isset($seoAdvanced['canonical_url']) && !$error) {
                    foreach ($seoAdvanced['canonical_url'] as $id_lang => $url) {
                        if ($url && !Validate::isAbsoluteUrl($url)) {
                            $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The Canonical url must start with http:// or https:// ');
                            break;
                        }
                    }
                }
                if (!$error && ($minorKeyphrase = Tools::getValue('ets_seo_minor_keyphrase')) && ($keyphrase = Tools::getValue('ets_seo_key_phrase'))) {
                    $seoSetting = EtsSeoSetting::getInstance();
                    if (is_array($keyphrase)) {
                        foreach ($keyphrase as $id_lang => $key) {
                            if (isset($minorKeyphrase[$id_lang]) && $minorKeyphrase[$id_lang] && $key) {
                                $minor = $seoSetting->getMinorKeyphrase($minorKeyphrase[$id_lang]);
                                if ($minor && in_array(trim($key), explode(',', $minor))) {
                                    $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The related keyphrase is the same as focus keyphrase');
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($error) {
                    throw new PrestaShopException($error);
                }
            }
        } else {
            $this->validateLinkRewrite($params);
        }
        $this->updateConfigSeoNoid();
    }

    public function formHandleLinkRewrite($params, $type)
    {
        $id = isset($params['id']) ? (int)$params['id'] : null;
        $redirectUrl = null;
        switch ($type) {
            case 'category':
                if ($id) {
                    $redirectUrl = $this->context->link->getAdminLink('AdminCategories', true,
                        array('route' => 'admin_categories_edit', 'categoryId' => $id),
                        array('id_category' => $id, 'updatecategory' => true));
                } else {
                    $idParent = (int)$params['form_data']['id_parent'];
                    $redirectUrl = $this->context->link->getAdminLink('AdminCategories', true,
                        array('route' => 'admin_categories_create', 'id_parent' => $idParent),
                        array('addcategory' => true));
                }
                break;
            case 'cms':
                if ($id) {
                    $redirectUrl = $this->context->link->getAdminLink('AdminCms', true,
                        array('route' => 'admin_cms_pages_edit', 'cmsPageId' => $id),
                        array('id_cms' => $id, 'updatecms' => true));
                } else {
                    $idParent = (int)$params['form_data']['id_cms_category'];
                    $redirectUrl = $this->context->link->getAdminLink('AdminCms', true,
                        array('route' => 'admin_cms_pages_create', 'id_cms_category' => $idParent),
                        array('addcms' => true, 'id_cms_category' => $idParent));
                }
                break;
            case 'cms_category':
                if ($id) {
                    $redirectUrl = $this->context->link->getAdminLink('AdminCmsCategories', true,
                        array('route' => 'admin_cms_pages_category_edit', 'cmsCategoryId' => $id),
                        array('id_cms_category' => $id, 'updatecms_category' => true));
                } else {
                    $redirectUrl = $this->context->link->getAdminLink('AdminCmsCategories', true,
                        array('route' => 'admin_cms_pages_category_create'),
                        array('addcms_category' => true));
                }
                break;
            case 'meta':
                if ($id) {
                    $redirectUrl = $this->context->link->getAdminLink('AdminMeta', true,
                        array('route' => 'admin_metas_edit', 'metaId' => $id),
                        array('id_meta' => $id, 'updatemeta' => true));
                } else {
                    $redirectUrl = $this->context->link->getAdminLink('AdminMeta', true,
                        array('route' => 'admin_metas_create'),
                        array('addmeta' => true));
                }
                break;
        }
        //fom_data
        $linkRewrites = '';
        if (isset($params['form_data']['link_rewrite'])) {
            $linkRewrites = $params['form_data']['link_rewrite'];
        } elseif (isset($params['form_data']['url_rewrite'])) {
            $linkRewrites = $params['form_data']['url_rewrite'];
        } elseif (isset($params['form_data']['friendly_url'])) {
            $linkRewrites = $params['form_data']['friendly_url'];
        }

        $error = null;
        $error = EtsSeoSetting::validateLinkRewrite($type, $linkRewrites, $id, $this->context);
        if ($error) {
            $this->l('The link rewrite') . ' "' . $error . '" ' . $this->l('has been taken');
        }
        $seoAdvanced = Tools::getValue('ets_seo_advanced');
        if (isset($seoAdvanced['canonical_url']) && !$error) {
            foreach ($seoAdvanced['canonical_url'] as $id_lang => $url) {
                if ($url && !Validate::isAbsoluteUrl($url)) {
                    $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The Canonical url must start with http:// or https:// ');
                    break;
                }
            }
        }
        if (!$error && ($minorKeyphrase = Tools::getValue('ets_seo_minor_keyphrase')) && ($keyphrase = Tools::getValue('ets_seo_key_phrase'))) {
            $seoSetting = EtsSeoSetting::getInstance();
            if (is_array($keyphrase)) {
                foreach ($keyphrase as $id_lang => $key) {
                    if (isset($minorKeyphrase[$id_lang]) && $minorKeyphrase[$id_lang] && $key) {
                        $minor = $seoSetting->getMinorKeyphrase($minorKeyphrase[$id_lang]);
                        if ($minor && in_array(trim($key), explode(',', $minor))) {
                            $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The related keyphrase is the same as focus keyphrase');
                            break;
                        }
                    }
                }
            }
        }

        if ($error) {
            $params['form_data']['ets_seo_error'] = $error;
            $fileName = time() . rand(1111, 99999) . '.json';
            file_put_contents(dirname(__FILE__) . '/cache/' . $fileName, Tools::jsonEncode($params['form_data']));
            $this->context->cookie->ets_seo_form_validate_data = $fileName;
            $this->context->cookie->write();
            Tools::redirectAdmin($redirectUrl);
        }

    }

    public function setFormBuilderModifier(&$params)
    {
        if ($fileData = $this->context->cookie->__get('ets_seo_form_validate_data')) {
            $data = array();
            if (file_exists(dirname(__FILE__) . '/cache/' . $fileData)) {
                $json = Tools::file_get_contents(dirname(__FILE__) . '/cache/' . $fileData);
                $data = Tools::jsonDecode($json, true);
                unlink(dirname(__FILE__) . '/cache/' . $fileData);
            }
            $error = '';
            if (isset($data['ets_seo_error'])) {
                $error = $data['ets_seo_error'];
                unset($data['ets_seo_error']);
            }
            $params['data'] = array_merge($params['data'], $data);
            $params['form_builder']->setData($params['data']);
            $this->context->cookie->__unset('ets_seo_form_validate_data');
            $this->context->cookie->__set('ets_seo_error_link_rewrite', $error);

        }
    }

    /* == Category ===*/
    public function hookActionBeforeUpdateCategoryFormHandler($params)
    {
        $this->formHandleLinkRewrite($params, 'category');
    }

    public function hookActionBeforeCreateCategoryFormHandler($params)
    {
        //fom_data
        $this->formHandleLinkRewrite($params, 'category');
    }

    public function hookActionCategoryFormBuilderModifier($params)
    {
        $this->setFormBuilderModifier($params);
    }

    //Root category
    public function hookActionBeforeUpdateRootCategoryFormHandler($params)
    {
        $this->formHandleLinkRewrite($params, 'category');
    }

    public function hookActionBeforeCreateRootCategoryFormHandler($params)
    {
        //fom_data
        $this->formHandleLinkRewrite($params, 'category');
    }

    public function hookActionRootCategoryFormBuilderModifier($params)
    {
        $this->setFormBuilderModifier($params);
    }

    /* = CMS ==*/
    public function hookActionBeforeUpdateCmsPageFormHandler($params)
    {
        $this->formHandleLinkRewrite($params, 'cms');
    }

    public function hookActionBeforeCreateCmsPageFormHandler($params)
    {
        //fom_data
        $this->formHandleLinkRewrite($params, 'cms');
    }

    public function hookActionCmsPageFormBuilderModifier($params)
    {
        $this->setFormBuilderModifier($params);
    }

    /* = CMS Category ==*/
    public function hookActionBeforeUpdateCmsPageCategoryFormHandler($params)
    {
        $this->formHandleLinkRewrite($params, 'cms_category');
    }

    public function hookActionBeforeCreateCmsPageCategoryFormHandler($params)
    {
        //fom_data
        $this->formHandleLinkRewrite($params, 'cms_category');
    }

    public function hookActionCmsPageCategoryFormBuilderModifier($params)
    {
        $this->setFormBuilderModifier($params);
    }

    /* = Meta ==*/
    public function hookActionBeforeUpdateMetaFormHandler($params)
    {
        $this->formHandleLinkRewrite($params, 'meta');
    }

    public function hookActionBeforeCreateMetaFormHandler($params)
    {
        //fom_data
        $this->formHandleLinkRewrite($params, 'meta');
    }

    public function hookActionMetaFormBuilderModifier($params)
    {
        $this->setFormBuilderModifier($params);
    }

    public function validateLinkRewrite($params)
    {
        if (!(int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
            return;
        }
        if (isset($params['object'])) {
            $type = null;
            $link_rewrites = null;
            $obj = $params['object'];
            $idCol = '';
            if ($obj instanceof Product) {
                $type = 'product';
                $link_rewrites = $obj->link_rewrite;
                $idCol = 'id_product';
            } elseif ($obj instanceof Category) {
                $type = 'category';
                $link_rewrites = $obj->link_rewrite;
                $idCol = 'id_category';
            } elseif ($obj instanceof CMS) {
                $type = 'cms';
                $link_rewrites = $obj->link_rewrite;
                $idCol = 'id_cms';
            } elseif ($obj instanceof CMSCategory) {
                $type = 'cms_category';
                $link_rewrites = $obj->link_rewrite;
                $idCol = 'id_cms_category';
            } elseif ($obj instanceof Meta) {
                $type = 'meta';
                $link_rewrites = $obj->url_rewrite;
                $idCol = 'id_meta';
            }

            if (!$type) {
                return;
            }
            $error = null;
            $error = EtsSeoSetting::validateLinkRewrite($type, $link_rewrites, $obj->id, $this->context);
            if ($error) {
                $error = $this->l('The Friendly url') . " " . $error . " " . $this->l(' has been taken');
            }
            $seoAdvanced = Tools::getValue('ets_seo_advanced');
            if (isset($seoAdvanced['canonical_url']) && !$error) {
                foreach ($seoAdvanced['canonical_url'] as $id_lang => $url) {
                    if ($url && !Validate::isAbsoluteUrl($url)) {
                        $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The Canonical url must start with http:// or https:// ');
                        break;
                    }
                }
            }
            if (!$error && ($minorKeyphrase = Tools::getValue('ets_seo_minor_keyphrase')) && ($keyphrase = Tools::getValue('ets_seo_key_phrase'))) {
                $seoSetting = EtsSeoSetting::getInstance();
                if (is_array($keyphrase)) {
                    foreach ($keyphrase as $id_lang => $key) {
                        if (isset($minorKeyphrase[$id_lang]) && $minorKeyphrase[$id_lang] && $key) {
                            $minor = $seoSetting->getMinorKeyphrase($minorKeyphrase[$id_lang]);
                            if ($minor && in_array(trim($key), explode(',', $minor))) {
                                $error = '[' . Language::getIsoById($id_lang) . '] ' . $this->l('The related keyphrase is the same as focus keyphrase');
                                break;
                            }
                        }
                    }
                }
            }
            if ($error) {
                if ($type !== 'cms') {
                    throw new PrestaShopException($error);
                } else {
                    $controller = ($controller = Tools::getValue('controller')) && Validate::isCleanHtml($controller) ? $controller : '';
                    $this->context->cookie->__set('ets_seo_error_link_rewrite', $error);
                    if ($obj->id) {
                        $redirectUrl = $this->context->link->getAdminLink($controller, true, array(), array(
                            $idCol => $obj->id,
                            'updatecms' => true
                        ));
                    } else {
                        $redirectUrl = $this->context->link->getAdminLink($controller, true, array(), array(
                            $idCol => $obj->id,
                            'id_cms_category' => $obj->id_cms_category,
                            'addcms' => true
                        ));
                    }

                    Tools::redirectAdmin($redirectUrl);
                }

            }
        }

    }


    public function processAfterSaveConfig()
    {
        Tools::clearCache();
        /*Update product has duplicate link_rewrite*/
        if ((int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
            if (!(int)Configuration::get('ETS_SEO_UPDATE_DUPLICATE_REWRITE')) {
                $seoUpdating = new EtsSeoUpdating();
                $seoUpdating->updateDuplicateProduct();
                $seoUpdating->updateDuplicateCategory();
                $seoUpdating->updateDuplicateCMS();
                $seoUpdating->updateDuplicateCMSCategory();
                $seoUpdating->updateDuplicateMeta();
                Configuration::updateValue('ETS_SEO_UPDATE_DUPLICATE_REWRITE', 1);
            }
        }

        /*Delete cache in others module to accept new configurations*/
        if ((int)Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') && Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed') && class_exists('Ets_ss_class_cache')) {
            $cacheObjSuperSpeed = new Ets_ss_class_cache();
            if (method_exists($cacheObjSuperSpeed, 'deleteCache'))
                $cacheObjSuperSpeed->deleteCache('index');
        }
        if ((int)Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') && Module::isInstalled('ets_pagecache') && Module::isEnabled('ets_pagecache') && class_exists('Ets_pagecache_class_cache')) {
            $cacheObjPageCache = new Ets_ss_class_cache();
            if (method_exists($cacheObjPageCache, 'deleteCache'))
                $cacheObjPageCache->deleteCache('index');
        }
    }

    /**
     * copy_directory
     *
     * @param  string $src
     * @param  string $dst
     *
     * @return void
     */
    public function copy_directory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst, 0777, true); // PS9: création récursive (le dossier parent peut ne pas exister)
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copy_directory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    if (file_exists($dst . '/' . $file) && $file != 'index.php' && ($content = Tools::file_get_contents($dst . '/' . $file)) && Tools::strpos($content, 'overried_by_hinh_ets') === false)
                        copy($dst . '/' . $file, $dst . '/backup_' . $file);
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * delete_directory
     *
     * @param  string $directory
     *
     * @return void
     */
    public function delete_directory($directory)
    {
        $dir = opendir($directory);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($directory . '/' . $file)) {
                    $this->delete_directory($directory . '/' . $file);
                } else {
                    if (file_exists($directory . '/' . $file) && $file != 'index.php' && ($content = Tools::file_get_contents($directory . '/' . $file)) && Tools::strpos($content, 'overried_by_hinh_ets') !== false) {
                        @unlink($directory . '/' . $file);
                        if (file_exists($directory . '/backup_' . $file))
                            copy($directory . '/backup_' . $file, $directory . '/' . $file);
                    }

                }
            }
        }
        closedir($dir);
    }

    /**
     * hookDisplayAdminAfterHeader
     *
     * @return void
     */
    public function hookDisplayAdminAfterHeader()
    {

        $seoDef = Ets_Seo_Define::getInstance();
        $controller = Tools::getValue('controller');
        $menus = array();
        $submenus = array();
        $showMenu = false;
        $all_menus = $seoDef->get_menus();
        if ($trafficControllers = $seoDef->traffic_seo_tabs()) {
            if (in_array($controller, $trafficControllers)) {
                $showMenu = true;
            }
        }
        $tabArray = array();
        foreach ($all_menus as $k => $menu) {
            if (!$showMenu && $controller == $k) {
                $showMenu = true;
            }
            if (isset($menu['parent_controller']) && $menu['parent_controller']) {
                $submenus[$k] = $menu;
            } else {
                $menus[$k] = $menu;
            }
            if ($k == 'AdminEtsSeoUrlAndRemoveId') {
                $id_tab = Tab::getIdFromClassName('AdminMeta');
                $tab = Tab::getTab($this->context->language->id, $id_tab);
                $submenus['AdminMeta'] = array(
                    'title' => $this->l('URL structure and remove IDs'),
                    'controller' => 'AdminMeta',
                    'link' => $this->context->link->getAdminLink('AdminMeta', true),
                    'parent_controller' => 'AdminEtsSeoUrlAndRemoveId',
                    'icon' => 'code',
                    'menu_icon' => 'menu-icon-meta',
                );
                $tabArray['AdminMeta'] = $submenus['AdminMeta'];
            } elseif ($k == 'AdminEtsSeoTraffic') {
                $id_tab = Tab::getIdFromClassName('AdminSearchEngines');
                $tab = Tab::getTab($this->context->language->id, $id_tab);
                $submenus['AdminSearchEngines'] = array(
                    'title' => $tab['name'],
                    'controller' => 'AdminSearchEngines',
                    'link' => $this->context->link->getAdminLink('AdminSearchEngines', true),
                    'parent_controller' => 'AdminEtsSeoTraffic',
                    'menu_icon' => 'menu-icon-search-engines',
                    'icon' => 'code',
                );
                $tabArray['AdminSearchEngines'] = $submenus['AdminSearchEngines'];
                $id_tab = Tab::getIdFromClassName('AdminReferrers');
                $tab = Tab::getTab($this->context->language->id, $id_tab);
                $submenus['AdminReferrers'] = array(
                    'title' => $tab['name'],
                    'controller' => 'AdminReferrers',
                    'link' => $this->context->link->getAdminLink('AdminReferrers', true),
                    'parent_controller' => 'AdminEtsSeoTraffic',
                    'menu_icon' => 'menu-icon-referrers',
                    'icon' => 'code',
                );
                $tabArray['AdminReferrers'] = $submenus['AdminReferrers'];
            }
        }

        if (!$showMenu) {
            return;
        }

        $all_menus = array_merge($tabArray, $all_menus);
        $parent_controller = null;
        $current_controller = Tools::getValue('controller', '');
        if (isset($all_menus[$current_controller]['parent_controller']) && $all_menus[$current_controller]['parent_controller']) {
            $parent_controller = $all_menus[$current_controller]['parent_controller'];
        }
        $page_name = '';
        if ($current_controller == 'AdminMeta') {
            if ($request = $this->getRequestContainer()) {
                $metaId = $request ? $request->get('metaId') : null;
                if ($metaId) {
                    $meta = new Meta($metaId, $this->context->language->id);
                    $page_name = $this->formatSeoMeta($meta->title, array('post_title' => '', 'is_title' => true), 'meta');
                }
            } else {
                if ($metaId = (int)Tools::getValue('id_meta')) {
                    $meta = new Meta($metaId, $this->context->language->id);
                    $page_name = $this->formatSeoMeta($meta->title, array('post_title' => '', 'is_title' => true), 'meta');
                }
            }
        }
        $intro = true;
        $localIps = array(
            '127.0.0.1',
            '::1'
        );
        $baseURL = Tools::strtolower(self::getBaseModLink());
        if (!Tools::isSubmit('intro') && (in_array(Tools::getRemoteAddr(), $localIps) || preg_match('/^.*(localhost|demo|test|dev|:\d+).*$/i', $baseURL)))
            $intro = false;
        $this->smarty->assign(array(
            'all_menus' => $all_menus,
            'menus' => $menus,
            'submenus' => $submenus,
            'current_controller' => $current_controller,
            'parent_controller' => $parent_controller,
            'page_name' => $page_name,
            'controller_link' => $page_name ? $this->context->link->getAdminLink($current_controller) : '',
            'dashboard_controller' => $all_menus['AdminEtsSeoGeneralDashboard'],
            'other_modules_link' => isset($this->refs) ? $this->refs.$this->context->language->iso_code : $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name . '&othermodules=1',
            'intro' => $intro,
            'refsLink' => isset($this->refs) ? $this->refs.$this->context->language->iso_code : false,
        ));

        return $this->display(__FILE__, 'admin_menu.tpl') . $this->display(__FILE__, 'admin_breadcrumb.tpl');
    }

    public static function getBaseModLink()
    {
        $context = Context::getContext();
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $context->shop->domain . $context->shop->getBaseURI();
    }

    public function displayRecommendedModules()
    {
        $cacheDir = dirname(__file__) . '/../../cache/' . $this->name . '/';
        $cacheFile = $cacheDir . 'module-list.xml';
        $cacheLifeTime = 24;
        $cacheTime = (int)Configuration::getGlobalValue('ETS_MOD_CACHE_' . $this->name);
        $profileLinks = array(
            'en' => 'https://addons.prestashop.com/en/207_ets-soft',
            'fr' => 'https://addons.prestashop.com/fr/207_ets-soft',
            'it' => 'https://addons.prestashop.com/it/207_ets-soft',
            'es' => 'https://addons.prestashop.com/es/207_ets-soft',
        );
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
            if (@file_exists(dirname(__file__) . '/index.php')) {
                @copy(dirname(__file__) . '/index.php', $cacheDir . 'index.php');
            }
        }
        if (!file_exists($cacheFile) || !$cacheTime || time() - $cacheTime > $cacheLifeTime * 60 * 60) {
            if (file_exists($cacheFile))
                @unlink($cacheFile);
            if ($xml = self::file_get_contents($this->shortlink . 'ml.xml')) {
                $xmlData = @simplexml_load_string($xml);
                if ($xmlData && (!isset($xmlData->enable_cache) || (int)$xmlData->enable_cache)) {
                    @file_put_contents($cacheFile, $xml);
                    Configuration::updateGlobalValue('ETS_MOD_CACHE_' . $this->name, time());
                }
            }
        } else
            $xml = Tools::file_get_contents($cacheFile);
        $modules = array();
        $categories = array();
        $categories[] = array('id' => 0, 'title' => $this->l('All categories'));
        $enabled = true;
        $iso = Tools::strtolower($this->context->language->iso_code);
        $moduleName = $this->displayName;
        $contactUrl = '';
        if ($xml && ($xmlData = @simplexml_load_string($xml))) {
            if (isset($xmlData->modules->item) && $xmlData->modules->item) {
                foreach ($xmlData->modules->item as $arg) {
                    if ($arg) {
                        if (isset($arg->module_id) && (string)$arg->module_id == $this->name && isset($arg->{'title' . ($iso == 'en' ? '' : '_' . $iso)}) && (string)$arg->{'title' . ($iso == 'en' ? '' : '_' . $iso)})
                            $moduleName = (string)$arg->{'title' . ($iso == 'en' ? '' : '_' . $iso)};
                        if (isset($arg->module_id) && (string)$arg->module_id == $this->name && isset($arg->contact_url) && (string)$arg->contact_url)
                            $contactUrl = $iso != 'en' ? str_replace('/en/', '/' . $iso . '/', (string)$arg->contact_url) : (string)$arg->contact_url;
                        $temp = array();
                        foreach ($arg as $key => $val) {
                            if ($key == 'price' || $key == 'download')
                                $temp[$key] = (int)$val;
                            elseif ($key == 'rating') {
                                $rating = (float)$val;
                                if ($rating > 0) {
                                    $ratingInt = (int)$rating;
                                    $ratingDec = $rating - $ratingInt;
                                    $startClass = $ratingDec >= 0.5 ? ceil($rating) : ($ratingDec > 0 ? $ratingInt . '5' : $ratingInt);
                                    $temp['ratingClass'] = 'mod-start-' . $startClass;
                                } else
                                    $temp['ratingClass'] = '';
                            } elseif ($key == 'rating_count')
                                $temp[$key] = (int)$val;
                            else
                                $temp[$key] = (string)strip_tags($val);
                        }
                        if ($iso) {
                            if (isset($temp['link_' . $iso]) && isset($temp['link_' . $iso]))
                                $temp['link'] = $temp['link_' . $iso];
                            if (isset($temp['title_' . $iso]) && isset($temp['title_' . $iso]))
                                $temp['title'] = $temp['title_' . $iso];
                            if (isset($temp['desc_' . $iso]) && isset($temp['desc_' . $iso]))
                                $temp['desc'] = $temp['desc_' . $iso];
                        }
                        $modules[] = $temp;

                    }
                }
            }
            if (isset($xmlData->categories->item) && $xmlData->categories->item) {
                foreach ($xmlData->categories->item as $arg) {
                    if ($arg) {
                        $temp = array();
                        foreach ($arg as $key => $val) {
                            $temp[$key] = (string)strip_tags($val);
                        }
                        if (isset($temp['title_' . $iso]) && $temp['title_' . $iso])
                            $temp['title'] = $temp['title_' . $iso];
                        $categories[] = $temp;
                    }
                }
            }
        }
        if (isset($xmlData->{'intro_' . $iso}))
            $intro = $xmlData->{'intro_' . $iso};
        else
            $intro = isset($xmlData->intro_en) ? $xmlData->intro_en : false;
        $this->smarty->assign(array(
            'modules' => $modules,
            'enabled' => $enabled,
            'module_name' => $moduleName,
            'categories' => $categories,
            'img_dir' => $this->_path . 'views/img/',
            'intro' => $intro,
            'shortlink' => $this->shortlink,
            'ets_profile_url' => isset($profileLinks[$iso]) ? $profileLinks[$iso] : $profileLinks['en'],
            'trans' => array(
                'txt_must_have' => $this->l('Must-Have'),
                'txt_downloads' => $this->l('Downloads!'),
                'txt_view_all' => $this->l('View all our modules'),
                'txt_fav' => $this->l('Prestashop\'s favourite'),
                'txt_elected' => $this->l('Elected by merchants'),
                'txt_superhero' => $this->l('Superhero Seller'),
                'txt_partner' => $this->l('Module Partner Creator'),
                'txt_contact' => $this->l('Contact us'),
                'txt_close' => $this->l('Close'),
            ),
            'contactUrl' => $contactUrl,
        ));
        echo $this->display(__FILE__, 'module-list.tpl');
        die;
    }

    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl" => array(
                    "allow_self_signed" => true,
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }

    public function arrayInsert(&$array, $position, $insert)
    {
        if (is_int($position)) {
            array_splice($array, $position, 0, $insert);
        } else {
            $pos = array_search($position, array_keys($array));
            $array = array_merge(
                array_slice($array, 0, $pos),
                $insert,
                array_slice($array, $pos)
            );
        }
    }

    /**
     * actionAdminProductsListingFieldsModifier
     *
     * @return void
     */
    public function hookActionAdminProductsListingFieldsModifier($params)
    {

        $filter_seo_score = Tools::getValue('filter_ets_seo_score', '');
        $filter_readability_score = Tools::getValue('filter_ets_seo_readability', '');

        $sql_select = array(
            'seo_score' => array(
                'table' => 'esp',
                'field' => 'seo_score',
                'filtering' => ' %s '
            ),
            'readability_score' => array(
                'table' => 'esp',
                'field' => 'readability_score',
                'filtering' => ' %s '
            ),
            'key_phrase' => array(
                'table' => 'esp',
                'field' => 'key_phrase',
                'filtering' => ' %s '
            ),
        );

        $sql_table = array(
            'esp' => array(
                'table' => 'ets_seo_product',
                'join' => 'LEFT JOIN',
                'on' => 'esp.`id_product` = p.`id_product` AND esp.`id_shop` = ' . (int)$this->context->shop->id . ' AND esp.`id_lang` = ' . (int)$this->context->language->id
            )
        );

        $params['sql_select'] = array_merge($params['sql_select'], $sql_select);
        $params['sql_table'] = array_merge($params['sql_table'], $sql_table);
        $sqlOverAllSeoScore = " (esp.`seo_score` / (".ETS_TOTAL_SEO_RULE_SCORE." * 9) * 10)";
        $sqlOverAllReadabilityScore = " (esp.`readability_score` / (".ETS_TOTAL_READABILITY_RULE_SCORE." * 9) * 10)";
        if ($filter_seo_score == 'bad') {
            $params['sql_where'][] = $sqlOverAllSeoScore."<= 4 ";
        } elseif ($filter_seo_score == 'ok') {
            $params['sql_where'][] = $sqlOverAllSeoScore." > 4 AND ".$sqlOverAllSeoScore." <= 7 ";
        } elseif ($filter_seo_score == 'good') {
            $params['sql_where'][] = $sqlOverAllSeoScore." > 7 ";
        } elseif ($filter_seo_score == 'na') {
            $params['sql_where'][] = " (esp.`key_phrase` IS NULL OR esp.`key_phrase` = '') ";
        } elseif ($filter_seo_score == 'noindex') {
            if (!(int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT')) {
                $params['sql_where'][] = " (esp.`allow_search` = 0 OR esp.`allow_search` = 2)";
            } else {
                $params['sql_where'][] = " (esp.`allow_search` = 0)";
            }
        }

        if ($filter_readability_score == 'bad') {
            $params['sql_where'][] = $sqlOverAllReadabilityScore." <= 4 ";
        } elseif ($filter_readability_score == 'ok') {
            $params['sql_where'][] = $sqlOverAllReadabilityScore." > 4 AND ".$sqlOverAllReadabilityScore." <= 7 ";
        } elseif ($filter_readability_score == 'good') {
            $params['sql_where'][] = $sqlOverAllReadabilityScore." > 7 ";
        }

    }

    /**
     * hookActionAdminProductsListingResultsModifier
     *
     * @param  mixed $params
     *
     * @return void
     */
    public function hookActionAdminProductsListingResultsModifier($params)
    {
        if (isset($params['products']) && is_array($params['products'])) {
            if(!$params['products']){
                $params['products'] = array(
                    array('id_product' => 0, 'total' => false)
                );
                $params['total'] = 0;
            }
            $products = $params['products'];
            if (!$products) {
                return;
            }
            $products = $this->modifyResultList('product', $products);
            $params['products'] = $products;
        }
    }

    /**
     * getIdCurrentPage
     *
     * @return void
     */
    public function getIdCurrentPage()
    {
        if ($controller = Tools::getValue('controller', false)) {
            if (!$this->is176) {
                if ($controller == 'AdminCmsContent') {
                    return Tools::getIsset('updatecms_category') ? (int)Tools::getValue('id_cms_category', 0) : (int)Tools::getValue('id_cms', 0);
                }
                if ($controller == 'AdminMeta') {
                    return (int)Tools::getValue('id_meta', 0);
                }
                if ($controller == 'AdminCategories') {
                    return (int)Tools::getValue('id_category', 0);
                }
                if ($controller == 'AdminManufacturers') {
                    return (int)Tools::getValue('id_manufacturer', 0);
                }
                if ($controller == 'AdminSuppliers') {
                    return (int)Tools::getValue('id_supplier', 0);
                }

            }

            $sfContainer = $this->getSfContainer();
            if (null !== $sfContainer && null !== $sfContainer->get('request_stack')->getCurrentRequest()) {
                $request = $sfContainer->get('request_stack')->getCurrentRequest();
                if ($controller == 'AdminProducts') {
                    return $request->get('id');
                }
                if ($controller == 'AdminCmsContent') {
                    if ($request->get('_route') == 'admin_cms_pages_category_edit' || $request->get('_route') == 'admin_cms_pages_category_create') {
                        return $request->get('cmsCategoryId');
                    }
                    return $request->get('cmsPageId');
                }
                if ($controller == 'AdminMeta') {
                    return $request->get('metaId');
                }

                if ($controller == 'AdminCategories') {
                    return $request->get('categoryId');
                }
                if ($controller == 'AdminManufacturers') {
                    return $request->get('manufacturerId');
                }
                if ($controller == 'AdminSuppliers') {
                    return $request->get('supplierId');
                }
            }
        }

        return 0;

    }

    public function isCmsCategoryPage()
    {
        $request = $this->getRequestContainer();
        if ($request) {
            if ($request->get('_route') == 'admin_cms_pages_category_edit' || $request->get('_route') == 'admin_cms_pages_category_create') {
                return true;
            }
        } else {
            if (Tools::getIsset('addcms_category') || Tools::getIsset('updatecms_category')) {
                return true;
            }
        }
        return false;

    }

    public function hookActionAdminCmsListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('cms', $params['list']);
        }
    }

    public function hookActionAdminCmsCategoriesListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('cms_category', $params['list']);
        }
    }

    /**
     * hookActionAdminCmsListingResultsModifier
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionAdminMetaListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('meta', $params['list']);

        }
    }

    /**
     * hookActionAdminCmsListingResultsModifier
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionAdminCategoriesListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('category', $params['list']);

        }
    }

    public function hookActionAdminManufacturersListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('manufacturer', $params['list']);

        }
    }

    public function hookActionAdminSuppliersListingResultsModifier($params)
    {
        if (isset($params['list']) && is_array($params['list'])) {
            $params['list'] = $this->modifyResultList('supplier', $params['list']);

        }
    }

    /**
     * array_merge
     *
     * @return array
     */
    public function get_fields_list_page($sort_name)
    {
        $def = array();
        if (1) {
            $def = array_merge($def, array(
                'seo_score' => array(
                    'title' => $this->l('SEO Score'),
                    'align' => 'text-center',
                    'float' => true,
                    'type' => 'select',
                    'filter_key' => $sort_name . '!seo_score',
                    'orderby' => false,
                    'list' => array(
                        'bad' => $this->l('SEO: Not good'),
                        'ok' => $this->l('SEO: Acceptable'),
                        'good' => $this->l('SEO: Excellent'),
                        'na' => $this->l('SEO: No Focus or Related key phrases'),
                        'noindex' => $this->l('SEO: No Index'),
                    )
                )
            ));
        }
        if (1) {
            $def = array_merge($def, array(
                'readability_score' => array(
                    'title' => $this->l('Readability Score'),
                    'align' => 'text-center',
                    'float' => true,
                    'type' => 'select',
                    'orderby' => false,
                    'filter_key' => $sort_name . '!readability_score',
                    'list' => array(
                        'bad' => $this->l('Readability: Not good'),
                        'ok' => $this->l('Readability: Acceptable'),
                        'good' => $this->l('Readability: Excellent'),
                    )
                )
            ));
        }
        return $def;
    }


    public function actionAdminCmsChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, false, $tblName);

    }

    public function actionAdminCmsCategoryChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, true, $tblName);

    }

    public function actionAdminMetaChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, false, $tblName);
    }

    public function actionAdminCategoriesChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, false, $tblName);
    }

    public function actionAdminManufacturerChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, false, $tblName);
    }

    public function actionAdminSupplierChangeFilter(&$filters, $tblName = null)
    {
        $this->__actionListChangeFilter($filters, false, $tblName);
    }

    protected function __actionListChangeFilter(&$filters, $is_cms_category = false, $tblName = '')
    {
        $controller = Tools::getValue('controller');

        if ($controller == 'AdminMeta'
            || $controller == 'AdminCmsContent'
            || $controller == 'AdminCategories'
            || $controller == 'AdminSuppliers'
            || $controller == 'AdminManufacturers') {

            $config_name = '';
            switch ($controller) {
                case 'AdminCmsContent':
                    $config_name = $is_cms_category ? 'ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT' : 'ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT';
                    break;
                case 'AdminMetaContent':
                    $config_name = 'ETS_SEO_META_SHOW_IN_SEARCH_RESULT';
                    break;
                case 'AdminCategoriesContent':
                    $config_name = 'ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT';
                    break;
                case 'AdminManufacturers':
                    $config_name = 'ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT';
                    break;
                case 'AdminSuppliers':
                    $config_name = 'ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT';
                    break;
            }
            if ($tblName) {
                $tblName .= '.';
            }
            $sql = "`allow_search` = 0";
            if (!(int)Configuration::get($config_name)) {
                $sql = "(`allow_search` = 0 OR `allow_search` = 2)";
            }
            $sqlOverAllSeoScore = " (`seo_score` / (".ETS_TOTAL_SEO_RULE_SCORE." * 9) * 10)";
            $sqlOverAllReadabilityScore = " (`readability_score` / (".ETS_TOTAL_READABILITY_RULE_SCORE." * 9) * 10)";

            $filters = str_replace("`seo_score` = 'ok'", $sqlOverAllSeoScore." > 4 AND ".$sqlOverAllSeoScore." < 7", $filters);
            $filters = str_replace("`seo_score` = 'good'", $sqlOverAllSeoScore." > 7", $filters);
            $filters = str_replace("`seo_score` = 'bad'", $sqlOverAllSeoScore." <= 4", $filters);
            $filters = str_replace($tblName."`seo_score` = 'noindex'", $sql, $filters);

            $filters = str_replace($tblName . "`seo_score` = 'na'", "((`key_phrase` IS NULL OR `key_phrase` = '') AND (`minor_key_phrase` IS NULL OR `minor_key_phrase` = ''))", $filters);
            if($controller !== 'AdminMeta')
            {
                $filters = str_replace("`readability_score` = 'ok'", $sqlOverAllReadabilityScore." > 4 AND ".$sqlOverAllReadabilityScore." <= 7", $filters);
                $filters = str_replace("`readability_score` = 'good'", $sqlOverAllReadabilityScore." > 7", $filters);
                $filters = str_replace("`readability_score` = 'bad'", $sqlOverAllReadabilityScore." <= 4", $filters);
            }
        }
    }

    //CMS
    public function hookActionCmsPageGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('cms', $params);

    }

    public function hookActionCmsPageGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('cms', $params);
    }

    public function hookActionCmsPageGridDataModifier($params)
    {
        $this->__actionGridDataModifier('cms', $params);
    }

    //Cms Category
    public function hookActionCmsPageCategoryGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('cms_category', $params);

    }

    public function hookActionCmsPageCategoryGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('cms_category', $params);
    }

    public function hookActionCmsPageCategoryGridDataModifier($params)
    {
        $this->__actionGridDataModifier('cms_category', $params);
    }

    //Meta
    public function hookActionMetaGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('meta', $params);
    }

    public function hookActionMetaGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('meta', $params);
    }

    public function hookActionMetaGridDataModifier($params)
    {
        $this->__actionGridDataModifier('meta', $params);
    }

    //Category
    public function hookActionCategoryGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('category', $params);
    }


    public function hookActionCategoryGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('category', $params);
    }


    public function hookActionCategoryGridDataModifier($params)
    {
        $this->__actionGridDataModifier('category', $params);
    }


    //Manufacturer
    public function hookActionManufacturerGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('manufacturer', $params);
    }


    public function hookActionManufacturerGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('manufacturer', $params);
    }


    public function hookActionManufacturerGridDataModifier($params)
    {
        $this->__actionGridDataModifier('manufacturer', $params);
    }

    //Supplier
    public function hookActionSuppliersGridQueryBuilderModifier($params)
    {
        $this->__actionGridQueryBuilderModifier('supplier', $params);
    }


    public function hookActionSupplierGridDefinitionModifier($params)
    {
        $this->__actionGridDefinitionModifier('supplier', $params);
    }


    public function hookActionSupplierGridDataModifier($params)
    {
        $this->__actionGridDataModifier('supplier', $params);
    }

    /**
     * __actionGridQueryBuilderModifier
     *
     * @param  string $type
     * @param  array $params
     *
     * @return void
     */
    protected function __actionGridQueryBuilderModifier($type, array $params)
    {
        $table = 'ets_seo_product';
        $sortName = 'esp';
        $tableJoin = 'p';
        $sqlJoin = '';
        $config_name = '';
        switch ($type) {
            case 'product':
                $table = 'ets_seo_product';
                $sortName = 'esp';
                $config_name = 'ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT';
                break;
            case 'cms':
                $table = 'ets_seo_cms';
                $sortName = 'esc';
                $tableJoin = 'c';
                $config_name = 'ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esc.`id_cms` = c.`id_cms` AND esc.`id_shop`=' . (int)$this->context->shop->id . ' AND esc.`id_lang`=cl.`id_lang`)';
                break;
            case 'cms_category':
                $table = 'ets_seo_cms_category';
                $sortName = 'esc';
                $tableJoin = 'cc';
                $config_name = 'ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esc.`id_cms_category` = cc.`id_cms_category` AND esc.`id_shop`=' . (int)$this->context->shop->id . ' AND esc.`id_lang`=ccl.`id_lang`)';
                break;
            case 'meta':
                $table = 'ets_seo_meta';
                $sortName = 'esm';
                $tableJoin = 'm';
                $config_name = 'ETS_SEO_META_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esm.`id_meta` = m.`id_meta` AND esm.`id_shop`=' . (int)$this->context->shop->id . ' AND esm.`id_lang`=l.`id_lang`)';
                break;
            case 'category':
                $table = 'ets_seo_category';
                $sortName = 'esc';
                $tableJoin = 'c';
                $config_name = 'ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esc.`id_category` = c.`id_category` AND esc.`id_shop`=' . (int)$this->context->shop->id . ' AND esc.`id_lang`=cl.`id_lang`)';
                break;

            case 'manufacturer':
                $table = 'ets_seo_manufacturer';
                $sortName = 'esc';
                $tableJoin = 'm';
                $config_name = 'ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esc.`id_manufacturer` = m.`id_manufacturer` AND esc.`id_shop`=' . (int)$this->context->shop->id . ' AND esc.`id_lang`=' . (int)$this->context->language->id . ')';
                break;
            case 'supplier':
                $table = 'ets_seo_supplier';
                $sortName = 'esc';
                $tableJoin = 's';
                $config_name = 'ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT';
                $sqlJoin = '(esc.`id_supplier` = s.`id_supplier` AND esc.`id_shop`=' . (int)$this->context->shop->id . ' AND esc.`id_lang`=' . (int)$this->context->language->id . ')';
                break;
        }

        if (isset($params['search_query_builder']) && $params['search_query_builder']) {
            $searchQueryBuilder = &$params['search_query_builder'];
            $searchQueryBuilder
                ->addSelect($sortName . '.`seo_score`')
                ->addSelect($sortName . '.`readability_score`')
                ->leftJoin($tableJoin,
                    _DB_PREFIX_ . $table,
                    $sortName,
                    $sqlJoin
                );
        }

        if (isset($params['count_query_builder']) && $params['count_query_builder']) {
            $countQueryBuilder = &$params['count_query_builder'];
            $countQueryBuilder
                ->leftJoin($tableJoin,
                    _DB_PREFIX_ . $table,
                    $sortName,
                    $sqlJoin
                );
        }
        $filters = null;
        if ($type == 'cms') {
            if (($cmsPage = Tools::getValue('cms_page', array())) && isset($cmsPage['filters'])) {
                $filters = $cmsPage['filters'];
            }

        } elseif ($type == 'cms_category') {

            if (($cmsPage = Tools::getValue('cms_page_category', array())) && isset($cmsPage['filters'])) {
                $filters = $cmsPage['filters'];
            }

        } elseif ($type == 'manufacturer') {
            if (($cmsPage = Tools::getValue('manufacturer', array())) && isset($cmsPage['filters'])) {
                $filters = $cmsPage['filters'];
            }
        }
        elseif ($type == 'supplier') {

            if (($cmsPage = Tools::getValue('supplier', array())) && isset($cmsPage['filters'])) {
                $filters = $cmsPage['filters'];
            }
        } else {
            $filters = Tools::getValue('filters', array());
        }
        $sqlOverAllSeoScore = " (".(string)$sortName.".`seo_score` / (".ETS_TOTAL_SEO_RULE_SCORE." * 9) * 10)";
        $sqlOverAllReadabilityScore = " (".(string)$sortName.".`readability_score` / (".ETS_TOTAL_READABILITY_RULE_SCORE." * 9) * 10)";

        if ($filters) {
            if (isset($filters['seo_score']) && $filters['seo_score']) {
                switch ($filters['seo_score']) {
                    case 'bad':
                        $searchQueryBuilder->andWhere($sqlOverAllSeoScore . ' <= 4');
                        $countQueryBuilder->andWhere($sqlOverAllSeoScore . ' <= 4');
                        break;
                    case 'ok':
                        $searchQueryBuilder->andWhere($sqlOverAllSeoScore . ' > 4 AND ' . $sqlOverAllSeoScore . ' <= 7');
                        $countQueryBuilder->andWhere($sqlOverAllSeoScore . ' > 4 AND ' . $sqlOverAllSeoScore . ' <= 7');
                        break;
                    case 'good':
                        $searchQueryBuilder->andWhere($sqlOverAllSeoScore . ' > 7');
                        $countQueryBuilder->andWhere($sqlOverAllSeoScore . ' > 7');
                        break;
                    case 'na':
                        $searchQueryBuilder->andWhere('((' . (string)$sortName . '.`key_phrase` IS NULL OR ' . (string)$sortName . '.`key_phrase` = \'\') AND ('. (string)$sortName . '.`minor_key_phrase` IS NULL OR ' . (string)$sortName . '.`minor_key_phrase` = \'\' ))');
                        $countQueryBuilder->andWhere('((' . (string)$sortName . '.`key_phrase` IS NULL OR ' . (string)$sortName . '.`key_phrase` = \'\') AND ('. (string)$sortName . '.`minor_key_phrase` IS NULL OR ' . (string)$sortName . '.`minor_key_phrase` = \'\' ))');
                        break;
                    case 'noindex':
                        $sql = (string)$sortName . '.`allow_search` = 0';
                        if ((int)Configuration::get($config_name)) {
                            $sql = '(' . (string)$sortName . '.`allow_search` = 0 OR ' . (string)$sortName . '.`allow_search` = 2)';
                        }
                        $searchQueryBuilder->andWhere((string)$sql);
                        $countQueryBuilder->andWhere((string)$sql);
                        break;

                }
            }
            if (isset($filters['readability_score']) && $filters['readability_score']) {
                if($type == 'meta')
                {
                    $searchQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' < 0');
                }
                else{
                    switch ($filters['readability_score']) {
                        case 'bad':
                            $searchQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' <= 4');
                            $countQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' <= 4');
                            break;
                        case 'ok':
                            $searchQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' > 4');
                            $searchQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' <= 7');
                            break;
                        case 'good':
                            $searchQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' > 7');
                            $countQueryBuilder->andWhere($sqlOverAllReadabilityScore . ' > 7');
                            break;
                    }
                }
            }
        }
    }


    /**
     * __actionGridDefinitionModifier
     *
     * @param  string $type
     * @param  array $params
     *
     * @return void
     */
    protected function __actionGridDefinitionModifier($type, array $params)
    {
        $colAfter = 'actions';
        if ($type) {

        }
        if (isset($params['definition']) && $params['definition']) {
            $seo_enabled = 1;
            $readability_enabled = 1;

            $defination = &$params['definition'];

            $columnCollection = $defination->getColumns();
            if ($seo_enabled) {
                $columnCollection->addBefore($colAfter, (new DataColumn('seo_score'))
                    ->setName($this->l('SEO score'))
                    ->setOptions([
                        'field' => 'seo_score',
                        'sortable' => false,
                    ])
                );
            }
            if ($readability_enabled) {
                if ($seo_enabled) {
                    $columnCollection->addAfter('seo_score', (new DataColumn('readability_score'))
                        ->setName($this->l('Readability score'))
                        ->setOptions([
                            'field' => 'readability_score',
                            'sortable' => false,
                        ])
                    );
                } else {
                    $columnCollection->addBefore($colAfter, (new DataColumn('readability_score'))
                        ->setName($this->l('Readability score'))
                        ->setOptions([
                            'field' => 'readability_score',
                            'sortable' => false,
                        ])
                    );
                }

            }


            $defination->setColumns($columnCollection);
            $filterCollection = $defination->getFilters();
            if ($seo_enabled) {
                $filterCollection->add((new Filter('seo_score', ChoiceType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'choices' => [
                            $this->l('SEO: Not good') => 'bad',
                            $this->l('SEO: Acceptable') => 'ok',
                            $this->l('SEO: Excellent') => 'good',
                            $this->l('SEO: No Focus or Related key phrases') => 'na',
                            $this->l('SEO: No Index') => 'noindex',
                        ],
                        'placeholder' => $this->l('All SEO Scores'),
                    ])
                    ->setAssociatedColumn('seo_score')
                );
            }

            if ($readability_enabled) {
                $filterCollection->add((new Filter('readability_score', ChoiceType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'choices' => [
                            $this->l('Readability: Not good') => 'bad',
                            $this->l('Readability: Acceptable') => 'ok',
                            $this->l('Readability: Excellent') => 'good',
                        ],
                        'placeholder' => $this->l('All Readability Scores'),
                    ])
                    ->setAssociatedColumn('readability_score')
                );
            }

        }
    }

    /**
     * __actionGridDataModifier
     *
     * @param  string $type
     * @param  array $params
     *
     * @return void
     */
    protected function __actionGridDataModifier($type, array $params)
    {
        if (isset($params['data']) && $params['data']) {
            $data = &$params['data'];
            $results = $data->getRecords();

            $results = $this->modifyResultList($type, $results);
            $recordCollection = new RecordCollection($results);
            $gridData = new GridData($recordCollection, $data->getRecordsTotal(), $data->getQuery());
            $data = $gridData;
        }
    }

    public function calcOverAllScore($type, $total_score = 0)
    {
        $numberResult = ETS_TOTAL_READABILITY_RULE_SCORE;
        if($type == 'seo_score')
        {
            $numberResult = ETS_TOTAL_SEO_RULE_SCORE;
        }
        return round((int)$total_score / ($numberResult * 9) * 10);
    }

    /**
     * modifyResultList
     *
     * @param  string $type
     * @param  array $list
     *
     * @return void
     */
    public function modifyResultList($type = 'cms', $lists)
    {
        $id_column = 'id_cms';
        $table = 'ets_seo_cms';
        $index_config_name = 'ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT';
        $enable_seo = 1;
        switch ($type) {
            case 'meta':
                $id_column = 'id_meta';
                $table = 'ets_seo_meta';
                $index_config_name = 'ETS_SEO_META_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
            case 'cms_category':
                $id_column = 'id_cms_category';
                $table = 'ets_seo_cms_category';
                $index_config_name = 'ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
            case 'product':
                $id_column = 'id_product';
                $table = 'ets_seo_product';
                $index_config_name = 'ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
            case 'category':
                $id_column = 'id_category';
                $table = 'ets_seo_category';
                $index_config_name = 'ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
            case 'manufacturer':
                $id_column = 'id_manufacturer';
                $table = 'ets_seo_manufacturer';
                $index_config_name = 'ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
            case 'supplier':
                $id_column = 'id_supplier';
                $table = 'ets_seo_supplier';
                $index_config_name = 'ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT';
                $enable_seo = 1;
                break;
        }

        $ids = array();
        $results = array();
        foreach ($lists as $item) {
            $results[] = $item;
            $ids[] = $item[$id_column];
        }

        if ($ids) {
            $multiLangActive = Language::isMultiLanguageActivated($this->context->shop->id);
            $idLangs = array($this->context->language->id);
            if ($multiLangActive) {
                $idLangs = array();
                $listIdsLang = Language::getIDs(true, $this->context->shop->id);

                foreach ($listIdsLang as $item) {
                    $idLangs[] = $item;
                }
            }

            $seo_data = Db::getInstance()->executeS(
                "SELECT esm.`" . (string)$id_column . "`, esm.`id_lang`, esm.`seo_score`, esm.`readability_score`, lang.`name` as lang_name, lang.`iso_code`, esm.`key_phrase`, esm.`allow_search`, esm.`minor_key_phrase`
                    FROM `" . _DB_PREFIX_ . (string)$table . "` esm 
                    JOIN `" . _DB_PREFIX_ . "lang` lang ON esm.`id_lang` = lang.`id_lang`
                    WHERE esm.`" . (string)$id_column . "` IN (" . implode(',', $ids) . ") 
                    AND esm.`id_lang` IN (" . implode(',', $idLangs) . ")
                    AND esm.`id_shop` =" . (int)$this->context->shop->id
            );
            $results = array();
            foreach ($lists as $item) {
                $item['seo_score'] = '';
                $item['readability_score'] = '';
                foreach ($seo_data as $seo) {
                    if ($seo[$id_column] == $item[$id_column]) {
                        $overAllSeoScore = $this->calcOverAllScore('seo_score', (int)$seo['seo_score']);
                        $overAllReadabilityScore = $this->calcOverAllScore('readability_score', (int)$seo['readability_score']);
                        $seo_status = $this->l('No analysis available');
                        $seo_class = 'grey';
                        if (!$enable_seo) {
                            $seo_status = $this->l('No index');
                            $seo_class = 'yellow';
                        }
                        elseif (!(int)$seo['allow_search'] || ((int)$seo['allow_search'] == 2 && (int)Configuration::get($index_config_name) == 0)) {
                            $seo_status = $this->l('No index');
                            $seo_class = 'grey-noindex';
                        }
                        else if (!trim($seo['key_phrase']) && !trim($seo['minor_key_phrase'])) {
                            $seo_status = $this->l('No Focus or Related key phrases');
                            $seo_class = 'grey-nokeyphrase';
                        } elseif ($overAllSeoScore <= 4) {
                            $seo_status = $this->l('Not good');
                            $seo_class = 'red';
                        } elseif ($overAllSeoScore > 4 && $overAllSeoScore <= 7) {
                            $seo_status = $this->l('Acceptable');
                            $seo_class = 'orange';
                        } elseif ($overAllSeoScore > 7) {
                            $seo_status = $this->l('Excellent');
                            $seo_class = 'green';
                        }
                        $this->smarty->assign(array(
                            'seo_class' => $seo_class,
                            'seo_status' => $seo_status,
                            'seo_isocode' => $seo['iso_code'],
                            'seo_multi_lang' => $multiLangActive
                        ));

                        $item['seo_score'] .= $this->display(__FILE__, 'parts/seo_score_status.tpl');

                        $readability_status = $this->l('No analysis available');
                        $readability_class = 'yellow';
                        if ($type == 'meta') {
                            $readability_status = $this->l('No analysis available');
                            $readability_class = 'grey-darken';
                        } elseif ($overAllReadabilityScore <= 4) {
                            $readability_status = $this->l('Not good');
                            $readability_class = 'red';
                        } elseif ($overAllReadabilityScore > 4 && $overAllReadabilityScore <= 7) {
                            $readability_status = $this->l('Acceptable');
                            $readability_class = 'orange';
                        } elseif ($overAllReadabilityScore > 7) {
                            $readability_status = $this->l('Excellent');
                            $readability_class = 'green';
                        }

                        $this->smarty->assign(array(
                            'seo_class' => $readability_class,
                            'seo_status' => $readability_status,
                            'seo_isocode' => $seo['iso_code'],
                            'seo_multi_lang' => $multiLangActive
                        ));
                        $item['readability_score'] .= $this->display(__FILE__, 'parts/seo_score_status.tpl');

                        //array_splice($seo_data, $k, 1);
                    }
                }
                if ($item['seo_score'] == '') {
                    $item['seo_score'] = '--';
                }
                if ($item['readability_score'] == '') {
                    $item['readability_score'] = '--';
                }

                if ($type == 'cms') {
                    if (isset($item['head_seo_title'])) {
                        $cms = new CMS($item['id_cms'], $this->context->language->id);
                        $cmsCate = new CMSCategory($cms->id_cms_category, $this->context->language->id);
                        $cmsCateName = $cmsCate ? $cmsCate->name : '';
                        $item['head_seo_title'] = $this->formatSeoMeta($item['head_seo_title'], array('post_title' => $item['meta_title'], 'is_title' => true, 'category' => $cmsCateName), 'cms');
                    }

                }
                $results[] = $item;
            }


        }
        return $results;
    }

    /**
     * deleteImage
     *
     * @param  string $image
     *
     * @return void
     */
    public function deleteImage($image)
    {
        if (Tools::strpos($image, '/img/social/') !== false) {
            if (file_exists(_PS_ROOT_DIR_ . '/img/social/' . basename($image))) {
                return @unlink(_PS_ROOT_DIR_ . '/img/social/' . basename($image));
            }
        } else {
            if (file_exists(_PS_MODULE_DIR_ . $this->name . '/cache/' . basename($image))) {
                return @unlink(_PS_MODULE_DIR_ . $this->name . '/cache/' . basename($image));
            }
        }
        return false;
    }


    public function getTotalProduct($active = false, $id_lang = null)
    {
        $sql = "SELECT COUNT(*) as total_product FROM `" . _DB_PREFIX_ . "product` p
                    INNER JOIN `" . _DB_PREFIX_ . "product_shop` product_shop ON (product_shop.id_product = p.id_product AND product_shop.id_shop = " . (int)$this->context->shop->id . ")
                    LEFT JOIN `" . _DB_PREFIX_ . "product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.id_shop = " . (int)$this->context->shop->id . ")
                    WHERE 1 " . ($active ? " AND p.`active` = 1 " : '') . ($id_lang ? " AND pl.`id_lang` = " . (int)$id_lang : '');
        return (int)Db::getInstance()->getValue($sql);
    }


    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
        if((int)Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL')){
            $isoCode = $this->context->language->id == Configuration::get('PS_LANG_DEFAULT') ? '' : $this->context->language->iso_code;
            $this->context->smarty->assign(array(
                'ets_seo_base_url' => $this->context->shop->getBaseURL(true).($isoCode ? $isoCode.'/' : '')
            ));
        }
        $this->getSeoMetaData();
        if (Tools::getValue('controller', null) != 'product') {
            return $this->display(__FILE__, 'head.tpl');
        }
    }

    /**
     * setDefaultConfig
     *
     * @return bool
     */
    public function setDefaultConfig()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $groups = $seoDef->fields_config();
        $languages = Language::getLanguages(false);
        foreach ($groups as $configs) {
            foreach ($configs as $key => $config) {
                if (isset($config['default']) && $config['default'] !== '') {
                    Configuration::updateGlobalValue($key, $config['default']);
                } else {
                    if (isset($config['type']) && ($config['type'] == 'textLang' || $config['type'] == 'textareaLang' || $config['type'] == 'selectLang')) {
                        $value = array();
                        foreach ($languages as $lang) {
                            $value[$lang['id_lang']] = '';
                        }
                        Configuration::updateGlobalValue($key, $value);
                    } else {
                        Configuration::updateGlobalValue($key, '');
                    }
                }
            }
        }
        //Create image folder
        if (!is_dir(_PS_ROOT_DIR_ . '/img/social')) {
            @mkdir(_PS_ROOT_DIR_ . '/img/social', 0755, true);
            @copy(dirname(__FILE__) . '/index.php', _PS_ROOT_DIR_ . '/img/social/index.php');
        }

        //Create cache folder
        if (!is_dir(_PS_ROOT_DIR_ . '/cache/' . $this->name)) {
            @mkdir(_PS_ROOT_DIR_ . '/cache/' . $this->name, 0755, true);
        }
        if (!@file_exists(_PS_ROOT_DIR_ . '/cache/' . $this->name . '/index.php')) {
            @copy(dirname(__FILE__) . '/index.php', _PS_ROOT_DIR_ . '/cache/' . $this->name . '/index.php');
        }
        return true;
    }

    public function removeAllConfigs(){
        $seoDef = Ets_Seo_Define::getInstance();
        $groups = $seoDef->fields_config();
        foreach ($groups as $configs) {
            foreach ($configs as $key => $config) {
                if($config){
                    Configuration::deleteByName($key);
                }

            }
        }
        Configuration::deleteByName('ETS_SEO_ENABLE_REMOVE_ID_IN_URL');
        Configuration::deleteByName('ETS_SEO_UPDATE_DUPLICATE_REWRITE');
        Configuration::deleteByName('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS');
        Configuration::deleteByName('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL');
        Configuration::deleteByName('ETS_SEO_SET_REMOVE_ID');
        return true;
    }

    public function hookActionDispatcherBefore($params)
    {
        if (defined('_PS_ADMIN_DIR_')) {
            return;
        }

        //Redirect
        if (!(int)Configuration::get('ETS_SEO_ENABLE_URL_REDIRECT')) {
            return;
        }
        $redirect = EtsSeoRedirect::getTypeUrlRedirect($this->context->shop->getBaseURL(true, false) . $_SERVER['REQUEST_URI'], $this->context, true);

        if ($redirect && $redirect['target']) {
            if (strpos($redirect['target'], 'http://') === false && strpos($redirect['target'], 'https://') === false) {
                $redirect['target'] = 'http://' . trim($redirect['target']);
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            $code = $redirect['type'];
            $text = '';
            switch ($redirect['type']) {
                case 301:
                    $text = 'Moved Permanently';
                    break;
                case 302:
                    $text = 'Moved Temporarily';
                    break;
                case 303:
                    $text = 'See Other';
                    break;
            }
            Tools::redirect($redirect['target'], __PS_BASE_URI__, $this->context->link, $protocol . ' ' . $code . ' ' . $text);
            exit();
        }
    }

    public function getSfContainer()
    {
        if(!class_exists('\PrestaShop\PrestaShop\Adapter\SymfonyContainer'))
        {
            $kernel = null;
            try{
                $kernel = new AppKernel('prod', false);
                $kernel->boot();
                return $kernel->getContainer();
            }
            catch (Exception $ex){
                return null;
            }
        }
        $sfContainer = call_user_func(array('\PrestaShop\PrestaShop\Adapter\SymfonyContainer', 'getInstance'));
        return $sfContainer;
    }

    public function addTwigVar($key, $value)
    {
        if($sfContainer = $this->getSfContainer())
        {
            $sfContainer->get('twig')->addGlobal($key, $value);
        }

    }
    public function getRequestContainer()
    {
        if($sfContainer = $this->getSfContainer())
        {
            return $sfContainer->get('request_stack')->getCurrentRequest();
        }
        return null;
    }

    public function generateGraphWebData()
    {
        $socialConfigs = array(
            'ETS_SEO_URL_FACEBOOK',
            'ETS_SEO_URL_TWITTER',
            'ETS_SEO_URL_INSTA',
            'ETS_SEO_URL_LINKEDIN',
            'ETS_SEO_URL_MYSPACE',
            'ETS_SEO_URL_PINTEREST',
            'ETS_SEO_URL_YOUTUBE',
            'ETS_SEO_URL_WIKI',
        );

        $data = array(
            '@context' => "https://schema.org",
            '@graph' => array(
                array(
                    '@type' => "WebSite",
                    '@id' => $this->context->shop->getBaseURL(true, true) . '#website',
                    'url' => $this->context->shop->getBaseURL(true, true),
                    'name' => Configuration::get('PS_SHOP_NAME'),
                    'potentialAction' => array(
                        '@type' => 'SearchAction',
                        "target" => $this->context->shop->getBaseURL(true, true) . 'search?s={search_term_string}',
                        "query-input" => "required name=search_term_string"
                    ),
                ),
            ),
        );

        $socialLinks = array();
        foreach ($socialConfigs as $name) {
            if ($link = Configuration::get($name)) {
                $socialLinks[] = $link;
            }
        }

        if ($typeWebsite = Configuration::get('ETS_SEO_SITE_OF_PERSON_OR_COMP')) {

            if ($typeWebsite == 'PERSON') {
                $name = Configuration::get('ETS_SEO_SITE_PERSON_NAME');
                $image = Configuration::get('ETS_SEO_SITE_PERSON_AVATAR');
                if ($name) {
                    $data['@graph'][] = array(
                        '@type' => array('Person', 'Organization'),
                        '@id' => $this->context->shop->getBaseURL(true, true) . '#/schema/person/' . md5($name),
                        'name' => addslashes($name),
                        'url' => $this->context->shop->getBaseURL(true, true),
                        'sameAs' => array(),

                        'image' => array(
                            '@type' => "ImageObject",
                            "@id" => $this->context->shop->getBaseURL(true, true) . '#personlogo',
                            "url" => $this->context->shop->getBaseURL(true, true) . 'img/social/' . ($image ? $image : 'default_avatar.png'),
                            "caption" => addslashes($name)
                        ),
                        "logo" => array(
                            '@id' => $this->context->shop->getBaseURL(true, true) . '#personlogo',
                        )
                    );
                }

            } else {
                $name = Configuration::get('ETS_SEO_SITE_ORIG_NAME');
                $image = Configuration::get('ETS_SEO_SITE_ORIG_LOGO');
                if ($name && $image) {
                    $data['@graph'][] = array(
                        '@type' => 'Organization',
                        '@id' => $this->context->shop->getBaseURL(true, true) . '#organization',
                        'name' => addslashes($name),
                        'url' => $this->context->shop->getBaseURL(true, true),
                        'sameAs' => $socialLinks,

                        'logo' => array(
                            '@type' => "ImageObject",
                            "@id" => $this->context->shop->getBaseURL(true, true) . '#logo',
                            "url" => $this->context->shop->getBaseURL(true, true) . 'img/social/' . $image,
                            "caption" => addslashes($name)
                        ),
                        "image" => array(
                            '@id' => $this->context->shop->getBaseURL(true, true) . '#logo',
                        )
                    );
                }

            }

        }

        if ((int)Configuration::get('ETS_SEO_BREADCRUMB_ENABLED')) {
            $breadcrumb = array(
                '@type' => 'BreadcrumbList',
                '@id' => $this->context->shop->getBaseURL(true, true) . '#breadcrumb',
                'itemListElement' => array(
                    array(
                        '@type' => 'ListItem',
                        'position' => 1,
                        'item' => array(
                            '@type' => 'WebPage',
                            'name' => Configuration::get('ETS_SEO_BREADCRUMB_ANCHOR_TEXT_HOME', $this->context->language->id),
                            '@id' => $this->getPageLink('index', $this->context->language->id),
                            'url' => $this->getPageLink('index', $this->context->language->id),
                        )
                    )
                )
            );

            $params = array(
                'link' => '',
                'title' => '',
            );
            if ($id = (int)Tools::getValue('id_product')) {
                $product = new Product($id, null, $this->context->language->id);
                if (!$product || !$product->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $product->getLink($this->context);
                $params['title'] = $product->name;
                if (Configuration::get('ETS_SEO_BREADCRUMB_PRODUCT') == 'category') {
                    $cate = new Category($product->id_category_default, $this->context->language->id);
                    $params['category_title'] = $cate->name;
                    $params['category_link'] = $cate->getLink($this->context->link, $this->context->language->id);
                }
            } elseif ($id = (int)Tools::getValue('id_category')) {
                $category = new Category($id, $this->context->language->id);
                if (!$category || !$category->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $category->getLink($this->context->link, $this->context->language->id);
                $params['title'] = $category->name;
            } elseif ($id = (int)Tools::getValue('id_cms')) {
                $cms = new CMS($id, $this->context->language->id);
                if (!$cms || !$cms->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $this->context->link->getCMSLink($cms, null, null, $this->context->language->id);
                $params['title'] = $cms->meta_title;

                if (Configuration::get('ETS_SEO_BREADCRUMB_CMS') == 'category') {
                    $cmsCategory = new CMSCategory($cms->id_cms_category, $this->context->language->id);
                    if (!$cmsCategory || !$cmsCategory->id){
                        Tools::redirect('index.php');
                    }
                    $params['category_link'] = $this->context->link->getCMSCategoryLink($cmsCategory, null, $this->context->language->id);
                    $params['category_title'] = $cmsCategory->name;
                }
            } elseif ($id = (int)Tools::getValue('id_cms_category')) {
                $cmsCategory = new CMSCategory($id, $this->context->language->id);
                if (!$cmsCategory || !$cmsCategory->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $this->context->link->getCMSCategoryLink($cmsCategory, null, $this->context->language->id);
                $params['title'] = $cmsCategory->name;
            } elseif ($id = (int)Tools::getValue('id_manufacturer')) {
                $manufacturer = new Manufacturer($id, $this->context->language->id);
                if (!$manufacturer || !$manufacturer->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $this->context->link->getManufacturerLink($manufacturer, null, $this->context->language->id);
                $params['title'] = $manufacturer->name;
            } elseif ($id = (int)Tools::getValue('id_supplier')) {
                $supplier = new Supplier($id, $this->context->language->id);
                if (!$supplier || !$supplier->id){
                    Tools::redirect('index.php');
                }
                $params['link'] = $this->context->link->getSupplierLink($supplier, null, $this->context->language->id);
                $params['title'] = $supplier->name;
            } elseif ($this->context->controller) {
                if ($this->context->controller->php_self !== 'index') {
                    $meta = Meta::getMetaByPage($this->context->controller->php_self, $this->context->language->id);
                    if($meta)
                    {
                        $params['link'] = $this->getPageLink($meta['page'], $this->context->language->id);
                        $params['title'] = $meta['title'];
                        if ($this->context->controller->php_self == 'search') {
                            $params['title'] = Configuration::get('ETS_SEO_BREADCRUMB_PREFIX_SEARCH', $this->context->language->id);
                            if (($s = Tools::getValue('s')) && Validate::isCleanHtml($s)) {
                                $params['title'] .= ' "' . $s . '"';
                                $params['link'] .= '?s=' . $s;
                            }
                        } elseif ($this->context->controller->php_self == 'pagenotfound') {
                            $params['title'] = Configuration::get('ETS_SEO_BREADCRUMB_404_PAGE', $this->context->language->id);
                        }
                    }

                }

            }

            $postion = 1;
            if (isset($params['category_title']) && isset($params['category_link'])) {
                $postion += 1;
                $breadcrumb['itemListElement'][] = array(
                    '@type' => 'ListItem',
                    'position' => $postion,
                    'item' => array(
                        '@type' => 'WebPage',
                        'name' => $params['category_title'],
                        '@id' => $params['category_link'],
                        'url' => $params['category_link'],
                    )
                );
            }
            if ($params['title'] && $params['link']) {
                $postion += 1;
                $breadcrumb['itemListElement'][] = array(
                    '@type' => 'ListItem',
                    'position' => $postion,
                    'item' => array(
                        '@type' => 'WebPage',
                        'name' => $params['title'],
                        '@id' => $params['link'],
                        'url' => $params['link'],
                    )
                );
            }

            $data['@graph'][] = $breadcrumb;
        }
        if ($controller = Tools::getValue('controller')) {
            if (in_array($controller, array('product', 'cms', 'meta', 'category', 'cms_category', 'manufacturer', 'supplier'))) {
                $id = null;
                $type = 'Product';
                $name = '';
                $brand = '';
                $desc = '';
                $image = '';
                $sku = '';
                $post_title = null;
                $category_name = null;
                $price = null;
                $shortDesc = null;
                $desc2 = null;
                switch ($controller) {
                    case 'product':
                        $id = (int)Tools::getValue('id_product');
                        $type = 'Product';
                        $p = new Product($id, null, $this->context->language->id);
                        $name = $p->name;
                        if ($p->id_manufacturer) {
                            $manufacturer = new Manufacturer($p->id_manufacturer, $this->context->language->id);
                            if ($manufacturer && $manufacturer->id) {
                                $brand = $manufacturer->name;
                            }
                        }
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : strip_tags($p->description_short);
                        $cover = Product::getCover($p->id);
                        $image = $this->context->link->getImageLink($p->link_rewrite, (isset($cover['id_image']) ? $cover['id_image'] : ''), ImageType::getFormattedName('home'));
                        $sku = $p->reference;
                        $post_title = $p->name;
                        $shortDesc = $p->description_short;
                        $id_customer = ($this->context->customer->id) ? (int)($this->context->customer->id) : 0;
                        $id_group = null;
                        if ($id_customer) {
                            $id_group = Customer::getDefaultGroupId((int)$id_customer);
                        }
                        if (!$id_group) {
                            $id_group = (int)Group::getCurrent()->id;
                        }
                        $group = new Group($id_group);
                        if ($group->price_display_method)
                            $tax = false;
                        else
                            $tax = true;
                        $price = Tools::displayPrice($p->getPrice($tax, null));
                        if ($p->id_category_default) {
                            $cateProduct = new Category($p->id_category_default, $this->context->language->id);
                            if ($cateProduct && $cateProduct->name) {
                                $category_name = $cateProduct->name;
                            }
                        }
                        break;
                    case 'cms':
                        $id = (int)Tools::getValue('id_cms');
                        $type = 'Product';
                        $p = new CMS($id, $this->context->language->id);
                        $name = $p->meta_title;
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : '';
                        $post_title = $p->meta_title;
                        $shortDesc = strip_tags($p->meta_description);
                        if ($p->id_cms_category) {
                            $cateCms = new CMSCategory($p->id_cms_category, $this->context->language->id);
                            if ($cateCms && $cateCms->name) {
                                $category_name = $cateCms->name;
                            }
                        }
                        break;
                    case 'meta':
                        $id = (int)Tools::getValue('id_meta');
                        $type = 'Product';
                        $p = new Meta($id, $this->context->language->id);
                        $name = $p->title;
                        $desc = $p->description ? strip_tags($p->description) : '';
                        $post_title = $p->title;
                        $shortDesc = $p->description ? strip_tags($p->description) : '';
                        break;
                    case 'category':
                        $id = (int)Tools::getValue('id_category');
                        $type = 'Product';
                        $p = new Category($id, $this->context->language->id);
                        $name = $p->name;
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : strip_tags($p->description);
                        $image = $this->context->link->getCatImageLink($p->link_rewrite, $p->id/*, 'category_default'*/);
                        $post_title = $p->name;
                        $shortDesc = $p->description ? strip_tags($p->description) : '';
                        break;
                    case 'cms_category':
                        $id = (int)Tools::getValue('id_cms_category');
                        $type = 'Product';
                        $p = new CMSCategory($id, $this->context->language->id);
                        $name = $p->name;
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : strip_tags($p->description);
                        $post_title = $p->name;
                        $shortDesc = '';
                        break;
                    case 'manufacturer':
                        $id = (int)Tools::getValue('id_manufacturer');
                        $type = 'Product';
                        $p = new Manufacturer($id, $this->context->language->id);
                        $name = $p->name;
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : strip_tags($p->description);
                        $post_title = $p->name;
                        $shortDesc = strip_tags($p->short_description);
                        $desc2 = strip_tags($p->description);
                        if (file_exists(_PS_ROOT_DIR_ . '/img/m/' . $id . '.jpg')) {
                            $image = $this->context->shop->getBaseURL(true, true) . 'img/m/' . $id . '.jpg';
                        }
                        break;
                    case 'supplier':
                        $id = (int)Tools::getValue('id_supplier');
                        $type = 'Product';
                        $p = new Supplier($id, $this->context->language->id);
                        $name = $p->name;
                        $desc = $p->meta_description ? strip_tags($p->meta_description) : '';
                        $post_title = $p->name;
                        $shortDesc = strip_tags($p->description);
                        if (file_exists(_PS_ROOT_DIR_ . '/img/s/' . $id . '.jpg')) {
                            $image = $this->context->shop->getBaseURL(true, true) . 'img/s/' . $id . '.jpg';
                        }
                        break;
                }

                $name = $this->formatSeoMeta($name, array('post_title' => $post_title, 'is_title' => true, 'category' => $category_name, 'price' => $price, 'description' => $shortDesc), $controller);
                $desc = $this->formatSeoMeta($desc, array('post_title' => $post_title, 'category' => $category_name, 'price' => $price, 'description' => $shortDesc, 'description2' => $desc2), $controller);

                $ratingSeo = EtsSeoRating::getRatingConfig($controller, $id);

                if ($ratingSeo) {
                    $ratingGraph = array(
                        '@type' => $type,
                        'name' => $name,
                        'aggregateRating' => array(
                            '@type' => 'AggregateRating',
                            'ratingValue' => $ratingSeo['avg_rating'],
                            'ratingCount' => $ratingSeo['rating_count'],
                            'bestRating' => $ratingSeo['best_rating'] ? $ratingSeo['best_rating'] : 5,
                            'worstRating' => $ratingSeo['worst_rating'] ? $ratingSeo['worst_rating'] : 1,
                        )
                    );
                    if ($type = 'product') {
                        if ($brand) {
                            $ratingGraph['brand'] = $brand;
                        }
                        if ($desc) {
                            $ratingGraph['description'] = $desc;
                        }
                        if ($image) {
                            $ratingGraph['image'] = $image;
                        }
                        if ($sku) {
                            $ratingGraph['sku'] = $sku;
                        }
                    }
                    $data['@graph'][] = $ratingGraph;
                }
            }
        }

        return $data;
    }

    public function hookDisplayOverrideTemplate($params)
    {
        if (isset($params['template_file']) && $params['template_file'] == 'catalog/product') {
            if(!Module::isEnabled('ets_product_slideshow'))
            {
                $this->getSeoMetaData(true);
                return $this->getTemplatePath('catalog/product.tpl');
            }
            
        }

    }

    public function getSeoMetaData($idPoductPage = false)
    {
        $page = $idPoductPage ? array() : $this->context->controller->getTemplateVarPage();
        $seo_social = array();
        if ($controller = Tools::getValue('controller', null)) {
            $id_lang = $this->context->language->id;
            $dataSeo = array();
            $config_allow_search = false;
            $config_meta_title = '';
            $config_meta_desc = '';
            $meta_title = null;
            $meta_desc = null;
            $post_title = null;
            $price = null;
            $category_name = null;
            $desc = null;
            $descSeo = null;
            $page_type = null;
            $desc2 = null;
            $discount_price = null;
            $brand = null;
            $forceUseMetaTemplate = 0;
            if (($id_product = (int)Tools::getValue('id_product')) && $controller == 'product') {
                $dataSeo = EtsSeoProduct::getSeoProduct($id_product, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_PROD_SHOW_IN_SEARCH_RESULT');
                if ($generalTitle = Configuration::get('ETS_SEO_PROD_META_TILE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_PROD_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }
                $meta_tags = Meta::getMetaTags($this->context->language->id, 'product');
                $page['meta']['title'] = $meta_tags['meta_title'];
                $page['meta']['description'] = $meta_tags['meta_description'];
                $page['meta']['keywords'] = $meta_tags['meta_keywords'];

                $p = new Product($id_product, null, $id_lang);
                $meta_title = $p->meta_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->name;

                $id_customer = ($this->context->customer->id) ? (int)($this->context->customer->id) : 0;
                $id_group = null;
                if ($id_customer) {
                    $id_group = Customer::getDefaultGroupId((int)$id_customer);
                }
                if (!$id_group) {
                    $id_group = (int)Group::getCurrent()->id;
                }
                $group = new Group($id_group);
                if ($group->price_display_method)
                    $tax = false;
                else
                    $tax = true;
                $price = Tools::displayPrice($p->getPriceWithoutReduct(!$tax));

                $pc = new Category($p->id_category_default, $id_lang);
                $category_name = $pc->name;
                $desc = $p->description_short;
                $descSeo = trim($p->description_short) ? $p->description_short : Tools::substr($p->description, 0, 120);
                $page_type = 'product';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_PROD_FORCE_USE_META_TEMPLATE');
                if($p->id_manufacturer && ($manuf = new Manufacturer($p->id_manufacturer, $this->context->language->id)) && $manuf->id){
                    $brand = $manuf->name;
                }
                $discount_price = Tools::displayPrice($p->getPrice($tax));

            } elseif (($id_category = (int)Tools::getValue('id_category'))  && $controller == 'category') {
                $dataSeo = EtsSeoCategory::getSeoCategory($id_category, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_CATEGORY_SHOW_IN_SEARCH_RESULT');

                if ($generalTitle = Configuration::get('ETS_SEO_CATEGORY_META_TILE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_CATEGORY_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }
                $p = new Category($id_category, $id_lang);
                $meta_title = $p->meta_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->name;
                $desc = $p->description;
                $descSeo = $p->description;
                $page_type = 'category';
                // Custom 2026-06-06: garde-fou categorie supprimee (fatal "Cannot use object of type Category as array")
                $page['canonical'] = Validate::isLoadedObject($p) ? $p->getLink() : '';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CATEGORY_FORCE_USE_META_TEMPLATE');
            }
            elseif (($id_cms = (int)Tools::getValue('id_cms'))  && $controller == 'cms') {
                $dataSeo = EtsSeoCms::getSeoCms($id_cms, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_CMS_SHOW_IN_SEARCH_RESULT');
                if ($generalTitle = Configuration::get('ETS_SEO_CMS_META_TILE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_CMS_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }

                $p = new CMS($id_cms, $id_lang);
                $meta_title = $p->head_seo_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->meta_title;

                $cc = new CMSCategory($p->id_cms_category, $id_lang);
                $category_name = $cc->name;
                $desc = $p->meta_description;
                $descSeo = $p->meta_description;
                $page_type = 'cms';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CMS_FORCE_USE_META_TEMPLATE');
                if((!isset($page['canonical']) || !$page['canonical']) && Validate::isLoadedObject($p)){ // Custom 2026-06-06: garde-fou objet supprime
                    $page['canonical'] = $this->context->link->getCMSLink($p, null, null, $this->context->language->id);
                }
            }
            elseif (($id_cms = (int)Tools::getValue('id_cms_category'))  && in_array($controller, array('cms', 'cms_category'))) {
                $dataSeo = EtsSeoCmsCategory::getSeoCmsCategory($id_cms, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_CMS_CATE_SHOW_IN_SEARCH_RESULT');

                if ($generalTitle = Configuration::get('ETS_SEO_CMS_CATE_META_TILE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_CMS_CATE_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }

                $p = new CMSCategory($id_cms, $id_lang);
                $meta_title = $p->meta_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->name;

                $desc = $p->description;
                $descSeo = $p->description;
                $page_type = 'cms_category';
                // Custom 2026-06-06: garde-fou objet supprime
                $page['canonical'] = Validate::isLoadedObject($p) ? $p->getLink() : '';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_CMS_CATE_FORCE_USE_META_TEMPLATE');
            } elseif (($id_manufaturer = (int)Tools::getValue('id_manufacturer')) && $controller == 'manufacturer') {
                $dataSeo = EtsSeoManufacturer::getSeoManufacturer($id_manufaturer, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_MANUFACTURER_SHOW_IN_SEARCH_RESULT');

                if ($generalTitle = Configuration::get('ETS_SEO_MANUFACTURER_META_TITLE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_MANUFACTURER_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }

                $p = new Manufacturer($id_manufaturer, $id_lang);
                $meta_title = $p->meta_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->name;

                $desc = $p->short_description;
                $desc = $p->description;
                $descSeo = $p->description ? $p->description : $p->short_description;
                $page_type = 'manufacturer';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_MANUFACTURER_FORCE_USE_META_TEMPLATE');
                if(!isset($page['canonical']) || !$page['canonical']){
                    $page['canonical'] = $this->context->link->getManufacturerLink($p, null, $this->context->language->id);
                }
            } elseif (($id_supplier = (int)Tools::getValue('id_supplier')) && $controller == 'supplier') {
                $dataSeo = EtsSeoSupplier::getSeoSupplier($id_supplier, $this->context, $id_lang);
                $config_allow_search = (int)Configuration::get('ETS_SEO_SUPPLIER_SHOW_IN_SEARCH_RESULT');

                if ($generalTitle = Configuration::get('ETS_SEO_SUPPLIER_META_TILE', $id_lang)) {
                    $config_meta_title = $generalTitle;
                }
                if ($generalDesc = Configuration::get('ETS_SEO_SUPPLIER_META_DESC', $id_lang)) {
                    $config_meta_desc = $generalDesc;
                }

                $p = new Supplier($id_supplier, $id_lang);
                $meta_title = $p->meta_title;
                $meta_desc = $p->meta_description;
                $post_title = $p->name;
                $desc = $p->description;
                $descSeo = $p->description;
                $page_type = 'supplier';
                $forceUseMetaTemplate = (int)Configuration::get('ETS_SEO_SUPPLIER_FORCE_USE_META_TEMPLATE');
                if(!isset($page['canonical']) || !$page['canonical']){
                    $page['canonical'] = $this->context->link->getSupplierLink($p, null, $this->context->language->id);
                }
            } else {
                $meta = Meta::getMetaByPage($this->context->controller->php_self, $id_lang);
                if ($meta) {
                    $meta_title = $meta['title'];
                    $meta_desc = $meta['description'];
                    $post_title = '';
                    $page_type = 'meta';
                    $dataSeo = EtsSeoMeta::getSeoMeta((int)$meta['id_meta'], $this->context, $id_lang);
                    $pageLink = $this->getPageLink($meta['page'], $id_lang);
                }

            }

            //Set default meta title if empty
            if (($forceUseMetaTemplate && $config_meta_title) || (!$meta_title && $config_meta_title)) {
                $page['meta']['title'] = $config_meta_title;
            }
            if (($forceUseMetaTemplate && $config_meta_desc) || (!$meta_desc && $config_meta_desc)) {
                $page['meta']['description'] = $config_meta_desc;
            }
            if (!isset($page['meta']['description']) || !$page['meta']['description']) {
                $page['meta']['description'] = $descSeo;
            }
            if (!isset($page['meta']['title']) || !$page['meta']['title']) {
                $page['meta']['title'] = $post_title;
            }
            if (!$post_title && $meta_title) {
                $post_title = $this->formatSeoMeta($page['meta']['title'], array('post_title' => '', 'is_title' => true, 'category' => $category_name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price), $page_type);
            }

            $page['meta']['title'] = $this->formatSeoMeta($page['meta']['title'], array('post_title' => $post_title, 'is_title' => true, 'category' => $category_name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price), $page_type);
            $page['meta']['description'] = $this->formatSeoMeta($page['meta']['description'], array('post_title' => $post_title, 'category' => $category_name, 'price' => $price, 'description' => $desc, 'description2' => $desc2, 'brand' => $brand, 'discount_price' => $discount_price), $page_type);
            $page['meta']['title'] = strip_tags($page['meta']['title']);
            $page['meta']['description'] = strip_tags($page['meta']['description']);
            if ((int)Configuration::get('PS_PRODUCT_ATTRIBUTES_IN_TITLE') && (int)Tools::getValue('id_product')) {
                $idProductAttribute = Product::getDefaultAttribute((int)Tools::getValue('id_product'));
                $page['meta']['title'] .= ' ' . EtsSeoProduct::getProductAttributeName($idProductAttribute);
            }
            //
            $meta_robot_default = isset($page['meta']['robots']) && $page['meta']['robots'] ? explode(',', $page['meta']['robots']) : array();
            $allow_search = $dataSeo ? (int)$dataSeo['allow_search'] : 1;
            $allow_flw_link = $dataSeo ? (int)$dataSeo['allow_flw_link'] : 1;
            $canonical_url = $dataSeo && $dataSeo['canonical_url'] ? $dataSeo['canonical_url'] : ($page_type == 'meta' && isset($pageLink) ? $pageLink : '');

            $meta_robot = $dataSeo ? $dataSeo['meta_robots_adv'] : '';
            $meta_robot = explode(',', $meta_robot);

            if (in_array('', $meta_robot)) {
                if (Tools::getValue('controller') == 'product') {
                    $meta_robot_default[] = 'index';
                }
            } elseif (in_array('none', $meta_robot)) {
                $meta_robot_default = array();
            } else {
                $meta_robot_default = array();
                if (in_array('noarchive', $meta_robot)) {
                    $meta_robot_default[] = 'noarchive';
                }
                if (in_array('nosnippet', $meta_robot)) {
                    $meta_robot_default[] = 'nosnippet';
                }
                if (in_array('noimageindex', $meta_robot)) {
                    $meta_robot_default[] = 'noimageindex';
                }
            }
            $index = null;
            if (!$allow_search || ($allow_search == 2 && !$config_allow_search)) {
                $index = 'noindex';
                foreach ($meta_robot_default as $k=>$rb){
                    if($rb == 'index')
                        unset($meta_robot_default[$k]);
                }
            }
            elseif(($allow_search == 2 && $config_allow_search) || $allow_search == 1){
                $index = 'index';
            }
            if ($index && array_search('index', $meta_robot_default) === false) {
                $noindexPost = array_search('noindex', $meta_robot_default);
                if ($noindexPost !== false) unset($meta_robot_default[$noindexPost]);
                $meta_robot_default[] = $index;
            }
            if (!$allow_flw_link) {
                $meta_robot_default[] = 'nofollow';
                foreach ($meta_robot_default as $k=>$rb){
                    if($rb == 'follow')
                        unset($meta_robot_default[$k]);
                }
            }

            $page['meta']['robots'] = implode(',', $meta_robot_default);
            if (trim($canonical_url)) {
                $page['canonical'] = $canonical_url;
                $seo_social['canonical'] = $canonical_url;
            }
            if (isset($dataSeo['social_title']) && $dataSeo['social_title']) {
                $dataSeo['social_title'] = $this->formatSeoMeta($dataSeo['social_title'], array('post_title' => $post_title, 'is_title' => true, 'category' => $category_name, 'price' => $price, 'description' => $desc), $page_type);
            }
            if (isset($dataSeo['social_desc']) && $dataSeo['social_desc']) {
                $dataSeo['social_desc'] = $this->formatSeoMeta($dataSeo['social_desc'], array('post_title' => $post_title, 'category' => $category_name, 'price' => $price, 'description' => $desc, 'description2' => $desc2), $page_type);
            }
            $seo_social['title'] = $dataSeo && $dataSeo['social_title'] ? $dataSeo['social_title'] : $page['meta']['title'];
            $seo_social['desc'] = $dataSeo && $dataSeo['social_desc'] ? $dataSeo['social_desc'] : $page['meta']['description'];
            $seo_social['url'] = $this->context->shop->getBaseURL(true, false) . $_SERVER['REQUEST_URI'];
            $seo_social['facebook_og'] = (int)Configuration::get('ETS_SEO_FACEBOOK_ENABLE_OG');
            $seo_social['twitter_card'] = (int)Configuration::get('ETS_SEO_TWITTER_ENABLE_CARD_META');
            $seo_social['twitter_card_type'] = Configuration::get('ETS_SEO_TWITTER_DEFAULT_CARD_TYPE');
            $seo_social['facebook_page'] = Configuration::get('ETS_SEO_URL_FACEBOOK');
            $seo_social['twitter_name'] = Configuration::get('ETS_SEO_URL_TWITTER');
            if ($seo_social['twitter_name']) {
                $seo_social['twitter_name'] = str_replace(' ', '', $seo_social['twitter_name']);
            }

            $seo_social['image'] = '';
            if ($dataSeo && $dataSeo['social_img']) {
                $seo_social['image'] = $this->context->shop->getBaseURL(true, true) . 'img/social/' . $dataSeo['social_img'];
            }

            if (!$seo_social['image']) {
                if ($img_default = Configuration::get('ETS_SEO_FACEBOOK_DEFULT_IMG_URL')) {
                    if(file_exists(_PS_IMG_DIR_.'social/'.$img_default)){
                        $seo_social['image'] = $this->context->shop->getBaseURL(true, true) . 'img/social/' . $img_default;
                    }

                } elseif ($img_front = Configuration::get('ETS_SEO_FACEBOOK_FP_IMG_URL')) {
                    if(file_exists(_PS_IMG_DIR_.'social/'.$img_front)) {
                        $seo_social['image'] = $this->context->shop->getBaseURL(true, true) . 'img/social/' . $img_front;
                    }
                }
                elseif ($img_auth = Configuration::get('ETS_SEO_SITE_ORIG_LOGO')) {
                    if(file_exists(_PS_IMG_DIR_.'social/'.$img_auth)) {
                        $seo_social['image'] = $this->context->shop->getBaseURL(true, true) . 'img/social/' . $img_auth;
                    }
                }

            }

            //
            $seo_social['pinterest_verification'] = Configuration::get('ETS_SEO_PINTEREST_CONFIRM');
            $seo_social['baidu_verification'] = Configuration::get('ETS_SEO_BAIDU_VERIFY_CODE');
            $seo_social['bing_verification'] = Configuration::get('ETS_SEO_BING_VERIFY_CODE');
            $seo_social['google_verification'] = Configuration::get('ETS_SEO_GOOGLE_VERIFY_CODE');
            $seo_social['yandex_verification'] = Configuration::get('ETS_SEO_YANDEX_VERIFY_CODE');
            $seo_social['social_verified'] = (int)Configuration::get('ETS_SEO_VERIFIED_BY_USING_OTHER_METHODS');
        }

        $this->context->smarty->assign(array(
            'page' => $page
        ));
        if (Tools::getValue('controller') == 'product') {
            $this->context->smarty->assign(array(
                'seo_social' => $seo_social,
                'ets_seo_social' => $seo_social,
                'is178' => $this->is178,
                'ets_seo_graph_knowledge' => Tools::jsonEncode($this->generateGraphWebData(), JSON_UNESCAPED_SLASHES)
            ));
        } else {
            $this->smarty->assign(array(
                'seo_social' => $seo_social,
                'graph_knowledge' => Tools::jsonEncode($this->generateGraphWebData(), JSON_UNESCAPED_SLASHES)
            ));
        }

    }

    public function getPageLink($page, $id_lang){
        try{
            return $this->context->link->getPageLink($page, null, $id_lang);
        }
        catch(Exception $ex){
            if($ex){
                return $this->context->link->getPageLink($page, null, $id_lang, $this->getParamsPage());
            }
            return '';
        }
    }

    public function getParamsPage()
    {
        $params = Tools::getAllValues();
        if(isset($params['id_lang']))
            unset($params['id_lang']);
        if(isset($params['controller']))
            unset($params['controller']);
        if(isset($params['isolang']))
            unset($params['isolang']);
        return $params;
    }

    public function getMetaCodeTemplate($type = null, $is_title = false)
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $this->smarty->assign(array(
            'list_meta_codes' => $seoDef->get_meta_codes($type, array('is_title' => $is_title))
        ));
        return $this->display(__FILE__, 'parts/meta_code.tpl');
    }


    /**
     * formatSeoMeta
     *
     * @param  string $type :title, desc
     * @param  string $str
     * @param  array $params
     *
     * @return string
     */
    public function formatSeoMeta($str, $params = array(), $type = null)
    {
        $seoDef = Ets_Seo_Define::getInstance();
        foreach ($seoDef->get_meta_codes($type, $params) as $item) {
            $str = str_replace($item['code'], $item['value'], $str);
        }
        return $str;
    }

    public function getLinkUrlRedirect($item, $type = 'target')
    {
        if ($type == 'url') {
            $this->smarty->assign(array(
                'ets_target' => $item['url'],
                'ets_link' => $item['url'],
            ));
        } else {
            $this->smarty->assign(array(
                'ets_target' => $item['target'],
                'ets_link' => strpos($item['target'], 'https://') === false && strpos($item['target'], 'http://') === false ? 'http://' . $item['target'] : $item['target'],
            ));
        }

        return $this->display(__FILE__, 'components/url_redirect_link.tpl');
    }

    public function deleteDataSocialImg($id, $type, $img, $is_cms_category = false)
    {
        $table = null;
        $id_col = '';
        switch ($type) {

            case 'AdminProducts':
                $table = 'ets_seo_product';
                $id_col = 'id_product';
                break;
            case 'AdminCmsContent':
                if($is_cms_category)
                {
                    $table = 'ets_seo_cms_category';
                    $id_col = 'id_cms_category';
                }
                else{
                    $table = 'ets_seo_cms';
                    $id_col = 'id_cms';
                }
                break;
            case 'AdminMeta':
                $table = 'ets_seo_meta';
                $id_col = 'id_meta';
                break;
            case 'AdminCategories':
                $table = 'ets_seo_category';
                $id_col = 'id_category';
                break;
            case 'AdminSuppliers':
                $table = 'ets_seo_supplier';
                $id_col = 'id_supplier';
                break;
            case 'AdminManufacturers':
                $table = 'ets_seo_manufacturer';
                $id_col = 'id_manufacturer';
                break;
        }
        if ($table && $id) {
            Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . (string)$table . "` SET `social_img` = NULL WHERE " . (string)$id_col . "=" . (int)$id . " AND `social_img`='" . (string)$img . "'");

            if ($table == 'ets_seo_cms') {
                Db::getInstance()->execute("UPDATE `" . _DB_PREFIX_ . "ets_seo_cms_category` SET `social_img` = NULL WHERE id_cms_category=" . (int)$id . " AND `social_img`='" . (string)$img . "'");
            }
            return true;
        }
        return false;
    }

    public function setRootSeoUrlConfig()
    {
        $seoDef = Ets_Seo_Define::getInstance();
        $urlSchemaConfigs = $seoDef->seo_url_schema_configs();
        foreach ($urlSchemaConfigs as $k => $config) {
            Configuration::updateGlobalValue($config['root_name'], Configuration::get('PS_ROUTE_' . $k));
        }
        return true;
    }

    public function restoreSeoUrlConfig()
    {
        Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL', 0);
        Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL', 0);
        Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS', 0);
        Configuration::updateValue('ETS_SEO_UPDATE_DUPLICATE_REWRITE', 0);
        Configuration::updateValue('ETS_SEO_SET_REMOVE_ID', 0);
        $seoDef = Ets_Seo_Define::getInstance();
        foreach ($seoDef->seo_url_schema_configs() as $rule => $name) {
            foreach (Shop::getShops() as $shop) {
                if ($configRule = Configuration::get($name['root_name'], null, null, $shop['id_shop'])) {
                    if ($rule !== 'module' && strpos($configRule, '{id}') !== false)
                        Configuration::updateValue('PS_ROUTE_' . $rule, $configRule, false, null, $shop['id_shop']);
                    else
                        Configuration::updateValue('PS_ROUTE_' . $rule, $name['default'], false, null, $shop['id_shop']);
                } else {
                    Configuration::updateValue('PS_ROUTE_' . $rule, $name['default'], false, null, $shop['id_shop']);
                }
            }

        }
        return true;
    }

    public function hookActionMetaPageSave($params)
    {
        if ($this->is178){
            $pattern = '/^ETS_SEO_.*$/';
            foreach (Tools::getAllValues() as $key => $value) {
                if (preg_match($pattern,$key)) {
                    Configuration::updateValue($key,$value);
                }
            }
        }
        $settings = null;
        if ($this->getRequestContainer()) {
            $settings = Tools::getValue('meta_settings_form');
        }
        $errors = array();
        if($settings && isset($settings['url_schema'])){
            if(count(array_unique($settings['url_schema'])) !== count($settings['url_schema'])){
                $errors[] = $this->l('Each route in schema of URLs must be unique');
            }
        }
        if($errors && !$params['errors']){
            $params['errors'] = $errors;
        }
    }

    public function updateConfigSeoNoid()
    {
        if (Tools::getIsset('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
            $params = null;
            if ($this->getRequestContainer()) {
                $params = ($params = Tools::getValue('meta_settings_form')) && is_array($params) ? $params : array();
            }

            $seoDef = Ets_Seo_Define::getInstance();

            $urlSchemaConfigs = $seoDef->seo_url_schema_configs();
            /* UPDATE schema configs */
            if (!(int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL') && (int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')) {
                if (!(int)Configuration::get('ETS_SEO_SET_REMOVE_ID')) {
                    Configuration::updateValue('ETS_SEO_SET_REMOVE_ID', 1);
                }

                foreach ($urlSchemaConfigs as $k => $config) {
                    if ($params && isset($params['url_schema'][$k])) {
                        $dataConfig = $params['url_schema'][$k];
                    } else {
                        $dataConfig = ($routeConfig = Tools::getValue('PS_ROUTE_' . $k)) && Validate::isCleanHtml($routeConfig) ? $routeConfig : '';
                    }

                    if ($dataConfig) {
                        Configuration::updateValue($config['no_id'], $dataConfig);
                        Configuration::updateValue($config['name'], Configuration::get('PS_ROUTE_' . $k));
                        $oldConfig = Configuration::get($config['old_name']);
                        $rootConfig = Configuration::get($config['root_name']);
                        if (!$oldConfig || ($oldConfig && $k != 'module' && !preg_match('/\{id\}/', $oldConfig))) {
                            Configuration::updateValue($config['old_name'], Configuration::get('PS_ROUTE_' . $k));
                        }
                        if (!$rootConfig || ($rootConfig && $k != 'module' && !preg_match('/\{id\}/', $rootConfig))) {
                            Configuration::updateValue($config['root_name'], Configuration::get('PS_ROUTE_' . $k));
                        }
                    }

                }
            }

            Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL', (int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL'));
            Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS', (int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS'));
            Configuration::updateValue('ETS_SEO_ENABLE_REDRECT_NOTFOUND', (int)Tools::getValue('ETS_SEO_ENABLE_REDRECT_NOTFOUND'));
            Configuration::updateValue('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL', (int)Tools::getValue('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL'));
            if ((int)Tools::getValue('ETS_SEO_REDIRECT_STATUS_CODE')) {
                Configuration::updateValue('ETS_SEO_REDIRECT_STATUS_CODE', (int)Tools::getValue('ETS_SEO_REDIRECT_STATUS_CODE'));
            }
        }

    }

    public function hookActionAdminEtsSeoUrlRedirectFormModifier($params)
    {
        if (isset($this->context->cookie->ets_seo_redirect_values)) {
            $params['fields_value'] = array_merge($params['fields_value'], Tools::jsonDecode($this->context->cookie->__get('ets_seo_redirect_values'), true));
            $this->context->cookie->__unset('ets_seo_redirect_values');
        }

    }

    public function getLinkDuplicate($type, $params)
    {
        $duplicate_link = '';
        $duplicate_title = '';
        if ($type == 'product') {
            $sfP = array('route' => 'admin_product_form', 'id' => $params['id']);
            $p = array('id_product' => $params['id'], 'edit' => true);
            $duplicate_link = $this->context->link->getAdminLink('AdminProducts', true, $sfP, $p);
            $duplicate_title = $params['title'];
        }
        elseif ($type == 'category') {
            $sfP = array('route' => 'admin_categories_edit', 'categoryId' => $params['id']);
            $p = array('id_category' => $params['id'], 'edit' => true);
            try{
                $duplicate_link = $this->context->link->getAdminLink('AdminCategories', true, $sfP, $p);
            }
            catch (Exception $ex){
                if($ex){
                    //
                }
                $duplicate_link = $this->context->link->getAdminLink('AdminCategories', true, array(), $p);
            }
            $duplicate_title = $params['title'];
        } elseif ($type == 'cms') {
            $sfP = array('route' => 'admin_cms_pages_edit', 'cmsPageId' => $params['id']);
            $p = array('id_cms' => $params['id'], 'edit' => true);
            try{
                $duplicate_link = $this->context->link->getAdminLink('AdminCmsContent', true, $sfP, $p);
            }
            catch (Exception $ex){
                if($ex){
                    //
                }
                $duplicate_link = $this->context->link->getAdminLink('AdminCmsContent', true, array(), $p);
            }
            $duplicate_title = $params['title'];
        } elseif ($type == 'cms_category') {
            $sfP = array('route' => 'admin_cms_pages_category_edit', 'cmsCategoryId' => $params['id']);
            $p = array('id_cms_category' => $params['id'], 'edit' => true);
            try{
                $duplicate_link = $this->context->link->getAdminLink('AdminCmsContent', true, $sfP, $p);
            }
            catch (Exception $ex){
                if($ex){
                    //
                }
                $duplicate_link = $this->context->link->getAdminLink('AdminCmsContent', true, array(), $p);
            }
            $duplicate_title = $params['title'];
        } elseif ($type == 'meta') {
            $sfP = array('route' => 'admin_metas_edit', 'metaId' => $params['id']);
            $p = array('id_meta' => $params['id'], 'edit' => true);
            try{
                $duplicate_link = $this->context->link->getAdminLink('AdminMeta', true, $sfP, $p);
            }
            catch (Exception $ex){
                if($ex){
                    //
                }
                $duplicate_link = $this->context->link->getAdminLink('AdminMeta', true, array(), $p);
            }
            $duplicate_title = $params['title'];
        }
        $this->smarty->assign(array(
            'duplicate_link' => $duplicate_link,
            'duplicate_title' => $duplicate_title,
        ));
        return $this->display(__FILE__, 'components/duplicate_link.tpl');
    }

    public function renderDashboardView()
    {
        return $this->display(__FILE__, 'dashboard.tpl');
    }

    public function setSitemap()
    {
        if ((int)Configuration::get('ETS_SEO_ENABLE_XML_SITEMAP') && (int)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            if (@file_exists(_PS_ROOT_DIR_ . '/robots.txt'))
                @rename(_PS_ROOT_DIR_ . '/robots.txt', _PS_ROOT_DIR_ . '/_robots.txt');
            $path = _PS_ROOT_DIR_ . '/_robots.txt';
        } else
            $path = _PS_ROOT_DIR_ . '/robots.txt';
        if (@file_exists($path) && @is_writable($path))
            $robots = trim(Tools::file_get_contents($path));
        else
            $robots = '';
        $robots = str_replace("\r\n", "\n", $robots);
        $robots = preg_replace('/^(Sitemap: .+index_sitemap.xml)$/im', '#$1', $robots);
        if ($shops = Shop::getShops(false)) {
            foreach ($shops as $shop) {
                $s = new Shop($shop['id_shop']);
                $shopUrl = $s->getBaseURL(true, true);
                if (!preg_match('/^Sitemap: ' . str_replace("/", "\/", $shopUrl) . 'sitemap.xml$/im', $robots))
                    $robots .= "\nSitemap: " . $shopUrl . "sitemap.xml";
            }
        }
        @file_put_contents($path, $robots);
        return true;
    }

    public function removeSitemap()
    {
        if (@file_exists(_PS_ROOT_DIR_ . '/_robots.txt'))
            @rename(_PS_ROOT_DIR_ . '/_robots.txt', _PS_ROOT_DIR_ . '/robots.txt');
        $path = _PS_ROOT_DIR_ . '/robots.txt';
        if (@file_exists($path) && @is_writable($path) && ($robots = Tools::file_get_contents($path))) {
            $robots = str_replace("\r\n", "\n", $robots);
            $robots = preg_replace('/^Sitemap: .+\/sitemap.xml$/im', '', $robots);
            $robots = preg_replace('/^#(Sitemap: .+index_sitemap.xml)$/im', '$1', $robots);
            @file_put_contents($path, $robots);
        }
        return true;
    }

    public function transTwig()
    {
        return array(
            'Content' => $this->l('Content'),
            'SEO settings' => $this->l('SEO settings'),
            'SEO analysis' => $this->l('SEO analysis'),
            'SEO score' => $this->l('SEO score'),
            'Readability score' => $this->l('Readability score'),
            'Remove ID in URL' => $this->l('Remove ID in URL'),
            'Remove ISO code in URL for default language' => $this->l('Remove ISO code in URL for default language'),
            'Remove attribute alias in URL' => $this->l('Remove attribute alias in URL'),
            'Redirect all old URLs to new URLs (keep your page rankings and backlinks)' => $this->l('Redirect all old URLs to new URLs (keep your page rankings and backlinks)'),
            'Redirect type' => $this->l('Redirect type'),
            '302 Moved Temporarily (recommended while setting up your store)' => $this->l('302 Moved Temporarily (recommended while setting up your store)'),
            '301 Moved Permanently (recommended once you have gone live)' => $this->l('301 Moved Permanently (recommended once you have gone live)'),
            'All SEO Scores' => $this->l('All SEO Scores'),
            'SEO: Not good' => $this->l('SEO: Not good'),
            'SEO: Acceptable' => $this->l('SEO: Acceptable'),
            'SEO: Excellent' => $this->l('SEO: Excellent'),
            'SEO: No Focus or Related key phrases' => $this->l('SEO: No Focus or Related key phrases'),
            'SEO: No Index' => $this->l('SEO: No Index'),
            'All Readability Scores' => $this->l('All Readability Scores'),
            'Readability: Not good' => $this->l('Readability: Not good'),
            'Readability: Acceptable' => $this->l('Readability: Acceptable'),
            'Readability: Excellent' => $this->l('Readability: Excellent'),
        );
    }

    public function hookActionAdminShopParametersMetaControllerPostProcessUrlSchemaBefore()
    {
        if ($this->is178){
            $this->updateConfigSeoNoid();
        }
    }
}
