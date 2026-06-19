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

interface BT_IAdmin
{
    /**
     * process display or updating or etc ... admin
     *
     * @param mixed $aParam => $_GET or $_POST
     * @return bool
     */
    public function run(array $aParam = null);
}
