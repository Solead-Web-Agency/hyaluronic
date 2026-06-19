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

use ScaleDEV\SdevAtos\SdevConfiguration;
use Configuration;
use Context;
use Module;
use Tools;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevModule
{
    const IP_FILTER = false;
    const NAME = 'SdevAtos';
    const LNAME = 'sdevatos';
    const UNAME = 'SDEVATOS';
    const VERSION = '1.2.1';
    const AUTHOR = 'ScaleDEV';
    const DIR = _PS_MODULE_DIR_.self::LNAME.'/';
    const TEMPLATE_PATH = 'views/templates/';
    const HOOK_PATH = self::TEMPLATE_PATH.'hooks/';
    const DB_PREFIX = _DB_PREFIX_.self::LNAME.'_';

    public static $hook_list = array(
        'actionAdminControllerSetMedia',
        'header',
        'displayHeader',
        'displayPayment',
        'paymentOptions',
        'paymentReturn'
    );

    /**
     * Install the default module configuration.
     *
     * @return bool
     */
    public static function install()
    {
        $is_success = true;

        // Configuration.
        foreach ((array)SdevConfiguration::getDefaultParameterList() as $key => $value) {
            if (!(bool)SdevConfiguration::updateValue(Tools::strtoupper($key), $value)) {
                $is_success = false;
            }
        }

        // Hooks.
        $Module = Module::getInstanceByName(self::LNAME);
        if (!empty((array)self::$hook_list)) {
            foreach (self::$hook_list as $hook) {
                if (!(bool)$Module->registerHook($hook)) {
                    $is_success = false;
                }
            }
        }
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=') && !(bool)$Module->registerHook('paymentOptions')) {
            $is_success = false;
        }
        unset($Module);

        // SQL.
        foreach (self::getModelList() as $model) {
            if (method_exists($model, 'installSQL') && !(bool)$model::installSQL()) {
                $is_success = false;
            }
        }

        return (bool)$is_success;
    }

    /**
     * Uninstall the module configuration.
     *
     * @return bool
     */
    public static function uninstall()
    {
        $is_success = true;

        // Configuration.
        foreach ((array)SdevConfiguration::getDefaultParameterList(true) as $key) {
            if (!(bool)SdevConfiguration::deleteByName($key)) {
                $is_success = false;
            }
        }

        // Hooks.
        $Module = Module::getInstanceByName(SdevModule::LNAME);
        if (!empty((array)self::$hook_list)) {
            foreach (self::$hook_list as $hook) {
                if (!(bool)$Module->unregisterHook($hook)) {
                    $is_success = false;
                }
            }
        }
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=') && !(bool)$Module->unregisterHook('paymentOptions')) {
            $is_success = false;
        }
        unset($Module);

        // SQL.
        foreach (self::getModelList() as $model) {
            if (method_exists($model, 'uninstallSQL') && !(bool)$model::uninstallSQL()) {
                $is_success = false;
            }
        }

        return (bool)$is_success;
    }

    /**
     * Reset the module configuration.
     *
     * @return bool
     */
    public static function reset()
    {
        if ((bool)self::uninstall()) {
            return (bool)self::install();
        }
        return false;
    }

    /**
     * Get model list.
     *
     * @return array
     */
    public static function getModelList()
    {
        $model_list = array();
        foreach (scandir(self::DIR.'models') as $file) {
            if (preg_match('[('.self::NAME.')([A-Z]{1}[a-z]*)*(\.php)]', $file) != false) {
                $model_list[] = str_replace('.php', '', $file);
            }
        }
        return (array)$model_list;
    }

    /**
     * Get the documentation list.
     *
     * @return array
     */
    public static function getDocList()
    {
        $doc_list = array();
        foreach (scandir(self::DIR) as $file) {
            if (preg_match('[(readme_)([a-z]{2})(\.pdf)]', $file) != false) {
                $doc_list[str_replace(array('readme_', '.pdf'), '', $file)] = $file;
            }
        }
        return (array)$doc_list;
    }

    /**
     * Assign vars to Smarty.
     *
     * @param array $vars - Vars to assign to Smarty.
     * @throws Exception
     */
    public static function smartyAssign($vars = array())
    {
        try {
            if (!is_array($vars)) {
                throw new Exception('The var $vars must be an array, '.gettype($vars).' given !');
            }
            $vars = array_merge($vars, array(
                'ps15' => Tools::version_compare(_PS_VERSION_, '1.6'),
                'ps17' => Tools::version_compare(_PS_VERSION_, '1.7', '>='),
                'is_symfony_page' => false,
                'module_token' => Configuration::get(self::UNAME.'_TOKEN'),
                'module_name' => self::LNAME,
                'module_version' => self::VERSION,
                'module_path' => self::DIR
            ));
            Context::getContext()->smarty->assign((array)$vars);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
