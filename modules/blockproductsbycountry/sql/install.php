<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bpbc_products` (
    `id_country` int(11) NOT NULL,
    `id_product` int(11) NOT NULL,
    PRIMARY KEY  (`id_country`, `id_product`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bpbc_categories` (
    `id_country` int(11) NOT NULL,
    `id_category` int(11) NOT NULL,
    PRIMARY KEY  (`id_country`, `id_category`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bpbc_manufacturers` (
    `id_country` int(11) NOT NULL,
    `id_manufacturer` int(11) NOT NULL,
    PRIMARY KEY  (`id_country`, `id_manufacturer`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'bpbc_suppliers` (
    `id_country` int(11) NOT NULL,
    `id_supplier` int(11) NOT NULL,
    PRIMARY KEY  (`id_country`, `id_supplier`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    Db::getInstance()->execute($query);
}
