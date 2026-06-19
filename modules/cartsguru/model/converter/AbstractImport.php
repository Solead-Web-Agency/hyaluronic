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

namespace CartsGuru\CartsGuru\Model\Converter;

/**
 * Trait that stores all the common methods for all the cases.
 */
trait AbstractImport
{
    /**
     * Import all Elements from this table.
     *
     * @param string $since
     * @param int $limit
     * @param int $page
     *
     * @return array
     */
    public function getImport($since, $limit = 100, $page = 1)
    {
        return [
            'result' => [
                'dataType' => $this->_dataType,
                'count' => $this->_getImportCount($since),
                'values' => $this->_getImportData($since, $limit, $page),
            ],
        ];
    }

    /**
     * Check if the next page exists for import/sync.
     *
     * @param string $since
     * @param int $limit
     * @param int $page
     *
     * @return bool
     */
    public function existsNextPage($since, $limit = 100, $page = 1)
    {
        return (0 < $page) && ($page < $this->_getLastPage($since, $limit));
    }

    /**
     * Check if the next page exists for import/sync.
     *
     * @param string $since
     * @param int $limit
     * @param int $page
     *
     * @return bool
     */
    public function existsCurrentPage($since, $limit = 100, $page = 1)
    {
        return (0 < $page) && ($page <= $this->_getLastPage($since, $limit));
    }

    /**
     * Get last page from a limit and since.
     *
     * @param string $since
     * @param int $limit
     *
     * @return bool
     */
    private function _getLastPage($since, $limit)
    {
        if (0 == $limit) {
            return 0;
        }

        $count = $this->_getImportCount($since);

        return ceil($count / $limit);
    }
}
