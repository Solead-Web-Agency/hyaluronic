<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

/* defines constant of module name */
define('_GMCP_MODULE_NAME', 'GMCP');
/* defines module name */
define('_GMCP_MODULE_SET_NAME', 'gmerchantcenterpro');
/* defines root path of the shop */
define('_GMCP_SHOP_PATH_ROOT', _PS_ROOT_DIR_ . '/');
/* defines root path of module */
define('_GMCP_PATH_ROOT', _PS_MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/');
/* defines conf path */
define('_GMCP_PATH_CONF', _GMCP_PATH_ROOT . 'conf/');
/* defines library path */
define('_GMCP_PATH_LIB', _GMCP_PATH_ROOT . 'lib/');
/* defines library path */
define('_GMCP_PATH_LIB_DAO', _GMCP_PATH_LIB . 'dao/');
/* defines hook tpl path */
define('_GMCP_TPL_HOOK_PATH', 'hook/');
/* defines common library path */
define('_GMCP_PATH_LIB_COMMON', _GMCP_PATH_LIB . 'common/');
/* defines sql path */
define('_GMCP_PATH_SQL', _GMCP_PATH_ROOT . 'sql/');
/* defines views folder */
define('_GMCP_PATH_VIEWS', 'views/');
/* defines js URL */
define('_GMCP_URL_JS', _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/' . _GMCP_PATH_VIEWS . 'js/');
/* defines css URL */
define('_GMCP_URL_CSS', _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/' . _GMCP_PATH_VIEWS . 'css/');
/* defines MODULE URL */
define('_GMCP_MODULE_URL', _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/');
/* defines img path */
define('_GMCP_PATH_IMG', 'img/');
/* defines img URL */
define('_GMCP_URL_IMG', _MODULE_DIR_ . _GMCP_MODULE_SET_NAME . '/' . _GMCP_PATH_VIEWS . _GMCP_PATH_IMG);
/* defines tpl path name */
define('_GMCP_PATH_TPL_NAME', _GMCP_PATH_VIEWS . 'templates/');
/* defines tpl path */
define('_GMCP_PATH_TPL', _GMCP_PATH_ROOT . _GMCP_PATH_TPL_NAME);
/* defines constant of error tpl */
define('_GMCP_TPL_ERROR', 'error.tpl');
/* defines confirm tpl */
define('_GMCP_TPL_CONFIRM', 'confirm.tpl');
/* defines activate / deactivate debug mode */
define('_GMCP_DEBUG', true);
/* defines constant to use or not js on submit action */
define('_GMCP_USE_JS', true);
/* defines variable for admin ctrl name */
define('_GMCP_PARAM_CTRL_NAME', 'sController');
/* defines variable for admin ctrl name */
define('_GMCP_ADMIN_CTRL', 'admin');
/* defines variable for the php script file to copy */
define('_GMCP_FEED_PHP_NAME', 'gmerchantcenterpro.feed.php');
/* defines variable for front module controller for the cron */
define('_GMCP_CTRL_CRON', 'cron');
/* defines variable for front module controller for the fly output */
define('_GMCP_CTRL_FLY', 'fly');

/* defines variables to configuration settings */
$GLOBALS['GMCP_CONFIGURATION'] = array(
    'GMCP_VERSION' => '',
    'GMCP_HOME_CAT' => '',
    'GMCP_LINK' => '',
    'GMCP_ID_PREFIX' => '',
    'GMCP_AJAX_CYCLE' => 1000,
    'GMCP_EXPORT_OOS' => 1,
    'GMCP_COND' => 'new',
    'GMCP_P_COMBOS' => 1,
    'GMCP_P_DESCR_TYPE' => 3,
    'GMCP_IMG_SIZE' => version_compare(_PS_VERSION_, '1.7', '>=') ? ImageType::getFormattedName('large') : ImageType::getFormatedName('large'),
    'GMCP_EXC_NO_EAN' => 0,
    'GMCP_EXC_NO_MREF' => 0,
    'GMCP_MIN_PRICE' => 0,
    'GMCP_INC_STOCK' => 1,
    'GMCP_INC_FEAT' => 0,
    'GMCP_FEAT_OPT' => 0,
    'GMCP_INC_GENRE' => 0,
    'GMCP_GENRE_OPT' => 0,
    'GMCP_INC_SIZE' => 0,
    'GMCP_SIZE_OPT' => array(),
    'GMCP_INC_COLOR' => '',
    'GMCP_COLOR_OPT' => array(),
    'GMCP_INC_MATER' => 0,
    'GMCP_INC_ENERGY' => 0,
    'GMCP_EXCLUDED_DEST' => 0,
    'GMCP_INC_SHIPPING_LABEL' => 0,
    'GMCP_INC_UNIT_PRICING' => 0,
    'GMCP_INC_B_UNIT_PRICING' => 0,
    'GMCP_MATER_OPT' => 0,
    'GMCP_INC_PATT' => 0,
    'GMCP_PATT_OPT' => 0,
    'GMCP_INC_GEND' => 0,
    'GMCP_GEND_OPT' => 0,
    'GMCP_INC_ADULT' => 0,
    'GMCP_INC_COST' => 0,
    'GMCP_ADULT_OPT' => 0,
    'GMCP_INC_AGE' => 0,
    'GMCP_AGE_OPT' => 0,
    'GMCP_SHIP_CARRIERS' => '',
    'GMCP_REPORTING' => 1,
    'GMCP_HOME_CAT_ID' => 1,
    'GMCP_MPN_TYPE' => 'supplier_ref',
    'GMCP_INC_ID_EXISTS' => 0,
    'GMCP_ADD_CURRENCY' => 0,
    'GMCP_UTM_CAMPAIGN' => '',
    'GMCP_UTM_SOURCE' => '',
    'GMCP_UTM_MEDIUM' => '',
    'GMCP_UTM_CONTENT' => 0,
    'GMCP_FEED_PROTECTION' => 1,
    'GMCP_FEED_TOKEN' => md5(rand(1000, 1000000) . time()),
    'GMCP_EXPORT_MODE' => 0,
    'GMCP_ADV_PRODUCT_NAME' => 0,
    'GMCP_ADV_PROD_TITLE' => 0,
    'GMCP_CHECK_EXPORT' => '',
    'GMCP_FEED_TAX' => '',
    'GMCP_INC_TAG_ADULT' => 0,
    'GMCP_SHIPPING_USE' => 1,
    'GMCP_DSC_FILT_NAME' => 0,
    'GMCP_DSC_FILT_DATE' => 0,
    'GMCP_DSC_FILT_MIN_AMOUNT' => 0,
    'GMCP_DSC_FILT_VALUE' => 0,
    'GMCP_DSC_FILT_TYPE' => 0,
    'GMCP_DSC_FILT_CUMU' => 0,
    'GMCP_DSC_FILT_FOR' => 0,
    'GMCP_DSC_NAME' => '',
    'GMCP_DSC_DATE_FROM' => '',
    'GMCP_DSC_DATE_TO' => '',
    'GMCP_DSC_MIN_AMOUNT' => '',
    'GMCP_DSC_VALUE_MIN' => 0,
    'GMCP_DSC_VALUE_MAX' => 0,
    'GMCP_DSC_TYPE' => 0,
    'GMCP_DSC_CUMULABLE' => 0,
    'GMCP_INV_PRICE' => 0,
    'GMCP_INV_STOCK' => 0,
    'GMCP_INV_SALE_PRICE' => 0,
    'GMCP_CL_TYPE' => 'Manual',
    'GMCP_IMPORT_FROM_GMC' => 1,
    'GMCP_PROD_EXCL' => '',
    'GMCP_GTIN_PREF' => 'ean',
    'GMCP_SIZE_TYPE' => '',
    'GMCP_SIZE_SYSTEM' => '',
    'GMCP_FREE_SHIP_PROD' => '',
    'GMCP_URL_ATTR_ID_INCL' => (version_compare(_PS_VERSION_, '1.6.0.13', '>=') ? 1 : 0),
    'GMCP_URL_NUM_ATTR_REWRITE' => 0,
    'GMCP_MAX_WEIGHT' => 0,
    'GMCP_P_TITLE' => 'title',
    'GMCP_ADV_PROD_NAME_PREFIX' => array(),
    'GMCP_ADV_PROD_NAME_SUFFIX' => array(),
    'GMCP_FORBIDDEN_WORDS' => '',
    'GMCP_EXPORT_PROD_OOS_ORDER' => 0,
    'GMCP_ADD_IMAGES' => 1,
    'GMCP_CONF_STEP_1' => 0,
    'GMCP_CONF_STEP_2' => 0,
    'GMCP_CONF_STEP_3' => 0,
    'GMCP_SIMPLE_PROD_ID' => 0,
    'GMCP_FORCE_IDENTIFIER' => 0,
    'GMCP_API_KEY' => '',
    'GMCP_OAUTH' => array(),
    'GMCP_GSA_CUSTOMER_GROUP' => 3,
    'GMCP_GSA_DEFAULT_CARRIER' => 0,
    'GMCP_MERCHANT_ID' => '',
    'GMCP_SHOP_LINK_API' => 0,
    'GMCP_GSA_CARRIERS_MAP' => '',
    'GMCP_EXCLUDED_COUNTRY' => '',
    'GMCP_PROMO_DEST' => '',
    'GMCP_COMBO_SEPARATOR' => 'v',
    'GMCP_MIN_HANDLING_TIME' => '2',
    'GMCP_MAX_HANDLING_TIME' => '4',
    'GMCP_GSA_STOCK' => 100,
    'GMCP_FUNDED_PROMO' => 'none',
    'GMCP_DIMENSION' => 0,
    'GMCP_STORE_CODE' => '',
    'GMCP_LIA_PICKUP' => 'reserve',
    'GMCP_LIA_PICKUP_SLA' => 'same day',
);

/* defines variable to translate js msg */
$GLOBALS['GMCP_JS_MSG'] = array();

/* defines variable to define available weight units */
$GLOBALS['GMCP_WEIGHT_UNITS'] = array('kg', 'lb', 'g', 'oz');

/* defines variable to define available dimenstion units */
$GLOBALS['GMCP_DIMENSION_UNITS'] = array('cm', 'in');

/* defines variable to define default home cat name translations */
$GLOBALS['GMCP_HOME_CAT_NAME'] = array(
    'en' => 'home',
    'fr' => 'accueil',
    'it' => 'ignazio',
    'es' => 'ignacio',
);

$GLOBALS['GMCP_HOOKS'] = array(
    array('name' => 'actionOrderStatusUpdate', 'use' => false, 'title' => 'Order status update'),
    array('name' => 'moduleRoutes', 'use' => false, 'title' => 'Order status update'),
);

/* defines available languages / countries / currencies for Google */
$GLOBALS['GMCP_AVAILABLE_COUNTRIES'] = array(
    'en' => array(
        'IE' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'GB' => array('currency' => array('EUR', 'KES', 'NGN', 'PAB', 'PKR', 'DZD', 'AOA', 'BYN', 'KHR', 'XAF', 'XOF', 'ETB', 'GHS', 'JOD', 'KZT', 'KWD', 'LBP', 'MGA', 'MUR', 'MAD', 'MZN', 'MMK', 'NPR', 'NIO', 'OMR', 'PYG', 'PEN', 'RON', 'XOF', 'LKR', 'UGX', 'UYU', 'UZS', 'ZMW'), 'taxonomy' => 'en-US'),
        'US' => array('currency' => array('EUR', 'EUR', 'NGN', 'PAB', 'PKR', 'DZD', 'AOA', 'BYN', 'KHR', 'XAF', 'XOF', 'ETB', 'GHS', 'JOD', 'KZT', 'KWD', 'LBP', 'MGA', 'MUR', 'MAD', 'MZN', 'MMK', 'NPR', 'NIO', 'OMR', 'PYG', 'PEN', 'RON', 'XOF', 'LKR', 'UGX', 'UYU', 'UZS', 'ZMW'), 'taxonomy' => 'en-US'),
        'AU' => array('currency' => array('AUD'), 'taxonomy' => 'en-US'),
        'CA' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'IN' => array('currency' => array('INR'), 'taxonomy' => 'en-US'),
        'CH' => array('currency' => array('CHF'), 'taxonomy' => 'en-US'),
        'BE' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'DK' => array('currency' => array('DKK'), 'taxonomy' => 'en-US'),
        'NO' => array('currency' => array('NOK'), 'taxonomy' => 'en-US'),
        'MY' => array('currency' => array('MYR'), 'taxonomy' => 'en-US'),
        'ID' => array('currency' => array('RP'), 'taxonomy' => 'en-US'),
        'SE' => array('currency' => array('SEK'), 'taxonomy' => 'en-US'),
        'HK' => array('currency' => array('HKD'), 'taxonomy' => 'en-US'),
        'MX' => array('currency' => array('MXN'), 'taxonomy' => 'en-US'),
        'NZ' => array('currency' => array('NZD'), 'taxonomy' => 'en-US'),
        'PH' => array('currency' => array('PHP'), 'taxonomy' => 'en-US'),
        'SG' => array('currency' => array('SGD'), 'taxonomy' => 'en-US'),
        'TW' => array('currency' => array('TWD'), 'taxonomy' => 'en-US'),
        'AE' => array('currency' => array('AED', 'DZD', 'EGP', 'TND'), 'taxonomy' => 'en-US'),
        'DE' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'AT' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'NL' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'TR' => array('currency' => array('TRY'), 'taxonomy' => 'en-US'),
        'ZA' => array('currency' => array('ZAR'), 'taxonomy' => 'en-US'),
        'CZ' => array('currency' => array('CZK'), 'taxonomy' => 'en-US'),
        'IL' => array('currency' => array('ILS'), 'taxonomy' => 'en-US'),
        'VN' => array('currency' => array('VND'), 'taxonomy' => 'en-US'),
        'TH' => array('currency' => array('THB'), 'taxonomy' => 'en-US'),
        'KO' => array('currency' => array('KRW'), 'taxonomy' => 'en-US'),
        'AR' => array('currency' => array('ARS', 'CRC', 'DOP', 'GTQ'), 'taxonomy' => 'en-US'),
        'BR' => array('currency' => array('BRL'), 'taxonomy' => 'en-US'),
        'CL' => array('currency' => array('CLP'), 'taxonomy' => 'en-US'),
        'CO' => array('currency' => array('COP'), 'taxonomy' => 'en-US'),
        'IT' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'JP' => array('currency' => array('JPY'), 'taxonomy' => 'en-US'),
        'PL' => array('currency' => array('PLN'), 'taxonomy' => 'en-US'),
        'RU' => array('currency' => array('RUB', 'GEL'), 'taxonomy' => 'en-US'),
        'PT' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'SA' => array('currency' => array('AED', 'SAR', 'DZD', 'EGP'), 'taxonomy' => 'en-US'),
        'ES' => array('currency' => array('EUR', 'GTQ'), 'taxonomy' => 'en-US'),
        'GE' => array('currency' => array('KAS'), 'taxonomy' => 'en-US'),
        'UR' => array('currency' => array('PKR'), 'taxonomy' => 'en-US'),
        'VE' => array('currency' => array('VEF'), 'taxonomy' => 'en-US'),
        'SK' => array('currency' => array('EUR'), 'taxonomy' => 'en-US'),
        'HU' => array('currency' => array('HUF'), 'taxonomy' => 'en-US'),
        'KW' => array('currency' => array('KWD'), 'taxonomy' => 'en-US'),
    ),
    'gb' => array(
        'AU' => array('currency' => array('AUD'), 'taxonomy' => 'en-GB'),
        'IE' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'IN' => array('currency' => array('INR'), 'taxonomy' => 'en-GB'),
        'CH' => array('currency' => array('CHF'), 'taxonomy' => 'en-GB'),
        'BE' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'DK' => array('currency' => array('DKK'), 'taxonomy' => 'en-GB'),
        'NO' => array('currency' => array('NOK'), 'taxonomy' => 'en-GB'),
        'MY' => array('currency' => array('MYR'), 'taxonomy' => 'en-GB'),
        'ID' => array('currency' => array('IDR'), 'taxonomy' => 'en-GB'),
        'SE' => array('currency' => array('SEK'), 'taxonomy' => 'en-GB'),
        'HK' => array('currency' => array('HKD'), 'taxonomy' => 'en-GB'),
        'MX' => array('currency' => array('MXN'), 'taxonomy' => 'en-GB'),
        'NZ' => array('currency' => array('NZD'), 'taxonomy' => 'en-GB'),
        'PH' => array('currency' => array('PHP'), 'taxonomy' => 'en-GB'),
        'SG' => array('currency' => array('SGD'), 'taxonomy' => 'en-GB'),
        'TW' => array('currency' => array('TWD'), 'taxonomy' => 'en-GB'),
        'SA' => array('currency' => array('AED, SAR', 'DZD', 'EGP', 'TND'), 'taxonomy' => 'en-GB'),
        'DE' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'AT' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'NL' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'TR' => array('currency' => array('TRY'), 'taxonomy' => 'en-GB'),
        'ZA' => array('currency' => array('ZAR'), 'taxonomy' => 'en-GB'),
        'CZ' => array('currency' => array('CZK'), 'taxonomy' => 'en-GB'),
        'IL' => array('currency' => array('ILS'), 'taxonomy' => 'en-GB'),
        'VN' => array('currency' => array('VND'), 'taxonomy' => 'en-GB'),
        'TH' => array('currency' => array('THB'), 'taxonomy' => 'en-GB'),
        'US' => array('currency' => array('USD'), 'taxonomy' => 'en-GB'),
        'GB' => array('currency' => array('GBP'), 'taxonomy' => 'en-GB'),
        'KO' => array('currency' => array('KRW'), 'taxonomy' => 'en-GB'),
        'AR' => array('currency' => array('ARS', 'CRC', 'DOP', 'GTQ'), 'taxonomy' => 'en-GB'),
        'BR' => array('currency' => array('BRL'), 'taxonomy' => 'en-GB'),
        'CL' => array('currency' => array('CLP'), 'taxonomy' => 'en-GB'),
        'CO' => array('currency' => array('COP'), 'taxonomy' => 'en-GB'),
        'IT' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'JP' => array('currency' => array('JPY'), 'taxonomy' => 'en-GB'),
        'PL' => array('currency' => array('PLN'), 'taxonomy' => 'en-GB'),
        'RU' => array('currency' => array('RUB', 'GEL'), 'taxonomy' => 'en-GB'),
        'PT' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'ES' => array('currency' => array('EUR', 'GTQ'), 'taxonomy' => 'en-GB'),
        'GE' => array('currency' => array('KAS'), 'taxonomy' => 'en-GB'),
        'UR' => array('currency' => array('PKR'), 'taxonomy' => 'en-GB'),
        'VE' => array('currency' => array('VEF'), 'taxonomy' => 'en-GB'),
        'SK' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
        'HU' => array('currency' => array('HUF'), 'taxonomy' => 'en-GB'),
    ),
    'fr' => array(
        'FR' => array('currency' => array('EUR', 'TND', 'DZD', 'XAF', 'XOF', 'MGA', 'MAD'), 'taxonomy' => 'fr-FR'),
        'CH' => array('currency' => array('CHF'), 'taxonomy' => 'fr-FR'),
        'CA' => array('currency' => array('EUR'), 'taxonomy' => 'fr-FR'),
        'BE' => array('currency' => array('EUR'), 'taxonomy' => 'fr-FR'),
        'SA' => array('currency' => array('DZD'), 'taxonomy' => 'fr-FR'),
    ),
    'de' => array(
        'EN' => array('currency' => array('EUR'), 'taxonomy' => 'de-DE'),
        'DE' => array('currency' => array('EUR'), 'taxonomy' => 'de-DE'),
        'CH' => array('currency' => array('CHF'), 'taxonomy' => 'de-DE'),
        'AT' => array('currency' => array('EUR'), 'taxonomy' => 'de-DE'),
        'BE' => array('currency' => array('EUR'), 'taxonomy' => 'de-DE'),
    ),
    'it' => array(
        'IT' => array('currency' => array('EUR'), 'taxonomy' => 'it-IT'),
        'CH' => array('currency' => array('CHF'), 'taxonomy' => 'it-IT')
    ),
    'nl' => array(
        'NL' => array('currency' => array('EUR'), 'taxonomy' => 'nl-NL'),
        'BE' => array('currency' => array('EUR'), 'taxonomy' => 'nl-NL')
    ),
    'es' => array(
        'ES' => array('currency' => array('EUR', 'MXN', 'ARS', 'CLP', 'COP', 'USD', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
        'MX' => array('currency' => array('MXN', 'EUR', 'ARS', 'CLP', 'COP', 'USD', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
        'AR' => array('currency' => array('ARS', 'EUR', 'MXN', 'CLP', 'COP', 'USD', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
        'CL' => array('currency' => array('CLP', 'EUR', 'MXN', 'ARS', 'COP', 'USD', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
        'CO' => array('currency' => array('COP', 'EUR', 'MXN', 'ARS', 'CLP', 'USD', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
        'US' => array('currency' => array('USD', 'EUR', 'MXN', 'ARS', 'CLP', 'COP', 'CRC', 'GTQ', 'PYG', 'NIO', 'PEN', 'UYU'), 'taxonomy' => 'es-ES'),
    ),

    'mx' => array(
        'ES' => array('currency' => array('EUR', 'MXN', 'ARS', 'CLP', 'COP', 'USD'), 'taxonomy' => 'es-ES'),
        'MX' => array('currency' => array('EUR', 'MXN', 'ARS', 'CLP', 'COP'), 'taxonomy' => 'es-ES'),
        'AR' => array('currency' => array('ARS', 'EUR', 'MXN', 'CLP', 'COP', 'USD'), 'taxonomy' => 'es-ES'),
        'CL' => array('currency' => array('CLP', 'EUR', 'MXN', 'ARS', 'COP', 'USD'), 'taxonomy' => 'es-ES'),
        'CO' => array('currency' => array('COP', 'EUR', 'MXN', 'ARS', 'CLP', 'USD'), 'taxonomy' => 'es-ES'),
        'US' => array('currency' => array('USD', 'EUR', 'MXN', 'ARS', 'CLP', 'COP'), 'taxonomy' => 'es-ES'),
    ),
    'ca' => array(
        'ES' => array('currency' => array('EUR'), 'taxonomy' => 'es-ES'),
    ),
    'zh' => array(
        'CN' => array('currency' => array('CNY'), 'taxonomy' => 'zh-CN'),
        'EN' => array('currency' => array('CNY'), 'taxonomy' => 'zh-CN'),
        'HK' => array('currency' => array('HKD'), 'taxonomy' => 'zh-CN'),
        'TW' => array('currency' => array('TWD'), 'taxonomy' => 'zh-CN'),
        'AU' => array('currency' => array('AUD'), 'taxonomy' => 'zh-CN'),
        'CA' => array('currency' => array('CAD'), 'taxonomy' => 'zh-CN'),
        'US' => array('currency' => array('USD'), 'taxonomy' => 'zh-CN'),
        'SG' => array('currency' => array('SGD'), 'taxonomy' => 'zh-CN'),
    ),
    'ja' => array(
        'JP' => array('currency' => array('JPY'), 'taxonomy' => 'ja-JP')
    ),
    'br' => array(
        'BR' => array('currency' => array('BRL'), 'taxonomy' => 'pt-BR')
    ),
    'cs' => array(
        'CZ' => array('currency' => array('CZK'), 'taxonomy' => 'cs-CZ')
    ),
    'ru' => array(

        'RU' => array('currency' => array('RUB', 'BYR', 'GEL', 'BYN', 'KZT', 'KWD', 'UZS'), 'taxonomy' => 'ru-RU'),
        'UA' => array('currency' => array('UAH'), 'taxonomy' => 'ru-RU')
    ),
    'sv' => array(
        'SE' => array('currency' => array('SEK'), 'taxonomy' => 'sv-SE'),
        'EN' => array('currency' => array('SEK'), 'taxonomy' => 'sv-SE')
    ),
    'da' => array(
        'DK' => array('currency' => array('DKK'), 'taxonomy' => 'da-DK'),
        'EN' => array('currency' => array('DKK'), 'taxonomy' => 'da-DK')
    ),
    'no' => array(
        'NO' => array('currency' => array('NOK'), 'taxonomy' => 'no-NO')
    ),
    'pl' => array(
        'PL' => array('currency' => array('PLN'), 'taxonomy' => 'pl-PL')
    ),
    'tr' => array(
        'TR' => array('currency' => array('TRY'), 'taxonomy' => 'tr-TR')
    ),
    'ms' => array(
        'MY' => array('currency' => array('MYR'), 'taxonomy' => 'en-US')
    ),
    'pt' => array(
        'PT' => array('currency' => array('EUR', 'AOA', 'MZN'), 'taxonomy' => 'es-ES')
    ),
    'ar' => array(
        'SA' => array('currency' => array('SAR', 'AED', 'DZD', 'CRC', 'EGP', 'TND', 'DZD', 'JOD', 'LBP', 'MAD', 'OMR'), 'taxonomy' => 'ar-SA'),
        'AE' => array('currency' => array('AED', 'SAR', 'DZD', 'EGP', 'DZD', 'JOD'), 'taxonomy' => 'ar-SA'),
        'KW' => array('currency' => array('KWD'), 'taxonomy' => 'ar-SA'),
    ),
    'id' => array(
        'ID' => array('currency' => array('IDR'), 'taxonomy' => 'en-US'),
    ),
    'he' => array(
        'IL' => array('currency' => array('ILS'), 'taxonomy' => 'en-US'),
    ),
    'vn' => array(
        'VN' => array('currency' => array('VND'), 'taxonomy' => 'en-US'),
    ),
    'uk' => array(
        'UA' => array('currency' => array('UAH'), 'taxonomy' => 'en-US'),
    ),
    'th' => array(
        'TH' => array('currency' => array('THB'), 'taxonomy' => 'en-US'),
    ),
    'ko' => array(
        'KO' => array('currency' => array('KRW'), 'taxonomy' => 'en-US'),
    ),
    'fi' => array(
        'FI' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
    ),
    'hu' => array(
        'HU' => array('currency' => array('HUF'), 'taxonomy' => 'en-GB'),
    ),
    'ag' => array(
        'AR' => array('currency' => array('CRC', 'DOP', 'GTQ'), 'taxonomy' => 'es-ES'),
    ),
    'ur' => array(
        'UR' => array('currency' => array('PKR'), 'taxonomy' => 'en-US'),
    ),
    've' => array(
        'VE' => array('currency' => array('VEF'), 'taxonomy' => 'es-ES'),
    ),
    'sk' => array(
        'SK' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
    ),
    'ro' => array(
        'RO' => array('currency' => array('RON'), 'taxonomy' => 'en-GB'),
    ),
    'el' => array(
        'GR' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
    ),
    'lt' => array(
        'LT' => array('currency' => array('EUR'), 'taxonomy' => 'en-GB'),
    ),
    'qc' => array(
        'CA' => array('currency' => array('cAD'), 'taxonomy' => 'fr-FR')
    ),
);
/* defines variable to set request parameters */
$GLOBALS['GMCP_MONTH'] = array(
    'en' => array(
        'short' => array(
            '',
            'Jan.',
            'Feb.',
            'March',
            'Apr.',
            'May',
            'June',
            'July',
            'Aug.',
            'Sept.',
            'Oct.',
            'Nov.',
            'Dec.'
        ),
        'long' => array(
            '',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ),
    ),
    'fr' => array(
        'short' => array(
            '',
            'Jan.',
            'F&eacute;v.',
            'Mars',
            'Avr.',
            'Mai',
            'Juin',
            'Juil.',
            'Aout',
            'Sept.',
            'Oct.',
            'Nov.',
            'D&eacute;c.'
        ),
        'long' => array(
            '',
            'Janvier',
            'F&eacute;vrier',
            'Mars',
            'Avril',
            'Mai',
            'Juin',
            'Juillet',
            'Aout',
            'Septembre',
            'Octobre',
            'Novembre',
            'D&eacute;cembre'
        ),
    ),
    'de' => array(
        'short' => array(
            '',
            'Jan.',
            'Feb.',
            'M' . chr(132) . 'rz',
            'Apr.',
            'Mai',
            'Juni',
            'Juli',
            'Aug.',
            'Sept.',
            'Okt.',
            'Nov.',
            'Dez.'
        ),
        'long' => array(
            '',
            'Januar',
            'Februar',
            'M' . chr(132) . 'rz',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ),
    ),
    'it' => array(
        'short' => array(
            '',
            'Gen.',
            'Feb.',
            'Marzo',
            'Apr.',
            'Mag.',
            'Giu.',
            'Lug.',
            'Ago.',
            'Sett.',
            'Ott.',
            'Nov.',
            'Dic.'
        ),
        'long' => array(
            '',
            'Gennaio',
            'Febbraio',
            'Marzo',
            'Aprile',
            'Maggio',
            'Giugno',
            'Luglio',
            'Agosto',
            'Settembre',
            'Ottobre',
            'Novembre',
            'Dicembre'
        ),
    ),
    'es' => array(
        'short' => array(
            '',
            'Ene.',
            'Feb.',
            'Marzo',
            'Abr.',
            'Mayo',
            'Junio',
            'Jul.',
            'Ago.',
            'Sept.',
            'Oct.',
            'Nov.',
            'Dic.'
        ),
        'long' => array(
            '',
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ),
    ),
);
