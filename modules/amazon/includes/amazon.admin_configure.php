<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/configuration/AmazonAdminConfigurationCron.php';
require_once dirname(__FILE__) . '/configuration/AmazonAdminBuildCron.php';
require_once dirname(__FILE__) . '/configuration/AmazonAdminConfigurationInformation.php';
require_once dirname(__FILE__) . '/configuration/AmazonAdminConfigurationMarketplaces.php';

/**
 * todo: Migrate all admin configurations to this class
 * Class AmazonAdminConfigure
 */
class AmazonAdminConfigure
{
    const TEMPLATE_HEADER = 1;
    const TEMPLATE_FOOTER = 2;
    const TEMPLATE_LICENSE = 3;
    const TEMPLATE_VALIDATE = 4;
    const TEMPLATE_CONFIGURE = 5;
    const TEMPLATE_TABS = 10;
    const TEMPLATE_TAB_SETTINGS = 19;
    const TEMPLATE_TAB_TOOLS = 20;
    const TEMPLATE_TAB_CRON = 21;
    const TEMPLATE_TAB_FILTERS = 22;
    const TEMPLATE_TAB_PARAMETERS = 23;
    const TEMPLATE_TAB_CATEGORIES = 24;
    const TEMPLATE_TAB_PROFILES = 25;
    const TEMPLATE_TAB_MAPPING = 26;
    const TEMPLATE_TAB_INFO = 27;
    const TEMPLATE_TAB_SHIPPING = 28;
    const TEMPLATE_TAB_MESSAGING = 29;
    const TEMPLATE_TAB_FBA = 30;
    const TEMPLATE_TAB_FEATURES = 31;
    const TEMPLATE_TAB_AMAZON = 43;
    const TEMPLATE_TAB_GLOSSARY = 44;

    private static $templates = array(
        self::TEMPLATE_TABS => 'views/templates/admin/configure/tabs.tpl',
        self::TEMPLATE_HEADER => 'views/templates/admin/configure/header.tpl',
        self::TEMPLATE_FOOTER => 'views/templates/admin/configure/footer.tpl',
        self::TEMPLATE_VALIDATE => 'views/templates/admin/configure/validate.tpl',
        self::TEMPLATE_CONFIGURE => 'views/templates/admin/configure/configure.tpl',
        self::TEMPLATE_LICENSE => 'views/templates/admin/configure/license.tpl',
        self::TEMPLATE_TAB_SETTINGS => 'views/templates/admin/configure/settings.tab.tpl',
        self::TEMPLATE_TAB_TOOLS => 'views/templates/admin/configure/tools.tab.tpl',
        self::TEMPLATE_TAB_CRON => 'views/templates/admin/configure/cron.tab.tpl',
        self::TEMPLATE_TAB_FILTERS => 'views/templates/admin/configure/filters.tab.tpl',
        self::TEMPLATE_TAB_PARAMETERS => 'views/templates/admin/configure/parameters.tab.tpl',
        self::TEMPLATE_TAB_PROFILES => 'views/templates/admin/configure/profiles.tab.tpl',
        self::TEMPLATE_TAB_CATEGORIES => 'views/templates/admin/configure/categories.tab.tpl',
        self::TEMPLATE_TAB_MAPPING => 'views/templates/admin/configure/mapping.tab.tpl',
        self::TEMPLATE_TAB_INFO => 'views/templates/admin/configure/informations.tab.tpl',
        self::TEMPLATE_TAB_FEATURES => 'views/templates/admin/configure/features.tab.tpl',
        self::TEMPLATE_TAB_SHIPPING => 'views/templates/admin/configure/shipping.tab.tpl',
        self::TEMPLATE_TAB_MESSAGING => 'views/templates/admin/configure/messaging.tab.tpl',
        self::TEMPLATE_TAB_FBA => 'views/templates/admin/configure/fba.tab.tpl',
        self::TEMPLATE_TAB_AMAZON => 'views/templates/admin/configure/amazon.tab.tpl',
        self::TEMPLATE_TAB_GLOSSARY => 'views/templates/admin/configure/glossary.tpl',
    );

    /** @var Amazon */
    public $module;

    /** @var Context */
    public $context;

    /** @var bool */
    public $enable_experimental_features = false;

    protected $config = array();

    /** @var AmazonAdminConfigurationInformation */
    public $information;
    /** @var AmazonAdminConfigurationMarketplaces */
    public $marketplaces;
    /** @var AmazonAdminConfigurationCron */
    public $cron;

