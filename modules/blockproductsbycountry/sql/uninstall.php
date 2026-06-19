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
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bpbc_products`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bpbc_categories`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bpbc_manufacturers`';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'bpbc_suppliers`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
