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
 * declare Purchase Exception class
 */
class BT_PurchaseException extends BT_DynTagsException
{
}

class BT_DynPurchaseTags extends BT_BaseDynTags
{
    /**
     * method assign
     *
     * @throws Exception
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        if (!empty($aParams['iOrderId'])) {
            $this->iOrderId = $aParams['iOrderId'];
        } else {
            throw new BT_PurchaseException(GRemarketing::$oModule->l('Internal server error => invalid order id', 'dynamic-purchase-tags_class'), 520);
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
            case 'iOrderId':
                $this->iOrderId = is_numeric($mValue) ? $mValue : null;
                break;
            case 'aOrderProducts':
                $this->aOrderProducts = is_array($mValue) ? $mValue : null;
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
     * @category hook collection
     * @uses
     *
     * @param string $sName
     * @return property : mixed or null
     */
    public function __get($sName)
    {
        switch ($sName) {
            case 'iOrderId':
                return $this->iProductId;
                break;
            case 'aOrderProducts':
                return $this->aOrderProducts;
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
        $aOrderProducts = array();

        // get current cart
        $oOrder = new Order($this->iOrderId);

        // check if the order is valid
        if (!empty($oOrder->valid)) {
            // get current products of order
            $this->aOrderProducts = $oOrder->getProducts();

            if (!empty($this->aOrderProducts)) {
                foreach ($this->aOrderProducts as $aProduct) {
                    // get current product tags obj
                    try {
                        $oProductTags = parent::get('product', array_merge($this->aProductParams, array(
                            'iProductId' => intval($aProduct['id_product']),
                            'iProductAttributeId' => intval($aProduct['product_attribute_id'])
                        )));

                        // set product ID
                        $oProductTags->setProductId();

                        if ($oProductTags->bValid) {
                            $aOrderProducts[] = $oProductTags->sProductId;
                        }
                    } catch (Exception $e) {
                        throw new BT_PurchaseException($e->getMessage(), $e->getCode());
                    }
                }
                if (!empty($aOrderProducts)) {
                    $this->bValid = true;

                    // get count
                    $iCount = count($aOrderProducts);

                    if ($iCount > 1) {
                        // concatenate product IDs in order to create an array of IDs for javascript array
                        for ($i = 0; $i < $iCount; $i++) {
                            if ($i == 0) {
                                $this->sProductId .= '[';
                            }
                            $this->sProductId .= $aOrderProducts[$i] . (isset($aOrderProducts[$i + 1]) ? ',' : ']');
                        }
                    } else {
                        $this->sProductId = $aOrderProducts[0];
                    }
                }
            }
        }
    }

    /**
     * set page type
     */
    public function setPageType()
    {
        $this->sPageType = parent::$sQuote . 'purchase' . parent::$sQuote;
    }

    /**
     * sset total value
     */
    public function setTotalValue()
    {
        $fTotalPrice = 0;

        $this->bValid = false;

        // test current product cart data
        if (!empty($this->aOrderProducts)) {
            foreach ($this->aOrderProducts as $aProduct) {
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
    }
}
