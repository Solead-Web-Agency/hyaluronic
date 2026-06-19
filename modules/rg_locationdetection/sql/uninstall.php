<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_country`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_country_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_redirection`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_redirection_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_redirection_country`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_infobar`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_infobar_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'rg_locationdetection_infobar_country`;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
