<?php
/**
 * 2007-2015 IQIT-COMMERCE.COM
 *
 * NOTICE OF LICENSE
 *
 *  @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
 *  @copyright 2007-2015 IQIT-COMMERCE.COM
 *  @license   GNU General Public License version 2
 *
 * You can not resell or redistribute this software.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($object)
{
    Configuration::updateValue('iqitextendedproduct_hook', 'modal');

    $customHooks = array(
        'displayAsLastProductImage',
        'displayAsFirstProductImage'
    );

    foreach($customHooks as $hookName){
        $idHook = Hook::getIdByName($hookName);
        if (!$idHook) {
            $newHook = new Hook();
            $newHook->name = pSQL($hookName);
            $newHook->title = pSQL($hookName);
            $newHook->position = 1;
            $newHook->add();
        }
    }


    $object->registerHook('displayAsLastProductImage');
    $object->registerHook('displayAsFirstProductImage');

    return true;
}
