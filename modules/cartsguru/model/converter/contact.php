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
class CGContact extends CGAbstractBase
{
    /**
     * Convert an input (Customer, Address or Email) in an array
     * with all the fields that we need to export to Carts Guru.
     *
     * @param mixed $object
     *
     * @return array
     */
    public function getContact($object)
    {
        Module::getInstanceByName('cartsguru')->log('getContact');

        if ($this->isCustomer($object)) {
            $customer = new CGCustomer($object->id);
            $addresses = $customer->getAddresses($customer->id_lang);

            $address = null;
            if (count($addresses)) {
                $idAddress = end($addresses)['id_address'];
                $address = new Address($idAddress);
            }
        } elseif ($this->isAddress($object)) {
            $address = $object;
            $customer = new CGCustomer($object->id_customer);
            $addresses = $customer->getAddresses($customer->id_lang);
        } elseif ($this->isNewsletter($object)) {
            $email = $object['email'];
            $customer = new CGCustomer();
            $customer->email = $email;
            $customer->newsletter = true;
            $customer->id_lang = $object['id_lang'];
            $address = $addresses = null;
        } else {
            throw new \LogicException('Non-existent data type.');
        }

        $accountId = $customer->email;
        $civility = $customer->civility;
        $lastname = $customer->lastname;
        $firstname = $customer->firstname;
        $email = $customer->email;
        $homePhoneNumber = ($address && isset($address->phone))
                            ? $address->phone
                            : '';
        $mobilePhoneNumber = ($address && isset($address->phone_mobile))
                              ? $address->phone_mobile
                              : '';
        $countryCode = ($address && isset($address->id_country))
                        ? Country::getIsoById($address->id_country)
                        : '';
        $country = ($address && isset($address->id_country))
                    ? Country::getNameById($customer->id_lang, $address->id_country)
                    : '';
        $birthDate = (isset($customer->birthday) && ('0000-00-00' != $customer->birthday))
                    ? date('Y-m-d', strtotime($customer->birthday))
                    : '';
        $storeLanguage = Language::getLanguage($customer->id_lang)['iso_code'];
        $buyerAcceptsMarketing = $customer->optin;

        $browserLanguage = $customer->browserLanguage;
        $customerGroupName = $customer->customerGroupName;

        // Default Custom Fields
        $custom = [
            'language' => isset($browserLanguage) ? $browserLanguage : '',
            'customerGroup' => isset($customerGroupName) ? $customerGroupName : '',
        ];

        $params = [
            'accountId' => ($accountId) ? $accountId : '',
            'civility' => ($civility) ? $civility : '',
            'lastname' => ($lastname) ? $lastname : '',
            'firstname' => ($firstname) ? $firstname : '',
            'email' => ($email) ? $email : '',
            'homePhoneNumber' => ($homePhoneNumber) ? $homePhoneNumber : '',
            'mobilePhoneNumber' => ($mobilePhoneNumber) ? $mobilePhoneNumber : '',
            'countryCode' => ($countryCode) ? $countryCode : '',
            'country' => ($country) ? $country : '',
            'birthDate' => ($birthDate) ? $birthDate : '',
            'language' => ($storeLanguage) ? $storeLanguage : '',
            'storeLanguage' => ($storeLanguage) ? $storeLanguage : '',
            'browserLanguage' => ($browserLanguage) ? $browserLanguage : '',
            'buyerAcceptsMarketing' => ($buyerAcceptsMarketing) ? 'true' : 'false',
            'custom' => $custom,
        ];

        if ($this->helper->isRawEnabled()) {
            $raw = $this->getRaw($customer, $addresses);
            if (isset($raw)) {
                $params['raw'] = $raw;
            }
        }

        return $params;
    }

    /**
     * Import all Contacts from customers table.
     *
     * @param string $since
     * @param int $limit
     * @param int $page
     *
     * @return array
     */
    public function getImportContacts($since, $limit, $page = 1)
    {
        $countSubscribers = $this->getSubscribersCount($since);
        $countCustomers = $this->getCustomersCount($since);
        $count = $countSubscribers + $countCustomers;

        $data = [];

        if (Module::isInstalled('ps_emailsubscription')
            && version_compare(Module::getInstanceByName('ps_emailsubscription')->version, '2.6.0', '>=')
            && count($data) != $limit) {
            $subscribers = $this->getSubscribers($since, $limit, $page, $countCustomers);

            if ($subscribers) {
                foreach ($subscribers as $item) {
                    $newsletter = ['email' => $item['email'], 'id_lang' => $item['id_lang']];
                    $data[] = $this->getContact($newsletter);
                }
            }
        }

        $customers = $this->getCustomers($since, $limit, $page);
        if ($customers) {
            foreach ($customers as $item) {
                $customer = new Customer($item['id_customer']);
                $data[] = $this->getContact($customer);
            }
        }

        return [
            'result' => [
                'dataType' => 'contact',
                'count' => $count,
                'values' => $data,
            ],
        ];
    }

    /**
     * Get the number of customers since a date.
     *
     * @param string $since
     *
     * @return int
     */
    private function getCustomersCount($since)
    {
        $sqlCount = 'SELECT
                count(`c`.`id_customer`) as `count`
            FROM ' . _DB_PREFIX_ . 'customer `c`
            ' . $this->getWhereCustomer($since) . ';';

        return \Db::getInstance()->getValue($sqlCount);
    }

    /**
     * Get the number of email subscribers since a date.
     *
     * @param string $since
     *
     * @return int
     */
    private function getSubscribersCount($since)
    {
        if (Module::isInstalled('ps_emailsubscription')
            && version_compare(Module::getInstanceByName('ps_emailsubscription')->version, '2.6.0', '>=')) {
            $sqlCount = 'SELECT
                    count(`es`.`id`) as `count`
                FROM ' . _DB_PREFIX_ . 'emailsubscription `es`
                ' . $this->getWhereSubscriber($since) . ';';

            return \Db::getInstance()->getValue($sqlCount);
        } else {
            return 0;
        }
    }

