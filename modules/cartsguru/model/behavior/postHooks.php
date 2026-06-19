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
class PostHooks implements CartsGuru\CartsGuru\Shared\BehaviorInterface
{
    public function execute($context)
    {
        Module::getInstanceByName('cartsguru')->log('PostHooks');

        if (isset($context->hook->target)) {
            $target = $context->hook->target;
        } else {
            throw new LogicException('The field target is required in post call.');
        }

        if (isset($context->hook->url)) {
            $url = $context->hook->url;
        } else {
            throw new LogicException('The field url is required in post call.');
        }

        switch ($target) {
            case 'cart':
                Module::getInstanceByName('cartsguru')->registerCartsHooks($url);
                break;
            case 'contact':
                Module::getInstanceByName('cartsguru')->registerContactsHooks($url);
                break;
            case 'order':
                Module::getInstanceByName('cartsguru')->registerOrdersHooks($url);
                break;
            default:
                throw new LogicException('The field target content is invalid in post call.');
        }
    }
}
