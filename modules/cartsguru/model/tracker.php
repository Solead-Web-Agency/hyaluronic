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
class CGTracker
{
    private $helper;

    private $context;

    private $data;

    /**
     * Contructor.
     *
     * @param CGHelper $helper
     * @param Context $context
     */
    public function __construct(CGHelper $helper, Context $context)
    {
        $this->helper = $helper;
        $this->context = $context;
        $this->data = $this->renderContent();

        $this->loadData();
    }

    /**
     * Reload the Cart & Order variables when it is needed.
     */
    public function loadData()
    {
        $var = [];

        $convertedCart = (isset($this->context->cart->id))
            ? $this->getConvertedCart()
            : null;

        $accountId = null;
        if (isset($convertedCart) && isset($convertedCart['accountId'])) {
            $accountId = $convertedCart['accountId'];
        }

        if (!$accountId && isset($this->context->customer) && isset($this->context->customer->email)) {
            $accountId = $this->context->customer->email;
        }

        if ($accountId) {
            $var['data']['accountId'] = $this->helper->base64Encode(
                $accountId
            );
        }

        if ($convertedCart) {
            $var['data']['cart'] = $this->helper->base64Encode(
                json_encode(
                    $convertedCart,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }

        $convertedOrder = $this->getConvertedOrder();
        if ($convertedOrder) {
            $var['data']['order'] = $this->helper->base64Encode(
                json_encode(
                    $convertedOrder,
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }

        $this->data->assign($var);
    }

    /**
     * Return data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Depending of the PS Version, it returns the content of Smarty.
     *
     * @return array
     */
    private function renderContent()
    {
        if (version_compare(_PS_VERSION_, '1.7.3', '>')) {
            return $this->context->smarty->createData($this->context->smarty);
        } else {
            return $this->context->smarty->createData();
        }
    }

    /**
     * Get converted cart if it is available.
     *
     * @return array
     */
    private function getConvertedCart()
    {
        $cartId = $this->context->cart->id;
        if (!isset($cartId)) {
            return null;
        }

        $cgCart = new CGCart($this->context);
        $cart = new Cart($cartId);

        return $cgCart->getCart($cart, true);
    }

    /**
     * Get converted order if it is available.
     *
     * @param Context $context
     *
     * @return array
     */
    private function getConvertedOrder()
    {
        $email = $this->context->cookie->email;
        $shopId = (int) $this->context->shop->id;

        if ($email) {
            $lastOrderId = $this->helper->getLastOrderId($email, $shopId);
            if ($lastOrderId) {
                $cgOrder = new CGOrder();
                $order = new Order($lastOrderId);

                return $this->helper->base64Encode(
                    json_encode(
                        $cgOrder->getOrder($order),
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    )
                );
            }
        }

        return null;
    }
}
