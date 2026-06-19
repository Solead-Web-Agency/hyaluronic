<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

namespace ScaleDEV\SdevAtos;

use ScaleDEV\SdevAtos\SdevModule;
use Configuration;
use Context;
use Exception;
use Language;
use Shop;
use Tools;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevTools
{
    /**
     * Get language ID.
     *
     * @return int
     */
    public static function getIdLang()
    {
        return (int)Context::getContext()->language->id;
    }

    /**
     * Get the language ISO code.
     *
     * @return string
     */
    public static function getIsoLang()
    {
        return Context::getContext()->language->iso_code;
    }

    /**
     * Get shop group ID.
     *
     * @return null|int
     */
    public static function getIdShopGroup()
    {
        return Shop::getContextShopGroupID();
    }

    /**
     * Get the shop ID.
     *
     * @return int
     */
    public static function getIdShop()
    {
        return (int)Context::getContext()->shop->id;
    }

    /**
     * Get the shop name.
     *
     * @param bool $url - Define if the shop name will be used into an URL.
     * @return string
     */
    public static function getShopName($url = false)
    {
        try {
            if (!is_bool($url)) {
                throw new Exception('The parameter $url must be a boolean, '.gettype($url).' given !');
            }
            return (bool)$url
                ? Tools::str2url(Context::getContext()->shop->name)
                : Context::getContext()->shop->name;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get the shop domain.
     *
     * @param bool $with_protocol - Get the shop domain with the protocol or not.
     * @param bool $with_base_uri - Get the shop domain with the base URI or not.
     * @return string
     */
    public static function getShopDomain($with_protocol = true, $with_base_uri = false)
    {
        try {
            if (!is_bool($with_protocol)) {
                throw new Exception('The parameter $with_protocol must be a boolean, '.gettype($with_protocol).' given !');
            }
            if (!is_bool($with_base_uri)) {
                throw new Exception('The parameter $with_base_uri must be a boolean, '.gettype($with_base_uri).' given !');
            }
            return (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')
                ? Tools::getShopDomainSsl((bool)$with_protocol)
                : Tools::getShopDomain((bool)$with_protocol)
            ).((bool)$with_base_uri ? __PS_BASE_URI__ : null);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Define if Windows used.
     *
     * @return bool
     */
    public static function isWindowsUsed()
    {
        return Tools::strtoupper(Tools::substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Define if Angular is already loaded.
     *
     * @param object $controller - Controller used.
     * @return bool
     * @throws Exception
     */
    public static function isAngularLoaded($controller)
    {
        try {
            if (!is_object($controller)) {
                throw new Exception('The controller must be an object, '.gettype($controller).' given !');
            }
            if (isset($controller->js_files) && is_array($controller->js_files)) {
                foreach ($controller->js_files as $js_file) {
                    if (strpos($js_file, 'angular.min.js')) {
                        return true;
                    }
                }
            }
            return false;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get if has an IP filter.
     *
     * @return bool
     */
    public static function hasIpFilter()
    {
        return SdevModule::IP_FILTER
            && ((isset($_SERVER['HTTP_X_FORWARDED_FOR'])
                && getenv('HTTP_X_FORWARDED_FOR') == SdevModule::IP_FILTER)
            || getenv('REMOTE_ADDR') == SdevModule::IP_FILTER);
    }

    /**
     * Debug for a client.
     *
     * @param mixed $var - Variable to debug.
     * @param bool $die - Die after the debug or not.
     */
    public static function debug($var, $die = true)
    {
        if ((bool)self::hasIpFilter()) {
            $function = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? 'dump' : 'ppp';
            $function($var);
            if ((bool)$die) {
                die();
            }
        }
    }

    /**
     * Debug values into the logs file.
     */
    public static function addlogs($values)
    {
        $logs_dir = SdevModule::DIR.'logs/';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir);
        }

        if (!is_array($values)) {
            $values = is_object($values)
                ? self::convertObjectToArray($values)
                : array($values);
        }

        file_put_contents($logs_dir.'debug.txt', print_r($values, true), FILE_APPEND);
    }

    /**
     * Decode a request.
     *
     * @param bool $array - Set true if you want an array.
     * @return object|array
     * @throws Exception
     */
    public static function decodeRequest($array = true)
    {
        try {
            if (!is_bool($array)) {
                throw new Exception('The parameter $array must be a boolean, '.gettype($array).' given !');
            }
            return Tools::jsonDecode(Tools::file_get_contents('php://input'), $array);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Send the Ajax result.
     *
     * @param array|string $data - Data to send.
     */
    public static function sendAjaxResult($data)
    {
        if (!is_array($data)) {
            $data = array('sdev_ajax_result' => $data);
        }
        die(Tools::jsonEncode((array)$data));
    }

    /**
     * Convert an object to an array.
     *
     * @param object $object
     * @return array
     * @throws Exception
     */
    public static function convertObjectToArray($object)
    {
        try {
            if (!is_object($object)) {
                throw new Exception('The variable sent must be an object, '.gettype($object).' given !');
            }
            return Tools::jsonDecode(Tools::jsonEncode($object), true);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Implode an associative array.
     *
     * @param string $glue_key - The key delimiter.
     * @param string $glue_value - The value delimiter.
     * @param array $pieces - Array to implode.
     * @return string
     * @throws Exception
     */
    public static function implodeAssociativeArray($glue_key, $glue_value, $pieces)
    {
        try {
            if (!is_string($glue_key)) {
                throw new Exception('The parameter $glue_key must be a string, '.gettype($glue_key).' given !');
            }
            if (!is_string($glue_value)) {
                throw new Exception('The parameter $glue_value must be a string, '.gettype($glue_value).' given !');
            }
            if (!is_array($pieces)) {
                throw new Exception('The parameter $pieces must be an array, '.gettype($pieces).' given !');
            }
            if (!empty($pieces)) {
                $string = array();
                foreach ($pieces as $key => $value) {
                    $string[] = $key.$glue_key.$value;
                }
                return implode($glue_value, $string);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get the ISO 31661-1 alpha-3 code by the language ID.
     *
     * @param string $id_lang - Language ID.
     * @return string
     * @throws Exception
     */
    public static function getIsoAlpha3ByIdLang($id_lang)
    {
        try {
            if (!is_numeric($id_lang)) {
                throw new Exception('The iso_lang must be a integer, '.gettype($id_lang).' given !');
            }

            $iso_alpha_3 = false;
            $iso_lang = Tools::strtoupper(Language::getIsoById($id_lang));

            switch ($iso_lang) {
                case 'BE':
                    $iso_alpha_3 = 'BEL';
                    break;

                case 'DE':
                    $iso_alpha_3 = 'DEU';
                    break;

                case 'DK':
                    $iso_alpha_3 = 'DNK';
                    break;

                case 'ES':
                    $iso_alpha_3 = 'ESP';
                    break;

                case 'FR':
                    $iso_alpha_3 = 'FRA';
                    break;

                case 'GB':
                    $iso_alpha_3 = 'GBR';
                    break;

                case 'IT':
                    $iso_alpha_3 = 'ITA';
                    break;

                case 'NL':
                    $iso_alpha_3 = 'NLD';
                    break;

                case 'PL':
                    $iso_alpha_3 = 'POL';
                    break;

                case 'PT':
                    $iso_alpha_3 = 'PRT';
                    break;

                case 'RO':
                    $iso_alpha_3 = 'ROU';
                    break;

                case 'RU':
                    $iso_alpha_3 = 'RUS';
                    break;

                case 'UA':
                    $iso_alpha_3 = 'UKR';
                    break;

                case 'US':
                    $iso_alpha_3 = 'USA';
                    break;
            }

            unset($id_lang, $iso_lang);
            return $iso_alpha_3;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Gets the current IP address.
     *
     * @return string
     * @since 1.1.0
     */
    public static function getIpAddress()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)
        ) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])
            && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])
        ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])
            && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])
        ) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])
            && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])
        ) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }

        return $ip;
    }
}
