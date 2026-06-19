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

class GsaOrders extends GsaBase
{
    /**
     * execute action
     *
     * @param array $aParams
     * @return array
     */
    public function run(array $aParams = null)
    {
        require_once(_GMC_PATH_CONF . 'admin.conf.php');
        require_once(_GMC_PATH_LIB_GSA . 'gsa-client_class.php');
        require_once(_GMC_PATH_LIB_GSA . 'gsa-tools_class.php');
        require_once(_GMC_PATH_LIB_GSA . 'gsa-dao_class.php');

        // set variables
        $aActions = array();
        switch ($this->sAction) {
            case 'get':
                // use case - execute to get orders from GSA and create orders on PS
                $aActions = call_user_func_array(array($this, 'get'), array($aParams));
                break;
            case 'pushAcknowledge':
                // use case - push Acknowledge to octopus
                $aActions = call_user_func_array(array($this, 'pushAcknowledge'), array($aParams));
                break;
            case 'acknowledge':
                // use case - execute to set the acknowledge when is done on GSA
                $aActions = call_user_func_array(array($this, 'acknowledge'), array($aParams));
                break;
            case 'pushInProgress':
                // use case - push Acknowledge to octopus
                $aActions = call_user_func_array(array($this, 'pushInProgress'), array($aParams));
                break;
            case 'pendingShipment':
                // use case - execute to set pendingShipment status when is done on GSA
                $aActions = call_user_func_array(array($this, 'pendingShipment'), array($aParams));
                break;
            case 'pushShipped':
                // use case - execute to set shipped sych status when is done on GSA
                $aActions = call_user_func_array(array($this, 'pushShipped'), array($aParams));
                break;
            case 'shipped':
                // use case - execute to set shipped sych status when is done on GSA
                $aActions = call_user_func_array(array($this, 'shipped'), array($aParams));
                break;
            case 'pushDelivered':
                // use case - pushd Delivered to octopus
                $aActions = call_user_func_array(array($this, 'pushDelivered'), array($aParams));
                break;
            case 'delivered':
                // use case - execute to set delivered sych status when is done on GSA
                $aActions = call_user_func_array(array($this, 'delivered'), array($aParams));
                break;
            case 'pushCancelByCustomer':
                // use case - execute the cancel action when is done on GSA
                $aActions = call_user_func_array(array($this, 'pushCancelByCustomer'), array($aParams));
                break;
            case 'canceledByCustomer':
                // use case - execute the cancel action when is done on GSA
                $aActions = call_user_func_array(array($this, 'canceledByCustomer'), array($aParams));
                break;
            case 'pushRefunded':
                // use case - execute to push refunded to octopus
                $aActions = call_user_func_array(array($this, 'pushRefunded'), array($aParams));
                break;
            case 'refunded':
                // use case - execute the refund action when is done on GSA
                $aActions = call_user_func_array(array($this, 'refunded'), array($aParams));
                break;
            case 'pushRefundedOrderLine':
                // use case - execute to push refunded order line to octopus
                $aActions = call_user_func_array(array($this, 'pushRefundedOrderLine'), array($aParams));
                break;
            case 'refundedOrderLine':
                // use case - execute the refund order line action when is done on GSA
                $aActions = call_user_func_array(array($this, 'refundedOrderLine'), array($aParams));
                break;
            default:
                break;
        }

        return $aActions;
    }

