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

class GsaPayment extends PaymentModule
{
    protected $identifier = 'gsaPayment';
    protected $public_name = 'Google Shopping Payment';
    public $active = true;
    public $name = 'gmerchantcenterpro';

    /**
     * Get order states
     *     *
     * @return array
     */
    public function getOrderStates()
    {
        $OrderStates = OrderState::getOrderStates(GMerchantCenterPro::$iCurrentLang);

        // Search orders status for GMC GSA 
        if (is_array($OrderStates) && isset($OrderStates)) {
            foreach ($OrderStates as $OrderState) {
                if ($OrderState['module_name'] == _GMCP_MODULE_SET_NAME) {

                    //Use case for validation in progress from GSA
                    if ($OrderState['logable'] == 1 && $OrderState['paid'] == 0  && $OrderState['shipped'] == 0) {
                        $iOrderValdationGsa = $OrderState['id_order_state'];
                    }

                    //Use case for validate order from GSA
                    if ($OrderState['logable'] == 1 && $OrderState['paid'] == 1 && $OrderState['shipped'] == 0) {
                        $iOrderValidateGsa = $OrderState['id_order_state'];
                    }

                    //Use case canceled order from GSA
                    if ($OrderState['logable'] == 0 && $OrderState['paid'] == 0 && $OrderState['shipped'] == 0) {
                        $iOrderCanceledGsa = $OrderState['id_order_state'];
                    }
                }
            }
        }
        return array(
            'gsa' => array(
                'in_progress' => (int) $iOrderValdationGsa,
                'done' => (int) $iOrderValidateGsa,
                'error' => (int) \Configuration::getGlobalValue('PS_OS_ERROR'),
                'cancel' => $iOrderCanceledGsa,
            ),
        );
    }

    /**
     * get public name
     *
     *
     * @return strung
     */
    public function getPublicName($options = array())
    {
        return $this->public_name;
    }
    
}
