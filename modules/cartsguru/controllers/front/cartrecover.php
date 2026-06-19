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
class CartsGuruCartrecoverModuleFrontController extends ModuleFrontController
{
    private $helper;

    public function __construct()
    {
        $this->helper = new CGHelper();
        parent::__construct();
    }

    public function display()
    {
        // Get query values
        $id = (int) Tools::getValue('cart_id');
        $discount = Tools::getValue('cart_discount');

        // Build new query
        $queryParams = $this->getOtherParams();
        $queryParams[] = 'recover_cart=' . $id;
        $queryParams[] = 'token_cart=' . md5(_COOKIE_KEY_ . 'recover_cart_' . $id);

        $idShopGroup = (int) Shop::getContextShopGroupID();
        $idShop = (int) Shop::getContextShopID();

        $authKey = Configuration::get('CARTS_GURU_SETTINGS_AUTH_KEY', null, $idShopGroup, $idShop);
        if (Tools::getValue('cart_token') != $this->helper->getCartToken($id, $authKey)) {
            Tools::redirect('/');
        }

        $cart = null;

        // We need remove other discount if new one must be applied.
        if ($discount && $discount != '') {
            // Get cart object
            $cart = new Cart((int) $id);
            if (Validate::isLoadedObject($cart)) {
                $cartRuleId = (int) CartRule::getIdByCode($discount);
                $cartRules = $cart->getCartRules();
                // Remove cart rule
                $isRuleInCart = false;
                if (count($cartRules)) {
                    foreach ($cartRules as $cartRule) {
                        // Don't remove the one we want add
                        if ($cartRule['id_cart_rule'] != $cartRuleId) {
                            $cart->removeCartRule($cartRule['id_cart_rule']);
                        } else {
                            $isRuleInCart = true;
                        }
                    }
                }
                // Add cart rule
                if (!$isRuleInCart) {
                    $cart->addCartRule($cartRuleId);
                }
            }
        }

        $cart = isset($cart) ? $cart : new Cart((int) $id);

        // If its not identified cart need to set the cart cookie
        if (Validate::isLoadedObject($cart)) {
            $customer = new Customer((int) $cart->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                $this->context->cookie->id_cart = (int) $id;
            }
        }

        // Get recover URL
        $controller = Configuration::get('PS_ORDER_PROCESS_TYPE')
                      && class_exists('OrderOpcController');
        $url = $this->context->link->getPageLink(
            $controller ? 'order-opc' : 'order',
            true,
            null,
            implode('&', $queryParams)
        );

        Tools::redirect($url);

        return true;
    }

    private function getOtherParams()
    {
        $queryParams = [];
        $excluded_keys = ['cart_id', 'cart_token', 'cart_discount', 'fc', 'module', 'controller'];
        $url = parse_url($_SERVER['REQUEST_URI']);
        $query = (isset($url['query'])) ? $url['query'] : '';
        parse_str($query, $params);

        foreach ($params as $key => $value) {
            // Keep params except $excluded
            if (in_array($key, $excluded_keys)) {
                continue;
            }
            $queryParams[] = $key . '=' . $value;
        }

        return $queryParams;
    }
}
