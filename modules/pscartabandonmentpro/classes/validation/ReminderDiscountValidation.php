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

class ReminderDiscountValidation implements ReminderStepsValidation
{
    const VALUE_TYPE_ERROR = 'The value type is not valid';
    const VALUE_ERROR = 'The amount is not valid';
    const VALUE_FROM_ERROR = 'The values "from" is not valid';
    const VALUE_TO_ERROR = 'The values "to" is not valid';
    const VALUE_RANGE_ERROR = 'The values "from/to" is not valid';
    const VALIDITY_ERROR = 'The validity is not valid';
    const TAXES_ERROR = 'The taxe input field not valid';
    const CUMULATE_ERROR = 'The cumulate field is not valid';

    /**
     * Validates the Discount step
     * 
     * @param array $data
     * 
     * @return array Error lists, bool if ok
     */
    public function validate($data)
    {
        $messagesError = array();
        
        if (empty($data)) {
            array_push(
                $messagesError,
                array(
                    self::VALUE_TYPE_ERROR,
                    self::VALUE_ERROR,
                    self::VALUE_FROM_ERROR,
                    self::VALUE_TO_ERROR,
                    self::VALUE_RANGE_ERROR,
                    self::VALIDITY_ERROR,
                    self::TAXES_ERROR,
                    self::CUMULATE_ERROR
                )
            );
            return $messagesError;  
        }

        foreach ($data as $key => $discount) {
            /**
            * Is mandatory
            * Check if data 'discount_value_type' exist
            * Data must equal 'amount' or 'percentage' or 'freeshipping' or 'no_discount'
            */
            if (isset($discount['discount_value_type'])) {
            if (!in_array($discount['discount_value_type'], array('amount', 'percentage', 'freeshipping', 'no_discount'))) {
                    $messagesError[$key][] = self::VALUE_TYPE_ERROR;
                }
            } else {
                $messagesError[$key][] = self::VALUE_TYPE_ERROR;
            }

            /**
            * Is mandatory
            * Check if data 'discount_value' exist
            * Data must be >= 1
            */
            if (isset($discount['discount_value'])) {
                if ((int)$discount['discount_value'] < 1 ) {
                    $messagesError[$key][] = self::VALUE_ERROR;
                }
            } else {
                $messagesError[$key][] = self::VALUE_ERROR;
            }

            /**
            * Is mandatory
            * Check if data 'discount_from' exist
            * Data must be >= 1
            */
            if (isset($discount['discount_from'])) {
                if ((int)$discount['discount_from'] < 1 ) {
                    $messagesError[$key][] = self::VALUE_FROM_ERROR;
                }
            } else {
                $messagesError[$key][] = self::VALUE_FROM_ERROR;
            }

            /**
            * Is mandatory
            * Check if data 'discount_to' exist
            * Data must be < 2
            */
            if (isset($discount['discount_to'])) {
                if ((int)$discount['discount_to'] < 1 ) {
                    $messagesError[$key][] = self::VALUE_TO_ERROR;
                }
            } else {
                $messagesError[$key][] = self::VALUE_TO_ERROR;
            }

            /**
            * Check data 'discount_to' and 'discount_from'
            * Data 'discount_to' must be > to data 'discount_from'
            */
            if (isset($discount['discount_to']) && isset($discount['discount_from'])) {
                if ((int) $discount['discount_to'] <  (int) $discount['discount_from']) {
                    $messagesError[$key][] = self::VALUE_RANGE_ERROR;
                }
            }

            /**
            * Is mandatory
            * Check if data 'discount_validity' exist
            * Data must be >= 0
            */
            if (isset($discount['discount_validity'])) {
                if ((int)$discount['discount_validity'] < 0 || (int)$discount['discount_validity'] > 255) {
                    $messagesError[$key][] = self::VALIDITY_ERROR;
                }
            } else {
                $messagesError[$key][] = self::VALIDITY_ERROR;
            }

            /**
            * Is mandatory
            * Check if data 'discount_ttc' exist
            * Data must be '1' or '0'
            */
            if (isset($discount['discount_ttc'])) {
                if (!in_array($discount['discount_ttc'], array('0', '1'))) {
                    $messagesError[$key][] = self::TAXES_ERROR;
                }
            } else {
                $messagesError[$key][] = self::TAXES_ERROR;
            }

            /**
            * Is mandatory
            * Check if data 'discount_cumulate' exist
            * Data must be '1' or '0'
            */
            if (isset($discount['discount_cumulate'])) {
                if (!in_array($discount['discount_cumulate'], array('0', '1'))) {
                    $messagesError[$key][] = self::CUMULATE_ERROR;
                }
            } else {
                $messagesError[$key][] = self::CUMULATE_ERROR;
            }
            
            if (!empty($messagesError[$key])) {
                $messagesError[$key] = array_unique($messagesError[$key]);
            } 
        }
        
        return $messagesError;
    }

    /**
     * Save the Discount step
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function save($data)
    {
        $oReminderInfos = new CartReminderInfo;

        // get the lastest reminder id 
        $iCartReminderId = $oReminderInfos->getLastestReminderId();

        foreach ($data as $key => $aValues) {
            
            $aValues['id_cart_abandonment'] = $iCartReminderId;

            // we escape the datas
            $aValues = array_map("pSQL", $aValues);

            if (!Db::getInstance()->insert('cart_abandonment_discount', $aValues)) {
                $oReminderInfos-> deleteReminderById($iCartReminderId);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Update the Discount step
     *
     * @param  array $data
     *
     * @return bool
     */
    public function update($data, $reminderId)
    {
        $oReminderInfos = new CartReminderInfo;

        if (!Db::getInstance()->update('cart_abandonment_discount', array('deleted' => 1), 'id_cart_abandonment = '.$reminderId)) {
            return false;
        }

        foreach ($data as $key => $aValues) {

            // we escape the datas
            $aValues = array_map("pSQL", $aValues);

            $aValues['id_cart_abandonment'] = $reminderId;
            if (!Db::getInstance()->insert('cart_abandonment_discount', $aValues)) {
                return false;
            }
        }

        return true;
    }
}