<?php
/**
 * ProQuality (c) All rights reserved.
 *
 * DISCLAIMER
 *
 * Do not edit, modify or copy this file.
 * If you wish to customize it, contact us at addons4prestashop@gmail.com.
 *
 * @author    Andrei Cimpean (ProQuality) <addons4prestashop@gmail.com>
 * @copyright 2015-2016 ProQuality
 * @license   Do not edit, modify or copy this file
 */

header('Access-Control-Allow-Origin: *');
/*
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
{
define('_PS_ADMIN_DIR_', getcwd());
*/
require_once (dirname(__FILE__).'/../../config/config.inc.php');
require_once (dirname(__FILE__).'/../../init.php');
ini_set('max_execution_time', '2880');
$module = Module::getInstanceByName('pqshippingcostsbasedonzipcodes');
$filename = pathinfo(__FILE__, PATHINFO_FILENAME);

if (Tools::isSubmit('token')) 
{
	if (!$module->isTokenValid(Tools::getValue('token'))) 
		die('Invalid Token!');
}
else 
	die('Invalid Token!');

$type = Tools::isSubmit('type') ? Tools::getValue('type') : false;

switch ($type) 
{
	case 'validateDataTablesInfo':
		
		$result = $module->validateDataTablesInfo(Tools::getValue('data'));
		
		if ($result === true) 
		{
			$response = array(
				'success' => true,
				'response' => $module->l('Success!', $filename)
			);
		} 
		elseif ($result == 'ERROR_DEMO_MODE') 
		{
			$response = array(
				'success' => false,
				'response' => $module->l('You can`t add a record in DEMO MODE!', $filename)
			);
		} 
		elseif ($result == 'ERROR_SAME_ZONE') 
		{
			$response = array(
				'success' => false,
				'response' => $module->l('The record was not added, condition has the same zone as the country`s default zone! You can change the default zone for this country in Localization > Countries.', $filename)
			);
		}
		
		die(Tools::jsonEncode($response));
		
	case 'conditions':
		$response = $module->fillGridDataTables($type, $_POST);
		break;

	case 'getCondition':
		$data = $module->getCondition(Tools::getValue('data'));
		die(Tools::jsonEncode($data));	
		
	default:
		break;
}


?>