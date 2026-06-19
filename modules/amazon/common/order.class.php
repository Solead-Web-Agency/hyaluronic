<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) { exit; }
if (!class_exists('CommonOrder')) {
    abstract class CommonOrder extends Order
    {
        /**
         * @param $order
         *
         * @return int|null
         */
        public function getWsShippingNumber()
        {

            if (version_compare(_PS_VERSION_, '8', '>=')) {
                return parent::getWsShippingNumber();
            }

            if (!empty($this->shipping_number)) {
                return ($this->shipping_number);
            } else {
                if (version_compare(_PS_VERSION_, '1.5', '>')) {
                    $id_order_carrier = Db::getInstance()->getValue(
                        '
                        SELECT `id_order_carrier`
                        FROM `'._DB_PREFIX_.'order_carrier`
                        WHERE `id_order` = '.(int)$this->id
                    );

                    if ($id_order_carrier) {
                        $order_carrier = new OrderCarrier($id_order_carrier);

                        if (Validate::isLoadedObject($order_carrier)) {
                            if (!empty($order_carrier->tracking_number)) {
                                return ($order_carrier->tracking_number);
                            }
                        }
                    }
                }
            }

            return (null);
        }


        /**
         * @param $date_add
         * @param $amount
         * @param $payment_title
         * @param $module
         *
         * @return bool
         */
        public static function isExistingOrder($date_add, $amount, $payment_title, $module)
        {
            $sql = 'SELECT `id_order` FROM `'._DB_PREFIX_.'orders`
                WHERE `payment` = "'.pSQL($payment_title).'" 
                    AND  `module`="'.pSQL($module).'" 
                    AND `date_add`="'.pSQL($date_add).'" 
                    AND `total_paid`='.(float)$amount;

            $result = Db::getInstance()->executeS($sql, true, false);

            if (static::$debug_mode) {
                CommonTools::p(sprintf(
                    '%s - %s::%s - line #%d'.Amazon::LF,
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
                CommonTools::p(sprintf('SQL: %s'.Amazon::LF, $sql));
                CommonTools::p(sprintf('Result: %s'.Amazon::LF, print_r($result, true)));
            }

            if (is_array($result) && count($result)) {
                return ($result[0]['id_order']);
            }

            return (false);
        }
    }
}
