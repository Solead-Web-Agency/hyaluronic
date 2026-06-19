<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

class CedWishHelper
{
    const WISH_STATIC_FILES_PATH = _PS_MODULE_DIR_ . 'cedwish/static_files/';
    const WISH_REGISTER_MERCHANT = 'https://apps.cedcommerce.com/marketplace-integration/wish/api/register-merchant';
    const WISH_GET_MERCHANT = 'https://apps.cedcommerce.com/marketplace-integration/wish/api/merchant?merchant_id=';
    const WISH_PUT_MERCHANT = 'https://apps.cedcommerce.com/marketplace-integration/wish/api/updatedata?merchant_id=';
    const WISH_MERCHANT_BEARER = 'RVrZY2NfmtcoiOBVeWdVrCCBlQCAGXrR';

    public static function getLanguageId()
    {
        if (Configuration::get('CED_WISH_LANG_ID')) {
            $language_id = Configuration::get('CED_WISH_LANG_ID');
        } elseif (Configuration::get('PS_DEFAULT_LANG')) {
            $language_id = Configuration::get('PS_DEFAULT_LANG');
        } else {
            $language_id = Context::getContext()->language->id;
        }
        return (int)$language_id;
    }

    public static function getCurrencyId()
    {
        if (Configuration::get('CED_WISH_CURRENCY_ID')) {
            $currency_id = Configuration::get('CED_WISH_CURRENCY_ID');
        } elseif (Configuration::get('PS_DEFAULT_CURRENCY')) {
            $currency_id = Configuration::get('PS_DEFAULT_CURRENCY');
        } else {
            $currency_id = Context::getContext()->currency->id;
        }
        return $currency_id;
    }

    public static function addLog($message, $level = FileLogger::ERROR)
    {
        if (Configuration::get('CED_WISH_DEBUG_MODE_ENABLE')) {
            try {
                $log = new FileLogger();
                if (is_dir(_PS_ROOT_DIR_ . '/var/logs')) {
                    $filename = _PS_ROOT_DIR_ . '/var/logs/cedwish.log';
                } elseif (is_dir(_PS_ROOT_DIR_ . '/log')) {
                    $filename = _PS_ROOT_DIR_ . '/log/cedwish.log';
                }
                if (!file_exists($filename)) {
                    $fp = @fopen($filename, "a+");
                    fclose($fp);
                }
                $log->setFilename($filename);
                $log->log($message, $level);
            } catch (Exception $e) {
                PrestaShopLogger::addLog($e->getMessage());
            }
        }
    }

    public static function getCarriers($force = false)
    {
        $api = new CedWishApi();
        if (!is_dir(self::WISH_STATIC_FILES_PATH)) {
            mkdir(self::WISH_STATIC_FILES_PATH, 0777, true);
        }

        if (!$force && file_exists(self::WISH_STATIC_FILES_PATH . 'carriers.json')) {
            return json_decode(Tools::file_get_contents(self::WISH_STATIC_FILES_PATH . 'carriers.json'), true);
        } else {
            $carriers = $api->getCarriers();
            if (isset($carriers['code']) && ($carriers['code'] == 0)) {
                file_put_contents(self::WISH_STATIC_FILES_PATH . 'carriers.json', json_encode($carriers['data']));
                return $carriers['data'];
            } else {
                return array();
            }
        }
    }

    public static function getAcceptedColors($force = false)
    {
        $api = new CedWishApi();
        if (!is_dir(self::WISH_STATIC_FILES_PATH)) {
            mkdir(self::WISH_STATIC_FILES_PATH, 0777, true);
        }

        if (!$force && file_exists(self::WISH_STATIC_FILES_PATH . 'colors.json')) {
            return json_decode(Tools::file_get_contents(self::WISH_STATIC_FILES_PATH . 'colors.json'), true);
        } else {
            $carriers = $api->getAcceptedColors();
            if (isset($carriers['code']) && ($carriers['code'] == 0)) {
                file_put_contents(self::WISH_STATIC_FILES_PATH . 'colors.json', json_encode($carriers['data']));
                return $carriers['data'];
            } else {
                return array();
            }
        }
    }

    public static function getWishMappedCarrier($id_carrier)
    {
        $mappings = Configuration::get('CED_WISH_CARRIER_MAPPING');
        $id_marketplace_carrier = false;
        if ($mappings) {
            $mappings = json_decode($mappings, true);
            if (is_array($mappings) && !empty($mappings)) {
                foreach ($mappings as $mapping) {
                    if ((int)$id_carrier == (int)$mapping['id_carrier']) {
                        $id_marketplace_carrier = $mapping['id_marketplace_carrier'];
                        break;
                    }
                }
            }
        }
        return $id_marketplace_carrier;
    }

