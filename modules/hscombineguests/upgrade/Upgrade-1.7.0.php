<?php
/**
 * Callback: upgrade module to 1.7.0
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @param string $module
 * @return boolean
 */

function upgrade_module_1_7_0($module)
{
    return $module->upgrade('1.7.0');
}
