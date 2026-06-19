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

/**
 * GetCoupons Behavior.
 */
class GetCoupons implements CartsGuru\CartsGuru\Shared\BehaviorInterface
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
        Module::getInstanceByName('cartsguru')->log('GetCoupons');

        if (!$context) {
            return false;
        }

        $idShop = (int) Context::getContext()->shop->id;
        $idLang = (int) Context::getContext()->language->id;

        $sql = 'SELECT cr.id_cart_rule as id FROM  `' . _DB_PREFIX_ . 'cart_rule` cr ' .
             'LEFT OUTER JOIN `' . _DB_PREFIX_ . 'cart_rule_shop` crs on cr.id_cart_rule = crs.id_cart_rule ' .
             'WHERE cr.description = \'Carts Guru generated rule\' AND cr.active = 1 ' .
             'AND (cr.id_customer is null OR cr.id_customer = 0)' .
             'AND (shop_restriction = 0 OR crs.id_shop = ' . (int) $idShop . ') ';

        $result = [];
        $result['coupons'] = [];
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                $item = new CartRule($row['id']);

                $coupon = [
                    'title' => $item->name[$idLang],
                    'freeShipping' => (bool) $item->free_shipping,
                    'code' => (string) $item->code,
                    'sendingStartDate' => date(CartsGuru::CARTSGURU_DATE_FORMAT, strtotime($item->date_from)),
                    'expirationDate' => date(CartsGuru::CARTSGURU_DATE_FORMAT, strtotime($item->date_to)),
                ];

                if (isset($item->reduction_percent) && (int) $item->reduction_percent) {
                    $coupon['reductionPercent'] = (float) $item->reduction_percent;
                }

                if (isset($item->reduction_amount) && (int) $item->reduction_amount) {
                    $coupon['reductionAmount'] = (float) $item->reduction_amount;
                    if (isset($item->reduction_tax)) {
                        $coupon['tax'] = (float) $item->reduction_tax;
                    }
                }

                $result['coupons'][] = $coupon;
            }
        }

        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
