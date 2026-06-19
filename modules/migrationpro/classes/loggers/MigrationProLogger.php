<?php
 /**
 * NOTICE OF LICENSE 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    MigrationPro
 * @copyright Copyright (c) 2012-2023 MigrationPro
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @package   MigrationPro: OpenCart to PrestaShop Migrate tool
 */

require_once 'MigrationProDBErrorLogger.php';
require_once 'MigrationProDBWarningLogger.php';
require_once 'EntityTypeMapper.php';

class MigrationProLogger
{
    public function addErrorLog($log, $entityType)
    {
        MigrationProDBErrorLogger::addErrorLog($log, $entityType);
    }

    public function addWarningLog($log, $entityType)
    {
        MigrationProDBWarningLogger::addWarningLogToDB($log, $entityType);
        MigrationProDBWarningLogger::addWarningLogToFile($log);
    }

    public function getAllWarnings()
    {
        $warnings = MigrationProDBWarningLogger::getAllWarnings();

        $warnings = array_map(array($this, 'createWarningMessage'), $warnings);

        return $warnings;
    }

    private function createWarningMessage($warning)
    {
        $warning = 'There is (are) ' . $warning['count'] . ' ' . EntityTypeMapper::getEntityTypeNameByAlias($warning['entity_type']) . ' warning(s).';

        return $warning;
    }
}