    public static function getWishMappedStateIdByWishState($state)
    {
        $mappings = Configuration::get('CED_WISH_STATUS_MAPPING');
        $id_marketplace_status = false;
        if ($mappings) {
            $mappings = json_decode($mappings, true);
            if (is_array($mappings) && !empty($mappings)) {
                foreach ($mappings as $mapping) {
                    if ($state == $mapping['marketplace_status']) {
                        $id_marketplace_status = $mapping['order_status'];
                        break;
                    }
                }
            }
        }
        return $id_marketplace_status;
    }

    public static function getShippableCountries()
    {
        return array(
            'AE' => 'United Arab Emirates',
            'AL' => 'Albania',
            'AR' => 'Argentina',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'BM' => 'Bermuda',
            'BR' => 'Brazil',
            'CA' => 'Canada',
            'CH' => 'Switzerland',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DK' => 'Denmark',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'ES' => 'Spain',
            'FI' => 'Finland',
            'FR' => 'France',
            'GB' => 'United Kingdom (Great Britain)',
            'GR' => 'Greece',
            'HK' => 'Hong Kong',
            'HR' => 'Croatia',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IN' => 'India',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NZ' => 'New Zealand',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PR' => 'Puerto Rico',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'SA' => 'Saudi Arabia',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SI' => 'Slovenia',
            'SK' => 'Slovakia',
            'TH' => 'Thailand',
            'TR' => 'Turkey',
            'TW' => 'Taiwan',
            'UA' => 'Ukraine',
            'US' => 'United States',
            'VE' => 'Venezuela',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'VN' => 'Vietnam',
            'ZA' => 'South Africa'
        );
    }

