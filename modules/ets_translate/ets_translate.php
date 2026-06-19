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

require_once dirname(__FILE__).'/classes/EtsTransApi.php';
require_once dirname(__FILE__).'/classes/EtsTransDefine.php';
require_once dirname(__FILE__).'/classes/EtsTransPage.php';
require_once dirname(__FILE__).'/classes/EtsTransInternational.php';
require_once dirname(__FILE__).'/classes/EtsTransConfig.php';
require_once dirname(__FILE__).'/classes/EtsTransLog.php';
require_once dirname(__FILE__).'/classes/EtsTransCache.php';
require_once dirname(__FILE__).'/classes/EtsTransCore.php';
require_once dirname(__FILE__).'/classes/EtsTransModule.php';
require_once dirname(__FILE__).'/classes/EtsTransNewSystem.php';
require_once dirname(__FILE__).'/classes/EtsTransAll.php';

class Ets_Translate extends Module
{
    public $listControllerAllowed = array();

    public function __construct()
    {
        $this->name = 'ets_translate';
        $this->tab = 'front_office_features';
        $this->version = '1.0.2';
        $this->author = 'ETS-Soft';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '72a1ed11a4df4c137e09b44930c85329';
        parent::__construct();
        $this->displayName = $this->l('G-Translate: Translate everything you see!');
        $this->description = $this->l('Automated PrestaShop translation tool for content, theme, module, front office, back office and core files based on Google translate');
        $this->ps_versions_compliancy = array('min' => '1.7.0.1', 'max' => _PS_VERSION_);
        $this->listControllerAllowed = array(
            'AdminProducts',
            'AdminCategories',
            'AdminCmsContent',
            'AdminManufacturers',
            'AdminSuppliers',
        );
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        $etsDef = EtsTransDefine::getInstance();
        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayBackOfficeTop')
            && $this->setDefaultConfig()
            && $etsDef->installDb()
            && $this->installTabs();
    }

    public function uninstall()
    {
        $etsDef = EtsTransDefine::getInstance();
        return parent::uninstall()
            && $this->deleteKeyConfig()
            && $etsDef->uninstallDb()
            && $this->uninstallTabs();
    }

    protected function setDefaultConfig()
    {
        $languages = Language::getLanguages(false);
        foreach ($this->getFieldForm() as $field) {
            if (isset($field['default']) && $field['default']) {
                if (isset($field['lang']) && $field['lang']) {
                    $value = array();
                    foreach ($languages as $lang) {
                        $value[$lang['id_lang']] = $field['default'];
                    }
                    Configuration::updateGlobalValue($field['name'], $value);
                } else {
                    Configuration::updateGlobalValue($field['name'], $field['default']);
                }
            }
        }

        return true;
    }

    protected function deleteKeyConfig()
    {
        foreach ($this->getFieldForm() as $field) {
            Configuration::deleteByName($field['name']);
        }
        EtsTransConfig::resetKeyConfig();
        return true;
    }

    public function installTabs()
    {
        $parentTabId = Tab::getIdFromClassName('AdminInternational');
        $languages = Language::getLanguages(false);
        if($parentTabId){
            $tab = new Tab();
            $tab->id_parent = $parentTabId;
            $tab->module = $this->name;
            $tab->class_name = 'AdminEtsTransConfig';
            foreach ($languages as $lang){
                $tab->name[$lang['id_lang']] = EtsTransDefine::getTextLang('G-Translate', $lang) ?: $this->l('G-Translate');
            }
            $tab->icon = 'translate';
            $tab->save();
        }

        return true;

    }
    public function uninstallTabs()
    {
        $tabConfigId = Tab::getIdFromClassName('AdminEtsTransConfig');
        if($tabConfigId){
            $tabConfig = new Tab((int)$tabConfigId);
            $tabConfig->delete();
        }

        return true;
    }

    public function getContent()
    {
        $this->actionAjax();
        if(Tools::getValue('viewTranslateLog')){
            return $this->renderListLog();
        }
        $inConfigTransWD = Tools::getIsset('configTransWebsite') ? true : false;
        if(!$inConfigTransWD){
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminEtsTransConfig'));
        }
        return $this->renderConfig($inConfigTransWD);
    }

