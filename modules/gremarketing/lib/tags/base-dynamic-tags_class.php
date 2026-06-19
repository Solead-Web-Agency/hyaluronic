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
class BT_DynTagsException extends Exception
{
}


abstract class BT_BaseDynTags
{
    /**
     * @var string $sName : stock tag type name
     */
    public static $sName = '';

    /**
     * @var string $sQuote : character used for tagging values
     */
    public static $sQuote = '\'';

    /**
     * @var bool $bValid : current object valid or not
     */
    public $bValid = false;

    /**
     * @var string $sProductId : ID or IDs of current product(s)
     */
    public $sProductId = '';

    /**
     * @var string $sProductIdLabel : label of product id
     */
    public $sProductIdLabel = 'ecomm_prodid';

    /**
     * @var string $sUserIdLabel : label of the user id
     */
    public $sUserIdLabel = 'user_id';

    /**
     * @var string $sUserId: label of the user id
     */
    public $sUserId = '';

    /**
     * @var string $sPageType : type of current page
     */
    public $sPageType = null;

    /**
     * @var string $sPageTypeLabel : label of page type
     */
    public $sPageTypeLabel = 'ecomm_pagetype';

    /**
     * @var float $fTotalValue : total of value of cart or purchase
     */
    public $fTotalValue = null;

    /**
     * @var string $sfTotalValueLabel : label of total value
     */
    public $fTotalValueLabel = 'ecomm_totalvalue';

    /**
     * @var string $sCategoryName : the category name
     */
    public $sCategoryName = null;

    /**
     * @var string $sCategoryNameLabel : label of the category name
     */
    public $sCategoryNameLabel = 'ecomm_category';

    /**
     * @var bool $bOnSale : define if the product is on a sale or not
     */
    public $bOnSale = null;

    /**
     * @var string $sOnSaleLabel : label of the on a sale tag
     */
    public $sOnSaleLabel = 'isSaleItem';

    /**
     * @var bool $bReturnCustomer : the return customer tag identify if the customer has made an order for the first time
     */
    public $bReturnCustomer = null;

    /**
     * @var string $sReturnCustomerLabel : label of the return customer tag
     */
    public $sReturnCustomerLabel = 'returnCustomer';


    /**
     * get params keys
     *
     * @param array $aParams
     */
    abstract public function __construct(array $aParams);


    /**
     * set value to a property of object
     */
    abstract public function setProductId();


    /**
     * set page type value and label
     */
    abstract public function setPageType();


    /**
     * set total value
     */
    abstract public function setTotalValue();


    /**
     * method set the category name
     */
    abstract public function setCategoryName();


    /**
     * set if the product is on a sale or not
     */
    abstract public function setOnSale();

    /**
     * set the user ID
     */
    abstract public function setUserId();


    /**
     * set if the customer purchase for the first time on the shop
     */
    abstract public function setReturnCustomer();

    /**
     * set values
     */
    public function set()
    {
        // set product ID
        $this->setProductId();

        // set page type
        $this->setPageType();

        // set total value
        $this->setTotalValue();

        // set category name
        $this->setCategoryName();

        // set on a sale
        $this->setOnSale();

        // set the user ID
        $this->setUserId();

        // set return customer
        $this->setReturnCustomer();
    }


    /**
     * display properties
     *
     * @return array of properties + labels
     */
    public function display()
    {
        $aProperties = array();

        // check product id
        if (!empty($this->sProductId)) {
            $aProperties[] = array('label' => $this->sProductIdLabel, 'value' => $this->sProductId);
        }

        $aProperties[] = array('label' => $this->sPageTypeLabel, 'value' => $this->sPageType);

        // check total value
        if (!empty($this->fTotalValue)) {
            $aProperties[] = array('label' => $this->fTotalValueLabel, 'value' => $this->fTotalValue);
        }

        // check category name
        if (!empty($this->sCategoryName)) {
            $aProperties[] = array('label' => $this->sCategoryNameLabel, 'value' => $this->sCategoryName);
        }

        // check on a sale option
        if ($this->bOnSale !== null) {
            $aProperties[] = array('label' => $this->sOnSaleLabel, 'value' => $this->bOnSale);
        }

        //Check the user ID
        if (!empty($this->sUserId)) {
            $aProperties[] = array('label' => $this->sUserIdLabel, 'value' => $this->sUserId);
        }

        // check return customer option
        if ($this->bReturnCustomer !== null) {
            $aProperties[] = array('label' => $this->sReturnCustomerLabel, 'value' => $this->bReturnCustomer);
        }

        return $aProperties;
    }

    /**
     * instantiate matched connector object
     *
     * @throws Exception
     * @param string $sTagsType
     * @param array $aParams
     * @return obj tags type abstract type
     */
    public static function get($sTagsType, array $aParams = null)
    {
        // if valid connector
        if (in_array($sTagsType, array_keys($GLOBALS['GR_TAGS_TYPE']))) {
            // include
            require_once('dynamic-' . $sTagsType . '-tags_class.php');

            // set class name
            $sClassName = 'BT_Dyn' . ucfirst($sTagsType) . 'Tags';

            // get tags type name
            self::$sName = $sTagsType;

            return (new $sClassName($aParams));
        } else {
            throw new BT_DynTagsException(GRemarketing::$oModule->l('Internal server error => invalid dynamic tags type', 'base-dynamic-tags_class'), 510);
        }
    }
}
