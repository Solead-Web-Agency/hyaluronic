<?php
/**
* Price increment/discount by customer groups
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

if (!class_exists('Group', false)) {
class Group extends GroupCore
{
	protected static $cache_increment = array();

	public static function getReductionByIdGroup($id_group)
	{
		if (!isset(self::$_cacheReduction['group'][$id_group]))
		{
			$values = unserialize(Configuration::get('GI_GROUP_VALUES'));
			self::$_cacheReduction['group'][$id_group] = isset($values[$id_group]) && $values[$id_group] != 0 ? -$values[$id_group] : Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `reduction`
			FROM `'._DB_PREFIX_.'group`
			WHERE `id_group` = '.(int)$id_group);
		}
		return self::$_cacheReduction['group'][$id_group];
	}
}
}
