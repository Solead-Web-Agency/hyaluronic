<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
require_once __DIR__ . '/traits/EtsGeoTranslationTrait.php';
require_once __DIR__ . '/traits/EtsGeoGetInstanceTrait.php';

/**
 * Class EtsGeoDefine
 *
 * @since 2.6.4
 */
class EtsGeoDefine
{
    use EtsGeoTranslationTrait;
    use EtsGeoGetInstanceTrait;

    const DEFAULT_MODULE_NAME = 'ets_geolocation';
    const DEFAULT_MODULE_TAB = 'front_office_features';

    /**
     * @var \Context|\ContextCore
     */
    private $context;

    /**
     * EtsGeoDefine constructor.
     */
    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * @param bool $loadCountries
     *
     * @return array[]
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getConfigSettings($loadCountries = false)
    {
        return [
            'PS_GEOLOCATION_ENABLED' => [
                'label' => $this->l('Enable Geolocation'),
                'type' => 'switch',
                'default' => (int) Configuration::get('PS_GEOLOCATION_ENABLED'),
                'jsType' => 'string',
            ],
            'ETS_GEO_AUTO_LANG' => [
                'label' => $this->l('Auto set customer language?'),
                'type' => 'switch',
                'default' => 1,
                'jsType' => 'string',
                'cast' => 'int',
            ],
            'ETS_GEO_AUTO_CURRENCY' => [
                'label' => $this->l('Auto set customer currency?'),
                'type' => 'switch',
                'default' => 1,
                'jsType' => 'string',
            ],
            'ETS_GEO_AUTO_TAX_SHIPPING' => [
                'label' => $this->l('Auto calculate shipping cost and tax?'),
                'type' => 'switch',
                'default' => 1,
                'jsType' => 'string',
            ],
            'PS_LANG_DEFAULT' => [
                'label' => $this->l('Default language'),
                'type' => 'select',
                'options' => [
                    'query' => Language::getLanguages(false),
                    'id' => 'id_lang',
                    'name' => 'name',
                ],
                'col' => 4,
                'default' => (int) Configuration::get('PS_LANG_DEFAULT'),
                'jsType' => 'string',
            ],
            'PS_CURRENCY_DEFAULT' => [
                'label' => $this->l('Default currency'),
                'type' => 'select',
                'options' => [
                    'query' => Currency::getCurrencies(),
                    'id' => 'id_currency',
                    'name' => 'name',
                ],
                'col' => 4,
                'default' => (int) Configuration::get('PS_CURRENCY_DEFAULT'),
                'jsType' => 'string',
            ],
            'PS_COUNTRY_DEFAULT' => [
                'label' => $this->l('Default country'),
                'type' => 'select',
                'options' => [
                    'query' => $loadCountries ? \EtsGeoCountryHelper::getCountriesWithOutRule($this->context->language->id, true) : Country::getCountries($this->context->language->id),
                    'id' => 'id_country',
                    'name' => 'name',
                ],
                'col' => 4,
                'default' => (int) Configuration::get('PS_COUNTRY_DEFAULT'),
                'jsType' => 'string',
            ],
            'ETS_GEO_ON_HOME_ONLY' => [
                'label' => $this->l('Only auto set language, currency, tax and shipping cost when customer lands on home page'),
                'type' => 'switch',
                'default' => 0,
                'jsType' => 'string',
                'desc' => $this->l('This is to stop geolocation for inner pages (product page, category page, cms page, etc.) '),
            ],
            'ETS_GEO_HIDE_NOTIFICATION' => [
                'label' => $this->l('Ask customer for confirmation before changing language and currency'),
                'type' => 'switch',
                'default' => 1,
                'jsType' => 'string',
            ],
            'ETS_GEO_ENABLE_SWITCH' => [
                'label' => $this->l('Enable location switching option?'),
                'type' => 'switch',
                'default' => 1,
                'jsType' => 'string',
                'desc' => $this->l('Allow customer to reselect their country manually. Language, currency, taxes and shipping cost will change accordingly to the selected country'),
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getConfigMessages()
    {
        return [
            'ETS_GEO_CONFIRM_MSG' => [
                'label' => $this->l('Confirmation message before changing both language and currency'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'required' => true,
                'class' => 'rte',
                'default' => $this->formatWithinTags($this->l('Our system detects that you are visiting our website from [1][detected_country][/1]. Do you want to change website language from [1][current_language][/1] to [1][detected_language][/1] and currency from [1][current_currency][/1] to [1][detected_currency][/1] ?'), 'strong'),
                'desc' => $this->formatWithinTags($this->l('Available variables: [1][detected_country][/1], [1][detected_language][/1], [1][detected_currency][/1], [1][current_language][/1], [1][current_currency][/1]'), 'span class="light_hight_color"'),
            ],
            'ETS_GEO_LANGUAGE_MSG' => [
                'label' => $this->l('Confirmation message before changing language only'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'required' => true,
                'default' => $this->formatWithinTags($this->l('Our system detects that you are visiting our website from [1][detected_country][/1]. Do you want to change website language from [1][current_language][/1] to [1][detected_language][/1] ?'), 'strong'),
                'desc' => $this->formatWithinTags($this->l('Available variables: [1][detected_country][/1], [1][detected_language][/1], [1][current_language][/1]'), 'span class="light_hight_color"'),
            ],
            'ETS_GEO_CURRENCY_MSG' => [
                'label' => $this->l('Confirmation message before changing currency only'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'required' => true,
                'default' => $this->formatWithinTags($this->l('Our system detects that you are visiting our website from [1][detected_country][/1]. Do you want to change website currency from [1][current_currency][/1] to [1][detected_currency][/1] ?'), 'strong'),
                'desc' => $this->formatWithinTags($this->l('Available variables: [1][detected_country][/1], [1][detected_currency][/1], [1][current_currency][/1]'), 'span class="light_hight_color"'),
            ],
            'ETS_GEO_SETTING_MSG' => [
                'label' => $this->l('Setting language and currency notification message'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'default' => $this->l('We are setting your language and currency. Please wait a moment..!'),
            ],
            'ETS_GEO_CHOOSE_MSG' => [
                'label' => $this->l('Message displayed on "Choose your location" popup'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'default' => $this->l('Taxes, delivery options, shipping price and delivery speeds may vary for different locations'),
            ],
            'ETS_GEO_BLOG_MSG' => [
                'label' => $this->l('Blocking message'),
                'type' => 'textarea',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'default' => $this->l('Sorry! You are blocked from accessing this website.'),
            ],
            'ETS_GEO_DISABLED_PRODUCT_MSG' => [
                'label' => $this->l('Message displayed when the product is disabled'),
                'type' => 'text',
                'cols' => 60,
                'rows' => 3,
                'lang' => true,
                'default' => $this->l('This product is not available.'),
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getConfigTabs()
    {
        return [
            'set' => [
                'name' => 'settings',
                'label' => $this->l('Settings '),
                'is_conf' => true,
                'render' => 'form',
                'value' => 'configs_settings',
                'getMethod' => 'getConfigSettings',
            ],
            'mes' => [
                'name' => 'messages',
                'label' => $this->l('Messages'),
                'is_conf' => true,
                'render' => 'form',
                'value' => 'configs_messages',
                'getMethod' => 'getConfigMessages',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getQuickTabs()
    {
        return [
            [
                'class_name' => 'AdminGeoLocationStatistics',
                'tab_name' => $this->l('Statistics'),
                'tab_name_origin' => 'Statistics',
                'icon' => 'fa fa-line-chart',
            ],
            [
                'class_name' => 'AdminGeoLocationSettings',
                'tab_name' => $this->l('Settings'),
                'tab_name_origin' => 'Settings',
                'icon' => 'fa fa-cogs',
            ],
            [
                'class_name' => 'AdminGeoLocationRules',
                'tab_name' => $this->l('Rules'),
                'tab_name_origin' => 'Rules',
                'icon' => 'fa fa-list-ul',
            ],
            [
                'class_name' => 'AdminGeoLocationMessages',
                'tab_name' => $this->l('Messages'),
                'tab_name_origin' => 'Messages',
                'icon' => 'fa fa-commenting-o',
            ],
            [
                'class_name' => 'AdminGeoLocationCronjob',
                'tab_name' => $this->l('Cronjob'),
                'tab_name_origin' => 'Cronjob',
                'icon' => 'fa fa-clock-o',
            ],
            [
                'class_name' => 'AdminGeoLocationHelp',
                'tab_name' => $this->l('Help'),
                'tab_name_origin' => 'Help',
                'icon' => 'fa fa-question-circle',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getMenuTabList()
    {
        return [
            [
                'label' => $this->l('Statistics'),
                'url' => $this->getAdminLink(['control' => 'statistics', 'list' => 'true']),
                'id' => 'ets_tab_statistics',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeoStatistics',
            ],
            [
                'label' => $this->l('Settings'),
                'url' => $this->getAdminLink(['control' => 'settings', 'list' => 'true']),
                'id' => 'ets_tab_settings',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeosettings',
            ],
            [
                'label' => $this->l('Rules'),
                'url' => $this->getAdminLink(['control' => 'rules', 'list' => 'true']),
                'id' => 'ets_tab_rules',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeoRules',
            ],
            [
                'label' => $this->l('Messages'),
                'url' => $this->getAdminLink(['control' => 'messages', 'list' => 'true']),
                'id' => 'ets_tab_messages',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeoMessages',
            ],
            [
                'label' => $this->l('Cronjob'),
                'url' => $this->getAdminLink(['control' => 'cronjob']),
                'id' => 'ets_tab_cronjob',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeoCronjob',
            ],
            [
                'label' => $this->l('Help'),
                'url' => $this->getAdminLink(['control' => 'help', 'list' => 'true']),
                'id' => 'ets_tab_help',
                'hasAccess' => '',
                'controller' => 'AdminEtsGeoHelp',
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getRuleFieldList()
    {
        return [
            'id_rule' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'class' => 'fixed-width-xs',
            ],
            'countries' => [
                'title' => $this->l('Countries'),
                'align' => 'left',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
                'callback' => 'displayCountry',
                'callback_object' => $this,
            ],
            'lang_to_set' => [
                'title' => $this->l('Language to set'),
                'align' => 'left',
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ],
            'currency_to_set' => [
                'title' => $this->l('Currency to set'),
                'type' => 'text',
                'search' => false,
                'orderby' => false,
            ],
            'disable_geo' => [
                'title' => $this->l('Disable GEO'),
                'type' => 'bool',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
                'active' => 'field=disable_geo&',
            ],
            'block_user' => [
                'title' => $this->l('Block access'),
                'type' => 'bool',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
                'active' => 'field=block_user&',
            ],
            'enabled' => [
                'title' => $this->l('Enabled'),
                'type' => 'bool',
                'active' => 'field=enabled&',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
            ],
//            'priority' => [
//                'title' => $this->l('Priority'),
//                'type' => 'text',
//                'search' => false,
//                'orderby' => false,
//                'align' => 'center',
//            ],
        ];
    }

    /**
     * @return array[]
     */
    public function getSwitchValues()
    {
        return [
            [
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Yes'),
            ],
            [
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('No'),
            ],
        ];
    }

    /**
     * This is for add/update rule form
     *
     * @param \Geo_rules|null $rule
     *
     * @return array[]
     *
     * @throws \PrestaShopDatabaseException
     */
    public function getRuleFormFieldList($rule = null)
    {
        if (!class_exists(EtsGeoCountryHelper::class)) {
            require_once __DIR__ . '/EtsGeoCountryHelper.php';
        }
        $languages = array_merge(
            [[
                'id_lang' => 0,
                'name' => $this->l('Auto'),
            ]],
            array_map(static function ($v) {
                return [
                    'id_lang' => $v['id_lang'],
                    'name' => $v['name'],
                ];
            }, Language::getLanguages(false))
        );
        $currencies = array_merge(
            [[
                'id_currency' => 0,
                'name' => $this->l('Auto'),
            ]],
            array_map(static function ($v) {
                return [
                    'id_currency' => $v['id_currency'],
                    'name' => $v['name'],
                ];
            }, Currency::getCurrencies())
        );
        $fields = [
            [
                'label' => $this->l('Id shop'),
                'type' => 'hidden',
                'name' => 'id_shop',
                'default' => (int) $this->context->shop->id,
            ],
            [
                'label' => $this->l('Enabled'),
                'type' => 'switch',
                'name' => 'enabled',
                'values' => $this->getSwitchValues(),
                'default' => 1,
            ],
            [
                'label' => $this->l('Countries'),
                'type' => 'geo_countries',
                'name' => 'countries',
                'required' => true,
                'options' => [
                    'query' => \EtsGeoCountryHelper::getCountriesWithOutRule($this->context->language->id, true, false, true, (int) Tools::getValue('id_rule')),
                    'id' => 'id_country',
                    'name' => 'name',
                ],
                'multiple' => true,
            ],
            [
                'label' => $this->l('Disable Geolocation for selected countries'),
                'type' => 'switch',
                'name' => 'disable_geo',
                'values' => $this->getSwitchValues(),
            ],
            [
                'label' => $this->l('Language to set'),
                'type' => 'select',
                'name' => 'lang_to_set',
                'options' => [
                    'query' => $languages,
                    'id' => 'id_lang',
                    'name' => 'name',
                ],
            ],
            [
                'label' => $this->l('Currency to set'),
                'type' => 'select',
                'name' => 'currency_to_set',
                'options' => [
                    'query' => $currencies,
                    'id' => 'id_currency',
                    'name' => 'name',
                ],
            ],
            [
                'label' => $this->l('Block all users from the selected countries?'),
                'type' => 'switch',
                'name' => 'block_user',
                'values' => $this->getSwitchValues(),
                'desc' => $this->l('All users come from these countries will blocked from accessing the website'),
                'default' => 0,
            ],
            [
                'label' => $this->l('Hide products'),
                'type' => 'text_search_prd',
                'name' => 'hidden_products',
                'values' => $rule ? $rule->hidden_products : '',
                'desc' => $this->l('Hide specific product if users came from selected countries'),
                'default' => null,
            ],
            [
                'label' => $this->l('Redirect to'),
                'type' => 'text',
                'name' => 'url_redirect',
                'desc' => $this->l('Redirect customers to another website'),
                'class' => 'col-lg-6',
            ],
//            [
//                'label' => $this->l('Priority'),
//                'type' => 'text',
//                'name' => 'priority',
//                'col' => '2',
//                'required' => true,
//                'default' => 1,
//                'desc' => $this->l('When there is more than one rule is defined for a location, the rule with a smaller priority will be applied'),
//            ],
        ];
        if ($rule && $rule->id) {
            $fields[] = [
                'type' => 'hidden',
                'name' => 'id_rule',
                'default' => $rule->id,
            ];
        }

        return $fields;
    }

    /**
     * @param string $string
     * @param string[]|string $tags
     *
     * @return mixed
     */
    public function formatWithinTags($string, $tags)
    {
        if ($tags) {
            $tags = !is_array($tags) ? [$tags] : $tags;
            $ik = 1;
            foreach ($tags as $tag) {
                $string = preg_replace('/(\[' . $ik . '\](.*?)\[\/' . $ik . '\])/i', '<' . $tag . '>$2</' . strtok($tag, ' ') . '>', $string);
                ++$ik;
            }
        }

        return Tools::stripslashes($string);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function displayCountry($value)
    {
        if ($value) {
            $value = Tools::truncate($value, 100);
        }

        return $value;
    }

    /**
     * @param array $args
     *
     * @return string
     */
    public function getAdminLink($args = [])
    {
        $uri = $this->context->link->getAdminLink('AdminModules', isset($args['token']) ? $args['token'] : true) . '&configure=' . self::DEFAULT_MODULE_NAME . '&tab_module=' . self::DEFAULT_MODULE_TAB . '&module_name=' . self::DEFAULT_MODULE_NAME;
        if ($args) {
            unset($args['token']);
            $uri .= '&' . http_build_query($args);
        }

        return $uri;
    }
}
