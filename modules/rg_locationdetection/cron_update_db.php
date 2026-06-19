<?php
/**
 * Advanced Location Detection
 *
 * @author    Rolige <www.rolige.com>
 * @copyright 2011-2019 Rolige - All Rights Reserved
 * @license   Proprietary and confidential
 */

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'/../../init.php';
require_once dirname(__FILE__).'/rg_locationdetection.php';

$module = new RG_LocationDetection();

if ($module->active && Tools::getValue('secure_key') == $module->secure_key) {
    $module->downloadDB();

    die(1);
}

die(0);
