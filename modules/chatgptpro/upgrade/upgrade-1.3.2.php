<?php
/**
* 2007-2023 Weblir
*
*  @author    weblir <hello@weblir.com>
*  @copyright 2012-2023 weblir
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*  International Registered Trademark & Property of weblir.com
*
*  You are allowed to modify this copy for your own use only. You must not redistribute it. License
*  is permitted for one Prestashop instance only but you can install it on your test instances.
*/

function upgrade_module_1_3_2($module)
{
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `old_tags` varchar(256)');
    Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'chatgptpro_log` ADD COLUMN `new_tags` varchar(256)');

    return true;
}
