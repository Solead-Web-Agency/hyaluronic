<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    SeQura Tech <prestashop@sequra.com>
 * @copyright Since 2013 SeQura WorldWide SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopSequra\Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\Module\PrestashopSequra\Crontab;

define('_SEQURA_SERVERS_IPS', '34.253.159.179,34.252.147.155,52.211.243.177');

class Settings
{
    protected $countries;
    protected $crontab;
    protected $context;
    protected $methods;
    protected $module;

    public static $MINIWIDGET_TYPES = ['CATEGORIES', 'CART', 'MINICART'];

    /**
     * IP regex to validate the IP address. Works for both IPv4 and IPv6.
     */
    public const IP_REGEX = '/^(((25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?))|([0-9a-fA-F]{1,4}:){7}([0-9a-fA-F]{1,4}))$/'; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * ISO 8061 regex to validate the date format.
     * Check dates (yyyy-mm-dd) and time durations (PnYnMnDTnHnMnS).
     */
    public const ISO8061_REGEX = '/^(?:(?:\d{4}-(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8]))|(?:\d{4}-(?:0[13-9]|1[0-2])-(?:29|30))|(?:\d{2}(?:0[48]|[2468][048]|[13579][26])-(?:02)-29)|(?:\d{2}(?:[02468][048]|[13579][26])-(?:02)-29))(?:T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)?)?|P(?:\d+Y)?(?:\d+M)?(?:\d+W)?(?:\d+D)?(?:T(?:\d+H)?(?:\d+M)?(?:\d+S)?)?$/'; // phpcs:ignore Generic.Files.LineLength.TooLong

    public function __construct($module)
    {
        $this->module = $module;
        $this->crontab = new Crontab();
        $this->context = $this->module->getContext();
        $this->countries = $this->module->getCountries();
        $this->getMethodsForAllCountries();
    }

    public function getConfigKeys()
    {
        return array_unique(
            array_merge(
                $this->getMerchantConfigKeys(),
                $this->getWidgetConfigKeys(),
                $this->getStatsConfigKeys(),
                $this->getAdvancedConfigKeys()
            )
        );
    }

    public function getMerchantConfigKeys()
    {
        $ret = [
            'SEQURA_USER',
            'SEQURA_ALLOW_IP',
            'SEQURA_ORDER_ID_FIELD',
            'SEQURA_AUTOCRON',
            'SEQURA_AUTOCRON_H',
            'SEQURA_AUTOCRON_M',
            'SEQURA_ASSETS_KEY',
            'SEQURA_MODE',
            'SEQURA_FOR_SERVICES',
            'SEQURA_ALLOW_PAYMENT_DELAY',
            'SEQURA_ALLOW_REGISTRATION_ITEMS',
            'SEQURA_FOR_SERVICES_END_DATE',
            'SEQURA_SEND_CANCELLATIONS',
            'SEQURA_PS_CANCELED',
            'SEQURA_BANNED_CAT_IDS',
            'SEQURA_OS_APPROVED',
            'SEQURA_OS_NEEDS_REVIEW',
            'SEQURA_OS_CANCELED',
            'SEQURA_OS_APPROVED_LOWRISK',
            'SEQURA_OS_APPROVED_UNKNOWNRISK',
            'SEQURA_OS_APPROVED_HIGHRISK',
            'SEQURA_COUNTRIES',
            'SEQURA_BANNERS',
        ];

        return array_merge(
            $ret,
            array_map(
                function ($code) {
                    return 'SEQURA_MERCHANT_ID_' . $code;
                },
                $this->module->getCountries()
            )
        );
    }

    public function getStatsConfigKeys()
    {
        return [
            'SEQURA_STATS_ALLOW',
            'SEQURA_STATS_AMOUNT',
            'SEQURA_STATS_PAYMENTMETHOD',
            'SEQURA_STATS_COUNTRIES',
            'SEQURA_STATS_BROWSER',
            'SEQURA_STATS_STATUS',
        ];
    }

    public function getAdvancedConfigKeys()
    {
        return [
            'SEQURA_ENABLE_DEBUG',
        ];
    }

