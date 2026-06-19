<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

/**
 * @param $object Ets_abandonedcart
 * @return bool
 */
function upgrade_module_4_2_1($object)
{
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_abancart_mail_log` (
          `id_ets_abancart_email_queue` int(11) UNSIGNED NOT NULL,
          `id_shop` int(11) UNSIGNED NOT NULL,
          `id_lang` int(11) UNSIGNED NOT NULL,
          `id_cart` int(11) UNSIGNED DEFAULT NULL,
          `id_customer` int(11) UNSIGNED DEFAULT NULL,
          `id_ets_abancart_reminder` int(11) UNSIGNED NOT NULL,
          `customer_name` varchar(191) NOT NULL,
          `email` varchar(191) NOT NULL,
          `subject` varchar(191) NOT NULL,
          `content` text NOT NULL,
          `sent_time` datetime NOT NULL,
          `status` tinyint(1) UNSIGNED NOT NULL, 
          PRIMARY KEY (`id_ets_abancart_email_queue`),
          KEY `id_shop` (`id_shop`),
          KEY `id_lang` (`id_lang`),
          KEY `id_cart` (`id_cart`),
          KEY `id_customer` (`id_customer`),
          KEY `id_ets_abancart_reminder` (`id_ets_abancart_reminder`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
    ');

    $id_parent = Tab::getIdFromClassName(Ets_abandonedcart::$slugTab . 'MailConfigs');
    if ($id_parent !== false) {
        $object->_addTab(['id_parent' => $id_parent, 'origin' => 'Mail log', 'class' => 'MailLog']);
    }

    Configuration::updateGlobalValue('ETS_ABANCART_CRONJOB_MAIL_LOG', 1);

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` ADD `delay_popup_based_on` TINYINT(1) NOT NULL DEFAULT 0 AFTER `redisplay`;');

    return true;
}