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

class BT_XmlReviews extends BT_BaseXml
{
    /**
     * @param array $aParams
     * @param string $sType : define the tpy of the object we need to load for product or combination product
     */
    public function __construct($aParams = array())
    {
        require_once(_GMCP_PATH_LIB_DAO . 'reviews-dao_class.php');
    }

    public function __destruct(){}


    /**
     * load reviews
     *
     * @return array
     */
    public function loadReviews($aParams)
    {
        $aReviews = array();

        require_once(_GMCP_PATH_LIB_REVIEWS . 'reviews-ctrl_class.php');

        $bProductComment = BT_GmcProModuleTools::isInstalled('productcomments');
        $bGsnippets = BT_GmcProModuleTools::isInstalled('gsnippetsreviews');

        if (!empty($bGsnippets)) {
            $oReviews = BT_GmcProReviewCtrl::get('gsnippetsreviews');
        } elseif (!empty($bProductComment)) {
            $oReviews = BT_GmcProReviewCtrl::get('productcomments');
        }

        if (!empty($oReviews)
            && is_object($oReviews)
        ) {
            $aReviews = $oReviews->getReviews($aParams['iLangId']);
        }

        return $aReviews;
    }


    /**
     * set the XML header and we redfine it for the review data feed
     *
     * @param array $aParams
     * @return bool
     */
    public function header(array $aParams = null)
    {
        // get meta
        $aMeta = Meta::getMetaByPage('index', (int)$aParams['iLangId']);

        $sContent = ''
            . '<?xml version="1.0" encoding="UTF-8"?>' . $this->sSep
            . '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning"' . $this->sSep
            . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . $this->sSep
            . 'xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.1/product_reviews.xsd" >' . $this->sSep
            . '<publisher>' . $this->sSep
            . "\t" . '<name>' . Tools::stripslashes(Configuration::get('PS_SHOP_NAME')) . '</name>' . $this->sSep
            . '</publisher>' . $this->sSep;

        if (!empty($this->bOutput)
            || !empty($aParams['bOutput'])
        ) {
            echo $sContent;
        } else {
            $this->sContent .= $sContent;
        }

        return true;
    }


    /**
     * set the XML footer for the reviews data feed
     *
     * @param array $aParams
     * @return bool
     */
    public function footer($aParams = null)
    {
        $sContent = $this->sSep . '</feed>';

        if (!empty($this->bOutput) || !empty($aParams['bOutput'])) {
            echo $sContent;
        } else {
            $this->sContent .= $sContent;
        }

        return true;
    }

    /**
     * get currency code for cart rule
     *
     * @param int $iCurrencyId
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
     * @params $aParams
     * @return array
     */
    public function buildReviewsXml($aParams)
    {
        $sContent = '';
        $aReviews = $this->loadReviews($aParams);

        if (!empty($aReviews)
            && is_array($aReviews)
        ) {
            $sContent .= '<reviews>' . $this->sSep;

            foreach ($aReviews as $aReview) {
                $sContent .=
                    "\t" . '<review>' . $this->sSep
                    . "\t\t" . '<reviewer>' . $this->sSep
                    . "\t\t\t" . '<name is_anonymous="true">Anonymous</name>' . $this->sSep
                    . "\t\t" . '</reviewer>' . $this->sSep;

                $sContent .= "\t\t" . '<review_timestamp>' . BT_GmcProModuleTools::formatDateReviews($aReview['sDate']) . '</review_timestamp>' . $this->sSep;

                // USE CASE TITLE
                if (!empty($aReview['sReview'])) {
                    $sContent .= "\t\t" . '<content>' . BT_GmcProModuleTools::cleanUpReview($aReview['sReview']) . '</content>' . $this->sSep;
                }

                $sContent .= "\t\t" . '<review_url type="singleton">' . $aReview['sReviewUrl'] . '</review_url>' . $this->sSep
                    . "\t\t" . '<ratings>' . $this->sSep
                    . "\t\t\t" . '<overall min="1" max="5">' . $aReview['sRating'] . '</overall>' . $this->sSep
                    . "\t\t" . '</ratings>' . $this->sSep
                    . "\t\t" . '<products>' . $this->sSep
                    . "\t\t\t" . '<product>' . $this->sSep;

                // Manage details product information to get the most accurate data feed
                // Only one value is required for the matching in the Review feed
                if (!empty($aReview['sGtin'])
                    || !empty($aReview['sMpn'])
                    || !empty($aReview['sManufacturer'])
                    || !empty($aReview['sSku'])
                ) {
                    $sContent .= "\t\t\t" . '<product_ids>' . $this->sSep;

                    if (!empty($aReview['sGtin'])) {
                        $sContent .= "\t\t\t\t" . '<gtins>' . $this->sSep
                            . "\t\t\t\t\t" . '<gtin><![CDATA[' . $aReview['sGtin'] . ']]></gtin>' . $this->sSep
                            . "\t\t\t\t" . '</gtins>' . $this->sSep;
                    }

                    if (!empty($aReview['sMpn'])) {
                        $sContent .= "\t\t\t\t" . '<mpns>' . $this->sSep
                            . "\t\t\t\t\t" . '<mpn><![CDATA[' . $aReview['sMpn'] . ']]></mpn>' . $this->sSep
                            . "\t\t\t\t" . '</mpns>' . $this->sSep;
                    }

                    if (!empty($aReview['sSku'])) {
                        $sContent .= "\t\t\t\t" . '<skus>' . $this->sSep
                            . "\t\t\t\t\t" . '<sku><![CDATA[' . $aReview['sMpn'] . ']]></sku>' . $this->sSep
                            . "\t\t\t\t" . '</skus>' . $this->sSep;
                    }

                    if (!empty($aReview['sManufacturer'])) {
                        $sContent .= "\t\t\t\t" . '<brands>' . $this->sSep
                            . "\t\t\t\t\t" . '<brand><![CDATA[' . $aReview['sManufacturer'] . ']]></brand>' . $this->sSep
                            . "\t\t\t\t" . '</brands>' . $this->sSep;
                    }

                    $sContent .= "\t\t\t" . '</product_ids>' . $this->sSep;
                }

                $sContent .= "\t\t\t\t" . '<product_url><![CDATA[' . $aReview['sProductUrl'] . ']]></product_url>' . $this->sSep;

                $sContent .= "\t\t\t" . '</product>' . $this->sSep
                    . "\t\t" . '</products>' . $this->sSep
                    . "\t" . '</review>' . $this->sSep;
            }
            $sContent .= '</reviews>';

            // Get the content file
            echo $sContent;
        }
    }
}
