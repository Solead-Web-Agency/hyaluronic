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
class GetHooks implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    /**
     * Execute behavior. When we receive a call from CG API.
     *
     * @param array $context
     *
     * @return void
     */
    public function execute($context)
    {
        Module::getInstanceByName('cartsguru')->log('GetHooks');

        if (!$context) {
            return false;
        }

        $hooks = [
            'cart' => 'CARTS_GURU_HOOKS_CART_URL',
            'contact' => 'CARTS_GURU_HOOKS_CONTACT_URL',
            'order' => 'CARTS_GURU_HOOKS_ORDER_URL',
        ];

        $idShop = Module::getInstanceByName('cartsguru')->getIdShop();
        $idShopGroup = Module::getInstanceByName('cartsguru')->getIdShopGroup();

        $result = [];
        $result['hooks'] = [];
        foreach ($hooks as $key => $value) {
            $content = Configuration::get($value, false, $idShopGroup, $idShop);

            if ($content) {
                $result['hooks'][] = [
                    'target' => $key,
                    'url' => $content,
                ];
            }
        }

        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
