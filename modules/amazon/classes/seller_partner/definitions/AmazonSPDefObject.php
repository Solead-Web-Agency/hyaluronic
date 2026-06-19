<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}

abstract class AmazonSPDefObject
{
    // In request, don't mass data sending. Because it can produce validation failure
    protected static $nonPrimitiveOptionalChildrenOnRequest = array();

    // ['child_name' => 'type_of_each_child_of_this_child']
    protected static $complexChildren = array();
    protected static $listOfComplexChildren = array();

    public function __construct($input = null)
    {
        $input = json_decode(json_encode($input));  // cast to object
        if ($input) {
            // stdClass is iterable, at least since 5.6
            foreach ($input as $key => $value) {
                if (isset(static::$listOfComplexChildren[$key])) {
                    if (is_array($value) && count($value)) {
                        $itemClass = static::$listOfComplexChildren[$key];
                        $this->$key = array_map(function ($itemInValue) use ($itemClass) {
                            return new $itemClass($itemInValue);
                        }, $value);
                    }
                } else {
                    $this->$key = $value;
                }
            }
        }

        // Init complex children, even empty objects. Except `$nonPrimitiveOptionalChildren` is set (on request objects)
        foreach (static::$complexChildren as $complexKey => $complexClass) {
            if (!in_array($complexKey, static::$nonPrimitiveOptionalChildrenOnRequest)
                || isset($input, $input->$complexKey)) {
                $this->$complexKey = new $complexClass(
                    isset($input, $input->$complexKey) ? $input->$complexKey : new stdClass()
                );
            }
        }
    }
}
