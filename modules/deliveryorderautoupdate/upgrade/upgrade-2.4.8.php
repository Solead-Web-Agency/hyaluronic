<?php
/**
* 2007-2023 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 2.1.0,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_2_4_8()
{
    $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_return` (
    `id_return` int(11) NOT NULL AUTO_INCREMENT,
    `id_order` int(11) NOT NULL,
    `id_order_return` int(11) NULL,
    `id_connector` int(11) NULL,
    `shipping_number` varchar(64) CHARACTER SET utf8 NOT NULL,
    `shipping_status` int(11) NULL,
    `date_add` datetime NOT NULL,
	`status_date` datetime NOT NULL,
	PRIMARY KEY (`id_return`),
	KEY `id_order` (`id_order`),
	KEY `id_order_return` (`id_order_return`),
    KEY `id_connector` (`id_connector`),
    UNIQUE `shipping_number` (`shipping_number`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }
    return true;
}
