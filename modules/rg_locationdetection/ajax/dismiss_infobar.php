<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

require_once dirname(__FILE__).'/../../../config/config.inc.php';
require_once dirname(__FILE__).'/../../../init.php';
include_once dirname(__FILE__).'/../rg_locationdetection.php';

$module = new RG_LocationDetection();

if ($module->active &&
    (Tools::getValue('public_key') == $module->public_key) &&
    Tools::getValue('dismiss')
) {
    RgLdCookie::get()->infobar_show = false;

    die('1');
}

die('0');
