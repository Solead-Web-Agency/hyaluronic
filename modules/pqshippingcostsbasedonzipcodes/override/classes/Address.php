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

class Address extends AddressCore
{
	public static function getZoneById($id_address) 
	{
		$address = new Address($id_address);
		#$module = Module::getInstanceByName('pqshippingcostsbasedonzipcodes');

		$result = Db::getInstance()->executeS('SELECT id_zone FROM `'._DB_PREFIX_.'pqshippingcostsbasedonzipcodes_conditions` 
					WHERE (
						filter = "range" 
						AND id_country = "'.(int)$address->id_country.'" 
						AND zipcode_min <= "'.$address->postcode.'" 
						AND zipcode_max >= "'.$address->postcode.'"
					) OR (
						filter = "equal"
						AND id_country = "'.(int)$address->id_country.'" 
						AND zipcode_min = "'.$address->postcode.'" 	
					) OR (
						filter = "starts"
						AND id_country = "'.(int)$address->id_country.'" 
						AND "'.$address->postcode.'" LIKE CONCAT (zipcode_min, "%") 
					) ORDER BY id_condition DESC');

		
		if ($result) 
			$id_zone = $result[0]['id_zone'];
		
		if (!empty($id_zone)) 
			return $id_zone;
		else 
			return parent::getZoneById($id_address);
	}
}
