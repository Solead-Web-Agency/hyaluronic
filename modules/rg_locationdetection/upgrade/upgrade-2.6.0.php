<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_6_0($module)
{
    Tools::deleteFile($module->getLocalPath().'autoload.php');

    if ($module->uninstallOverrides()) {
        return $module->installOverrides();
    }

    return false;
}
