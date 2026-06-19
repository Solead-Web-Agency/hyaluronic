<?php

/**
 * Google Merchant Center
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class GsaCtrl
{
    /**
     * @var obj $_oHook : defines hook object to display
     */
    private $oHook = null;

    /**
     * instantiate the matching hook class
     *
     * @param string $sType : type of interface to execute
     * @param string $sAction
     */
    public function __construct($sType, $sAction)
    {

        // include interface of hook executing
        require_once(_GMC_GSA_LIB . 'gsa-base_class.php');
        require_once(_GMC_GSA_LIB . 'gsa-dao_class.php');
        require_once(_GMC_GSA_PAYMENT_MODULE . 'gsa-payment_class.php');

        // check if file exists
        if (!file_exists(_GMC_GSA_LIB . 'gsa-' . $sType . '_class.php')) {
            throw new Exception("no valid file", 130);
        } else {
            // include matched hook object
            require_once(_GMC_GSA_LIB . 'gsa-' . $sType . '_class.php');

            if (
                !class_exists('gsa' . ucfirst($sType))
                && !method_exists('gsa' . ucfirst($sType), 'run')
            ) {
                throw new Exception("no valid class and method", 131);
            } else {
                // set class name
                $sClassName = 'gsa' . ucfirst($sType);

                // instantiate
                $this->oHook = new $sClassName($sAction);
            }
        }
    }

    /**
     * execute hook
     *
     * @category hook collection
     * @param array $aParams
     * @return array $aDisplay : empty => false / not empty => true
     */
    public function run(array $aParams = null)
    {
        return $this->oHook->run($aParams);
    }
}
