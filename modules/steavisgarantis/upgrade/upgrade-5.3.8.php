<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Société des Avis Garantis <contact@societe-des-avis-garantis.fr>
*  @copyright 2013-2026 Société des Avis Garantis
*  @license   LICENSE.txt
*/
 
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_3_8($module) {
    $module->registerHook('displayProductAdditionalInfo');
    $module->registerHook('actionOrderHistoryAddAfter');
    
    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
        $module->registerHook('actionObjectOrderHistoryAddAfter');
    }

    return true;
}
