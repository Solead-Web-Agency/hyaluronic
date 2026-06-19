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

$sql = [];

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'orderedit_original_orders`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