    public static function getShippingRegions()
    {
        return array(
            'AU' => array(
                array(
                    'AU_ACT',
                    'Australian Capital Territory',
                    'AU',
                    'True'
                ),
                array(
                    'AU_JBT',
                    'Jervis Bay Territory',
                    'AU',
                    'True'
                ),
                array(
                    'AU_NSW',
                    'New South Wales',
                    'AU',
                    'True'
                ),
                array(
                    'AU_NT',
                    'Northern Territory',
                    'AU',
                    'True'
                ),
                array(
                    'AU_QLD',
                    'Queensland',
                    'AU',
                    'True'
                ),
                array(
                    'AU_SA',
                    'South Australia',
                    'AU',
                    'True'
                ),
                array(
                    'AU_TAS',
                    'Tasmania',
                    'AU',
                    'True'
                ),
                array(
                    'AU_VIC',
                    'Victoria',
                    'AU',
                    'True'
                ),
                array(
                    'AU_WA',
                    'Western Australia',
                    'AU',
                    'True'
                )
            ),
            'BR' => array(
                array(
                    'BR_BAC',
                    'BA Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_BAI',
                    'BA Interior, SE, & Sede Recife',
                    'BR',
                    'True'
                ),
                array(
                    'BR_MGC',
                    'MG Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_MGI',
                    'MG Interior',
                    'BR',
                    'True'
                ),
                array(
                    'BR_PRC',
                    'PR Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_PRI',
                    'PR Interior, SC, & RS Interior',
                    'BR',
                    'True'
                ),
                array(
                    'BR_RJC',
                    'RJ Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_RJI',
                    'RJ Interior & ES',
                    'BR',
                    'True'
                ),
                array(
                    'BR_RSC',
                    'RS Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_SFB',
                    'Sede Fortaleza & Sede Brasilia',
                    'BR',
                    'True'
                ),
                array(
                    'BR_SPC',
                    'SP Capital & Metro Area',
                    'BR',
                    'True'
                ),
                array(
                    'BR_SPI',
                    'SP Interior',
                    'BR',
                    'True'
                )
            ),
            'DK' => array(
                array(
                    'DK_GL',
                    'Greenland',
                    'DK',
                    'True'
                )
            ),
            'ES' => array(
                array(
                    'ES_CN',
                    'Canary Islands',
                    'ES',
                    'True'
                )
            ),
            'FR' => array(
                array(
                    'FR_GF',
                    'French Guiana',
                    'FR',
                    'True'
                ),
                array(
                    'FR_GP',
                    'Guadeloupe',
                    'FR',
                    'True'
                ),
                array(
                    'FR_MF',
                    'Saint Martin',
                    'FR',
                    'True'
                ),
                array(
                    'FR_MQ',
                    'Martinique',
                    'FR',
                    'True'
                ),
                array(
                    'FR_NC',
                    'New Caledonia',
                    'FR',
                    'True'
                ),
                array(
                    'FR_PF',
                    'French Polynesia',
                    'FR',
                    'True'
                ),
                array(
                    'FR_PM',
                    'Saint Pierre and Miquelon',
                    'FR',
                    'True'
                ),
                array(
                    'FR_RE',
                    'Reunion',
                    'FR',
                    'True'
                ),
                array(
                    'FR_WF',
                    'Wallis and Futuna',
                    'FR',
                    'True'
                ),
                array(
                    'FR_YT',
                    'Mayotte',
                    'FR',
                    'True'
                )
            ),
            'US' => array(
                array(
                    'US_AA',
                    'AA',
                    'US',
                    'True'
                ),
                array(
                    'US_AE',
                    'AE',
                    'US',
                    'True'
                ),
                array(
                    'US_AK',
                    'Alaska',
                    'US',
                    'True'
                ),
                array(
                    'US_AL',
                    'Alabama',
                    'US',
                    'True'
                ),
                array(
                    'US_AP',
                    'AP',
                    'US',
                    'True'
                ),
                array(
                    'US_AR',
                    'Arkansas',
                    'US',
                    'True'
                ),
                array(
                    'US_AS',
                    'American Samoa',
                    'US',
                    'True'
                ),
                array(
                    'US_AZ',
                    'Arizona',
                    'US',
                    'True'
                ),
                array(
                    'US_CA',
                    'California',
                    'US',
                    'True'
                ),
                array(
                    'US_CO',
                    'Colorado',
                    'US',
                    'True'
                ),
                array(
                    'US_CT',
                    'Connecticut',
                    'US',
                    'True'
                ),
                array(
                    'US_DC',
                    'District of Columbia',
                    'US',
                    'True'
                ),
                array(
                    'US_DE',
                    'Delaware',
                    'US',
                    'True'
                ),
                array(
                    'US_FL',
                    'Florida',
                    'US',
                    'True'
                ),
                array(
                    'US_GA',
                    'Georgia',
                    'US',
                    'True'
                ),
                array(
                    'US_GU',
                    'Guam',
                    'US',
                    'True'
                ),
                array(
                    'US_HI',
                    'Hawaii',
                    'US',
                    'True'
                ),
                array(
                    'US_IA',
                    'Iowa',
                    'US',
                    'True'
                ),
                array(
                    'US_ID',
                    'Idaho',
                    'US',
                    'True'
                ),
                array(
                    'US_IL',
                    'Illinois',
                    'US',
                    'True'
                ),
                array(
                    'US_IN',
                    'Indiana',
                    'US',
                    'True'
                ),
                array(
                    'US_KS',
                    'Kansas',
                    'US',
                    'True'
                ),
                array(
                    'US_KY',
                    'Kentucky',
                    'US',
                    'True'
                ),
                array(
                    'US_LA',
                    'Louisiana',
                    'US',
                    'True'
                ),
                array(
                    'US_MA',
                    'Massachusetts',
                    'US',
                    'True'
                ),
                array(
                    'US_MD',
                    'Maryland',
                    'US',
                    'True'
                ),
                array(
                    'US_ME',
                    'Maine',
                    'US',
                    'True'
                ),
                array(
                    'US_MI',
                    'Michigan',
                    'US',
                    'True'
                ),
                array(
                    'US_MN',
                    'Minnesota',
                    'US',
                    'True'
                ),
                array(
                    'US_MO',
                    'Missouri',
                    'US',
                    'True'
                ),
                array(
                    'US_MP',
                    'Northern Mariana Islands',
                    'US',
                    'True'
                ),
                array(
                    'US_MS',
                    'Mississippi',
                    'US',
                    'True'
                ),
                array(
                    'US_MT',
                    'Montana',
                    'US',
                    'True'
                ),
                array(
                    'US_NC',
                    'North Carolina',
                    'US',
                    'True'
                ),
                array(
                    'US_ND',
                    'North Dakota',
                    'US',
                    'True'
                ),
                array(
                    'US_NE',
                    'Nebraska',
                    'US',
                    'True'
                ),
                array(
                    'US_NH',
                    'New Hampshire',
                    'US',
                    'True'
                ),
                array(
                    'US_NJ',
                    'New Jersey',
                    'US',
                    'True'
                ),
                array(
                    'US_NM',
                    'New Mexico',
                    'US',
                    'True'
                ),
                array(
                    'US_NV',
                    'Nevada',
                    'US',
                    'True'
                ),
                array(
                    'US_NY',
                    'New York',
                    'US',
                    'True'
                ),
                array(
                    'US_OH',
                    'Ohio',
                    'US',
                    'True'
                ),
                array(
                    'US_OK',
                    'Oklahoma',
                    'US',
                    'True'
                ),
                array(
                    'US_OR',
                    'Oregon',
                    'US',
                    'True'
                ),
                array(
                    'US_PA',
                    'Pennsylvania',
                    'US',
                    'True'
                ),
                array(
                    'US_RI',
                    'Rhode Island',
                    'US',
                    'True'
                ),
                array(
                    'US_SC',
                    'South Carolina',
                    'US',
                    'True'
                ),
                array(
                    'US_SD',
                    'South Dakota',
                    'US',
                    'True'),
                array(
                    'US_TN',
                    'Tennessee',
                    'US',
                    'True'
                ),
                array(
                    'US_TX',
                    'Texas',
                    'US',
                    'True'
                ),
                array(
                    'US_UT',
                    'Utah',
                    'US',
                    'True'
                ),
                array(
                    'US_VA',
                    'Virginia',
                    'US',
                    'True'
                ),
                array(
                    'US_VT',
                    'Vermont',
                    'US',
                    'True'
                ),
                array(
                    'US_WA',
                    'Washington',
                    'US',
                    'True'
                ),
                array(
                    'US_WI',
                    'Wisconsin',
                    'US',
                    'True'
                ),
                array(
                    'US_WV',
                    'West Virginia',
                    'US',
                    'True'
                ),
                array(
                    'US_WY',
                    'Wyoming',
                    'US',
                    'True'
                )
            )
        );
    }

