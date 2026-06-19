<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_1($module)
{
    /* Uninstall Tabs */
    $tab = new Tab((int) Tab::getIdFromClassName('AdminZendesk'));
    $tab->delete();

    $sql = 'SELECT id_parent FROM ' . _DB_PREFIX_ . "tab
			WHERE class_name = 'AdminCustomers'";
    $id_parent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    if ($id_parent <= 0) {
        return false;
    }

    /* Install Tabs */
    $tab = new Tab();
    foreach (Language::getLanguages(true) as $language) {
        $tab->name[(int) $language['id_lang']] = 'Zendesk';
    }
    $tab->class_name = 'AdminZendesk';
    $tab->id_parent = (int) $id_parent;
    $tab->module = 'zendesk';
    $tab->add();

    return true;
}
