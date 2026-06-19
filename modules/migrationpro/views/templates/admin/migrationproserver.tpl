<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2023 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class Migrationproserver extends Module
{

    public function __construct()
    {
        $this->name = 'migrationproserver';
        $this->tab = 'administration';
        $this->version = '4.0.0';
        $this->author = 'MigrationPro';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */

        parent::__construct();

        $this->displayName = $this->l('MigrationPro: Bridge connector');
        $this->description = $this->l('Server module for Client MigrationPro module');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (Module::isInstalled('migrationpro')) {
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
                $this->_errors[] = Context::getContext()->getTranslator()->trans('Prestashop Upgrade and Migrate tool and Bridge tool can not be in the same shop. Please, read documentation and check video guide!', array(), 'Admin.Modules.Notification');
            } else {
                $this->_errors[] = Tools::displayError('Prestashop Upgrade and Migrate tool and Bridge tool can not be in the same shop. Please, read documentation and check video guide!');
            }
            return false;
        }

	if (!empty(Configuration::get('PS_MAINTENANCE_IP'))) {
            Configuration::updateValue('PS_MAINTENANCE_IP', Configuration::get('PS_MAINTENANCE_IP') . ', [[[[[[ip_target]]]]]]]');
        } else {
            Configuration::updateValue('PS_MAINTENANCE_IP', '[[[[[[ip_target]]]]]]]');
        }

        

        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

}
