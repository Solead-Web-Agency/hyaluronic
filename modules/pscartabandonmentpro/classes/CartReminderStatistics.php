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

class CartReminderStatistics
{
    /**
     * Get all the reminder statistics for all reminders, or just one.
     *
     * @param  int|bool $reminderId
     *
     * @return array $aStats
     */
    public function getReminderGeneralStatistics($dateFrom, $dateTo, $reminderId = false)
    {
        $context = Context::getContext();
        $sWhere = '';

        if ($reminderId) {
            $sWhere = 'AND id_cart_abandonment = '.$reminderId;
        }

        $aStats = Db::getInstance()->executeS(
            'SELECT 
                cac.id_cart_abandonment,
                (cacs.id_customer) AS amount_send,
                (cacs.visualize) AS visualize,
                (cacs.click) AS shop_click,
                (cacs.click_cart) AS cart_click,
                (cacs.click_product) AS product_click,
                (ord.id_order) AS nb_conversion,
                ((ord.total_paid * ord.conversion_rate)) AS amount_conversion,
                cac.cart_frequency_number,
                cac.cart_frequency_type 
            FROM `'._DB_PREFIX_.'cart_abandonment` cac
            LEFT JOIN `'._DB_PREFIX_.'cart_abandonment_customer_send` cacs ON (
                cacs.id_cart_abandonment = cac.id_cart_abandonment
                AND cac.id_shop = '.(int)$context->shop->id.'
                AND cacs.send_date >= "'.pSQL($dateFrom).'" 
                AND cacs.send_date <= "'.pSQL($dateTo).'"
            ) 
            LEFT JOIN `'._DB_PREFIX_.'cart_abandonment_customer_unsubscribe` cacu ON (cacu.id_customer = cacs.id_customer AND cacu.id_shop = cac.id_shop)
            LEFT JOIN `'._DB_PREFIX_.'orders` ord ON (ord.id_cart = cacs.id_cart AND ord.id_shop = cac.id_shop)
            WHERE 1
                AND cac.id_shop = '.(int)$context->shop->id.'
                AND cac.deleted = 0 
                '.$sWhere.'
           
            ORDER BY IF(cac.cart_frequency_type = "hour", cac.cart_frequency_number, cac.cart_frequency_number*24), cac.id_cart_abandonment
            '
        );     

        $iReminderId = 0;
        $iMailSent = 0;
        $iVisualized = 0;
        $iAmountConversion = 0;
        $iAmountSend = 0;

        $aStatsFinal = array();

        // Ordering the datas to get a perfect array
        foreach ($aStats as $key => $aReminder) {
            // When passing to a new ID Reminder, reset all stats
            if ($aReminder['id_cart_abandonment'] !== $iReminderId) {
                $iClickedEmails = 0;
                $iMailSent = 0;
                $iVisualized = 0;
                $iNbConversion = 0;
                $iAmountConversion = 0;
                $iAmountSend = 0;
            }

            // Prepare stats
            $iReminderId = $aReminder['id_cart_abandonment'];
            $iIsSent = ($aReminder['amount_send'] > 0) ? 1 : 0;
            $iMailSent = $iMailSent + $iIsSent;
            $iVisualized = $iVisualized + $aReminder['visualize'];
            $iClickedEmails = $iClickedEmails + (int) ($aReminder['shop_click'] || $aReminder['cart_click'] || $aReminder['product_click']);
            $iNbConversion = ($aReminder['nb_conversion'] > 0) ? 1 : 0;
            $iAmountConversion = $iAmountConversion + $iNbConversion;
            $iAmountSend = $iAmountSend + $aReminder['amount_conversion'];

            // Put datas in array only if the next key has datas for the next reminder_id
            if (!isset($aStats[$key+1]) || $aStats[$key+1]['id_cart_abandonment'] !== $iReminderId) {
                $aStatsFinal[$iReminderId]['id_cart_abandonment'] = $iReminderId;
                $aStatsFinal[$iReminderId]['cart_frequency_number'] = $aReminder['cart_frequency_number'];
                $aStatsFinal[$iReminderId]['cart_frequency_type'] = $aReminder['cart_frequency_type'];
                $aStatsFinal[$iReminderId]['amount_send'] = $iMailSent;
                $aStatsFinal[$iReminderId]['visualize'] = $iVisualized;
                $aStatsFinal[$iReminderId]['email_clicked'] = $iClickedEmails;
                $aStatsFinal[$iReminderId]['nb_conversion'] = $iAmountConversion;
                $aStatsFinal[$iReminderId]['amount_conversion'] = $iAmountSend;

                // Get percentage for emails opened on emails send
                $percentBetweenSendAndOpened = $this->getDivisionResultPercentage($iVisualized, $iMailSent); 
                $aStatsFinal[$iReminderId]['between_send_and_opened'] = round($percentBetweenSendAndOpened,2);
                
                // Get percentage for emails clicked on emails opened
                $percentBetweenOpenedAndClicked = $this->getDivisionResultPercentage($iClickedEmails, $iVisualized); 
                $aStatsFinal[$iReminderId]['between_opened_and_clicked'] = round($percentBetweenOpenedAndClicked,2);

                // Get percentage for emails converted on emails clicked
                $percentBetweenClickedAndConverted = $this->getDivisionResultPercentage($iAmountConversion,$iClickedEmails); 
                $aStatsFinal[$iReminderId]['between_clicked_and_converted'] = round($percentBetweenClickedAndConverted,2);
            }
        }

        return $aStatsFinal;
    }

