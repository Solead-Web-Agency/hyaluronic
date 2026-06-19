<?php
/**
 * hook-display_class.php file defines controller which manage hooks sequentially
 */

class BT_GactHookDisplay extends BT_GactHookBase
{
    /**
     * @var string $sHookType : define hook type
     */
    protected $sHookType = null;

    /**
     * Magic Method __construct assigns few information about hook
     *
     * @param string
     */
    public function __construct($sHookType)
    {
        // set hook type
        $this->sHookType = $sHookType;
    }


    /**
     * execute hook
     *
     * @param array $aParams
     *
     * @return array
     */
    public function run(array $aParams = null)
    {
        // set variables
        $aDisplayHook = array();

        // include DAO
        require_once(_GACT_PATH_LIB . 'module-dao_class.php');

        switch ($this->sHookType) {
            case 'header' : // use case - display footer
                $aDisplayHook = call_user_func_array(array($this, 'display' . ucfirst($this->sHookType)), array($aParams));
                break;
            default :
                break;
        }

        return $aDisplayHook;
    }


    /**
     * display header and the tag
     *
     * @param array $aParams
     * @return array
     */
    private function displayHeader(array $aParams = null)
    {
        try {
            // Use case for 1.7
            if (isset(Context::getContext()->controller->OrderConfirmationController->id_order)
                && Context::getContext()->controller->OrderConfirmationController->id_order != false
                && Context::getContext()->controller->OrderConfirmationController->id_order != null
            ) {
                $iOrderId = Context::getContext()->controller->OrderConfirmationController->id_order;

            } elseif (isset(Context::getContext()->controller->id_order)
                && Context::getContext()->controller->id_order != false
                && Context::getContext()->controller->id_order != null
            ) {
                $iOrderId = Context::getContext()->controller->id_order;

            } elseif (Tools::getIsset('id_order')) {
                $iOrderId = Tools::getValue('id_order');
            }

            // set default to display
            $aAssign['bDisplay'] = false;
            $aAssign['iConversionId'] = GAdwordsTracking::$conf['GACT_CONVERSION_ID'];
            $aAssign['sConversionLabel'] = GAdwordsTracking::$conf['GACT_CONVERSION_LABEL'];

            if (!empty($iOrderId)
                && !empty($aAssign['iConversionId'])
                && !empty($aAssign['sConversionLabel'])
            ) {
                // get the current order
                $oOrder = new Order($iOrderId);

                if (!empty($oOrder->valid)) {
                    // check if the order is not already registered in the database
                    if (!BT_GactModuleDao::checkOrder($iOrderId, 0)) {
                        // add the order
                        BT_GactModuleDao::addOrder($oOrder->id_cart, 0, $iOrderId, $oOrder->valid, 'confirmation');
                    }

                    // define the total paid and the currency
                    $sCurrencyIso = BT_GactModuleTools::getCurrency('iso_code', $oOrder->id_currency);
                    $fTotalPaid = BT_GactModuleTools::getOrderPrice($oOrder, GAdwordsTracking::$conf['GACT_USE_TAX'], GAdwordsTracking::$conf['GACT_USE_SHIPPING'], GAdwordsTracking::$conf['GACT_USE_WRAPPING']);

                    // include base class of dynamic tags
                    require(_GACT_PATH_LIB_DYN_TAGS . 'base-dynamic-tags_class.php');

                    // set dyn tags params
                    $aDynTags = array(
                        'iOrderId' => $iOrderId,
                        'fTotalPaid' => (float)$fTotalPaid,
                    );
                    // get current dynamic tags
                    $oTagsCtrl = BT_GactBaseDynTags::get('purchase', $aDynTags);

                    // set params
                    $oTagsCtrl->set();

                    // assign customized tags
                    if ($oTagsCtrl->bValid === true) {
                        // get current value
                        $aResult = $oTagsCtrl->display();

                        $aAssign['bDisplay'] = true;
                        $aAssign['fTotalPaid'] = $fTotalPaid;
                        $aAssign['iTransactionId'] = $iOrderId;
                        $aAssign['sCurrency'] = $sCurrencyIso;

                        // update code into the table
                        BT_GactModuleDao::updateOrder((int)$iOrderId, 1);
                    }
                }
            }
        } catch (Exception $e) {}

        return array(
            'tpl'    => _GACT_TPL_HOOK_PATH . _GACT_TPL_HEADER,
            'assign' => $aAssign,
        );
    }
}
