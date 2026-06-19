<?php
/**
* 2007-2023 weblir
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    weblir <contact@weblir.com>
*  @copyright 2007-2023 weblir
*  @license   weblir.com
*/
class AdminChatGPTPROCronController extends ModuleAdminController
{
    public $available_fields;
    public $name = 'chatgptpro';

    public function __construct()
    {
        $this->bootstrap = true;
        $this->meta_title = 'Cron Job Settings';
        $this->module_name = 'chatgptpro';
        $this->mod = 'WEBLIR_' . strtoupper($this->module_name);
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function renderView()
    {
        $return = $this->displayCronInfo();
        $return .= $this->generateForm();
        $return .= $this->generateCategoryForm();
        $return .= $this->displayParameterInfo();

        return $return;
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $ver = explode('.', $version);
        return $ver[1];
    }

    private function displayUsageSummary($date)
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $usage_data = $this->getChatUsage($date);
        $this->context->smarty->assign([
            'path' => _PS_BASE_URL_SSL_.  __PS_BASE_URI__ . 'modules/chatgptpro/',
            'shop' => $shop,
            'usage_data' => $usage_data,
            'current_lang' => $this->context->language->id,
            'lang_list' => Language::getLanguages(true),
        ]);
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/usage.tpl');
    }

    public function postProcess()
    {
        if (Tools::getIsset("submitCronUpdate")) {
            $form_values = $this->getConfigFieldsValues();

            foreach (array_keys($form_values) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }

            if (is_array(Tools::getValue('categories'))) {
                Configuration::updateValue('categories', implode(",", Tools::getValue('categories')));
            } else {
                Configuration::updateValue('categories', "");
            }
        }

        if (Tools::getIsset("submitCatCronUpdate")) {
            $form_values = $this->getCategoryConfigFieldsValues();

            foreach (array_keys($form_values) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }

    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();
        $this->addjQueryPlugin('tagify', null, false);
        $this->addjQueryPlugin('validate', null, false);
    }


    private function generateForm()
    {
        $inputs_cron = array();

        $languages = Language::getLanguages();

        $periods = array(
            array('id_option' => '1', 'name' => $this->l('Every 1 Minute')),
            array('id_option' => '2', 'name' => $this->l('Every 2 Minutes')),
            array('id_option' => '3', 'name' => $this->l('Every 5 Minutes')),
            array('id_option' => '4', 'name' => $this->l('Every 10 Minutes')),
            array('id_option' => '5', 'name' => $this->l('Every 30 Minutes')),
            array('id_option' => '6', 'name' => $this->l('Every Hour')),
            array('id_option' => '7', 'name' => $this->l('Every 2 Hours')),
            array('id_option' => '8', 'name' => $this->l('Every 6 Hours'))
        );

        $toUpdate = array(
            array('id_option' => '2', 'name' => $this->l('2 products')),
            array('id_option' => '3', 'name' => $this->l('3 products')),
            array('id_option' => '5', 'name' => $this->l('5 products')),
            array('id_option' => '8', 'name' => $this->l('8 products')),
            array('id_option' => '10', 'name' => $this->l('10 products')),
            array('id_option' => '15', 'name' => $this->l('15 products')),
            array('id_option' => '20', 'name' => $this->l('20 products')),
            array('id_option' => '50', 'name' => $this->l('50 products')),
            array('id_option' => '100', 'name' => $this->l('100 products')),
            array('id_option' => '200', 'name' => $this->l('200 products')),
            array('id_option' => '300', 'name' => $this->l('300 products')),
            array('id_option' => '500', 'name' => $this->l('500 products')),
            array('id_option' => '1000', 'name' => $this->l('1000 products')),
        );

        $inputs_cron[] = array(
            'type' => 'switch',
            'label' => $this->l('Enable Cron Feature'),
            'name' => $this->mod . '_ENABLE_CRON',
            'class' => 'enable_cron',
            'id' => 'enable_cron',
            'desc' => $this->l('Automatically create content for products using the Cron Job feature'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                    ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                    )
                )
        );

        $inputs_cron[] = array(
            'type' => 'textbutton',
            'label' => 'Cron Job Secret Token',
            'name' => $this->mod . '_SECRET',
            'button' => [
                'label' => 'Generate Token',
                'attributes' => [
                    'onclick' => 'generateTokenAndFill(9);',
                ],
            ],
        );

        $inputs_cron[] = [
            'type' => 'radio',
            'label' => $this->l('Update Field'),
            'desc' => $this->l('Select the field you wish to update'),
            'name' => 'updatedField',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "name",
                    'label' => $this->l('name - Update product name field'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "description",
                    'label' => $this->l('description - Update product description field'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "description_short",
                    'label' => $this->l('description_short - Update product description_short field'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "meta_title",
                    'label' => $this->l('meta_title - Update product meta_title field'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "meta_description",
                    'label' => $this->l('meta_description - Update product meta_description field'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "tags",
                    'label' => $this->l('tags - Update product tags field'),
                ],
            ],
        ];

        $inputs_cron[] = [
            'type' => 'radio',
            'label' => $this->l('Selected Products'),
            'desc' => $this->l('Target products you wish to be updated on each cron job execution'),
            'name' => 'productTarget',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "pendingOpenAI",
                    'label' => $this->l('pendingOpenAI - Update products that have not been edited so far using this module'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "emptyField",
                    'label' => $this->l('emptyField - Update only products that have the updatedField empty'),
                ],
            ],
        ];


        $active_cats = Configuration::get('categories');
        if (!$active_cats) {
            $active_cats = array();
        } else {
            $active_cats = explode(',', $active_cats);
        }

        $inputs_cron[] = array(
            'type' => 'categories',
            'label' => $this->l('Product Categories'),
            'desc' => $this->l('Target products by categories.') . "<br>" . $this->l('Leave unselected to disable.'),
            'required' => false,
            'name' => 'categories',
            'class' => 'categories-list',
            'tree' => array(
                'root_category' => $this->context->shop->getCategory(),
                'id' => 'id_category',
                'use_checkbox' => true,
                'use_search' => true,
                'selected_categories' => $active_cats
                )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Language'),
            'desc' => $this->l('Select the language you wish to update'),
            'name' => 'languageId',
            'options' => array(
                'query' => $languages,
                'id' => 'id_lang',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Product to update'),
            'desc' => $this->l('Set the number of products that you wish to update'),
            'name' => 'productsToUpdate',
            'options' => array(
                'query' => $toUpdate,
                'id' => 'id_option',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Prompt Template'),
            'desc' => $this->l('Select the language you wish to update'),
            'name' => 'promptTemplateId',
            'options' => array(
                'query' => $this->getPromptList(1, 500),
                'id' => 'id_prompt',
                'name' => 'title'
            )
        );

        $inputs_cron[] = array(
            'type' => 'switch',
            'label' => $this->l('Enable Debug'),
            'name' => 'debug',
            'class' => 'debug',
            'id' => 'debug',
            'desc' => $this->l('If debug is enabled products you will see detailed informations if you open the Cron link in the browser. Products will not be updated if Debug is enabled!'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                    ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                    )
                )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('CRON JOB interval'),
            'desc' => $this->l('Set the cron run interval'),
            'name' => $this->mod . '_CRON_INTERVAL',
            'class' => 'interval_select',
            'options' => array(
                'query' => $periods,
                'id' => 'id_option',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'html',
            'label' => $this->l('Product Cron Job command'),
            'name' => '',
            'desc' => $this->l('To update the products with OpenAI content, the CRON JOB needs to be
                executed on your server with the following command: ').
                '<br /><span class="cron-command"><strong><em><span class="cron-target"></span> '.
                    Context::getContext()->link->getModuleLink(
                        $this->name,
                        'cron',
                        array()
                    ).
                    '<span class="cron-parameters"></span></em></strong></span><hr>'.
                    $this->l('This is just a Cron Job Command Generation Form, you can create unlimited CRON Job commands.')
        );

        $fields_form_cron = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('CRON JOB Settings - Generate product content using OpenAI'),
                    'icon' => 'icon-clock-o'
                    ),
                'input' => $inputs_cron,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitCronUpdate'
                ),
            )
        );


        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $lang->id;
        // $helper->submit_action = 'submitUpdate';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPROCron');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(
            array($fields_form_cron)
        );
    }





















