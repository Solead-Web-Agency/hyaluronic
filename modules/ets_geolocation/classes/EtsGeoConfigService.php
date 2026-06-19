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
 **/

/**
 * Class EtsGeoConfigService
 */
class EtsGeoConfigService
{
    const MSG_KEY_PREFIX = 'ETS_GEO_';
    const MSG_KEY_SURFIX = '_MSG';

    /**
     * @var \EtsGeoConfigService
     */
    private static $_instance;

    /**
     * @var \Context|\ContextCore
     */
    private $_context;

    /**
     * @return \EtsGeoConfigService
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return (bool) Configuration::get('PS_GEOLOCATION_ENABLED');
    }

    /**
     * @return bool
     */
    public function isBotAndIgnored()
    {
        // old:  '/botlink|zoombot|linkdexbot|yeti|zeus|webzip|webleacher|webfindbot|webcopier|url_spider_sql|urlresolver|unwindfetchor|twitterbot|technoratisnoop|sitesucker|search|pingdom|netcraftsurveyagent|naverbot|nationaldirectory|majesticseo|m2e|libwww-perl|inktomi|infoseek|idbot|httrack|firefly|froogle|get|girafabot|grabnet|facebot|fetch|find|facebookexternalhit|ezooms|easouspider|duckduckbot|curl|dataprovider|crawler|contaxe|chinaclaw|blackwidow|applebot|crawl|xing|baidu|yandex|duckduckgo|bing|naver|linkedin|pinterest|twitter|facebook|skype|alexa|sogou|geona|ia_archiver|lycos|scrubby|estyle|acoi|accona|aspseek|altavista|abacho|rambler|openstat.ru\/bot|dotbot|addthis|baiduspider|seznambot|mod_pagespeed|ccbot|mj12bot|fatbot|semrushbot|yandexmobilebot|ahrefsbot|bingbot|ahoy|alkalinebot|anthill|appie|arale|araneo|araybot|ariadne|arks|atn_worldwide|atomz|bbot|bjaaland|ukonline|borg-bot\/0\.9|boxseabot|bspider|calif|christcrawler|cmc\/0\.01|combine|confuzzledbot|coolbot|cosmos|internet cruiser robot|cusco|cyberspyder|cydralspider|desertrealm, desert realm|digger|diibot|grabber|downloadexpress|dragonbot|dwcp|ecollector|ebiness|elfinbot|esculapio|esther|fastcrawler|fdse|felix ide|fido|kit-fireball|fouineur|freecrawl|gammaspider|gazz|gcreep|golem|googlebot|griffon|gromit|gulliver|gulper|hambot|havindex|hotwired|htdig|iajabot|ingrid\/0\.1|informant|infospiders|inspectorwww|irobot|iron33|jbot|jcrawler|teoma|jeeves|jobo|image\.kapsi\.net|kdd-explorer|ko_yappo_robot|label-grabber|larbin|legs|linkidator|linkwalker|lockon|logo_gif_crawler|marvin|mattie|mediafox|merzscope|nec-meshexplorer|mindcrawler|moget|motor|msnbot|muncher|muninn|muscatferret|mwdsearch|sharp-info-agent|webmechanic|netscoop|newscan-online|objectssearch|occam|orbsearch\/1\.0|packrat|pageboy|parasite|patric|pegasus|perlcrawler|phpdig|piltdownman|pimptrain|pjspider|plumtreewebaccessor|portalbspider|psbot|getterrobo-plus|raven|rhcs|rixbot|roadrunner|robbie|robi|robocrawl|robofox|scooter|search-au|searchprocess|senrigan|shagseeker|sift|simbot|site valet|skymob|slcrawler\/2\.0|slurp|esi|snooper|solbot|speedy|spider_monkey|spiderbot\/1\.0|spiderline|nil|suke|http:\/\/www\.sygol\.com|tach_bw|techbot|templeton|titin|topiclink|udmsearch|urlck|valkyrie libwww-perl|verticrawl|victoria|void-bot|voyager|vwbot_k|crawlpaper|wapspider|webbandit\/1\.0|webcatcher|t-h-u-n-d-e-r-s-t-o-n-e|webmoose|webquest|webreaper|webs|webspider|webwalker|wget|winona|whowhere|wlm|wolp|wwwc|none|xget|nederland\.zoek|aisearchbot|woriobot|netseer|nutch|yandexbot|google-inspectiontool|storebot|googleother|apis-google|adsbot|mediapartners-google|feedfetcher-google|googleproducer|google-read-aloud|google-site-verification|schema-markup-validator/iu'
        return isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|spider|crawl|yeti|zeus|webzip|webleacher|webcopier|urlresolver|unwindfetchor|technoratisnoop|sitesucker|search|pingdom|netcraftsurveyagent|nationaldirectory|majesticseo|m2e|libwww-perl|inktomi|infoseek|httrack|firefly|froogle|get|grabnet|fetch|find|facebookexternalhit|ezooms|curl|dataprovider|crawler|contaxe|chinaclaw|blackwidow|xing|baidu|yandex|duckduckgo|bing|naver|linkedin|pinterest|twitter|facebook|skype|alexa|sogou|geona|ia_archiver|lycos|scrubby|estyle|acoi|accona|aspseek|altavista|abacho|rambler|addthis|mod_pagespeed|ahoy|anthill|appie|arale|araneo|ariadne|arks|atn_worldwide|atomz|bjaaland|ukonline|calif|cmc\/0\.01|combine|cosmos|internet cruiser robot|cusco|cyberspyder|desertrealm, desert realm|digger|grabber|downloadexpress|dwcp|ecollector|ebiness|esculapio|esther|fdse|felix ide|fido|kit-fireball|fouineur|gazz|gcreep|golem|griffon|gromit|gulliver|gulper|havindex|hotwired|htdig|informant|inspectorwww|iron33|teoma|jeeves|jobo|image\.kapsi\.net|kdd-explorer|label-grabber|larbin|legs|linkidator|linkwalker|lockon|marvin|mattie|mediafox|merzscope|nec-meshexplorer|moget|motor|muncher|muninn|muscatferret|mwdsearch|sharp-info-agent|webmechanic|netscoop|newscan-online|objectssearch|occam|orbsearch\/1\.0|packrat|pageboy|parasite|patric|pegasus|phpdig|piltdownman|pimptrain|plumtreewebaccessor|getterrobo-plus|raven|rhcs|roadrunner|robbie|robi|robofox|scooter|search-au|searchprocess|senrigan|shagseeker|sift|site valet|skymob|slurp|esi|snooper|speedy|nil|suke|http:\/\/www\.sygol\.com|tach_bw|templeton|titin|topiclink|udmsearch|urlck|valkyrie libwww-perl|victoria|voyager|webbandit\/1\.0|webcatcher|t-h-u-n-d-e-r-s-t-o-n-e|webmoose|webquest|webreaper|webs|webwalker|wget|winona|whowhere|wlm|wolp|wwwc|none|xget|nederland\.zoek|netseer|nutch|google-inspectiontool|googleother|apis-google|mediapartners-google|feedfetcher-google|googleproducer|google-read-aloud|google-site-verification|schema-markup-validator/iu', $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @return bool
     */
    public function isBackoffice()
    {
        return defined('_PS_ADMIN_DIR_');
    }

    /**
     * @return bool
     */
    public function isAjaxRequest()
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        }

