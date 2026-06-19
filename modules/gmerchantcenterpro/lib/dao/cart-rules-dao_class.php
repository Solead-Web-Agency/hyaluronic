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

class BT_GmcProCartRulesDao
{
    /**
     * get available for datafeed
     *
     * @param array $sName
     * @return array of key value for
     */
    public static function getCartRules($sName = null, $sDateFrom = null, $sDateTo = null, $fMinAmount = null, $fValueMin = null, $fValueMax = null, $sTypeExport = null, $sCumulate = null)
    {
        $sQuery = 'SELECT * from `' . _DB_PREFIX_ . 'cart_rule` cr '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON (cr.`id_cart_rule` = crl.`id_cart_rule` AND crl.`id_lang` = ' . (int) GMerchantCenterPro::$oContext->cookie->id_lang . ') '
            // General exclusion to follow Google Prerequisites
            . 'WHERE cr.id_customer = 0 '
            . 'AND cr.quantity != 0 '
            . 'AND cr.active = 1 '
            . 'AND cr.group_restriction  = 0 '
            . 'AND cr.country_restriction  = 0 '
            . 'AND cr.carrier_restriction  = 0 '
            // exclusions configure by merchant
            . (!empty($sName) ? 'AND crl.name  like "%' . pSQL($sName) . '%"' : '')
            . (!empty($sDateFrom) ? 'AND cr.date_from  >= "' . pSQL($sDateFrom) . '"' : '')
            . (!empty($sDateTo) ? ' AND cr.date_to  <= "' . pSQL($sDateTo)  . '"' : '')
            . (!empty($fMinAmount) ? 'AND cr.minimum_amount  = "' . pSQL($fMinAmount) . '"' : '');

        if (!empty($sTypeExport)) {
            if ($sTypeExport == "amount") {
                $sQuery .= (!empty($fValueMin) ? 'AND cr.reduction_amount  >= "' . pSQL($fValueMin) . '"' : '');
                $sQuery .= (!empty($fValueMax) ? 'AND cr.reduction_amount  <= "' . pSQL($fValueMax) . '"' : '');
            }
            if ($sTypeExport == "percent") {
                $sQuery .= (!empty($fValueMin) ? 'AND cr.reduction_percent  >= "' . pSQL($fValueMin) . '"' : '');
                $sQuery .= (!empty($fValueMax) ? 'AND cr.reduction_percent  <= "' . pSQL($fValueMax) . '"' : '');
            }
        }

        if ($sCumulate != "all") {
            ($sCumulate == 'nocumulated' ? $sQuery .= 'AND cr.cart_rule_restriction  = "1"' : $sQuery .= 'AND cr.cart_rule_restriction  = "0"');
        }

        $aAvailableDiscount = Db::getInstance()->ExecuteS($sQuery);

        return $aAvailableDiscount;
    }

    /**
     * detect if the cart rule has item associate in oder to manage the value offer_type in the XML
     *
     * @param string $iCartRuleId
     * @return array of  item and cart rule
     */
    public static function hasAssociateItem($iCartRuleId)
    {
        $sQuery = 'SELECT id_item, id_cart_rule, crpr.type from `' . _DB_PREFIX_ . 'cart_rule_product_rule` crpr '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_product_rule_group` crprg ON (crprg.`id_product_rule_group` = crpr.`id_product_rule_group`) '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule_product_rule_value` crprv ON (crprv.`id_product_rule` = crpr.`id_product_rule`) '
            . 'WHERE crprg.id_cart_rule = ' . (int) $iCartRuleId . '';

        $aHasAssociateItem = Db::getInstance()->ExecuteS($sQuery);

        return $aHasAssociateItem;
    }

    /**
     * set the table for association in the main data feed
     *
     * @param array $aPost
     * @return array of key value for
     */
    public static function setAssocCartRules($iDicountId, $iProductId)
    {
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association' . '` (`id_discount`, `id_product`) VALUES (' . (int) $iDicountId . ', ' . (int) $iProductId . ')');
    }

    /**
     * clean the association table
     *
     * @param array $aPost
     * @return array of key value for
     */
    public static function cleanAssocCartRules()
    {
        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association`');
        Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association` AUTO_INCREMENT=0');
    }

    /**
     *get id cart_rule the id cart rule corresponding as g:promotion_id
     *
     * @param array $iProductId
     * @return array of key value for
     */
    public static function getAssocCartRules($iProductId)
    {
        return Db::getInstance()->ExecuteS('SELECT id_discount FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association` WHERE `id_product` = ' . (int) $iProductId . '');
    }

    /**
     * get all id cart rules available for Google 
     * @param array $iProductId
     * @return array of key value for
     */
    public static function getGoogleCartRules($iProductId)
    {
        return Db::getInstance()->getRow('SELECT id_discount FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association` WHERE `id_product` = ' . (int) $iProductId . '');
    }

    /**
     * get the google channel 
     * @param array $iProductId
     * @return array of key value for
     */
    public static function getGoogleChannel($iDicountId)
    {
        return Db::getInstance()->getRow('SELECT channel FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association` WHERE `id_discount` = ' . (int) $iDicountId . '');
    }

    /**
     * get distinct number of discount code
     *
     * @param $iShopId
     * @return int number of distinct code
     */
    public static function getCartRulesId()
    {
        $iNumberOfDiscount = Db::getInstance()->GetRow('SELECT COUNT(DISTINCT id_discount) AS NumberOfDiscount FROM  `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_discount_association`');

        return $iNumberOfDiscount['NumberOfDiscount'];
    }
}