    public static function getOrderStatuses()
    {
        return array(
            "APPROVED",
            "SHIPPED",
            "REFUNDED",
            "CANCELLED",
            "REQUIRE_REVIEW"
        );
    }

    public static function stepInstallation()
    {
        $params = self::getInstallationStats();
        self::registerSellerInfo($params);
    }

    public static function getInstallationStats()
    {
        $partner_merchant_id = Configuration::get('CED_WISH_MERCHANT_ID');
        if (!$partner_merchant_id) {
            $partner_merchant_id = _PS_BASE_URL_;
        }
        return array(
            "owner_name" => Configuration::get('PS_SHOP_NAME'),
            "partner_merchant_id" => $partner_merchant_id,
            "registration_date" => date("Y-m-d h:i:s"),
            "email" => Configuration::get('PS_SHOP_EMAIL'),
            "website_url" => _PS_BASE_URL_,
            "store_platform_name" => 'PrestaShop',
            "product_categories" => self::getCategoriesSelling(),
            "address" => array(
                "street_1" => Configuration::get('PS_SHOP_ADDR1'),
                "street_2" => Configuration::get('PS_SHOP_ADDR2'),
                "city" => Configuration::get('PS_SHOP_CITY'),
                "state" => State::getNameById(Configuration::get('PS_SHOP_STATE_ID')),
                "country_code" => Country::getNameById(
                    Configuration::get('PS_LANG_DEFAULT'),
                    Configuration::get('PS_SHOP_COUNTRY_ID')
                ),
                "postal_code" => Configuration::get('PS_SHOP_CODE'),
            ),
            "ip_address" => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1',
            "phone" => Configuration::get('PS_SHOP_PHONE'),
            "store_platform_version" => _PS_VERSION_,
            "marketplace_seller" => "Y",
            "priority" => "LOW",
            "account_type" => "bussiness",
            "current_step" => "INSTALLATION",
            "seller_currency" => Configuration::get('CED_WISH_WISH_CURRENCY'),
            "total_orders" => Db::getInstance()->getValue(
                "SELECT COUNT(id_cedwish_order) FROM `" . _DB_PREFIX_ . "cedwish_order`"
            ),
            "total_live_items" => Db::getInstance()->getValue(
                "SELECT COUNT(id_cedwish_product) FROM `" . _DB_PREFIX_ . "cedwish_product` WHERE 
                (marketplace_id !='') AND (id_product_attribute = 0)"
            ),
            "total_sales" => self::getTotalSales(),
            "order_info_url" => Context::getContext()->link->getModuleLink(
                'cedwish',
                'support',
                array(
                    'secure_key' => Configuration::get('CED_WISH_MERCHANT_ID'),
                    'order' => true
                )
            ),
            "bestseller_url" => Context::getContext()->link->getModuleLink(
                'cedwish',
                'support',
                array(
                    'secure_key' => Configuration::get('CED_WISH_MERCHANT_ID'),
                    'product' => true
                )
            )
        );
    }