    /**
     * All tabs use same footer
     * @var string html
     */
    protected $tab_footer;

    public function __construct($module, $context, $enable_experimental_features)
    {
        $this->module = $module;
        $this->context = $context;
        $this->enable_experimental_features = $enable_experimental_features;
        $this->marketplaces = new AmazonAdminConfigurationMarketplaces($module);

        $this->cron = new AmazonAdminConfigurationCron($this->module);
    }

    public function initInformation($seemToBeConfigured, $needMigration)
    {
        $this->information = new AmazonAdminConfigurationInformation(
            $this->module->url,
            $this->module->id_lang,
            $this->module->getConfig()['instant_token'],
            $this->module->getErrorClassName(),
            $seemToBeConfigured,
            $needMigration
        );
    }

    public function spApiAuthorization()
    {
        /**
         * Save SP API authorization information after return from Amazon, something like:
         * http://ps1-7-8-0.ps/adm/index.php?controller=AdminModules&token=e269d888a05b7e94a384315f61570888&configure=amazon&sp_mkp=ATVPDKIKX0DER&access_token=Atza%7CIwEBIH6TZV_KPgvA_bv3iD9gzr1a6GN_Y3VscYkHyKzY9jU3bqoTiJJsy3jKYScT1WNsObwVczouYfKER4rn0itFqSqGNyz5xrGV8NJI9tzo3DFY9FCodt_d-yqRuoXhuD9BdLdgxeGYda0Vk24Rps0qLywTa2qz7Y9kTpZdXvDQcLVzAGV-zpU1FUpO5rZIfYgXIqOJQk_Nfk5AfXqhJY0haYbmjE1UU8L_iiBCEThRQiJ6F04eOIvRSGslkc58xb4InhYe0O57Li21mRaPvpbsW0Q8RnRblTaxp7tn1ze4BCAskhImhzVToE_N_4MdJoXZy2M&refresh_token=Atzr%7CIwEBIMsjASqaNoQzAX4jGkxMfrHLVnL4N6wL35znG99DUpv20_UvddXpR7xRIZVPwcCjdKHNdGMzrYhMAUhIQoh9I9xuP7VdLZULOuTtYQv3mQE8LSNqA-wgn5QDH1IGuZZq2nlnzuPkzfeHAr8XOQN83EVbkr1AmPQ3EDvZEthzscrnZNJBColxC0c47w-qHLoj0ZIBdIQ3lUwuuU5Kvdsr_PzHXuIOY09ju9yqLVYSv50DOCJdQRwV90ypkpcYZLg0fbbEnppJ47aRWeHubzkhlL07YZui7A5phZthqFFumFB8Qpi6NvwjPqoeucnULflxaZg&state=cHJlc3Rhc2hvcDo6MTY0NDQ3NTA2ODo6cmVnaW9uPWV1LXdlc3QtMQ%3D%3D&selling_partner_id=A12TSOGZWIJ0IM&mws_auth_token=
         */
        $spState = AmazonTools::getValue('state');
        $spSellerId = AmazonTools::getValue('selling_partner_id');
        $accessToken = AmazonTools::getValue('access_token');
        $refreshToken = AmazonTools::getValue('refresh_token');
        $mkpId = AmazonTools::getValue('sp_mkp');
        if ($spState && $spSellerId && $accessToken && $refreshToken) {
            $logger = new AmazonLogger(AmazonLogger::CHANNEL_SP_API_AUTHORIZATION);
            $logger->debug('Incoming SP API Auth request', ['url' => $_SERVER['REQUEST_URI'], 'state' => $spState, 'seller' => $spSellerId, 'access_token' => $accessToken, 'refresh_token' => $refreshToken]);

            $authState = AmazonSPAuthPS::verifyState($spState, $spSellerId, $accessToken, $refreshToken, $mkpId);
            $logger->debug('SP API Auth result', ['status' => $authState]);

            // Redirect to truncate Amazon response's params
            Tools::redirect(AmazonTools::buildAbsolutePsAdminLink($this->context->link, 'AdminModules', $this->module->ps17x) .
                '&configure=' . $this->module->name . '&' . AmazonConstant::SP_API_RETURN_QUERY_NAME . "=$authState");
        }
    }