    /**
     * Get the customers since a date and pagination.
     *
     * @param string $since
     *
     * @return int
     */
    private function getCustomers($since, $limit, $page)
    {
        $offset = ($page - 1) * $limit;

        $sql = 'SELECT
                `c`.`id_customer` as `id_customer`
            FROM ' . _DB_PREFIX_ . 'customer `c`
            ' . $this->getWhereCustomer($since) . '
            ORDER BY `c`.`id_customer` ASC
            LIMIT ' . $offset . ', ' . $limit . ';';

        return \Db::getInstance()->executes($sql);
    }

    /**
     * Get the subscribers since a date and pagination.
     *
     * @param string $since
     *
     * @return int
     */
    private function getSubscribers($since, $limit, $page, $numCustomers = 0)
    {
        $offset = ($page - 1) * $limit - $numCustomers;

        if ($offset < 0) {
            $limit = $limit + $offset;
            $offset = 0;
        }

        $sql = 'SELECT
                `es`.`email` as `email`,
                `es`.`id_lang` as `id_lang`
            FROM ' . _DB_PREFIX_ . 'emailsubscription `es`
            ' . $this->getWhereSubscriber($since) . '
            ORDER BY `es`.`id` ASC
            LIMIT ' . $offset . ', ' . $limit . ';';

        return \Db::getInstance()->executes($sql);
    }

    /**
     * Generate the 'where' clause for customer table.
     *
     * @param string $since
     *
     * @return string
     */
    private function getWhereCustomer($since)
    {
        $where = ($since) ? 'WHERE `c`.`date_add` >= \'' . $since . '\'' : null;

        if (\Shop::isFeatureActive()) {
            $id_shop_group = (int) \Shop::getContextShopGroupID();
            $id_shop = \Context::getContext()->shop->id;

            $where .= (is_null($where)) ?
                        'WHERE `c`.`id_shop_group` = ' . $id_shop_group . ' AND `c`.`id_shop` = ' . $id_shop :
                        ' AND `c`.`id_shop_group` = ' . $id_shop_group . ' AND `c`.`id_shop` = ' . $id_shop;
        }

        return $where;
    }

    /**
     * Generate the 'where' clause for emailsubscriber table.
     *
     * @param string $since
     *
     * @return string
     */
    private function getWhereSubscriber($since)
    {
        $where = ($since) ? 'WHERE `es`.`newsletter_date_add` >= \'' . $since . '\'' : null;

        if (\Shop::isFeatureActive()) {
            $id_shop_group = (int) \Shop::getContextShopGroupID();
            $id_shop = \Context::getContext()->shop->id;

            $where .= (is_null($where)) ?
                        'WHERE `es`.`id_shop_group` = ' . $id_shop_group . ' AND `es`.`id_shop` = ' . $id_shop :
                        ' AND `es`.`id_shop_group` = ' . $id_shop_group . ' AND `es`.`id_shop` = ' . $id_shop;
        }

        $where .= (is_null($where)) ? 'WHERE `es`.`active` = 1' : ' AND `es`.`active` = 1';

        return $where;
    }

    /**
     * Check if the next table exists.
     *
     * @param string $since
     * @param int $limit
     * @param int $page
     *
     * @return bool
     */
    public function existsNextPage($since, $limit, $page = 1)
    {
        $where = ($since) ? 'WHERE `c`.`date_add` >= \'' . $since . '\'' : null;

        if (\Shop::isFeatureActive()) {
            $id_shop_group = (int) \Shop::getContextShopGroupID();
            $id_shop = \Context::getContext()->shop->id;

            $where .= (is_null($where)) ?
                        'WHERE `c`.`id_shop_group` = ' . $id_shop_group . ' AND `c`.`id_shop` = ' . $id_shop :
                        ' AND `c`.`id_shop_group` = ' . $id_shop_group . ' AND `c`.`id_shop` = ' . $id_shop;
        }

        $offset = $page * $limit;

        $sql = 'SELECT COUNT(`t`.`id_customer`) as `result`
                FROM (
                    SELECT
                        `c`.`id_customer`
                    FROM ' . _DB_PREFIX_ . 'customer `c`
                    ' . $where . '
                    LIMIT ' . $offset . ', ' . $limit . '
                ) AS `t`;';

        $numRows = \Db::getInstance()->executeS($sql);

        return (int) $numRows[0]['result'] > 0;
    }

    /**
     * Get raw data from Customer and Addresses.
     *
     * @param CGCustomer $customer
     * @param array $addresses
     *
     * @return array
     */
    private function getRaw($customer, $addresses)
    {
        foreach ($customer as $key => $value) {
            if (preg_match('/passwd|password/i', $key)) {
                unset($customer->{$key});
            }
        }
        unset($customer->secure_key);

        return ['customer' => $customer, 'addresses' => $addresses];
    }

    /**
     * Check if the input is a Customer.
     *
     * @param $object
     *
     * @return bool
     */
    private function isCustomer($object)
    {
        return $object instanceof Customer;
    }

    /**
     * Check if the input is a Address.
     *
     * @param $object
     *
     * @return bool
     */
    private function isAddress($object)
    {
        return $object instanceof Address;
    }

    /**
     * Check if the input is an Email.
     *
     * @param $object
     *
     * @return bool
     */
    private function isNewsletter($object)
    {
        return is_array($object) && array_keys($object) === ['email', 'id_lang'];
    }
}