    public static function getCategoriesSelling()
    {
        try {
            $profile_categories = Db::getInstance()->executeS(
                "SELECT `categories` FROM `" . _DB_PREFIX_ . "cedwish_profile`"
            );
            $categories = array();
            if (!empty($profile_categories)) {
                $profile_categories = array_column($profile_categories, 'categories');
                if (!empty($profile_categories)) {
                    $ids = array();
                    foreach ($profile_categories as $category) {
                        if (!empty(json_decode($category, true))) {
                            $ids = array_merge($ids, json_decode($category, true));
                        }
                    }
                    if (!empty($ids)) {
                        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                            'SELECT cl.`name` FROM `' . _DB_PREFIX_ . 'category` c LEFT JOIN 
                            `' . _DB_PREFIX_ . 'category_lang` cl 
                            ON (c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
                            ' . Shop::addSqlAssociation('category', 'c') . '
                            WHERE cl.`id_lang` = ' . (int)Context::getContext()->language->id . '
                            AND c.`id_category` IN (' . implode(',', array_map('intval', $ids)) . ')'
                        );
                        if (!empty($results)) {
                            $categories = array_column($results, 'name');
                        }
                    }
                }
            }
            return $categories;
        } catch (PrestaShopDatabaseException $e) {
            return array();
        }
    }

    public static function getTotalSales()
    {
        $amount = 0;
        try {
            $orders = Db::getInstance()->executeS(
                "SELECT wish_order FROM `" . _DB_PREFIX_ . "cedwish_order`"
            );
        } catch (PrestaShopDatabaseException $e) {
            return $amount;
        }

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order_data = @json_decode(Tools::getDescriptionClean($order['wish_order']), true);
                if ($order_data && is_array($order_data)) {
                    if (isset($order_data['order_payment']['general_payment_details']['payment_total']['amount'])
                        && $order_data['order_payment']['general_payment_details']['payment_total']['amount']
                    ) {
                        $amount
                            += (float)$order_data['order_payment']['general_payment_details']['payment_total']
                        ['amount'];
                    }
                }
            }
        }
        return $amount;
    }

    public static function updateInfo()
    {
        if (!Configuration::get('CED_WISH_INFO_UPDATE')
            || (date("Y-m-d") == Configuration::get('CED_WISH_INFO_UPDATE'))
        ) {
            Configuration::updateValue('CED_WISH_INFO_UPDATE', date("Y-m-d", strtotime("+7 days")));
            $params = self::getInstallationStats();
            if (($params['total_orders'] > 0) && ($params['total_live_items'] > 0)) {
                $params['current_step'] = "ONBOARDED";
            }

            if (($params['total_orders'] == 0) && ($params['total_live_items'] > 0)) {
                $params['current_step'] = "PRODUCT_CREATED";
            }
            self::registerSellerInfo($params);
        }
    }

    public static function registerSellerInfo($params)
    {
        $merchant_id = $params['partner_merchant_id'];
        $get_url = self::WISH_GET_MERCHANT . $merchant_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $get_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . self::WISH_MERCHANT_BEARER));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $server_output = curl_exec($ch);
        $server_output = @json_decode($server_output, true);
        curl_close($ch);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (isset($params['address']) && !empty($params['address'])) {
            $params['address'] = json_encode($params['address']);
        }

        if (isset($params['product_categories']) && !empty($params['product_categories'])) {
            $params['product_categories'] = json_encode($params['product_categories']);
        }

        if ($server_output
            && isset($server_output['status'])
            && $server_output['status']
            && isset($server_output['data']['merchant_id'])
            && ($server_output['data']['merchant_id'] == $merchant_id)
        ) {
            curl_setopt($ch, CURLOPT_URL, self::WISH_PUT_MERCHANT . $merchant_id);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Authorization: Bearer ' . self::WISH_MERCHANT_BEARER,
                    'Content-Type: application/x-www-form-urlencoded'
                )
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, self::WISH_REGISTER_MERCHANT);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Authorization: Bearer ' . self::WISH_MERCHANT_BEARER
                )
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        curl_exec($ch);
        curl_close($ch);
    }
}
