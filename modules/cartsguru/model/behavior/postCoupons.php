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
class PostCoupons implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    public function execute($context)
    {
        Module::getInstanceByName('cartsguru')->log('PostCoupons');

        if (isset($context->coupon->title)) {
            $title = $context->coupon->title;
        } else {
            throw new LogicException('The field title is required in post call.');
        }

        if (isset($context->coupon->freeShipping) || isset($context->coupon->reductionPercent) || isset($context->coupon->reductionAmount)) {
            $freeShipping = (isset($context->coupon->freeShipping) && $context->coupon->freeShipping) ? 1 : 0;

            $reductionPercent = isset($context->coupon->reductionPercent) ? (float) ($context->coupon->reductionPercent) : 0;
            $reductionAmount = isset($context->coupon->reductionAmount) ? (float) ($context->coupon->reductionAmount) : 0;
            $minCartvalue = (isset($context->coupon->minCartValue)) ? (float) ($context->coupon->minCartValue) : 0;
        } else {
            throw new \LogicException('The fields freeShipping or reductionPercent or reductionAmount are required in post call.');
        }

        if (isset($context->coupon->code)) {
            $code = $context->coupon->code;
        } else {
            throw new LogicException('The field code is required in post call.');
        }

        if ($reductionAmount) {
            if (isset($context->coupon->tax)) {
                $tax = isset($context->coupon->tax) ? (int) ($context->coupon->tax) : 0;
            } else {
                throw new LogicException('The field tax is required in a post call with fixed amount coupons.');
            }
        }

        if (isset($context->coupon->sendingStartDate)) {
            $sendingStartDate = new DateTime($context->coupon->sendingStartDate, new DateTimeZone('UTC'));
        } else {
            throw new LogicException('The field sendingStartDate is required in post call.');
        }

        if (isset($context->coupon->expirationDate)) {
            $expirationDate = new DateTime($context->coupon->expirationDate, new DateTimeZone('UTC'));
        } else {
            throw new LogicException('The field expirationDate is required in post call.');
        }

        $cartrule = new CartRule();
        if ($cartrule->cartRuleExists($code)) {
            throw new LogicException('There is already a coupon with this code.');
        }

        $id_shop_group = (int) Shop::getContextShopGroupID();
        $id_shop = (int) Shop::getContextShopID();

        $cartrule = new CartRule();
        $cartrule->date_from = $sendingStartDate->format('Y-m-d H:i:s');
        $cartrule->date_to = $expirationDate->format('Y-m-d H:i:s');
        $cartrule->description = 'Carts Guru generated rule';
        $cartrule->quantity = 100000;
        $cartrule->quantity_per_user = 1;
        $cartrule->priority = 1;
        $cartrule->partial_use = false;
        $cartrule->code = $code;
        $cartrule->minimum_amount = $minCartvalue;
        $cartrule->minimum_amount_tax = 1;
        if ($freeShipping) {
            $cartrule->free_shipping = true;
        }

        if ($reductionPercent) {
            $cartrule->reduction_percent = $reductionPercent;
        }

        if ($reductionAmount) {
            $cartrule->reduction_amount = $reductionAmount;
            $cartrule->reduction_tax = $tax;
            $cartrule->reduction_currency = Configuration::get('PS_CURRENCY_DEFAULT', null, $id_shop_group, $id_shop);
        }

        $cartrule->cart_rule_restriction = true; // Coupon is not combinable
        $cartrule->shop_restriction = true;

        $cartrule->highlight = false;
        $cartrule->active = true;

        /* Lang fields */
        $cartrule->name = [
            (int) Configuration::get('PS_LANG_DEFAULT', null, $id_shop_group, $id_shop) => $title,
        ];

        if ($cartrule->add()) {
            // Add shop restriction
            $shop_id = Context::getContext()->shop->id;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'cart_rule_shop` (`id_cart_rule`,`id_shop`) ';
            $sql .= 'VALUES (' . (int) $cartrule->id . ',' . (int) $shop_id . ')  ';
            Db::getInstance()->execute($sql);
        }
    }
}
