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
class RegisterPlugin implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    private $helper;

    private $contextLink;

    public function __construct()
    {
        $this->helper = new CGHelper();
        $this->contextLink = new Link();
    }

    public function execute($context)
    {
        Module::getInstanceByName('cartsguru')->log('RegisterPlugin');

        $base = Configuration::get('CARTS_GURU_API_URL', false, $context['idShopGroup'], $context['idShop']);

        if (!$base) {
            $base = CartsGuru\CartsGuru\Shared\Constants::$apiUrl;
        }

        $pattern = $base . 'prestashop/:siteId/hooks/register';
        $url = str_replace(':siteId', $context['siteId'], $pattern);
        Module::getInstanceByName('cartsguru')->log('RegisterPlugin - Url: ' . $url);

        $adminUrl = $this->contextLink->getModuleLink('cartsguru', 'admin', [], null, null, $context['idShop']);

        $params = [
            'plugin' => 'prestashop',
            'pluginVersion' => Module::getInstanceByName('cartsguru')->version,
            'storeVersion' => _PS_VERSION_,
            'adminUrl' => $adminUrl,
        ];

        $auth = $this->helper->getHmac($context['siteId'], $context['authKey']);
        $url .= '?auth=' . $auth;

        Module::getInstanceByName('cartsguru')->log('RegisterPlugin - Url with Auth: ' . $url);

        $jsonParams = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $this->helper->postSync($url, $jsonParams);
    }
}
