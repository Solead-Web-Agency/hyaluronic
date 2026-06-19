<?php
/**
*  @author    Amazzing <mail@amazzing.ru>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function upgrade_module_2_7_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    if (method_exists($module_obj, 'prepareDataBase')) {
        $module_obj->prepareDataBase();
    }
    return true;
}
