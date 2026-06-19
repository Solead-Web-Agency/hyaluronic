<?php
/**
 * Project : everpsminimumorder
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

$sql[] =
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'everpsminimumorder` (
    `id_everpsminimumorder` int(10) unsigned NOT NULL auto_increment,
    `id_shop` int(10) unsigned NOT NULL,
    `id_country` int(10) unsigned NOT NULL,
    `id_group` int(10) unsigned NOT NULL,
    `amount` text NOT NULL,
    `use_tax` int(10) unsigned DEFAULT NULL,
    `active` int(10) unsigned DEFAULT NULL,
    PRIMARY KEY (`id_everpsminimumorder`))
    ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

foreach ($sql as $s) {
    if (!Db::getInstance()->execute($s)) {
        return false;
    }
}
