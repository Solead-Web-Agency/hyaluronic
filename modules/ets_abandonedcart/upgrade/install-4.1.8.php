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
function upgrade_module_4_1_8($object)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD COLUMN `last_login_time` datetime DEFAULT NULL AFTER `customer_date_add`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD COLUMN `newsletter_date_add` datetime DEFAULT NULL AFTER `last_login_time`;');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD COLUMN `last_date_order` datetime DEFAULT NULL AFTER `newsletter_date_add`;');

    // afterLogin
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic 
        INNER JOIN  `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ic.id_ets_abancart_campaign = ac.id_ets_abancart_campaign)
        JOIN (
            SELECT MAX(cn.date_add) `last_login_time`, g.id_customer, cn.id_shop
            FROM `' . _DB_PREFIX_ . 'guest` g
            LEFT JOIN  `' . _DB_PREFIX_ . 'connections` cn ON (cn.id_guest  = g.id_guest)
            WHERE g.id_customer is NOT NULL AND g.id_customer > 0
            GROUP BY g.id_customer, cn.id_shop
        ) tmp ON (tmp.id_customer = ic.id_customer AND tmp.id_shop = ic.id_shop)
        SET 
            ic.last_login_time = tmp.last_login_time
        WHERE ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN . '
    ');

    // afterSubscription has visitor
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic
        INNER JOIN  `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ic.id_ets_abancart_campaign = ac.id_ets_abancart_campaign)
        INNER JOIN  `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = ic.id_customer)
        SET 
            ic.newsletter_date_add = c.newsletter_date_add
        WHERE c.newsletter = 1 AND ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER . '
    ');

    // afterSubscription has customer
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic
        INNER JOIN  `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ic.id_ets_abancart_campaign = ac.id_ets_abancart_campaign)
        INNER JOIN  `' . _DB_PREFIX_ . ($object->is17 ? 'emailsubscription' : 'newsletter') . '` e ON (e.email = ic.email)
        SET 
            ic.newsletter_date_add = e.newsletter_date_add
        WHERE ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER . '
    ');

    // afterOrderCreate
    Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic
        INNER JOIN  `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ic.id_ets_abancart_campaign = ac.id_ets_abancart_campaign)
        JOIN (
            SELECT MAX(o.date_add) `date_add`, o.id_customer, o.id_shop
            FROM `' . _DB_PREFIX_ . 'orders` o
            GROUP BY o.id_customer, o.id_shop
        ) order_tmp ON (order_tmp.id_customer = ic.id_customer AND order_tmp.id_shop = ic.id_shop)
        INNER JOIN  `' . _DB_PREFIX_ . 'orders` o ON (o.id_customer = ic.id_customer)
        SET 
            ic.last_date_order=order_tmp.date_add
        WHERE ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION . '
    ');

    Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_mailed_cart`');
    Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_mailed_customer`');
    Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_abancart_cart`');

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` DROP COLUMN `send_email_now`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_campaign` ADD COLUMN `deleted` tinyint(1) DEFAULT 0 AFTER `enabled`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` ADD COLUMN `deleted` tinyint(1) DEFAULT 0 AFTER `enabled`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_tracking` ADD COLUMN `deleted` tinyint(1) DEFAULT 0 AFTER `read`');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_reminder` CHANGE `schedule_time` `schedule_time` DATETIME NULL DEFAULT NULL');

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` DROP PRIMARY KEY');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD `id_ets_abancart_index_customer` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id_ets_abancart_index_customer`);');

    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD INDEX `id_customer` (`id_customer`)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD INDEX `id_ets_abancart_reminder` (`id_ets_abancart_reminder`)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD INDEX `id_ets_abancart_campaign` (`id_ets_abancart_campaign`)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ADD INDEX `email` (`email`)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` CHANGE `date_upd` `date_upd` DATETIME NULL DEFAULT NULL');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` CHANGE `customer_date_add` `customer_date_add` DATETIME NULL DEFAULT NULL;');

    Configuration::deleteByName('ETS_ABANCART_AUTO_CLEAR_ANY_DISCOUNT');

    $id_parent = Tab::getIdFromClassName(Ets_abandonedcart::$slugTab . 'MailConfigs');
    if ($id_parent !== false) {
        $object->_addTab([
            'id_parent' => $id_parent,
            'origin' => 'Indexed customers',
            'class' => 'IndexedCustomers',
        ]);
    }

    return true;
}