    public function renderConfig($inConfigTransWD = false)
    {
        $this->saveFormConfig();
        $etsDef = EtsTransDefine::getInstance();
        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $inConfigTransWD ? $this->l('1-Click translate') : $this->l('Global settings')
                ),
                'input' => $inConfigTransWD ? $etsDef->configTransAllWensite() : $this->getFieldForm(true),
                'submit' => array(
                    'name' => $inConfigTransWD ? 'saveConfigTransAllWD' : 'saveEtstransSettings',
                    'title' => $inConfigTransWD ? $this->l('Translate') : $this->l('Save'),
                    'class' => $inConfigTransWD ? 'btn btn-default pull-right saveConfigAll js-ets-trans-btn-trans-all-website' : 'btn btn-default pull-right',
                    'icon' => 'process-icon-save'
                ),
            ),
        );
        if($inConfigTransWD){
            $fieldsForm['form']['buttons'] = array(
                array(
                    'title' => $this->l('Back'),
                    'class' => 'ets-trans-btn-back',
                    'icon' => 'process-icon-arrow-left',
                    'href' => $this->context->link->getAdminLink('AdminEtsTransConfig')
                )
            );
        }

        $fieldsValue = array();
        if($inConfigTransWD)
        {
            $configs = $etsDef->configTransAllWensite();
        }
        else{
            $configs = $this->getFieldForm();
        }

        foreach ($configs as $field) {
            if(isset($field['lang']) && $field['lang']){
                $fieldsValue[$field['name']] = array();
                foreach (Language::getLanguages(false) as $lang){
                    $fieldsValue[$field['name']][$lang['id_lang']] = Tools::getIsset($field['name'].'_'.$lang['id_lang']) ? Tools::getValue($field['name'].'_'.$lang['id_lang']) : Configuration::get($field['name'], $lang['id_lang']);
                }
            }
            else{
                $fieldsValue[$field['name']] = Tools::getIsset($field['name']) ? Tools::getValue($field['name']) : Configuration::get($field['name']);
            }
            if(isset($field['is_array']) && $field['is_array']  && !is_array($fieldsValue[$field['name']])){
                $fieldsValue[$field['name']] = explode(',', $fieldsValue[$field['name']]);
            }
        }
        return $this->renderForm($fieldsForm, $fieldsValue);
    }

    protected function renderListLog()
    {
        $this->smarty->assign(array(
            'logData' => EtsTransLog::getLogs(50, Tools::getValue('page', 1)),
            'linkPaginate' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&viewTranslateLog=1',
            'linkConfig' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name,
        ));
        return $this->display(__FILE__, 'list_translate_log.tpl');
    }

    protected function renderForm($fields_form, $fields_value)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEts_testModule';
        $helper->currentIndex = Tools::getIsset('configTransWebsite') ? $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name.'&configTransWebsite=1' : '';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $etsDef = EtsTransDefine::getInstance();
        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'langTarget' => Language::getLanguages(true),
            'langWithFlag' => $this->getLangWithFlagImage(true),
            'treeWebPageOption' => $etsDef->treeWebPageSelection(),
            'isMultipleLanguage' => Language::isMultiLanguageActivated(),
            'linkToConfigLang' => $this->getLocalizationLink(),
            'pageAppendContextWords' => $this->getPageAppendContextWords(),
            'linkToConfigWd' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&configTransWebsite=1',
            'ETS_TRANS_LANG_TARGET' => Tools::getValue('saveEtstransSettings') ? Tools::getValue('ETS_TRANS_LANG_TARGET',array()) : explode(',', Configuration::get('ETS_TRANS_LANG_TARGET')),
            'ETS_TRANS_WD_CONFIG' => Tools::getValue('saveEtstransSettings') ? Tools::getValue('ETS_TRANS_WD_CONFIG', array()) : explode(',', Configuration::get('ETS_TRANS_WD_CONFIG')),
            'ETS_TRANS_PAGE_APPEND_CONTEXT_WORD' => Tools::getValue('saveEtstransSettings') ? Tools::getValue('ETS_TRANS_PAGE_APPEND_CONTEXT_WORD', array()) : explode(',', Configuration::get('ETS_TRANS_PAGE_APPEND_CONTEXT_WORD')),
        );
        return $helper->generateForm(array($fields_form));
    }

    public function getPageAppendContextWords()
    {
        $pages = array(
            array(
                'title' => $this->l('Catalog (products, categories, features and attributes, etc.)'),
                'value' => 'catalog',
            ),
            array(
                'title' => $this->l('CSM Pages and categories'),
                'value' => 'page',
            ),
            array(
                'title' => $this->l('International / Translations (texts in tpl or php files and email templates)'),
                'value' => 'inter',
            ),
        );
        if(Module::isInstalled('ybc_blog')){
            $pages[] = array(
                'title' => $this->l('Blog'),
                'value' => 'blog'
            );
        }
        if(Module::isInstalled('ets_megamenu')){
            $pages[] = array(
                'title' => $this->l('Mega Menu Pro'),
                'value' => 'megamenu'
            );
        }
        if(Module::isInstalled('ets_productcomments')){
            $pages[] = array(
                'title' => $this->l('Product comments'),
                'value' => 'pc'
            );
        }
        if(Module::isInstalled('blockreassurance')){
            $pages[] = array(
                'title' => $this->l('Customer Reassurance'),
                'value' => 'blockreassurance'
            );
        }
        if(Module::isInstalled('ps_linklist')){
            $pages[] = array(
                'title' => $this->l('Link widget (footer menu)'),
                'value' => 'ps_linklist'
            );
        }
        if(Module::isInstalled('ps_mainmenu')){
            $pages[] = array(
                'title' => $this->l('Main menu (top menu)'),
                'value' => 'ps_mainmenu'
            );
        }
        if(Module::isInstalled('ps_customtext')){
            $pages[] = array(
                'title' => $this->l('Custom text blocks'),
                'value' => 'ps_customtext'
            );
        }
        if(Module::isInstalled('ps_imageslider')){
            $pages[] = array(
                'title' => $this->l('Image slider on home page'),
                'value' => 'ps_imageslider'
            );
        }
        if(Module::isInstalled('ets_extraproducttabs')){
            $pages[] = array(
                'title' => $this->l('Extra Product Info Tabs'),
                'value' => 'ets_extraproducttabs'
            );
        }
        return $pages;
    }

    public function getLocalizationLink()
    {
        try{
            return $this->context->link->getAdminLink('AdminLocalization', true, array('route' => 'admin_localization_index'), array());
        }
        catch(Exception $ex){
            return $this->context->link->getAdminLink('AdminLocalization', true);
        }

    }

    public function getFieldForm($isInForm = false)
    {
        if ($isInForm) {
            $this->smarty->assign(array(
                'linkDesc' => 'https://cloud.google.com/translate/docs/basic/setup-basic',
                'linkText' => $this->l('How to get API key?'),
                'linkTarget' => '_blank',
                'checkApi' => true,
                'linkViewLog' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&viewTranslateLog=1',
                'linkConfigTransWD' => $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name.'&configTransWebsite=1',
                'ETS_TRANS_SUFFIX_RATE_GOOGLE' => Tools::isSubmit('saveEtstransSettings') ? Tools::getValue('ETS_TRANS_SUFFIX_RATE_GOOGLE') : Configuration::get('ETS_TRANS_SUFFIX_RATE_GOOGLE')
            ));
            $transOption = array();
            foreach ($this->renderTransOptions() as $key=>$option){
                $transOption[] = array(
                    'label' => $option['title'],
                    'value' => $key,
                    'id' => 'ETS_TRANS_FILED_TRANS_'.$key
                );
            }
            $ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD = Tools::isSubmit('saveEtstransSettings') ? (int)Tools::getValue('ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD') : (int)Configuration::get('ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD');
        }
        $defaultLangTarget = Language::getIDs(true);
        $defaultLangSource = (int)Configuration::get('PS_LANG_DEFAULT');
        if (($key = array_search($defaultLangSource, $defaultLangTarget)) !== false) {
            unset($defaultLangTarget[$key]);
        }
        $enableAutoSetting = Tools::isSubmit('saveEtstransSettings') ? (int)Tools::getValue('ETS_TRANS_AUTO_SETTING_ENABLED') : (int)Configuration::get('ETS_TRANS_AUTO_SETTING_ENABLED');
        return array(
            'ETS_TRANS_GOOGLE_API_KEY' => array(
                'name' => 'ETS_TRANS_GOOGLE_API_KEY',
                'label' => $this->l('Google API key for "Cloud Translation API"'),
                'desc' => $isInForm ? $this->display(__FILE__, 'parts/link_desc.tpl') : $this->l('How to get API key?'),
                'validate' => 'isString',
                'required' => true,
                'type' => 'text',
                'isApiKey' => true,
                'error_message' => array(
                    'required' => $this->l('The Google translate API key is required'),
                    'validate' => $this->l('The Google translate API key must be a string'),
                    'isApiKey' => $this->l('The Google translate API key is invalid'),
                )
            ),
            'ETS_TRANS_AUTO_SETTING_ENABLED' => array(
                'name' => 'ETS_TRANS_AUTO_SETTING_ENABLED',
                'label' => $this->l('Auto apply global settings when translating'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('If this option is turned off, you will be required to specify your preferred translation options every time you translate'),
            ),
            'ETS_TRANS_LANG_SOURCE' => array(
                'name' => 'ETS_TRANS_LANG_SOURCE',
                'label' => $this->l('Translate from'),
                'type' => 'text',
                'form_group_class' => 'ets-trans-auto-setting-group '.(!$enableAutoSetting ? 'hide' : ''),
                'default' => $defaultLangSource,
                'error_message' => array(
                    'required' => $this->l('The "Translate from" field is required'),
                ),
                'desc' => $this->l('This is source language that will be used to translate into destination languages selected below')
            ),
            'ETS_TRANS_LANG_TARGET' => array(
                'name' => 'ETS_TRANS_LANG_TARGET',
                'label' => $this->l('Translate to'),
                'type' => 'text',
                'form_group_class' => 'ets-trans-auto-setting-group ets-trans-target-lang-config '.(!$enableAutoSetting ? 'hide' : ''),
                'multiple' => true,
                'default' => isset($defaultLangTarget) ? implode(',', $defaultLangTarget) : '',
                'error_message' => array(
                    'required' => $this->l('The "Translate to" field is required'),
                ),
                'desc' => $this->l('These are destination languages to apply translation, they will be translated from the source language. Translation will only be performed for languages selected here')
            ),
            'ETS_TRANS_AUTO_DETECT_LANG' => array(
                'name' => 'ETS_TRANS_AUTO_DETECT_LANG',
                'label' => $this->l('Auto detect customer language when translating customer comments'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('The detected language of customer comments will be selected as source language of the translation'),
            ),
            'ETS_TRANS_FILED_TRANS' => array(
                'name' => 'ETS_TRANS_FILED_TRANS',
                'label' => $this->l('How to translate?'),
                'type' => 'radio',
                'form_group_class' => 'ets-trans-auto-setting-group '.(!$enableAutoSetting ? 'hide' : ''),
                'values' => isset($transOption) ? $transOption : array(),
                'default' => 'both',
                'error_message' => array(
                    'required' => $this->l('The "How to translate?" field is required'),
                ),
            ),
            'ETS_TRANS_AUTO_GENERATE_LINK_REWRITE' => array(
                'name' => 'ETS_TRANS_AUTO_GENERATE_LINK_REWRITE',
                'label' => $this->l('Regenerate friendly URL when translating titles'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('Friendly URL will be generated base on the translated text when titles such as product name, category name, CMS page title, etc. are translated'),
            ),
            'ETS_TRANS_ENABLE_ANALYSIS' => array(
                'name' => 'ETS_TRANS_ENABLE_ANALYSIS',
                'label' => $this->l('Analyze the translation before translating'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('You will be noticed how many characters you are going to translate and an estimated cost you will pay Google for the translation')
            ),
            'ETS_TRANS_IGNORE_OLD_CONTROLLER' => array(
                'name' => 'ETS_TRANS_IGNORE_OLD_CONTROLLER',
                'label' => $this->l('Ignore old controller files when translating'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('Old controller files are not used in PrestaShop 1.7 however lot of them still exists, we do not need to translate text in those files.')
            ),
            'ETS_TRANS_ENABLE_TRANS_FIELD' => array(
                'name' => 'ETS_TRANS_ENABLE_TRANS_FIELD',
                'label' => $this->l('Enable field translatation'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->l('You will see a "Translate" icon besides every input field that allows you translate the content inside the field')
            ),
            'ETS_TRANS_EXCLUDE_WORDS' => array(
                'name' => 'ETS_TRANS_EXCLUDE_WORDS',
                'label' => $this->l('Excluded words (or phrases)'),
                'default' => '',
                'type' => 'textarea',
                'col' => 4,
                'rows'=> 5,
                'desc' => $this->l('These words or phrases will not be translated. Each word or phrase on a line.')
            ),
            'ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD' => array(
                'name' => 'ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD',
                'label' => $this->l('Append contextual words when translating'),
                'type' => 'switch',
                'default' => 0,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
            ),
            'ETS_TRANS_CONTEXT_WORDS' => array(
                'name' => 'ETS_TRANS_CONTEXT_WORDS',
                'label' => $this->l('Append contextual words to improve translations'),
                'type' => 'textarea',
                'lang' => true,
                'required' => true,
                'col' => 6,
                'rows' => 5,
                'default' => 'data, database, programming, computer, technology',
                'form_group_class' => 'ets-trans-append-context-word'.(isset($ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD) && $ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD ? '': ' hide'),
                'error_message' => array(
                    'required' => $this->l('The context words is required'),
                ),
                'desc' => $this->l('These words help Google understands the context of the translation better, especially when the text to translate is too short (has only one or a few words). These words will be removed from the translation result')
            ),
            'ETS_TRANS_PAGE_APPEND_CONTEXT_WORD' => array(
                'name' => 'ETS_TRANS_PAGE_APPEND_CONTEXT_WORD',
                'label' => $this->l('Pages to append contextual words when translating'),
                'type' => 'text',
                'required' => true,
                'form_group_class' => 'ets-trans-append-context-word'.(isset($ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD) && $ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD ? '': ' hide'),
                'default' => 'inter',
                'error_message' => array(
                    'required' => $this->l('The pages to append context words is required'),
                ),
            ),
            'ETS_TRANS_MAX_WORD_APPEND_CONTEXT_WORD' => array(
                'name' => 'ETS_TRANS_MAX_WORD_APPEND_CONTEXT_WORD',
                'label' => $this->l('Append contextual words when original text to translate fewer than (or equals)'),
                'type' => 'text',
                'col' => 2,
                'suffix' => $this->l('words'),
                'validate' => 'isUnsignedInt',
                'default' => 10,
                'form_group_class' => 'ets-trans-append-context-word'.(isset($ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD) && $ETS_TRANS_ENABLE_APPEND_CONTEXT_WORD ? '': ' hide'),
                'error_message' => array(
                    'validate' => $this->l('The "Append contextual words when original text to translate fewer than (or equals)" is invalid'),
                ),
                'desc' => $this->l('Leave blank to append contextual words to every text when translating')
            ),
            'ETS_TRANS_RATE_GOOGLE' => array(
                'name' => 'ETS_TRANS_RATE_GOOGLE',
                'label' => $this->l('Google translation pricing (per one million characters)'),
                'suffix' => $this->display(__FILE__,'suffix_rate_google.tpl'),
                'form_group_class' => 'ets-trans-rate-setting',
                'validate' => 'isUnSignedFloat',
                'type' => 'text',
                'desc' => $this->l('Don\'t panic! You have 500.000 free characters every month and Google also offers 12-month and $300 free trial! For more see').' '.$this->display(__FILE__, 'parts/pricing.tpl').'. '.$this->l('This value is used to estimate your expense when translating your website content. Leave blank if you do not want to see estimated cost for your translation. '),
                'error_message' => array(
                    'validate' => $this->l('The Google translation pricing must be a decimal number'),
                )
            ),
            'ETS_TRANS_SUFFIX_RATE_GOOGLE' => array(
                'name' => 'ETS_TRANS_SUFFIX_RATE_GOOGLE',
                'label' => '',
                'default' => 'USD',
                'type' => 'text',
                'required' => true,
                'error_message' => array(
                    'required' => $this->l('The Google translation rate unit is required'),
                )
            ),
            'ETS_TRANS_ENABLE_LOG' => array(
                'name' => 'ETS_TRANS_ENABLE_LOG',
                'label' => $this->l('Enable translation log'),
                'type' => 'switch',
                'default' => 1,
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('No'),
                    ),
                ),
                'desc' => $this->display(__FILE__, 'parts/desc_log.tpl')
            ),
        );
    }

    public function saveFormConfig()
    {
        if(Tools::isSubmit('saveConfigTransAllWD')){
            $wdValue = is_array(Tools::getValue('ETS_TRANS_WD_CONFIG')) ? implode(',', Tools::getValue('ETS_TRANS_WD_CONFIG')) : Tools::getValue('ETS_TRANS_WD_CONFIG');
            Configuration::updateValue('ETS_TRANS_WD_CONFIG', $wdValue);
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');
        $request = $this->getRequestContainer();
        $arrayAdminPath = explode('/', str_replace('\\', '/', _PS_ADMIN_DIR_));
        $this->smarty->assign(array(
            'transJs' => $this->transJs(),
            'linkAjaxModule' =>$this->context->link->getAdminLink('AdminModules').'&configure='.$this->name,
            'hasModuleSeo' => Module::isInstalled('ets_seo'),
            'linkAjaxBo' => _MODULE_DIR_ . $this->name . '/ajax.bo.php?token=' . Tools::getAdminTokenLite('AdminModules'),
            'langSourceDefault' => Language::getIdByIso('en'),
            'langTargetInterTrans' => Tools::getValue('lang') ? Language::getIdByIso(Tools::getValue('lang')) : '',
            'isAutoConfigEnabled' => (int)Configuration::get('ETS_TRANS_AUTO_SETTING_ENABLED'),
            'defaultTransConfig' => $this->getDefaultTransConfig(),
            'linkPsAdmin' => $arrayAdminPath[count($arrayAdminPath)-1],
            'enableAnalysis' => (int)Configuration::get('ETS_TRANS_ENABLE_ANALYSIS'),
            'rateGoogleVal' => Configuration::get('ETS_TRANS_RATE_GOOGLE') ? (float)Configuration::get('ETS_TRANS_RATE_GOOGLE') : '',
            'rateGoogleSuffix' => ($googleRateSuffix = Configuration::get('ETS_TRANS_SUFFIX_RATE_GOOGLE')) && Tools::strlen($googleRateSuffix) ? $googleRateSuffix : '',
            'enableAutoGenerateLinkRewrite' => (int)Configuration::get('ETS_TRANS_AUTO_GENERATE_LINK_REWRITE'),
            'ETS_TRANS_ENABLE_TRANS_FIELD' => (int)Configuration::get('ETS_TRANS_ENABLE_TRANS_FIELD'),
            'PS_ALLOW_ACCENTED_CHARS_URL' => (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
        ));
        $this->context->controller->addCSS($this->_path . 'views/css/admin_pages.css');
        if($this->isModuleInstalled('ets_megamenu')){
            $this->context->controller->addCSS($this->_path . 'views/css/admin_megamenu.css');
            $this->smarty->assign(array(
                'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                'jsTransMegamenu' => $this->_path . 'views/js/trans_megamenu.js',
            ));
            return $this->display(__FILE__, 'admin_head.tpl');
        }
        if($this->isModuleInstalled('ybc_blog')){
            $this->context->controller->addCSS($this->_path . 'views/css/admin_blog.css');
            $this->smarty->assign(array(
                'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                'jsTransMegamenu' => $this->_path . 'views/js/trans_blog.js',
            ));
            return $this->display(__FILE__, 'admin_head.tpl');
        }
        if($this->isModuleInstalled('ets_productcomments')){
            $this->context->controller->addCSS($this->_path . 'views/css/admin_productcomments.css');
            $this->smarty->assign(array(
                'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                'jsTransMegamenu' => $this->_path . 'views/js/trans_productcomments.js',
                'autoDetectLanguage' => (int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG')
            ));
            return $this->display(__FILE__, 'admin_head.tpl');
        }
        if (!in_array($controller, $this->listControllerAllowed)) {
            if(($controller == 'AdminModules' && Tools::getValue('configure') == $this->name) || $controller == 'AdminEtsTransConfig'){
                $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
                $this->smarty->assign(array(
                    'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                    'linkJsBo' => $this->_path.'views/js/admin.js',
                ));
                return $this->display(__FILE__, 'admin_head.tpl');
            }
            elseif(($request && $request->get('_route') == 'admin_international_translation_overview')
                || (!$request && $controller == 'AdminTranslations' && (Tools::getIsset('locale') || Tools::getValue('lang')))
                    || (!$request && isset($_SERVER['PHP_SELF']) && Tools::strpos($_SERVER['PHP_SELF'],'/international/translations'))
                ){
                $this->context->controller->addCSS($this->_path . 'views/css/admin_inter_trans.css');
                $this->smarty->assign(array(
                    'linkJsInterTrans' => $this->_path.'views/js/admin_inter_trans.js',
                    'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                    'linkJsSimulate' => $this->_path . 'views/js/jquery.simulate.js',
                ));
                return $this->display(__FILE__, 'admin_head.tpl');
            }
            elseif(($request || (!$request && !Tools::getIsset('locale'))) && $controller == 'AdminTranslations'){
                $this->smarty->assign(array(
                    'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
                    'linkJsBo' => $this->_path.'views/js/admin.js',
                ));
                $this->context->controller->addCSS($this->_path . 'views/css/admin_inter_trans.css');
                return $this->display(__FILE__, 'admin_head.tpl');
            }
        }

        $this->smarty->assign(array(
            'linkJsConfig' => $this->_path . 'views/js/admin_defines.js',
            'linkJsPages' => $this->_path . 'views/js/admin_pages.js',
            'linkJsCommon' => $this->_path . 'views/js/admin_common_trans.js',
        ));
        $this->getAssignment();
        return $this->display(__FILE__, 'admin_head.tpl');
    }

    public function getAssignment()
    {
        $controller = Tools::getValue('controller');
        $request = $this->getRequestContainer();
        switch ($controller) {
            case 'AdminProducts':
                $pageType = 'product';
                $pageId = $request ? $request->get('id') : ($this->getIdProductOnUrl() ?: (int)Tools::getValue('id_product'));
                $isDetailPage = ($request && $request->get('id')) || (!$request && ($this->getIdProductOnUrl() || (int)Tools::getValue('id_product')));
                break;
            case 'AdminCategories':
                $pageType = 'category';
                $pageId = $request ? $request->get('categoryId') : (int)Tools::getValue('id_category');
                $isDetailPage = $request ? in_array($request->get('_route'), array('admin_categories_create', 'admin_categories_edit')) : (Tools::getIsset('updatecategory') || Tools::getIsset('addcategory'));
                break;
            case 'AdminCmsContent':

                if ($request) {
                    $cmsRoutes = array('admin_cms_pages_edit', 'admin_cms_pages_create', 'admin_cms_pages_index');
                    if(in_array($request->get('_route'), $cmsRoutes))
                    {
                        $pageType = 'cms';
                        $pageId = $request ? $request->get('cmsPageId') : (int)Tools::getValue('id_cms');
                        $isDetailPage = $request ? in_array($request->get('_route'), array('admin_cms_pages_edit', 'admin_cms_pages_create')) : false;
                    }
                    else {
                        $pageType = 'cms_category';
                        $pageId = $request ? $request->get('cmsCategoryId') : (int)Tools::getValue('id_category');
                        $isDetailPage = $request ? in_array($request->get('_route'), array('admin_cms_pages_category_edit', 'admin_cms_pages_category_create')) : false;
                    }
                }
                else{
                    $pageType = 'cms';
                    $isDetailPage = false;
                    $pageId = 0;
                    if(Tools::getIsset('updatecms') || Tools::getIsset('addcms')){
                        $pageType = 'cms';
                        $pageId = (int)Tools::getValue('id_cms');
                        $isDetailPage = true;
                    }
                    else if(Tools::getIsset('updatecms_category') || Tools::getIsset('addcms_category'))
                    {
                        $pageType = 'cms_category';
                        $pageId = (int)Tools::getValue('id_cms_category');
                        $isDetailPage = true;
                    }
                }
                break;
            case 'AdminManufacturers':
                $pageType = 'manufacturer';
                $pageId = $request ? $request->get('manufacturerId') : (int)Tools::getValue('id_manufacturer');
                $isDetailPage = $request ? in_array($request->get('_route'), array('admin_manufacturers_create', 'admin_manufacturers_edit')) : (Tools::getIsset('addmanufacturer') || Tools::getIsset('updatemanufacturer'));
                break;
            case 'AdminSuppliers':
                $pageType = 'supplier';
                $pageId = $request ? $request->get('supplierId') : (int)Tools::getValue('id_supplier');
                $isDetailPage = $request ? in_array($request->get('_route'), array('admin_suppliers_create', 'admin_suppliers_edit')) : (Tools::getIsset('addsupplier') || Tools::getIsset('updatesupplier'));
                break;
            case 'AdminAttributesGroups':
                $pageType = 'attribute_group';
                $pageId = (int)Tools::getValue('id_attribute_group');
                $isDetailPage = Tools::getIsset('updateattribute_group') || Tools::getIsset('addattribute_group');
                if(Tools::getIsset('viewattribute_group') || Tools::getIsset('updateattribute') || Tools::getIsset('addattribute')){
                    $pageType = 'attribute';
                    $pageId = (int)Tools::getValue('id_attribute');
                    $isDetailPage = Tools::getIsset('updateattribute') || Tools::getIsset('addattribute');
                }
                break;
            case 'AdminFeatures':
                $pageType = 'feature';
                $pageId = (int)Tools::getValue('id_feature');
                $isDetailPage = Tools::getIsset('updatefeature') || Tools::getIsset('addfeature');
                if(Tools::getIsset('viewfeature') || Tools::getIsset('updatefeature_value') || Tools::getIsset('addfeature_value')){
                    $pageType = 'feature_value';
                    $pageId = (int)Tools::getValue('id_feature_value');
                    $isDetailPage = Tools::getIsset('updatefeature_value') || Tools::getIsset('addfeature_value');
                }
                break;
            case 'AdminModules':
                if(Tools::getValue('configure') == 'blockreassurance'){
                    $pageType = 'blockreassurance';
                    $pageId = (int)Tools::getValue('id_reassurance');
                    $isDetailPage = Tools::getIsset('updateblockreassurance') || Tools::getIsset('addblockreassurance');
                }
                elseif(Tools::getValue('configure') == 'ps_mainmenu'){
                    $pageType = 'ps_mainmenu';
                    $pageId = (int)Tools::getValue('id_linksmenutop');
                    $isDetailPage = 1;
                }
                elseif(Tools::getValue('configure') == 'ps_customtext'){
                    $pageType = 'ps_customtext';
                    $pageId = 1;
                    $isDetailPage = 1;
                }
                elseif(Tools::getValue('configure') == 'ps_imageslider'){
                    $pageType = 'ps_imageslider';
                    $pageId = (int)Tools::getValue('id_slide');
                    $isDetailPage = Tools::getIsset('addSlide') || Tools::getValue('id_slide');
                }
                elseif(Tools::getValue('configure') == 'ets_extraproducttabs'){
                    $pageType = 'ets_extraproducttabs';
                    $pageId = 0;
                    $isDetailPage = 1;
                }
                else{
                    $pageType = null;
                    $pageId = null;
                    $isDetailPage = false;
                }
                break;
            case 'AdminLinkWidget':
                $pageType = 'ps_linklist';
                $pageId = $request ? $request->get('linkBlockId') : '';
                $isDetailPage = $request && in_array($request->get('_route'), array('admin_link_block_edit', 'admin_link_block_create')) ? true : false;
                break;
            default:
                $pageType = null;
                $pageId = null;
                $isDetailPage = false;
                break;
        }
        if (!$pageType) {
            return;
        }
        $this->smarty->assign(array(
            'pageType' => $pageType,
            'pageId' => $pageId,
            'isDetailPage' => $isDetailPage
        ));
    }

    public function getIdProductOnUrl()
    {
        if( isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF'] && Tools::strpos($_SERVER['PHP_SELF'],'/product/form')) {
            preg_match('/\/product\/form\/(\d+)/', $_SERVER['PHP_SELF'], $matches);
            if($matches && isset($matches[1])){
                return (int)$matches[1];
            }
        }
        return null;
    }

    public function getDefaultTransConfig()
    {
        if((int)Configuration::get('ETS_TRANS_AUTO_SETTING_ENABLED'))
        {
            return array(
                'lang_source' => Configuration::get('ETS_TRANS_LANG_SOURCE'),
                'lang_target' => Configuration::get('ETS_TRANS_LANG_TARGET'),
                'field_option' => Configuration::get('ETS_TRANS_FILED_TRANS'),
                'wd_data' => Configuration::get('ETS_TRANS_WD_CONFIG'),
            );
        }
        return array(
            'wd_data' => Configuration::get('ETS_TRANS_WD_CONFIG'),
        );
    }

    public function transJs()
    {
        return array(
            'translate' => $this->l('Translate'),
            'bulk_translate' => $this->l('Translate selection'),
            'translate_all' => $this->l('Translate all'),
            'checking' => $this->l('Checking'),
            'stop' => $this->l('Stop'),
            'delete' => $this->l('Delete'),
            'translating' => $this->l('Translating'),
            'target_lang_required' => $this->l('The target language is required'),
            'can_not_trans_item' => $this->l('Cannot translate this item'),
            'reset_all_trans' => $this->l('Reset all translation'),
            'reset_trans' => $this->l('Reset translation'),
            'reset_all_trans_success' => $this->l('Reset translation successfully'),
            'confirm_form_trans_not_save' => $this->l('The new translations are not saved. Do you want to leave this page?'),
            'translate_updated' => $this->l('Translation successfully updated'),
            'product' => $this->l('product'),
            'products' => $this->l('products'),
            'category' => $this->l('category'),
            'categories' => $this->l('categories'),
            'CMS' => $this->l('CMS'),
            'CMSs' => $this->l('CMSs'),
            'CMS_category' => $this->l('CMS category'),
            'CMS_categories' => $this->l('CMS categories'),
            'manufacturer' => $this->l('manufacturer'),
            'manufacturers' => $this->l('manufacturers'),
            'supplier' => $this->l('supplier'),
            'suppliers' => $this->l('suppliers'),
            'item' => $this->l('item'),
            'items' => $this->l('items'),
            'pause_success' => $this->l('Translate paused'),
            'resume' => $this->l('Resume'),
            'close' => $this->l('Close'),
            'pause' => $this->l('Pause'),
            'confirm_clear_all_logs' => $this->l('Are you sure you want to clear all logs?'),
            'confirm_delete_log_item' => $this->l('Do you want to delete this log item?'),
            'sentence' => $this->l('text'),
            'sentences' => $this->l('texts'),
            'not_need_translate' => $this->l('This page does not need to translate'),
            'web_data_required' => $this->l('Data to translate is required'),
            'initializing' => $this->l('Initializing'),
            'no_text_trans' => $this->l('All content has been translated, nothing to do!'),
            'blog_post' => $this->l('Blog post'),
            'blog_posts' => $this->l('Blog posts'),
            'blog_category' => $this->l('Blog category'),
            'blog_categories' => $this->l('Blog categories'),
            'megamenu' => $this->l('Mega menu'),
            'confirm_translate' => $this->l('Are you sure you want to translate?'),
            'translate_fields' => $this->l('Translate fields'),
            'translate_pages' => $this->l('Translate pages'),
            'translate_category_pages' => $this->l('Translate category pages'),
            'file_emails' => $this->l('file emails'),
            'attribute' => $this->l('attribute'),
            'attributes' => $this->l('attributes'),
            'attribute_group' => $this->l('attribute group'),
            'attribute_groups' => $this->l('attribute groups'),
            'feature' => $this->l('feature'),
            'features' => $this->l('features'),
            'feature_value' => $this->l('feature value'),
            'feature_values' => $this->l('feature values'),
        );
    }

    public function getSfContainer()
    {
        if (!class_exists('\PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
            $kernel = null;
            try {
                if (!class_exists('AppKernel')) {
                    return null;
                }
                $kernel = new AppKernel('prod', false);
                $kernel->boot();
                return $kernel->getContainer();
            } catch (Exception $ex) {
                return null;
            }
        }
        return call_user_func(array('\PrestaShop\PrestaShop\Adapter\SymfonyContainer', 'getInstance'));
    }

    public function getRequestContainer()
    {
        if ($sfContainer = $this->getSfContainer()) {
            return $sfContainer->get('request_stack')->getCurrentRequest();
        }
        return null;
    }

    public function getFormTrans($pageId, $pageType, $isTransAll = false, $fieldTrans = null, $resetTrans = 0)
    {
        if((int)$isTransAll){
            $etsConfig = EtsTransConfig::getInstance();
            $selectedTheme = 0;
            $langCodeTarget = Tools::getValue('langCodeTarget') ? Language::getIdByIso(Tools::getValue('langCodeTarget')) : '';
            if($pageType == 'email' || $pageType == 'theme'){
                $selectedTheme = Tools::getValue('selectedTheme');
            }
            else if($pageType == 'module'){
                $selectedTheme = Tools::getValue('moduleName');
            }
            $typeDataResume = $pageType;
            if($pageType == 'blog'){
                $typeDataResume = $pageType.'_'.Tools::getValue('blogType');
            }
            if($etsConfig->hasResumeData($typeDataResume, $selectedTheme, Tools::getValue('sfType'), $langCodeTarget))
            {
                if(!(int)$resetTrans){
                    $resumeData = $etsConfig->getResumeData($typeDataResume, $selectedTheme, Tools::getValue('sfType'), $langCodeTarget);
                    $resumeData['total_translate'] = $pageType == 'email' || $pageType == 'module' ? Tools::getValue('totalItems') : $this->getTotalTranslate($pageType);
                    $resumeData['page_type'] = $pageType;
                    if($pageType == 'blog'){
                        $resumeData['blog_type'] = Tools::getValue('blogType');
                    }
                    $this->smarty->assign($resumeData);
                    return $this->display(__FILE__, 'parts/popup_alert_resume.tpl');
                }
                else{
                    $etsConfig->deletePauseData($typeDataResume, $selectedTheme, Tools::getValue('sfType', ''), $langCodeTarget);
                }
            }
        }
        if($pageType == 'email' && $isTransAll)
        {
            $selectedTheme = Tools::getValue('selectedTheme');
            $emailTrans = EtsTransInternational::getEmailTemplate(null, true);
        }
        $configAutoEnable = Configuration::get('ETS_TRANS_AUTO_SETTING_ENABLED');
        if($configAutoEnable &&($target = Configuration::get('ETS_TRANS_LANG_TARGET'))){
            $langTarget = array();
            $target = explode(',', $target);
            foreach ($target as $item){
                $langTarget[] = Language::getLanguage($item);
            }
        }
        $totalTranslate = 0;
        if($isTransAll){
            $totalTranslate = ($pageType == 'email' || $pageType == 'module') && ($totalEmail = Tools::getValue('totalItems')) ? $totalEmail : $this->getTotalTranslate($pageType);
        }
        else if($pageId && is_array($pageId)){
            $totalTranslate = count($pageId);
        }
        $langCodeTarget = Tools::getValue('langCodeTarget');
        if($langCodeTarget && ($idLangTarget = Language::getIdByIso($langCodeTarget))){
            $langTargetDefault = Language::getLanguage($idLangTarget);
        }
        $this->smarty->assign(array(
            'allLanguages' => $this->getLangWithFlagImage(true),
            'transOptions' => $this->renderTransOptions(),
            'idLangDefault' => (int)Configuration::get('PS_LANG_DEFAULT'),
            'pageId' => $pageId && is_array($pageId) ? implode(',', $pageId) : $pageId,
            'pageType' => $pageType,
            'blogType' => Tools::getValue('blogType'),
            'hasGoogleApiKey' => Configuration::get('ETS_TRANS_GOOGLE_API_KEY') ? true : false,
            'linkConfigApi' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'isTransAll' => $isTransAll,
            'optionMailTrans' => isset($emailTrans) && $emailTrans ? $emailTrans : array(),
            'fieldTrans' => $fieldTrans,
            'totalTranslate' => $totalTranslate,
            'configAutoEnable' => $configAutoEnable,
            'enableAnalysis' => (int)Configuration::get('ETS_TRANS_ENABLE_ANALYSIS'),
            'langSource' => $configAutoEnable ? Language::getLanguage((int)Configuration::get('ETS_TRANS_LANG_SOURCE')) : '',
            'langTarget' => isset($langTarget) ?$langTarget : '',
            'langTargetIds' => $configAutoEnable ? explode(',', Configuration::get('ETS_TRANS_LANG_TARGET')) : array(),
            'fieldTranslate' => $configAutoEnable ? Configuration::get('ETS_TRANS_FILED_TRANS') : '',
            'langSourceDefault' => Language::getLanguage(Language::getIdByIso('en')),
            'langTargetDefault' => isset($langTargetDefault) ? $langTargetDefault : array(),
            'selectedTheme' => Tools::getValue('selectedTheme'),
            'moduleName' => Tools::getValue('moduleName'),
            'sfType' => Tools::getValue('sfType'),
            'imgDir' => _PS_IMG_,
            'autoDetectLang' => $pageType == 'pc' && Tools::getValue('autoDetectLang') ? true : false,
            'isConfigGoogleRate' => Configuration::get('ETS_TRANS_RATE_GOOGLE') !== false && Tools::strlen(Configuration::get('ETS_TRANS_RATE_GOOGLE')) ? true : false,
            'isLocalize' => in_array($pageType, array('theme', 'email', 'module', 'subject')),
            'pcType' => Tools::getValue('pcType'),
            'hideDataToTrans' => Tools::getValue('hideDataToTrans')
        ));

        return ($pageId && is_array($pageId)) || $isTransAll == 1 ?  $this->display(__FILE__, 'parts/popup_trans_all.tpl') : $this->display(__FILE__, 'parts/popup_trans.tpl');
    }

    public function renderTransOptions()
    {
        return array(
            'both' => array(
                'title' => $this->l('Translate all missing translation fields (empty fields and fields which have the same content of source language)'),
                'default' => true
            ),
            'only_empty' => array(
                'title' => $this->l('Translate empty fields only')
            ),
            'same_source' => array(
                'title' => $this->l('Translate fields which have the same content of source language only')
            ),
            'all' => array(
                'title' => $this->l('Translate all fields (replace all old translations)'),
            )
        );
    }

    public function getTotalTranslate($pageType)
    {
        switch ($pageType){
            case 'product':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."product_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'category':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."category_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'cms':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."cms_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'cms_category':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."cms_category_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'manufacturer':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."manufacturer_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'supplier':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."supplier_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'attribute_group':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."attribute_group_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'attribute':
                $total = Db::getInstance()->getValue("
                SELECT COUNT(*) as total FROM `"._DB_PREFIX_."attribute_shop` attrs 
                LEFT JOIN "._DB_PREFIX_."attribute a ON attrs.id_attribute=a.id_attribute 
                WHERE attrs.`id_shop`=".(int)$this->context->shop->id.(($idAttributeGroup = Tools::getValue('idAttributeGroup')) ? " AND a.id_attribute_group=".(int)$idAttributeGroup : ""));
                break;
            case 'feature':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."feature_shop` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'feature_value':
                $total = Db::getInstance()->getValue("
                        SELECT COUNT(*) as total FROM `"._DB_PREFIX_."feature_value` fv 
                         LEFT JOIN "._DB_PREFIX_."feature_shop fs ON fv.id_feature=fs.id_feature WHERE fs.`id_shop`=".(int)$this->context->shop->id.(($idFeature = Tools::getValue('idFeature')) ? " AND fv.id_feature=".(int)$idFeature : ""));
            case 'blockreassurance':
                if(Tools::getValue('isNewBlockreassurance')){
                    $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."psreassurance` WHERE `id_shop`=".(int)$this->context->shop->id);
                }
                else
                    $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."reassurance` WHERE `id_shop`=".(int)$this->context->shop->id);
                break;
            case 'ps_linklist':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."link_block`");
                break;
            case 'ps_mainmenu':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."linksmenutop` WHERE id_shop=".(int)$this->context->shop->id);
                break;
            case 'ps_imageslider':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."homeslider` WHERE id_shop=".(int)$this->context->shop->id);
                break;
            case 'ps_customtext':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."info_shop` WHERE id_shop=".(int)$this->context->shop->id);
                break;
            case 'ets_extraproducttabs':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."ets_ept_tab` WHERE id_shop=".(int)$this->context->shop->id);
                break;
            case 'pc':
                $total = Db::getInstance()->getValue("SELECT COUNT(*) as total FROM `"._DB_PREFIX_."ets_pc_product_comment` epc
                                                            LEFT JOIN "._DB_PREFIX_."product_shop ps ON epc.id_product=ps.id_product AND ps.id_shop=".(int)$this->context->shop->id."
                                                             WHERE 1 ".(($pcType = Tools::getValue('pcType')) ? " AND epc.question=".($pcType == 'question' ? 1 : 0) : ""));
                break;
            case 'email':
                $total = 0;
                break;
            default:
                $total = 0;
                break;
        }

        return $total;
    }

    public function getLangWithFlagImage($active = false)
    {
        $languages = Language::getLanguages($active);
        foreach ($languages as &$lang) {
            $lang['flag'] = _PS_IMG_ . 'l/' . $lang['id_lang'] . '.jpg';
        }
        return $languages;
    }

    public function isModuleInstalled($moduleName)
    {
        $controller = Tools::getValue('controller');
        if($moduleName == 'ets_productcomments'){
            return Module::isInstalled($moduleName)
                && Module::isEnabled($moduleName) &&( $controller == 'AdminEtsPCReviews' || $controller == 'AdminEtsPCQuestions');
        }
        return Module::isInstalled($moduleName)
            && Module::isEnabled($moduleName)
            && $controller == 'AdminModules'
            && Tools::getValue('configure') == $moduleName;
    }

    public function translateDataPage($formData, $pageType, $isDetailPage = false)
    {
        $errors = array();
        if (!$formData || !is_array($formData)) {
            $errors[] = $this->l('The translate data is invalid');
        }

        if (!isset($formData['trans_source']) || !(int)$formData['trans_source']) {
            $errors[] = $this->l('The translate source language is required');
        }

        if (!isset($formData['trans_target']) || !$formData['trans_target']) {
            $errors[] = $this->l('The translate target language is required');
        }

        if (!isset($formData['trans_option']) || !(string)$formData['trans_option']) {
            $errors[] = $this->l('The translate field is required');
        }

        if ($errors) {
            return array(
                'errors' => $errors,
                'data' => array(),
                'message' => $this->l('Form validate fail')
            );
        }
        if(!is_array($formData['trans_target'])){
            $formData['trans_target'] = explode(',', $formData['trans_target']);
        }
        $message = "";
        if(!$this->context->cookie->__get('ets_trans_translate')){
            $this->context->cookie->__set('ets_trans_translate', EtsTransLog::generateIdCode());
        }

        if ($isDetailPage) {
            $savedSuccess = false;
            if($pageType == 'email'){
                $emailData = EtsTransInternational::getEmailSource($formData);
                $resTrans = EtsTransPage::translate((int)$formData['trans_source'], $emailData, $pageType);
                $results = isset($resTrans['result']) ? $resTrans['result'] : array();
                $results = EtsTransInternational::modifyResultTranslated($results, $emailData);
                $textTranslatedLength = EtsTransInternational::getTextLength($emailData['source']);
                $results['nb_translated'] = EtsTransInternational::getTotalEmailTranslate($emailData['target']);
                $results['nb_char_translated'] = $textTranslatedLength;

                $emailSelectedTheme = isset($formData['selected_theme']) ? $formData['selected_theme'] : null;
                if($results && (!isset($results['errors']) || !$results['errors'])){
                    EtsTransInternational::saveEmailTranslated($results, $emailSelectedTheme);
                    $savedSuccess = $results['nb_translated'] ? true : false;
                }
            }
            else if($pageType == 'module' && $formData['trans_all'] == 1 && $formData['module_name']){
                $etsTransModule = new EtsTransModule($formData['module_name']);
                if((int)Tools::getValue('initTranslate')){
                    $etsTransModule->loadModuleFiles();
                    $results = array(
                        'errors' => false,
                        'data' => array(),
                        'after_init' => 1,
                        'done' => 1,
                        'step' => 1
                    );
                }
                else{
                    $etsTransModule->setLangSource($formData['trans_source']);
                    $etsTransModule->setLangTarget($formData['trans_target']);
                    $etsTransModule->setTransOption($formData['trans_option']);
                    $results = $etsTransModule->translateModule();
                    $savedSuccess = true;
                }
            }
            else if($pageType == 'theme' && $formData['trans_all'] == 1){
                $sfType = Tools::getValue('sfType');
                $sfTransType = 'theme';
                switch ($sfType)
                {
                    case 'themes':
                        $sfTransType = 'theme';
                        break;
                    case 'modules':
                        $sfTransType = 'sfmodule';
                        break;
                    case 'back':
                        $sfTransType = 'back';
                        break;
                    case 'others':
                        $sfTransType = 'others';
                        break;
                    case 'mails':
                        $sfTransType = 'mail';
                        break;
                }
                $etsTransTheme = new EtsTransNewSystem($sfTransType);
                $etsTransTheme->setAdminFD(Tools::getValue('adminFD'));
                $etsTransTheme->setSelectedName($formData['selected_theme']);
                $initStep = isset($formData['init_step']) ? (int)$formData['init_step'] : 1;
                if((int)Tools::getValue('initTranslate')){
                    $initDone = $etsTransTheme->loadFileSystem($initStep);
                    $results = array(
                        'errors' => false,
                        'data' => array(),
                        'done' => $initDone === true ? 1 : 0,
                        'step' => $initStep,
                        'after_init' => 1
                    );
                }
                else{
                    $etsTransTheme->setLangSource($formData['trans_source']);
                    $etsTransTheme->setLangTarget($formData['trans_target']);
                    $etsTransTheme->setTransOption($formData['trans_option']);
                    $results = $etsTransTheme->translateData($formData['selected_theme']);
                    if(isset($results['stop_translate']) && $results['stop_translate']){
                        $etsConfig = EtsTransConfig::getInstance();
                        $etsConfig->deletePauseData('theme', $formData['selected_theme'], $sfType, $formData['trans_target']);
                    }
                    $savedSuccess = true;
                }

            }
            else{
                $langSource = $pageType == 'pc' && (int)Configuration::get('ETS_TRANS_AUTO_DETECT_LANG') ? null : (int)$formData['trans_source'];
                if($formData['trans_data']){
                    $formData['trans_data']['lang_source'] = (int)$formData['trans_source'];
                }
                $pageId = isset($formData['page_id']) && $formData['page_id'] ? (int)$formData['page_id'] : 0;

                $resTrans = EtsTransPage::translate($langSource, isset($formData['trans_data']) ? $formData['trans_data'] : array(), $pageType, isset($formData['col_data']) ? $formData['col_data'] : array(), $pageId);
                $results = isset($resTrans['result']) ? $resTrans['result'] : array();
                if(isset($resTrans['dataSaved']) && $resTrans['dataSaved']){
                    $savedSuccess = true;
                }
                if(isset($resTrans['resultText']) && $resTrans['resultText']){
                    if($pageType == 'module' && isset($formData['module_name']) && $formData['module_name'] && isset($formData['file_trans']) && $formData['file_trans']){
                        EtsTransModule::updateTextModule($resTrans['resultText'], $formData['module_name'], $formData['file_trans']);
                        $savedSuccess = true;
                    }
                }
                if($results && (!isset($results['errors']) || !$results['errors']) ){
                    if($pageType == 'megamenu' && isset($formData['col_data']) && isset($formData['menu_type'])){
                        $savedSuccess = EtsTransModule::updateTransMegamenuItem((int)$formData['page_id'], $formData['menu_type'], $formData['col_data'], $results);
                    }
                    elseif($pageType == 'blog' && isset($formData['col_data'])){
                        $savedSuccess = EtsTransModule::updateTransBlogItem((int)$formData['page_id'], $formData['blog_type'], $formData['col_data'], $results);
                    }
                    elseif($pageType == 'pc' && isset($formData['col_data'])){
                        $savedSuccess = EtsTransModule::updateTransPCItem((int)$formData['page_id'], $formData['col_data'], $results);
                    }
                    elseif($pageType == 'theme'){
                        $savedSuccess = true;
                    }
                }
            }

            if ($results && isset($results['errors']) && $results['errors']) {
                $errors[] = isset($results['message']) ? $results['message'] : (is_string($results['errors']) ? $results['errors'] : $this->l('Translate failed'));
            } else{
                if((isset($formData['trans_all']) && $formData['trans_all']) || (isset($results['nb_translated']) && $results['nb_translated']) || ((!isset($formData['trans_all']) || !$formData['trans_all']) && $results)){
                    $message = $savedSuccess ? ($pageType == 'theme' && (!isset($formData['trans_all']) || !$formData['trans_all']) ? $this->l('Translated successfully') : $this->l('Translated successfully, translations saved')) : $this->l('Translated successfully, however translations HAVE NOT been saved. Please manually save the translations');
                }
                else{
                    $message = $this->l('All content has been translated, nothing to do!');
                }
            }
            if($this->context->cookie->__get('ets_trans_translate')){
                $this->context->cookie->__unset('ets_trans_translate');
            }
        }
        else {
            $etsConfig = EtsTransConfig::getInstance();
            $isTransAll = isset($formData['trans_all']) && $formData['trans_all'] == 1;
            switch ($pageType) {
                case 'product':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::transAllProduct($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);

                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('product');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('product', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated products successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] products successfully'));
                    }
                    else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] product successfully'));
                    }
                    break;
                case 'category':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllCategory($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('category');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('category', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated categories successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] categories successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] category successfully'));
                    }
                    break;
                case 'cms':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllCMS($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('cms');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('cms', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated CMS successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] CMSs successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] CMS successfully'));
                    }
                    break;
                case 'cms_category':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllCMSCategory($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('cms_category');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('cms_category', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated CMS categories successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] CMS categories successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] CMS category successfully'));
                    }
                    break;
                case 'manufacturer':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllManufacturer($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('manufacturer');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('manufacturer', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated manufacturers successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] manufacturers successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] manufacturer successfully'));
                    }
                    break;
                case 'supplier':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllSupplier($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('supplier');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('supplier', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated suppliers successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] suppliers successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] supplier successfully'));
                    }
                    break;
                case 'attribute_group':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllAttributeGroup($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('attribute_group');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('attribute_group', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated attribute groups successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] attribute groups successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] attribute group successfully'));
                    }
                    break;
                case 'attribute':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllAttribute($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('attribute');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('attribute', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated attributes successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] attributes successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] attribute successfully'));
                    }
                    break;
                case 'feature':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllFeature($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('feature');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('feature', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated features successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] features successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] feature successfully'));
                    }
                    break;
                case 'feature_value':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllFeatureValue($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('feature_value');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('feature_value', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated feature values successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] feature values successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] feature value successfully'));
                    }
                    break;
                case 'blockreassurance':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllBlockReassurance($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('blockreassurance');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('blockreassurance', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated block reassurance successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurances successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurance successfully'));
                    }
                    break;
                case 'ps_linklist':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllLinkList($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('ps_linklist');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('ps_linklist', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated link widget successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurances successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurance successfully'));
                    }
                    break;
                case 'ps_mainmenu':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllMainMenu($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('ps_mainmenu');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('ps_mainmenu', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated menu items successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurances successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] block reassurance successfully'));
                    }
                    break;
                case 'ps_imageslider':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllImageSliders($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('ps_imageslider');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('ps_imageslider', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated sliders successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] sliders successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] slider successfully'));
                    }
                    break;
                case 'ets_extraproducttabs':
                    if($isTransAll)
                    {
                        $offset = isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0;
                        $results = EtsTransPage::translateAllExtraProductTabs($formData['trans_source'], $formData['trans_target'], $formData['trans_option'], $offset);
                        if($results['stop_translate']){
                            $etsConfig->deletePauseData('ets_extraproducttabs');
                        }
                    }
                    else {
                        $results = EtsTransPage::translatePage('ets_extraproducttabs', $formData);
                    }
                    $nbTranslated = isset($results['nb_translated']) ? $results['nb_translated'] : 0;
                    if(!$nbTranslated){
                        $message = $this->l('Translated tabs successfully');
                    }
                    else if ($nbTranslated > 1) {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] tabs successfully'));
                    } else {
                        $message = str_replace('[number]', $nbTranslated, $this->l('Translated [number] tab successfully'));
                    }
                    break;
                default:
                    $results = array();
                    break;
            }

            if ($results && isset($results['errors']) && $results['errors']) {
                if($this->context->cookie->__get('ets_trans_translate')){
                    $this->context->cookie->__unset('ets_trans_translate');
                }
                if($isTransAll){
                    $etsConfig->updatePauseData(array(
                        'pageType' => $pageType,
                        'nbTranslated' => isset($formData['nb_translated']) ? (int)$formData['nb_translated'] : 0,
                        'nbCharTranslated' => isset($formData['nb_char_translated']) ? (int)$formData['nb_char_translated'] : 0,
                        'langSource' => isset($formData['trans_source']) ? $formData['trans_source'] : 0,
                        'langTarget' => isset($formData['trans_target']) ? $formData['trans_target'] : 0,
                        'fieldOption' => isset($formData['trans_option']) ? $formData['trans_option'] : '',
                    ));
                }
                $errors[] = isset($results['message']) ? $results['message'] : $this->l('Translate fail');
            }
            if(isset($results['stop_translate']) && $results['stop_translate']){
                if($this->context->cookie->__get('ets_trans_translate')){
                    $this->context->cookie->__unset('ets_trans_translate');
                }
            }
        }
        $noTrans = false;
        if($isDetailPage && (!isset($formData['trans_all']) ||$formData['trans_all'] != 1)){
            if(!isset($results) || !$results){
                $message = $this->l('All content has been translated, nothing to do!');
                $noTrans = true;
            }
        }
        else if((!isset($formData['trans_all']) ||$formData['trans_all'] != 1) && !isset($results['translated_length'])){
            $message = $this->l('All content has been translated, nothing to do!');
            $noTrans = true;
        }
        if(!Configuration::get('ETS_TRANS_GOOGLE_API_KEY')){
            $message = $this->l('No Google translate API key found.');
            $errors = [$message];
        }

        return array(
            'errors' => $errors,
            'data' => $results,
            'noTrans' => $noTrans ? 1 : 0,
            'message' => $errors ? '' : $message // Message has a short code : [number]
        );
    }

    public function actionAjax()
    {
        if(Tools::isSubmit('etsTransGetFormTranslate')){
            $form = $this->getFormTrans(
                Tools::getValue('pageId'),
                Tools::getValue('pageType'),
                Tools::getValue('isTransAll'),
                Tools::getValue('fieldTrans'),
                Tools::getValue('resetTrans')
            );
            if($form){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'form' => $form,
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'form' => '',
            )));
        }

        if(Tools::isSubmit('etsTransGetFormInterTrans'))
        {
            $form = $this->getFormTrans(null,
                Tools::getValue('pageType'),
                (int)Tools::getValue('isTransAll'),
                Tools::getValue('fieldTrans'),
                Tools::getValue('resetTrans')
            );
            if($form){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'form' => $form,
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'form' => '',
            )));
        }

        if(Tools::isSubmit('etsTransCheckApiKey'))
        {
            if($apiKey = Tools::getValue('apiKey')){
                $api = new EtsTransApi();
                if($api->validateApiKey($apiKey))
                {
                    die(Tools::jsonEncode(array(
                        'success' => true,
                        'message' => $this->l('The translate API key is good')
                    )));
                }
                die(Tools::jsonEncode(array(
                    'success' => false,
                    'message' => $this->l('The translate API key is invalid')
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('The translate API key is required')
            )));
        }

        if(Tools::isSubmit('etsTransClearLogItem')){
            $idLog = (int)Tools::getValue('idLog');
            if($idLog){
                $log = new EtsTransLog($idLog);
                if($log && $log->id){
                    $log->delete();
                    die(Tools::jsonEncode(array(
                        'success' => true,
                        'message' => $this->l('Deleted log item successfully')
                    )));
                }
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('Failed to delete log item')
            )));
        }

        if(Tools::isSubmit('etsTransClearAllLogs')){
            if(EtsTransLog::clearAllLog()){
                die(Tools::jsonEncode(array(
                    'success' => true,
                    'message' => $this->l('Clear log successfully')
                )));
            }
            die(Tools::jsonEncode(array(
                'success' => false,
                'message' => $this->l('Clear log failed')
            )));
        }
        if(Tools::isSubmit('etsTransGetFormConfigTransAll')){
            if($transWd = Tools::getValue('transWd')){
                if(is_array($transWd)){
                    $transWd = implode(',', $transWd);
                }
                Configuration::updateValue('ETS_TRANS_WD_CONFIG', $transWd);
            }
            else{
                Configuration::updateValue('ETS_TRANS_WD_CONFIG', '');
            }
            $formHtml = $this->getFormConfigTransAll((int)Tools::getValue('interTrans'), (int)Tools::getValue('resetTrans'));
            die(Tools::jsonEncode(array(
                'success' => true,
                'form' => $formHtml
            )));
        }

        if(Tools::isSubmit('etsTransGetFormAnalysis')){
            die(Tools::jsonEncode(array(
                'success' => true,
                'form_html' => $this->display(__FILE__, 'parts/popup_analysis.tpl'),
            )));
        }
        if(Tools::isSubmit('etsTransGetFormAnalysisCompleted')){
            die(Tools::jsonEncode(array(
                'success' => true,
                'form_html' => $this->display(__FILE__, 'parts/popup_analysis_completed.tpl'),
            )));
        }
        if(Tools::isSubmit('etsTransGetFormTranslatingAll')){
            die(Tools::jsonEncode(array(
                'success' => true,
                'form_html' => $this->display(__FILE__, 'parts/popup_translating.tpl'),
            )));
        }
    }

    public function saveDataAfterPause($transInfo)
    {
        $etsConfig = EtsTransConfig::getInstance();
        return $etsConfig->updatePauseData($transInfo);
    }

    public function deleteDataPause($pageType, $selectedTheme = 0)
    {
        $etsConfig = EtsTransConfig::getInstance();
        return $etsConfig->deletePauseData($pageType, $selectedTheme);
    }

    public function analyzeBeforeTranslate($pageType, $formData, $offset)
    {
        if($pageType == 'megamenu'){
            $result = EtsTransModule::analysisModuleMegamenu($formData, $offset);
        }
        else if($pageType == 'blog'){
            $result = EtsTransModule::analysisModuleBlog($formData);
        }
        else if($pageType == 'pc'){
            $result = EtsTransModule::analysisModulePc($formData);
        }
        else
            $result = EtsTransPage::analysisTranslate($pageType, $formData, $offset);
        if($result){
            $result['total_item'] = $this->getTotalTranslate($pageType);
        }
        return $result;
    }

    public function analyzeBeforeTranslateLz($pageType, $formData, $step, $selected, $sfType, $isLoadFile, $resetData)
    {
        $transSource = isset($formData['trans_source']) ? $formData['trans_source'] : '';
        $transTarget = isset($formData['trans_target']) ? $formData['trans_target'] : array();

        $errors = array();
        if(!$transSource){
            $errors[] =  $this->l('The source language is required');
        }
        else if(!$transTarget){
            $errors[] =  $this->l('The target language is required');
        }
        else if(in_array($transSource, $transTarget)){
            $errors = $this->l('The target language cannot contain the source language');
        }
        if($errors){
            return array(
                'errors' => $errors
            );
        }
        $result = EtsTransInternational::analysisBeforeTranslate($pageType, $formData, $step, $selected, $sfType, $isLoadFile, $resetData);
        if($result){
            $result['total_item'] = $this->getTotalTranslate($pageType);
        }
        return $result;
    }

    public function getFormConfigTransAll($isInterTrans = false, $resetTrans = false)
    {
        $ec = EtsTransConfig::getInstance();
        $pageType = $isInterTrans ? 'inter' : 'all';
        if($ec->hasResumeData($pageType)){
            if($resetTrans){
                $ec->deletePauseData($pageType);
            }
            else{
                $resumeData = $ec->getResumeData($pageType);
                $resumeData['total_translate'] = '';
                $resumeData['page_type'] = $pageType;
                if(!$resumeData['nb_path'])
                    $resumeData['nb_path'] = EtsTransAll::getTotalItemTransAll(null, $pageType);
                $this->smarty->assign($resumeData);
                return $this->display(__FILE__, 'parts/popup_alert_resume.tpl');
            }

        }
        $configAutoEnable = Configuration::get('ETS_TRANS_AUTO_SETTING_ENABLED');
        if($configAutoEnable &&($target = Configuration::get('ETS_TRANS_LANG_TARGET'))){
            $langTarget = array();
            $target = explode(',', $target);
            foreach ($target as $item){
                $langTarget[] = Language::getLanguage($item);
            }
        }
        $this->smarty->assign(array(
            'allLanguages' => $this->getLangWithFlagImage(true),
            'transOptions' => $this->renderTransOptions(),
            'idLangDefault' => (int)Configuration::get('PS_LANG_DEFAULT'),
            'pageType' => $pageType,
            'fieldTrans' => '',
            'isTransAll' => 1,
            'configAutoEnable' => $configAutoEnable,
            'hasGoogleApiKey' => Configuration::get('ETS_TRANS_GOOGLE_API_KEY') ? true : false,
            'totalTranslate' => Configuration::get('ETS_TRANS_WD_CONFIG') ? 1 : 0,
            'langSource' => Language::getLanguage((int)Configuration::get('ETS_TRANS_LANG_SOURCE')),
            'langTarget' => isset($langTarget) ? $langTarget : array(),
            'linkConfigApi' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name,
            'enableAnalysis' => (int)Configuration::get('ETS_TRANS_ENABLE_ANALYSIS'),
            'fieldTranslate' => Configuration::get('ETS_TRANS_FILED_TRANS'),
            'pageId' => '',
            'imgDir' => _PS_IMG_,
            'ETS_TRANS_WD_CONFIG' => array(),
            'isInterTrans' => $isInterTrans,
            'treeWebTranslations' => $isInterTrans ? null : EtsTransDefine::getInstance()->treeWebPageSelection(),
            'treeWebPageOption' => $isInterTrans ? $this->getConfigInterTrans() : array(),
            'langTargetIds' => $configAutoEnable ? explode(',', Configuration::get('ETS_TRANS_LANG_TARGET')) : array(),
            'wdConfig' => Configuration::get('ETS_TRANS_WD_CONFIG'),
            'isLocalize' => in_array($pageType, array('theme', 'email', 'module', 'subject'))
        ));
        return $this->display(__FILE__, 'parts/popup_trans_all.tpl');
    }

    public function getConfigInterTrans()
    {
        $treeData = EtsTransDefine::getInstance()->treeWebPageSelection();
        return array($treeData['inter']);
    }

    public function loadFileTranslateAll($formData, $pageType = 'all')
    {
        $errors = array();
        if(!isset($formData['trans_wd']) || $formData['trans_wd']){
            $errors[] = $this->l('The web data is required');
        }
        if(!isset($formData['trans_source']) || !$formData['trans_source']){
            $errors[] = $this->l('The source language is required');
        }
        if(!isset($formData['trans_target']) || !$formData['trans_target']){
            $errors[] = $this->l('The target language is required');
        }
        if(!isset($formData['trans_option']) || !$formData['trans_option']){
            $errors[] = $this->l('The field options is required');
        }
        if(!$errors){
            return array(
                'errors' => $errors,
            );
        }
        $etsTransAll = new EtsTransAll($pageType);
        if($result = $etsTransAll->loadFileTranslateAll($formData, $pageType)){
            return array(
                'errors' => false,
                'total_item' => isset($result['total_item']) ? $result['total_item'] : 0
            );
        }

    }

    public function translateAllWebData($formData, $pageType)
    {
        $errors = array();
        if(!isset($formData['trans_wd']) || $formData['trans_wd']){
             $errors[] = $this->l('The web data is required');
        }
        if(!isset($formData['trans_source']) || !$formData['trans_source']){
             $errors[] = $this->l('The source language is required');
        }
        if(!isset($formData['trans_target']) || !$formData['trans_target']){
             $errors[] = $this->l('The target language is required');
        }
        if(!isset($formData['trans_option']) || !$formData['trans_option']){
             $errors[] = $this->l('The field options is required');
        }
        if(!$errors){
            return array(
                'errors' => $errors,
            );
        }
        $etsTransAll = new EtsTransAll($pageType);
        if($result = $etsTransAll->translateAllWebData($formData)){
            if(isset($result['stop_translate']) && $result['stop_translate']){
                $result['message'] = $this->l('Translated successfully');
            }
            return $result;
        }
        return array(
            'errors' => $this->l('Cannot load files to translate'),
        );
    }

    public function analyzingAllPage($pageType, $formData, $offset, $isInit= false)
    {
        if($isInit){
            $eta = new EtsTransAll($pageType);
            return $eta->indexDataTranslate($formData['trans_wd']);
        }
        else{
            $eta = new EtsTransAll($pageType);
            return $eta->analysisBeforeTranslate($formData, $offset);
        }
    }

    public function translateAllMegamenu($formData)
    {
        $result =  EtsTransModule::transAllMegamenu($formData);
        if(isset($result['stop']) && $result['stop']){
            $result['message'] = $this->l('Translate successfully');
        }
        return $result;
    }

    public function translateAllBlog($formData)
    {
        $result =  EtsTransModule::translateAllBlog($formData);
        if(isset($result['stop']) && $result['stop']){
            $result['message'] = $this->l('Translate successfully');
        }
        return $result;
    }

    public function translateAllModulePc($formData)
    {
        $result =  EtsTransModule::translateAllModulePc($formData);
        if(isset($result['stop']) && $result['stop']){
            $result['message'] = $this->l('Translate successfully');
        }
        return $result;
    }
}