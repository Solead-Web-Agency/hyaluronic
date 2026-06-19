<?php
/**
* 2007-2019 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

// Check if the call came from CLI
if (php_sapi_name() !== 'cli') {
    exit('not CLI');
}

require_once __DIR__ . '/../../config/config.inc.php';
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/pscartabandonmentpro.php';

$isCLI = true;
$oCronTask = new pscartabandonmentproFrontCAPCronJobModuleFrontController($isCLI);
$oCronTask->initContent();