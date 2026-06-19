<?php
/**
 * A Route Prestashop Extension that adds secure shipping
 * protection to your orders
 *
 * Php version 7.0^
 *
 * @author    Route Development Team <dev@routeapp.io>
 * @copyright 2019 Route App Inc. Copyright (c) https://www.routeapp.io/
 * @license   https://www.routeapp.io/merchant-terms-of-use  Proprietary License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// Process Module upgrade to 1.0.2
function upgrade_module_1_0_2($module)
{
    //make order field unique on our custom table
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'routeapp_fee`
            ADD UNIQUE (`id_order`);';
    Db::getInstance()->execute($sql);

    //create field to control orders sent to Route API
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'routeapp_fee`
            ADD `processed` TINYINT(1) DEFAULT 0;';
    Db::getInstance()->execute($sql);

    //mark existing orders as already sent
    $sql = 'UPDATE `' . _DB_PREFIX_ . 'routeapp_fee` SET `processed`=1';
    Db::getInstance()->execute($sql);

    //create shipments table
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'routeapp_shipment`( 
            `id_routeapp_shipment` INT(11) NOT NULL AUTO_INCREMENT ,
            `id_order` INT(11) NOT NULL UNIQUE, 
            `id_order_carrier` INT(11) NOT NULL, 
            `tracking_number` VARCHAR(50) NULL,
            `processed` TINYINT(1) DEFAULT 0,
        PRIMARY KEY (`id_routeapp_shipment`),
        INDEX `ID_ORDER_IND` (`id_order`));';
    Db::getInstance()->execute($sql);

    return true;
}