    /**
     * Prevent Division by 0 and return a percentage
     *
     * @param  float|int $hover
     * @param  float|int $under
     *
     * @return float|int
     */
    private function getDivisionResultPercentage($hover, $under)
    {
        if ($hover != 0 && $under != 0) {
            return (($hover/$under) * 100);
        }
        
        return 0;
    }

    /**
     * Get all cart from existing in a date range AND get all abandoned carts in that range
     * Get datas without orders only
     *
     * @param string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array $aStats
     */
    public function getAbandonedCartsByPrestashop($dateFrom, $dateTo)
    {
        $context = Context::getContext();
        // In Prestashop, a cart is abandonmed when the cart is not updated for, at least, 24 hours
        $aStats = Db::getInstance()->getRow(
            'SELECT COUNT(all_cart.id_cart) AS all_cart, COUNT(abandon_cart.id_cart) AS ordered
            FROM `'._DB_PREFIX_.'cart` all_cart
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (all_cart.id_cart = o.id_cart AND all_cart.id_shop = o.id_shop)
            LEFT JOIN `'._DB_PREFIX_.'cart` abandon_cart ON (
                all_cart.id_cart = abandon_cart.id_cart 
                AND abandon_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND abandon_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND abandon_cart.id_cart = o.id_cart
                AND abandon_cart.id_shop = '.(int)$context->shop->id.'
            )
            WHERE 1
                AND all_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND all_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND all_cart.id_shop = '.(int)$context->shop->id
        );

        // To prevent division by 0
        if ($aStats['all_cart'] == 0) {
            $aStats['percent'] = 0;
            return $aStats;
        }

        // Get the percentage of abandoned carts
        $percent = 100 - ($aStats['ordered']/$aStats['all_cart']) * 100;
        $aStats['percent'] = round($percent, 2);

        return $aStats;
    }

    /**
     * Get all cart from existing in a date range AND get all abandoned carts in that range
     *
     * @param  string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param  string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array $aStats
     */
    public function getFinalizedCarts($dateFrom, $dateTo)
    {
        // In Prestashop, a cart is abandonmed when the cart is not updated for, at least, 24 hours
        $aStats = Db::getInstance()->getRow(
            'SELECT COUNT(DISTINCT all_cart.id_cart) AS all_cart, COUNT(DISTINCT abandon_cart.id_cart) AS ordered
            FROM `'._DB_PREFIX_.'cart` all_cart
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_customer_send` cacs ON all_cart.id_cart = cacs.id_cart 
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (all_cart.id_cart = o.id_cart AND all_cart.id_shop = o.id_shop)
            LEFT JOIN `'._DB_PREFIX_.'cart` abandon_cart ON (
                all_cart.id_cart = abandon_cart.id_cart 
                AND abandon_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND abandon_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND abandon_cart.id_cart = o.id_cart
                AND abandon_cart.id_shop = o.id_shop
            )
            WHERE 1
                AND all_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND all_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND all_cart.id_shop = '.(int)Context::getContext()->shop->id
        );

        // To prevent division by 0
        if ($aStats['all_cart'] == 0) {
            $aStats['percent'] = 0;
            return $aStats;
        }

        // Get the percentage of abandoned carts
        $percent = ($aStats['ordered']/$aStats['all_cart']) * 100;
        $aStats['percent'] = round($percent, 2);

        return $aStats;
    }

    /**
     * Get all cart turnover from existing in a date range
     *
     * @param  string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param  string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array $aStats
     */
    public function getTurnoverThatCouldBeGenerated($dateFrom, $dateTo)
    {
        $aStats = Db::getInstance()->executeS(
            'SELECT 
                all_cart.id_cart AS all_cart, 
                all_p.price AS all_price
            FROM `'._DB_PREFIX_.'cart` all_cart
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_customer_send` cacs ON all_cart.id_cart = cacs.id_cart 
            INNER JOIN `'._DB_PREFIX_.'cart_product` cp ON (
                cp.id_cart = all_cart.id_cart 
                AND cp.id_shop = all_cart.id_shop
            )
            INNER JOIN `'._DB_PREFIX_.'product` all_p ON (
                all_p.id_product = cp.id_product
            )
            WHERE 1
                AND all_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND all_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND all_cart.id_shop = '.(int)Context::getContext()->shop->id.'
            GROUP BY cacs.id_cart'
        );

        return $this->prepareTurnoverDatas($aStats, 'all_cart', 'all_price', 'all_price_final');
    }

