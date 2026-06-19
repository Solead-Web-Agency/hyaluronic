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

class BT_GmcProCustomLabelDao
{
    /**
     * insert a specific tag
     *
     * @param int $iShopId
     * @param string $sLabelName
     * @param string $sLabelType
     * @return int
     */
    public static function insertGmcTag($iShopId, $sLabelName, $sLabelType, $bActive, $iOrder = null, $sDateEnd = null)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` (`id_shop`, `name`, `type`, `active`'
            . (!empty($iOrder) ? ', `position`' : '')
            . (!empty($sDateEnd) ? ', `end_date`' : '')
            . ')'
            . 'VALUES (' . $iShopId . ',"' . pSQL($sLabelName) . '", "' . pSQL($sLabelType) . '", "' . pSQL($bActive) . '"'
            . (!empty($iOrder) ? ',"' . (int) $iOrder . '"' : '')
            . (!empty($sDateEnd) ? ',"' . pSQL($sDateEnd) . '"' : '')
            . ')';

        Db::getInstance()->Execute($sQuery);

        return Db::getInstance()->Insert_ID();
    }

    /**
     * get the active tags
     * 
     * @param $iShopId
     * @return mixed
     */
    public static function getActiveTag($iShopId)
    {
        $sQuery = 'SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` WHERE `id_shop` = "' . (int) $iShopId . '" AND `active` = 1';
        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * returns specific categories or brands or suppliers for one tag
     *
     * @param int $iShopId
     * @param int $iTagId
     * @param string $sTableType
     * @param string $sFieldType
     * @return array
     */
    public static function getGmcTags($iShopId = null, $iTagId = null, $sTableType = null, $sFieldType = null)
    {
        // set
        $aReturn = array();

        $sQuery = 'SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags' . ($sTableType !== null ? '_' . $sTableType : '') . '` WHERE 1 = 1 ' . ($iShopId !== null ? ' AND id_shop = ' . (int) $iShopId : '') . ($iTagId !== null ? ' AND `id_tag` = ' . (int) $iTagId : '' . ' ORDER BY `position`  ASC');

        $aResult = Db::getInstance()->ExecuteS($sQuery);

        if (!empty($aResult) && $sFieldType !== null) {
            foreach ($aResult as $aCat) {
                $aReturn[] = $aCat['id_' . $sFieldType];
            }
        } else {
            $aReturn = $aResult;
        }

        return $aReturn;
    }

