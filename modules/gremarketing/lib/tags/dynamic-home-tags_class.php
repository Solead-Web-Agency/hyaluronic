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
 * declare Home Exception class
 */
class BT_HomeException extends BT_DynTagsException
{
}

class BT_DynHomeTags extends BT_BaseDynTags
{
    /**
     * magic method assign
     *
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        $this->bValid = true;
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
                //            case 'iProductId' :
                //                $this->iProductId = is_numeric($mValue)? $mValue : null;
                //                break;
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
                //            case 'iProductId' :
                //                return $this->iProductId;
                //                break;
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
    }

    /**
     * set page type
     */
    public function setPageType()
    {
        $this->sPageType = parent::$sQuote . 'home' . parent::$sQuote;
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