    /**
     * update PS orders and customer with the data provided by our API
     *
     * @param array $aParams
     * @return bool
     */
    private function get(array $aParams = null)
    {
        $bSuccess = false;
        // Use if we have data from GSA
        if (!empty($aParams['data'])) {

            // Loop on data from Google shopping action
            foreach ($aParams['data'] as $sKey => $aParamData) {
                //Check if the order already exist on our table to prevent multiple order creation
                $bAlreadyExist = BT_GmcGsaDao::isGsaOrderExist($aParamData['order_data']['gsa_id']);

                //Use case for order creation on Prestashop
                if (!$bAlreadyExist) {

                    //Check if the customer account already exist
                    $iCustomerId = Customer::customerExists($aParamData['customer']['email'], true);

                    //Use case for new customer account
                    if (empty($iCustomerId)) {
                        $oCustomer = BT_GmcGsaTools::createCustomer($aParamData['customer']);
                    } else {
                        $oCustomer = new Customer($iCustomerId);
                    }

                    //Use case manage address
                    if (Validate::isLoadedObject($oCustomer)) {
                        //Address Alias for GSA orders - Todo option to manage the value prefrerence for the merchant can be do later
                        $sAliasDelivery = 'GSA import delivery';

                        //Manage case to test if the address already exist for delivery
                        $bAddressDeliveryExist = Address::aliasExist((string) $sAliasDelivery, 0, (int) $oCustomer->id);
                        if (empty($bAddressDeliveryExist)) {
                            $oAddressDelivery = BT_GmcGsaTools::createAddress($aParamData['address'], $oCustomer, $sAliasDelivery);
                        } else {
                            $oAddressDelivery = BT_GmcGsaTools::getAddressIdAlreadyExist($oCustomer, $sAliasDelivery);
                        }

                        //Manage case for billing address
                        $sAliasBilling = 'GSA import billing';
                        $bAddressBillingExist = Address::aliasExist((string) $sAliasBilling, 0, (int) $oCustomer->id);
                        if (empty($bAddressBillingExist)) {
                            $oAddressBilling = BT_GmcGsaTools::createAddress($aParamData['billing_address'], $oCustomer, $sAliasBilling);
                        } else {
                            $oAddressBilling = BT_GmcGsaTools::getAddressIdAlreadyExist($oCustomer, $sAliasBilling);
                        }
                    }
                    //Use case cart creation
                    if (Validate::isLoadedObject($oAddressDelivery) && Validate::isLoadedObject($oAddressBilling) && Validate::isLoadedObject($oCustomer)) {

                        $oCart = BT_GmcGsaTools::createCart($oCustomer, $oAddressDelivery, $oAddressBilling);

                        //Manage the case for order already added todo
                        if (Validate::isLoadedObject($oCart) && !empty($oCart->id)) {

                            // Added product line on the cart
                            if (BT_GmcGsaTools::createCartProductLines($aParamData['order_details'], $aParamData['order_data'], $oCart)) {
                                //Create the order
                                if (BT_GmcGsaTools::createOrder($aParamData['order_data'], $oCart, $oCustomer)) {
                                    $bSuccess = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $bSuccess;
    }

    /**
     * push acknowledge to Octopus
     *
     * @param array $aParams
     * @return array
     */
    private function pushAcknowledge(array $aParams = null)
    {
        $aToAcknowledgeOutput = array();

        // Get no acknowledge order yet
        $aToAcknowledge = BT_GmcGsaDao::getSimpleData('id_gsa_order', 'acknowledge', 0);
        //Build output with required data for Google
        foreach ($aToAcknowledge as $sKey => $aData) {
            $aToAcknowledgeOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToAcknowledgeOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToAcknowledgeOutput[$sKey]['merchant_order_id'] = $iOrderId;
        }

        $aOrdersData = array(
            'acknowledge' => $aToAcknowledgeOutput,
        );

        return $aOrdersData;
    }

    /**
     * acknowledge update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function acknowledge(array $aParams = null)
    {
        $bResponse = false;

        // Loop on order id
        if (isset($aParams['data'])) {
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('acknowledge', 1, (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }

        return $bResponse;
    }
    /**
     * push inprogress orders to Octopus
     *
     * @param array $aParams
     * @return array
     */
    private function pushInProgress(array $aParams = null)
    {
        $aToValidate = array();

        // Get no acknowledge order yet
        $aValidate = BT_GmcGsaDao::checkOrdersCanBeCancel();

        //Build output with required data for Google
        foreach ($aValidate as $sKey => $aData) {
            $aToValidate[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToValidate[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToValidate[$sKey]['merchant_order_id'] = $iOrderId;
        }

        $aOrdersData = array(
            'to_validate' => $aToValidate,
        );

        return $aOrdersData;
    }

    /**
     * update pendingShipment
     *
     * @param array $aParams
     * @return array
     */
    private function pendingShipment(array $aParams = null)
    {
        $bResponse = false;

        if (isset($aParams['data'])) {

            $paymentModule = new GsaPayment();
            $aOrderStates = $paymentModule->getOrderStates();

            foreach ($aParams['data'] as $sOrderId) {
                if (
                    BT_GmcGsaDao::updateGsaOrders('gsa_status', 'pendingShipment', (string) $sOrderId)
                    && BT_GmcGsaDao::updateGsaOrders('is_paid', 1, (string) $sOrderId)
                ) {
                    //Update the status on PS
                    $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa((string) $sOrderId);
                    $Order = new Order($iOrderId);
                    $oOrderHistory = new OrderHistory();
                    $oOrderHistory->id_order = (int) $Order->id;
                    $oOrderHistory->id_order_state = (int) $aOrderStates['gsa']['done'];

                    if ($oOrderHistory->add()) {
                        $oOrderHistory->changeIdOrderState((int) $aOrderStates['gsa']['done'], (int) $Order->id);
                        $bResponse = true;
                    }
                }
            }
        }

        return $bResponse;
    }

    /**
     * push update shipped from PS to our API
     *
     * @param array $aParams
     * @return array
     */
    private function pushShipped(array $aParams = null)
    {
        $aToShippedOutput = array();

        // Get shipped order and managed required data for the synch
        $aToShipped =  BT_GmcGsaDao::haveToBeShipped();
        //Build output with required data for Google
        foreach ($aToShipped as $sKey => $aData) {
            $aToShippedOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aDataShipping = BT_GmcGsaDao::getShopShippingData($iOrderId);

            // Instanciate order object
            $oOrder = new Order((int) $iOrderId);
            $oCarrier = new Carrier((int) $oOrder->id_carrier);
            $aCarriersMapped = !empty(GMerchantCenter::$conf['GMC_GSA_CARRIERS_MAP']) ? unserialize(GMerchantCenter::$conf['GMC_GSA_CARRIERS_MAP']) : '';

            $aToShippedOutput[$sKey]['carrier'] = $aCarriersMapped[$oCarrier->id];
            $aToShippedOutput[$sKey]['tracking_number'] = $aDataShipping['tracking_number'];
            $aToShippedOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToShippedOutput[$sKey]['merchant_order_id'] = $iOrderId;

            // Init product object to us the getProducts and get order details
            $oOrder = new Order((int) $iOrderId);
            $aOrderProducts = $oOrder->getProducts(false, false, false, false);

            // Build order line item to send it to GSA
            foreach ($aOrderProducts as $aProduct) {
                //Get the iLineItemId to identify the line on GSA
                $iLineItemId = BT_GmcGsaDao::getGsaLineItemId($aData['id_gsa_order'], $aProduct['product_id'], $aProduct['product_attribute_id'], GMerchantCenter::$iShopId);
                $aToShippedOutput[$sKey]['line_items'][] = array(
                    'lineItemId' => $iLineItemId,
                    'quantity' => $aProduct['product_quantity']
                );
            }
        }

        $aOrdersData = array(
            'shipped' =>  !empty($aToShippedOutput) ? $aToShippedOutput : array(),
        );

        return $aOrdersData;
    }

    /**
     * shipped update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function shipped(array $aParams = null)
    {
        $bResponse = false;

        if (isset($aParams['data'])) {
            // Loop on order id
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('is_shipped_synch', 1, (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('gsa_status', 'shipped', (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }
        return $bResponse;
    }

    /**
     * push delivered to Octopus
     *
     * @param array $aParams
     * @return array
     */
    private function pushDelivered(array $aParams = null)
    {
        $aToDeliveredOutput = array();

        //Managed the delivered orders 
        $aToDelivred = BT_GmcGsaDao::haveToBeDelivered();
        //Build output with required data for Google in this case the loop can be remove, but if we need other data later we can keep this build
        foreach ($aToDelivred as $sKey => $aData) {
            $aToDeliveredOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToDeliveredOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToDeliveredOutput[$sKey]['merchant_order_id'] = $iOrderId;
        }

        $aOrdersData = array(
            'delivered' => $aToDeliveredOutput,
        );

        return $aOrdersData;
    }

    /**
     * delivered update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function delivered(array $aParams = null)
    {
        $bResponse = false;

        // Loop on order id
        if (isset($aParams['data'])) {
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('is_delivered_synch', 1, (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('gsa_status', 'delivered', (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }

        return $bResponse;
    }


    /**
     * push order not validate to manage the case of cancled customer (in the 1st 30 min)
     *
     * @param array $aParams
     * @return array
     */
    private function pushCancelByCustomer(array $aParams = null)
    {
        $aToCancelCkeckOutput = array();

        //get Refunded orders
        $aToCancelCkeck = BT_GmcGsaDao::checkOrdersCanBeCancel();
        foreach ($aToCancelCkeck as $sKey => $aData) {
            $aToCancelCkeckOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToCancelCkeckOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToCancelCkeckOutput[$sKey]['merchant_order_id'] = $iOrderId;
        }

        $aOrdersData = array(
            'ckeck_cancel' => $aToCancelCkeckOutput,
        );

        return $aOrdersData;
    }

    /**
     * update canceled by customer
     *
     * @param array $aParams
     * @return array
     */
    private function canceledByCustomer(array $aParams = null)
    {
        $bResponse = false;

        // Loop on order id
        if (isset($aParams['data'])) {

            $paymentModule = new GsaPayment();
            $aOrderStates = $paymentModule->getOrderStates();

            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('gsa_status', 'canceled', (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('is_canceled_synch', 1, (string) $sOrderId)) {
                    //Update the status on PS
                    $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa((string) $sOrderId);
                    $Order = new Order($iOrderId);
                    $oOrderHistory = new OrderHistory();
                    $oOrderHistory->id_order = (int) $Order->id;
                    $oOrderHistory->id_order_state = (int) $aOrderStates['gsa']['cancel'];

                    if ($oOrderHistory->add()) {
                        $oOrderHistory->changeIdOrderState((int) $aOrderStates['gsa']['cancel'], (int) $Order->id);
                        $bResponse = 'cancel orders update done';
                    }
                }
            }
        }
        return $bResponse;
    }

    /**
     * push refunded data from PS to our API
     *
     * @param array $aParams
     * @return array
     */
    private function pushRefunded(array $aParams = null)
    {
        $aToRefundOutput = array();

        //get Refunded orders
        $aToRefund = BT_GmcGsaDao::haveToBeRefunded();
        foreach ($aToRefund as $sKey => $aData) {
            $aToRefundOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToRefundOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToRefundOutput[$sKey]['merchant_order_id'] = $iOrderId;
        }

        $aOrdersData = array(
            'refunded' => $aToRefundOutput,
        );

        return $aOrdersData;
    }

    /**
     * refunded update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function refunded(array $aParams = null)
    {
        $bResponse = false;

        // Loop on order id
        if (isset($aParams['data'])) {
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('is_refunded_synch', 1, (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('gsa_status', 'refunded', (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }
        return $bResponse;
    }

    /**
     * push update data from PS to our API
     *
     * @param array $aParams
     * @return array
     */
    private function pushRefundedOrderLine(array $aParams = null)
    {
        $aToRefunOrderLineOutput = array();

        //Get refunded product line 
        $aToRefunOrdersLines = BT_GmcGsaDao::haveRefundOrderLine();
        foreach ($aToRefunOrdersLines as $sKey => $aData) {
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToRefunOrderLineOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $aToRefunOrderLineOutput[$sKey]['merchant_order_id'] = $iOrderId;
            $aToRefunOrderLineOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToRefunOrderLineOutput[$sKey]['line_item_id'] = $aData['gsa_product_id'];
            $aToRefunOrderLineOutput[$sKey]['quantity_refunded'] = $aData['quantity_refunded'];
        }

        $aOrdersData = array(
            'order_lines_refunded' => $aToRefunOrderLineOutput,
        );

        return $aOrdersData;
    }

    /**
     * refunded order line update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function refundedOrderLine(array $aParams = null)
    {
        $bResponse = false;

        // Loop on order id
        if (isset($aParams['data'])) {
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('is_product_refunded_synch', 1, (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('gsa_status', 'partialRefund', (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }
        return $bResponse;
    }
}
