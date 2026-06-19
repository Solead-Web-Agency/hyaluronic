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

class BT_ProductcommentsReviews implements BT_IReviews
{
    /**
     *
     * @param array $aParams
     */
    public function __construct($aParams = array())
    {
    }


    /**
     * get the reviews from the ctrl object
     * @param int $iLangId
     * @return array of reviews
     */
    public function getReviews($iLangId)
    {
        return $this->buildGenericReviewsArray(BT_GmcProReviewsDao::getProductCommentReviews(), $iLangId);
    }

    /**
     * get the reviews from the co matched ctrl object
     *
     * @param array $aReviews
     * @param int $iLangId
     * @return array
     */
    public function buildGenericReviewsArray(array $aReviews, $iLangId)
    {
        $aGenericArray = array();

        foreach ($aReviews as $sKey => $aReview) {
            // Manage the product information to have the most accurate data feed possible
            if (!empty($aReview['id_product'])) {
                // Init the product object
                $oProduct = new Product($aReview['id_product']);

                // check if the product is still valid
                if (!empty($oProduct->active)) {
                    // get the availalbe data
                    $aGenericArray[$sKey]['sCustomerName'] = $aReview['customer_name'];
                    $aGenericArray[$sKey]['sDate'] = $aReview['date_add'];
                    $aGenericArray[$sKey]['sReview'] = $aReview['content'];
                    $aGenericArray[$sKey]['sTitle'] = $aReview['title'];
                    $aGenericArray[$sKey]['sReviewUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['id_product'], $iLangId);
                    $aGenericArray[$sKey]['sRating'] = $aReview['grade'];
                    $aGenericArray[$sKey]['sProductUrl'] = BT_GmcProModuleTools::getProductLink((int)$aReview['id_product'], $iLangId);
                    $aGenericArray[$sKey]['sProductName'] = $oProduct->name[$iLangId];

                    // USE case for the GTIN code // Same logic as the product data feed.
                    $sGtin = BT_GmcProModuleTools::getGtin(GMerchantCenterPro::$conf['GMCP_GTIN_PREF'], (array)$oProduct);

                    if (!empty($sGtin)) {
                        $aGenericArray[$sKey]['sGtin'] = $sGtin;
                    }

                    // USE case for the MPN
                    if (!empty($oProduct->reference)) {
                        $aGenericArray[$sKey]['sMpn'] = $oProduct->reference;
                    }

                    // USE case for the SKU
                    if (!empty($oProduct->supplier_reference)) {
                        $aGenericArray[$sKey]['sSku'] = $oProduct->supplier_reference;
                    }

                    // USE case for the brand
                    if (!empty($oProduct->manufacturer_name)) {
                        $aGenericArray[$sKey]['sManufacturer'] = $oProduct->manufacturer_name;
                    }

                    //Handle the review ID information
                    if (!empty($aReview['id_product_comment'])) {
                        $aGenericArray[$sKey]['iReviewId'] = $aReview['id_product_comment'];
                    }
                }
            }
        }

        return $aGenericArray;
    }
}
