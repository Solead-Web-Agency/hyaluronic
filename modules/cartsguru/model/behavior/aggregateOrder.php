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
class AggregateOrder implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    private $helper;

    public function __construct()
    {
        $this->helper = new CGHelper();
    }

    public function execute($jsonOrder)
    {
        Module::getInstanceByName('cartsguru')->log('AggregateOrder');

        $idShopGroup = (int) Shop::getContextShopGroupID();
        $idShop = (int) Shop::getContextShopID();

        $url = Configuration::get('CARTS_GURU_HOOKS_ORDER_URL', null, $idShopGroup, $idShop);

        $siteId = Configuration::get('CARTS_GURU_SETTINGS_SITE_ID', null, $idShopGroup, $idShop);
        $authKey = Configuration::get('CARTS_GURU_SETTINGS_AUTH_KEY', null, $idShopGroup, $idShop);

        $auth = $this->helper->getHmac($siteId, $authKey);
        $url .= '?auth=' . $auth;

        $this->helper->postAsync($url, $jsonOrder);
    }
}
