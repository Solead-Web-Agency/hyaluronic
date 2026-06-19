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
function upgrade_module_2_1_0()
{
    Configuration::updateValue("DELIVERY_EMAIL_SUBJECT", '');
    Configuration::updateValue("HL_TRACKING_CRON_RESPONSE", 0);
    Configuration::updateValue("HL_ORDER_TRACKING_BLOCK", 1);
    Configuration::updateValue("HL_CUSTOMER_SHIPPING_STEP", 1);
    Configuration::updateValue("DELIVERY_EVENT_CODE_MAIL_ADMIN_EMAILS", '');
    Configuration::updateValue("DELIVERY_EVENT_CODE_MAIL_ADMIN", '');
    Configuration::updateValue("DELIVERY_ORDER_STATUS_TO", '{"0":"-1","1":"-1","2":"-1","3":"-1","4":"-1","5":"-1",'.
    '"6":"-1","7":"-1","8":"-1","9":"-1","100":"-1","101":"-1","102":"-1","103":"-1","104":"-1"'.
    ',"105":"-1","500":"-1"}');
    $module = Module::getInstanceByName('deliveryorderautoupdate');
    $module->registerHook('displayOrderDetail');
    $sql = array();
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_email` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `id_order` int(11) NOT NULL,
				  `id_tracking_history` int(11) DEFAULT NULL,
				  `shipping_status` tinyint(1) NOT NULL,
			      `date_sent` datetime NOT NULL,
				  `email_status` tinyint(1) NOT NULL,
                  PRIMARY KEY (`id`),
				  KEY `id_order` (`id_order`),
				  KEY `id_tracking_history` (`id_tracking_history`)
                ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';
        $sql[] = 'INSERT IGNORE INTO `'._DB_PREFIX_.'configuration_lang`(`id_configuration`, `id_lang`,
        `value`, `date_upd`)
			(SELECT c.id_configuration, l.id_lang,
			CASE WHEN l.iso_code LIKE "en" THEN "Delivery tracking of your order"
			WHEN l.iso_code LIKE "fr" THEN "Suivi de livraison de votre commande"
			WHEN l.iso_code LIKE "es" THEN "Seguimiento de la entrega de su pedido"
			WHEN l.iso_code LIKE "it" THEN "Tracciamento della consegna del tuo ordine"
			WHEN l.iso_code LIKE "de" THEN "Sendungsverfolgung Ihrer Bestellung"
			WHEN l.iso_code LIKE "nl" THEN "Levering volgen van uw bestelling"
			ELSE "Delivery tracking of your order"
			END,CURRENT_TIMESTAMP FROM '._DB_PREFIX_.'lang l
			LEFT JOIN '._DB_PREFIX_.'configuration c ON c.name LIKE "DELIVERY_EMAIL_SUBJECT")';

    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }
    return true;
}
