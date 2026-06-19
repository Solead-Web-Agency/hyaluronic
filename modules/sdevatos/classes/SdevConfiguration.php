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
use Tools;
use Configuration;
use ReflectionClass;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevConfiguration
{
    const PARAM_DEBUG_MODE = 'debug_mode';
    const PARAM_IP_FILTERING = 'ip_filtering';

    const PARAM_REDIRECTION = 'redirection';
    const REDIRECTION_SHOP = 'shop';
    const REDIRECTION_ATOS = 'atos';

    const PARAM_PAYMENT_ERRORS = 'payment_errors';
    const PAYMENT_ERRORS_SAVE = 'save';
    const PAYMENT_ERRORS_NOTHING = 'nothing';

    const PARAM_BINARY_PATH = 'binary_path';
    const PARAM_PATHFILE_PATH = 'pathfile_path';

    const TOKEN = 'token';

    /**
     * Get the default module parameter list.
     *
     * @param bool $key_list_only - Get only the key list.
     * @return array
     */
    public static function getDefaultParameterList($key_list_only = false)
    {
        $parameter_list = array(
            self::PARAM_DEBUG_MODE => '0',
            self::PARAM_IP_FILTERING => '',
            self::PARAM_REDIRECTION => self::REDIRECTION_SHOP,
            self::PARAM_PAYMENT_ERRORS => self::PAYMENT_ERRORS_SAVE,
            self::PARAM_BINARY_PATH => SdevModule::DIR.'bin/',
            self::PARAM_PATHFILE_PATH => SdevModule::DIR.'param/',
            self::TOKEN => Tools::hash(uniqid())
        );
        return (bool)$key_list_only
            ? array_keys((array)$parameter_list)
            : (array)$parameter_list;
    }

    /**
     * Get a single configuration value (in one language only).
     *
     * @param string $key - Key wanted.
     * @param int $id_lang - Language ID.
     * @param int $id_shop_group - Shop group ID.
     * @param int $id_shop - Shop ID.
     * @return string - Value.
     */
    public static function get($key, $id_lang = null, $id_shop_group = null, $id_shop = null)
    {
        return Configuration::get(SdevModule::UNAME.'_'.Tools::strtoupper($key), $id_lang, $id_shop_group, $id_shop);
    }

    /**
     * Update configuration key and value into database (automatically insert if key does not exist).
     *
     * Values are inserted/updated directly using SQL, because using (Configuration) ObjectModel
     * may not insert values correctly (for example, HTML is escaped, when it should not be).
     *
     * @param string $key - Key.
     * @param mixed $values - $values is an array if the configuration is multilingual, a single string else.
     * @param bool $html - Specify if html is authorized in value.
     * @param int $id_shop_group - Shop group ID.
     * @param int $id_shop - Shop ID..
     * @return bool - Update result.
     */
    public static function updateValue($key, $values, $html = false, $id_shop_group = null, $id_shop = null)
    {
        return (bool)Configuration::updateValue(SdevModule::UNAME.'_'.Tools::strtoupper($key), $values, $html, $id_shop_group, $id_shop);
    }

    /**
     * Delete a configuration key in database (with or without language management).
     *
     * @param string $key - Key to delete.
     * @return bool - Deletion result
     */
    public static function deleteByName($key)
    {
        return (bool)Configuration::deleteByName(SdevModule::UNAME.'_'.Tools::strtoupper($key));
    }

    /**
     * Get the redirection allowed list.
     *
     * @return array
     */
    public static function getRedirectionAllowedList()
    {
        $field_list = array();
        $ReflectionClass = new ReflectionClass(get_class(self));
        foreach ($ReflectionClass->getConstants() as $const => $value) {
            if (strpos($const, 'REDIRECTION_') !== false) {
                $field_list[$const] = $value;
            }
        }
        return (array)$field_list;
    }

    /**
     * Get the payment errors allowed list.
     *
     * @return array
     */
    public static function getPaymentErrorsAllowedList()
    {
        $field_list = array();
        $ReflectionClass = new ReflectionClass(get_class(self));
        foreach ($ReflectionClass->getConstants() as $const => $value) {
            if (strpos($const, 'PAYMENT_ERRORS_') !== false) {
                $field_list[$const] = $value;
            }
        }
        return (array)$field_list;
    }
}