    private function generateCategoryForm()
    {
        $inputs_cron = array();

        $languages = Language::getLanguages();

        $periods = array(
            array('id_option' => '1', 'name' => $this->l('Every 1 Minute')),
            array('id_option' => '2', 'name' => $this->l('Every 2 Minutes')),
            array('id_option' => '3', 'name' => $this->l('Every 5 Minutes')),
            array('id_option' => '4', 'name' => $this->l('Every 10 Minutes')),
            array('id_option' => '5', 'name' => $this->l('Every 30 Minutes')),
            array('id_option' => '6', 'name' => $this->l('Every Hour')),
            array('id_option' => '7', 'name' => $this->l('Every 2 Hours')),
            array('id_option' => '8', 'name' => $this->l('Every 6 Hours'))
        );

        $toUpdate = array(
            array('id_option' => '2', 'name' => $this->l('2 categories')),
            array('id_option' => '3', 'name' => $this->l('3 categories')),
            array('id_option' => '5', 'name' => $this->l('5 categories')),
            array('id_option' => '8', 'name' => $this->l('8 categories')),
            array('id_option' => '10', 'name' => $this->l('10 categories')),
            array('id_option' => '15', 'name' => $this->l('15 categories')),
            array('id_option' => '20', 'name' => $this->l('20 categories')),
            array('id_option' => '50', 'name' => $this->l('50 categories')),
            array('id_option' => '100', 'name' => $this->l('100 categories')),
            array('id_option' => '200', 'name' => $this->l('200 categories')),
            array('id_option' => '300', 'name' => $this->l('300 categories')),
            array('id_option' => '500', 'name' => $this->l('500 categories')),
            array('id_option' => '1000', 'name' => $this->l('1000 categories')),
        );

        $inputs_cron[] = array(
            'type' => 'textbutton',
            'label' => 'Cron Job Secret Token',
            'name' => $this->mod . '_CAT_SECRET',
            'button' => [
                'label' => 'Generate Token',
                'attributes' => [
                    'onclick' => 'generateCategoryTokenAndFill(9);',
                ],
            ],
        );

        $inputs_cron[] = [
            'type' => 'radio',
            'label' => $this->l('Update Field'),
            'desc' => $this->l('Select the field you wish to update'),
            'name' => 'catupdatedField',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "name",
                    'label' => $this->l('name - Update category name field'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "description",
                    'label' => $this->l('description - Update category description field'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "meta_title",
                    'label' => $this->l('meta_title - Update category meta_title field'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "meta_description",
                    'label' => $this->l('meta_description - Update category meta_description field'),
                ],
            ],
        ];

        $inputs_cron[] = [
            'type' => 'radio',
            'label' => $this->l('Selected categories'),
            'desc' => $this->l('Target categories you wish to be updated on each cron job execution'),
            'name' => 'categoryTarget',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "pendingOpenAI",
                    'label' => $this->l('pendingOpenAI - Update categories that have not been edited so far using this module'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "emptyField",
                    'label' => $this->l('emptyField - Update only categories that have the updatedField empty'),
                ],
            ],
        ];

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Language'),
            'desc' => $this->l('Select the language you wish to update'),
            'name' => 'catlanguageId',
            'options' => array(
                'query' => $languages,
                'id' => 'id_lang',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Categories to update'),
            'desc' => $this->l('Set the number of categories that you wish to update'),
            'name' => 'categoriesToUpdate',
            'options' => array(
                'query' => $toUpdate,
                'id' => 'id_option',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('Prompt Template'),
            'desc' => $this->l('Select the language you wish to update'),
            'name' => 'catpromptTemplateId',
            'options' => array(
                'query' => $this->getPromptList(1, 500),
                'id' => 'id_prompt',
                'name' => 'title'
            )
        );

        $inputs_cron[] = array(
            'type' => 'switch',
            'label' => $this->l('Enable Debug'),
            'name' => 'catdebug',
            'class' => 'catdebug',
            'id' => 'catdebug',
            'desc' => $this->l('If debug is enabled categories you will see detailed informations if you open the Cron link in the browser. Categories will not be updated if Debug is enabled!'),
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Yes')
                    ),
                array(
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('No')
                    )
                )
        );

