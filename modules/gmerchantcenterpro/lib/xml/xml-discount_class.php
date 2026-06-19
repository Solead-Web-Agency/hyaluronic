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

require_once(_GMCP_PATH_LIB_XML . 'base-xml_class.php');

class BT_XmlDiscount extends BT_BaseXml
{
    /**
     * @param array $aParams
     * @param string $sType : define the tpy of the object we need to load for product or combination product
     */
    public function __construct($aParams = array())
    {
        require_once(_GMCP_PATH_LIB_DAO . 'cart-rules-dao_class.php');
    }

    /**
     * load available cart rules
     *
     * @return array
     */
    public function loadCartRules()
    {
        return BT_GmcProCartRulesDao::getCartRules(
            (string) GMerchantCenterPro::$conf['GMCP_DSC_NAME'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_DATE_FROM'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_DATE_TO'],
            (string) GMerchantCenterPro::$conf['GMCP_DSC_MIN_AMOUNT'],
            GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MIN'],
            GMerchantCenterPro::$conf['GMCP_DSC_VALUE_MAX'],
            GMerchantCenterPro::$conf['GMCP_DSC_TYPE'],
            GMerchantCenterPro::$conf['GMCP_DSC_CUMULABLE']
        );
    }

    /**
     * get currency code for cart rule
     *
     * @return string
     */
    private function getCurrencyCode($iCurrencyId)
    {
        $oCurrency = new Currency($iCurrencyId);
        return $oCurrency->iso_code;
    }

