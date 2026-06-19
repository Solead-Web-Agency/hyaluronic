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

class GsaReturned extends GsaBase
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

        // set variables
        $aActions = array();
        switch ($this->sAction) {
            case 'getReturn':
                // use case - execute to get return from GSA and create orders on PS
                $aActions = call_user_func_array(array($this, 'getReturn'), array($aParams));
                break;
            case 'pushReturnedOrderLine':
                // use case - execute to push refunded order line to octopus
                $aActions = call_user_func_array(array($this, 'pushReturnedOrderLine'), array($aParams));
                break;
            case 'returnedOrderLine':
                // use case - execute the returned order line action when is done on GSA
                $aActions = call_user_func_array(array($this, 'returnedOrderLine'), array($aParams));
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
     * @return array
     */
    private function getReturn(array $aParams = null)
    {
        $bResponse = false;
        if (!empty($aParams['data'])) {

            //Loop on data returned by google shopping action
            foreach ($aParams['data'] as $sKey => $aParamData) {
                // Use case if we have already the data from our database to prevent multiple return creation
                $bAlreadyReturned = BT_GmcGsaDao::isAlreadyReturned($aParamData['return_id']);
                $iTotalQtyReturn = 0;

                // If the order returned is not managed yet on our table
                if (empty($bAlreadyReturned)) {
                    $aOrderDetailList = array();
                    $aProductQtyList = array();
                    $aParamDataProduct = array();
                    if (isset($aParamData['items'])) {

                        // Loop on item returned from GSA
                        foreach ($aParamData['items'] as $sKey => $item) {
                            $aOrderDetails = BT_GmcGsaDao::getOrderGsaProductDetails($aParamData['id_gsa_order'], $item['id']);
                            if (!empty($aOrderDetails)) {
                                foreach ($aOrderDetails as $aOrderDetail) {
                                    $iOrderDetailId = BT_GmcGsaDao::getOrderDetailId((int) $aParamData['id_shop_order'], (int) $aOrderDetail['shop_product_id'], (int) $aOrderDetail['shop_product_attribute_id'], (string) $aOrderDetail['gsa_product_id']);
                                    //Build the array required for addReturnDetail form method param from PS
                                    if (!empty($iOrderDetailId)) {
                                        $iTotalQtyReturn = $iTotalQtyReturn + (int) $item['qty'];
                                        $aOrderDetailList[$iOrderDetailId['id_order_detail']] = $iOrderDetailId['id_order_detail'];
                                        $aProductQtyList[$iOrderDetailId['id_order_detail']] = $item['qty'];
                                        $aParamDataProduct['id_gsa_product'] = $item['id'];
                                    }
                                }
                            }
                        }
                    }

                    if (isset($aParamDataProduct['id_gsa_product'])) {
                        $oOrder = new Order((int) $aParamData['id_shop_order']);
                        $oOrderReturn = new OrderReturn();
                        $oOrderReturn->id_customer = (int) $oOrder->id_customer;
                        $oOrderReturn->id_order = (int) $oOrder->id;
                        $oOrderReturn->state = 1;
                        $oOrderReturn->question = 'Refund from GSA';

                        if ($oOrderReturn->add()) {
                            if ($oOrderReturn->addReturnDetail($aOrderDetailList, $aProductQtyList, 0, 0)) {
                                if (BT_GmcGsaDao::updateGsaOrdersProductsByGsaProductId('quantity_returned', (int) $iTotalQtyReturn, (string) $aParamData['id_gsa_order'], (string) $aParamDataProduct['id_gsa_product']) && BT_GmcGsaDao::addReturnData((string) $aParamData['return_id'], (int) $aParamData['id_shop_order'], (string) $aParamData['id_gsa_order'], $aParamData['items'], GMerchantCenter::$iShopId)) {
                                    $bResponse = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $bResponse;
    }

    /**
     * push update returned order from PS to our API
     *
     * @param array $aParams
     * @return array
     */
    private function pushReturnedOrderLine(array $aParams = null)
    {

        $aToReturnOrderLineOutput = array();

        //Get return product line 
        $aToReturnOrdersLines = BT_GmcGsaDao::haveReturnedOrderLine();
        foreach ($aToReturnOrdersLines as $sKey => $aData) {
            $iOrderId = BT_GmcGsaDao::getPsOrderFromGsa($aData['id_gsa_order']);
            $aToReturnOrderLineOutput[$sKey]['id_gsa_order'] = $aData['id_gsa_order'];
            $aToReturnOrderLineOutput[$sKey]['transaction_id'] = !empty($iOrderId) ? Order::getUniqReferenceOf($iOrderId) : rand(1, 9999);
            $aToReturnOrderLineOutput[$sKey]['line_item_id'] = $aData['gsa_product_id'];
            $aToReturnOrderLineOutput[$sKey]['quantity_returned'] = $aData['quantity_returned'];
        }

        $aOrdersData = array(
            'order_lines_returned' => $aToReturnOrderLineOutput,
        );

        return $aOrdersData;
    }

    /**
     * refunded order line update from GSA
     *
     * @param array $aParams
     * @return array
     */
    private function returnedOrderLine(array $aParams = null)
    {
        $bResponse = false;

        if (isset($aParams['data'])) {
            foreach ($aParams['data'] as $sOrderId) {
                if (BT_GmcGsaDao::updateGsaOrders('is_returned_synch', 1, (string) $sOrderId) && BT_GmcGsaDao::updateGsaOrders('gsa_status', 'partialReturned', (string) $sOrderId)) {
                    $bResponse = true;
                }
            }
        }
        return $bResponse;
    }
}
