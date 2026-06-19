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

class MigrationProTimeRemapping
{
    private $start_time;
    private $end_time;
    private $mapping_time;
    private $slow_time_coefficient = 0;
    private $medium_time_coefficient = 0;
    private $fast_time_coefficient = 0;
    private $fast_time_obj = array("taxes", "cms", "seo", "accessories");
    private $medium_time_obj = array("manufacturers", "categories", "catalog price rules", "employees", "customers",
        "cart rules", "orders", "message_threads", "customers");
    private $slow_time_obj = array("products");
    private $current_type = '';
    private $speed = 1;

    /**
     * Calculate time remapping for all data
    */
    public function __construct()
    {
        $this->start_time = new DateTime(date('Y-m-d H:i:s'));
        //Set default values if do not exists for time remapping or get values
        if (!MigrationPro::mpConfigure("migrationpro_slow_time_coefficient", 'get')) {
            MigrationPro::mpConfigure("migrationpro_slow_time_coefficient", 0.24);
        } else {
            $this->slow_time_coefficient = MigrationPro::mpConfigure("migrationpro_slow_time_coefficient", 'get');
        }
        if (!MigrationPro::mpConfigure("migrationpro_medium_time_coefficient", 'get')) {
            MigrationPro::mpConfigure("migrationpro_medium_time_coefficient", 0.12);
        } else {
            $this->medium_time_coefficient = MigrationPro::mpConfigure("migrationpro_medium_time_coefficient", 'get');
        }
        if (!MigrationPro::mpConfigure("migrationpro_fast_time_coefficient", 'get')) {
            MigrationPro::mpConfigure("migrationpro_fast_time_coefficient", 0.06);
        } else {
            $this->fast_time_coefficient = MigrationPro::mpConfigure("migrationpro_fast_time_coefficient", 'get');
        }
        //Get mapping time
        if (MigrationPro::mpConfigure("migrationpro_mapping_time", 'get')) {
            $this->mapping_time = MigrationPro::mpConfigure("migrationpro_mapping_time", 'get');
        }
        //Get curent migration speed
        $this->speed = MigrationPro::mpConfigure('migrationpro_query_row_count', 'get');
    }

    /**
     * Return remapping time
     *
     * @param $speed Migration speed
     */
    public function getTimeRemapping()
    {
        $result = 0;
        foreach (MigrationProProcess::getAll() as $process) {
            if ($process["finish"] != 1) {
                $this->end_time = new DateTime(date('Y-m-d H:i:s'));
                $time_stamp_diff = ($this->end_time->getTimestamp() - $this->start_time->getTimestamp());

                //Update coefficient
                if ($this->current_type == $process["type"]) {
                    $this->updateTimeCoefficients($process["type"], $time_stamp_diff / $this->speed);
                }

                $balance = (int)$process["total"] - (int)$process["imported"];
                $coefficient = $this->getProcessTimeCoefficient($process["type"]);

                $result += ($balance*$coefficient);
            }
        }
        return $this->timeFormating((int)$result);
    }

    /**
     * Set current migrated process type
     *
     * @param $type Process type
     */
    public function setCurrentType($type)
    {
        $this->current_type = $type;
    }

    /**
     * Set mapping load time
     */
    public function setMappingTime()
    {
        $this->end_time = new DateTime(date('Y-m-d H:i:s'));
        $time_stamp_diff = ($this->end_time->getTimestamp() - $this->start_time->getTimestamp());
            
        MigrationPro::mpConfigure("migrationpro_mapping_time", round($time_stamp_diff, 4));
    }

    /**
     * Convert seccons to hh:mm:ss format
     *
     * @param $secconds Total remapping secconds
     */
    private function timeFormating($secconds)
    {
        if ($secconds == 0) {
            $secconds = 5;
        }
        return gmdate("H:i:s", $secconds);
    }

    /**
     * Return coefficient for  migration types
     *
     * @param $type Process type
     * @return int
     */
    private function getProcessTimeCoefficient($type)
    {
        if (in_array($type, $this->fast_time_obj)) {
            return $this->fast_time_coefficient;
        } else if (in_array($type, $this->medium_time_obj)) {
            return $this->medium_time_coefficient;
        } else if (in_array($type, $this->slow_time_obj)) {
            return $this->slow_time_coefficient;
        }
    }

    /**
     * Update on DB time coifficient
     *
     * @param $type Process type
     * @param $coefficient Coefficient of type
     */
    private function updateTimeCoefficients($type, $coefficient)
    {
        
        //Set default values if do not exists for time remapping or get values
        if (in_array($type, $this->slow_time_obj)) {
            MigrationPro::mpConfigure("migrationpro_slow_time_coefficient", $coefficient);
            $this->slow_time_coefficient = MigrationPro::mpConfigure("migrationpro_slow_time_coefficient", 'get');
        } else if (in_array($type, $this->medium_time_obj)) {
            MigrationPro::mpConfigure("migrationpro_medium_time_coefficient", $coefficient);
            $this->medium_time_coefficient = MigrationPro::mpConfigure("migrationpro_medium_time_coefficient", 'get');
        } else if (in_array($type, $this->fast_time_obj)) {
            MigrationPro::mpConfigure("migrationpro_fast_time_coefficient", $coefficient);
            $this->fast_time_coefficient = MigrationPro::mpConfigure("migrationpro_fast_time_coefficient", 'get');
        }
    }
}