    /**
     * build discount XML tags
     *
     * @return array
     */
    public function buildDiscountXml($aParams)
    {
        $aDiscount = $this->loadCartRules();
        $aPromotionDestination = unserialize(GMerchantCenterPro::$conf['GMCP_PROMO_DEST']);

        // clean table association before generate XML
        BT_GmcProCartRulesDao::cleanAssocCartRules();

        $sContent = '';

        foreach ($aDiscount as $aCurrentDiscount) {
            $iCartRuleId = (int) $aCurrentDiscount['id_cart_rule'];
            $sDiscountTitle = (string) $aCurrentDiscount['name'];
            $fMinAmountPrice = (float) $aCurrentDiscount['minimum_amount'];
            $fAmountInCurrency = (float) $aCurrentDiscount['reduction_amount'];
            $fAmountInPercent = (float) $aCurrentDiscount['reduction_percent'];
            $sCurrencyDiscount = $this->getCurrencyCode((int) $aCurrentDiscount['reduction_currency']);
            $sCurrencyMinAmount = $this->getCurrencyCode((int) $aCurrentDiscount['minimum_amount_currency']);
            $sOfferType = BT_GmcProCartRulesDao::hasAssociateItem($iCartRuleId);
            $iQuantity = (int)$aCurrentDiscount['quantity'];

            // manage database insert for product association
            // 1st et get all discount code available
            $iCartRuleId = (int) $aCurrentDiscount['id_cart_rule'];

            if (!empty($iCartRuleId)) {
                $aProductIds = BT_GmcProCartRulesDao::hasAssociateItem($iCartRuleId);

                if (!empty($aProductIds)) {
                    //clean before make new assocation
                    foreach ($aProductIds as $iCurrentProdId) {
                        if ($iCurrentProdId['type'] == 'products') {
                            BT_GmcProCartRulesDao::setAssocCartRules($iCartRuleId, $iCurrentProdId['id_item']);
                        } elseif ($iCurrentProdId['type'] == 'categories') {
                            $oCategories = new Category(
                                (int) $iCurrentProdId['id_item'],
                                GMerchantCenterPro::$iCurrentLang
                            );

                            if (is_object($oCategories)) {
                                $aProducts = $oCategories->getProducts(GMerchantCenterPro::$iCurrentLang, 0, 1000, null, null, false, true, false, 1, true, null);

                                if (!empty($aProducts)) {
                                    foreach ($aProducts as $aProduct) {
                                        if ($aProduct['price'] > $fMinAmountPrice) {
                                            BT_GmcProCartRulesDao::setAssocCartRules($iCartRuleId, $aProduct['id_product']);
                                        }
                                    }
                                }
                            }
                        } elseif ($iCurrentProdId['type'] == 'manufacturers') {
                            $aProducts = Manufacturer::getProducts((int) $iCurrentProdId['id_item'], GMerchantCenterPro::$iCurrentLang, 0, 1000);

                            if (!empty($aProducts)) {
                                foreach ($aProducts as $aProduct) {
                                    if ($aProduct['price'] > $fMinAmountPrice) {
                                        BT_GmcProCartRulesDao::setAssocCartRules($iCartRuleId, $aProduct['id_product']);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $sContent .= "\t" . '<item>' . "\n";
            $sContent .= "\t\t" . '<g:promotion_id>' . GMerchantCenterPro::$conf['GMCP_ID_PREFIX'] . $iCartRuleId . '</g:promotion_id>' . "\n";
            $sContent .= "\t\t" . '<g:product_applicability>' . (!empty($sOfferType) ? 'SPECIFIC_PRODUCTS' : 'ALL_PRODUCTS') . '</g:product_applicability>' . "\n";

            if (!empty($aCurrentDiscount['code'])) {
                $sContent .= "\t\t" . '<g:offer_type>GENERIC_CODE</g:offer_type>' . "\n";
                $sContent .= "\t\t" . '<g:generic_redemption_code> ' . $aCurrentDiscount['code'] . ' </g:generic_redemption_code>' . "\n";
            } else {
                $sContent .= "\t\t" . '<g:offer_type>NO_CODE</g:offer_type>' . "\n";
            }
            $sContent .= "\t\t" . '<g:long_title>' . BT_GmcProModuleTools::formatTextForGoogle($sDiscountTitle) . '</g:long_title>' . "\n";

            //Use case for description
            if (!empty($aCurrentDiscount['description'])) {
                $sContent .= "\t\t" . '<g:description><![CDATA[' . $aCurrentDiscount['description'] . ']]></g:description>' . "\n";
            }

            $sContent .= "\t\t" . '<g:promotion_effective_dates>' . BT_GmcProModuleTools::formatDateISO8601($aCurrentDiscount['date_from']) . '/' . BT_GmcProModuleTools::formatDateISO8601($aCurrentDiscount['date_to']) . '</g:promotion_effective_dates>' . "\n";
            $sContent .= "\t\t" . '<g:redemption_channel>ONLINE</g:redemption_channel>' . "\n";
            $sContent .= "\t\t" . '<g:promotion_display_dates>' . BT_GmcProModuleTools::formatDateISO8601($aCurrentDiscount['date_from']) . '/' . BT_GmcProModuleTools::formatDateISO8601($aCurrentDiscount['date_to']) . '</g:promotion_display_dates>' . "\n";

            if (
                !empty($fMinAmountPrice)
                && !empty($sCurrencyMinAmount)
            ) {
                $sContent .= "\t\t" . '<g:minimum_purchase_amount>' . $fMinAmountPrice . ' ' . $sCurrencyDiscount . '</g:minimum_purchase_amount>' . "\n";
            }

            //Use case for add tag for the percent off
            if (!empty($fAmountInPercent)) {
                $sContent .= "\t\t" . '<g:percent_off>' . $fAmountInPercent . '</g:percent_off>' . "\n";
            }

            //Use case for add tag for the amount off
            if (!empty($fAmountInCurrency)) {
                $sContent .= "\t\t" . '<g:money_off_amount>' . $fAmountInCurrency . '</g:money_off_amount>' . "\n";
            }

            // Use case for gift product
            if (!empty($aCurrentDiscount['gift_product'])) {
                $sContent .= "\t\t" . '<g:free_gift_item_id>' . $aCurrentDiscount['gift_product'] . '</g:free_gift_item_id>' . "\n";

                $oProduct = new Product($aCurrentDiscount['gift_product'], GMerchantCenterPro::$iCurrentLang);
                if (is_object($oProduct)) {
                    $sContent .= "\t\t" . '<g:free_gift_value>' . floatval(Product::getPriceStatic((int) $aCurrentDiscount['gift_product'], true, null, 2)) . '</g:free_gift_value>' . "\n";

                    //get the current lang id for the data feed
                    $iCurrentLang = Tools::getValue('id_lang');

                    $sContent .= "\t\t" . '<g:free_gift_description><![CDATA[' . $this->getProductDesc($oProduct->description[(int) $iCurrentLang], $oProduct->description_short[(int) $iCurrentLang], $oProduct->meta_description[(int) $iCurrentLang]) . ']]></g:free_gift_description>' . "\n";
                }
            }

            // Use case for promotion destination
            if (!empty($aPromotionDestination)) {
                foreach ($aPromotionDestination as $aCurrentDiscountChannel) {
                    foreach ($aCurrentDiscountChannel as $sCurrentDiscountChannel) {
                        $sContent .= "\t\t" . '<g:promotion_destination>' . $sCurrentDiscountChannel . '</g:promotion_destination>' . "\n";
                    }
                }
            }

            // Use case for quantity available for promotion and very important with Google Shopping actions to stop promotion diffusion when the quantity is 0
            if (!empty($iQuantity)) {
                $sContent .= "\t\t" . '<g:end_promo_max_applies>' . $iQuantity . '</g:end_promo_max_applies>' . "\n";
            }

            $sContent .= "\t" . '</item>' . "\n";
        }

        echo $sContent;
    }


    /**
     * returns a cleaned desc string
     *
     * @param int $iProdCatId
     * @param int $iLangId
     * @return string
     */
    public function getProductDesc($sShortDesc, $sLongDesc, $sMetaDesc)
    {
        // set product description
        switch (GMerchantCenterPro::$conf['GMCP_P_DESCR_TYPE']) {
            case 1:
                $sDesc = $sShortDesc;
                break;
            case 2:
                $sDesc = $sLongDesc;
                break;
            case 3:
                $sDesc = $sShortDesc . '<br />' . $sLongDesc;
                break;
            case 4:
                $sDesc = $sMetaDesc;
                break;
            default:
                $sDesc = $sLongDesc;
                break;
        }
        return (
            (function_exists('mb_substr') ? mb_substr(BT_GmcProModuleTools::cleanUp($sDesc), 0, 100) : Tools::substr(BT_GmcProModuleTools::cleanUp($sDesc), 0, 100)));
    }
}
