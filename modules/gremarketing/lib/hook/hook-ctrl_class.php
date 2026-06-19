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

class BT_GRHookCtrl
{
    /**
     * @var obj $oHook : defines hook object to display
     */
    private $oHook = null;

    /**
     * Magic Method __construct assigns few information about module and instantiate parent class
     *
     * @param string $sType : type of interface to execute
     * @param string $sAction
     */
    public function __construct($sType, $sAction)
    {
        // include interface of hook executing
        require_once(_GR_PATH_LIB_HOOK . 'hook-base_class.php');

        // check if file exists
        if (!file_exists(_GR_PATH_LIB_HOOK . 'hook-' . $sType . '_class.php')) {
            throw new Exception("no valid file", 130);
        } else {
            // include matched hook object
            require_once(_GR_PATH_LIB_HOOK . 'hook-' . $sType . '_class.php');

            if (
                !class_exists('BT_GRHook' . ucfirst($sType))
                && !method_exists('BT_GRHook' . ucfirst($sType), 'run')
            ) {
                throw new Exception("no valid class and method", 131);
            } else {
                // set class name
                $sClassName = 'BT_GRHook' . ucfirst($sType);

                // instantiate
                $this->oHook = new $sClassName($sAction);
            }
        }
    }

    /**
     * execute hook
     *
     * @param array $aParams
     * @return array $aDisplay : empty => false / not empty => true
     */
    public function run(array $aParams = null)
    {
        return $this->oHook->run($aParams);
    }
}
