<?php
/**
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

/**
 * TABLE : cart_abandonment
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment` (
    `id_cart_abandonment` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `cart_target_active` tinyint(1) unsigned NOT NULL,
    `cart_target_inactive` tinyint(1) unsigned NOT NULL,
    `cart_target_no_orders` tinyint(1) unsigned NOT NULL,
    `cart_target_newsletter` tinyint(1) NOT NULL,
    `cart_frequency_number` tinyint unsigned NOT NULL,
    `cart_frequency_type` varchar(5) NOT NULL,
    `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
    `deleted` tinyint(1) unsigned NOT NULL,
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_cart_abandonment`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

/**
 * TABLE : cart_abandonment_discount
 * 
 * discount_value_type :
 *      1 - amount
 *      2 - percentage
 *      3 - freeshipping
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment_discount` (
    `id_discount` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_cart_abandonment` int(10) unsigned NOT NULL,
    `discount_value` varchar(10) NOT NULL DEFAULT "0",
    `discount_from` varchar(10) NOT NULL DEFAULT "0",
    `discount_to` varchar(10) NOT NULL DEFAULT "0",
    `discount_value_type` varchar(12) NOT NULL,
    `discount_ttc` tinyint(1) unsigned NOT NULL DEFAULT "0",
    `discount_cumulate` tinyint(1) unsigned NOT NULL DEFAULT "0",
    `discount_validity` tinyint unsigned NOT NULL,
    `deleted` tinyint(1) unsigned NOT NULL,
    PRIMARY KEY (`id_discount`, `id_cart_abandonment`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

/**
 * TABLE : cart_abandonment_template
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment_template` (
    `id_template` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_cart_abandonment` int(10) unsigned NOT NULL,
    `model_name` varchar(50) NOT NULL,
    `primary_color` varchar(7) NOT NULL,
    `secondary_color` varchar(7) NOT NULL,
    `deleted` tinyint(1) unsigned NOT NULL,
    PRIMARY KEY (`id_template`, `id_cart_abandonment`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

/**
 * TABLE : cart_abandonment_template_lang
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment_template_lang` (
    `id_template` int(10) unsigned NOT NULL,
    `id_lang` int(10) unsigned NOT NULL,
    `lang_iso` varchar(3) NOT NULL,
    `email_subject` varchar(255) NOT NULL,
    `email_content` text(2500),
    `email_discount` text(2500),
    `email_link_facebook` varchar(255) NOT NULL,
    `email_link_twitter` varchar(255) NOT NULL,
    `email_link_instagram` varchar(255) NOT NULL,
    `email_reassurance_text1` varchar(100) NOT NULL,
    `email_reassurance_text2` varchar(100) NOT NULL,
    `email_reassurance_text3` varchar(100) NOT NULL,
    `email_reassurance_img1` varchar(150) NOT NULL,
    `email_reassurance_img2` varchar(150) NOT NULL,
    `email_reassurance_img3` varchar(150) NOT NULL,
    `email_cta` varchar(25) NOT NULL,
    `email_unsubscribe` text(2500),
    `email_unsubscribe_text` varchar(100) NOT NULL,
    `deleted` tinyint(1) unsigned NOT NULL,
    PRIMARY KEY (`id_template`, `id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

/**
 * TABLE : cart_abandonment_customer_send
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment_customer_send` (
    `id_send` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `id_customer` int(11) unsigned NOT NULL,
    `id_cart_abandonment` int(10) unsigned NOT NULL,
    `id_cart` int(11) unsigned NOT NULL,
    `send_date` date NOT NULL,
    `visualize` tinyint(1) unsigned NOT NULL,
    `click` tinyint(1) unsigned NOT NULL,
    `click_cart` tinyint(1) unsigned NOT NULL,
    `click_product` tinyint(1) unsigned NOT NULL,
    PRIMARY KEY (`id_send`, `id_customer`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

/**
 * TABLE : cart_abandonment_customer_unsubscribe
 */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'cart_abandonment_customer_unsubscribe` (
    `id_customer` int(11) unsigned NOT NULL,
    `date` date NOT NULL,
    `id_shop` int(11) unsigned NOT NULL,
    PRIMARY KEY (`id_customer`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
