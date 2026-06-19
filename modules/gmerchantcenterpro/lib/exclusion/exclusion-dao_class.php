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

class BT_GmcProExclusionDao
{
    /**
     * method add the exclusion rules
     *
     * @param bool $bActive
     * @param int $iShopId
     * @param string $sName
     * @param string $sType
     * @param string $sValue
     * @return bool
     */
    public static function addExclusionRule($bActive, $iShopId, $sName, $sType, $sValue)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` (`status`, `id_shop`, `name`, `type`, `exclusion_value`)'
            . 'VALUES ("' . (int)$bActive . '", "' . (int)$iShopId . '", "' . pSQL($sName) . '", "' . pSQL($sType) . '", "' . pSQL($sValue) . '")';

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * method update the exclusion rules
     *
     * @param bool $bActive
     * @param int $iShopId
     * @param string $sName
     * @param string $sType
     * @param string $sValue
     * @param int $iRuleId
     * @return bool
     */
    public static function updateExclusionRule($bActive, $iShopId, $sName, $sType, $sValue, $iRuleId)
    {
        $sQuery = 'UPDATE `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` SET `status` = "' . (int)$bActive . '",'
            . ' `id_shop`=' . (int)$iShopId . ','
            . ' `name`="' . pSQL($sName) . '",'
            . ' `type`="' . pSQL($sType) . '",'
            . ' `exclusion_value`="' . pSQL($sValue) . '"'
            . ' WHERE `id` = ' . (int)$iRuleId;

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * method update a specific tag
     *
     * @param int $iTagId
     * @param string $sLabelName
     * @param string $sLabelType
     * @return bool
     */
    public static function updateRulesStatus($iRulesId, $sType, $bActivate)
    {
        if ($sType != 'bulk') {
            $sQuery = 'UPDATE `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` SET `status` = "' . (int)$bActivate . '" WHERE `id` = ' . (int)$iRulesId;

            $bSuccess = Db::getInstance()->Execute($sQuery);
        } else {
            $aRules = explode(',', $iRulesId);

            foreach ($aRules as $aRule) {
                $sQuery = 'UPDATE `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` SET `status` = "' . (int)$bActivate . '" WHERE `id` = ' . (int)$aRule;

                $bSuccess = Db::getInstance()->Execute($sQuery);
            }
        }

        return $bSuccess;
    }

    /**
     * method returns the exclusion rules
     *
     * @return mixed :
     */
    public static function getExclusionRules()
    {
        $sQuery = 'SELECT * ' . ' FROM `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` ';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * method returns the exclusion rule from an id
     *
     * @param int $iRuleId
     * @return mixed :
     */
    public static function getExclusionRulesById($iRuleId)
    {
        $sQuery = 'SELECT * ' . ' FROM `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` ' . ' WHERE `id`= ' . (int)$iRuleId;

        return Db::getInstance()->getRow($sQuery);
    }

    /**
     * method returns the exclusion rules
     *
     * @return mixed :
     */
    public static function deleteExclusionRules($iRulesId, $sType)
    {

        if ($sType != 'bulk') {
            $sQuery = 'DELETE '
                . ' FROM `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` '
                . 'WHERE `id`=' . (int)$iRulesId;

            $bSuccess = Db::getInstance()->Execute($sQuery);
        } else {
            $aRules = explode(',', $iRulesId);

            foreach ($aRules as $aRule) {
                $sQuery = 'DELETE '
                    . ' FROM `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion` '
                    . ' WHERE `id`=' . (int)$aRule;

                $bSuccess = Db::getInstance()->Execute($sQuery);
            }
        }

        return $bSuccess;
    }

    /**
     * method add the exclusion rules
     *
     * @param string $sType
     * @param string $sValue
     * @return bool
     */
    public static function addTmpDataRules($iShopId, $sType, $sValue)
    {
        return (
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'gmcp_tmp_rules` (`id_shop`, `type`, `exclusion_values`)' . 'VALUES (' . (int)$iShopId . ', "' . pSQL($sType) . '", "' . pSQL($sValue) . '")')
        );
    }

    /**
     * getTmpRules
     *
     * @param string $sType
     * @param string $sValue
     * @return bool
     */
    public static function getTmpRules()
    {
        $sQuery = 'SELECT * ' . ' FROM `' . _DB_PREFIX_ . 'gmcp_tmp_rules` ';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * clean the tmp rules
     * @return bool
     */
    public static function cleanTmpRules()
    {
        $sQuery = 'DELETE '
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_tmp_rules` ';

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * clean the tmp rules
     * @return bool
     */
    public static function resetIncrement()
    {
        $sQuery = 'ALTER TABLE `' . _DB_PREFIX_ . 'gmcp_tmp_rules` AUTO_INCREMENT = 1';

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * delete a specific tmp rules
     * @param int $iRulesId
     * @return bool
     */
    public static function deleteTmpRules($iRulesId)
    {
        $sQuery = 'DELETE'
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_tmp_rules` '
            . ' WHERE `id` = ' . (int)$iRulesId
            . ' AND `id_shop` = ' . (int)GMerchantCenterpro::$iShopId;

        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * get the products from a or somes categories
     *
     * @param array $aCategories
     * @param int $ishopId
     */
    public static function getProductFromCategories($aCategories, $ishopId)
    {
        $sQuery = 'SELECT ps.id_product '
            . ' FROM `' . _DB_PREFIX_ . 'product` p '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON p.`id_product` = ps.`id_product`'
            . ' WHERE ps.`id_category_default` IN (' . implode(',', $aCategories) . ')'
            . ' AND ps.`id_shop`=' . $ishopId;

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * get the products from a or somes manufacturers
     *
     * @param array $aManufacturer
     * @param int $ishopId
     */
    public static function getProductFromManufacturers($aManufacturer, $ishopId)
    {
        $sQuery = 'SELECT p.id_product'
            . ' FROM `' . _DB_PREFIX_ . 'product` p '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer_shop` ms ON p.`id_manufacturer` = ms.`id_manufacturer`'
            . ' WHERE ms.`id_manufacturer` IN (' . implode(',', $aManufacturer) . ')'
            . ' AND ms.`id_shop`=' . $ishopId;

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * get the products from a or somes suppliers
     *
     * @param array $aSuppliers
     * @param int $ishopId
     */
    public static function getProductFromSuppliers($aSuppliers, $ishopId)
    {
        $sQuery = 'SELECT p.id_product'
            . ' FROM `' . _DB_PREFIX_ . 'product` p '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'supplier_shop` ss ON p.`id_supplier` = ss.`id_supplier`'
            . ' WHERE ss.`id_supplier` IN (' . implode(',', $aSuppliers) . ')'
            . ' AND ss.`id_shop`=' . $ishopId;

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * get the products from a or somes suppliers
     *
     * @param string $sType
     * @param string $sSentences
     */
    public static function getProductFromWords($sType, $sSentences)
    {
        $sQuery = 'SELECT p.id_product'
            . ' FROM `' . _DB_PREFIX_ . 'product` p '
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON p.`id_product` = pl.`id_product`';

        if ($sType == 'title') {
            $sQuery .= 'WHERE `name` LIKE  \'%' . $sSentences . '%\'';
        }
        if ($sType == 'description') {
            $sQuery .= 'WHERE `description` LIKE  \'%' . $sSentences . '%\''
                . ' OR `description_short` LIKE  \'%' . $sSentences . '%\'';
        }
        if ($sType == 'both') {
            $sQuery .= 'WHERE `name` LIKE  \'%' . $sSentences . '%\''
                . ' OR `description` LIKE  \'%' . $sSentences . '%\''
                . ' OR `description_short` LIKE  \'%' . $sSentences . '%\'';
        }

        $sQuery .= ' AND pl.id_lang = ' . GMerchantCenterpro::$iCurrentLang
            . ' AND pl.id_shop = ' . GMerchantCenterpro::$iShopId
            . ' GROUP BY  p.id_product';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * method get the last rule id
     *
     * @return int
     */
    public static function getLastRuleId()
    {
        return Db::getInstance()->getRow('SELECT MAX(id) as last_id FROM `' . _DB_PREFIX_ . 'gmcp_advanced_exclusion`');
    }

    /**
     * method add the exclusion rules
     *
     * @param int $iIdRule
     * @param int $iProductId
     * @param int $iProductAttribute
     * @return mixed
     */
    public static function addProductExcluded($iIdRule, $iProductId, $iProductAttribute)
    {
        return Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'gmcp_product_excluded` (`id_rule`, `id_product`, `id_product_attribute`)'
            . 'VALUES (' . (int)$iIdRule . ',' . (int)$iProductId . ', ' . (int)$iProductAttribute . ')');
    }

    /**
     * method delete the product exluded when we deactive or delete rule
     *
     * @param int $iIdRuleId
     * @return bool
     */
    public static function deleteProductExcluded($iIdRuleId)
    {
        $sQuery = 'DELETE'
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_product_excluded` '
            . ' WHERE `id_rule` = ' . (int)$iIdRuleId;

        return Db::getInstance()->Execute($sQuery);

    }

    /**
     * method returns the all excluded rules
     *
     * @return mixed :
     */
    public static function getProductExcluded()
    {
        $sQuery = 'SELECT * '
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_product_excluded` ';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * method returns the all the product excluded for a rule
     *
     * @param int $iIdRuleId
     * @return mixed :
     */
    public static function getProductExcludedById($iIdRuleId)
    {
        $sQuery = 'SELECT * '
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_product_excluded` '
            . ' WHERE `id_rule` = ' . (int)$iIdRuleId;

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * count the excluded product
     *
     * @return bool
     */
    public static function isExcludedProduct()
    {
        $sQuery = 'SELECT COUNT(*) as nb'
            . ' FROM `' . _DB_PREFIX_ . 'gmcp_product_excluded` ';

        $aResult = Db::getInstance()->ExecuteS($sQuery);

        return !empty($aResult[0]['nb']) ? 1 : 0;
    }

}