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

class BT_AdminCtrl
{
    /**
     * execute abstract derived admin object
     *
     * @param string $sAdminType : type of interface to display
     * @param array $aRequest : request
     * @return array $aDisplay : empty => false / not empty => true
     */
    public function run($sAdminType, $aRequest)
    {
        // set
        $aDisplay = array();

        // include interface
        require_once(_GR_PATH_LIB_ADMIN . 'i-admin.php');

        switch ($sAdminType) {
            case 'display':
                // include matched admin object
                require_once(_GR_PATH_LIB_ADMIN . 'admin-display_class.php');

                $oAdminType = BT_AdminDisplay::create();
                break;
            case 'update': // update basic settings /
                // include matched admin object
                require_once(_GR_PATH_LIB_ADMIN . 'admin-update_class.php');

                $oAdminType = BT_AdminUpdate::create();
                break;
            case 'delete': // delete comment
                // include matched admin object
                require_once(_GR_PATH_LIB_ADMIN . 'admin-delete_class.php');

                $oAdminType = BT_AdminDelete::create();
                break;
            case 'send': // send email for callback
                // include matched admin object
                require_once(_GR_PATH_LIB_ADMIN . 'admin-send_class.php');

                $oAdminType = BT_AdminSend::create();
                break;
            default:
                $oAdminType = false;
                break;
        }

        // process data to use in view (tpl)
        if (!empty($oAdminType)) {
            $aDisplay = $oAdminType->run($aRequest);
        }

        return $aDisplay;
    }
}