        $inputs_cron[] = array(
            'type' => 'select',
            'label' => $this->l('CRON JOB interval'),
            'desc' => $this->l('Set the cron run interval'),
            'name' => $this->mod . '_CAT_CRON_INTERVAL',
            'class' => 'cat_interval_select',
            'options' => array(
                'query' => $periods,
                'id' => 'id_option',
                'name' => 'name'
            )
        );

        $inputs_cron[] = array(
            'type' => 'html',
            'label' => $this->l('Category Cron Job command'),
            'name' => '',
            'desc' => $this->l('To update the categories with OpenAI content, the CRON JOB needs to be
                executed on your server with the following command: ').
                '<br /><span class="cat-cron-command"><strong><em><span class="cat-cron-target"></span> '.
                    Context::getContext()->link->getModuleLink(
                        $this->name,
                        'cron',
                        array()
                    ).
                    '<span class="cat-cron-parameters"></span></em></strong></span><hr>'.
                    $this->l('This is just a Cron Job Command Generation Form, you can create unlimited CRON Job commands.')
        );



        $fields_form_cron = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('CRON JOB Settings - Generate category content using OpenAI'),
                    'icon' => 'icon-clock-o'
                    ),
                'input' => $inputs_cron,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitCatCronUpdate'
                ),
            )
        );


        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->default_form_language = $lang->id;
        // $helper->submit_action = 'submitUpdate';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPROCron');
        $helper->tpl_vars = array(
            'fields_value' => $this->getCategoryConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(
            array($fields_form_cron)
        );
    }













    public function getLogHistory($page = 1, $fields_list = 50)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page-1)*$fields_list;
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->module_name.'_log
            ORDER BY id_log DESC
            LIMIT '.$offset.', '.$fields_list;
        return Db::getInstance()->ExecuteS($sql);
    }

    public function countLogHistory()
    {
        $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.$this->module_name.'_log';
        return Db::getInstance()->getValue($sql);
    }

    public function getConfigFieldsValues()
    {
        $arr = [
            $this->mod . '_ENABLE_CRON' => Configuration::get($this->mod . '_ENABLE_CRON'),
            $this->mod . '_SECRET' => Configuration::get($this->mod . '_SECRET'),
            $this->mod . '_CRON_INTERVAL' => Configuration::get($this->mod . '_CRON_INTERVAL'),
            'updatedField' => Configuration::get('updatedField'),
            'productTarget' => Configuration::get('productTarget'),
            'productsToUpdate' => Configuration::get('productsToUpdate'),
            'promptTemplateId' => Configuration::get('promptTemplateId'),
            'languageId' => Configuration::get('languageId'),
            'debug' => Configuration::get('debug'),
            'categories' => Configuration::get('categories'),
        ];
        return $arr;
    }

    public function getCategoryConfigFieldsValues()
    {
        $arr = [
            $this->mod . '_CAT_SECRET' => Configuration::get($this->mod . '_CAT_SECRET'),
            $this->mod . '_CAT_CRON_INTERVAL' => Configuration::get($this->mod . '_CAT_CRON_INTERVAL'),
            'catupdatedField' => Configuration::get('catupdatedField'),
            'catpromptTemplateId' => Configuration::get('catpromptTemplateId'),
            'catlanguageId' => Configuration::get('catlanguageId'),
            'catdebug' => Configuration::get('catdebug'),
            'categoryTarget' => Configuration::get('categoryTarget'),
            'categoriesToUpdate' => Configuration::get('categoriesToUpdate'),
        ];
        return $arr;
    }

    private function displayCronInfo()
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $module_controller = $this->context->link->getModuleLink(
            "chatgptpro",
            "cron",
            [
                'secret_token' => Configuration::get($this->mod . '_TOKEN'),
            ]
        );

        $this->context->smarty->assign([
            'path' => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/chatgptpro/',
            'shop' => $shop,
            'formvalues' => $this->getConfigFieldsValues(),
            'catformvalues' => $this->getCategoryConfigFieldsValues(),
            'module_controller' => $module_controller,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/cron-info.tpl');
    }

    private function displayParameterInfo()
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $module_controller = $this->context->link->getModuleLink(
            "chatgptpro",
            "cron",
            [
                'secret_token' => Configuration::get($this->mod . '_TOKEN'),
            ]
        );

        $this->context->smarty->assign([
            'path' => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/chatgptpro/',
            'shop' => $shop,
            'formvalues' => $this->getConfigFieldsValues(),
            'catformvalues' => $this->getCategoryConfigFieldsValues(),
            'module_controller' => $module_controller,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/cron.tpl');
    }

    public function getPromptList($page = 1, $fields_list = 50)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page-1)*$fields_list;
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_prompt
            ORDER BY id_prompt DESC
            LIMIT '.$offset.', '.$fields_list;

        return Db::getInstance()->ExecuteS($sql);
    }

}
