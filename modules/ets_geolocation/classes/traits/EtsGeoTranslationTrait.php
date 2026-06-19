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
trait EtsGeoTranslationTrait
{
    /**
     * Default value using when object does not have property "name"
     *
     * @var string
     */
    private $defaultModuleName = 'ets_geolocation';

    /**
     * Get translation for a given module text.
     *
     * Note: $specific parameter is mandatory for library files.
     * Otherwise, translation key will not match for Module library
     * when module is loaded with eval() Module::getModulesOnDisk()
     *
     * @param string $string String to translate
     * @param bool|string $specific filename to use in translation key
     * @param string|null $locale Locale to translate to
     *
     * @return string Translation
     */
    public function l($string, $specific = false, $locale = null)
    {
        if (isset(static::$_generate_config_xml_mode) && static::$_generate_config_xml_mode) {
            return $string;
        }
        $name = isset($this->name) ? $this->name : $this->defaultModuleName;
        if ($specific && self::strEndsWith($specific, '.php')) {
            $specific = str_replace('.php', '', basename($specific));
        }

        return Translate::getModuleTranslation(
            $name,
            $string,
            ($specific) ? $specific : $name,
            null,
            false,
            $locale
        );
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private static function strEndsWith($haystack, $needle)
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
}
