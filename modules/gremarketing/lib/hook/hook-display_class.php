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

class BT_GRHookDisplay extends BT_GRHookBase
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
     * @return array
     */
    public function run(array $aParams = null)
    {
        // set variables
        $aDisplayHook = array();

        switch ($this->sHookType) {
            case 'header': // use case - display footer
                $aDisplayHook = call_user_func_array(array($this, 'display' . ucfirst($this->sHookType)), array($aParams));
                break;
            default:
                break;
        }

        return $aDisplayHook;
    }

    /**
     * display footer
     *
     * @param array $aParams
     * @return array
     */
    private function displayHeader(array $aParams = null)
    {
        // set
        $aAssign = array();

        try {
            $aAssign['iGoogleId'] = GRemarketing::$conf['GR_REMARKETING_ID'];
            $aAssign['bPS16'] = false;

            // set output
            $aOutPut = array();

            // if dynamic activated
            if (
                !empty(GRemarketing::$conf['GR_REMARKETING_DYNAMIC'])
                && !empty($aAssign['iGoogleId'])
            ) {
                // include base class of dynamic tags
                require_once(_GR_PATH_LIB_DYN_TAGS . 'base-dynamic-tags_class.php');

                // detect page - force page type => use case for order confirmation page with PayPal order
                $sPageType = (!empty($aParams['sPageType']) ? $aParams['sPageType'] : BT_GRModuleTools::detectCurrentPage());

                // detect if GMC PRO is installed
                $oGMCPro = BT_GRModuleTools::isInstalled('gmerchantcenterpro', array(), true);
                $oGMC = BT_GRModuleTools::isInstalled('gmerchantcenter', array(), true);

                // set prefix module name for Google
                $sPrefixGmcName = (!empty($oGMCPro) ? 'GMCP' : 'GMERCHANTCENTER');

                // define the product attribute ID
                $iProductAttributeId = null;

                // detect if we export by combination into GMC or GMCPRO
                $bComboProduct = (Configuration::get($sPrefixGmcName . '_P_COMBOS') == 1 ? true : false);

                // check if there is a current produc page
                $iProductId = Tools::getvalue('id_product');

                // Detect the Order ID - Use case for 1.7
                if (
                    isset(Context::getContext()->controller->OrderConfirmationController->id_order)
                    && Context::getContext()->controller->OrderConfirmationController->id_order != false
                    && Context::getContext()->controller->OrderConfirmationController->id_order != null
                ) {
                    $iOrderId = Context::getContext()->controller->OrderConfirmationController->id_order;
                } elseif (
                    isset(Context::getContext()->controller->id_order)
                    && Context::getContext()->controller->id_order != false
                    && Context::getContext()->controller->id_order != null
                ) {
                    $iOrderId = Context::getContext()->controller->id_order;
                } elseif (Tools::getIsset('id_order')) {
                    $iOrderId = Tools::getValue('id_order');
                } else {
                    $iOrderId = null;
                }

                // force the page tupe if we detect the order ID
                if (!empty($iOrderId)) {
                    $sPageType = 'purchase';
                }

                // Detect the Cart ID - Use case for 1.7
                if (Tools::getvalue('id_cart') != false) {
                    $iCartId = Tools::getvalue('id_cart');
                } elseif (!empty(GRemarketing::$oCookie->id_cart)) {
                    $iCartId = GRemarketing::$oCookie->id_cart;
                } else {
                    $iCartId = null;
                }

                // detect if we have the product attribute ID when we do an export with combination in GMC / GMCPRO
                if (
                    !empty($bComboProduct)
                    && !empty($iProductId)
                ) {
                    if (!empty(Tools::getIsset('id_product_attribute'))) {
                        $iProductAttributeId = Tools::getValue('id_product_attribute');
                    } elseif (!empty(Tools::getIsset('bt_product_attribute'))) {
                        $iProductAttributeId = Tools::getValue('bt_product_attribute');
                    } else {
                        $iDefaultAttribute = product::getDefaultAttribute($iProductId);

                        if (!empty($iDefaultAttribute)) {
                            $iProductAttributeId = $iDefaultAttribute;
                        }
                    }
                }

                // set dyn tags params
                $aDynTags = array(
                    'iCategoryId' => Tools::getvalue('id_category'),
                    'iProductId' => $iProductId,
                    'iProductAttributeId' => $iProductAttributeId,
                    'iCartId' => $iCartId,
                    'iOrderId' => $iOrderId,
                    'bUseTax' => (Configuration::get('PS_TAX') == 1 ? true : false),
                    'oGMC' => (!empty($oGMCPro) ? $oGMCPro : $oGMC),
                    'bComboProduct' => $bComboProduct,
                    'sPrefixName' => (!empty($oGMCPro) ? 'GMCP' : 'GMC'),
                    'sGmcPrefix' => Configuration::get($sPrefixGmcName . '_ID_PREFIX'),
                    'sGooglePrefix' => GRemarketing::$conf['GR_GOOGLE_PREFIX'],
                    'bUserId' => GRemarketing::$conf['GR_USER_ID'],
                );

                // get current dynamic tags
                $oTagsCtrl = BT_BaseDynTags::get($sPageType, $aDynTags);

                $oTagsCtrl->set();

                // assign customized tags
                if ($oTagsCtrl->bValid) {
                    $aAssign['aDynTags'] = $oTagsCtrl->display();
                    $aAssign['sCR'] = "\n";

                    // add JS Definition for PS 1.6 according to their defer Inline JS function
                    if (!empty(GRemarketing::$bCompare16) && !empty($aAssign['aDynTags'])) {
                        foreach ($aAssign['aDynTags'] as $iIndex => $aParams) {
                            $aOutPut[$aParams['label']] = str_replace('\'', '', $aParams['value']);
                        }

                        // add google tags params
                        $aAssign['google_tag_params'] = $aOutPut;
                    }
                }
            }
            // add JS inline for PS 1.6
            if (!empty($aAssign['iGoogleId'])) {
                // set other google tags
                $aAssign['google_conversion_id'] = $aAssign['iGoogleId'];
                $aAssign['google_remarketing_only'] = true;
                $aAssign['google_custom_params'] = $aOutPut;
            }
        } catch (Exception $e) {
            //@TODO: nothing if exception is caught
        }

        return array('tpl' => _GR_TPL_HOOK_PATH . _GR_TPL_FOOTER, 'assign' => $aAssign);
    }
}
