<?php
/**
* Price increment/Reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

class AdminGroupincController extends ModuleAdminController
{
    protected $delete_mode;

    protected $_defaultOrderBy = 'date_add';
    protected $_defaultOrderWay = 'DESC';
    protected $can_add_giconf = true;
    protected $top_elements_in_list = 4;
    protected $orderBy = 'id_product';
    protected $orderWay = 'ASC';

    protected static $meaning_status = array();

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'groupinc_configuration';
        $this->className = 'GroupincConfiguration';
        $this->tabClassName = 'AdminGroupinc';
        $this->module_name = 'groupinc';
        $this->name = 'groupinc';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('duplicate');
        $this->addRowAction('viewproducts');
        $this->_orderWay = $this->_defaultOrderWay;
        $this->taxes_included = (Configuration::get('PS_TAX') == '0' ? false : true);
        $this->allow_duplicate = true;

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );

        $this->context = Context::getContext();

        $this->default_form_language = $this->context->language->id;

        $this->fields_list = array(
            'id_groupinc_configuration' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'filter_key' => 'a!name'
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'callback' => 'getType',
                'type' => 'select',
                'list' => array(0 => $this->l('Fix'), 1 => $this->l('Percentage'), 2 => $this->l('Fix + Percentage')),
                'filter_key' => 'a!type',
                'align' => 'text-center'
            ),
            'mode' => array(
                'title' => $this->l('Mode'),
                'callback' => 'getMode',
                'type' => 'select',
                'filter_key' => 'a!mode',
                'list' => array(0 => $this->l('Increment'), 1 => $this->l('Reduction'), 2 => $this->l('Set Fixed price'), 3 => $this->l('Free Tax'), 4 => $this->l('Add Tax')),
                'align' => 'text-center'
            ),
            'fix' => array(
                'title' => $this->l('Fix'),
                'align' => 'text-center'
            ),
            'percentage' => array(
                'title' => $this->l('Percentage'),
                'align' => 'text-center'
            ),
            'groups' => array(
                'title' => $this->l('Group(s)'),
                'callback' => 'getCustomerGroups',
                'align' => 'text-center'
            ),
            'categories' => array(
                'title' => $this->l('Category(s)'),
                'callback' => 'getCategories',
                'align' => 'text-center'
            ),
            'products' => array(
                'title' => $this->l('Products(s)'),
                'callback' => 'getProducts',
                'align' => 'text-center'
            ),
            'priority' => array(
                'title' => $this->l('Priority'),
                'align' => 'text-center'
            ),
            'date_to' => array(
                'title' => $this->l('Valid'),
                'align' => 'text-center',
                'callback' => 'printValidIcon',
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'callback' => 'printActiveIcon'
            ),
        );

        if (Tools::isSubmit('submitFilterproduct') && (int)Tools::getValue('submitFilterproduct') == 0) {
            $this->action = 'reset_filters';
        }

        if (Shop::isFeatureActive() && (Shop::getContext() == Shop::CONTEXT_ALL || Shop::getContext() == Shop::CONTEXT_GROUP)) {
            $this->can_add_giconf = false;
        }

        if (!Shop::isFeatureActive()) {
            $this->shopLinkType = '';
        } else {
            $this->shopLinkType = 'shop';
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin(array('typewatch', 'fancybox', 'autocomplete'));

        $_path = _MODULE_DIR_.$this->module->name;

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->context->controller->addCSS($_path.'/views/css/groupinc_15.css');
            $this->context->controller->addJS($_path.'/views/js/gi_functions_15.js');
        } else {
            $this->context->controller->addCSS($_path.'/views/css/groupinc.css');
            $this->context->controller->addJS($_path.'/views/js/gi_functions.js');
            if ($this->display) {
                $this->context->controller->addJS($_path.'/views/js/tabs.js');
            }
        }
    }

    public function postProcess()
    {
        if (Tools::isSubmit('conf')) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->cleanCache();
            }
        }
        return parent::postProcess();
    }

    public function initContent()
    {
        if ($this->action == 'select_delete') {
            $this->context->smarty->assign(array(
                'delete_form' => true,
                'url_delete' => htmlentities($_SERVER['REQUEST_URI']),
                'boxes' => $this->boxes,
            ));
        }

        if (!$this->can_add_giconf && !$this->display) {
            $this->informations[] = $this->l('You have to select a shop if you want to create a new configuration.');
        }

        $module = new Groupinc();
        if ($this->action != 'new' && !Tools::isSubmit('updategroupinc_configuration')) {
            if (Tools::isSubmit('submitGroupincModuleGlobalConfig')) {
                $form_values = $this->getGlobalConfigFormValues();
                foreach (array_keys($form_values) as $key) {
                    if ((version_compare(_PS_VERSION_, '1.6', '>=') ? Tools::strpos($key, '[]') > 0 : strpos($key, '[]') > 0)) {
                        $key = Tools::str_replace_once('[]', '', $key);
                        Configuration::updateValue($key, implode(';', Tools::getValue($key)));
                    } else {
                        Configuration::updateValue($key, Tools::getValue($key));
                    }
                }
                $this->content .= $module->displayConfirmation($this->l('Configuration saved successfully.'));
            }
        }

        if (!$this->display) {
            $this->content .= $this->renderGlobalConfigForm();
        }

        if ((Tools::getValue('id_groupinc_configuration')) && Tools::getIsset('duplicategroupinc_configuration')) {
            $this->processDuplicate();
        }

        parent::initContent();

        if (Tools::isSubmit('exportProducts')) {
            $collection = $this->getProductsList(true, Tools::getValue('id_groupinc_configuration'));
            if (count($collection) == 0) {
                $this->warnings[] = $this->l('No products affected from this rule.');
            } else {
                die($this->downloadCsv($collection, date('Ymd_hi').'_rule_'.Tools::getValue("id_groupinc_configuration").'_products.csv'));
            }
        }
        if ($this->action != 'new' && !Tools::isSubmit('updategroupinc_configuration') && Tools::isSubmit('viewProducts')) {
            //$this->content .= $this->getConfigurations(Tools::getValue('id_groupinc_configuration'));
            $this->content .= $this->getProductsList(false, Tools::getValue('id_groupinc_configuration'));
        }

        if (!$this->display) {
            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->smarty->assign(array(
                    'this_path'                 => $this->module->getPathUri(),
                    'support_id'                => $module->addons_id_product,
                ));

                $available_iso_codes = array('en', 'es');
                $default_iso_code = 'en';
                $template_iso_suffix = in_array($this->context->language->iso_code, $available_iso_codes) ? $this->context->language->iso_code : $default_iso_code;
                $this->content .= $this->context->smarty->fetch($this->module->getLocalPath().'views/templates/admin/company/information_'.$template_iso_suffix.'.tpl');
            }

            $this->context->smarty->assign(array(
                'content' => $this->content,
                'token' => $this->token,
            ));
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();

        if (!$this->can_add_giconf) {
            unset($this->toolbar_btn['new']);
        }
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop);
    }

    public function initModal()
    {
        parent::initModal();

        $languages = Language::getLanguages(false);
        $translateLinks = array();

        if (version_compare(_PS_VERSION_, '1.7.2.1', '>=')) {
            $module = Module::getInstanceByName($this->module->name);
            $isNewTranslateSystem = $module->isUsingNewTranslationSystem();
            $link = Context::getContext()->link;
            foreach ($languages as $lang) {
                if ($isNewTranslateSystem) {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslationSf', true, array(
                        'lang' => $lang['iso_code'],
                        'type' => 'modules',
                        'selected' => $module->name,
                        'locale' => $lang['locale'],
                    ));
                } else {
                    $translateLinks[$lang['iso_code']] = $link->getAdminLink('AdminTranslations', true, array(), array(
                        'type' => 'modules',
                        'module' => $module->name,
                        'lang' => $lang['iso_code'],
                    ));
                }
            }
        }

        $this->context->smarty->assign(array(
            'trad_link' => 'index.php?tab=AdminTranslations&token='.Tools::getAdminTokenLite('AdminTranslations').'&type=modules&module='.$this->module->name.'&lang=',
            'module_languages' => $languages,
            'module_name' => $this->module->name,
            'translateLinks' => $translateLinks,
        ));

        $modal_content = $this->context->smarty->fetch('controllers/modules/modal_translation.tpl');

        $this->modals[] = array(
            'modal_id' => 'moduleTradLangSelect',
            'modal_class' => 'modal-sm',
            'modal_title' => $this->l('Translate this module'),
            'modal_content' => $modal_content
        );
    }

    public function initToolbarTitle()
    {
        parent::initToolbarTitle();

        switch ($this->display) {
            case '':
            case 'list':
                array_pop($this->toolbar_title);
                $this->toolbar_title[] = $this->l('Manage Groupinc Configuration');
                break;
            case 'view':
                if (($giconf = $this->loadObject(true)) && Validate::isLoadedObject($giconf)) {
                    array_pop($this->toolbar_title);
                    $this->toolbar_title[] = sprintf($this->l('Configuration: %s'), $giconf->name);
                }
                break;
            case 'add':
            case 'edit':
                array_pop($this->toolbar_title);
                if (($giconf = $this->loadObject(true)) && Validate::isLoadedObject($giconf)) {
                    $this->toolbar_title[] = sprintf($this->l('Editing Configuration: %s'), $giconf->name);
                } else {
                    $this->toolbar_title[] = $this->l('Creating a new Increment/Reduction configuration:');
                }
                break;
        }
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['desc-module-back'] = array(
                'href' => 'index.php?controller=AdminModules&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back'),
                'icon' => 'process-icon-back'
            );
            $this->page_header_toolbar_btn['desc-module-new'] = array(
                'href' => 'index.php?controller='.$this->tabClassName.'&add'.$this->table.'&token='.Tools::getAdminTokenLite($this->tabClassName),
                'desc' => $this->l('New'),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['desc-module-reload'] = array(
                'href' => 'index.php?controller='.$this->tabClassName.'&token='.Tools::getAdminTokenLite($this->tabClassName).'&reload=1',
                'desc' => $this->l('Reload'),
                'icon' => 'process-icon-refresh'
            );
            /*$this->page_header_toolbar_btn['desc-module-exportrules'] = array(
                'href' => 'index.php?controller='.$this->tabClassName.'&token='.Tools::getAdminTokenLite($this->tabClassName).'&exportrules=1',
                'desc' => $this->l('Export Rules'),
                'icon' => 'process-icon-export'
            );*/
            $this->page_header_toolbar_btn['desc-module-translate'] = array(
                'href' => '#',
                'desc' => $this->l('Translate'),
                'modal_target' => '#moduleTradLangSelect',
                'icon' => 'process-icon-flag'
            );
            $this->page_header_toolbar_btn['desc-module-hook'] = array(
                'href' => 'index.php?tab=AdminModulesPositions&token='.Tools::getAdminTokenLite('AdminModulesPositions').'&show_modules='.Module::getModuleIdByName('groupinc'),
                'desc' => $this->l('Manage hooks'),
                'icon' => 'process-icon-anchor'
            );
        }

        if (!$this->can_add_giconf) {
            unset($this->page_header_toolbar_btn['desc-module-new']);
        }
    }

    public function initProcess()
    {
        parent::initProcess();

        if (Tools::isSubmit('changeBackofficeVal') && $this->id_object) {
            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'change_backoffice_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
    }

    public function renderList()
    {
        if ((Tools::isSubmit('submitBulkdelete'.$this->table) || Tools::isSubmit('delete'.$this->table)) && $this->tabAccess['delete'] === '1') {
            $this->tpl_list_vars = array(
                'delete_groupincconf' => true,
                'REQUEST_URI' => $_SERVER['REQUEST_URI'],
                'POST' => $_POST
            );
        }

        return parent::renderList();
    }

    public function renderForm()
    {

        if (!($giconf = $this->loadObject(true))) {
            return;
        }

        $types = $this->getTypes();
        $modes = $this->getModes();
        $price_calc_options = $this->getPriceOptions(true);
        $price_options = $this->getPriceOptions();
        $group_options = $this->getGroupOptions();
        $id_lang = 0;
        $id_shop = 0;
        $categories = array();

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $id_lang = (int)$this->context->cookie->id_lang;
            $id_shop = (int)$this->context->cookie->id_shop;
            $currencies = Currency::getCurrencies(false, true);
        } else {
            $id_lang = (int)$this->context->language->id;
            $id_shop = (int)$this->context->shop->id;
            if (Shop::isFeatureActive()) {
                $currencies = Currency::getCurrenciesByIdShop($this->context->shop->id);
            } else {
                $currencies = Currency::getCurrencies(false, true);
            }
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $categories = array_merge($categories, Category::getCategories((int)($this->context->cookie->id_lang), false, false, '', 'ORDER BY cl.`name` ASC'));
        }

        $groups = Group::getGroups($id_lang, true);
        $customers = array();
        $products = array();
        if (Configuration::get('GROUPINC_USE_PRODUCTS') || Configuration::get('GROUPINC_USE_EXCLUSION')) {
            $products = $this->getProductsLite($id_lang, false, false);
        }

        if (Configuration::get('GROUPINC_USE_CUSTOMERS') || Configuration::get('GROUPINC_USE_EXCLUSION')) {
            $customers = Customer::getCustomers(true);
        }

        $countries = Country::getCountries($id_lang);
        $zones = Zone::getZones();
        $manufacturers = Manufacturer::getManufacturers(false, $id_lang, false);
        $suppliers = Supplier::getSuppliers(false, $id_lang, false);
        $languages = Language::getLanguages(false, $id_shop);

        $this->multiple_fieldsets = true;
        $this->default_form_language = $this->context->language->id;

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Increment/Reduction configuration'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable or disable this configuration'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'required' => true,
                    'col' => '8',
                    'desc' => $this->l('Invalid characters:').' !&lt;&gt;,;?=+()@#"Â°{}_$%:',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Mode'),
                    'name' => 'mode',
                    'required' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $modes,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Select if you want to configure an Increment, a Reduction or a Fixed Price'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Type'),
                    'name' => 'type',
                    'required' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $types,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Percentage, fix amount or a fix + percentage combination to define the rule'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Price percentage calculation'),
                    'name' => 'price_calculation',
                    'required' => true,
                    'col' => '3',
                    'class' => 'price_calculation',
                    'options' => array(
                        'query' => $price_calc_options,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Price to get the percentage of the increment/reduction'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Price to apply the increment/reduction'),
                    'name' => 'price_application',
                    'required' => true,
                    'col' => '3',
                    'options' => array(
                        'query' => $price_options,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Select the price where you want to apply the increment/reduction. The amount (percentage or fix) will be added, subtract or applied to this price'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Fix'),
                    'name' => 'fix',
                    'col' => '8',
                    'prefix' => $this->context->currency->sign,
                    'desc' => sprintf($this->l('Fixed amount increment/reduction (%s)'), ($this->taxes_included) ? $this->l('taxes included') : $this->l('without taxes')),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Percentage'),
                    'name' => 'percentage',
                    'col' => '8',
                    'prefix' => '%',
                    'desc' => $this->l('Percentage amount'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Minimum result price'),
                    'name' => 'min_result_price',
                    'col' => '8',
                    'default' => '0',
                    'prefix' => $this->context->currency->sign,
                    'desc' => $this->l('Define the minimum result price. When the rule is applied, if the product price is lower, the result will be this minimum result price. Example: if the minimum result price is 4€, when 5€ x 50% = 2.5€, the system will use 4€ (0 means no minimum result price)'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Maximum result price'),
                    'name' => 'max_result_price',
                    'col' => '8',
                    'default' => '0',
                    'prefix' => $this->context->currency->sign,
                    'desc' => $this->l('Define the maximum result price. When the rule is applied, if the product price is higher, the result will be this maximum result price. Example: if the maximum result price is 4€, when 10€ - 10% = 9€, the price will be 4€ instead of 9€ (0 means no maximum result price)'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display the reduction as discount'),
                    'name' => 'show_as_discount',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_as_discount_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_as_discount_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Show the reduction as an specific price (old price crossed out, discount and new price)'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Apply discount in sequence'),
                    'name' => 'sequential_discount',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'sequential_discount_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'sequential_discount_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable this option if you want to apply this discount in a sequence if the product has a previous discount (Example: 10% existing, 20% discount, with
                    the option enabled, the 10% is applied and after the 20%, with the option disabled, a 30% discount will be applied)'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Priorize existing discounts'),
                    'name' => 'priorize_existing',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'priorize_existing_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'priorize_existing_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Priorize existing discounts and apply the rules after'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Apply discount only if higher than existing'),
                    'name' => 'higher_discount',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'higher_discount_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'higher_discount_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Apply discount only if is higher than existing product discount'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Skip products with discounts'),
                    'name' => 'skip_discounts',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'skip_discounts_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'skip_discounts_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enabled, the rule will not be applied to the product with other discounts'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Override discounts'),
                    'name' => 'override_discounts',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'override_discounts_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'override_discounts_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enabled, only this discount will be applied, even if the product has a previous discount (not removed)'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Decimals in reductions'),
                    'name' => 'show_decimals',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_decimals_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_decimals_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('If the calculation of the reduction amount or percentage has decimals, you can decide if it will be displayed'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Show "On Sale" badge'),
                    'name' => 'show_on_sale',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_on_sale_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_on_sale_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Display the "On Sale" badge on the product and category pages'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Show in "Prices Drop" section'),
                    'name' => 'show_prices_drop',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_prices_drop_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_prices_drop_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('If the rule applies a reduction, you can enable this option to show the products in the "Prices Drop" (Sales) section'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display additional text/image in product lists and blocks'),
                    'name' => 'show_text',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_text_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_text_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable it to display an additional text and/or image under the modified price in the product lists and blocks'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Add the additional text in product list and blocks'),
                    'name' => 'addit_text',
                    'id' => 'html_content',
                    'required' => false,
                    'lang' => true,
                    'cols' => 8,
                    'rows' => 4,
                    'class' => 'toggle_show_text',
                    'autoload_rte' => 'rte',
                    'desc' => $this->l('Additional text and/or image in product lists and blocks. You can use HTML code, CSS and upload an image'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Display additional text/image in the product Page'),
                    'name' => 'show_text_prodpage',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'show_text_prodpage_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'show_text_prodpage_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable it to display an additional text and/or image under the modified price in the product page'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Add the additional text in the product page'),
                    'name' => 'addit_text_prodpage',
                    'id' => 'html_content_prodpage',
                    'required' => false,
                    'lang' => true,
                    'cols' => 8,
                    'rows' => 4,
                    'class' => 'toggle_show_text_prodpage',
                    'autoload_rte' => 'rte',
                    'desc' => $this->l('Additional text and/or image in the product page. You can use HTML code, CSS and upload an image'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Skip other rules with lower priority'),
                    'name' => 'first_condition',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'first_condition_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'first_condition_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable to skip the other rules with lower priority'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Use in Admin (Backoffice)'),
                    'name' => 'backoffice',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'backoffice_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'backoffice_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Use this configuration in Prestashop Backoffice to show the prices of products modified with this conditions'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Priority'),
                    'name' => 'priority',
                    'default' => '1',
                    'col' => '8',
                    'desc' => $this->l('Set priority when 2 or more configurations overlaps. Configuration with less number will have more priority'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
        );

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product filters'),
                'icon' => 'icon-edit'
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Filter by prices'),
                    'name' => 'filter_prices',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'filter_prices_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'filter_prices_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Filter by prices range. Example: Apply the rule to products with Retail price with taxes between 10€ (min.) and 50€ (max.)'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Product price'),
                    'name' => 'fp_product_price',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'fp_product_price_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'fp_product_price_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable to include the product price in the threshold calculation'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Attribute price'),
                    'name' => 'fp_attribute_price',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'fp_attribute_price_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'ffp_attribute_price_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable to include the attribute price (combination) in the threshold calculation'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Price from calculate the minimum and maximum'),
                    'name' => 'threshold_price',
                    'class' => 'toggle_filter_prices',
                    'col' => '8',
                    'options' => array(
                        'query' => $price_options,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Select the price which the prices range will be calculated. Example: Apply the rule to products with Retail price with taxes prices between 10€ (min.) and 50€ (max.)'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Threshold minimum price'),
                    'name' => 'threshold_min_price',
                    'class' => 'toggle_filter_prices',
                    'col' => '8',
                    'default' => '0',
                    'desc' => sprintf($this->l('Minimum price to set a less Threshold price (%s) to apply the rule. If this value is 0 there\'s no minimum limit'), ($this->taxes_included) ? $this->l('taxes included') : $this->l('without taxes')),
                    'prefix' => $this->context->currency->sign
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Threshold maximum price'),
                    'name' => 'threshold_max_price',
                    'class' => 'toggle_filter_prices',
                    'col' => '8',
                    'default' => '0',
                    'desc' => sprintf($this->l('Maximum price to set a top Threshold price (%s) to apply the rule. If this value is 0 there\'s no maximum limit'), ($this->taxes_included) ? $this->l('taxes included') : $this->l('without taxes')),
                    'prefix' => $this->context->currency->sign
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Filter by stock'),
                    'name' => 'filter_stock',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'filter_stock_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'filter_stock_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Filter by stock range. For example: Apply the rule to the products with stock quantity between 50 and 100'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Products with stock from'),
                    'name' => 'min_stock',
                    'class' => 'toggle_filter_stock',
                    'col' => '8',
                    'default' => '0',
                    'desc' => $this->l('Apply rule to products with stock from'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Products with stock until'),
                    'name' => 'max_stock',
                    'class' => 'toggle_filter_stock',
                    'col' => '8',
                    'default' => '0',
                    'desc' => $this->l('Apply rule to products with stock until'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Filter by weight'),
                    'name' => 'filter_weight',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'filter_weight_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'filter_weight_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Filter by weight range. For example: Apply the rule to the products with weight between 0 and 6'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Products with weight from'),
                    'name' => 'min_weight',
                    'class' => 'toggle_filter_weight',
                    'col' => '8',
                    'default' => '0',
                    'desc' => $this->l('Apply rule to products with weight from'),
                    'prefix' => Configuration::get('PS_WEIGHT_UNIT')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Products with weight until'),
                    'name' => 'max_weight',
                    'class' => 'toggle_filter_weight',
                    'col' => '8',
                    'default' => '0',
                    'desc' => $this->l('Apply rule to products with weight until'),
                    'prefix' => Configuration::get('PS_WEIGHT_UNIT')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Product quantity units'),
                    'name' => 'product_qty',
                    'col' => '8',
                    'default' => '0',
                    'desc' => sprintf($this->l('Creates a quantity discount. Minimum quantity units of products in the cart')),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Cart Amount'),
                    'name' => 'cart_amount',
                    'col' => '8',
                    'default' => '0',
                    'prefix' => $this->context->currency->sign,
                    'desc' => sprintf($this->l('Define the total cart amount to apply the rule')),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Grouped by'),
                    'name' => 'grouped_by',
                    'class' => 't',
                    'col' => '8',
                    'options' => array(
                        'query' => $group_options,
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => '',
                            'label' => $this->l('-- Choose --')
                        )
                    ),
                    'desc' => $this->l('Grouped products criteria (cart total amount or units quantity) grouped by condition. Example: 10 units to apply discount, 5 units from product A and 5 units from product B'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Compare only from default category'),
                    'name' => 'cat_default',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'cat_default_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'cat_default_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable if you want to apply only if the category selected is the default category of the product'),
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
        );


        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $selected_categories = array();
            if ($giconf->categories != '') {
                if (@unserialize($giconf->categories) !== false) {
                    $selected_categories = unserialize($giconf->categories);
                } else {
                    $selected_categories = explode(';', $giconf->categories);
                }
            }

            $categories_form_array = array(
               'type'  => 'categories',
               'label' => $this->l('Select Category(s)'),
               'multiple' => true,
               'name'  => 'categories',
               'col' => '8',
               'tree'  => array(
                    'id' => 'id_category',
                    'use_checkbox' => true,
                    'selected_categories' => $selected_categories,
                    ),
                'desc' => $this->l('Select the Category(es) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Categories'),
            );
        } else {
            $categories_form_array = array(
                'type' => 'swap-custom',
                'label' => $this->l('Select Category(s)'),
                'class' => 'switch_categories',
                'name'  => 'categories[]',
                'multiple' => true,
                'required' => false,
                'search' => true,
                'col' => '2',
                'options' => array(
                    'query' => $categories,
                    'id' => 'id_category',
                    'name' => 'name'
                ),
                'desc' => $this->l('Select the Category(es) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Categories'),
            );
        }

        array_push($this->fields_form[1]['form']['input'], $categories_form_array);

        $render_form_pr = array();

        if (Configuration::get('GROUPINC_USE_PRODUCTS')) {
            $render_form_pr[] =
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Product(s)'),
                    'name' => 'products[]',
                    'class' => 'switch_products',
                    'multiple' => true,
                    'search' => true,
                    'required' => false,
                    'col' => '8',
                    'options' => array(
                        'query' => $products,
                        'id' => 'id_product',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select the Product(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Products'),
                );
        }

        $render_form_pr[] =
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Select Manufacturer(s)'),
                'name' => 'manufacturers[]',
                'class' => 'switch_manufacturers',
                'multiple' => true,
                'search' => true,
                'required' => false,
                'col' => '8',
                'options' => array(
                    'query' => $manufacturers,
                    'id' => 'id_manufacturer',
                    'name' => 'name'
                ),
                'desc' => $this->l('Select the Manufacturer(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Manufacturers'),
            );
        $render_form_pr[] =
            array(
                'type' => 'swap-custom',
                'label' => $this->l('Select Supplier(s)'),
                'name' => 'suppliers[]',
                'class' => 'switch_suppliers',
                'multiple' => true,
                'search' => true,
                'required' => false,
                'col' => '8',
                'options' => array(
                    'query' => $suppliers,
                    'id' => 'id_supplier',
                    'name' => 'name'
                ),
                'desc' => $this->l('Select the Supplier(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Suppliers'),
            );


        if (Configuration::get('GROUPINC_USE_FEATURES')) {
            $features = Feature::getFeatures((int)$id_lang);
            $array_features = array();

            foreach ($features as $key => $feature) {
                if ($feature['name']) {
                    $feature_values = FeatureValue::getFeatureValuesWithLang((int)$id_lang, $feature['id_feature']);
                    if (!empty($feature_values)) {
                        $array_features[] = array(
                            'type' => 'swap-custom',
                            'label' => $this->l('Select').' '.$feature['name'],
                            'name' => 'feature_'.$feature['id_feature'].'[]',
                            'multiple' => true,
                            'required' => false,
                            'search' => true,
                            'class' => 'switch_features',
                            'col' => '8',
                            'options' => array(
                                'query' => $feature_values,
                                'id' => 'id_feature_value',
                                'name' => 'value'
                            ),
                            'desc' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Select the ').' '.$feature['name'].' '.$this->l('to apply this configuration') : '',
                        );
                    }
                }
            }

            foreach ($array_features as $f) {
                array_push($render_form_pr, $f);
            }
        }

        if (Configuration::get('GROUPINC_USE_ATTRIBUTES')) {
            $attributeGroups = AttributeGroup::getAttributesGroups((int)$id_lang);

            $array_attributes = array();
            foreach ($attributeGroups as $key => $attributeGroup) {
                if ($attributeGroup['name']) {
                    if (!empty($attributeGroups)) {
                        $array_attributes[] = array(
                            'type' => 'swap-custom',
                            'label' => $this->l('Select').' '.$attributeGroup['name'],
                            'name' => 'attribute_'.$attributeGroup['id_attribute_group'].'[]',
                            'multiple' => true,
                            'required' => false,
                            'search' => true,
                            'class' => 'switch_attributes',
                            'col' => '8',
                            'options' => array(
                                'query' => AttributeGroup::getAttributes((int)$id_lang, $attributeGroup['id_attribute_group']),
                                'id' => 'id_attribute',
                                'name' => 'name'
                            ),
                            'desc' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Select the ').' '.$attributeGroup['name'].' '.$this->l('to apply this configuration') : '',
                        );
                    }
                }
            }

            foreach ($array_attributes as $a) {
                array_push($render_form_pr, $a);
            }
        }

        foreach ($render_form_pr as $f) {
            array_push($this->fields_form[1]['form']['input'], $f);
        }

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Destination filters (target)'),
                'icon' => 'icon-globe'
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Compare only from default group'),
                    'name' => 'group_default',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'group_default_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'group_default_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable if you want to apply only if the group selected is the default group of the customer'),
                ),
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Customer group(s)'),
                    'name' => 'groups[]',
                    'class' => 'switch_groups',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $groups,
                        'id' => 'id_group',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select the Customer Group(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Groups'),
                ),
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Currency(es)'),
                    'name' => 'currencies[]',
                    'class' => 'switch_currencies',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $currencies,
                        'id' => 'id_currency',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Currency(es) to apply this configuration'),
                ),
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Language(s)'),
                    'name' => 'languages[]',
                    'class' => 'switch_languages',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $languages,
                        'id' => 'id_lang',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select the Language(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Languages'),
                ),
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Country(s)'),
                    'name' => 'countries[]',
                    'class' => 'switch_countries',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $countries,
                        'id' => 'id_country',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select the Country(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Countries'),
                ),
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Zone(s)'),
                    'name' => 'zones[]',
                    'class' => 'switch_zones',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $zones,
                        'id' => 'id_zone',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Select the Zone(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Zones'),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
        );

        $render_form_end = array();
        if (Configuration::get('GROUPINC_USE_CUSTOMERS')) {
            $render_form_end[] =
                array(
                    'type' => 'swap-custom',
                    'label' => $this->l('Select Customer(s)'),
                    'name' => 'customers[]',
                    'class' => 'switch_customers',
                    'multiple' => true,
                    'required' => false,
                    'search' => true,
                    'col' => '8',
                    'options' => array(
                        'query' => $customers,
                        'id' => 'id_customer',
                        'name' => 'email'
                    ),
                    'desc' => $this->l('Select the Customer(s) where the rule will be applied. If you don\'t select any value, the rule will be applied to all Customers'),
                );
        }

        // add the final part of the form
        foreach ($render_form_end as $f) {
            array_unshift($this->fields_form[2]['form']['input'], $f);
        }

        if (Configuration::get('GROUPINC_USE_EXCLUSION')) {
            $this->fields_form[]['form'] = array(
                'legend' => array(
                    'title' => $this->l('Excluded products and customers'),
                    'icon' => 'icon-calendar'
                ),
                'input' => array(
                    array(
                        'type' => 'swap-custom',
                        'label' => $this->l('Select Excluded Product(s)'),
                        'name' => 'products_excluded[]',
                        'class' => 'switch_products_excluded',
                        'multiple' => true,
                        'required' => false,
                        'search' => true,
                        'col' => '8',
                        'options' => array(
                            'query' => $products,
                            'id' => 'id_product',
                            'name' => 'name'
                        ),
                        'desc' => $this->l('Select the Product(s) where the rule will not be applied. Only the products selected will be excluded'),
                    ),
                    array(
                        'type' => 'swap-custom',
                        'label' => $this->l('Select Excluded Customer(s)'),
                        'name' => 'customers_excluded[]',
                        'class' => 'switch_customers_excluded',
                        'multiple' => true,
                        'required' => false,
                        'search' => true,
                        'col' => '8',
                        'options' => array(
                            'query' => $customers,
                            'id' => 'id_customer',
                            'name' => 'email'
                        ),
                        'desc' => $this->l('Select the Customer(s) where the rule will not be applied. Only the customers selected will be excluded.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                ),
            );
        }

        $this->fields_form[]['form'] = array(
            'legend' => array(
                'title' => $this->l('Schedule and countdown'),
                'icon' => 'icon-calendar'
            ),
            'input' => array(
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable countdown in products list'),
                    'name' => 'countdown',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'countdown_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'countdown_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable countdown to display in the products (list and blocks) the offers remaining time (Ends in 01d 12h 30m 45s)'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Countdown style in products list'),
                    'name' => 'cd_style',
                    'id' => 'html_content_cd_prodpage',
                    'required' => false,
                    'lang' => true,
                    'cols' => 8,
                    'rows' => 4,
                    'class' => 'toggle_countdown',
                    'default_value' => '<p style="text-align: center;">%countdown%</p>',
                    'autoload_rte' => 'rte',
                    'desc' => $this->l('Configure the countdown style in in the product lists and blocks (keep the %countdown% to maintain the correct behavior). You can use HTML and CSS'),
                ),
                array(
                    'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                    'label' => $this->l('Enable countdown in product page'),
                    'name' => 'countdown_prodpage',
                    'class' => 't',
                    'col' => '8',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'countdown_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'countdown_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'desc' => $this->l('Enable countdown to display in the product page the offers remaining time (Ends in 01d 12h 30m 45s)'),
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Countdown style in product page'),
                    'name' => 'cd_style_prodpage',
                    'id' => 'html_content_cd',
                    'required' => false,
                    'lang' => true,
                    'cols' => 8,
                    'rows' => 4,
                    'class' => 'toggle_countdown_prodpage',
                    'autoload_rte' => 'rte',
                    'default_value' => '<p style="text-align: center;">%countdown%</p>',
                    'desc' => $this->l('Configure the countdown style in product page (keep the %countdown% to maintain the correct behavior). You can use HTML and CSS'),
                ),
                array(
                    'type' =>  (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'datetime' : 'date',
                    'label' => $this->l('Date From'),
                    'name' => 'date_from',
                    'col' => '7',
                    'desc' => $this->l('Date from which the rule is valid. You can use hours, minutes and secons. Example: 2019-02-20 is considered 2019-02-20 00:00:00 and it means that the rule is valid from 2019-02-20 00:00:00'),
                ),
                array(
                    'type' =>  (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'datetime' : 'date',
                    'label' => $this->l('Date To'),
                    'name' => 'date_to',
                    'col' => '7',
                    'desc' => $this->l('Date to which the rule is valid. You can use hours, minutes and secons. Example: 2019-02-20 is considered 2019-02-20 00:00:00 and it means that the rule is valid until 2019-02-19 23:59:59'),
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Schedule'),
                    'name' => 'schedule',
                    'hint' => $this->l('Select days of week and hours to apply the rule (Click on the box to enable or disable the day and define the time range)')
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'type' => 'submit',
            ),
        );

        //Load db values for select inputs
        if ($giconf->id) {
            $features_decoded_array = Tools::jsonDecode($giconf->features);
            $attributes_decoded_array = Tools::jsonDecode($giconf->attributes);

            $this->fields_value = array(
                'customers[]' => explode(';', $giconf->customers),
                'customers_excluded[]' => explode(';', $giconf->customers_excluded),
                'products[]' => explode(';', $giconf->products),
                'products_excluded[]' => explode(';', $giconf->products_excluded),
                'groups[]' => explode(';', $giconf->groups),
                'zones[]' => explode(';', $giconf->zones),
                'countries[]' => explode(';', $giconf->countries),
                'manufacturers[]' => explode(';', $giconf->manufacturers),
                'suppliers[]' => explode(';', $giconf->suppliers),
                'currencies[]' => explode(';', $giconf->currencies),
                'languages[]' => explode(';', $giconf->languages),
            );

            $i = 0;
            if (!empty($features_decoded_array)) {
                foreach ($features_decoded_array as $key => $feature) {
                    $feature_values['feature_'.$key.'[]'] = explode(';', $feature);
                    $i++;
                }

                foreach ($feature_values as $key => $f) {
                    $this->fields_value[$key] = $f;
                }
            }

            $i = 0;
            $attribute_values = array();
            if (!empty($attributes_decoded_array)) {
                foreach ($attributes_decoded_array as $key => $attribute) {
                    $attribute_values['attribute_'.$key.'[]'] = explode(';', $attribute);
                    $i++;
                }

                foreach ($attribute_values as $key => $f) {
                    $this->fields_value[$key] = $f;
                }
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $this->fields_value['categories[]'] = explode(';', $giconf->categories);
            }
            $this->context->smarty->assign(array(
                'schedule' => $giconf->schedule,
            ));
        } else {
            //Initialize empty values
            $this->fields_value['customers[]'] = array();
            $this->fields_value['customers_excluded[]'] = array();
            $this->fields_value['groups[]'] = array();
            $this->fields_value['currencies[]'] = array();
            $this->fields_value['products[]'] = array();
            $this->fields_value['products_excluded[]'] = array();
            $this->fields_value['suppliers[]'] = array();
            $this->fields_value['manufacturers[]'] = array();
            $this->fields_value['categories[]'] = array();
            $this->fields_value['attributes[]'] = array();
            $this->fields_value['features[]'] = array();
            $this->fields_value['countries[]'] = array();
            $this->fields_value['zones[]'] = array();
            
            $this->context->smarty->assign(array(
                'schedule' => '',
            ));
        }

        $this->fields_value['schedule'] =  $this->context->smarty->fetch($this->module->getLocalPath().'views/templates/admin/schedule.tpl');

        $this->content .= parent::renderForm();
        return;
    }

    protected function renderGlobalConfigForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = new Groupinc();
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = self::$currentIndex;
        $helper->submit_action = 'submitGroupincModuleGlobalConfig';
        $helper->token = Tools::getAdminTokenLite($this->tabClassName);
        $helper->tpl_vars = array(
            'fields_value' => $this->getGlobalConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getGlobalConfigForm()));
    }

    protected function getGlobalConfigForm()
    {
        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Global settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_PRIORIZE_MIN',
                        'label' => $this->l('Display the minimum price'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you want to analyze the rules and apply only the rule better for customer (minimum price result)') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you want to analyze the rules and apply only the rule better for customer (minimum price result)') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_PRIORIZE_MIN_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_PRIORIZE_MIN_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_USE_PRODUCTS',
                        'label' => $this->l('Enable products filter'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you need to create configuration rules using the products filter') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you need to create configuration rules using the products filter') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_USE_PRODUCTS_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_USE_PRODUCTS_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_USE_CUSTOMERS',
                        'label' => $this->l('Enable customers filter'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you need to create configuration rules using the customers filter') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you need to create configuration rules using the customers filter') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_USE_CUSTOMERS_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_USE_CUSTOMERS_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_USE_FEATURES',
                        'label' => $this->l('Enable features filter'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you need to create configuration rules using the features filter') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you need to create configuration rules using the features filter') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_USE_FEATURES_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_USE_FEATURES_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_USE_ATTRIBUTES',
                        'label' => $this->l('Enable attributes filter'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you need to create configuration rules using the attributes filter') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you need to create configuration rules using the attributes filter') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_USE_ATTRIBUTES_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_USE_ATTRIBUTES_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 5,
                        'type' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'switch' : 'radio',
                        'name' => 'GROUPINC_USE_EXCLUSION',
                        'label' => $this->l('Enable exclusion filters (products and customers)'),
                        'class' => 't',
                        'default_value' => true,
                        'desc' => (version_compare(_PS_VERSION_, '1.6', '<')) ? $this->l('Enable if you need to create configuration rules using the exclusions feature. Enabled you will see a new section with products and customers filters to select specific products and/or customers. Useful if you want to create massive rules but exclude specific products and/or customers.') : '',
                        'hint' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? $this->l('Enable if you need to create configuration rules using the exclusions feature. Enabled you will see a new section with products and customers filters to select specific products and/or customers. Useful if you want to create massive rules but exclude specific products and/or customers.') : '',
                        'values' => array(
                            array(
                                'id' => 'GROUPINC_USE_EXCLUSION_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'GROUPINC_USE_EXCLUSION_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'type' => 'submit',
                    'class' => (version_compare(_PS_VERSION_, '1.6', '>=')) ? 'btn btn-default pull-right' : 'button big',
                    'name' => 'submitGroupincModuleGlobalConfig',
                ),
            ),
        );

        return $form;
    }

    protected function getGlobalConfigFormValues()
    {
        return array(
            'GROUPINC_PRIORIZE_MIN' => Configuration::get('GROUPINC_PRIORIZE_MIN'),
            'GROUPINC_USE_FEATURES' => Configuration::get('GROUPINC_USE_FEATURES'),
            'GROUPINC_USE_ATTRIBUTES' => Configuration::get('GROUPINC_USE_ATTRIBUTES'),
            'GROUPINC_USE_PRODUCTS' => Configuration::get('GROUPINC_USE_PRODUCTS'),
            'GROUPINC_USE_CUSTOMERS' => Configuration::get('GROUPINC_USE_CUSTOMERS'),
            'GROUPINC_USE_EXCLUSION' => Configuration::get('GROUPINC_USE_EXCLUSION'),
        );
    }

    public function renderView()
    {
        return parent::renderView();
    }

    public function processDelete()
    {
        parent::processDelete();
    }

    public function processSave()
    {
        if (Tools::getValue('submitFormAjax')) {
            $this->redirect_after = false;
        }

        if ($this->_formValidations()) {
            $_POST['groups'] = (!Tools::getValue('groups')) ? '' : implode(';', Tools::getValue('groups'));
            $_POST['countries'] = (!Tools::getValue('countries')) ? '' : implode(';', Tools::getValue('countries'));
            $_POST['zones'] = (!Tools::getValue('zones')) ? '' : implode(';', Tools::getValue('zones'));
            $_POST['manufacturers'] = (!Tools::getValue('manufacturers')) ? '' : implode(';', Tools::getValue('manufacturers'));
            $_POST['suppliers'] = (!Tools::getValue('suppliers')) ? '' : implode(';', Tools::getValue('suppliers'));
            $_POST['customers'] = (!Tools::getValue('customers')) ? '' : implode(';', Tools::getValue('customers'));
            $_POST['customers_excluded'] = (!Tools::getValue('customers_excluded')) ? '' : implode(';', Tools::getValue('customers_excluded'));
            $_POST['products'] = (!Tools::getValue('products')) ? '' : implode(';', Tools::getValue('products'));
            $_POST['products_excluded'] = (!Tools::getValue('products_excluded')) ? '' : implode(';', Tools::getValue('products_excluded'));
            $_POST['currencies'] = (!Tools::getValue('currencies')) ? '' : implode(';', Tools::getValue('currencies'));
            $_POST['languages'] = (!Tools::getValue('languages')) ? '' : implode(';', Tools::getValue('languages'));
            $_POST['attributes'] = (!Tools::getValue('attributes')) ? '' : implode(';', Tools::getValue('attributes'));
            $_POST['features'] = (!Tools::getValue('features')) ? '' : implode(';', Tools::getValue('features'));

            $languages = Language::getLanguages(true, Context::getContext()->shop->id);
            foreach ($languages as $lang) {
                if (strpos(Tools::getValue('cd_style_'.$lang['id_lang']), '%countdown%') === false) {
                    $_POST['cd_style_'.$lang['id_lang']] = '<p style="text-align: center;">%countdown%</p>';
                }
                if (strpos(Tools::getValue('cd_style_prodpage_'.$lang['id_lang']), '%countdown%') === false) {
                    $_POST['cd_style_prodpage_'.$lang['id_lang']] = '<p style="text-align: center;">%countdown%</p>';
                }
            }

            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                if (Tools::isSubmit('categories')) {
                    $cats = Tools::getValue('categories');
                    $_POST['categories'] = serialize($cats);
                } else {
                    $_POST['categories'] = '';
                }
            } else {
                $_POST['categories'] = (!Tools::isSubmit('categories')) ? '' : implode(';', Tools::getValue('categories'));
            }

            if (Tools::getValue('type') == 1) {
                $_POST['fix'] = 0;
            } else if (Tools::getValue('type') == 0) {
                $_POST['percentage'] = 0;
            }

            if (Tools::getValue('mode') >= 3) {
                $_POST['percentage'] = 0;
            }

            return parent::processSave();
        }
    }

    public function processDuplicate()
    {
        $id_conf = Tools::getValue($this->identifier);
        $conf = new GroupincConfiguration($id_conf);
        unset($conf->id_groupinc_configuration);

        if (!$conf->add()) {
            $this->errors[] = Tools::displayError('An error occurred while duplicating the report #'.$id_conf);
        } else {
            $this->confirmations[] = sprintf($this->l('Rule #%s - %s successfully duplicated.'), $id_conf, $conf->name);
            $this->afterUpdate($conf, $conf->id);
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return Tools::redirectAdmin('index.php?tab='.$this->tabClassName.'&token='.Tools::getAdminTokenLite($this->tabClassName));
            } else {
                return Tools::redirectAdmin('index.php?controller='.$this->tabClassName.'&token='.Tools::getAdminTokenLite($this->tabClassName));
            }
        }
    }

    protected function afterAdd($object)
    {
        $id = Tools::getValue('id_groupinc_configuration');
        $this->afterUpdate($object, $id);
        return true;
    }

    protected function afterUpdate($object, $id = false)
    {
        if ($id) {
            $giconf = new GroupincConfiguration((int)$id);
        } else {
            $giconf = new GroupincConfiguration((int)$object->id);
        }
        if (Validate::isLoadedObject($giconf)) {
            $features = Feature::getFeatures((int)$this->context->cookie->id_lang);
            $attributeGroups = AttributeGroup::getAttributesGroups((int)$this->context->cookie->id_lang);

            $array_features_result = array();
            $array_attributes_result = array();

            foreach ($features as $f) {
                if (Tools::getValue('feature_'.$f['id_feature'])) {
                    $array_features_result[$f['id_feature']] = implode(';', Tools::getValue('feature_'.$f['id_feature']));
                }
            }

            foreach ($attributeGroups as $a) {
                if (Tools::getValue('attribute_'.$a['id_attribute_group'])) {
                    $array_attributes_result[$a['id_attribute_group']] = implode(';', Tools::getValue('attribute_'.$a['id_attribute_group']));
                }
            }
            if (!empty($array_features_result)) {
                $giconf->features = Tools::jsonEncode($array_features_result);
            } else {
                $giconf->features = '';
            }

            if (!empty($array_attributes_result)) {
                $giconf->attributes = Tools::jsonEncode($array_attributes_result);
            } else {
                $giconf->attributes = '';
            }
            $giconf->save();
        }
        return true;
    }

    /**
     * @param string $token
     * @param int $id
     * @param string $name
     * @return mixed
     */
    public function displayDeleteLink($token = null, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

        $tpl->assign(array(
            'href' => self::$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token != null ? $token : $this->token),
            'confirm' => $this->l('Delete the selected item?').$name,
            'action' => $this->l('Delete'),
            'id' => $id,
        ));

        return $tpl->fetch();
    }

    protected function getTypes()
    {
        //$types = array($this->l('Fix'), $this->l('Percentage'), $this->l('Fix + Percentage'), $this->l('Percentage + Fix'));
        $types = array($this->l('Fix'), $this->l('Percentage'), $this->l('Fix + Percentage'));

        $list_types = array();
        foreach ($types as $key => $type) {
            $list_types[$key]['id'] = $key;
            $list_types[$key]['value'] = $key;
            $list_types[$key]['name'] = $type;
        }
        return $list_types;
    }

    protected function getModes()
    {
        $modes = array($this->l('Increment'), $this->l('Reduction'), $this->l('Set Fixed price'), $this->l('Free tax'), $this->l('Add tax'));

        $list_modes = array();
        foreach ($modes as $key => $mode) {
            $list_modes[$key]['id'] = $key;
            $list_modes[$key]['value'] = $key;
            $list_modes[$key]['name'] = $mode;
        }
        return $list_modes;
    }

    public function getType($type)
    {
        if ($type == '0') {
            return $this->l('Fix');
        } elseif ($type == '1') {
            return $this->l('Percentage');
        } else if ($type == '2')
            return $this->l('Fix + Percentage');
    }

    public function getMode($mode)
    {
        if ($mode == '0') {
            return $this->l('Increment');
        } elseif ($mode == '1') {
            return $this->l('Reduction');
        }  elseif ($mode == '2') {
             return $this->l('Fixed Price');
        }  elseif ($mode == '3') {
            return $this->l('Free Tax');
        }  elseif ($mode == '4') {
            return $this->l('Add Tax');
        }
    }

    public function getGroupOptions()
    {
        $group_options = array($this->l('No grouped (individually)'), $this->l('Product'), $this->l('Category'), $this->l('Manufacturers'), $this->l('Suppliers'));

        $list_group_options = array();
        foreach ($group_options as $key => $mode) {
            $list_group_options[$key]['id'] = $key;
            $list_group_options[$key]['value'] = $key;
            $list_group_options[$key]['name'] = $mode;
        }
        return $list_group_options;
    }

    public function getPriceOptions($calculation = false)
    {
        $price_options = array($this->l('Wholesale Price without Taxes'), $this->l('Retail Price Without Taxes'), $this->l('Wholesale Price with Taxes'), $this->l('Retail Price with Taxes'), $this->l('Supplier Price without Taxes'), $this->l('Supplier Price with Taxes'));

        if ($calculation) {
            array_push($price_options, $this->l('Benefits margin without taxes (Retail - Wholesale)'));
            array_push($price_options, $this->l('Benefits margin with taxes (Retail - Wholesale)'));
        }

        $list_price_options = array();
        foreach ($price_options as $key => $mode) {
            $list_price_options[$key]['id'] = $key;
            $list_price_options[$key]['value'] = $key;
            $list_price_options[$key]['name'] = $mode;
        }
        return $list_price_options;
    }

    public function getCustomerGroups($ids_customer_groups)
    {
        if ($ids_customer_groups === '' || $ids_customer_groups === 'all') {
            return $this->l('All');
        }
        $groups = array();
        $groups_array = explode(';', $ids_customer_groups);
        foreach ($groups_array as $key => $group) {
            if ($key == $this->top_elements_in_list) {
                $groups[] = $this->l('...and more');
                break;
            }
            $group = new Group($group, $this->context->language->id);
            $groups[] = $group->id .' - '.$group->name;
        }
        return implode('<br />', $groups);
    }

    public function getCountries($ids_countries)
    {
        if ($ids_countries === '' || $ids_countries === 'all') {
            return $this->l('All');
        }
        $countries = array();
        $countries_array = explode(';', $ids_countries);
        foreach ($countries_array as $key => $country) {
            if ($key == $this->top_elements_in_list) {
                $countries[] = $this->l('...and more');
                break;
            }
            $country = new Country($country, $this->context->language->id);
            $countries[] = $country->name;
        }
        return implode('<br />', $countries);
    }

    public function getZones($ids_zones)
    {
        if ($ids_zones === '' || $ids_zones === 'all') {
            return $this->l('All');
        }
        $zones = array();
        $zones_array = explode(';', $ids_zones);
        foreach ($zones_array as $key => $zone) {
            if ($key == $this->top_elements_in_list) {
                $zones[] = $this->l('...and more');
                break;
            }
            $zone = new Zone($zone, $this->context->language->id);
            $zones[] = $zone->name;
        }
        return implode('<br />', $zones);
    }

    public function getSuppliers($ids_suppliers)
    {
        if ($ids_suppliers === '' || $ids_suppliers === 'all') {
            return $this->l('All');
        }

        $suppliers = array();
        $suppliers_array = explode(';', $ids_suppliers);
        foreach ($suppliers_array as $key => $supplier) {
            if ($key == $this->top_elements_in_list) {
                $suppliers[] = $this->l('...and more');
                break;
            }
            $supplier = new Supplier($supplier);
            $suppliers[] = $supplier->name;
        }
        return implode('<br />', $suppliers);
    }


    public function getCategories($ids_categories)
    {
        if ($ids_categories === '' || $ids_categories === 'all') {
            return $this->l('All');
        }

        $categories = array();

        if (@unserialize($ids_categories) !== false) {
            $categories_array = unserialize($ids_categories);
        } else {
            $categories_array = explode(';', $ids_categories);
        }

        foreach ($categories_array as $key => $category) {
            if ($key == $this->top_elements_in_list) {
                $categories[] = $this->l('...and more');
                break;
            }
            $category = new Category($category, $this->context->language->id);
            $categories[] = $category->id.' - '.$category->name;
        }
        return implode('<br />', $categories);
    }

    public function getCurrencies($ids_currencies)
    {
        if ($ids_currencies === '' || $ids_currencies === 'all') {
            return $this->l('All');
        }
        $currencies = array();
        $currencies_array = explode(';', $ids_currencies);
        foreach ($currencies_array as $key => $currency) {
            if ($key == $this->top_elements_in_list) {
                $currencies[] = $this->l('...and more');
                break;
            }
            $currency = new Currency($currency);
            $currencies[] = $currency->name;
        }
        return implode('<br />', $currencies);
    }

    public function getProducts($ids_products)
    {
        if ($ids_products === '' || $ids_products === 'all') {
            return $this->l('All');
        }
        $products = array();
        $products_array = explode(';', $ids_products);
        foreach ($products_array as $key => $product) {
            if ($key == $this->top_elements_in_list) {
                $products[] = $this->l('...and more');
                break;
            }
            $product = new Product($product, $this->context->language->id);
            $products[] = $product->id.' - '.Tools::substr($product->name[$this->context->language->id], 0, 9).'..';
        }
        return implode('<br />', $products);
    }

    public function getCustomers($ids_customers)
    {
        if ($ids_customers === '' || $ids_customers === 'all') {
            return $this->l('All');
        }
        $customers = array();
        $customers_array = explode(';', $ids_customers);
        foreach ($customers_array as $key => $customer) {
            if ($key == $this->top_elements_in_list) {
                $customers[] = $this->l('...and more');
                break;
            }
            $customer = new Customer($customer, $this->context->language->id);
            $customers[] = $customer->firstname.' '.$customer->lastname;
        }
        return implode('<br />', $customers);
    }

    private function _createTemplate($tpl_name)
    {
        if ($this->override_folder) {
            if ($this->context->controller instanceof ModuleAdminController) {
                $override_tpl_path = $this->context->controller->getTemplatePath().$tpl_name;
            } elseif ($this->module) {
                $override_tpl_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/'.$tpl_name;
            } else {
                if (file_exists($this->context->smarty->getTemplateDir(1).DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name)) {
                    $override_tpl_path = $this->context->smarty->getTemplateDir(1).DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name;
                } elseif (file_exists($this->context->smarty->getTemplateDir(0).DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name)) {
                    $override_tpl_path = $this->context->smarty->getTemplateDir(0).'controllers'.DIRECTORY_SEPARATOR.$this->override_folder.$this->base_folder.$tpl_name;
                }
            }
        } else if ($this->module) {
            $override_tpl_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/'.$tpl_name;
        }
        if (isset($override_tpl_path) && file_exists($override_tpl_path)) {
            return $this->context->smarty->createTemplate($override_tpl_path, $this->context->smarty);
        } else {
            return $this->context->smarty->createTemplate($tpl_name, $this->context->smarty);
        }
    }

    private function _formValidations()
    {
        if (trim(Tools::getValue('name')) == '') {
            $this->validateRules();
            $this->errors[] = Tools::displayError($this->l('Field Name can not be empty.'));
            $this->display = 'edit';
        }

        if (Tools::getValue('date_from') > 0) {
            if (!Validate::isDate(Tools::getValue('date_from'))) {
                $this->errors[] = $this->l('Invalid "Date From" format');
                $this->display = 'edit';
            }
        }

        if (Tools::getValue('date_to') > 0) {
            if (!Validate::isDate(Tools::getValue('date_to'))) {
                $this->errors[] = $this->l('Invalid "Date To" format');
                $this->display = 'edit';
            }
        }

        if (count($this->errors)) {
            return false;
        }

        return true;
    }

    public function processChangeBackofficeVal()
    {
        $gi = new GroupincConfiguration($this->id_object);
        if (!Validate::isLoadedObject($gi)) {
            $this->errors[] = Tools::displayError('An error occurred while updating Virtual POS information.');
        }
        $gi->backoffice = $gi->backoffice ? 0 : 1;
        if (!$gi->update()) {
            $this->errors[] = Tools::displayError('An error occurred while updating customer information.');
        }
        Tools::redirectAdmin(self::$currentIndex.'&token='.$this->token);
    }

    public function printValidIcon($value, $gi)
    {
        $today = date("Y-m-d H:i:s");
        $date_title = '';

        if ($gi['date_from'] > $today) {
            $date_title = $this->l("Future rule");
            if ($gi['date_from'] != "0000-00-00 00:00:00") {
                $date_title = $date_title.'. '.$this->l("Begins in:").' '.$gi['date_from'];
            }
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column future-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column future-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        }

        if ($gi['date_to'] == "0000-00-00 00:00:00" || $today < $gi['date_to']) {
            $date_title = $this->l("Valid rule");
            if ($gi['date_from'] != "0000-00-00 00:00:00" && $gi['date_to'] != "0000-00-00 00:00:00") {
                $date_title = $date_title.'. '.$this->l("From:").' '.$gi['date_from'].'. '.$this->l("Until:").' '.$gi['date_to'];
            } else if ($gi['date_from'] != "0000-00-00 00:00:00" && $gi['date_to'] == "0000-00-00 00:00:00") {
                $date_title = $date_title.'. '.$this->l("From:").' '.$gi['date_from'].' ('.$this->l("no expires").')';
            } else if ($gi['date_from'] == "0000-00-00 00:00:00" && $gi['date_to'] != "0000-00-00 00:00:00") {
                $date_title = $date_title.'. '.$this->l("Until:").' '.$gi['date_to'];
            } else {
                $date_title = $date_title.' ('.$this->l("no expires").')';
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column valid-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column valid-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        } else {
            $date_title = $this->l("Expired rule");
            if ($gi['date_from'] != "0000-00-00 00:00:00" && $gi['date_to'] != "0000-00-00 00:00:00") {
                $date_title = $date_title.'. '.$this->l("Between:").' '.$gi['date_from'].' '.$this->l("and:").' '.$gi['date_to'];
            } else {
                $date_title = $date_title.'. '.$this->l("From:").' '.$gi['date_to'];
            }
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                return '<span class="time-column expired-date" title="'.$date_title.'"></span>';
            } else {
                return '<span class="time-column expired-date-icon" title="'.$date_title.'"><i class="icon-time"></i></span>';
            }
        }
    }

    public function printBackofficeIcon($value, $gi)
    {
        return '<a class="list-action-enable '.($value ? 'action-enabled' : 'action-disabled').'" href="index.php?'.htmlspecialchars('tab=AdminGroupinc&id_groupinc_configuration='.(int)$gi['id_groupinc_configuration'].'&changeBackofficeVal&token='.Tools::getAdminTokenLite('AdminGroupinc')).'">
                '.($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>').
            '</a>';
    }

    protected function getProductsLite($id_lang, $only_active = false, $front = false, Context $context = null)
    {
        if (!$context)
            $context = Context::getContext();

        $sql = 'SELECT p.`id_product`, CONCAT(p.`reference`, " - ", pl.`name`) as name FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
                WHERE pl.`id_lang` = '.(int)$id_lang.
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                    ($only_active ? ' AND product_shop.`active` = 1' : '');

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return ($rq);
    }

    protected function getNumProducts($id_lang, $only_active = false, $front = false, Context $context = null)
    {
        if (!$context)
            $context = Context::getContext();

        $sql = 'SELECT count(p.`id_product`) as num_products FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
                WHERE pl.`id_lang` = '.(int)$id_lang.
                    ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').
                    ($only_active ? ' AND product_shop.`active` = 1' : '');

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return ($rq);
    }

    protected function cleanCache() {
        /* delete smarty cache to refresh the static blocks */
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();
        if (version_compare(_PS_VERSION_, '1.7', '<') && version_compare(_PS_VERSION_, '1.4', '>')) {
            PrestaShopAutoload::getInstance()->generateIndex();
        } else if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            Tools::generateIndex();
        }
    }






    public function getConfigurations($id_product)
    {
        $configs = $this->getListConfigurations($id_product);

        if (empty($configs) || !$configs) {
            $configs = array();
        }

        $fields_list = array(
            'id_groupinc_configuration' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name')
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'callback' => 'getType',
                'type' => 'select',
                'list' => array(0 => $this->l('Fix'), 1 => $this->l('Percentage'), 2 => $this->l('Fix + Percentage')),
                'filter_key' => 'a!type',
                'align' => 'text-center'
            ),
            'mode' => array(
                'title' => $this->l('Mode'),
                'callback' => 'getMode',
                'type' => 'select',
                'filter_key' => 'a!mode',
                'list' => array(0 => $this->l('Increment'), 1 => $this->l('Reduction'), 2 => $this->l('Set Fixed price'), 3 => $this->l('Free Tax'), 4 => $this->l('Add Tax')),
                'align' => 'text-center'
            ),
            'fix' => array(
                'title' => $this->l('Fix'),
                'align' => 'text-center'
            ),
            'percentage' => array(
                'title' => $this->l('Percentage'),
                'align' => 'text-center'
            ),
            'customers' => array(
                'title' => $this->l('Customers(s)'),
                'callback' => 'getCustomers',
                'align' => 'text-center'
            ),
            'categories' => array(
                'title' => $this->l('Category(s)'),
                'callback' => 'getCategories',
                'align' => 'text-center'
            ),
            'products' => array(
                'title' => $this->l('Products(s)'),
                'callback' => 'getProducts',
                'align' => 'text-center'
            ),
            'priority' => array(
                'title' => $this->l('Priority'),
                'align' => 'text-center'
            ),
            'backoffice' => array(
                'title' => $this->l('Backoffice'),
                'align' => 'text-center',
                'type' => 'select',
                'list' => array(0 => $this->l('No'), 1 => $this->l('Yes')),
                'callback' => 'printBackofficeIcon',
                'filter_key' => 'a!backoffice'
            ),
            'date_to' => array(
                'title' => $this->l('Valid'),
                'align' => 'text-center',
                'callback' => 'printValidIcon',
            ),
            'active' => array(
                'title' => $this->l('Enabled'),
                'align' => 'text-center',
                'active' => 'status',
                'type' => 'bool',
                'callback' => 'printActiveIcon'
            ),
        );

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $list = '<table class="table tableDnD" cellspacing="0" cellpadding="0"><tr>';
            foreach ($fields_list as $key => $field) {
                $list .= '<th>'.$field['title'].'</th>';
            }
            $list .= '</tr>';

            foreach ($configs as $config) {
                $list .= '<tr>';
                foreach ($fields_list as $key => $field) {
                    $list .= '<td>'.$config[$key].'</td>';
                }

                $list .= '</tr>';
            }

            $list .= '</table>';
        } else {
            $helper_list = new HelperList();
            $helper_list->name = 'GroupincConfiguration';
            $helper_list->module = $this;
            $helper_list->title = $this->l('Groupinc Configs');
            $helper_list->shopLinkType = 'shop';
            $helper_list->no_link = true;
            $helper_list->default_form_language = 1;
            $helper_list->show_toolbar = true;
            $helper_list->simple_header = false;
            $helper_list->identifier = 'id_groupinc_configuration';
            $helper_list->actions = array('edit');
            $helper_list->table = $this->name.'_configuration';
            $helper_list->list_id = $helper_list->table;
            $helper_list->currentIndex = $this->context->link->getAdminLink($this->tabClassName, false).'&updategroupinc_configuration';
            $helper_list->token = Tools::getAdminTokenLite($this->tabClassName);
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $helper_list->toolbar_btn['export'] = array(
                    'href' => $this->context->link->getAdminLink($this->tabClassName),
                    'desc' => $this->l('Export')
                );
            }
            // This is needed for displayEnableLink to avoid code duplication
            $this->_helperlist = $helper_list;
            $helper_list->listTotal = count($configs);
            $helper_list->tpl_vars['icon'] = 'icon-AdminParentOrders';
            $generated_list = $helper_list->generateList($configs, $fields_list);
        }

        return $generated_list;
    }

    private function getListConfigurations($id_product = 0)
    {
        $id_shop = Context::getContext()->shop->id;

        $categories = Product::getProductCategories($id_product);
        $product = new Product($id_product);
        $id_manufacturer = $product->id_manufacturer;
        $product_suppliers_array = ProductSupplier::getSupplierCollection($id_product);
        $id_manufacturer = $product->id_manufacturer;

        $query = '';
        $today = date("Y-m-d H:i:s");

        $query = '
                 SELECT gi.* FROM `'._DB_PREFIX_.'groupinc_configuration` gi ';

        $datefilters = ' WHERE (date_from <= "'.$today. '" OR date_from = "0000-00-00 00:00:00") AND (date_to >= "'.$today.'" OR date_to = "0000-00-00 00:00:00")';

        $query = $query.$datefilters;

        $query = $query.' AND gi.`id_shop` = '.(int)$id_shop.' AND gi.`active` = 1 ';

        $configs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

        if (empty($configs) || $configs === false) {
            return false;
        }

        $configs_priority = array();
        foreach ($configs as $key => $row)
        {
            $configs_priority[$key] = $row['priority'];
        }

        array_multisort($configs_priority, SORT_ASC, $configs);

        $array_configurations_result = array();
        $tax = new Tax();
        $tax->rate = $product->getTaxesRate();
        $tax_calculator = new TaxCalculator(array($tax));

        $wholesale_price_without_taxes = $product->wholesale_price;
        $wholesale_price_with_taxes = $tax_calculator->addTaxes($product->wholesale_price);
        $retail_price_without_taxes = $product->price;
        $retail_price_with_taxes = $tax_calculator->addTaxes($product->price);

        foreach ($configs as $conf) {
            if ($conf['threshold_min_price'] > 0 || $conf['threshold_max_price'] > 0) {
                if ($conf['threshold_price'] == 0) {
                    $price_to_compare = $wholesale_price_without_taxes;
                } else if ($conf['threshold_price'] == 1) {
                    $price_to_compare = $retail_price_without_taxes;
                } else if ($conf['threshold_price'] == 2) {
                    $price_to_compare = $wholesale_price_with_taxes;
                } else if ($conf['threshold_price'] == 3) {
                    $price_to_compare = $retail_price_with_taxes;
                }
            }

            if ($conf['threshold_min_price'] > 0 && $conf['threshold_min_price'] >= $price_to_compare) {
                continue;
            }

            if ($conf['threshold_max_price'] > 0 && $conf['threshold_max_price'] <= $price_to_compare) {
                continue;
            }

            if ($conf['filter_stock']) {
                $stock = Product::getQuantity($id_product);

                if ($stock < $conf['min_stock'] || $stock > $conf['max_stock']) {
                    continue;
                }
            }

            if ($conf['filter_weight']) {
                $weight = $product->weight;
                if ($weight < $conf['min_weight'] || ($conf['max_weight'] > 0 && $weight > $conf['max_weight'])) {
                    continue;
                }
            }

            if (!$conf['filter_store']) {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }

            if ($conf['currencies'] == 'all') {
                $conf['currencies'] = '';
            }
            if ($conf['languages'] == 'all') {
                $conf['languages'] = '';
            }
            if ($conf['groups'] == 'all') {
                $conf['groups'] = '';
            }
            if ($conf['products'] == 'all') {
                $conf['products'] = '';
            }
            if ($conf['customers'] == 'all') {
                $conf['customers'] = '';
            }
            if ($conf['countries'] == 'all') {
                $conf['countries'] = '';
            }
            if ($conf['zones'] == 'all') {
                $conf['zones'] = '';
            }
            if ($conf['categories'] == 'all') {
                $conf['categories'] = '';
            }
            if ($conf['manufacturers'] == 'all') {
                $conf['manufacturers'] = '';
            }
            if ($conf['suppliers'] == 'all') {
                $conf['suppliers'] = '';
            }
            if ($conf['features'] == 'all' || empty($conf['features'])) {
                $conf['features'] = '';
            }
            if ($conf['attributes'] == 'all' || empty($conf['attributes'])) {
                $conf['attributes'] = '';
            }

            if ($conf['attributes'] == '' && $conf['features'] == '' && $conf['products'] == '' && $conf['categories'] == '' && $conf['manufacturers'] == '' && $conf['suppliers'] == '') {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }

            $filter_features = false;
            $array_features_selected = Tools::jsonDecode($conf['features'], true);
            $product_features = Product::getFeaturesStatic((int)$id_product);

            $flag_features = 0;
            if (!empty($array_features_selected) && count($array_features_selected) > 0) {
                foreach ($product_features as $pf) {
                    if (isset($array_features_selected[$pf['id_feature']])) {
                        $array_f = explode(";", $array_features_selected[$pf['id_feature']]);
                        if (in_array($pf['id_feature_value'], $array_f)) {
                            $flag_features++;
                            continue;
                        }
                    }
                }
            } else {
                $filter_features = true;
            }

            if ($flag_features > 0) {
                $filter_features = true;
            }

            $filter_attributes = true;

            $filter_categories = true;
            $filter_products = true;
            if (@unserialize($conf['categories']) !== false) {
                $categories_array = unserialize($conf['categories']);
            } else {
                $categories_array = explode(';', $conf['categories']);
            }

            if ($conf['categories'] !== '' && $conf['products'] == '') {
                foreach ($categories as $category) {
                    if (in_array($category, $categories_array)) {
                        $filter_categories = true;
                        $filter_products = true;
                        break;
                    } else {
                        $filter_categories = false;
                    }
                }
                if (!$filter_categories) {
                    $filter_products = false;
                }
            } else if ($conf['categories'] == '' && $conf['products'] !== '') {

                $products_array = explode(';', $conf['products']);
                if (!in_array($id_product, $products_array)) {
                    $filter_products = false;
                    $filter_categories = true;
                }
            } else if ($conf['categories'] !== '' && $conf['products'] !== '') {
                foreach ($categories as $category) {
                    if (!in_array($category, $categories_array)) {
                        $filter_categories = false;
                    } else {
                        $filter_categories = true;
                        break;
                    }
                }
                if (!$filter_categories) {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    } else {
                        $filter_products = true;
                    }
                } else {
                    $products_array = explode(';', $conf['products']);
                    if (!in_array($id_product, $products_array)) {
                        $filter_products = false;
                    }
                }
            }
            $filter_manufacturers = true;
            if ($conf['manufacturers'] !== '') {
                $manufacturers_array = explode(';', $conf['manufacturers']);
                if (!in_array($id_manufacturer, $manufacturers_array)) {
                    $filter_manufacturers = false;
                }
            }
            $filter_suppliers = true;
            if ($conf['suppliers'] !== '') {
                $filter_suppliers = false;
                $suppliers_array = explode(';', $conf['suppliers']);
                if (!empty($product_suppliers_array)) {
                    foreach ($product_suppliers_array as $ps) {
                        if (in_array($ps->id_supplier, $suppliers_array)) {
                            $filter_suppliers = true;
                            break;
                        }
                    }
                }
            }

            if ($filter_attributes && $filter_features && $filter_categories && $filter_products && $filter_manufacturers && $filter_suppliers) {
                $array_configurations_result[] = $conf;
                if ($conf['first_condition']) {
                    break;
                } else {
                    continue;
                }
            }
        }

        if (count($array_configurations_result) > 0) {
            return $array_configurations_result;
        } else {
            return false;
        }
    }

    public function displayviewproductsLink($token, $id)
    {
        $module = new Groupinc();
        return $module->displayProductsLink($token, $id);
    }

        public function getProductsList($export = false, $id_config = false)
    {
         $config = new GroupincConfiguration($id_config);
         $list_products = GroupincConfiguration::getProductsExport($config);
         $fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID product'),
                'type' => 'text',
                'align' => 'center',
            ),
             'reference' => array(
                 'title' => $this->l('Reference'),
                 'type' => 'text',
                 'align' => 'center',
             ),
            'name' => array(
                'title' => $this->l('Name'),
                'type' => 'text',
                'align' => 'center',
            ),
            'price' => array(
                'title' => $this->l('Price no tax'),
                'type' => 'price',
                'align' => 'center',
             ),
            'whole_without_taxes' => array(
                'title' => $this->l('Wholesale no tax'),
                'type' => 'price',
                'align' => 'center',
             ),
            'price_with_taxes' => array(
                'title' => $this->l('Price'),
                'type' => 'price',
                'align' => 'center',
             ),
            'whole_with_taxes' => array(
                'title' => $this->l('Wholesale'),
                'type' => 'price',
                'align' => 'center',
             ),
             'type' => array(
                 'title' => $this->l('Type'),
                 'type' => 'text',
                 'align' => 'center',
                 'callback' => 'getType'
             ),
             'mode' => array(
                 'title' => $this->l('Mode'),
                 'type' => 'text',
                 'align' => 'center',
                 'callback' => 'getMode'
             ),
             'amount' => array(
                 'title' => $this->l('Amount'),
                 'type' => 'text',
                 'align' => 'center',
             ),
             'new_price' => array(
                 'title' => $this->l('Final price'),
                 'type' => 'price',
                 'align' => 'center',
             ),
        );
        if ($export === true) {
            return array_merge(array($fields_list), $list_products);
        }
        $module = new Groupinc();
        $helper_list = new HelperList();
        $helper_list->module = $module;
        $helper_list->title = $this->l('Product list');
        $helper_list->shopLinkType = '';
        $helper_list->no_link = true;
        $helper_list->show_toolbar = true;
        $helper_list->simple_header = (version_compare(_PS_VERSION_, '1.5', '<')) ? true : false;
        $helper_list->identifier = 'id_product';
        $helper_list->table = 'product';
        $helper_list->list_id = $helper_list->table;
        $helper_list->currentIndex = $this->context->link->getAdminLink($this->tabClassName, false).'&configure='.$this->module_name.'&id_groupinc_configuration='.$id_config.'&viewProducts';
        $helper_list->token = Tools::getAdminTokenLite($this->tabClassName);
        $this->_helperlist = $helper_list;

        $helper_list->listTotal = count($list_products);
        $helper_list->tpl_vars['icon'] = 'icon-money';
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $helper_list->toolbar_btn['export'] = array(
                'href' => $this->context->link->getAdminLink($this->tabClassName).'&exportProducts&id_groupinc_configuration='.$id_config,
                'desc' => $this->l('Export')
            );
        }

        if (version_compare(_PS_VERSION_, '1.6.0.14', '>')) {
            if (Tools::isSubmit($helper_list->table.'_pagination')) {
                $helper_list->_default_pagination = Tools::getValue($helper_list->table.'_pagination');
            } else {
                $helper_list->_default_pagination = $this->_default_pagination;
            }
        }

        $page = ($page = Tools::getValue('submitFilter'.$helper_list->table)) ? $page : 1;
        $helper_list->_default_pagination = 50;
        $helper_list->_pagination = [50];

        $pagination = ($pagination = Tools::getValue($helper_list->table.'_pagination')) ? $pagination : $this->_default_pagination;
        $list_products = $this->paginate($list_products, $page, $pagination);
        $this->tpl_list_vars['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        $this->tpl_list_vars['POST'] = $_POST;
        $list = $helper_list->generateList($list_products, $fields_list);
        return $list;
    }

    public function downloadCsv($data, $filename)
    {
        header('Content-type: text/csv');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
        header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: attachment;filename='.$filename);
        header('Content-Transfer-Encoding: binary');
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        $head = array();
        foreach ($data[0] as $heading) {
            $head[] = $heading['title'];
        }
        fputcsv($output, $head, ';');
        unset($data[0]);

        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }

    public function getPriceWithCurrency($price, $params)
    {
        return Tools::displayPrice($price, (int)$params['id_currency']);
    }

    public function paginate($array_elements, $page = 1, $pagination = 5)
    {
        if (count($array_elements) > $pagination) {
            $array_elements = array_slice($array_elements, $pagination * ($page - 1), $pagination);
        }
        return $array_elements;
    }

    public static function getProductsByRule($id_config)
    {
        return array(
            'id_product'                 => 1,
            'name'                => 'prodname',
        );
    }
}