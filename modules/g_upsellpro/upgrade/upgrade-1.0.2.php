<?php
/**
 * This is main class of module.
 *
 * @author    Globo Software Solution JSC <contact@globosoftware.net>
 * @copyright 2021 Globo ., Jsc
 * @license   please read license in file license.txt
 * @link	     http://www.globosoftware.net
 */

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_0_2($module)
{
    $module;
    Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'g_upsellrule` ADD COLUMN `showoffers` tinyint(1) NULL');
    return true;
}