    protected function getMethodCountryWidgetConfigKeys($method)
    {
        $country = $method['country'];
        $i18nproduct = PaymentMethodsSettings::buildUniqueI18ProductCode($method);
        $ret = [];
        if (PaymentMethodsSettings::getFamilyFor($method) != 'CARD') {
            $ret = [
                'SEQURA_' . $i18nproduct . '_SHOW_BANNER',
                'SEQURA_' . $i18nproduct . '_CSS_SEL',
                'SEQURA_' . $i18nproduct . '_WIDGET_THEME',
            ];
        }
        if (PaymentMethodsSettings::getFamilyFor($method) == 'PARTPAYMENT') {
            $ret = array_merge(
                $ret,
                [
                    'SEQURA_' . $country . '_PARTPAYMENT_PRODUCT',
                    'SEQURA_' . $country . '_PARTPAYMENT_CATEGORIES_SHOW',
                    'SEQURA_' . $country . '_PARTPAYMENT_CART_SHOW',
                    'SEQURA_' . $country . '_PARTPAYMENT_MINICART_SHOW',
                    'SEQURA_' . $country . '_PARTPAYMENT_CATEGORIES_TEASER_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_CART_TEASER_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_MINICART_TEASER_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_CATEGORIES_BELOW_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_CART_BELOW_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_MINICART_BELOW_MSG',
                    'SEQURA_' . $country . '_PARTPAYMENT_CATEGORIES_CSS_SEL',
                    'SEQURA_' . $country . '_PARTPAYMENT_CART_CSS_SEL',
                    'SEQURA_' . $country . '_PARTPAYMENT_MINICART_CSS_SEL',
                    'SEQURA_' . $country . '_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE',
                    'SEQURA_' . $country . '_PARTPAYMENT_CART_CSS_SEL_PRICE',
                    'SEQURA_' . $country . '_PARTPAYMENT_MINICART_CSS_SEL_PRICE',
                ]
            );
        }

        return $ret;
    }

    public function getCountryWidgetConfigKeys($country)
    {
        if (!$this->methods[$country]) {
            return [];
        }

        return array_merge(
            ...array_map(
                [$this, 'getMethodCountryWidgetConfigKeys'],
                $this->methods[$country]
            )
        );
    }

    public function getWidgetConfigKeys()
    {
        $ret = [
            'SEQURA_CSS_SEL_PRICE',
            'SEQURA_FORCE_NEW_PAGE',
        ];

        return array_merge(
            $ret,
            ...array_map(
                [$this, 'getCountryWidgetConfigKeys'],
                $this->module->getCountries()
            )
        );
    }

    public function getMethodsForAllCountries()
    {
        if (is_null($this->methods)) {
            $methods = [];
            foreach ($this->countries as $country) {
                $methods[$country] = PaymentMethodsSettings::getMerchantPaymentMethods(true, $country);
                array_walk(
                    $methods[$country],
                    function (&$method) use ($country) {
                        $method['country'] = $country;
                    }
                );
            }
            $this->methods = $methods;
        }

        return $this->methods;
    }

    public function getFileContents($file)
    {
        if (file_exists($file)) {
            return \Tools::file_get_contents($file);
        }

        return '';
    }

    public function getCustomCssPath()
    {
        $file_path_in_theme = $this->getCustomCssThemePath();

        return file_exists($file_path_in_theme) ? $file_path_in_theme : $this->getCustomCssModulePath();
    }

    public function getCustomCssThemePath()
    {
        return _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/views/css/' . \Sequra::CSS_FILE;
    }

    public function getCustomCssModulePath()
    {
        return _PS_MODULE_DIR_ . '/' . $this->module->name . '/views/css/' . \Sequra::CSS_FILE;
    }

    public function getPaymentFormTplPath($force = false)
    {
        $tpl = (\Sequra::needsBasicPresentation() ? 'opc_' : '') . 'payment_info.tpl';
        $file_path_in_tpl = _PS_THEME_DIR_ . 'modules/' . $this->module->name . '/views/templates/front/' . $tpl;
        if ($force || file_exists($file_path_in_tpl)) {
            if (!file_exists(dirname($file_path_in_tpl))) {
                mkdir(dirname($file_path_in_tpl), 0755, true);
            }

            return $file_path_in_tpl;
        }

        return _PS_MODULE_DIR_ . $this->module->name . '/views/templates/front/' . $tpl;
    }

    /**
     * Get the name of a category given its id.
     *
     * @param int $category_id The id of the category
     * @param array $categories An array of categories
     *
     * @return string
     */
    private function getCategoryName($category_id, $categories)
    {
        $filtered = array_filter($categories, function ($cat) use ($category_id) {
            return $category_id === $cat['id_category'];
        });
        $category = array_shift($filtered);
        if (empty($category) || $category['is_root_category'] || empty($category['id_parent'])) {
            return '';
        }
        $category_name = $category['name'];

        if (!empty($category['id_parent'])) {
            $parent_name = $this->getCategoryName($category['id_parent'], $categories);
            if (!empty($parent_name)) {
                $category_name = $parent_name . ' > ' . $category_name;
            }
        }

        return $category_name;
    }

    /**
     * Get an array of categories. Each category is an array with id and name.
     *
     * @return array
     */
    public function getCategoryOptions()
    {
        $default_lang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $categories = \Category::getCategories($default_lang, false, false);
        $category_options = [];

        foreach ($categories as $category) {
            if (empty($category['id_parent']) || $category['is_root_category']) {
                continue; // skip root category
            }

            $category_name = $this->getCategoryName($category['id_category'], $categories);

            $cat_item = [
                'id' => $category['id_category'],
                'name' => $category_name,
            ];
            $category_options[] = $cat_item;
        }

        $category_options_names = array_column($category_options, 'name');
        array_multisort($category_options_names, SORT_ASC, $category_options);

        return $category_options;
    }
}
