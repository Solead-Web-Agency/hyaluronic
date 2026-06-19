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
 * declare Cart Exception class
 */
class BT_CartException extends BT_DynTagsException
{
}

class BT_DynCartTags extends BT_BaseDynTags
{
    /**
     * magic method assign
     *
     * @throws Exception
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        if (!empty($aParams['iCartId'])) {
            $this->iCartId = $aParams['iCartId'];
        } else {
            throw new BT_CartException(GRemarketing::$oModule->l('Internal server error => invalid cart id', 'dynamic-cart-tags_class'), 510);
        }
        $this->aProductParams = $aParams;
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
            case 'iCartId':
                $this->iCartId = is_numeric($mValue) ? $mValue : null;
                break;
            case 'aProductCart':
                $this->aProductCart = is_array($mValue) ? $mValue : null;
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
            case 'iCartId':
                return $this->iProductId;
                break;
            case 'aProductCart':
                return $this->aProductCart;
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
        // set var
        $aProductCart = array();

        // get current cart
        $oCart = new Cart($this->iCartId);

        // get current products of cart
        $this->aProductCart = $oCart->getProducts();

        if (!empty($this->aProductCart)) {
            foreach ($this->aProductCart as $aProduct) {
                // get current product tags obj
                try {

                    $oProductTags = parent::get('product', array_merge($this->aProductParams, array(
                        'iProductId' => intval($aProduct['id_product']),
                        'iProductAttributeId' => intval($aProduct['id_product_attribute'])
                    )));

                    // set product ID
                    $oProductTags->setProductId();

                    if ($oProductTags->bValid) {
                        $aProductCart[] = $oProductTags->sProductId;
                    }
                } catch (Exception $e) {
                    throw new BT_CartException($e->getMessage(), $e->getCode());
                }
            }
            if (!empty($aProductCart)) {
                $this->bValid = true;

                // get count
                $iCount = count($aProductCart);

                if ($iCount > 1) {
                    // concatenate product IDs in order to create an array of IDs for javascript array
                    for ($i = 0; $i < $iCount; $i++) {
                        if ($i == 0) {
                            $this->sProductId .= '[';
                        }
                        $this->sProductId .= $aProductCart[$i] . (isset($aProductCart[$i + 1]) ? ',' : ']');
                    }
                } else {
                    $this->sProductId = $aProductCart[0];
                }
            }
        }
    }

    /**
     * set page type
     */
    public function setPageType()
    {
        $this->sPageType = parent::$sQuote . 'cart' . parent::$sQuote;
    }

    /**
     * set total value
     */
    public function setTotalValue()
    {
        $fTotalPrice = 0;

        $this->bValid = false;

        // test current product cart data
        if (!empty($this->aProductCart)) {
            foreach ($this->aProductCart as $aProduct) {
                $fTotalPrice += floatval($aProduct['total_wt']);
            }
            if (!empty($fTotalPrice)) {
                $this->fTotalValue = (is_float($fTotalPrice) ? number_format($fTotalPrice, 2, '.', '') : $fTotalPrice);

                $this->bValid = true;
            }
        }
    }


    /**
     * set category name
     */
    public function setCategoryName()
    {
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
        $this->bReturnCustomer = 'false';

        if (!empty(GRemarketing::$oCookie->id_customer)) {
            $oCustomer = new Customer(GRemarketing::$oCookie->id_customer);

            // check if the customer has already purchased on the shop
            $aBoughtProducts = $oCustomer->getBoughtProducts();

            if (!empty($aBoughtProducts)) {
                $this->bReturnCustomer = 'true';
            }
        }
    }
}
