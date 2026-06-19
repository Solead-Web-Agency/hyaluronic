<?php
/**
 * hook-base_class.php file defines controller which manage hooks sequentially
 */

abstract class BT_GactHookBase
{
    /**
     * Magic Method __construct assigns few information about hook
     *
     * @param string $sHookAction
     */
    abstract public function __construct($sHookAction);

    /**
     * run() method execute hook
     *
     * @param array $aParams
     *
     * @return array
     */
    abstract public function run(array $aParams = null);
}
