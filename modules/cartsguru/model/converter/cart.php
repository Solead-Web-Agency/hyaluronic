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
class CGCart extends CGAbstractSales
{
    public function __construct($context = null)
    {
        parent::__construct($context);
    }

    public function getCart(Cart $cart, $withoutEmail = false)
    {
        // Module::getInstanceByName('cartsguru')->log('getCart');

        if (!$this->context->cart) {
            $this->context->cart = $cart;
            $this->context->currency = new Currency($cart->id_currency);
        }

        $items = $this->getItemsData($cart);

        if (!count($items)) {
            return false;
        }

        $customer = new CGCustomer((int) $cart->id_customer);

        $address = new Address((int) $cart->id_address_invoice);

        $email = null;
        if (isset($customer->id)) {
            $email = $customer->email;
            if (!$email && isset($address->email)) {
                $email = $address->email;
            }
        }

        if (!$withoutEmail && !$email) {
            return false;
        }

        $id = (string) $cart->id;

        $totalATI = (float) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $totalET = (float) $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);

        $currencyObj = new Currency((int) $cart->id_currency);
        $currency = (string) $currencyObj->iso_code;

        $accountId = $email;

        $ip = isset($_SERVER['HTTP_CF_CONNECTING_IP'])
                ? $_SERVER['HTTP_CF_CONNECTING_IP']
                : Tools::getRemoteAddr();

        $recoverUrl = $this->getRecoverUrl($cart);

        $civility = $customer->civility;

        $lastname = ($customer->lastname)
                ? $customer->lastname
                : $address->lastname;

        $firstname = ($customer->firstname)
                ? $customer->firstname
                : $address->firstname;

        $homePhoneNumber = ($address && $address->phone) ? $address->phone : null;
        $mobilePhoneNumber = ($address && $address->phone_mobile) ? $address->phone_mobile : null;

        $idLang = $cart->id_lang;

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
            'totalATI' => ($totalATI) ? $totalATI : 0,
            'totalET' => ($totalET) ? $totalET : 0,
            'currency' => ($currency) ? $currency : '',
            'accountId' => ($accountId) ? $accountId : '',
            'ip' => ($ip) ? $ip : '',
            'recoverUrl' => ($recoverUrl) ? $recoverUrl : '',
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

            $raw = ['cart' => $cart, 'customer' => $rawCustomer, 'address' => $address];
            if (isset($raw)) {
                $params['raw'] = $raw;
            }
        }

        return $params;
    }

    /**
     * Get Items from a cart.
     *
     * @param object $object
     *
     * @return array
     */
    protected function getItems($object)
    {
        return $object->getProducts(true);
    }

    /**
     * Get Items content from a cart.
     *
     * @param array $item
     * @param int $idLang
     *
     * @return array
     */
    protected function getItemData($item, $idLang)
    {
        $product = new Product($item['id_product'], false, $idLang);

        $idProductAttribute = 0;
        $label = $product->name;
        if (isset($item['id_product_attribute'])) {
            $idProductAttribute = (int) $item['id_product_attribute'];
        }

        return [
            'idProductAttribute' => $idProductAttribute,
            'label' => $label,
            'quantity' => (int) $item['cart_quantity'],
            'totalET' => (float) $item['total'],
            'totalATI' => (float) $item['total_wt'],
        ];
    }

    private function getRecoverUrl(Cart $cart)
    {
        $authKey = Configuration::get('CARTS_GURU_SETTINGS_AUTH_KEY', null, $cart->id_shop_group, $cart->id_shop);

        $params = [
            'cart_id' => (int) $cart->id,
            'cart_token' => $this->helper->getCartToken($cart->id, $authKey),
        ];

        return $this->link->getModuleLink('cartsguru', 'cartrecover', $params, null, null, $cart->id_shop);
    }
}
