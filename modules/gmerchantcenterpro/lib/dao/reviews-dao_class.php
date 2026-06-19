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

class BT_GmcProReviewsDao
{
    /**
     * get product comment reviews
     *
     * @return array of key value for
     */
    public static function getProductCommentReviews()
    {
        return Db::getInstance()->ExecuteS('SELECT * from `' . _DB_PREFIX_ . 'product_comment`');
    }

    /**
     * get product comment reviews
     *
     * @param int $iLangId
     * 
     * @return array of key value for
     */
    public static function getGsrReviews($iLangId)
    {
        $sQuery = 'SELECT * from `' . _DB_PREFIX_ . 'gsr_rating` rt' . ' INNER JOIN `' . _DB_PREFIX_ . 'gsr_review` rw ON (rt.`RTG_ID` = rw.`RTG_ID`) WHERE RTG_SHOP_ID = ' . (int)GMerchantCenterPro::$iShopId . ' AND RVW_LANG_ID=' . $iLangId;

        return Db::getInstance()->ExecuteS($sQuery);
    }
}