    /**
     * Get all abandoned carts turnover in that range
     *
     * @param  string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param  string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array $aStats
     */
    public function getTurnoverGenerated($dateFrom, $dateTo)
    {
        $aStats = Db::getInstance()->executeS(
            'SELECT 
                abandon_cart.id_cart AS abandoned, 
                abandon_p.price AS abandoned_price
            FROM `'._DB_PREFIX_.'cart` abandon_cart
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment_customer_send` cacs ON abandon_cart.id_cart = cacs.id_cart 
            INNER JOIN `'._DB_PREFIX_.'cart_product` cp ON (
                cp.id_cart = abandon_cart.id_cart 
                AND cp.id_shop = abandon_cart.id_shop
            )
            INNER JOIN `'._DB_PREFIX_.'orders` o ON (
                abandon_cart.id_cart = o.id_cart 
                AND o.id_shop = abandon_cart.id_shop
            )
            INNER JOIN `'._DB_PREFIX_.'product` abandon_p ON (
                abandon_p.id_product = cp.id_product 
                AND cp.id_cart = o.id_cart
            )
            WHERE 1
                AND abandon_cart.date_upd >= DATE_ADD("'.pSQL($dateFrom).'", INTERVAL 1 HOUR)
                AND abandon_cart.date_upd <= DATE_ADD("'.pSQL($dateTo).'", INTERVAL 1 HOUR)
                AND abandon_cart.id_shop = '.(int)Context::getContext()->shop->id.'
            GROUP BY abandon_cart.id_cart'
        );

        return $this->prepareTurnoverDatas($aStats, 'abandoned', 'abandoned_price', 'abandoned_price_final');
    }

    /**
     * Prepare Datas to use them in the template
     *
     * @param  array $aStats
     * @param  string $sKeyOne
     * @param  string $sKeyTwo
     * @param  string $sKeyFinalPrice
     *
     * @return array
     */
    private function prepareTurnoverDatas($aStats, $sKeyOne, $sKeyTwo, $sKeyFinalPrice)
    {
        // Initialize returned array
        $aFinalStats = array(
            $sKeyOne => 0,
            $sKeyTwo => 0,
            $sKeyFinalPrice => 0,
        );

        // If there is no datas, we return the previous initialized array
        if (!$aStats) {
            return $aFinalStats;
        }

        // Rework the datas
        foreach ($aStats as $key => $aData) {
            $aFinalStats[$sKeyOne] = $aFinalStats[$sKeyOne] + 1;
            $aFinalStats[$sKeyTwo] = $aFinalStats[$sKeyTwo] + $aData[$sKeyTwo];
        }

        // set a price from Tools's class
        $aFinalStats[$sKeyFinalPrice] = Tools::displayPrice((float)$aFinalStats[$sKeyTwo]);

        return $aFinalStats;
    }

    /**
     * Get all unsubscribe datas in a date range
     *
     * @param  string $dateFrom (yyyy-mm-dd hh:ii:ss)
     * @param  string $dateTo (yyyy-mm-dd hh:ii:ss)
     *
     * @return array $aStats
     */
    public function getUnsubscribedDatas($dateFrom, $dateTo)
    {
        $aStats = Db::getInstance()->getRow(
            'SELECT 
                COUNT(cacs.id_send) AS send_reminder, 
                COUNT(DISTINCT cacu.id_customer) AS unsubscribed
            FROM `'._DB_PREFIX_.'cart_abandonment_customer_send` cacs
            INNER JOIN `'._DB_PREFIX_.'cart_abandonment` ca ON ca.id_cart_abandonment = cacs.id_cart_abandonment
            LEFT JOIN  `'._DB_PREFIX_.'cart_abandonment_customer_unsubscribe` cacu 
                ON (
                    cacu.id_customer = cacs.id_customer AND 
                    cacu.date >= "'.pSQL($dateFrom).'" AND 
                    cacu.date <= "'.pSQL($dateTo).'"
                )
            WHERE 1
                AND cacs.send_date >= "'.pSQL($dateFrom).'"
                AND cacs.send_date <= "'.pSQL($dateTo).'"
                AND ca.id_shop = '.(int) Context::getContext()->shop->id
        );

        // To prevent division by 0
        if ($aStats['send_reminder'] == 0) {
            $aStats['percent'] = 0;
            return $aStats;
        }

        // Get the percentage of abandoned carts
        $percent = ($aStats['unsubscribed']/$aStats['send_reminder']) * 100;
        $aStats['percent'] = round($percent, 2);

        return $aStats;
    }

}