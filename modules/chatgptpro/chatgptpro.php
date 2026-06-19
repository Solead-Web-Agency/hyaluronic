<?php
/**
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class ChatGPTPro extends Module
{
    private $html = '';

    public function __construct()
    {
        $this->name = 'chatgptpro';
        $this->tab = 'front_office_features';
        $this->version = '1.3.5';
        $this->author = 'Weblir';
        $this->need_instance = 0;
        $this->mod = 'WEBLIR_' . strtoupper($this->name);
        $this->module_key = '79185762c0c1df819ee19be66c5fabf3';

        $this->bootstrap = true;

        $this->displayName = $this->l('OpenAI ChatGPT Integration PRO - AI Content generation');
        $this->description = $this->l('Easily generate content for products, categories and other type of text on your shop.');

        parent::__construct();

        $this->options = [
          [
            'id_option' => 1,
            'name' => 'Option 1',
          ], [
            'id_option' => 2,
            'name' => 'Option 2',
          ],
        ];

        $this->dash_display = true;

        $this->frontControllerPage = 'ajax';

        $this->strings_top = [
            $this->l('Do you need a skilled PrestaShop developer?'),
            $this->l('Click'),
            $this->l('here'),
            $this->l('and get the best one!'),
            $this->l('You are using the latest version of the module! :)'),
        ];

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        Configuration::updateValue('PS_USE_HTMLPURIFIER', 0);
        Configuration::updateValue($this->mod . '_LIVE_MODE', '0');
        Configuration::updateValue($this->mod . '_DEBUG_MODE', '0');
        Configuration::updateValue($this->mod . '_TOPCTA', '1');
        Configuration::updateValue($this->mod . '_PRODUCT', '1');
        Configuration::updateValue($this->mod . '_PRODUCT_LOG', '1');
        Configuration::updateValue($this->mod . '_TOKEN', $this->randomString()); // for AJAX requests
        Configuration::updateValue($this->mod . '_SECRET', $this->randomString()); // for Cron Jobs
        Configuration::updateValue($this->mod . '_API_KEY', ' ');
        Configuration::updateValue($this->mod . '_MODEL', 'gpt-3.5-turbo');
        Configuration::updateValue($this->mod . '_TEMPERATURE', '0.7');
        Configuration::updateValue($this->mod . '_MAX_TOKENS', '3200');
        Configuration::updateValue($this->mod . '_TOP_P', '1');
        Configuration::updateValue($this->mod . '_FREQ_PEN', '0');
        Configuration::updateValue($this->mod . '_PRES_PEN', '0');

        Configuration::updateValue($this->mod . '_ENABLE_CRON', 0);
        Configuration::updateValue($this->mod . '_CRON_INTERVAL', 6);
        Configuration::updateValue('updatedField', 'description');
        Configuration::updateValue('productTarget', 'pendingOpenAI');
        Configuration::updateValue('productsToUpdate', 5);
        Configuration::updateValue('promptTemplateId', '');
        Configuration::updateValue('languageId', (int) Configuration::get('PS_LANG_DEFAULT'));
        Configuration::updateValue('interval_select', '');
        Configuration::updateValue('debug', 0);

        $this->installMainController('AdminChatGPTPROMain', $this->l('OpenAI Content Creator'));
        $this->installController('AdminChatGPTPRO', 'Mass Content Creator');
        $this->installController('AdminChatGPTPROSettings', 'Module Settings');
        $this->installController('AdminChatGPTPROCron', 'Cron Job Settings');
        $this->installController('AdminChatGPTPROUsage', 'Module Log and Usage');
        $this->installController('AdminChatGPTPROChangelog', 'Module Changelog');

        $this->installMyTables();

        $data = [];

        //  PRODUCT TITLE
        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product name 1"),
            'prompt' => $this->l("Generate better short product name based on the existing product name: {product_name}.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product name 2"),
            'prompt' => $this->l("Think like an ecommerce merchandising specialist and write a product title for {product_name} from {product_brand}, a {product_default_category} product.") ." \n\n",
        );


        //  PRODUCT DESCRIPTION
        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 1"),
            'prompt' => $this->l("Generate creative HTML code description for this product: {product_name} following the next template:") . " \n\n " . $this->l("<H2>Product detailed description:</H2>") . " \n\n " . $this->l("<H2>Product features:</H2>") . "\n\n " . $this->l("<H2>Product recommandations:</H2>") . " \n\n " . $this->l("Within the description also add the following keywords written with html bold characters: {product_tags}.") . " \n\n " . $this->l("Also make sure you also include some spelling mistakes into the text, but not in the structure of the keywords, to create a more human-like text."),
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 2"),
            'prompt' => $this->l("Generate creative description for this product:") . " {product_name} " . $this->l("following the next template:") . " \n\n " . $this->l("Product detailed description:") . "\n\n " . $this->l("Product features:") . " \n\n " . $this->l("Product recommandations:") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 3"),
            'prompt' => $this->l("Please ignore all previous instructions. Please respond only in the English language. You are an E-commerce SEO expert copywriter who writes product descriptions that compel users to purchase the products. Do not self reference. Do not explain what you are doing. In this task, you will craft a compelling product description for an e-commerce item that I will provide. Your goal is to create three unique content sections for the product description, each focusing on a different set of relevant keywords. Be sure to label each section with an eye-catching subheading that accurately summarizes its content. Your product description should be keyword-rich, informative, and engaging, with a word count of under 1000 words. Your objective is to use emotional language and creative reasoning to persuade potential buyers to purchase the product. Once you have written the product description, please create a bulleted list of 5 possible H1 headings for the product page. Provide a bulleted list of 10 broad match keywords that you used to create the product description. To further enhance the product page marketing appeal, create a persuasive and professional sounding meta title and description that incorporates similar language to that of the new product summary. This is the e-commerce product - {product_name}") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 4"),
            'prompt' => $this->l("Write a captivating product description for our {product_name}.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 5"),
            'prompt' => $this->l("Create a product description for my e-commerce website. The product is {product_name}. Include the following keywords in the description: {product_tags}.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 6"),
            'prompt' => $this->l("Think like an ecommerce merchandising specialist and write a product description for {product_name} from {product_brand} brand, a {product_default_category} product.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product description 7 - using product images"),
            'prompt' => $this->l("Think like an ecommerce merchandising specialist and write a product description for {product_name} from {product_brand} brand, a {product_default_category} product. Embed the following image links within the contend of the new description using HTML: {product_images} . The images must be rendered using the folowing HTML image tag: <img src=\"url_of_the_image\" alt=\"{product_name}\">") ." \n\n",
        );


        //  META TITLE
        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta title 1"),
            'prompt' => pSQL($this->l("Please ignore all previous instructions. Please respond only in the English language. You are an E-commerce SEO expert copywriter. Do not self reference. Do not explain what you are doing. Generate compelling meta title for a product that I will specify. The meta title should not be more than 60 characters long. The meta title should have the product keywords in it. The meta title should use persuasive language that speaks to the audience\'s emotions and desires and makes them want to purchase the product. Here is the product name: {product_name}") ." \n\n"),
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta title 2"),
            'prompt' => $this->l("Think like an ecommerce SEO expert and write a meta title for {product_name}.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta title 3"),
            'prompt' => $this->l("Think like an ecommerce SEO expert and write a meta tile for the product {product_name}, from {product_brand} brand.") ." \n\n",
        );


        //  META DESCRIPTION
        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta description 1"),
            'prompt' => pSQL($this->l("Please ignore all previous instructions. Please respond only in the English language. You are an E-commerce SEO expert copywriter. Do not self reference. Do not explain what you are doing. Generate compelling meta description for a product that I will specify. The meta descriptions should not be longer than 160 characters long. The meta description should have the product keywords in it. The meta description should use persuasive language that speaks to the audience\'s emotions and desires and makes them want to purchase the product. Here is the product name: {product_name}") ." \n\n"),
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta description 2"),
            'prompt' => $this->l("Write a compelling meta description for our bestselling smartwatch product called {product_name}.") ." \n\n",
        );

        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product meta description 3"),
            'prompt' => $this->l("Write a compelling meta description for our bestselling smartwatch product called {product_name}.") ." \n\n",
        );

        //  META DESCRIPTION
        $data[] = array(
            'status' => 1,
            'title' => $this->l("Product tags"),
            'prompt' => pSQL($this->l("Please ignore all previous instructions. Please respond only in the English language. You are an E-commerce SEO expert copywriter who writes product tags that best represents the product. Do not self reference. Do not explain what you are doing. In this task, you will craft some product tags for an e-commerce item that I will provide. Generate 5 product keyword tags based on the product name: {product_name}. Write the tags as a list, on a single line, separated by comma.") ." \n\n"),
        );

        foreach ($data as $key => $prompt) {
            $insert = Db::getInstance()->insert(
                'chatgptpro_prompt',
                $prompt
            );
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnBottom') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayBackOfficeTop') &&
            $this->registerHook('displayFooter') &&
            $this->registerHook('hookdashboardZoneTwo') &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
        $this->uninstallController('AdminChatGPTPRO');
        $this->uninstallController('AdminChatGPTPROSettings');
        $this->uninstallController('AdminChatGPTPROCron');
        $this->uninstallController('AdminChatGPTPROUsage');
        $this->uninstallController('AdminChatGPTPROChangelog');
        $this->removeTable();

        Configuration::deleteByName($this->mod . '_LIVE_MODE');
        Configuration::deleteByName($this->mod . '_DEBUG_MODE');
        Configuration::deleteByName($this->mod . '_TOPCTA');
        Configuration::deleteByName($this->mod . '_PRODUCT');
        Configuration::deleteByName($this->mod . '_PRODUCT_LOG');
        Configuration::deleteByName($this->mod . '_API_KEY');
        Configuration::deleteByName($this->mod . '_MODEL');
        Configuration::deleteByName($this->mod . '_TEMPERATURE');
        Configuration::deleteByName($this->mod . '_MAX_TOKENS');
        Configuration::deleteByName($this->mod . '_TOP_P');
        Configuration::deleteByName($this->mod . '_FREQ_PEN');
        Configuration::deleteByName($this->mod . '_PRES_PEN');
        Configuration::deleteByName($this->mod . '_ENABLE_CRON');
        Configuration::deleteByName($this->mod . '_CRON_INTERVAL');
        Configuration::deleteByName('updatedField');
        Configuration::deleteByName('productTarget');
        Configuration::deleteByName('productsToUpdate');
        Configuration::deleteByName('promptTemplateId');
        Configuration::deleteByName('languageId');
        Configuration::deleteByName('interval_select');
        Configuration::deleteByName('debug');

        return parent::uninstall();
    }

    private function removeTable()
    {
        if (!Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_prompt`') ||
            !Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.$this->name.'_log`')
        ) {
            return false;
        }
        return true;
    }

    private function installMyTables()
    {
        $prompts = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name .'_prompt` (
                `id_prompt` INT(12) NOT NULL AUTO_INCREMENT,
                `status` INT(2) NOT NULL DEFAULT 0,
                `title` VARCHAR(128) NOT NULL,
                `prompt` TEXT,
                `date_generated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY ( `id_prompt` )
                ) ENGINE = ' ._MYSQL_ENGINE_;

        $log = '
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.$this->name .'_log` (
                `id_log` INT(12) NOT NULL AUTO_INCREMENT,
                `id_product` INT(12) NULL,
                `id_category` INT(12) NULL,
                `id_cms` INT(12) NULL,
                `id_lang` INT(12) NULL,
                `id_employee` INT(12) NULL,
                `type` VARCHAR(128) NULL,
                `method` VARCHAR(128) NULL,
                `prompt` TEXT,
                `reply` TEXT NULL,
                `old_name` VARCHAR(255),
                `new_name` VARCHAR(255),
                `old_description` TEXT,
                `new_description` TEXT,
                `old_description_short` TEXT,
                `new_description_short` TEXT,
                `old_meta_title` VARCHAR(255),
                `new_meta_title` VARCHAR(255),
                `old_meta_description` VARCHAR(512),
                `new_meta_description` VARCHAR(512),
                `old_tags` VARCHAR(256),
                `new_tags` VARCHAR(256),
                `date_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY ( `id_log` )
                ) ENGINE = ' ._MYSQL_ENGINE_;

        if (!Db::getInstance()->Execute($prompts) ||
            !Db::getInstance()->Execute($log)
        ) {
            return false;
        }
        return true;
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $ver = explode('.', $version);

        return $ver[1];
    }

    private function installMainController($controllerName, $name)
    {
        if ($this->psversion() == '6') {
            $tab_admin_order_id = 0;
        } elseif ($this->psversion() == '7') {
            $tab_admin_order_id = Tab::getIdFromClassName('AdminTools') ? Tab::getIdFromClassName('AdminTools') :
                Tab::getIdFromClassName('AdminAdvancedParameters');
        } else {
            $tab_admin_order_id = Tab::getIdFromClassName('AdminTools') ? Tab::getIdFromClassName('AdminTools') :
                Tab::getIdFromClassName('AdminAdvancedParameters');
        }

        $tab = new Tab();
        $tab->class_name = $controllerName;
        $tab->id_parent = $tab_admin_order_id;
        $tab->module = $this->name;
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->icon = "icon-openai openai-icon";
        $tab->save();
    }

    private function installController($controllerName, $name)
    {
        $tab_admin_order_id = Tab::getIdFromClassName('AdminChatGPTPROMain');
        $tab = new Tab();
        $tab->class_name = $controllerName;
        $tab->id_parent = $tab_admin_order_id;
        $tab->module = $this->name;
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        $tab->save();
    }

    public function uninstallController($controllerName)
    {
        $tab_controller_main_id = TabCore::getIdFromClassName($controllerName);
        $tab_controller_main = new Tab($tab_controller_main_id);
        $tab_controller_main->delete();
    }

    public function randomString($length = 7)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = Tools::strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function displayCustomTop()
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $ref = $this->author;
        $module_version = $this->version;

        $all_models = [];
        if (Tools::strlen(Configuration::get($this->mod . '_API_KEY')) > 0) {
            $models = $this->getChatGPTModels();
            if (isset($models['data']) && is_array($models['data'])) {
                $all_models = $models['data'];
            }
        }

        $this->context->smarty->assign([
            'path' => $this->_path,
            'shop' => $shop,
            'ref' => $ref,
            'moduleversion' => $module_version,
            'AdminChatGPTPRO_link' => $this->context->link->getAdminLink('AdminChatGPTPRO', false).
                '&token='.Tools::getAdminTokenLite('AdminChatGPTPRO'),
            'AdminChatGPTPROUsage_link' => $this->context->link->getAdminLink('AdminChatGPTPROUsage', false).
                '&token='.Tools::getAdminTokenLite('AdminChatGPTPROUsage'),
            'AdminChatGPTPROChangelog_link' => $this->context->link->getAdminLink('AdminChatGPTPROChangelog', false).
                '&token='.Tools::getAdminTokenLite('AdminChatGPTPROChangelog'),
            'AdminChatGPTPROCron_link' => $this->context->link->getAdminLink('AdminChatGPTPROCron', false).
                '&token='.Tools::getAdminTokenLite('AdminChatGPTPROCron'),
            'chatgpt_models' => array_reverse($all_models),
            'modulename' => $this->name,
            'moduletitle' => $this->displayName,
            'strings_top' => $this->strings_top,
        ]);

        $this->html .= $this->display(__FILE__, 'top.tpl');
    }

    private function displaySimulationForm()
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $module_version = $this->version;

        $module_controller = $this->context->link->getModuleLink(
            $this->name,
            $this->frontControllerPage,
            [
                'secret_token' => Configuration::get($this->mod . '_TOKEN'),
            ]
        );

        $this->context->smarty->assign([
            'path' => $this->_path,
            'shop' => $shop,
            'module_controller' => $module_controller,
            'moduleversion' => $module_version,
            'modulename' => $this->name,
            'moduletitle' => $this->displayName,
            'strings_top' => $this->strings_top,
        ]);

        $this->html .= $this->display(__FILE__, 'playground.tpl');
    }

    public function getContent()
    {
        $this->postProcess();

        if (Tools::getIsset('newTemplate')) {
            $this->renderPromptTemplateForm();
        } else if (Tools::getIsset('updatechatgptpro_prompt')) {
            $this->renderPromptTemplateForm(true);
        } else {
            $this->displayCustomTop();

            $this->renderForm();

            $this->generatePromptList();

            $this->displaySimulationForm();
        }

        return $this->html;
    }

    public function renderPromptTemplateForm($edit = false)
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int) $this->context->shop->id;
        $categories = [];
        $lang_options = [];
        $submit_name = "submitTemplate";

        if ($edit == true) {
            $inputs[] = array(
                'type' => 'hidden',
                'label' => $this->l('Template ID'),
                'name' => 'id_prompt',
                'class' => '',
                'lang' => false
            );
            $submit_name = "editTemplate";
        }

        $inputs[] = array(
            'type' => 'switch',
            'label' => $this->l('Status'),
            'name' => $this->mod . '_P_STATUS',
            'is_bool' => true,
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled'),
                ],
                [
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled'),
                ],
            ],
        );

        $inputs[] = array(
            'type' => 'text',
            'label' => $this->l('Prompt title'),
            'name' => $this->mod . '_T_TITLE',
            'lang' => false,
            'required' => true,
            'autoload_rte' => false
        );

        $inputs[] = array(
            'type' => 'textarea',
            'label' => $this->l('Prompt content'),
            'name' => $this->mod . '_TEMPLATE',
            'lang' => false,
            'required' => true,
            'autoload_rte' => false
        );

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Generate Prompt Template'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Save Prompt'),
                    'id' => $submit_name,
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminModules', false) .
                            '&configure=' . $this->name . '&module_name=' . $this->name .
                            '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Go Back to Module Settings'),
                        'name' => 'goBack',
                        'icon' => 'process-icon-cogs',
                    ],
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = [];
        $helper->identifier = $this->identifier;
        $helper->submit_action = $submit_name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name .'&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getPromptConfigFieldsValues($edit),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];


        $this->html .= $helper->generateForm([$fields_form]);
    }

    private function generatePromptList()
    {
        if (Tools::getIsset('page') && !Tools::getIsset('submitBulkdeletechatgptpro_prompt')) {
            $page = (int)Tools::getValue('page');
            if (Tools::getIsset('submitFilter' . $this->name . '_prompt')) {
                $page = (int)Tools::getValue('submitFilter' . $this->module_name . '_prompt');
            }
        } else {
            $page = 1;
        }

        if (Tools::getIsset('selected_pagination') && !Tools::getIsset('submitBulkdeletechatgptpro_prompt')) {
            $selected_pagination = Tools::getValue('selected_pagination');
        } else {
            $selected_pagination = 50;
        }

        $content = $this->getPromptList($page, $selected_pagination);

        $fields_list = array(
            'id_prompt' => array(
                'title' => 'ID',
                'align' => 'center',
                'search' => false,
                'class' => 'fixed-width-xs'
            ),
            'title' => array(
                'title' => $this->l('Title'),
                'search' => false,
            ),
            'prompt' => array(
                'title' => $this->l('Prompt'),
                'search' => false,
            ),
            'status' => array(
                'title' => $this->l('Status'),
                'search' => false,
                'class' => 'fixed-width-xs',
                'active' => 'status',
                'remove_onclick' => true
            ),
            'date_generated' => array(
                'title' => $this->l('Timestamp'),
                'search' => false,
                'class' => 'fixed-width-xs'
            )
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->actions = array('edit', 'delete');
        $helper->module = $this;
        $helper->toolbar_btn = array(
            'new' => array(
                'desc' => $this->l('Create new template'),
                'href' => $this->context->link->getAdminLink('AdminModules', false) .
                    '&configure=' . $this->name . '&module_name=' . $this->name . '&newTemplate' .
                    '&token=' . Tools::getAdminTokenLite('AdminModules'),
            )
        );
        $helper->listTotal = $this->getPromptTotal();
        $helper->identifier = 'id_prompt';
        $helper->title = $this->l('Prompt Templates');
        $helper->table = $this->name.'_prompt';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->name .'&module_name=' . $this->name;
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete'),
                'confirm' => $this->l('Are you sure you want to delete the selected prompts?'),
                'icon' => 'icon-trash',
            ),
        );

        $this->html .= $helper->generateList($content, $fields_list);
    }

    public function getPromptList($page = 1, $fields_list = 50, $active = false)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page-1)*$fields_list;
        }

        $where = '';
        if ($active) {
            $where = 'WHERE status = 1';
        }

        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_prompt
            ' . $where . '
            ORDER BY id_prompt DESC
            LIMIT '.$offset.', '.$fields_list;

        return Db::getInstance()->ExecuteS($sql);
    }

    public function getPromptTotal()
    {
        $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.$this->name.'_prompt';

        return Db::getInstance()->getValue($sql);
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        $this->html .= $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm()
    {
        $root = Category::getRootCategory();
        $tree = new HelperTreeCategories('categories_col1');
        $tree->setUseCheckBox(true)
             ->setAttribute('is_category_filter', $root->id)
             ->setRootCategory($root->id)
             ->setUseSearch(true)
             ->setSelectedCategories(explode(',', Configuration::get($this->mod . '_CATEGORIES')))
             ->setInputName($this->mod . '_CATEGORIES');
        $categoryTreeCol = $tree->render();

        $tree = new HelperTreeCategories('categories_col2');
        $tree->setUseCheckBox(false)
             ->setAttribute('is_category_filter', $root->id)
             ->setRootCategory($root->id)
             ->setUseSearch(true)
             ->setSelectedCategories([(int) Configuration::get($this->mod . '_CATEGORY')])
             ->setInputName($this->mod . '_CATEGORY');
        $categoryTreeCol2 = $tree->render();

        $button = null;
        $button_type = 'text';

        $chatgpt = $this->getChatGPTModels();

        if (Tools::strlen(Configuration::get($this->mod . '_API_KEY')) > 0 && isset($chatgpt['data']) && is_array($chatgpt['data']) && count($chatgpt['data']) > 0) {
            $button_type = 'textbutton';
            $button = [
                'label' => 'See all models',
                'attributes' => [
                    'onclick' => 'showAllModels();',
                ],
            ];
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => $this->mod . '_LIVE_MODE',
                        'is_bool' => true,
                        'hint' => $this->l('Use this module in live mode'),
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Debug mode'),
                        'name' => $this->mod . '_DEBUG_MODE',
                        'is_bool' => true,
                        'hint' => $this->l('Use this option to enable/disable Debug mode'),
                        'desc' => $this->l('Use this option to enable/disable Debug mode'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Admin Top Bar CTA'),
                        'name' => $this->mod . '_TOPCTA',
                        'is_bool' => true,
                        'desc' => $this->l('Display a CTA Button on admin top bar for fast access to ChatGPT content generator.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Product edit page Form'),
                        'name' => $this->mod . '_PRODUCT',
                        'is_bool' => true,
                        'desc' => $this->l('Display a ChatGPT content generator form on the product page edit page, underneath the description fields.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Product Edit History'),
                        'name' => $this->mod . '_PRODUCT_LOG',
                        'is_bool' => true,
                        'desc' => $this->l('Display a history log with all edits on the product edit page. You can use this to restore past product data like title, descriptions and metas.'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 5,
                        'type' => 'password',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'label' => $this->l('OpenAI API KEY'),
                        'desc' => $this->l('Enter your API KEY in order to use the module.')
                            . ' ' . $this->l('Here you can get your API KEY') .
                            ': <a href="https://platform.openai.com/account/api-keys" target="_blank">https://platform.openai.com/account/api-keys</a>',
                        'name' => $this->mod . '_API_KEY',
                        'required' => true,
                    ],
                    [
                        'type' => $button_type,
                        'label' => $this->l('Model'),
                        'name' => $this->mod . '_MODEL',
                        'desc' => $this->l('The OpenAI API is powered by a family of models with different capabilities and price points.')
                        . ' ' . $this->l('You can also customize our base models for your specific use case with fine-tuning.'),
                        'required' => true,
                        'button' => $button,
                    ],
                    [
                        'type' => 'text',
                        'min' => 1,
                        'max' => 4000,
                        'step' => 1,
                        'label' => $this->l('Max Tokens'),
                        'name' => $this->mod . '_MAX_TOKENS',
                        'value' => Configuration::get($this->mod . '_MAX_TOKENS'),
                        'desc' => $this->l('OpenAI models understand and process text by breaking it down into tokens. Tokens can be words or just chunks of characters. For example, the word “hamburger” gets broken up into the tokens “ham”, “bur” and “ger”, while a short and common word like “pear” is a single token. Many tokens start with a whitespace, for example “ hello” and “ bye”.') . ' ' . $this->l('Default value:') . ' 256',
                    ],
                    [
                        'type' => 'text',
                        'name' => $this->mod . '_TEMPERATURE',
                        'label' => $this->l('Temperature'),
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                        'value' => Configuration::get($this->mod . '_TEMPERATURE'),
                        'desc' => $this->l('Temperature is a value between 0 and 1 that essentially lets you control how confident the model should be when making these predictions. Lowering temperature means it will take fewer risks, and completions will be more accurate and deterministic. Increasing temperature will result in more diverse completions.') . ' ' . $this->l('Default value:') . ' 0.7',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                        'label' => $this->l('Top P'),
                        'hint' => $this->l('Enter a valid OpenAI API Prompt or leave it blank'),
                        'name' => $this->mod . '_TOP_P',
                        'value' => Configuration::get($this->mod . '_TOP_P'),
                        'desc' => $this->l('An alternative to sampling with temperature, called nucleus sampling, where the model considers the results of the tokens with top_p probability mass. So 0.1 means only the tokens comprising the top 10% probability mass are considered. We generally recommend altering this or temperature but not both.') . ' ' . $this->l('Default value:') . ' 1',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'min' => -2,
                        'max' => 2,
                        'step' => 0.1,
                        'value' => Configuration::get($this->mod . '_FREQ_PEN'),
                        'label' => $this->l('Frequency Penalty Value'),
                        'hint' => $this->l('The frequency and presence penalties found in the Completions API can be used to reduce the likelihood of sampling repetitive sequences of tokens. They work by directly modifying the logits (un-normalized log-probabilities) with an additive contribution.'),
                        'name' => $this->mod . '_FREQ_PEN',
                        'desc' => $this->l('Number between -2.0 and 2.0. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the model\'s likelihood to repeat the same line verbatim.') . ' ' . $this->l('Default value:') . ' 0',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'min' => -2,
                        'max' => 2,
                        'step' => 0.1,
                        'value' => Configuration::get($this->mod . '_PRES_PEN'),
                        'label' => $this->l('Presence Penalty Value'),
                        'hint' => $this->l('The frequency and presence penalties found in the Completions API can be used to reduce the likelihood of sampling repetitive sequences of tokens. They work by directly modifying the logits (un-normalized log-probabilities) with an additive contribution.'),
                        'name' => $this->mod . '_PRES_PEN',
                        'desc' => $this->l('Number between -2.0 and 2.0. Positive values penalize new tokens based on whether they appear in the text so far, increasing the model\'s likelihood to talk about new topics.') . ' ' . $this->l('Default value:') . ' 0',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                    'name' => 'submitOptions',
                ],
            ],
        ];
    }

    protected function getConfigFormValues()
    {
        $variables = [
            $this->mod . '_LIVE_MODE' => Configuration::get($this->mod . '_LIVE_MODE'),
            $this->mod . '_DEBUG_MODE' => Configuration::get($this->mod . '_DEBUG_MODE'),
            $this->mod . '_TOPCTA' => Configuration::get($this->mod . '_TOPCTA'),
            $this->mod . '_PRODUCT' => Configuration::get($this->mod . '_PRODUCT'),
            $this->mod . '_PRODUCT_LOG' => Configuration::get($this->mod . '_PRODUCT_LOG'),
            $this->mod . '_API_KEY' => Configuration::get($this->mod . '_API_KEY'),
            $this->mod . '_MODEL' => Configuration::get($this->mod . '_MODEL'),
            $this->mod . '_TEMPERATURE' => Configuration::get($this->mod . '_TEMPERATURE'),
            $this->mod . '_MAX_TOKENS' => Configuration::get($this->mod . '_MAX_TOKENS'),
            $this->mod . '_TOP_P' => Configuration::get($this->mod . '_TOP_P'),
            $this->mod . '_FREQ_PEN' => Configuration::get($this->mod . '_FREQ_PEN'),
            $this->mod . '_PRES_PEN' => Configuration::get($this->mod . '_PRES_PEN'),
        ];

        foreach ($this->options as $key => $item) {
            $variables[$this->mod . '_CHECK_' . $item['id_option']] = Configuration::get($this->mod . '_CHECK_' . $item['id_option']);
        }

        return $variables;
    }

    protected function getPromptConfigFieldsValues($edit)
    {
        if ($edit) {
            $input_values = $this->getTemplateByID((int)Tools::getValue('id_prompt'));
            $variables = [
                $this->mod . '_TEMPLATE' => $input_values['prompt'],
                $this->mod . '_T_TITLE' => $input_values['title'],
                $this->mod . '_P_STATUS' => $input_values['status'],
                'id_prompt' => (int)Tools::getValue('id_prompt'),
            ];
        } else {
            $variables = [
                $this->mod . '_TEMPLATE' => '',
                $this->mod . '_T_TITLE' => '',
                $this->mod . '_P_STATUS' => '',
            ];
        }

        return $variables;
    }

    public function getTemplateByID($id)
    {
        $query = 'SELECT * FROM `'._DB_PREFIX_.$this->name .'_prompt` WHERE id_prompt = '. (int)$id;
        return Db::getInstance()->getRow($query);
    }

    public function getTemplateStatus($id)
    {
        $query = 'SELECT `status` FROM `'._DB_PREFIX_.$this->name .'_prompt` WHERE id_prompt = '. (int)$id;
        return Db::getInstance()->getValue($query);
    }

    public function deleteTemplate($id)
    {
        $delete_template = Db::getInstance()->delete(
            $this->name.'_prompt',
            'id_prompt = '.(int)$id
        );

        if (!$delete_template
        ) {
            return false;
        }
            
        return true;
    }

    private function postProcess()
    {
        if (((bool) Tools::isSubmit('submitTemplate')) == true) {
            $data = array(
                'id_prompt' => (int)Tools::getValue('id_prompt'),
                'status' => pSQL(Tools::getValue($this->mod . '_P_STATUS')),
                'title' => pSQL(Tools::getValue($this->mod . '_T_TITLE')),
                'prompt' => addslashes(str_replace('"', "'", Tools::getValue($this->mod . '_TEMPLATE'))),
            );

            $insert = Db::getInstance()->insert(
                pSQL($this->name).'_prompt',
                $data
            );

            if (!$insert) {
                $this->displayError(
                    'Failed to insert new prompt template! Please fill the form with the right informations!'
                );
            } else {
                $this->html .= $this->displayConfirmation($this->l('Prompt template successfully added!'));
            }
        }

        if (Tools::isSubmit('status'.$this->name.'_prompt')) {
            //start delete entry
            $id = Tools::getValue('id_prompt');

            $status = $this->getTemplateStatus($id);
            $new_status = false;
            if ($status == 1) {
                $new_status = 0;
            } else {
                $new_status = 1;
            }
            Db::getInstance()->update($this->name .'_prompt', array('status' => $new_status), 'id_prompt='.$id);
        }

        if (Tools::isSubmit('delete'.$this->name.'_prompt')) {
            //start delete entry
            $id = Tools::getValue('id_prompt');

            if ($this->deleteTemplate($id)) {
                $this->html .= $this->displayConfirmation($this->l('Prompt template removed.'));
            } else {
                $this->_errors[] =
                    $this->l('Unable to delete prompt template with id:').' '.(int)$id;
            }
            //end delete entry
        }

        if (((bool) Tools::isSubmit('editTemplate')) == true) {
            $data = array(
                'status' => pSQL(Tools::getValue($this->mod . '_P_STATUS')),
                'title' => pSQL(Tools::getValue($this->mod . '_T_TITLE')),
                'prompt' => addslashes(str_replace('"', "'", Tools::getValue($this->mod . '_TEMPLATE'))),
            );

            if (!Db::getInstance()->update(
                $this->name."_prompt",
                $data,
                'id_prompt = '. (int)Tools::getValue('id_prompt')
            )
            ) {
                $this->_errors[] = $this->l('Failed to edit prompt template! Please fill the form with the right informations!');
            } else {
                $this->html .= $this->displayConfirmation($this->l('Prompt template successfully updated!'));
            }
        }

        if (((bool) Tools::isSubmit('submitOptions')) == true) {
            if (Tools::strlen(Tools::getValue($this->mod . '_API_KEY')) < 10 && Tools::strlen(Configuration::get($this->mod . '_API_KEY')) < 1) {
                $this->_errors[] = $this->l('OpenAI API KEY field is invalid!');
            }

            if (Tools::strlen(Tools::getValue($this->mod . '_MODEL')) < 3) {
                $this->_errors[] = $this->l('Model field is invalid!');
            }

            if ($this->_errors) {
                $this->html .= $this->displayError(implode('<br />', $this->_errors));
            } else {
                $form_values = $this->getConfigFormValues();
                foreach (array_keys($form_values) as $key) {
                    if ($key == $this->mod . '_API_KEY') {
                        if (Tools::strlen(Tools::getValue($key))>0) {
                            Configuration::updateValue($key, Tools::getValue($key));
                        }
                    } else {
                        Configuration::updateValue($key, Tools::getValue($key));
                    }
                }

                $this->html .= $this->displayConfirmation($this->l('Settings Updated'));
            }
        }

        if (Tools::getIsset('submitBulkdeletechatgptpro_prompt')) {
            $templates = Tools::getValue('chatgptpro_promptBox');
            $errors = [];
            $success = [];

            foreach ($templates as $key => $template) {
                if ($this->deleteTemplate($template)) {
                    $success[] = (int)$template;
                } else {
                    $errors[] = (int)$template;
                }
            }

            if (count($errors)>0) {
                $this->_errors[] = $this->l('Failed to delete the following prompt templates:').' '. implode(", ", $errors);
            }

            if (count($success)>0) {
                $this->html .= $this->displayConfirmation($this->l('Th following prompt templates have been successfully deleted:').' '. implode(", ", $success));
            }
        }
    }

    public function hookdisplayAdminProductsMainStepLeftColumnMiddle($params)
    {
        if (Configuration::get($this->mod . '_PRODUCT') == 1) {
            $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
            $module_version = $this->version;

            $languages = Language::getLanguages(true);
            $lang_summary = [];

            foreach ($languages as $key => $lang) {
                $lang_summary[$lang['iso_code']] = $lang['id_lang'];
            }

            Media::addJsDef(['lang_summary' => $lang_summary]);

            $module_controller = $this->context->link->getModuleLink(
                $this->name,
                $this->frontControllerPage,
                [
                    'secret_token' => Configuration::get($this->mod . '_TOKEN'),
                ]
            );

            $models = $this->getChatGPTModels();

            $prompts = $this->getPromptList(1, 50, true);

            $product_data = new Product($params['id_product'], false, $this->context->language->id);

            $this->context->smarty->assign([
                'path' => $this->_path,
                'mod_controller' => $this->context->link->getAdminLink('AdminModules', false)
                    . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' .
                    Tools::getAdminTokenLite('AdminModules'),
                'shop' => $shop,
                'idproduct' => (int)$params['id_product'],
                'prompts' => $prompts,
                'languages_list' => $languages,
                'lang_summary' => $lang_summary,
                'prod_name' => $product_data->name,
                'module_controller' => $module_controller,
                'moduleversion' => $module_version,
                'chatgpt_models' => array_reverse($models['data']),
                'modulename' => $this->name,
                'moduletitle' => $this->displayName,
                'strings_top' => $this->strings_top,
            ]);

            return $this->display(__FILE__, 'product-content-generator.tpl');
        }
    }

    public function hookdisplayAdminProductsMainStepLeftColumnBottom($params)
    {
        if (Configuration::get($this->mod . '_PRODUCT_LOG') == 1) {
            $product_log = $this->getProductLogHistory($params['id_product'], 1, 5000);
            $languages = Language::getLanguages(true);

            $languages = Language::getLanguages(true);
            $languages_parsed = [];

            foreach ($languages as $key => $lang) {
                $languages_parsed[$lang['id_lang']] = $lang['name'];
            }

            $module_controller = $this->context->link->getModuleLink(
                "chatgptpro",
                "ajax",
                [
                    'secret_token' => Configuration::get($this->mod . '_TOKEN'),
                ]
            );

            $this->context->smarty->assign([
                'path' => $this->_path,
                'shopurl' => Tools::getHttpHost(true) . __PS_BASE_URI__,
                'mod_controller' => $this->context->link->getAdminLink('AdminModules', false)
                    . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' .
                    Tools::getAdminTokenLite('AdminModules'),
                'product_log' => $product_log,
                'languages_list' => $languages,
                'languages_parsed' => $languages_parsed,
                'id_language_selected' => $this->context->language->id,
                'module_controller' => $module_controller,
                'current_product_id' => (int)$params['id_product'],
            ]);

            return $this->display(__FILE__, 'product-log.tpl');
        }
    }

    public function getProductLogHistory($id_product, $page = 1, $fields_list = 50)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page-1)*$fields_list;
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_log
            WHERE id_product = ' . (int)$id_product . '
            ORDER BY id_log DESC
            LIMIT '.$offset.', '.$fields_list;

        return Db::getInstance()->ExecuteS($sql);
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookdisplayBackOfficeTop($params)
    {
        if (isset(Context::getContext()->controller) && isset(Context::getContext()->controller->php_self)) {
            
            if (Context::getContext()->controller->php_self == "AdminCmsContent") {
                $id_cms = false;
                if (Tools::getValue('id_cms') && (int)Tools::getValue('id_cms') > 0) {
                    $id_cms = (int)Tools::getValue('id_cms');
                }
                Media::addJsDef(['idcms' => $id_cms]);
            }

            if (Context::getContext()->controller->php_self == "AdminCategories") {
                $id_category = false;
                if (Tools::getValue('id_category') && (int)Tools::getValue('id_category') > 0) {
                    $id_category = (int)Tools::getValue('id_category');
                }
                Media::addJsDef(['idcategory' => $id_category]);
            }

            if (Context::getContext()->controller->php_self == "AdminManufacturers") {
                $id_manufacturer = false;
                if (Tools::getValue('id_manufacturer') && (int)Tools::getValue('id_manufacturer') > 0) {
                    $id_manufacturer = (int)Tools::getValue('id_manufacturer');
                }
                Media::addJsDef(['idmanufacturer' => $id_manufacturer]);
            }
        }
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('ui.slider');
        }

        // dump(Context::getContext()->controller->php_self);
        // dump(Tools::getValue('id_category'));
        // dump($params);
        // exit;

        if (isset(Context::getContext()->controller) && isset(Context::getContext()->controller->className) && Context::getContext()->controller->className == "AdminChatGPTPROChangelog") {
            $this->context->controller->addCSS($this->_path . '/views/css/uikit.min.css');
            $this->context->controller->addJS($this->_path . '/views/js/uikit.min.js');
            $this->context->controller->addJS($this->_path . '/views/js/uikit-icons.min.js');
            $this->context->controller->addJS($this->_path . '/views/js/react.min.js');
            $this->context->controller->addJS($this->_path . '/views/js/react-dom.min.js');
        } 

        $this->context->controller->addJS($this->_path . 'views/js/back.js');
        $this->context->controller->addCSS($this->_path . 'views/css/back.css');

        $languages = Language::getLanguages(true);
        $lang_summary = [];

        foreach ($languages as $key => $lang) {
            $lang_summary[$lang['iso_code']] = $lang['id_lang'];
        }

        Media::addJsDef(['lang_summary' => $lang_summary]);

        if (Configuration::get($this->mod . '_TOPCTA') == 0) {
            return '';
        }

        $module_controller = $this->context->link->getModuleLink(
            $this->name,
            $this->frontControllerPage,
            ['secret_token' => Configuration::get($this->mod . '_TOKEN')]
        );

        $prompts = $this->getPromptList(1, 50, true);

        $currentController = false;
        if (isset(Context::getContext()->controller) && isset(Context::getContext()->controller->php_self)) {
            $currentController = Context::getContext()->controller->php_self;
        }

        $this->context->smarty->assign([
            'path' => $this->_path,
            'prompts' => $prompts,
            'mod_controller' => $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'module_vars' => $this->getConfigFormValues(),
            'module_controller' => $module_controller,
            'currentController' => $currentController,
            'languages_list' => $languages,
            'id_language_selected' => $this->context->language->id,
        ]);

        return $this->display(__FILE__, 'content-generator.tpl');
    }

    public function hookdashboardZoneTwo()
    {
        if ($this->dash_display == true) {
            $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
            $this->context->smarty->assign([
                'path' => $this->_path,
                'shop' => $shop,
                'module_config' => $this->context->link->getAdminLink('AdminModules', false)
                        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&token=' .
                        Tools::getAdminTokenLite('AdminModules'),
                'AdminChatGPTPRO_link' => $this->context->link->getAdminLink('AdminChatGPTPRO', false).
                    '&token='.Tools::getAdminTokenLite('AdminChatGPTPRO'),
                'AdminChatGPTPROUsage_link' => $this->context->link->getAdminLink('AdminChatGPTPROUsage', false).
                    '&token='.Tools::getAdminTokenLite('AdminChatGPTPROUsage'),
                'AdminChatGPTPROCron_link' => $this->context->link->getAdminLink('AdminChatGPTPROCron', false).
                    '&token='.Tools::getAdminTokenLite('AdminChatGPTPROCron'),
            ]);

            return $this->display(__FILE__, 'dashboard.tpl');
        }
    }

    public function hookDisplayFooter()
    {
        /* Place your code here. */
    }

    public function hookDisplayHeader()
    {
        /* Place your code here. */
    }

    protected function getChatGPTModels()
    {
        $OPENAI_API_KEY = Configuration::get($this->mod . '_API_KEY');

        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $OPENAI_API_KEY . '',
        ];

        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/models');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($ch);
        $decoded_json = json_decode($result, true);

        if (Configuration::get($this->mod . '_DEBUG_MODE') == 1) {
            dump($decoded_json);
        }

        return $decoded_json;
    }
}
