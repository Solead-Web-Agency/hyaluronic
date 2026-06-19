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
 * declare Category Exception class
 */
class BT_CategoryException extends BT_DynTagsException
{
}

class BT_DynCategoryTags extends BT_BaseDynTags
{

    /**
     * @var object $oCategory : the category object
     */
    public $oCategory = null;

    /**
     * @var array $aProducts : the product data array
     */
    public $aProducts = null;

    /**
     * magic method assign
     *
     * @throws Exception
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        if (!empty($aParams['iCategoryId'])) {
            $this->iCategoryId = $aParams['iCategoryId'];
        } else {
            throw new BT_CategoryException(GRemarketing::$oModule->l('Internal server error => invalid category id', 'dynamic-category-tags_class'), 501);
        }

        // Set 
        if (!empty($this->iCategoryId)) {
            $this->oCategory = new Category($this->iCategoryId, GRemarketing::$iCurrentLang);

            if (!empty($this->oCategory)) {

                //handle the pagnitation
                $iPostPage = Tools::getValue('p');
                $iPostProductPerPage = Tools::getValue('n');
                $sPostOrderBy = Tools::getValue('orderby');
                $sPostOrderWay = Tools::getValue('orderby');

                $iPage = !empty($iPostPage) ? $iPostPage : 0;
                $iProductPerPage = !empty($iPostProductPerPage) ? $iPostProductPerPage : Configuration::get('PS_PRODUCTS_PER_PAGE');
                $sOrderby = !empty($sPostOrderBy) ? $sPostOrderBy : null;
                $sOrderway = !empty($sPostOrderWay) ? $sPostOrderWay : null;

                $this->aProducts = $this->oCategory->getProducts(GRemarketing::$iCurrentLang, $iPage, $iProductPerPage, $sOrderby, $sOrderway, false, true, false, 1, true, null);
                $this->aProductParams = $aParams;
            }
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
            case 'iCategoryId':
                $this->iCategoryId = is_numeric($mValue) ? $mValue : null;
                break;
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
            case 'iCategoryId':
                return $this->iCategoryId;
                break;
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

            foreach ($this->aProducts as $keyProduct => $aProduct) {
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
        $this->sPageType = parent::$sQuote . 'category' . parent::$sQuote;
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
        if (!empty($this->iCategoryId)) {
            $oCategory = new Category($this->iCategoryId, GRemarketing::$iCurrentLang);
            $this->sCategoryName = parent::$sQuote . str_replace(array('\'', '"'), ' ', $oCategory->name) . parent::$sQuote;
        }
    }


    /**
     * set if the product is on a sale or not
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
