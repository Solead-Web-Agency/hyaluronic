<?php
/**
 * Google Merchant Center Pro
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

class BT_GmcProHookAction extends BT_GmcProHookBase
{
    /**
     * Magic Method __destruct
     *
     * @category hook collection
     *
     */
    public function __destruct(){}

    /**
     * run() method execute hook
     *
     * @param array $aParams
     * @return array
     */
    public function run(array $aParams = null)
    {
        // set variables
        $aDisplayHook = array();

        switch ($this->sHook) {
            default:
                break;
        }

        return $aDisplayHook;
    }
}
