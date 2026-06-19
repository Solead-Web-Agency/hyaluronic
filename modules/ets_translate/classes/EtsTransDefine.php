<?php
/**
 * 2007-2020 ETS-Soft
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
 * @copyright  2007-2020 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class EtsTransDefine{
    public $context;
    public $module;
    public static $instance = null;

    public function __construct($module = null)
    {
        if (!(is_object($module)) || !$module) {
            $module = Module::getInstanceByName('ets_translate');
        }
        $this->module = $module;
        $context = Context::getContext();
        $this->context = $context;
    }

    public function l($string)
    {
        return Translate::getModuleTranslation('ets_translate', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }


    public function display($template)
    {
        if (!$this->module)
            return;
        return $this->module->display($this->module->getLocalPath(), $template);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EtsTransDefine();
        }
        return self::$instance;
    }

    public function installDb()
    {
        $tblLog = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_trans_log` (
            `id_ets_trans_log` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_session` VARCHAR(20) DEFAULT NULL,
            `page_type` VARCHAR(20) NOT NULL,
            `lang_source` VARCHAR(20) NOT NULL,
            `lang_target` VARCHAR(191) NOT NULL,
            `ids_translated` VARCHAR(191) DEFAULT NULL,
            `text_translated` TEXT DEFAULT NULL,
            `status` TINYINT(1) DEFAULT NULL,
            `res_message` TEXT DEFAULT NULL,
            `timeout` INT(10) DEFAULT NULL,
            `date_add` DATETIME DEFAULT NULL,
            `id_shop` INT(10) NOT NULL,
            PRIMARY KEY (`id_ets_trans_log`),
            INDEX (`id_shop`, `status`, `page_type`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";

        $tblCache = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."ets_trans_cache` (
            `id_ets_trans_cache` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `cache_type` VARCHAR(50) NOT NULL,
            `name` VARCHAR(50) DEFAULT NULL,
            `file_path` TEXT DEFAULT NULL,
            `file_type` VARCHAR(20) DEFAULT NULL,
            `nb_translated` INT(10) DEFAULT NULL,
            `status` TINYINT(1) DEFAULT NULL,
            `is_oneclick` TINYINT(1) DEFAULT 0,
            `date_add` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_upd` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            `id_shop` INT(10) NOT NULL,
            PRIMARY KEY (`id_ets_trans_cache`),
            INDEX (`cache_type`, `name`, `status`,`id_shop`,`is_oneclick`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=UTF8";


        return Db::getInstance()->execute($tblLog)
            && Db::getInstance()->execute($tblCache);
    }

    public function uninstallDb()
    {
        return Db::getInstance()->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_."ets_trans_log`")
            && Db::getInstance()->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_."ets_trans_cache`");
    }

    public function configTransAllWensite()
    {
        return array(
            'ETS_TRANS_WD_CONFIG' => array(
                'name' => 'ETS_TRANS_WD_CONFIG',
                'label' => '',
                'is_array' => true,
                'type' => 'text',
            )
        );
    }

    public function treeWebPageSelection()
    {
        $modules = EtsTransCore::getAllModules();
        $themes = EtsTransCore::getAllThemes();
        $emails = EtsTransInternational::getEmailTemplate(null, true);
        $pagesTrans = array(
            'all' => array(
                'title' => $this->l('All (translate everything)'),
                'name' => 'wd_all',
            ),
            'catalog' => array(
                'title' => $this->l('Catalog'),
                'name' => 'catalog_all',
                'items' => array(
                    'product' => array(
                        'title' => $this->l('Products'),
                        'name' => 'catalog_product',
                    ),
                    'category' => array(
                        'title' =>$this->l('Categories'),
                        'name' => 'catalog_category',
                    ),
                    'manufacturer' => array(
                        'title' => $this->l('Brands'),
                        'name' => 'catalog_manufacturer',
                    ),
                    'supplier' => array(
                        'title' => $this->l('Suppliers'),
                        'name' => 'catalog_supplier',
                    ),
                    'attribute' => array(
                        'title' => $this->l('Attributes'),
                        'name' => 'catalog_attribute',
                    ),
                    'attribute_group' => array(
                        'title' => $this->l('Attribute groups'),
                        'name' => 'catalog_attribute_group',
                    ),
                    'feature' => array(
                        'title' => $this->l('Features'),
                        'name' => 'catalog_feature',
                    ),
                    'feature_value' => array(
                        'title' => $this->l('Feature values'),
                        'name' => 'catalog_feature_value',
                    ),
                )
            ),
            'page' => array(
                'title' => $this->l('Pages'),
                'name' => 'page_all',
                'items' => array(
                    'cms_category' => array(
                        'title' => $this->l('CMS categories'),
                        'name' => 'page_cms_category',
                    ),
                    'cms' => array(
                        'title' => $this->l('CMS pages'),
                        'name' => 'page_cms',
                    ),
                )
            ),
            'inter' => array(
                'title' => $this->l('International / Translations'),
                'name' => 'inter_all',
                'items' => array(
                    'back' => array(
                        'title' => $this->l('Back office'),
                        'name' => 'inter_back',
                    ),
                    'theme' => array(
                        'title' => $this->l('Themes'),
                        'name' => 'inter_theme',
                        'items' => EtsTransCore::setNameForAllThemes($themes, 'inter_theme_'),
                    ),
                    'module' => array(
                        'title' => $this->l('Installed modules'),
                        'name' => 'inter_module',
                        'items' => EtsTransCore::setNameForAllModules($modules, 'inter_module_'),
                    ),
                    'email' => array(
                        'title' => $this->l('Email'),
                        'name' => 'inter_email',
                        'items' => array(
                            'subject' => array(
                                'title' => $this->l('Subjects'),
                                'name' => 'inter_email_subject',
                            ),
                            'body' => array(
                                'title' => $this->l('Body'),
                                'name' => 'inter_email_body',
                                'items' => array(
                                    'core' =>array(
                                        'title' => $this->l('Core'),
                                        'name' => 'inter_email_body_core',
                                        'emails' => EtsTransCore::setValForEmail($emails, 'inter_email_body_core_'),
                                    ),
                                    'theme' =>array(
                                        'title' => $this->l('Theme'),
                                        'name' => 'inter_email_body_theme',
                                        'items' => EtsTransCore::setEmailForAllThemes(EtsTransCore::setNameForAllThemes($themes, 'inter_email_body_theme_'), $emails),
                                    ),
                                )
                            ),
                        )
                    ),
                    'others' => array(
                        'title' => $this->l('Static pages (pages in SEO settings tab such homepage, login, my account, etc.)'),
                        'name' => 'inter_other',
                    ),
                )
            ),
        );
        if(Module::isInstalled('ybc_blog')){
            $pagesTrans['blog'] = array(
                'title' => $this->l('Blog'),
                'name' => 'blog_all',
                'items' => array(
                    'blog_post' => array(
                        'title' => $this->l('Blog posts'),
                        'name' => 'blog_post'
                    ),
                    'blog_category' => array(
                        'title' => $this->l('Blog categories'),
                        'name' => 'blog_category'
                    ),
                )
            );
        }
        if(Module::isInstalled('ets_megamenu')){
            $pagesTrans['megamenu'] = array(
                'title' => $this->l('Mega Menu Pro'),
                'name' => 'megamenu'
            );
        }
        if(Module::isInstalled('ets_productcomments')){
            $pagesTrans['pc'] = array(
                'title' => $this->l('Product comments'),
                'name' => 'pc'
            );
        }
        if(Module::isInstalled('blockreassurance')){
            $pagesTrans['blockreassurance'] = array(
                'title' => $this->l('Customer Reassurance'),
                'name' => 'blockreassurance'
            );
        }
        if(Module::isInstalled('ps_linklist')){
            $pagesTrans['ps_linklist'] = array(
                'title' => $this->l('Link widget (footer menu)'),
                'name' => 'ps_linklist'
            );
        }
        if(Module::isInstalled('ps_mainmenu')){
            $pagesTrans['ps_mainmenu'] = array(
                'title' => $this->l('Main menu (top menu)'),
                'name' => 'ps_mainmenu'
            );
        }
        if(Module::isInstalled('ps_customtext')){
            $pagesTrans['ps_customtext'] = array(
                'title' => $this->l('Custom text blocks'),
                'name' => 'ps_customtext'
            );
        }
        if(Module::isInstalled('ps_imageslider')){
            $pagesTrans['ps_imageslider'] = array(
                'title' => $this->l('Image slider'),
                'name' => 'ps_imageslider'
            );
        }
        if(Module::isInstalled('ets_extraproducttabs')){
            $pagesTrans['ets_extraproducttabs'] = array(
                'title' => $this->l('Extra Product Info Tabs'),
                'name' => 'ets_extraproducttabs'
            );
        }
        return $pagesTrans;
    }

    public static function getTextLang($text, $lang,$file_name='')
    {
        $moduleName = 'ets_translate';
        $text2 = preg_replace("/\\\*'/", "\'", $text);
        if(is_array($lang))
            $iso_code = $lang['iso_code'];
        elseif(is_object($lang))
            $iso_code = $lang->iso_code;
        else
        {
            $language = new Language($lang);
            $iso_code = $language->iso_code;
        }
        $modulePath = rtrim(_PS_MODULE_DIR_, '/').'/'.$moduleName;
        $fileTransDir = $modulePath.'/translations/'.$iso_code.'.'.'php';
        if(!@file_exists($fileTransDir)){
            return '';
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $strMd5 = md5($text2);
        $keyMd5 = '<{' . $moduleName . '}prestashop>' .($file_name ? Tools::strtolower($file_name) : $moduleName). '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5) . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if($matches && isset($matches[2])){
            return  $matches[2];
        }
        return '';
    }

}