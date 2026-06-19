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
class CGCustomer extends Customer
{
    const TYPE_CIVILITY_MALE_ID = 1;
    const TYPE_CIVILITY_FEMALE_ID = 2;

    const TYPE_CIVILITY_MALE_CODE = 'mister';
    const TYPE_CIVILITY_FEMALE_CODE = 'madam';

    public $civility;
    public $isNewCustomer;
    public $customerGroupName;
    public $browserLanguage;

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->civility = $this->getCivility();
        $this->isNewCustomer = $this->isNewCustomer();
        $this->customerGroupName = $this->getCustomerGroupName();
        $this->browserLanguage = $this->getBrowserLanguage();
    }

    public function getCivility()
    {
        switch ($this->id_gender) {
            case self::TYPE_CIVILITY_MALE_ID:
                return self::TYPE_CIVILITY_MALE_CODE;
            case self::TYPE_CIVILITY_FEMALE_ID:
                return self::TYPE_CIVILITY_FEMALE_CODE;
            default:
                return '';
        }
    }

    public function isNewCustomer($orderId = null)
    {
        $idShop = (int) $this->id_shop;
        $email = $this->email;

        $sql = 'SELECT o.id_order AS id FROM ' . _DB_PREFIX_ . 'orders o ';
        $sql .= 'JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer ';
        $sql .= "WHERE c.email = '" . pSQL($email) . "' and o.id_shop = " . $idShop;

        if ($orderId) {
            $sql .= ' and o.id_order <> ' . $orderId;
        }

        $orders = Db::getInstance()->ExecuteS($sql);

        return 0 == count($orders);
    }

    public function getCustomerGroupName()
    {
        $customerGroupNames = [];

        if ($this->id) {
            $customerGroups = $this->getWsGroups();
            foreach ($customerGroups as $id) {
                $group = new Group($id['id']);
                if ($group) {
                    array_push($customerGroupNames, $group->name[$this->id_lang]);
                }
            }

            return implode(',', $customerGroupNames);
        }

        return null;
    }

    public function getBrowserLanguage()
    {
        $language = array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)
                    ? $_SERVER['HTTP_ACCEPT_LANGUAGE']
                    : null;

        if (!isset($language)) {
            return null;
        }

        $langs = [];
        $quality = [];
        foreach (explode(',', Tools::strtolower($language)) as $accept) {
            if (preg_match('!([a-z-]+)(;q=([0-9\\.]+))?!', trim($accept), $found)) {
                $langs[] = $found[1];
                $quality[] = (isset($found[3]) ? (float) $found[3] : 1.0);
            }
        }

        array_multisort($quality, SORT_NUMERIC, SORT_DESC, $langs);

        foreach ($langs as $lang) {
            $lang = Tools::substr($lang, 0, 2);

            return $lang;
        }

        return null;
    }
}
