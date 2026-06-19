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

if (!defined('_PS_VERSION_')) {
    exit(1);
}

/**
 * declare Search Exception class
 */
class BT_SearchException extends BT_DynTagsException
{
}

class BT_DynSearchTags extends BT_BaseDynTags
{

    /**
     * @var string $sQuery : the query to search
     */
    public $sQuery = array();

    /**
     * @var array $aProducts : the product data array
     */
    public $aProducts = null;

    /**
     * method assign
     *
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        $this->bValid = false;

        $sQuery = empty(GRemarketing::$bCompare17) ? Tools::getValue('search_query') : Tools::getValue('s');

        // Sometimes the param is q
        if (empty($sQuery)) {
            $sQuery = Tools::getValue('q');
        }

        $this->sQuery = $sQuery;

        //handle the pagnitation
        $iPostPage = Tools::getValue('p');
        $iPostProductPerPage = Tools::getValue('n');

        $iPage = !empty($iPostPage) ? $iPostPage : 0;
        $iProductPerPage = !empty($iPostProductPerPage) ? $iPostProductPerPage : Configuration::get('PS_PRODUCTS_PER_PAGE');

        // get the search results
        $this->aProducts = Search::find(GRemarketing::$iCurrentLang, $this->sQuery, $iPage, $iProductPerPage, 'position', 'desc');
        $this->aProductParams = $aParams;

        if (!empty($this->aProducts['result'])) {
            $this->bValid = true;
        }
    }


    /**
     * allow to check value assign to property
     *
     * @param string $sName
     * @param mixed $mValue
     */
    public function __set($sName, $mValue)
    {
        switch ($sName) {
            case 'aProductParams':
                $this->aProductParams = is_array($mValue) ? $mValue : null;
                break;
            default:
                break;
        }
    }

    /**
     * returns allowed properties
     *
     * @param string $sName
     * @return property : mixed or null
     */
    public function __get($sName)
    {
        switch ($sName) {
            case 'aProductParams':
                return $this->aProductParams;
                break;
            default:
                break;
        }
        return null;
    }


    /**
     * set Product Id according to GMC requirements : one product per combination / get Countries array or GMC prefix
     */
    public function setProductId()
    {
        if (!empty($this->aProducts)) {
            reset($this->aProducts);

            foreach ($this->aProducts['result'] as $keyProduct => $aProduct) {
                $oProductTags = parent::get('product', array_merge($this->aProductParams, array(
                    'iProductId' => intval($aProduct['id_product']),
                    'iProductAttributeId' => intval($aProduct['id_product_attribute'])
                )));

                // set product ID
                $oProductTags->setProductId();

                if ($oProductTags->bValid) {
                    $aProductCat[] = $oProductTags->sProductId;
                }
            }

            if (!empty($aProductCat)) {
                $this->bValid = true;

                // get count
                $iCount = count($aProductCat);

                if ($iCount > 1) {
                    // concatenate product IDs in order to create an array of IDs for javascript array
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($i == 0) {
                            $this->sProductId .= '[';
                        }
                        $this->sProductId .= $aProductCat[$i] . (isset($aProductCat[$i + 1]) ? ',' : ']');
                    }
                } else {
                    $this->sProductId = $aProductCat[0];
                }
            }
        }
    }

    /**
     * set page type
     */
    public function setPageType()
    {
        $this->sPageType = parent::$sQuote . 'searchresults' . parent::$sQuote;
    }

    /**
     * set total value
     */
    public function setTotalValue()
    {
    }


    /**
     * set category name
     */
    public function setCategoryName()
    {
    }


    /**
     * setOnSale() method set if the product is on a sale or not
     */
    public function setOnSale()
    {
    }

    /**
     * set if the customer id
     */
    public function setUserId()
    {
        // Use case to mange user_id according to the option
        if (!empty((int)GRemarketing::$conf['GR_USER_ID'])) {
            $sUserId = Context::getContext()->customer->logged == 1 ? Context::getContext()->customer->id : rand(0, 999999);
            if (!empty($sUserId)) {
                $this->sUserId = parent::$sQuote . $sUserId . parent::$sQuote;
            }
        }
    }


    /**
     * set if the customer purchase for the first time on the shop
     */
    public function setReturnCustomer()
    {
    }
}
