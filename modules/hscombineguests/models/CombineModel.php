<?php
/**
 * Combine guests for PrestaShop
 *
 * @author    PrestaMonster
 * @copyright PrestaMonster
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class CombineModel extends ObjectModel
{

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'customer',
        'primary' => 'id_customer'
    );

    /**
     * @desc check if guest is exists as a customer
     * @param string $id_guest_string
     * @return int id_guest || Boolean
     *
     * */
    public function checkGuestIsExistsCustomer($id_guest_string)
    {
        $sql = 'SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'customer`
					WHERE `id_customer` IN (' . pSQL($id_guest_string) . ') AND `is_guest` = 0 and `active` = 1';
        return Db::getInstance()->getRow($sql);
    }

    /**
     * @desc Combine guest to customer
     * @param string $guest_email
     * @return int $id_customer
     *
     * */
    public function combineGuestToCustomer($id_customer, $password)
    {
        $customer = new Customer((int) $id_customer);
        $customer->is_guest = 0;
        $customer->passwd = $password;
        $customer->cleanGroups();
        $customer->addGroups(array(Configuration::get('PS_CUSTOMER_GROUP')));
        return $customer->update();
    }

    /**
     * @desc combine all guest's address to customer converted
     * @param array $id_guests
     * @param int $id_customer
     * @return boolean
     * */
    public function combineAddresses(array $id_guests, $id_customer)
    {
        $id_guests_string = implode(',', $id_guests);
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'address` SET `id_customer` = ' . (int) $id_customer . '
					WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';
        $flag = Db::getInstance()->execute($sql);

        if ($flag) {
            $flag = $this->updateAliasAddress($id_customer);
        }
        return $flag;
    }

    /**
     * get alias address of customer
     * @param int $customer_id
     * @return array
     */
    protected function getAddressAlias($customer_id)
    {
        $sql = 'SELECT `id_address`, `alias`
                FROM `' . _DB_PREFIX_ . 'address`
                WHERE `id_customer` = ' . (int) $customer_id;

        $results = Db::getInstance()->executeS($sql);
        $alias_address = array();
        if (!empty($results)) {
            foreach ($results as $result) {
                $alias_address[$result['id_address']] = $result['alias'];
            }
        }
        return $alias_address;
    }

    /**
     * update alias address of customer which are duplicate
     * @param int $customer_id
     * @return boolean
     */
    protected function updateAliasAddress($customer_id)
    {
        $flag = true;
        $alias_address = $this->getAddressAlias($customer_id);
        if (!empty($alias_address)) {
            $alias_address_uniques = array_unique($alias_address);
            $alias_address_diffs = array_diff_key($alias_address, $alias_address_uniques);
            if (!empty($alias_address_diffs)) {
                foreach ($alias_address_diffs as $id_address => $alias_address_diff) {
                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'address`
                                SET `alias` = \'' . $alias_address_diff . '-' . (int) $id_address . '\'
                                WHERE `id_address` = ' . (int) $id_address;
                    if (!Db::getInstance()->query($sql) && $flag) {
                        $flag = false;
                    }
                }
            }
            $this->combineDuplicateAddresses($alias_address);
        }
        return $flag;
    }
    
    protected function combineDuplicateAddresses($alias_addresses)
    {
        $success = array();
        $object_addresses = array();
        $the_same_addresses = array();
        $unset_array = array();
        foreach ($alias_addresses as $id_address => $alias_address) {
            $address = new Address((int)$id_address);
            if (Validate::isLoadedObject($address)) {
                $object_addresses[$id_address] = get_object_vars($address);
            }
        }
        if (!empty($object_addresses)) {
            $i = 0;
            foreach ($object_addresses as $id_address => &$obj_address) {
                $j = $i;
                foreach ($object_addresses as $id_address2 => $obj_address2) {
                    if ($j > $i && $id_address !== $id_address2 && !in_array($id_address2, $unset_array)) {
                        if ($this->compareAddresses($obj_address, $obj_address2)) {
                            $unset_array[] = $id_address2;
                            $the_same_addresses[] = $id_address.'_'.$id_address2;
                            unset($object_addresses[$id_address]);
                        }
                    }
                    $j++;
                }
                $i++;
            }
        }
        if (!empty($the_same_addresses)) {
            foreach ($the_same_addresses as $the_same_address) {
                $addresses = explode('_', $the_same_address);
                // update address 2 by address 1 in the table:
                // 1. cart: id_address_delivery, id_address_invoice
                $success[] = $this->updateAddressInTableCart($addresses);
                // 2. cart_product: id_address_delivery
                $success[] = $this->updateAddressInTableCartProduct($addresses);
                // 3. customization: id_address_delivery
                $success[] = $this->updateAddressInTableCustomization($addresses);
                // 4. orders: id_address_delivery, id_address_invoice
                $success[] = $this->updateAddressInTableOrders($addresses);
                if (array_sum($success) >= count($success)) {
                    // delete address 2 in the table address:
                    $address2 = new Address($addresses[1]);
                    $success[] = $address2->delete();
                }
            }
        }
        return array_sum($success) >= count($success);
    }
    
    /**
     * @param array $addresses
     * @return boolean
     */
    protected function updateAddressInTableCart($addresses)
    {
        $success = array();
        $success[] = Db::getInstance()->update('cart', array('id_address_delivery' => (int) $addresses[0]), '`id_address_delivery` = ' . (int) $addresses[1]);
        $success[] = Db::getInstance()->update('cart', array('id_address_invoice' => (int) $addresses[0]), '`id_address_invoice` = ' . (int) $addresses[1]);
        return array_sum($success) >= count($success);
    }
    
    /**
     * @param array $addresses
     * @return boolean
     */
    protected function updateAddressInTableCartProduct($addresses)
    {
        return Db::getInstance()->update('cart_product', array('id_address_delivery' => (int) $addresses[0]), '`id_address_delivery` = ' . (int) $addresses[1]);
    }
    
    /**
     * @param array $addresses
     * @return boolean
     */
    protected function updateAddressInTableCustomization($addresses)
    {
        return Db::getInstance()->update('customization', array('id_address_delivery' => (int) $addresses[0]), '`id_address_delivery` = ' . (int) $addresses[1]);
    }
    
    /**
     * @param array $addresses
     * @return boolean
     */
    protected function updateAddressInTableOrders($addresses)
    {
        $success = array();
        $success[] = Db::getInstance()->update('orders', array('id_address_delivery' => (int) $addresses[0]), '`id_address_delivery` = ' . (int) $addresses[1]);
        $success[] = Db::getInstance()->update('orders', array('id_address_invoice' => (int) $addresses[0]), '`id_address_invoice` = ' . (int) $addresses[1]);
        return array_sum($success) >= count($success);
    }

    /**
     *
     * @param array $address1
     * <pre>
     * array (
     *  ['id_address'] => int
     *  ['id_customer'] => int
     *  ['id_manufacturer'] => int
     *  ['id_supplier'] => string
     *  ['id_warehouse'] => string
     *  ['alias'] => string
     *  ['company'] => string
     *  ['firstname'] => string
     *  ['lastname'] => string
     *  ['id_country'] => int
     *  ['id_state'] => int
     *  ['address1'] => string
     *  ['address2'] => string
     *  ['city'] => string
     *  ['postcode'] => string
     *  ['other'] => string
     *  ['phone'] => string
     *  ['phone_mobile'] => string
     *  ['vat_number'] => string
     *  ['dni'] => string
     *  ['date_add'] => date time
     *  ['date_upd'] => date time
     *  ['active'] => bolean
     *  ['deleted'] => bolean
     * )
     * </pre>
     * @param array $address2 @see $address1
     * @return bolean
     */
    public static function compareAddresses(array $address1, array $address2)
    {
        $array_to_compare = array(
            'firstname',
            'lastname',
            'id_country',
            'id_state',
            'address1',
            'city',
            'phone_mobile',
            'phone',
            'postcode'
        );
        $is_the_same_address = true;
        foreach ($array_to_compare as $field) {
            $is_the_same_address &= $address1[$field] == $address2[$field];
        }
        return $is_the_same_address;
    }

    /**
     * @desc combine Guest's Cart to Customer converted
     * @param int $id_guests
     * @param int $id_customer
     * @return boolean
     * */
    public function combineCarts(array $id_guests, $id_customer)
    {
        $sql_get_secure_key = 'SELECT `secure_key` FROM `' . _DB_PREFIX_ . 'customer` WHERE `id_customer` =' . (int) $id_customer;
        $secure_key = Db::getInstance()->getValue($sql_get_secure_key);
        $id_guests_string = implode(',', $id_guests);
        // now the customer doesn't a guest, so we need update everything of guest to customer
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart`
                    SET `id_customer` = ' . (int) $id_customer . ', `id_guest` = 0, `secure_key` = "' . pSQL($secure_key) . '"
                    WHERE `id_customer` IN (' . (int) $id_customer . ',' . pSQL($id_guests_string) . ')';
        return Db::getInstance()->execute($sql);
    }

    /**
     * @desc convert all guest's orders to customer converted
     * @param int $id_guests
     * @param int $id_customer
     * @return boolean
     * */
    public function combineOrders(array $id_guests, $id_customer)
    {
        $id_guests_string = implode(',', $id_guests);
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders`
                    SET `id_customer` = ' . (int) $id_customer . '
		    WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';
        return Db::getInstance()->execute($sql);
    }

    /**
     * @desc Delete customer or guest after combine
     * @param array $id_cutomers
     * @return boolean
     * */
    public function deleteGuestsCustomers(array $id_cutomers)
    {
        $id_guests_string = implode(',', $id_cutomers);
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'customer`
				WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Combine all loyalty of guests to customer
     * @param array $id_guests
     * @param int $id_customer
     * @return boolean
     */
    public function combineLoyalty(array $id_guests, $id_customer)
    {
        $id_guests_string = implode(',', $id_guests);
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'loyalty` SET `id_customer` = ' . (int) $id_customer . '
					WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';
        return Db::getInstance()->execute($sql);
    }

    /**
     * Combine all order message of guests to customer
     * @param array $id_guests
     * @param int $id_customer
     * @return boolean
     */
    public function combineMessage(array $id_guests, $id_customer)
    {
        $id_guests_string = implode(',', $id_guests);
        // update message table
        $message = 'UPDATE `' . _DB_PREFIX_ . 'message` SET `id_customer` = ' . (int) $id_customer . '
					WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';
        // update customer_thread table
        $customer_thread = 'UPDATE `' . _DB_PREFIX_ . 'customer_thread` SET `id_customer` = ' . (int) $id_customer . '
					WHERE `id_customer` IN (' . pSQL($id_guests_string) . ')';

        return (Db::getInstance()->execute($message) && Db::getInstance()->execute($customer_thread));
    }
    
    /**
     * Combine all private notes of guests to customer
     * @param array $id_guests
     * @param int $id_customer
     * @return boolean
     */
    public function combinePrivateNotes(array $id_guests, $id_customer)
    {
        $result = true;
        $id_customers = array_merge($id_guests, array($id_customer));
        $query = new DbQuery();
        $query->select('`note`');
        $query->from('customer');
        if (!empty($id_customers)) {
            $query->where('`id_customer` IN (' . implode(', ', array_map('intval', $id_customers)) . ')');
        }
        $customer_notes = Db::getInstance()->executeS($query);
        $private_note = '';
        if (!empty($customer_notes)) {
            foreach ($customer_notes as $customer_note) {
                $space = !empty($private_note) ? ' ' : '';
                if ($customer_note['note'] !== '') {
                    $private_note .= $space . $customer_note['note'];
                }
            }
        }
        if ($private_note) {
            $sql = 'UPDATE `' . _DB_PREFIX_ . 'customer` SET `note` = "' . pSQL($private_note) . '" WHERE `id_customer` =' . (int) $id_customer;
            $result = Db::getInstance()->execute($sql);
        }
        return $result;
    }

    /**
     * update id_customer of specific price to target customer
     * @param array $customer_ids
     * @param int $target_customer_id
     * @param int $id_currency
     * @param int $id_country
     * @param int $id_group
     * @return boolean
     */
    public function combineSpecificPrice(array $customer_ids, $target_customer_id, $id_currency, $id_country, $id_group)
    {
        $customer_ids_string = implode(',', $customer_ids);
        $specific_price = 'UPDATE `' . _DB_PREFIX_ . 'specific_price`
                                        SET
                                            `id_customer` = ' . (int) $target_customer_id . ',
                                            `id_currency` = ' . (int) $id_currency . ',
                                            `id_country` = ' . (int) $id_country . ',
                                            `id_group` = ' . (int) $id_group . '
					WHERE `id_customer` IN (' . pSQL($customer_ids_string) . ')';

        return Db::getInstance()->execute($specific_price);
    }

    /**
     * update id_customer of compare to target customer
     * @param array $customer_ids
     * @param int $target_customer_id
     * @return boolean
     */
    public function combineCompareProduct(array $customer_ids, $target_customer_id)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            return true;
        }
        $customer_ids_string = implode(',', $customer_ids);
        $compare = 'UPDATE `' . _DB_PREFIX_ . 'compare` SET `id_customer` = ' . (int) $target_customer_id . '
					WHERE `id_customer` IN (' . pSQL($customer_ids_string) . ')';

        return Db::getInstance()->execute($compare);
    }

    /**
     * update id_customer of cart rule to target customer
     * @param array $customer_ids
     * @param int $target_customer_id
     * @param int $id_group
     * @return boolean
     */
    public function combineCartRule(array $customer_ids, $target_customer_id, $id_group)
    {
        $this->insertGroupCartRule($customer_ids, $id_group);
        $customer_ids_string = implode(',', $customer_ids);
        $cart_rule = 'UPDATE `' . _DB_PREFIX_ . 'cart_rule` SET `id_customer` = ' . (int) $target_customer_id . '
					WHERE `id_customer` IN (' . pSQL($customer_ids_string) . ')';

        return Db::getInstance()->execute($cart_rule);
    }

    /**
     * insert cart rule group if not exist
     * @param array $customer_ids
     * @param int $id_group
     */
    protected function insertGroupCartRule(array $customer_ids, $id_group)
    {
        foreach ($customer_ids as $customer_id) {
            $cart_rule_ids = $this->getIdCartRuleByIdCustomer($customer_id);
            if (!empty($cart_rule_ids)) {
                foreach ($cart_rule_ids as $cart_rule_id) {
                    if (!$this->checkExistGroupCartRule($cart_rule_id['id_cart_rule'], $id_group)) {
                        $sql = 'REPLACE INTO
                                    `' . _DB_PREFIX_ . 'cart_rule_group` (`id_cart_rule`, `id_group`) VALUES
                                    (' . (int)$cart_rule_id['id_cart_rule'] . ', ' . (int) $id_group . ')
                                ';
                        Db::getInstance()->execute($sql);
                    }
                }
            }
        }
    }

    /**
     * get id cart rule by id customer
     * @param int $id_customer
     * @return array
     */
    protected function getIdCartRuleByIdCustomer($id_customer)
    {
        $sql = 'SELECT
                    cr.`id_cart_rule`
                    FROM `' . _DB_PREFIX_ . 'cart_rule` cr
                    WHERE
                        cr.`id_customer` = ' . (int) $id_customer . '
                        AND cr.`group_restriction` != 0';
        return Db::getInstance()->executeS($sql);
    }

    /**
     * check cart rule existed id group
     * @param int $cart_rule_id
     * @param int $id_group
     * @return boolean
     */
    protected function checkExistGroupCartRule($cart_rule_id, $id_group)
    {
        $exist = false;
        $sql = 'SELECT
                    crg.`id_cart_rule`
                    FROM `' . _DB_PREFIX_ . 'cart_rule_group` crg
                        LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr
                            ON (cr.`id_cart_rule` = crg.`id_cart_rule`)
                    WHERE
                        cr.`id_cart_rule` = ' . (int) $cart_rule_id . '
                        AND crg.`id_group` = ' . (int) $id_group;
        $results = Db::getInstance()->executeS($sql);
        if (!empty($results)) {
            $exist = true;
        }
        return $exist;
    }
}
