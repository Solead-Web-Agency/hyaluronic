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

abstract class BT_GmcProReviewCtrl
{
    /**
     * instantiate matched ctrl object
     *
     * @throws Exception
     * @param string $sCtrlType
     * @param array $aParams
     * @return obj ctrl type
     */
    public static function get($sCtrlType, array $aParams = null)
    {
        if (!empty($sCtrlType)) {
            $sCtrlType = strtolower($sCtrlType);

            require_once(_GMCP_PATH_LIB_REVIEWS . 'i-reviews.php');
            require_once(_GMCP_PATH_LIB_DAO . 'reviews-dao_class.php');

            // if valid controller
            if (file_exists(_GMCP_PATH_LIB_REVIEWS . $sCtrlType . '-reviews_class.php')) {
                // require
                require_once($sCtrlType . '-reviews_class.php');

                // set class name
                $sClassName = 'BT_' . ucfirst($sCtrlType) . 'Reviews';

                try {
                    $oReflection = new ReflectionClass($sClassName);

                    if ($oReflection->isInstantiable()) {
                        return $oReflection->newInstance($aParams);
                    } else {
                        throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => controller isn\'t instantiable', 'base-ctrl_class'), 900);
                    }
                } catch (ReflectionException $e) {
                    throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => invalid controller', 'base-ctrl_class'), 901);
                }
            } else {
                throw new Exception(GMerchantCenterPro::$oModule->l('Internal server error => the controller file doesn\'t exist', 'base-ctrl_class'), 902);
            }
        }
    }
}