        if (isset($_SERVER['HTTP_ACCEPT']) && preg_match('#\bapplication/json\b#', $_SERVER['HTTP_ACCEPT'])) {
            return true;
        }

        return Tools::isSubmit('ajax');
    }

    /**
     * @param string $isoCode
     *
     * @return self
     */
    public function setCookieCountryIso($isoCode)
    {
        $this->getContext()->cookie->iso_code_country = $isoCode;
        $this->getContext()->cookie->write();

        return $this;
    }

    /**
     * @param bool $val
     *
     * @return self
     */
    public function setGeoRedirected($val)
    {
        $this->getContext()->cookie->geoRedirected = (bool) $val;
        $this->getContext()->cookie->write();

        return $this;
    }

    /**
     * @return bool
     */
    public function isGeoRedirected()
    {
        return (bool) $this->getContext()->cookie->geoRedirected;
    }

    /**
     * @param bool $val
     *
     * @return self
     */
    public function setGeoAutoCountryProcessed($val)
    {
        $this->getContext()->cookie->ets_geocountryloaded = (bool) $val;
        $this->getContext()->cookie->write();

        return $this;
    }

    /**
     * @return bool
     */
    public function isGeoAutoCountryProcessed()
    {
        return (bool) $this->getContext()->cookie->ets_geocountryloaded;
    }

    /**
     * @param bool $val
     *
     * @return self
     */
    public function setManualSelectCountry($val)
    {
        $this->getContext()->cookie->ets_geo_manual_select_country = (bool) $val;
        $this->getContext()->cookie->write();

        return $this;
    }

    /**
     * @return bool
     */
    public function isManualSelectCountry()
    {
        return (bool) $this->getContext()->cookie->ets_geo_manual_select_country;
    }

    /**
     * @return bool
     */
    public function isWorkingOnHomePageOnly()
    {
        return (bool) Configuration::get('ETS_GEO_ON_HOME_ONLY');
    }

    /**
     * @return bool
     */
    public function isAutoSetLang()
    {
        return (bool) Configuration::get('ETS_GEO_AUTO_LANG');
    }

    /**
     * @return bool
     */
    public function isAutoSetTax()
    {
        return (bool) Configuration::get('ETS_GEO_AUTO_TAX_SHIPPING');
    }

    /**
     * @return bool
     */
    public function isAutoSetCurrency()
    {
        return (bool) Configuration::get('ETS_GEO_AUTO_CURRENCY');
    }

    /**
     * @return bool
     */
    public function isShowSwitchNav()
    {
        return (bool) Configuration::get('ETS_GEO_ENABLE_SWITCH');
    }

    /**
     * @return bool
     */
    public function isShowConfirmPopup()
    {
        return (bool) Configuration::get('ETS_GEO_HIDE_NOTIFICATION');
    }

    /**
     * @param string $key
     * @param int $langId
     *
     * @return string|false
     */
    public function getMessage($key, $langId)
    {
        if (self::strStartsWith($key, self::MSG_KEY_PREFIX)) {
            return Configuration::get($key, $langId);
        }
        $keysMap = [
            'disabledproduct' => 'DISABLED_PRODUCT',
            'confirm' => 'CONFIRM',
            'language' => 'LANGUAGE',
            'changelanguage' => 'LANGUAGE',
            'currency' => 'CURRENCY',
            'changecurrency' => 'CURRENCY',
            'setting' => 'SETTING',
            'choose' => 'CHOOSE',
            'blog' => 'BLOG',
            'block' => 'BLOG',
        ];
        if (array_key_exists(strtolower($key), $keysMap)) {
            $key = sprintf('%s%s%s', self::MSG_KEY_PREFIX, $keysMap[strtolower($key)], self::MSG_KEY_SURFIX);

            return Configuration::get($key, $langId);
        }

        return false;
    }

    /**
     * @param string $text
     * @param array|\Language $lang
     * @param string $file
     *
     * @return string|null
     */
    public function getTextLang($text, $lang, $file = '')
    {
        $name = 'ets_geolocation';
        if ($lang instanceof \Language) {
            $lang = get_object_vars($lang);
        }
        $modulePath = rtrim(_PS_MODULE_DIR_, '/') . '/' . $name;
        $fileTransDir = $modulePath . '/translations/' . $lang['iso_code'] . '.php';
        if (!@file_exists($fileTransDir)) {
            return null;
        }
        $fileContent = Tools::file_get_contents($fileTransDir);
        $strMd5 = md5($text);
        $keyMd5 = '<{' . $name . '}prestashop>' . ($file ?: $name) . '_' . $strMd5;
        preg_match('/(\$_MODULE\[\'' . preg_quote($keyMd5, '/') . '\'\]\s*=\s*\')(.*)(\';)/', $fileContent, $matches);
        if ($matches && isset($matches[2])) {
            return $matches[2];
        }

        return null;
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function strStartsWith($haystack, $needle)
    {
        return 0 === strncmp($haystack, $needle, \strlen($needle));
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    public static function strEndsWith($haystack, $needle)
    {
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = \strlen($needle);

        return $needleLength <= \strlen($haystack) && 0 === substr_compare($haystack, $needle, -$needleLength);
    }

    /**
     * @return \Context|\ContextCore
     */
    public function getContext()
    {
        if (!$this->_context instanceof \ContextCore) {
            $this->_context = Context::getContext();
        }

        return $this->_context;
    }
}
