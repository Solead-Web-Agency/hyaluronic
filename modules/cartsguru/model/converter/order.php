<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @author    Carts Guru <prestashop@carts.guru>
 * @copyright Since 2017 Carts Guru
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
class CGOrder extends CGAbstractSales
{
    public function getOrder(Order $order, $isSync = false)
    {
        // Module::getInstanceByName('cartsguru')->log('getOrder');

        // Makes sure that a currency exists
        if (!$this->context->currency) {
            $this->context->currency = new Currency($order->id_currency);
        }

        $items = $this->getItemsData($order, $isSync);

        if (!count($items)) {
            return false;
        }

        $customer = new CGCustomer((int) $order->id_customer);
        $address = new Address((int) $order->id_address_invoice);

        $id = (string) $order->id;
        $cartId = (string) $order->id_cart;
        $creationDate = date(CartsGuru::CARTSGURU_DATE_FORMAT, strtotime($order->date_add));

        $discounts = $this->getDiscounts($order);

        $productDiscountTotalET = 0;
        foreach ($discounts as $discount) {
            if (isset($discount['totalET']) && !$discount['freeShipping']) {
                $productDiscountTotalET += $discount['totalET'];
            }
        }

        $totalET = (float) $order->getTotalProductsWithoutTaxes() - $productDiscountTotalET;
        $totalATI = (float) $order->total_paid;

        $currencyObj = new Currency((int) $order->id_currency);
        $currency = (string) $currencyObj->iso_code;

        $paymentMethod = (string) $order->payment;

        $idLang = $order->id_lang;

        $state = null;
        $order_state_id = (int) $order->current_state;
        if ((int) $order_state_id) {
            $currentStatus = new OrderState((int) $order_state_id, $idLang);
            $state = $currentStatus->name;
        }

        $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP'])
                ? $_SERVER['HTTP_CF_CONNECTING_IP']
                : Tools::getRemoteAddr();

        $accountId = $customer->email;
        if (!$accountId) {
            $accountId = $address->email;
        }

        $civility = $customer->civility;

        $lastname = ($customer->lastname)
                ? $customer->lastname
                : $address->lastname;

        $firstname = ($customer->firstname)
                ? $customer->firstname
                : $address->firstname;

        $email = $customer->email;
        if (!$email) {
            $email = $address->email;
        }

        $homePhoneNumber = $address->phone;
        $mobilePhoneNumber = $address->phone_mobile;

        $country = null;
        $countryCode = null;
        if ($address && $address->id_country) {
            $country = Country::getNameById($idLang, $address->id_country);
            $countryCode = Country::getIsoById($address->id_country);
        }

        $storeLanguage = Language::getLanguage($idLang)['iso_code'];

        $buyerAcceptsMarketing = $customer->optin;

        $browserLanguage = $customer->browserLanguage;
        $customerGroupName = $customer->customerGroupName;

        // Default Custom Fields
        $custom = [
            'language' => isset($browserLanguage) ? $browserLanguage : '',
            'customerGroup' => isset($customerGroupName) ? $customerGroupName : '',
        ];

        $params = [
            'id' => ($id) ? $id : '',
            'cartId' => ($cartId) ? $cartId : '',
            'creationDate' => ($creationDate) ? $creationDate : '',
            'totalATI' => ($totalATI) ? $totalATI : '',
            'totalET' => ($totalET) ? $totalET : '',
            'currency' => ($currency) ? $currency : '',
            'paymentMethod' => ($paymentMethod) ? $paymentMethod : '',
            'state' => ($state) ? $state : '',
            'ip' => ($ip) ? $ip : '',
            'accountId' => ($accountId) ? $accountId : '',
            'civility' => ($civility) ? $civility : '',
            'lastname' => ($lastname) ? $lastname : '',
            'firstname' => ($firstname) ? $firstname : '',
            'email' => ($email) ? $email : '',
            'homePhoneNumber' => ($homePhoneNumber) ? $homePhoneNumber : '',
            'mobilePhoneNumber' => ($mobilePhoneNumber) ? $mobilePhoneNumber : '',
            'country' => ($country) ? $country : '',
            'countryCode' => ($countryCode) ? $countryCode : '',
            'language' => ($storeLanguage) ? $storeLanguage : '',
            'storeLanguage' => ($storeLanguage) ? $storeLanguage : '',
            'browserLanguage' => ($browserLanguage) ? $browserLanguage : '',
            'buyerAcceptsMarketing' => ($buyerAcceptsMarketing) ? 'true' : 'false',
            'items' => $items,
            'custom' => $custom
        ];

        if ($this->helper->isRawEnabled()) {
            $rawCustomer = $customer;

            foreach ($rawCustomer as $key => $value) {
                if (preg_match('/passwd|password/i', $key)) {
                    unset($rawCustomer->{$key});
                }
            }
            unset($rawCustomer->secure_key);

            $raw = ['order' => $order, 'customer' => $rawCustomer, 'address' => $address];
            if (isset($raw)) {
                $params['raw'] = $raw;
            }
        }

        return $params;
    }

