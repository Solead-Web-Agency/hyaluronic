<?php
/**
* 2007-2023 PrestaShop
*
* deliveryorderautoupdate
*
*  @author    Helloshop <modules@helloshop.com>
*  @copyright 2007-2023 Helloshop
*  @license   license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*  @Website: http://www.Helloshop.com
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0()
{
    $query = array();
    $date = pSQL(Date("Y-m-d"));
    if (check_index() == null) {
        $query[]  = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hl_tracking_history` (
				  `id` int(10) NOT NULL AUTO_INCREMENT,
				  `id_order` int(10) NOT NULL,
				  `hl_carrier` int(11) NOT NULL,
				  `method` tinyint(1) NOT NULL,
				  `success_response` tinyint(1) NOT NULL,
				  `event_code` tinyint(1) NOT NULL,
				  `carrier_response` varchar(128) NOT NULL,
				  `step_date` datetime NOT NULL,
				  `email_sent` tinyint(1) NOT NULL,
				  `date_add` datetime NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8';
        $query[]  = 'ALTER TABLE `'._DB_PREFIX_.'hl_carrier` ADD `method` TINYINT(1) NOT NULL AFTER `url`;';
        $query[]  = 'ALTER TABLE `'._DB_PREFIX_.'hl_carrier` ADD `user` VARCHAR(64) NOT NULL AFTER `method`;';
        $query[]  = 'ALTER TABLE `'._DB_PREFIX_.'hl_carrier` ADD `password` VARCHAR(64) NOT NULL AFTER `user`';
        $query[]  = 'INSERT INTO '._DB_PREFIX_.'configuration
        (name, value, id_shop, id_shop_group, date_add, date_add)
        value ("updatestatusversion", 1.3.0, 1, 1, "'.$date.'", "'.$date.'")';
        foreach ($query as $q) {
            Db::getInstance()->execute($q);
        }
        //insertData();
        return true;
    }
    return false;
}

function check_index()
{
    $indexes = Db::getInstance()->getRow(
        'SELECT value FROM '._DB_PREFIX_.'configuration WHERE name like "updatestatusversion"'
    );
    return ($indexes['value']);
}
function insertData()
{
    $sql = array();

    $sql[]  = 'ALTER TABLE `'._DB_PREFIX_.'hl_carrier` auto_increment = 1000';
    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }
}
