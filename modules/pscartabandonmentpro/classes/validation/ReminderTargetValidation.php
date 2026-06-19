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

class ReminderTargetValidation implements ReminderStepsValidation
{
    const NEWSLETTER_ERROR = 'Newletter is invalid';
    const TARGET_ERROR = 'Target is invalid';
    const FREQUENCY_NUMBER_ERROR = 'Frequency number is invalid';
    const FREQUENCY_TYPE_ERROR = 'Frequency type is invalid';

    /**
     * Validates the Target step
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
                self::NEWSLETTER_ERROR,
                self::TARGET_ERROR,
                self::FREQUENCY_NUMBER_ERROR,
                self::FREQUENCY_TYPE_ERROR
            );
            return $messagesError;  
        }

        /**
        * Is mandatory
        * Check if data 'cart_target_newsletter' exist
        * Data must be '1' or '0'
        */
        if (isset($data['cart_target_newsletter'])) {
            if (!in_array($data['cart_target_newsletter'], array('0', '1'))) {
                $messagesError[] = self::NEWSLETTER_ERROR;
            }
        } else {
            $messagesError[] = self::NEWSLETTER_ERROR;
        }

        /**
        * Is mandatory
        * Check if data 'cart_target' exist
        * Data must be an array
        * count(Data) must be > 0
        * Data must be 'active' or 'inactive' or 'no_orders'
        */
        if (isset($data['cart_target'])) {
            if (!is_array($data['cart_target']) || !count($data['cart_target'])) {
                $messagesError[] = self::TARGET_ERROR;
            } else {
                foreach ($data['cart_target'] as $value) {
                    if (!in_array($value, array('active', 'inactive', 'no_orders'))) {
                        $messagesError[] = self::TARGET_ERROR;
                        break;
                    }
                }
            }
        } else {
            $messagesError[] = self::TARGET_ERROR;
        }

        /**
        * Is mandatory
        * Check if data 'cart_frequency_number' exist
        * Data must be between 1 and 255
        */
        if (isset($data['cart_frequency_number'])) {
            if ((int) $data['cart_frequency_number'] < 1 || (int) $data['cart_frequency_number'] > 255) {
                $messagesError[] = self::FREQUENCY_NUMBER_ERROR;
            }
        } else {
            $messagesError[] = self::FREQUENCY_NUMBER_ERROR;
        }

        /**
        * Is mandatory
        * Check if data 'cart_frequency_type' exist
        * Data must be 'hour' or 'day'
        */
        if (isset($data['cart_frequency_type'])) {
            if (!in_array($data['cart_frequency_type'], array('hour', 'day'))) {
                $messagesError[] = self::FREQUENCY_TYPE_ERROR;
            } 
        } else {
            $messagesError[] = self::FREQUENCY_TYPE_ERROR;
        }

        return $messagesError;
    }

    /**
     * prepareTargetDatas
     *
     * @param  array $data
     *
     * @return array
     */
    private function prepareTargetDatas($data) 
    {
        $aExistingCartTargets = array(
            'active',
            'inactive',
            'no_orders'
        );

        /**
        * $data['cart_target'] is an array which can contain these following datas : 'active' or 'inactive' or 'no_orders'
        * The result must be an array having these datas as key :
        * $data['cart_target_active'] or $data['cart_target_inactive'] or $data['cart_target_no_orders']
        */
        foreach ($data['cart_target'] as $key => $value) {
            $data['cart_target_'.$value] = 1;
        }

        // if a target is not on the list, we create it and set it to 0
        foreach ($aExistingCartTargets as $value) {
            if (!isset($data['cart_target_'.$value])) {
                $data['cart_target_'.$value] = 0;
            }
        }

        unset($data['cart_target']);

        // we escape the datas
        $data = array_map("pSQL", $data);

        return $data;
    }

    /**
     * Save the Target step
     * 
     * @param array $data
     * 
     * @return bool
     */
    public function save($data)
    {
        $data = $this->prepareTargetDatas($data);
        $data['id_shop'] = (int)Context::getContext()->shop->id;

        if (!Db::getInstance()->insert('cart_abandonment', $data)) {
            return false;
        }
        
        return true;
    }

    /**
     * Update the Target step
     *
     * @param  array $data
     *
     * @return bool
     */
    public function update($data, $reminderId)
    {
        $data = $this->prepareTargetDatas($data);
        $where = 'id_cart_abandonment = '.$reminderId.' AND id_shop = '.(int)Context::getContext()->shop->id;

        if (!Db::getInstance()->update('cart_abandonment', $data, $where)) {
            return false;
        }

        return true;
    }
}
