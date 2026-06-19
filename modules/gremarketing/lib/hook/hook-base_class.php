<?php

/**
 * Google Dynamic Remarketing
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

abstract class BT_GRHookBase
{
    /**
     * assigns few information about hook
     *
     * @param string $sHookAction
     */
    abstract public function __construct($sHookAction);

    /**
     * execute hook
     *
     * @param array $aParams
     * @return array
     */
    abstract public function run(array $aParams = null);
}