    /**
     * @param $iShopId
     * @return mixed
     */
    public static function getTagDate($iShopId)
    {
        $sQuery = 'SELECT `id_tag`,`end_date` FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` WHERE `id_shop` = "' . (int) $iShopId . '" AND `end_date` != "0000-00-00"';
        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * update a specific tag
     *
     * @param int $iTagId
     * @param string $sLabelName
     * @param string $sLabelType
     * @return bool
     */
    public static function updateGmcTag($iTagId, $sLabelName, $sLabelType, $bActive, $iOrder = null, $sDateEnd = null)
    {
        $sQuery = 'UPDATE `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` SET `name` = "' . pSQL($sLabelName) . '", `type` = "' . pSQL($sLabelType) . '"'
            . ',`active` = "' . pSQL($bActive) . ' " '
            . (!empty($iOrder) ? ',`position` = "' . (int) $iOrder . '"' : '')
            . (!empty($sDateEnd) ? ',`end_date` ="' . pSQL($sDateEnd) . '"' : '')
            . 'WHERE `id_tag` = ' . (int) $iTagId;
        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * update a specific tag
     * @param int $iTagId
     * @param int $iStatus
     * @return
     */
    public static function updateTagActivation($iTagId, $iStatus)
    {
        $sQuery = 'UPDATE `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` SET `active` = "' . pSQL($iStatus) . '" WHERE `id_tag` = "' . (int) $iTagId . '"';
        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * update a tag date
     * @param int $iTagId
     * @param int $iStatut
     * @return
     */
    public static function updateProcessDate($iTagId, $iStatut, $iPosition)
    {
        $sQuery = 'UPDATE `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` SET `active` = "' . (int) $iStatut . '", `position` = "' . (int) $iPosition . '" WHERE `id_tag` = "' . (int) $iTagId . '"';
        return Db::getInstance()->Execute($sQuery);
    }

    /**
     * get tag position
     * @param int $iTagId
     * @return int position
     */
    public static function getTagPosition($iTagId)
    {
        $sQuery = 'SELECT `position` FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags`  WHERE `id_tag` = "' . (int) $iTagId . '"';
        return Db::getInstance()->getRow($sQuery);
    }

    /**
     * insert categories / brands / manufacturers for a specific tag
     *
     * @param int $iTagId
     * @param int $iCatId
     * @param string $sTableName
     * @param string $sFieldType
     * @return int
     */
    public static function insertGmcCatTag($iTagId, $iCatId, $sTableName, $sFieldType)
    {
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_' . $sTableName . '` (`id_tag`, `id_' . pSQL($sFieldType) . '`) VALUES (' . (int) $iTagId . ', ' . (int) $iCatId . ')');
    }

    /**
     * insert dynamic feature for a specific tag
     *
     * @param int $iTagId
     * @param int $iFeatureId
     * @return int
     */
    public static function insertGmcpDynFeatureTag($iTagId, $iFeatureId)
    {
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_features` (`id_tag`, `id_feature`, `id_shop`) VALUES ("' . (int) $iTagId . '", "' . (int) ($iFeatureId) . '", "' . (int) (GMerchantCenterPro::$iShopId) . '" )');
    }


    /**
     * method delete a specific tag
     *
     * @param int $iTagId
     * @param array $aLabelList
     * @return bool
     */
    public static function deleteGmcTag($iTagId, array $aLabelList = null, $sCustomLabelType = null)
    {
        if (Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` WHERE `id_tag` = ' . (int) $iTagId)) {
            if (!empty($aLabelList)) {
                foreach ($aLabelList as $sTableName => $sFieldType) {
                    self::deleteGmcCatTag($iTagId, $sTableName, $sCustomLabelType);
                }
            }
        }
    }

    /**
     * update the position between 2 tags
     *
     * @param int $iTagId
     * @param array $aLabelList
     * @return bool
     */
    public static function updatePositionTag($iTagId, $iPosition)
    {
        $sQuery = 'UPDATE `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` SET `position` = "' . (int) $iPosition . '"'
            . ' WHERE `id_tag` = ' . (int) $iTagId;

        Db::getInstance()->Execute($sQuery);
    }

    /**
     * get last id
     *
     * @param int $iTagId
     * @param array $aLabelList
     * @return int
     */
    public static function getLastId()
    {
        $sQuery = 'SELECT `position` FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` ORDER BY `position` DESC LIMIT 1';

        $aRow = Db::getInstance()->ExecuteS($sQuery);

        return (int) $aRow[0]['position'];
    }

    /**
     * delete a specific related categories / brands / manufacturers tag
     *
     * @param int $iTagId
     * @param string $sTableType
     * @return bool
     */
    public static function deleteGmcCatTag($iTagId, $sTableType)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_' . pSQL($sTableType) . '` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * feature save for one custom_label_id
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getFeatureSave($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT id_feature FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_features` WHERE `id_tag` = ' . (int) $iTagId . ' AND `id_shop` =' . (int) GMerchantCenterPro::$iShopId);
    }

    /**
     * delete one feature
     *
     * @param int $iTagId
     * @return bool
     */
    public static function deleteFeatureSave($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_features` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * insert add specific product id for one product
     *
     * @param int $iTagId
     * @param int $sProductId
     * @return int
     */
    public static function insertGmcpProductTag($iTagId, $sProductId, $sProductName)
    {
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_products` (`id_tag`, `id_product`, `product_name`) VALUES ("' . (int) $iTagId . '", "' . (int) ($sProductId) . '" , "' . pSQL(($sProductName)) . '")');
    }

    /**
     * delete feature save for one custom_label_id
     *
     * @param int $iTagId
     * @param string $sTableType
     * @return bool
     */
    public static function deleteGmcpProductTag($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_products` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * return id_product for the tag
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getGmcTagsProduct($iTagId)
    {
        return Db::getInstance()->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_products` WHERE `id_tag` = ' . (int) ($iTagId));
    }

    /**
     * insert dynamic category
     *
     * @param int $iTagId
     * @param int $iCatId
     * @return int
     */
    public static function insertDynamicCat($iTagId, $iCatId)
    {
        Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_categories` (`id_tag`, `id_category`, `id_shop`) VALUES ("' . (int) $iTagId . '", "' . (int) ($iCatId) . '", "' . (int) (GMerchantCenterPro::$iShopId) . '")');
    }

    /**
     * clean value for dynamic category
     *
     * @param int $iTagId
     * @param string $sTableType
     * @return bool
     */
    public static function deleteDynamicCat($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_categories` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * category id  for the tag
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicCat($iTagId)
    {
        $aResult = Db::getInstance()->ExecuteS('SELECT id_category FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_categories` WHERE `id_tag` = ' . (int) ($iTagId) . ' AND `id_shop` = ' . (int) GMerchantCenterPro::$iShopId);

        if (!empty($aResult)) {
            foreach ($aResult as $aCat) {
                $aReturn[] = $aCat['id_category'];
            }
        } else {
            $aReturn = $aResult;
        }

        return $aReturn;
    }

    /**
     * insert dynamic category
     *
     * @param int $iTagId
     * @param int $iCatId
     * @return int
     */
    public static function insertDynamicNew($iTagId, $sDate, $iProductId)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_new_product` (`id_tag`, `from_date`, `id_product`, `id_shop`) VALUES ("' . (int) $iTagId . '", "' . pSQL($sDate) . '", "' . (int) $iProductId . '" , "' . (int) GMerchantCenterPro::$iShopId . '")';

        Db::getInstance()->Execute($sQuery);
    }

    /**
     * return id_product for the tag
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicNew($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT from_date FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_new_product` WHERE `id_tag` = ' . (int) ($iTagId) . ' AND `id_shop` = ' . (int) GMerchantCenterPro::$iShopId);
    }

    /**
     * insert dynamic price range
     *
     * @param int $iTagId
     * @param float $fPriceMin
     * @param float $fPriceMax
     * @param int $iProductId
     * @return int
     */
    public static function insertDynamicPriceRange($iTagId, $fPriceMin, $fPriceMax, $iProductId)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_price_range` (`id_tag`, `price_min`, `price_max` ,`id_product`) VALUES ("' . (int) $iTagId . '", "' . (float) $fPriceMin . '", "' . (float) $fPriceMax . '" , "' . (int) $iProductId . '")';

        Db::getInstance()->Execute($sQuery);
    }

    /**
     * return id_product for the tag
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicPriceRange($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_price_range` WHERE `id_tag` = ' . (int) ($iTagId));
    }

    /**
     * clean value for dynamic categorie
     *
     * @param int $iTagId
     * @param string $sTableType
     * @return bool
     */
    public static function deleteDynamicNew($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_new_product` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * insert dynamic category
     *
     * @param int $iTagId
     * @param int $iCatId
     * @return int
     */
    public static function insertDynamicBestSales($iTagId, $fUnitValue, $sUnit, $sDateFrom = null, $sDateTo = null, $sProductIds)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_best_sale` (`id_tag`, `amount` , `unit` , `start_date` , `end_date` ,`id_product`, `id_shop`)
		 VALUES ("' . (int) $iTagId . '", "' . (float) $fUnitValue . '", "' . pSQL($sUnit) . '", "' . pSQL($sDateFrom) . '", "' . pSQL($sDateTo) . '", "' . pSQL($sProductIds) . '" , "' . (int) GMerchantCenterPro::$iShopId . '")';

        Db::getInstance()->Execute($sQuery);
    }


    /**
     * record for best sales from database
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicBestSales($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_best_sale` WHERE `id_tag` = ' . (int) ($iTagId) . ' AND `id_shop`=' . (int) GMerchantCenterPro::$iShopId);
    }

    /**
     * clean value for dynamic best sales
     *
     * @param int $iTagId
     * @return bool
     */
    public static function deleteDynamicBestSales($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_best_sale` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * clean value for dynamic best sales
     *
     * @param int $iTagId
     * @return bool
     */
    public static function deleteDynamicPriceRange($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_price_range` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * Get productBestSales with set parameters for one TAG
     *
     * @param string $sNewDateFrom
     * @return string of ids
     */
    public static function getNewProducts($sNewDateFrom)
    {
        $sQuery = 'SELECT DISTINCT p.id_product FROM `' . _DB_PREFIX_ . 'product` p' . ' WHERE p.date_add >= "' . $sNewDateFrom . '"';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * Get getPriceRangeProduct with set parameters for one TAG
     *
     * @param string $fPriceMin
     * @param string $fPriceMax
     * @return string of ids
     */
    public static function getPriceRangeProduct($fPriceMin, $fPriceMax)
    {
        $sQuery = 'SELECT DISTINCT p.id_product FROM `' . _DB_PREFIX_ . 'product` p' . ' WHERE p.price >= "' . $fPriceMin . '"' . ' AND p.price <= "' . $fPriceMax . '"';

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * Get productBestSales with set parameters for one TAG
     *
     * @param string $sBestSaleType
     * @param float $fBestSaleAmount
     * @param string $sBestSaleStartDate
     * @param string $sBestSaleStartEnd
     * @return string of ids
     */
    public static function getProductBestSales($sBestSaleType, $fBestSaleAmount, $sBestSaleStartDate = null, $sBestSaleEndDate = null)
    {
        if ($sBestSaleType == "unit") {
            $sQuery = 'SELECT DISTINCT pod.product_id, SUM(pod.product_quantity) as qty FROM `' . _DB_PREFIX_ . 'order_detail` pod'
                . ' LEFT JOIN `' . _DB_PREFIX_ . 'orders` po ON (po.id_order = pod.id_order)';

            // manage date picker vallue if date range is filled out
            $sQuery .= (!empty($sBestSaleStartDate) ? ' WHERE po.date_add >= "' . $sBestSaleStartDate . '"' : '');
            $sQuery .= (!empty($sBestSaleEndDate) ? ' AND po.date_add <= "' . $sBestSaleEndDate . '"' : '');

            $sQuery .= 'GROUP BY pod.product_id HAVING qty >= ' . (int)$fBestSaleAmount;
        } elseif ($sBestSaleType == "price") {
            $sQuery = 'SELECT pod.product_id, SUM(pod.total_price_tax_incl) as total_sale_amount FROM `' . _DB_PREFIX_ . 'order_detail` pod LEFT JOIN `' . _DB_PREFIX_ . 'orders` po ON (po.id_order = pod.id_order)';
            $sQuery .= (!empty($sBestSaleStartDate) ? ' WHERE po.date_add >= "' . pSQL($sBestSaleStartDate) . '"' : '');

            // to manage if the merchant set date range or just start or end date
            if (!empty($sBestSaleStartDate)) {
                $sQuery .= ' AND po.date_add <= "' . pSQL($sBestSaleEndDate) . '"';
            } elseif (!empty($sBestSaleStartDate) && !empty($sBestSaleEndDate)) {
                $sQuery .= ' WHERE po.date_add <= "' . pSQL($sBestSaleEndDate) . '"';
            }

            $sQuery .= ' GROUP BY pod.product_id HAVING SUM(pod.total_price_tax_incl) >= ' . (float) $fBestSaleAmount . ' ORDER BY total_sale_amount DESC';
        }

        return Db::getInstance()->ExecuteS($sQuery);
    }

    /**
     * insert dynamic last product ordered
     *
     * @param int $iTagId
     * @param string $sDateFrom
     * @param string $sDateTos
     * @param string $sProductIds
     * @return int
     */
    public static function insertDynamicLastProductOrdered($iTagId, $sDateFrom = null, $sDateTo = null, $sProductIds)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_last_product_ordered` (`id_tag` , `start_date` , `end_date` ,`id_product`)
		 VALUES ("' . (int) $iTagId . '", "' . pSQL($sDateFrom) . '", "' . pSQL($sDateTo) . '", "' . pSQL($sProductIds) . '")';

        Db::getInstance()->Execute($sQuery);
    }

    /**
     * clean value for dynamic last ordered
     *
     * @param int $iTagId
     * @return bool
     */
    public static function deleteDynamicLastProductOrdered($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_last_product_ordered` WHERE `id_tag` = ' . (int) $iTagId);
    }


    /**
     * record for last sales from database
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicLastProductOrdered($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_last_product_ordered` WHERE `id_tag` = ' . (int) ($iTagId));
    }

    /**
     * insert dynamic product promotion
     *
     * @param int $iTagId
     * @param string $sDateFrom
     * @param string $sDateTos
     * @param string $sProductIds
     * @return int
     */
    public static function insertDynamicPromotion($iTagId, $sDateFrom = null, $sDateTo = null, $sProductIds)
    {
        $sQuery = 'INSERT INTO `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_promotion` (`id_tag` , `start_date` , `end_date` ,`id_product`)
		 VALUES ("' . (int) $iTagId . '", "' . pSQL($sDateFrom) . '", "' . pSQL($sDateTo) . '", "' . pSQL($sProductIds) . '")';

        Db::getInstance()->Execute($sQuery);
    }

    /**
     * clean value for dynamic promotion
     *
     * @param int $iTagId
     * @return bool
     */
    public static function deleteDynamicPromotion($iTagId)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_promotion` WHERE `id_tag` = ' . (int) $iTagId);
    }

    /**
     * record for promotion cl
     *
     * @param int $iTagId
     * @return bool
     */
    public static function getDynamicLastDynamicPromotion($iTagId)
    {
        return Db::getInstance()->GetRow('SELECT * FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_promotion` WHERE `id_tag` = ' . (int) ($iTagId));
    }

    /**
     * returns Google tags for XML
     *
     * @param int $iProdId
     * @param array $aCat
     * @param int $iManufacturerId
     * @param int $iSupplierId
     * @param int $iLangId
     * @return array
     */
    public static function getTagsForXml($iProdId, $aCat, $iManufacturerId, $iSupplierId, $iLangId)
    {
        $sIn = implode(',', array_map('intval', $aCat));

        $sQuery =
            '(SELECT distinct(gt.id_tag), fvl.value as name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_features` gtdf ON (gt.id_tag = gtdf.id_tag)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON (gtdf.id_feature = fl.id_feature)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON (fl.id_feature = fp.id_feature)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON (fp.id_feature_value = fvl.id_feature_value)'
            . ' WHERE fp.id_product = ' . (int) ($iProdId) . ''
            . ' AND fl.id_lang = ' . (int) $iLangId . ''
            . ' AND fvl.id_lang = ' . (int) $iLangId . ''
            . ' AND gt.active = 1 )'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_products` gtp ON (gt.id_tag = gtp.id_tag)'
            . ' WHERE gtp.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag),cl.name as name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_categories` gtdc ON (gt.id_tag = gtdc.id_tag)'
            . ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.id_category = gtdc.id_category)'
            . ' WHERE cl.id_lang = ' . (int) $iLangId . ''
            . ' AND cl.id_category in ( ' . pSQL($sIn) . ')'
            . ' AND gt.active = 1)'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_best_sale` gtdbs ON (gt.id_tag = gtdbs.id_tag)'
            . ' WHERE gtdbs.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_new_product` gtdnp ON (gt.id_tag = gtdnp.id_tag)'
            . ' WHERE gtdnp.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag), gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_cats` gtc ON (gt.id_tag = gtc.id_tag)'
            . ' WHERE gtc.id_category in ( ' . pSQL($sIn) . ')'
            . ' AND gt.active = 1)'

            . ' UNION'

            . ' (SELECT distinct(gt.id_tag), gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_brands` gtb ON (gt.id_tag = gtb.id_tag)'
            . ' WHERE gtb.id_brand = ' . (int) $iManufacturerId
            . ' AND gt.active = 1)'

            . ' UNION'

            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_price_range` gtdpr ON (gt.id_tag = gtdpr.id_tag)'
            . ' WHERE gtdpr.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'

            . ' UNION '
            
            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_last_product_ordered` gtdblo ON (gt.id_tag = gtdblo.id_tag)'
            . ' WHERE gtdblo.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'

            . ' UNION '

            . '(SELECT distinct(gt.id_tag),gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_dynamic_promotion` gtdp ON (gt.id_tag = gtdp.id_tag)'
            . ' WHERE gtdp.id_product = ' . (int) ($iProdId) . ''
            . ' AND gt.active = 1)'
            
            . ' UNION '

            . ' (SELECT distinct(gt.id_tag), gt.name, gt.type, gt.position'
            . ' FROM `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags` gt'
            . ' LEFT JOIN `' . _DB_PREFIX_ . Tools::strtolower(_GMCP_MODULE_NAME) . '_tags_suppliers` gts ON (gt.id_tag = gts.id_tag)'
            . ' WHERE gts.id_supplier IN (SELECT distinct(id_supplier) FROM `' . _DB_PREFIX_ . 'product_supplier` WHERE id_product = ' . (int) $iProdId . ')'
            . ' AND gt.active = 1 GROUP BY gt.id_tag)'
            . ' ORDER BY position ASC';

        $aData = Db::getInstance()->ExecuteS($sQuery);

        $aTags = array('custom_label' => array());

        if (!empty($aData) && is_array($aData)) {
            foreach ($aData as $row) {
                $aTags['custom_label'][] = $row['name'];
            }
        }
        return $aTags;
    }

    /**
     * returns Google tags for XML
     *
     * @param int $iTagId
     * @param array $aFilter
     */
    public static function getCustomLabelProductIds($iTagId, $aFilter)
    {
        $sQuery = 'SELECT ' . $aFilter['sFieldSelect'] . ' FROM `' . _DB_PREFIX_ . $aFilter['sPopulateTable'] . '` WHERE id_tag= ' . (int) $iTagId;

        $aProductIds = Db::getInstance()->ExecuteS($sQuery);

        if ($aFilter['bUsePsTable'] == 1 && !empty($aProductIds)) {
            foreach ($aProductIds as $aFilterID) {
                $sQuery = 'SELECT id_product FROM `' . _DB_PREFIX_ . pSQL($aFilter['sPsTable']) . '` WHERE ' . pSQL($aFilter['sPsTableWhere']) . '=' . (int) $aFilterID[$aFilter['sFieldSelect']];
                $aProductIds = Db::getInstance()->ExecuteS($sQuery);
            }
        }

        foreach ($aProductIds as $aProductId) {
            array_push($aProductIds, $aProductId['id_product']);
        }

        return $aProductIds;
    }
}
