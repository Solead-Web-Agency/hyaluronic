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
function upgrade_module_2_4_9()
{
    $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_issue` (
  `id_issue` int(11) NOT NULL AUTO_INCREMENT,
  `id_order_carrier` int(11) NOT NULL,
  `issue_type` int(11) NOT NULL,
	PRIMARY KEY (`id_issue`),
	KEY `id_order_carrier` (`id_order_carrier`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_issue_status` (
   `id_tracking_issue_status` int(11) NOT NULL AUTO_INCREMENT,
   `id_issue` int(11) NOT NULL,
   `status` int(11) NOT NULL,
   `detail` varchar(128) NULL,
   `date` datetime NOT NULL,
  	PRIMARY KEY (`id_tracking_issue_status`),
	KEY `id_issue` (`id_issue`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }
    return true;
}
