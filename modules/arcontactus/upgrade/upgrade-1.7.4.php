<?php
/**
* 2012-2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Areama <contact@areama.net>
*  @copyright 2018 Areama
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_7_4($module)
{
    $tab = new Tab();
    $tab->active = 1;
    $tab->class_name = 'AdminArCu';
    $tab->name = array();
    foreach (Language::getLanguages(true) as $lang) {
        $tab->name[$lang['id_lang']] = 'Callbacks';
    }
    if ($module->is17()) {
        $parentId = Tab::getIdFromClassName('CONFIGURE');
        $tab->id_parent = $parentId;
        if (property_exists($tab, 'icon')) {
            $tab->icon = 'link';
        }
    } else {
        $tab->id_parent = 0;
    }
    $tab->module = $module->name;
    $tab->add();
    
    return true;
}
