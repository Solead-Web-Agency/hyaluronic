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
class GetOrders implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    public function execute($context)
    {
        Module::getInstanceByName('cartsguru')->log('GetOrders');

        if (isset($context['since'])) {
            $since = pSQL($context['since']);
        } else {
            throw new LogicException('The param since is required.');
        }

        if (isset($context['limit'])) {
            $limit = pSQL($context['limit']);
        } else {
            throw new LogicException('The param limit is required.');
        }

        $context['page'] = (array_key_exists('page', $context)) ? pSQL($context['page']) : 1;

        $order = new CGOrder();
        $data = $order->getImportOrders($since, $limit, $context['page']);

        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');

        if ($order->existsNextPage($since, $limit, $context['page'])) {
            ++$context['page'];
            $idShop = (int) Context::getContext()->shop->id;
            $url = Context::getContext()->link->getModuleLink('cartsguru', 'admin', $context, null, null, $idShop);
            header('next: ' . $url);
        }

        echo json_encode($data);

        exit;
    }
}
