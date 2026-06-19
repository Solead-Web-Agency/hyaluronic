<?php
/**
 * hook-action_class.php file defines controller which manage hooks sequentially
 */

class BT_GactHookAction extends BT_GactHookBase
{
    /**
     * BT_GactHookAction constructor.
     * @param $sHookAction
     */
    public function __construct($sHookAction)
    {
        // set hook action
        $this->sHookAction = $sHookAction;
    }

    /**
     * execute hook
     *
     * @param array $aParams
     * @return array
     */
    public function run(array $aParams = null)
    {
        //to handle DAO
        require_once(_GACT_PATH_LIB . 'module-dao_class.php');

        // set variables
        $aDisplayHook = array();

        switch ($this->sHookAction) {
            case 'validateOrder' :
                // use case - display nothing only process storage in order to send an email
                $aDisplayHook = call_user_func_array(array($this, 'validateOrder'), array($aParams));
                break;
            default :
                break;
        }

        return $aDisplayHook;
    }


    /**
     * add order in Prestashop
     *
     * @param array $aParams
     * @return array
     */
    private function validateOrder(array $aParams = null)
    {
        if (!empty($aParams['order']->id)) {
            // detect if the module has already sent the code and create and update the order in the table
            if (!BT_GactModuleDao::checkOrder($aParams['order']->id, 'order', true)) {
                BT_GactModuleDao::addOrder($aParams['cart']->id, 0, $aParams['order']->id, 0, 'confirmation');
            }
        }
    }
}
