<?php
/**
 * @author    Jamoliddin Nasriddinov <jamolsoft@gmail.com>
 * @copyright (c) 2020, Jamoliddin Nasriddinov
 * @license   http://www.gnu.org/licenses/gpl-2.0.html  GNU General Public License, version 2
 */

/**
 * This file returns array of sql queries that are required to be executed during module uninstallation.
 */
$sql = array();

// Drop tables that are created during module installation. Note: order of queries is important here.
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_category_map`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_csv`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport_shop`";
$sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "elegantaleasyimport`";

return $sql;
