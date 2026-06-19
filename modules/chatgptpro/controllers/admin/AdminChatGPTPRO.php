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

class AdminChatGPTPROController extends ModuleAdminController
{
    public $available_fields;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->meta_title = 'OpenAI Content Creator';

        parent::__construct();

        $this->name = 'chatgptpro';

        $this->mod = 'WEBLIR_' . strtoupper($this->name);

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function renderView()
    {
        $return = '';
        if (Tools::isSubmit('filterProducts')) {
            $listed_products = [];
            $where_strings = [];

            if (Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIES') && count(Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIES'))>0) {
                $where_strings[] = "( p.`id_product` IN (SELECT id_product FROM `" . _DB_PREFIX_ . "category_product` WHERE `id_category` IN (" . implode(",", Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIES')) . ")) OR p.id_category_default IN (" . implode(",", Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIES')) . ") )";
            }

            if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_BRANDS')>0) {
                $where_strings[] = "p.id_manufacturer = " . (int)Tools::getValue('WEBLIR_CHATGPTPRO_BRANDS');
            }

            if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS') == 0 || (int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS') == 1) {
                $where_strings[] = "p.active = " . (int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS');
            }

            if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_STOCK') == 1) {
                $where_strings[] = "p.id_product IN (SELECT DISTINCT id_product FROM `"._DB_PREFIX_."stock_available` WHERE quantity > 0 )";
            }

            if (Tools::strlen(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_IDS'))>0) {
                $where_strings[] = "p.id_product IN (" . str_replace(" ", "", Tools::getValue('WEBLIR_CHATGPTPRO_PROD_IDS')) . ")";
            }

            if (Tools::strlen(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_REF'))>0) {
                $refs = str_replace(" ", "", Tools::getValue('WEBLIR_CHATGPTPRO_PROD_REF'));
                $refs = str_replace("'", "", $refs);
                $refs = explode(",", $refs);
                $refs = "'".implode("','", $refs)."'";
                $where_strings[] = "p.reference IN (" . $refs . ")";
            }

            if (Tools::strlen(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_DATE_FROM'))>0) {
                $where_strings[] = "p.date_add > '" . pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_DATE_FROM')) . "'";
            }

            if (Tools::strlen(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_DATE_TO'))>0) {
                $where_strings[] = "p.date_add < '" . pSQL(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_DATE_TO')) . "'";
            }

            if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_HISTORY') == 0 || (int)Tools::getValue('WEBLIR_CHATGPTPRO_HISTORY') == 1 ) {
                if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_HISTORY') == 1) {
                    $where_strings[] = "p.`id_product` IN (SELECT DISTINCT id_product FROM `"._DB_PREFIX_.$this->name ."_log` )";
                } else {
                    $where_strings[] = "NOT EXISTS ( SELECT DISTINCT q.id_product FROM `"._DB_PREFIX_.$this->name ."_log` q where q.id_product = p.id_product)";
                }
            }

            $where_string = "";
            if (count($where_strings) > 0) {
                $where_string = " WHERE " . implode(" AND ", $where_strings);
            }

            $limit_string = "";
            if (Tools::strlen(Tools::getValue('WEBLIR_CHATGPTPRO_PROD_LIMIT'))>0 && (int)Tools::getValue('WEBLIR_CHATGPTPRO_PROD_LIMIT')>0) {
                $limit_string = " LIMIT ".(int)Tools::getValue('WEBLIR_CHATGPTPRO_PROD_LIMIT');
            }

            $sql = '
                SELECT p.id_product, p.reference
                FROM ' . _DB_PREFIX_ . 'product p' .
                $where_string .
                " GROUP BY p.id_product" . $limit_string;

            $selected_products = Db::getInstance()->executeS($sql);

            foreach ($selected_products as $key => $product) {
                $lang_data_sql = '
                    SELECT 
                        id_lang,
                        name,
                        CHAR_LENGTH(description) AS description_size,
                        CHAR_LENGTH(description_short) AS description_short_size,
                        CHAR_LENGTH(meta_title) AS meta_title_size,
                        CHAR_LENGTH(meta_description) AS meta_description_size,
                        CHAR_LENGTH(name) AS name_size
                    FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_product = ' . (int)$product['id_product'];
                $lang_data = Db::getInstance()->executeS($lang_data_sql);

                $prod_lang_data = [];
                foreach ($lang_data as $key => $lang_row) {
                    $prod_lang_data[$lang_row['id_lang']] = $lang_row;
                }

                $listed_products[] = [
                    "id_product" => $product['id_product'],
                    "reference" => $product['reference'],
                    "lang_data" => $prod_lang_data
                ];
            }

            $return .= $this->displayMassContentForm($listed_products);
            $return .= $this->renderProductUpdateForm();
        } elseif (Tools::isSubmit('filterCategories')) {
            $listed_categories = [];
            $where_strings = [];

            if (Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIESC') && count(Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIESC'))>0) {
                $where_strings[] = "c.`id_category` IN (" . implode(",", Tools::getValue('WEBLIR_CHATGPTPRO_CATEGORIESC')) . ")";
            }

            if ((int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS') == 0 || (int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS') == 1) {
                $where_strings[] = "c.active = " . (int)Tools::getValue('WEBLIR_CHATGPTPRO_STATUS');
            }

            $where_string = "";
            if (count($where_strings) > 0) {
                $where_string = " WHERE " . implode(" AND ", $where_strings);
            }

            $sql = '
                SELECT c.id_category
                FROM ' . _DB_PREFIX_ . 'category c' .
                $where_string .
                " GROUP BY c.id_category";

            $selected_categories = Db::getInstance()->executeS($sql);

            

            foreach ($selected_categories as $key => $category) {
                $lang_data_sql = '
                    SELECT 
                        id_lang,
                        name,
                        CHAR_LENGTH(description) AS description_size,
                        CHAR_LENGTH(meta_title) AS meta_title_size,
                        CHAR_LENGTH(meta_description) AS meta_description_size,
                        CHAR_LENGTH(name) AS name_size
                    FROM ' . _DB_PREFIX_ . 'category_lang WHERE id_category = ' . (int)$category['id_category'];
                $lang_data = Db::getInstance()->executeS($lang_data_sql);

                $cat_lang_data = [];
                foreach ($lang_data as $key => $lang_row) {
                    $cat_lang_data[$lang_row['id_lang']] = $lang_row;
                }

                $listed_categories[] = [
                    "id_category" => $category['id_category'],
                    "lang_data" => $cat_lang_data
                ];
            }

            $return .= $this->displayMassCatContentForm($listed_categories);
            $return .= $this->renderCategoryUpdateForm();
        }

        else {
            $return .= $this->renderProductFilterForm();
            $return .= $this->renderCategoryFilterForm();
        }
        
        return $return;
    }

    public function psversion()
    {
        $version = _PS_VERSION_;
        $ver = explode('.', $version);

        return $ver[1];
    }

    private function displayMassContentForm($listed_products)
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $module_controller = $this->context->link->getModuleLink(
            "chatgptpro",
            "ajax",
            [
                'secret_token' => Configuration::get($this->mod . '_TOKEN'),
            ]
        );

        $prompts = $this->getPromptList();

        $this->context->smarty->assign([
            'path' => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/chatgptpro/',
            'shop' => $shop,
            'prompts' => $prompts,
            'module_controller' => $module_controller,
            'current_lang' => $this->context->language->id,
            'lang_list' => Language::getLanguages(true),
            'listed_products' => $listed_products,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/mass-content-generator.tpl');
    }

    private function displayMassCatContentForm($listed_categories)
    {
        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $module_controller = $this->context->link->getModuleLink(
            "chatgptpro",
            "ajax",
            [
                'secret_token' => Configuration::get($this->mod . '_TOKEN'),
            ]
        );

        $prompts = $this->getPromptList();

        $this->context->smarty->assign([
            'path' => _PS_BASE_URL_SSL_.__PS_BASE_URI__.'modules/chatgptpro/',
            'shop' => $shop,
            'prompts' => $prompts,
            'module_controller' => $module_controller,
            'current_lang' => $this->context->language->id,
            'lang_list' => Language::getLanguages(true),
            'listed_categories' => $listed_categories,
        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/mass-category-generator.tpl');
    }

    public function renderCategoryFilterForm()
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int) $this->context->shop->id;
        $categories = [];

        $cats = $this->getCategories(
            $lang->id,
            true,
            $id_shop
        );

        foreach ($cats as $cat) {
            $categories[] = ['id_option' => $cat['id_category'], 'name' => $cat['name']];
        }

        $inputs[] = array(
            'type' => 'categories',
            'label' => $this->l('Categories'),
            'desc' => $this->l('Target products by categories.') . "<br>" . $this->l('Leave unselected to disable.'),
            'required' => false,
            'name' => 'WEBLIR_CHATGPTPRO_CATEGORIESC',
            'tree' => array(
                'root_category' => $this->context->shop->getCategory(),
                'id' => 'id_categoryc',
                'use_checkbox' => true,
                'use_search' => true,
                'selected_categories' => array()
                )
        );

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Category status'),
            'desc' => $this->l('Target categories by status'),
            'name' => 'WEBLIR_CHATGPTPRO_STATUS',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Active'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 2,
                    'label' => $this->l('Both'),
                ],
            ],
        ];

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Filter categories to update'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Go to next step'),
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminModules', false) .
                        '&token=' . Tools::getAdminTokenLite('AdminModules') .
                        '&configure=chatgptpro&tab_module=administration&module_name=chatgptpro',
                        'title' => $this->l('Module settings'),
                        'name' => 'saveCategorySettings',
                        'icon' => 'icon-save',
                    ],
                ],
            ],
        ];

        if (count($this->errors) > 0) {
            $keep_data = 1;
        } else {
            $keep_data = 1;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = [];
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'filterCategories';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPRO');
        $helper->tpl_vars = [
            'fields_value' => $this->getCatConfigFieldsValues($keep_data),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);

    }

    public function renderProductFilterForm()
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int) $this->context->shop->id;
        $categories = [];
        $options = [];

        foreach ($langs as $language) {
            $options[] = ['id_option' => $language['id_lang'], 'name' => $language['name']];
        }

        $cats = $this->getCategories(
            $lang->id,
            true,
            $id_shop
        );

        $inputs = [];

        foreach ($cats as $cat) {
            $categories[] = ['id_option' => $cat['id_category'], 'name' => $cat['name']];
        }

        $brands = [];
        $brands[] = [
            "id_manufacturer" => 0,
            "name" => $this->l('Select brand'),
        ];

        $brand_list = Manufacturer::getManufacturers();
        foreach ($brand_list as $key => $brand) {
            $brands[] = [
                "id_manufacturer" => $brand['id_manufacturer'],
                "name" => $brand['name'],
            ];
        }

        $suppliers = Supplier::getSuppliers();
        $carriers = Carrier::getCarriers($this->context->language->id, false);

        // $combinations = Attribute::getAttributes((int) Configuration::get('PS_LANG_DEFAULT'));
        // array_unshift($combinations, ['id_attribute' => 0, 'name' => 'NO']);

        $taxes = TaxRulesGroup::getTaxRulesGroups();

        $inputs[] = array(
            'type' => 'categories',
            'label' => $this->l('Product Categories'),
            'desc' => $this->l('Target products by categories.') . "<br>" . $this->l('Leave unselected to disable.'),
            'required' => false,
            'name' => 'WEBLIR_CHATGPTPRO_CATEGORIES',
            'tree' => array(
                'root_category' => $this->context->shop->getCategory(),
                'id' => 'id_category',
                'use_checkbox' => true,
                'use_search' => true,
                'selected_categories' => array()
                )
        );

        $inputs[] = array(
            'type' => 'select',
            'label' => $this->l('Brand'),
            'desc' => $this->l('Filter products by brand.') . "<br>" . $this->l('Leave unselected to disable.'),
            'name' => 'WEBLIR_CHATGPTPRO_BRANDS',
            'required' => false,
            'values' => array(),
            'options' => array(
                'query' => $brands,
                'id' => 'id_manufacturer',
                'name' => 'name'
            )
        );

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Product status'),
            'desc' => $this->l('Target products by status'),
            'name' => 'WEBLIR_CHATGPTPRO_STATUS',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Active'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('Disabled'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 2,
                    'label' => $this->l('Both'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Product stock'),
            'desc' => $this->l('Target products by stock'),
            'name' => 'WEBLIR_CHATGPTPRO_STOCK',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => 1,
                    'label' => $this->l('Only in stock products'),
                ],
                [
                    'id' => 'active_off',
                    'value' => 0,
                    'label' => $this->l('All products'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Update only'),
            'hint' => $this->l('Target products using the History Log'),
            'name' => 'WEBLIR_CHATGPTPRO_HISTORY',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "0",
                    'label' => $this->l('Products that have not been updated with OpenAi content'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "1",
                    'label' => $this->l('Products that already have been updated with OpenAi content'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "2",
                    'label' => $this->l('Update all'),
                ]
            ],
        ];

        $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('Product IDs'),
                'name' => 'WEBLIR_CHATGPTPRO_PROD_IDS',
                'desc' => $this->l('Filter the products by product ID.') . "<br>" . $this->l('You can enter multiple product IDs separated by comma.') . "<br>" . $this->l('Leave empty to disable.'),
                'required' => false,
                'lang' => false,
        );

        $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('Product Reference'),
                'name' => 'WEBLIR_CHATGPTPRO_PROD_REF',
                'desc' => $this->l('Filter the products by product Reference.') . "<br>" . $this->l('You can enter multiple product reference codes separated by comma.') . "<br>" . $this->l('Leave empty to disable.'),
                'required' => false,
                'lang' => false,
        );

        $inputs[] = array(
                'type' => 'datetime',
                'label' => $this->l('Product Add Date From'),
                'name' => 'WEBLIR_CHATGPTPRO_PROD_DATE_FROM',
                'desc' => $this->l('Filter the products by creation date.') . "<br>" . $this->l('Here you can set the starting date.'),
                'required' => false,
                'lang' => false,
        );

        $inputs[] = array(
                'type' => 'datetime',
                'label' => $this->l('Product Add Date To'),
                'name' => 'WEBLIR_CHATGPTPRO_PROD_DATE_TO',
                'desc' => $this->l('Filter the products by creation date.') . "<br>" . $this->l('Here you can set the ending date.'),
                'required' => false,
                'lang' => false,
        );

        $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('Product Limit'),
                'name' => 'WEBLIR_CHATGPTPRO_PROD_LIMIT',
                'desc' => $this->l('Limit the number of products you want to process.') . "<br>" . $this->l('This field is useful when processing a large number of products and you need to set a limit to avoid execution timeouts..') . "<br>" . $this->l('Leave empty to disable.'),
                'required' => false,
                'lang' => false,
        );

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Filter products to update'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Go to next step'),
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminModules', false) .
                        '&token=' . Tools::getAdminTokenLite('AdminModules') .
                        '&configure=chatgptpro&tab_module=administration&module_name=chatgptpro',
                        'title' => $this->l('Module settings'),
                        'name' => 'saveProductSettings',
                        'icon' => 'icon-save',
                    ],
                ],
            ],
        ];

        if (count($this->errors) > 0) {
            $keep_data = 1;
        } else {
            $keep_data = 1;
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(
            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'
        ) : 0;
        $this->fields_form = [];
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'filterProducts';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPRO');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues($keep_data),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }


    public function renderProductUpdateForm()
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int) $this->context->shop->id;
        $categories = [];
        $lang_options = [];

        foreach ($langs as $language) {
            $lang_options[] = ['id' => "lang_".$language['id_lang'], 'value' => $language['id_lang'], 'label' => $language['name']];
        }

        $cats = $this->getCategories(
            $lang->id,
            true,
            $id_shop
        );

        $inputs = [];

        foreach ($cats as $cat) {
            $categories[] = ['id_option' => $cat['id_category'], 'name' => $cat['name']];
        }

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Field to update'),
            'name' => 'WEBLIR_CHATGPTPRO_FIELD',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "name",
                    'label' => $this->l('Product name'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "description",
                    'label' => $this->l('Description'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "description_short",
                    'label' => $this->l('Short Description'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "meta_title",
                    'label' => $this->l('Meta Title'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "meta_description",
                    'label' => $this->l('Meta Description'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "tags",
                    'label' => $this->l('Tags'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Existing data'),
            'hint' => $this->l('Choose what to do if the product already has data'),
            'name' => 'WEBLIR_CHATGPTPRO_EXISTING',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "keep_top",
                    'label' => $this->l('Keep existing data and add new generated content at the top'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "keep_end",
                    'label' => $this->l('Keep existing data and add new generated content at the end'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "keep",
                    'label' => $this->l('Keep existing data and skip product'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "replace",
                    'label' => $this->l('Remove data and add new generated content'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Language'),
            'name' => 'WEBLIR_CHATGPTPRO_LANGUAGE',
            'desc' => $this->l('Select the language that will be updated'),
            'values' => $lang_options,
        ];

        $inputs[] = array(
                'type' => 'textarea',
                'label' => $this->l('Content Prompt'),
                'name' => 'WEBLIR_CHATGPTPRO_CONTENT',
                'lang' => false,
                'required' => true,
                'autoload_rte' => false
        );

        $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('API call delay'),
                'hint' => $this->l('Set 0 to disable'),
                'desc' => $this->l('If using the free OpenAI trial, there is an API rate limit, therefore you need to set a delay between each API request.') . "<br>" . $this->l('Set 0 to disable'),
                'name' => 'WEBLIR_CHATGPTPRO_DELAY',
                'lang' => false,
                'required' => false,
                'autoload_rte' => false,
                'suffix' => $this->l('seconds'),
        );

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Generate product content'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Generate content'),
                    'id' => "generateProductContent",
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminModules', false) .
                        '&token=' . Tools::getAdminTokenLite('AdminModules') .
                        '&configure=chatgptpro&tab_module=administration&module_name=chatgptpro',
                        'title' => $this->l('Module settings'),
                        'name' => 'saveProductSettings',
                        'icon' => 'icon-save',
                    ],
                    [
                        'href' => self::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminChatGPTPRO'),
                        'title' => $this->l('Go Back to Product Filter'),
                        'name' => 'goToProductFilter',
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
        $helper->submit_action = 'generateProductContent';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPRO');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];


        return $helper->generateForm([$fields_form]);
    }

    public function renderCategoryUpdateForm()
    {
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $langs = Language::getLanguages();
        $id_shop = (int) $this->context->shop->id;
        $categories = [];
        $lang_options = [];

        foreach ($langs as $language) {
            $lang_options[] = ['id' => "lang_".$language['id_lang'], 'value' => $language['id_lang'], 'label' => $language['name']];
        }

        $cats = $this->getCategories(
            $lang->id,
            true,
            $id_shop
        );

        $inputs = [];

        foreach ($cats as $cat) {
            $categories[] = ['id_option' => $cat['id_category'], 'name' => $cat['name']];
        }

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Field to update'),
            'name' => 'WEBLIR_CHATGPTPRO_FIELD',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "name",
                    'label' => $this->l('Category name'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "description",
                    'label' => $this->l('Description'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "meta_title",
                    'label' => $this->l('Meta Title'),
                ],
                [
                    'id' => 'active_on',
                    'value' => "meta_description",
                    'label' => $this->l('Meta Description'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Existing data'),
            'hint' => $this->l('Choose what to do if the product already has data'),
            'name' => 'WEBLIR_CHATGPTPRO_EXISTING',
            'class' => 't',
            'values' => [
                [
                    'id' => 'active_on',
                    'value' => "keep_top",
                    'label' => $this->l('Keep existing data and add new generated content at the top'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "keep_end",
                    'label' => $this->l('Keep existing data and add new generated content at the end'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "keep",
                    'label' => $this->l('Keep existing data and skip category'),
                ],
                [
                    'id' => 'active_off',
                    'value' => "replace",
                    'label' => $this->l('Remove data and add new generated content'),
                ],
            ],
        ];

        $inputs[] = [
            'type' => 'radio',
            'label' => $this->l('Language'),
            'name' => 'WEBLIR_CHATGPTPRO_LANGUAGE',
            'desc' => $this->l('Select the language that will be updated'),
            'values' => $lang_options,
        ];

        $inputs[] = array(
                'type' => 'textarea',
                'label' => $this->l('Content Prompt'),
                'name' => 'WEBLIR_CHATGPTPRO_CONTENT',
                'lang' => false,
                'required' => true,
                'autoload_rte' => false
            );

        $inputs[] = array(
                'type' => 'text',
                'label' => $this->l('API call delay'),
                'hint' => $this->l('Set 0 to disable'),
                'desc' => $this->l('If using the free OpenAI trial, there is an API rate limit, therefore you need to set a delay between each API request.') . "<br>" . $this->l('Set 0 to disable'),
                'name' => 'WEBLIR_CHATGPTPRO_DELAY',
                'lang' => false,
                'required' => false,
                'autoload_rte' => false,
                'suffix' => $this->l('seconds'),
        );

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Generate category content'),
                    'icon' => 'icon-cogs',
                ],
                'input' => $inputs,
                'submit' => [
                    'title' => $this->l('Generate content'),
                    'id' => "generateCategoryContent",
                ],
                'buttons' => [
                    [
                        'href' => $this->context->link->getAdminLink('AdminModules', false) .
                        '&token=' . Tools::getAdminTokenLite('AdminModules') .
                        '&configure=chatgptpro&tab_module=administration&module_name=chatgptpro',
                        'title' => $this->l('Module settings'),
                        'name' => 'saveCategorySettings',
                        'icon' => 'icon-save',
                    ],
                    [
                        'href' => self::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminChatGPTPRO'),
                        'title' => $this->l('Go Back to Category Filter'),
                        'name' => 'goToProductFilter',
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
        $helper->submit_action = 'generateCategoryContent';
        $helper->currentIndex = self::$currentIndex;
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPRO');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];


        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        $arr = [
            'WEBLIR_CHATGPTPRO_FIELD' => "description",
            'WEBLIR_CHATGPTPRO_LANGUAGE' => (int)$this->context->language->id,
            'WEBLIR_CHATGPTPRO_CONTENT' => null,
            'WEBLIR_CHATGPTPRO_DELAY' => 0,
            'WEBLIR_CHATGPTPRO_EXISTING' => "replace",
            'WEBLIR_CHATGPTPRO_HISTORY' => 2,
            'WEBLIR_CHATGPTPRO_STATUS' => 1,
            'WEBLIR_CHATGPTPRO_STOCK' => 0,
            'WEBLIR_CHATGPTPRO_PROD_IDS' => null,
            'WEBLIR_CHATGPTPRO_PROD_REF' => null,
            'WEBLIR_CHATGPTPRO_PROD_DATE_FROM' => null,
            'WEBLIR_CHATGPTPRO_PROD_DATE_TO' => null,
            'WEBLIR_CHATGPTPRO_PROD_LIMIT' => null,
            'WEBLIR_CHATGPTPRO_CATEGORIES' => null,
            'WEBLIR_CHATGPTPRO_BRANDS' => array(),
        ];

        return $arr;
    }

    public function getCatConfigFieldsValues()
    {
        $arr = [
            'WEBLIR_CHATGPTPRO_STATUS' => 1,
            'WEBLIR_CHATGPTPRO_CATEGORIESC' => null,
        ];

        return $arr;
    }

    public function postProcess()
    {
        $form_values = $this->getConfigFieldsValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
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

    public function getPromptList($page = 1, $fields_list = 50)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            $offset = ($page-1)*$fields_list;
        }
        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->name.'_prompt
            WHERE status = 1
            ORDER BY id_prompt DESC
            LIMIT '.$offset.', '.$fields_list;

        return Db::getInstance()->ExecuteS($sql);
    }

    public function getCategories(
        $id_lang,
        $active,
        $id_shop
    ) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'category` c
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`
            WHERE ' . ($id_shop ? 'cl.`id_shop` = ' .
            (int) $id_shop : '') . ' ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '') . '
            ' . ($active ? 'AND `active` = 1' : '') . '
            ' . (!$id_lang ? 'GROUP BY c.id_category' : '') . '
            ORDER BY c.`level_depth` ASC, c.`position` ASC'
        );

        return $result;
    }
}