    public function handleSpAPIStatus()
    {
        if (Tools::getIsset(AmazonConstant::SP_API_RETURN_QUERY_NAME)) {
            switch (Tools::getValue(AmazonConstant::SP_API_RETURN_QUERY_NAME)) {
                case AmazonSPAuthPS::STATUS_SUCCESS:
                    return array('success' => true, 'msg' => $this->module->l('Authorized successfully'));
                case AmazonSPAuthPS::STATUS_MALFORMED_INPUT:
                    return array('success' => false, 'msg' => $this->module->l('Malformed SP API authorization request!'));
                case AmazonSPAuthPS::STATUS_MISSING_MWS_TOKEN:
                    return array('success' => true, 'msg' => $this->module->l('Authorized successfully without MWSToken!'));
                case AmazonSPAuthPS::STATUS_UPDATE_CONFIG_FAILED:
                    return array('success' => false, 'msg' => $this->module->l('Failed to save authorization information!'));
                case AmazonSPAuthPS::STATUS_PROPAGATE_CONFIG_FAILED:
                    return array('success' => false, 'msg' => $this->module->l('Failed to apply authorization information to all marketplaces in the region!'));
                default:
                    return array('success' => false, 'msg' => $this->module->l('Failed to authorize, please contact support!'));
            }
        }

        return array(); // nothing
    }

