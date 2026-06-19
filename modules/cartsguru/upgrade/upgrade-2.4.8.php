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

if (!defined('_PS_VERSION_')) {
    exit();
}

// Upgrade module to 2.4.8
function upgrade_module_2_4_8($module)
{
    $existsRaw = Configuration::get(
        'CARTS_GURU_SETTINGS_RAW_ENABLED',
        false,
        $module->id_shop_group,
        $module->id_shop
    );

    $existsCatalog = Configuration::get(
        'CARTS_GURU_SETTINGS_RAW_ENABLED',
        false,
        $module->id_shop_group,
        $module->id_shop
    );

    if (false === $existsRaw && false === $existsCatalog) {
        Configuration::updateValue(
            'CARTS_GURU_SETTINGS_RAW_ENABLED',
            1,
            false,
            $module->id_shop_group,
            $module->id_shop
        );

        Configuration::updateValue(
            'CARTS_GURU_SETTINGS_PRODUCT_CATALOG_ENABLED',
            1,
            false,
            $module->id_shop_group,
            $module->id_shop
        );
    }

    return true;
}
