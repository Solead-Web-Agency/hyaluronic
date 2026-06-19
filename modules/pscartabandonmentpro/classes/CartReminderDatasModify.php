<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

class CartReminderDatasModify
{
    /**
     * Use parse_str for strings and array having strings
     *
     * @param  mixed $data  string|array
     *
     * @return mixed string|array
     */
    public function parseStr($data) 
    {
        $finalData = array();

        if (!is_array($data)) {
            $data = html_entity_decode($data);
            parse_str($data, $finalData);
            return $finalData;
        }
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $finalData[$key] = self::parseStr($value);
            } else {
                $value = html_entity_decode($value);
                parse_str($value, $finalData[$key]);
            }
        }
        
        return $finalData;
    }
}