    public function loadSettings()
    {
        if (!count($this->config)) {
            $this->config = array(
                'taxes_comply_eu_vat_rules' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_OI_TAXES_COMPLY_EU_VAT_RULES),
                'taxes_on_business_orders' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_OI_TAXES_ON_BUSINESS_ORDERS),
                'force_vat_recalculation' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_OI_TAXES_FORCE_RECALCULATION),
                'vidr' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_VCS_ENABLED),
                'vidr_send_invoice' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_VCS_SEND_INVOICE),
                'vidr_update_customer_vat_number' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_VCS_UPDATE_CUSTOMER_VAT_NUMBER),
                'vidr_update_billing_address' => (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_VCS_UPDATE_BILLING_ADDRESS),
                'canceled_state' => AmazonConfiguration::get(AmazonConstant::ORDER_CANCELED_TYPE),
            );
        }

        return $this->config;
    }

    public function allTabControllerParams()
    {
        $module = $this->module;
        $selected_tab = $this->selectedTab();
        $country_iso_code = Configuration::get('PS_LOCALE_COUNTRY');

        $platforms = array();
        foreach (AmazonTools::languages() as $language) {
            $index = $language['iso_code'];
            $platforms[$index] = array(
                'iso_code' => $index,
                'name_short' => preg_replace('/ .*/', '', $language['name']),
                'name_long' => $language['name'],
                'selected' => ($selected_tab === $language['iso_code'] ? 'selected' : ''),
                'geo_flag' => $module->geoFlag($language['id_lang']),
                'area' => $language['area'],
                'display' => Tools::strtolower($country_iso_code) === Tools::strtolower($language['country_iso_code'])
            );
        }

        $tabsParams = array(
            'images_url' => $module->images,
            'amazon' => 'Amazon',
            'informations' => $module->l('Informations'),
            'features' => $module->l('Features'),
            'platforms' => $platforms,
            'parameters' => $module->l('Parameters'),
            'categories' => $module->l('Categories'),
            'mapping' => $module->l('Mappings'),
            'profiles' => $module->l('Profiles'),
            'shipping' => $module->l('Shipping'),
            'filters' => $module->l('Filters'),
            'messaging' => $module->l('Messaging'),
            'fba' => $module->l('Amazon FBA'),
            'prime' => $module->l('Prime'),
            'tools' => $module->l('Tools'),
            'cron' => $module->l('Scheduled Tasks'),
            'debug' => $module->l('Debug Mode'),
        );
        // 'amazon_selected', 'informations_selected', 'features_selected', 'parameters_selected', 'categories_selected',
        // 'mapping_selected', 'profiles_selected', 'shipping_selected', 'filters_selected', 'messaging_selected',
        // 'fba_selected', 'repricing_selected', 'prime_selected', 'tools_selected', 'cron_selected', 'debug_selected'
        $tabs = array(
            'amazon',
            'informations',
            'features',
            'parameters',
            'categories',
            'mapping',
            'profiles',
            'shipping',
            'filters',
            'messaging',
            'fba',
            'prime',
            'tools',
            'cron',
            'debug'
        );
        foreach ($tabs as $tab_name) {
            $tabsParams[$tab_name . '_selected'] = ($selected_tab === $tab_name ? 'selected' : '');
        }

        return $tabsParams;
    }

    /***************************************************** First tab ***************************************************
     * @return array
     */
    public function tabAmazon()
    {
        $current_lang = $this->context->language->iso_code;
        $psOrderId = AmazonConfiguration::get(AmazonConstant::CONFIG_PS_ORDER_ID);

        return array(
            'selected_tab' => $this->selectedTab() === 'amazon',
            'images' => $this->module->images,
            'images_url' => $this->module->images,
            'name' => $this->module->displayName,
            'description' => $this->module->description,
            'documentation' => AmazonSupport::gethreflink(),
            'version' => $this->module->version,
            'ps_version' => _PS_VERSION_,
            'is_lite' => isset($this->amazon_features['module']) && $this->amazon_features['module'] == 'amazonlite',
            'lang' => $current_lang,
            'lang_fr' => $current_lang == 'fr',
            'support_info' => array(
                'subject' => $this->module->l('Support for Amazon'),
                'body' => sprintf($this->module->l('Hi, I have a problem with my amazon module v%s on Pretashop v%s.'), $this->module->version, _PS_VERSION_),
            ),
            'ps_order_id' => $psOrderId,
            'ps_order_id_color_identity' => $psOrderId ? 'green' : 'red',
            'tab_footer' => $this->tabFooter()
        );
    }

    /****************************************************** Features ***************************************************
     * All view params of feature tab
     * @param $view_params
     * @return mixed
     */
    public function tabFeatures(&$view_params)
    {
        $view_params['features']['selected_tab'] = $this->selectedTab() === 'features';
        $view_params['features']['images'] = $this->module->images;
        $view_params['features']['experimental'] = $this->enable_experimental_features;
        $view_params['features']['documentation'] = AmazonSupport::gethreflink();
        $view_params['features']['tutorial'] = AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FEATURES);
        $view_params['features']['images_url'] = $this->module->images;
        $view_params['features']['version'] = $this->module->version;
        $view_params['features']['ps_version'] = _PS_VERSION_;

        $view_params['features']['links'] = array(
            'synchronization' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SYNCHRONIZATION),
            'creation' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CREATION),
            'second_hand' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SECOND_HAND),
            'prices_rules' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_PRICES_RULES),
            'europe' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_EUROPE),
            'orders' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_ORDERS_IMPORT),
            'gcid' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_GCID),
            'filters' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FILTERS),
            'import_products' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_IMPORT_PRODUCTS),
            'offers' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_OFFERS),
            'fba' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_FBA),
            'remote_cart' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_REMOTE_CART),
            'shipping_template' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_SHIPPING_TEMPLATE),
            'messaging' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_MESSAGING),
            'cancel_orders' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_CANCEL_ORDERS),
            'expert_mode' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_EXPERT_MODE),
            'debug_express' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_DEBUG_EXPRESS),
            'business' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_BUSINESS),
            'orders_reports' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_ORDERS_REPORT),
            'be' => AmazonSupport::gethreflink(AmazonSupport::TUTORIAL_AMAZON_BE),
        );

        $moduleConfig = $this->module->getConfig();
        $view_params['features']['config'] = $moduleConfig['features'];
        $view_params['features']['config']['noway'] =
            in_array($moduleConfig['features']['module'], array('amazonlite', 'ready'));
        $view_params['features']['validation'] = $this->tabFooter();

        return ($view_params);
    }

    /*************************************************** Parameters ****************************************************
     * @return array
     */
    public function tabParameterSettings()
    {
        $config = $this->module->getConfig();
        $orderStates = $config['order_state'];
        $osIncomingCombination = AmazonConfiguration::get(AmazonConstant::CONFIG_ORDER_STATES_INCOMING_OF_ORDER_ATTRS_COMBINATION);

        $osTypeStd = AmazonConstant::ORDER_INCOMING_TYPE_STANDARD;
        $osTypedStdUnshipped = AmazonConstant::ORDER_INCOMING_TYPE_STANDARD_UNSHIPPED;
        $osTypePrime = AmazonConstant::ORDER_INCOMING_TYPE_PRIME;
        $osTypeBusiness = AmazonConstant::ORDER_INCOMING_TYPE_BUSINESS;
        $osTypePreOrder = AmazonConstant::ORDER_INCOMING_TYPE_PREORDER;
        $osTypeSent = AmazonConstant::ORDER_SENT_TYPE;
        $osTypeCanceled = AmazonConstant::ORDER_CANCELED_TYPE;

        return array(
            // Orders States
            'ps_order_states' => OrderState::getOrderStates($this->module->id_lang),    // todo: Use this in all places
            'order_states' => array(
                'standard' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => false,
                    'name' => "order_state[$osTypeStd]",
                    'title' => $this->module->l('Incoming Orders'),
                    'desc' => $this->module->l('Choose a default incoming order status for Amazon'),
                    'glossary' => 'set_incoming_order_state',
                    'value' => isset($orderStates[$osTypeStd]) ? $orderStates[$osTypeStd] : '',
                ),
                'standard_unshipped' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => true,
                    'name' => "order_state[$osTypedStdUnshipped]",
                    'title' => $this->module->l('Incoming Orders (For Unshipped)'),
                    'desc' => $this->module->l('Choose the default status for the Unshipped order'),
                    'glossary' => 'set_incoming_unshipped_order_state',
                    'value' => isset($orderStates[$osTypedStdUnshipped]) ? $orderStates[$osTypedStdUnshipped] : '',
                ),
                'prime' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => true,
                    'name' => "order_state[$osTypePrime]",
                    'title' => $this->module->l('Prime Orders'),
                    'desc' => $this->module->l('Choose a default incoming Prime order status for Amazon'),
                    'glossary' => 'set_incoming_order_state_prime',
                    'value' => isset($orderStates[$osTypePrime]) ? $orderStates[$osTypePrime] : '',
                ),
                'business' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => true,
                    'name' => "order_state[$osTypeBusiness]",
                    'title' => $this->module->l('Business Orders'),
                    'desc' => $this->module->l('Choose a default incoming Business order status for Amazon'),
                    'glossary' => 'set_incoming_order_state_business',
                    'value' => isset($orderStates[$osTypeBusiness]) ? $orderStates[$osTypeBusiness] : '',
                ),
                'preorder' => array(
                    'enabled' => version_compare(_PS_VERSION_, '1.5', '>='),
                    'active' => $config['preorder'],
                    'allow_deselect' => false,
                    'id' => 'order-state-preorder',
                    'name' => "order_state[$osTypePreOrder]",
                    'title' => $this->module->l('Pre-Orders'),
                    'desc' => $this->module->l('Choose a default PreOrder order status for Amazon'),
                    'glossary' => 'set_incoming_preorder',
                    'value' => isset($orderStates[$osTypePreOrder]) ? $orderStates[$osTypePreOrder] : '',
                ),
                'sent' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => false,
                    'name' => $osTypeSent,
                    'title' => $this->module->l('Orders Sent'),
                    'desc' => $this->module->l('Choose a default sent order status for Amazon'),
                    'glossary' => 'set_order_sent',
                    'value' => isset($config[$osTypeSent]) ? $config[$osTypeSent] : '',
                ),
                'canceled' => array(
                    'enabled' => true,
                    'active' => true,
                    'allow_deselect' => false,
                    'name' => $osTypeCanceled,
                    'title' => $this->module->l('Canceled Orders'),
                    'desc' => $this->module->l('Choose a default canceled order status for Amazon'),
                    'glossary' => 'set_order_cancel',
                    'value' => isset($config[$osTypeCanceled]) ? $config[$osTypeCanceled] : '',
                    'rel' => 'amazon-cancel-orders' // toggle feature
                ),
            ),
            'os_incoming_possible_attrs' => array(
                AmazonConstant::ORDER_INCOMING_TYPE_FBA => $this->module->l('FBA'),
                AmazonConstant::ORDER_INCOMING_TYPE_PRIME => $this->module->l('Prime'),
                AmazonConstant::ORDER_INCOMING_TYPE_BUSINESS => $this->module->l('Business'),
            ),
            'os_incoming_combination' => $osIncomingCombination,

            'get_config_no_cache' => Configuration::get(AmazonConstant::CONFIG_GET_BY_DIRECT_SQL),

            // VCS
            'vidr' => $config['vidr'],
            'vidr_send_invoice' => $config['vidr_send_invoice'],
            'vidr_update_customer_vat_number' => $config['vidr_update_customer_vat_number'],
            'vidr_update_billing_address' => $config['vidr_update_billing_address'],

            'discount' => (bool)$config['specials'],
            'specials_apply_rules' => (bool)$config['specials_apply_rules'],
            'regular_to_rpr' => (bool)$config['regular_to_rpr'],
            'preorder' => (bool)$config['preorder'],
            'taxes' => (int)$config['taxes'],
            'taxes_comply_eu_vat_rules' => $config['taxes_comply_eu_vat_rules'],
            'taxes_on_business_orders' => $config['taxes_on_business_orders'],
            'force_vat_recalculation' => $config['force_vat_recalculation'],
            'product_update_condition_ignore' => AmazonConfiguration::get(AmazonConstant::CONFIG_PRODUCT_UPDATE_CONDITION_IGNORE),
            'import_by_id' => (bool)$config['import_by_id'],
            'not_decrease_stock_for_shipped' => (bool)$config['not_decrease_stock_for_shipped'],
            'price_rule_after_conversion' => $config['price_rule_after_conversion'],
        );
    }

    public function tabPrime()
    {
        $primeConfig = AmazonConfiguration::get(AmazonConstant::CONFIG_PRIME_SETTINGS);
        return array(
            'selected_tab' => $this->selectedTab() === 'prime',
            'delivery_experience' => isset($primeConfig['delivery_experience']) ? $primeConfig['delivery_experience'] : '',
            'carrier_will_pickup' => isset($primeConfig['carrier_will_pickup']) && (bool)$primeConfig['carrier_will_pickup'],
            'shop_name' => isset($primeConfig['shop_name']) ? $primeConfig['shop_name'] : '',
            'address1' => isset($primeConfig['address1']) ? $primeConfig['address1'] : '',
            'address2' => isset($primeConfig['address2']) ? $primeConfig['address2'] : '',
            'city' => isset($primeConfig['city']) ? $primeConfig['city'] : '',
            'postcode' => isset($primeConfig['postcode']) ? $primeConfig['postcode'] : '',
            'email' => isset($primeConfig['email']) ? $primeConfig['email'] : '',
            'phone' => isset($primeConfig['phone']) ? $primeConfig['phone'] : '',
            'country' => isset($primeConfig['country']) ? $primeConfig['country'] : '',
            'platforms' => $this->module->getPlatforms(),   // country
            'available_delivery_experiences' => AmazonSPDefShippingServiceOptions::$deliveryExpEnum,
            'tab_footer' => $this->tabFooter()
        );
    }

    /**
     * @return array [status, iso_code, error]
     */
    public function checkCountryConsistency()
    {
        $countryIsoCode = AmazonTools::strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));

        if (!empty($countryIsoCode)) {
            if (!Validate::isLanguageIsoCode($countryIsoCode) || !Country::getByIso($countryIsoCode)) {
                return array('status' => false, 'iso_code' => $countryIsoCode, 'error' => 'Localization > Locale Country is not valid');
            }
        } else {
            return array('status' => false, 'iso_code' => $countryIsoCode, 'error' => 'Localization > Locale Country setting is empty !');
        }

        return array('status' => true, 'iso_code' => $countryIsoCode, 'error' => '');
    }

    /**
     * @return array [status, iso_code, error]
     */
    public function checkLanguageConsistency($hasRegions = true)
    {
        $langIsoCode = AmazonTools::strtolower(Configuration::get('PS_LOCALE_LANGUAGE'));

        if ($hasRegions && !empty($langIsoCode)) {
            if (!Validate::isLanguageIsoCode($langIsoCode) || !Language::getIdByIso($langIsoCode)) {
                return array('status' => false, 'iso_code' => $langIsoCode, 'error' => 'Localization > Locale Language setting doesnt match any lang in Prestashop tables');
            } elseif (!AmazonTools::lang2MarketplaceId($langIsoCode)) {
                return array('status' => false, 'iso_code' => $langIsoCode, 'error' => 'Support Info: Localization > Locale Language setting doesnt match any Amazon platform');
            } else {
                return array('status' => true, 'iso_code' => $langIsoCode, 'error' => '');
            }
        } else {
            return array('status' => false, 'iso_code' => $langIsoCode, 'error' => 'Localization > Locale Language setting is empty !');
        }
    }

    protected function selectedTab()
    {
        return (($selected_tab = AmazonTools::getValue('selected_tab')) ? $selected_tab : 'amazon');
    }

    protected function tabFooter()
    {
        if (!$this->tab_footer) {
            $this->tab_footer = $this->context->smarty->fetch($this->module->path . self::$templates[self::TEMPLATE_VALIDATE]);
        }

        return $this->tab_footer;
    }
}
