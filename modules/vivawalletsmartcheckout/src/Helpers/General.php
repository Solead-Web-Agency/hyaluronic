<?php
/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Vivawalletsmartcheckout\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class General
{
    /**
     * Retrieve all children of an array
     *
     * @param array $array
     *
     * @return array Children of array
     */
    public static function getAllChildrenOfArray(array $array): array
    {
        $children = [];
        array_walk_recursive(
            $array,
            function ($v) use (&$children) {
                $children[] = $v;
            }
        );

        return $children;
    }

    /**
     * Get value of array by  given key
     *
     * @param array $array
     * @param string $key key must have the format '{filename}.{path_of_value_separated_by_dot}'
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public static function getArrayValueByKey(array $array, string $key, $default = null)
    {
        $keyParts = explode('.', $key);
        $keyPart = array_shift($keyParts);
        if (isset($array[$keyPart])) {
            if (empty($keyParts)) {
                return $array[$keyPart];
            } elseif (is_array($array[$keyPart])) {
                return self::getArrayValueByKey($array[$keyPart], implode('.', $keyParts));
            }
        }

        return $default;
    }

    /**
     * Convert an array of currency iso code num to currency iso code
     *
     * @param array $isoCodeNums
     *
     * @return array
     */
    public static function convertIsoCodeNumArrayToIsoCode(array $isoCodeNums): array
    {
        return !empty($isoCodeNums) ?
            array_keys(array_intersect(array_flip(self::getCurrencyIsoCodeNumToIsoCodeArray()), $isoCodeNums))
            : [];
    }

    /**
     * Get an array with the mapping of currency iso code num and currency iso code
     */
    public static function getCurrencyIsoCodeNumToIsoCodeArray(): array
    {
        return [
            '784' => 'AED',
            '971' => 'AFN',
            '008' => 'ALL',
            '051' => 'AMD',
            '532' => 'ANG',
            '973' => 'AOA',
            '032' => 'ARS',
            '036' => 'AUD',
            '533' => 'AWG',
            '944' => 'AZN',
            '977' => 'BAM',
            '052' => 'BBD',
            '050' => 'BDT',
            '975' => 'BGN',
            '048' => 'BHD',
            '108' => 'BIF',
            '060' => 'BMD',
            '096' => 'BND',
            '068' => 'BOB',
            '984' => 'BOV',
            '986' => 'BRL',
            '044' => 'BSD',
            '064' => 'BTN',
            '072' => 'BWP',
            '933' => 'BYN',
            '084' => 'BZD',
            '124' => 'CAD',
            '976' => 'CDF',
            '947' => 'CHE',
            '756' => 'CHF',
            '948' => 'CHW',
            '990' => 'CLF',
            '152' => 'CLP',
            '156' => 'CNY',
            '170' => 'COP',
            '970' => 'COU',
            '188' => 'CRC',
            '931' => 'CUC',
            '192' => 'CUP',
            '132' => 'CVE',
            '203' => 'CZK',
            '262' => 'DJF',
            '208' => 'DKK',
            '214' => 'DOP',
            '012' => 'DZD',
            '818' => 'EGP',
            '232' => 'ERN',
            '230' => 'ETB',
            '978' => 'EUR',
            '242' => 'FJD',
            '238' => 'FKP',
            '826' => 'GBP',
            '981' => 'GEL',
            '936' => 'GHS',
            '292' => 'GIP',
            '270' => 'GMD',
            '324' => 'GNF',
            '320' => 'GTQ',
            '328' => 'GYD',
            '344' => 'HKD',
            '340' => 'HNL',
            '191' => 'HRK',
            '332' => 'HTG',
            '348' => 'HUF',
            '360' => 'IDR',
            '376' => 'ILS',
            '356' => 'INR',
            '368' => 'IQD',
            '364' => 'IRR',
            '352' => 'ISK',
            '388' => 'JMD',
            '400' => 'JOD',
            '392' => 'JPY',
            '404' => 'KES',
            '417' => 'KGS',
            '116' => 'KHR',
            '174' => 'KMF',
            '408' => 'KPW',
            '410' => 'KRW',
            '414' => 'KWD',
            '136' => 'KYD',
            '398' => 'KZT',
            '418' => 'LAK',
            '422' => 'LBP',
            '144' => 'LKR',
            '430' => 'LRD',
            '426' => 'LSL',
            '434' => 'LYD',
            '504' => 'MAD',
            '498' => 'MDL',
            '969' => 'MGA',
            '807' => 'MKD',
            '104' => 'MMK',
            '496' => 'MNT',
            '446' => 'MOP',
            '929' => 'MRU',
            '480' => 'MUR',
            '462' => 'MVR',
            '454' => 'MWK',
            '484' => 'MXN',
            '979' => 'MXV',
            '458' => 'MYR',
            '943' => 'MZN',
            '516' => 'NAD',
            '566' => 'NGN',
            '558' => 'NIO',
            '578' => 'NOK',
            '524' => 'NPR',
            '554' => 'NZD',
            '512' => 'OMR',
            '590' => 'PAB',
            '604' => 'PEN',
            '598' => 'PGK',
            '608' => 'PHP',
            '586' => 'PKR',
            '985' => 'PLN',
            '600' => 'PYG',
            '634' => 'QAR',
            '946' => 'RON',
            '941' => 'RSD',
            '643' => 'RUB',
            '646' => 'RWF',
            '682' => 'SAR',
            '090' => 'SBD',
            '690' => 'SCR',
            '938' => 'SDG',
            '752' => 'SEK',
            '702' => 'SGD',
            '654' => 'SHP',
            '694' => 'SLL',
            '706' => 'SOS',
            '968' => 'SRD',
            '728' => 'SSP',
            '930' => 'STN',
            '222' => 'SVC',
            '760' => 'SYP',
            '748' => 'SZL',
            '764' => 'THB',
            '972' => 'TJS',
            '934' => 'TMT',
            '788' => 'TND',
            '776' => 'TOP',
            '949' => 'TRY',
            '780' => 'TTD',
            '901' => 'TWD',
            '834' => 'TZS',
            '980' => 'UAH',
            '800' => 'UGX',
            '840' => 'USD',
            '997' => 'USN',
            '940' => 'UYI',
            '858' => 'UYU',
            '927' => 'UYW',
            '860' => 'UZS',
            '926' => 'VED',
            '928' => 'VES',
            '704' => 'VND',
            '548' => 'VUV',
            '882' => 'WST',
            '950' => 'XAF',
            '961' => 'XAG',
            '959' => 'XAU',
            '955' => 'XBA',
            '956' => 'XBB',
            '957' => 'XBC',
            '958' => 'XBD',
            '951' => 'XCD',
            '960' => 'XDR',
            '952' => 'XOF',
            '964' => 'XPD',
            '953' => 'XPF',
            '962' => 'XPT',
            '994' => 'XSU',
            '963' => 'XTS',
            '965' => 'XUA',
            '999' => 'XXX',
            '886' => 'YER',
            '710' => 'ZAR',
            '967' => 'ZMW',
            '932' => 'ZWL',
        ];
    }

    /**
     * Get the current shop domain
     *
     * @return string
     */
    public static function getDomain()
    {
        $shopDomain = \Tools::getShopDomainSsl();
        if (!empty(self::getMultistoreShopContextId())) {
            $shopDomain = \Context::getContext()->shop->domain_ssl ? \Context::getContext()->shop->domain_ssl : '';
        }
        return $shopDomain;
    }

    /**
     * Get the redirection url for viva wallet smart checkout
     *
     * @param string $controller
     * @param string $environment
     * @param bool $includeBaseURL
     *
     * @return string
     */
    public static function getUrlWithoutLanguage(string $controller, string $environment, bool $includeBaseURL = false)
    {
        $moduleName = \Vivawalletsmartcheckout\Helpers\Config::get('app.module.attributes.name');
        $domain = $includeBaseURL ? \Tools::getShopDomainSsl() : self::getDomain();
        $shop = $controller == 'webhook' ? new \Shop((int) \Configuration::get('PS_SHOP_DEFAULT')) : \Context::getContext()->shop;
        $baseUri = $shop->getBaseURI();
        $baseUrl = 'https://' . $domain . '/';
        $pathUrl = ltrim($baseUri, '/') . "module/$moduleName/$controller?environment=$environment";

        return $includeBaseURL ? $baseUrl . $pathUrl : $pathUrl;
    }

    /**
     * Get context type
     *
     * @param int $contextId
     *
     * @return string
     */
    public static function getContextType(int $contextId): string
    {
        switch ($contextId) {
            case \Shop::CONTEXT_SHOP:
                $context = 'shop';
                break;
            case \Shop::CONTEXT_GROUP:
                $context = 'group';
                break;
            case \Shop::CONTEXT_ALL:
                $context = 'all';
                break;
            default:
                throw new \PrestaShopException('Not in correct context');
        }
        return $context;
    }

    /**
     * If is Multistore and shop context get shop_id
     *
     * @return int|null
     */
    public static function getMultistoreShopContextId()
    {
        $contextType = \Vivawalletsmartcheckout\Helpers\General::getContextType(\Shop::getContext());
        $multistore = \Shop::isFeatureActive();
        return $multistore && $contextType === 'shop' ? \Context::getContext()->shop->id : null;
    }
}
