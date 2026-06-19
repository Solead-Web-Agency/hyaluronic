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

class CartReminderCustomerInfo
{

    /**
     * getAbandonedCart
     *
     * @param  int $reminderId
     * @param  int $delayToBeAbandonmentCart
     *
     * @return mixed array|bool
     */
    public function getAbandonedCart($reminderId, $delayToBeAbandonmentCart, $aTargetProfile)
    {
        $sWhereSubscribedNewsletter = 'cu.newsletter IN (1,0)';

        // Get the maximum date at which cart reminders can be sent.
        $reminderInfo = new CartReminderInfo;
        $maxDateSending = $reminderInfo->getMaxDateSending();

        if ($aTargetProfile['cart_target_newsletter']) {
            $sWhereSubscribedNewsletter = 'cu.newsletter = 1';
        }
        
        $query = 'SELECT ca.*, 
                cu.firstname, 
                cu.lastname, 
                cu.id_customer, 
                cu.email,
                cg.name as gender,
                ord.id_order,
                cr.id_send,
                SUM((p.price*cp.quantity)) as cart_value,
                cacu.id_customer as unsubscribe,
                cu.id_lang,
                cu.id_shop
            FROM `'._DB_PREFIX_.'cart` ca
            INNER JOIN `'._DB_PREFIX_.'customer` cu ON cu.id_customer = ca.id_customer
            LEFT JOIN `'._DB_PREFIX_.'gender_lang` cg ON (cg.id_gender = cu.id_gender AND cg.id_lang = cu.id_lang)
            LEFT JOIN `'._DB_PREFIX_.'cart_abandonment_customer_unsubscribe` cacu ON cacu.id_customer = cu.id_customer
            LEFT JOIN `'._DB_PREFIX_.'orders` ord ON ord.id_cart = ca.id_cart
            LEFT JOIN `'._DB_PREFIX_.'cart_abandonment_customer_send` cr ON (cr.id_cart_abandonment = '.(int)$reminderId.' AND ca.id_cart = cr.id_cart)
            INNER JOIN `'._DB_PREFIX_.'cart_product` cp ON ca.id_cart = cp.id_cart
            INNER JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = cp.id_product
            WHERE 
                NOW() >= DATE_ADD(ca.date_upd, INTERVAL '.(int)$delayToBeAbandonmentCart.' HOUR)
                AND ca.date_upd >= "'.pSQL($maxDateSending).'"
                AND '.$sWhereSubscribedNewsletter.' 
                AND cu.active = 1
            GROUP BY ca.id_cart
            HAVING 
                ord.id_order IS NULL 
                AND cr.id_send IS NULL 
                AND unsubscribe IS NULL
            LIMIT 20';
        
        $customerList = Db::getInstance()->executeS($query);

        return $this->getCustomerFinalList($aTargetProfile, $customerList);
    }

    /**
     * getAbandonedCartForTestUser
     *
     * @param  int $reminderId
     * @param  string $sEmail
     *
     * @return mixed array|bool
     */
    public function getAbandonedCartForTestUser()
    {
        // Prepare query
        // We get the customers cart link
        $query = 'SELECT ca.*, 
                    cu.firstname, 
                    cu.lastname, 
                    cu.id_customer, 
                    cu.email,
                    cg.name as gender,
                    ord.id_order,
                    SUM((p.price*cp.quantity)) as cart_value,
                    cu.id_lang,
                    cu.id_shop
                FROM `'._DB_PREFIX_.'cart` ca
                INNER JOIN `'._DB_PREFIX_.'customer` cu ON cu.id_customer = ca.id_customer
                LEFT JOIN `'._DB_PREFIX_.'gender_lang` cg ON (cg.id_gender = cu.id_gender AND cg.id_lang = cu.id_lang)
                LEFT JOIN `'._DB_PREFIX_.'orders` ord ON ord.id_cart = ca.id_cart
                INNER JOIN `'._DB_PREFIX_.'cart_product` cp ON ca.id_cart = cp.id_cart
                INNER JOIN `' . _DB_PREFIX_ . 'product` p ON p.id_product = cp.id_product
                WHERE 1
                GROUP BY ca.id_cart
                ORDER BY RAND()
                LIMIT 1';
                
        return Db::getInstance()->executeS($query);
    }

    /**
     * Get the customer Final list for who we need to send a reminder.
     * We check if they are eligible for the reminder
     *
     * @param  array $aTargetProfile
     * @param  array $customerList
     *
     * @return array
     */
    private function getCustomerFinalList($aTargetProfile, $customerList)
    {
        $customerFinalList = $customerList;

        foreach ($customerFinalList as $key => $customer) {
            $customerOrdersHistory = Order::getCustomerOrders($customer['id_customer']);

            // If we don't take customer without orders, we remove them from the list
            if (!$aTargetProfile['cart_target_no_orders'] && empty($customerOrdersHistory)) {
                unset($customerFinalList[$key]);
            }

            // We check if the lastest possible order exist, else we continue
            if (empty($customerOrdersHistory)) {
                continue;
            }

            $latestOrder = reset($customerOrdersHistory);

            $dateNow = new DateTime('now');
            $orderDate = new DateTime($latestOrder['date_add']);
            $orderDate->modify('+6 month');

            // If we don't take active customer, we remove them from the list
            if (!$aTargetProfile['cart_target_active'] && $orderDate >= $dateNow) {
                unset($customerFinalList[$key]);
            }

            // If we don't take inactive customer, we remove them from the list
            if (!$aTargetProfile['cart_target_inactive'] && $orderDate <= $dateNow) {
                unset($customerFinalList[$key]);
            }
        }

        return $customerFinalList;
    }
}
