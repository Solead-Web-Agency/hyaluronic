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
abstract class CGAbstractBase
{
    protected $context;

    protected $link;

    protected $helper;

    public function __construct($context = null)
    {
        $this->context = ($context) ? $context : Context::getContext();

        $protocolLink = Configuration::get(
            'PS_SSL_ENABLED',
            null,
            (int) Shop::getContextShopGroupID(),
            (int) Shop::getContextShopID()
        ) ? 'https://' : 'http://';

        $useSsl = Configuration::get('PS_SSL_ENABLED') ? true : false;
        $protocolContent = ($useSsl) ? 'https://' : 'http://';
        $this->link = new Link($protocolLink, $protocolContent);

        $this->helper = new CGHelper();
    }

    protected function getBrowserLanguage()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return null;
        }

        $langs = [];
        $quality = [];
        $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
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

    protected function getCustomerGroupName($customerGroups, $language)
    {
        $customerGroupNames = [];
        foreach ($customerGroups as $id) {
            $group = new Group((int) $id);
            if ($group) {
                array_push($customerGroupNames, $group->name[(int) $language->id]);
            }
        }

        return $customerGroupNames;
    }

    protected function getCivility($customer)
    {
        $genderName = '';
        if ((int) $customer->id_gender) {
            $gender = new Gender((int) $customer->id_gender, $customer->id_lang);
            if (1 == (int) $gender->id_gender) {
                $genderName = 'mister';
            } elseif (2 == (int) $gender->id_gender) {
                $genderName = 'madam';
            }
        }

        return $genderName;
    }
}