    /**
     * Get Items from an order.
     *
     * @param object $object
     *
     * @return array
     */
    protected function getItems($object)
    {
        return $object->getProducts();
    }

    /**
     * Get Items content from an order.
     *
     * @param array $item
     * @param int $idLang
     *
     * @return array
     */
    protected function getItemData($item, $idLang = null)
    {
        $product = new Product($item['product_id'], false, $idLang);

        $idProductAttribute = 0;
        $label = $product->name;
        if (isset($item['product_attribute_id'])) {
            $idProductAttribute = (int) $item['product_attribute_id'];
        }

        return [
            'idProductAttribute' => $idProductAttribute,
            'label' => $label,
            'quantity' => (int) $item['product_quantity'],
            'totalET' => (float) $item['total_price_tax_excl'],
            'totalATI' => (float) $item['total_price_tax_incl'],
        ];
    }

    private function getDiscounts(Order $order)
    {
        $items = [];

        $orderCartRules = $order->getCartRules();
        foreach ($orderCartRules as $orderCartRule) {
            $cartRule = new CartRule($orderCartRule['id_cart_rule']);
            if (Validate::isLoadedObject($cartRule)) {
                $items[] = [
                    'totalET' => (float) $orderCartRule['value_tax_excl'],
                    'totalATI' => (float) $orderCartRule['value'],
                    'freeShipping' => (bool) $orderCartRule['free_shipping'],
                    'code' => $cartRule->code,
                ];
            }
        }

        return $items;
    }

    public function getImportOrders($since, $limit, $page = 1)
    {
        $where = ($since) ? 'WHERE `o`.`date_add` >= \'' . $since . '\'' : null;

        if (\Shop::isFeatureActive()) {
            $id_shop_group = (int) \Shop::getContextShopGroupID();
            $id_shop = \Context::getContext()->shop->id;

            $where .= (is_null($where)) ?
                        'WHERE `o`.`id_shop_group` = ' . $id_shop_group . ' AND `o`.`id_shop` = ' . $id_shop :
                        ' AND `o`.`id_shop_group` = ' . $id_shop_group . ' AND `o`.`id_shop` = ' . $id_shop;
        }

        $offset = ($page - 1) * $limit;

        $sqlCount = 'SELECT
            count(`o`.`id_order`) as `count`
        FROM ' . _DB_PREFIX_ . 'orders `o`
        ' . $where . ';';

        $count = \Db::getInstance()->getValue($sqlCount);

        $sql = 'SELECT
                    `o`.`id_order` as `id_order`
                FROM ' . _DB_PREFIX_ . 'orders `o`
                ' . $where . '
                ORDER BY `o`.`id_order` DESC
                LIMIT ' . $offset . ', ' . $limit . ';';

        $orders = \Db::getInstance()->executes($sql);

        $data = [];
        if ($orders) {
            foreach ($orders as $item) {
                $order = new Order($item['id_order']);
                $row = $this->getOrder($order, true);
                if ($row) {
                    $data[] = $row;
                }
            }
        }

        return [
            'result' => [
                'dataType' => 'order',
                'count' => $count,
                'values' => $data,
            ],
        ];
    }

    public function existsNextPage($since, $limit, $page = 1)
    {
        $where = ($since) ? 'WHERE `o`.`date_add` >= \'' . $since . '\'' : null;

        if (\Shop::isFeatureActive()) {
            $id_shop_group = (int) \Shop::getContextShopGroupID();
            $id_shop = \Context::getContext()->shop->id;

            $where .= (is_null($where)) ?
                        'WHERE `o`.`id_shop_group` = ' . $id_shop_group . ' AND `o`.`id_shop` = ' . $id_shop :
                        ' AND `o`.`id_shop_group` = ' . $id_shop_group . ' AND `o`.`id_shop` = ' . $id_shop;
        }

        $offset = $page * $limit;

        $sql = 'SELECT COUNT(`t`.`id_order`) as `result`
                FROM (
                    SELECT
                        `o`.`id_order`
                    FROM ' . _DB_PREFIX_ . 'orders `o`
                    ' . $where . '
                    LIMIT ' . $offset . ', ' . $limit . '
                ) AS `t`;';

        $numRows = \Db::getInstance()->executeS($sql);

        return (int) $numRows[0]['result'] > 0;
    }
}
