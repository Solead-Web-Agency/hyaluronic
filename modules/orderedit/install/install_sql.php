<?php
/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2021 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.8
 * @link      https://www.silbersaiten.de
 */

$sql = array(
    'orderedit_original_orders' => '
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'orderedit_history` (
                `id_original`              int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_order`                 int(10) unsigned NOT NULL,
                `reference`                varchar(32) null,
                `id_address_delivery`      int unsigned not null,
                `id_address_invoice`       int unsigned not null,
                `current_state`            int unsigned not null,
                `module`                   varchar(255) null,                        
                `total_paid`               decimal(20, 6) default 0.000000 not null,
                `total_discounts`          decimal(20, 6) default 0.000000 not null,
                `total_shipping`           decimal(20, 6) default 0.000000 not null,
                `total_wrapping`           decimal(20, 6) default 0.000000 not null,
                `total_products_wt`        decimal(20, 6) default 0.000000 not null,
                `invoice_number`           int unsigned default \'0\' not null,
                `delivery_number`          int unsigned default \'0\' not null,
                `invoice_date`             datetime not null,
                `delivery_date`            datetime not null,
                `valid`                    int unsigned default \'0\' not null,
                `date_add_origin`          datetime not null,
                `date_add`                 datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`id_original`),
              KEY `original_order` (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;'
);

foreach ($sql as $table) {
    if (!Db::getInstance()->execute($table)) {
        return false;
    }
}
