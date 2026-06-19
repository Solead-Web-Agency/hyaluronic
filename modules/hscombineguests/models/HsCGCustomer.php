<?php
/**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class HsCGCustomer extends Customer
{

    /**
     * Light back office search for customers
     * @param string $query Searched string
     * @param int id_customer without current customer
     * @return array Corresponding customers
     */
    public static function searchGuestCustomers($query, $id_customer)
    {
        $sql = 'SELECT DISTINCT c.*
                    FROM `' . _DB_PREFIX_ . 'customer` AS c
                LEFT JOIN `' . _DB_PREFIX_ . 'address` a
                    ON (a.`id_customer` = c.`id_customer`)
                    WHERE
                    (
                        c.`email` LIKE \'%' . pSQL($query) . '%\'
                        OR c.`id_customer` LIKE \'%' . pSQL($query) . '%\'
                        OR c.`lastname` LIKE \'%' . pSQL($query) . '%\'
                        OR c.`firstname` LIKE \'%' . pSQL($query) . '%\'
                        OR a.`phone` LIKE \'%' . pSQL($query) . '%\'
                        OR a.`phone_mobile` LIKE \'%' . pSQL($query) . '%\'
                    )
					AND c.`active` = 1
					AND c.`deleted` = 0
					AND c.`id_customer` != ' . (int)$id_customer . '
					' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * get customer register is customer
     * @param array $selected_customer_ids
     * @return array
     */
    public static function getSelectedCustomers(array $selected_customer_ids)
    {
        $selected_customer_ids_string = implode(',', $selected_customer_ids);
        $sql = 'SELECT
                    c.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer` c
                    WHERE
                        c.`id_customer` IN  (' . pSQL($selected_customer_ids_string) . ')
                        AND c.`active` = 1
                        AND c.`deleted` = 0
                        AND c.`is_guest` = 0
                        ' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
        $resutls = array();
        $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $resutls[] = (int)$customer['id_customer'];
            }
        }
        return $resutls;
    }

    /**
     * get target customer register is customer
     * @param int $current_customer_id
     * @param array $selected_customer_id
     * @return int
     */
    public static function getTargetCustomerId($current_customer_id, array $selected_customer_id)
    {
        $target_customer_id = self::isCustomer((array) $current_customer_id);
        if (!$target_customer_id) {
            $target_customer_id = self::isCustomer($selected_customer_id);
            if (!$target_customer_id) {
                $target_customer_id = (int) $current_customer_id;
            }
        }
        return $target_customer_id;
    }

    /**
     * get target id_customer of customer
     * @param array $id_customers
     * @return int
     */
    protected static function isCustomer(array $id_customers)
    {
        $id_customers_string = implode(',', $id_customers);
        $sql = 'SELECT
                    c.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer` c
                    WHERE
                        c.`id_customer` IN  (' . pSQL($id_customers_string) . ')
                        AND c.`active` = 1
                        AND c.`deleted` = 0
                        AND c.`is_guest` = 0
                        ' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

        return Db::getInstance()->getValue($sql);
    }
    
    public static function getIdCustomersSameEmail()
    {
        $sql_where = array();
        $sql_where[] = "`email` != ''";
        $sql_where[] = '`active` = 1';
        $sql = 'SELECT
                    GROUP_CONCAT(`is_guest`) AS `is_guest_string`,
                    GROUP_CONCAT(`id_customer`) AS `id_customer_string`,
                    COUNT(`id_customer`) AS `total`
                    FROM `' . _DB_PREFIX_ . 'customer`
                    WHERE
                    '.implode(' AND ', $sql_where).'
                    ' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER).''
                . ' GROUP BY `email` HAVING `total` > 1';
        $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $id_customers = array();
        if (!empty($customers)) {
            foreach ($customers as $customer) {
                $id_customers[] = $customer['id_customer_string'];
            }
        }
        return $id_customers;
    }
}
