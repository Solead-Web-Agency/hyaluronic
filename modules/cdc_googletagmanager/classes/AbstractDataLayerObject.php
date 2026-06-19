<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SAS Comptoir du Code
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SAS Comptoir du Code is strictly forbidden.
 * In order to obtain a license, please contact us: contact@comptoirducode.com
 *
 * @author    Vincent - Comptoir du Code
 * @copyright Copyright(c) 2015-2024 SAS Comptoir du Code
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 * @package   cdc_googletagmanager
 */

if (!defined('_PS_VERSION_')) { exit; }

abstract class AbstractDataLayerObject {
    public function removeNull()
    {
        $properties = get_object_vars($this);
        foreach ($properties as $p_key => $p_val) {
            if(is_null($p_val)) {
                unset($this->$p_key);
            } elseif (is_object($p_val) && is_a($p_val, 'AbstractDataLayerObject')) {
                $p_val->removeNull();
            }
        }
    }

    /**
     * Merge object
     * @param $object
     */
    public function mergeObject($object) {
        if(is_object($object)) {
            $attributes = get_object_vars($object);
            foreach ($attributes as $attribute => $value) {
                $this->$attribute = $value;
            }
        }
    }
}