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
 * declare Product Exception class
 */
class BT_ProductException extends BT_DynTagsException
{
}

class BT_DynProductTags extends BT_BaseDynTags
{

    /*
    * defines country without real country on Prestashop
    * uses  => with admin interface
    *
    */
    private $notPsCountry = array('CHF');

    /**
     * method assign
     *
     * @throws Exception
     * @param array $aParams
     */
    public function __construct(array $aParams)
    {
        // get the product ID - required
        if (!empty($aParams['iProductId'])) {
            $this->iProductId = $aParams['iProductId'];
        } else {
            throw new BT_ProductException(GRemarketing::$oModule->l('Internal server error => invalid product id', 'dynamic-product-tags_class'), 501);
        }

        // get the product ID - always defined but could be set to NULL
        $this->iProductAttributeId = isset($aParams['iProductAttributeId']) ? $aParams['iProductAttributeId'] : null;

        // get the category ID - optional
        if (!empty($aParams['iCategoryId'])) {
            $this->iCategoryId = $aParams['iCategoryId'];
        } else {
            // get product
            $oProduct = BT_GRModuleTools::isProductObj($this->iProductId, GRemarketing::$iCurrentLang, true);
            // set the category ID
            if (!empty($oProduct->id_category_default)) {
                $this->iCategoryId = $oProduct->id_category_default;
            }
        }

        // optional
        $this->bUseTax = !empty($aParams['bUseTax']) ? $aParams['bUseTax'] : null;
        $this->bComboProduct = !empty($aParams['bComboProduct']) ? $aParams['bComboProduct'] : null;
        $this->oGMC = !empty($aParams['oGMC']) ? $aParams['oGMC'] : null;
        $this->sGmcPrefix = !empty($aParams['sGmcPrefix']) ? strtoupper($aParams['sGmcPrefix']) : null;
        $this->sGooglePrefix = !empty($aParams['sGooglePrefix']) ? strtoupper($aParams['sGooglePrefix']) : null;
        $this->sPrefixName = !empty($aParams['sPrefixName']) ? strtoupper($aParams['sPrefixName']) : 'GMC';
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
            case 'iProductId':
                $this->iProductId = is_numeric($mValue) ? $mValue : null;
                break;
            case 'iProductAttributeId':
                $this->iProductAttributeId = is_numeric($mValue) ? $mValue : null;
                break;
            case 'iCategoryId':
                $this->iCategoryId = is_numeric($mValue) ? $mValue : null;
                break;
            case 'bUseTax':
                $this->bUseTax = is_bool($mValue) ? $mValue : null;
                break;
            case 'oGMC':
                $this->oGMC = is_object($mValue) ? $mValue : null;
                break;
            case 'bComboProduct':
                $this->bComboProduct = is_bool($mValue) ? $mValue : null;
                break;
            case 'sGmcPrefix':
                $this->sGmcPrefix = is_string($mValue) ? $mValue : null;
                break;
            case 'sGooglePrefix':
                $this->sGooglePrefix = is_string($mValue) ? $mValue : null;
                break;
            case 'sPrefixName':
                $this->sPrefixName = is_string($mValue) ? $mValue : null;
                break;
            case 'fPriceNoDiscount':
                $this->fPriceNoDiscount = is_numeric($mValue) ? $mValue : null;
                break;
            case 'fPriceDiscount':
                $this->fPriceDiscount = is_numeric($mValue) ? $mValue : null;
                break;
            default:
                break;
        }
    }

    /**
     * returns allowed properties
     *
     * @param string $sName
     *
     * @return property : mixed or null
     */
    public function __get($sName)
    {
        switch ($sName) {
            case 'iProductId':
                return $this->iProductId;
                break;
            case 'iProductAttributeId':
                return $this->iProductAttributeId;
                break;
            case 'iCategoryId':
                return $this->iCategoryId;
                break;
            case 'bUseTax':
                return $this->bUseTax;
                break;
            case 'oGMC':
                return $this->oGMC;
                break;
            case 'bComboProduct':
                return $this->bComboProduct;
                break;
            case 'sGmcPrefix':
                return $this->sGmcPrefix;
                break;
            case 'sPrefixName':
                return $this->sPrefixName;
                break;
            case 'sGooglePrefix':
                return $this->sGooglePrefix;
                break;
            case 'fPriceNoDiscount':
                return $this->fPriceNoDiscount;
                break;
            case 'fPriceDiscount':
                return $this->fPriceDiscount;
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
        // use case - tag management with Google Merchant Center (both versions
        if (!empty($this->oGMC)) {
            // If simple id option is deactivated
            if (empty(Configuration::get($this->sPrefixName . '_SIMPLE_PROD_ID'))) {
                // if GMC prefix - we concatenate it to the final product ID
                if (!empty($this->sGmcPrefix)) {
                    $this->sProductId .= $this->sGmcPrefix;
                }

                // check both ways to recover the available country list from both GMC modules
                if (!empty($this->oGMC->gMerchantCenterCountries) || !empty($GLOBALS[$this->sPrefixName . '_AVAILABLE_COUNTRIES'])) {
                    $aAvailableCountries = !empty($GLOBALS[$this->sPrefixName . '_AVAILABLE_COUNTRIES']) ? $GLOBALS[$this->sPrefixName . '_AVAILABLE_COUNTRIES'] : $this->oGMC->gMerchantCenterCountries;

                    // get current currency
                    $sCurrencyIso = Tools::strtoupper(BT_GRModuleTools::getCurrency('iso_code', GRemarketing::$oCookie->id_currency));

                    // loop on each language and country of Google Merchant center in order to get current country ISO to make product ID matching with GMC
                    foreach ($aAvailableCountries as $sLangIso => $aCountries) {
                        if ($sLangIso == GRemarketing::$sCurrentLang) {
                            foreach ($aCountries as $sCountryIso => $sCurrency) {
                                foreach ($sCurrency['currency'] as $sCurrentCurrency) {
                                    if (!in_array($sCurrencyIso, $this->notPsCountry)) {
                                        if ($sCurrentCurrency == $sCurrencyIso && $sCountryIso == Tools::strtoupper(GRemarketing::$sCurrentLang)  && !$this->bValid) {
                                            $this->bValid = true;
                                            $this->sProductId .= $sCountryIso;
                                        }
                                    } else {
                                        if ($sCurrentCurrency == $sCurrencyIso  && !$this->bValid) {
                                            $this->bValid = true;
                                            $this->sProductId .= $sCountryIso;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $this->bValid = true;
                }

                if ($this->bValid) {
                    // set product ID
                    $this->sProductId .= ((string)$this->iProductId . (!empty($this->bComboProduct) && !empty($this->iProductAttributeId) ? GRemarketing::$conf['GR_COMBO_SEPARATOR'] . $this->iProductAttributeId : ''));
                    $this->sProductId = parent::$sQuote . $this->sProductId . parent::$sQuote;
                }
            } else {
                $this->sProductId .= parent::$sQuote . (!empty($this->sGooglePrefix) ? $this->sGooglePrefix : '') . ((string)$this->iProductId) . parent::$sQuote;
                $this->bValid = true;
            }
        }
    }

    /**
     * set page type
     */
    public function setPageType()
    {
        $this->sPageType = parent::$sQuote . 'product' . parent::$sQuote;
    }

    /**
     * set total value
     */
    public function setTotalValue()
    {
        // get product price
        $this->fPriceDiscount = Product::getPriceStatic($this->iProductId, $this->bUseTax, $this->iProductAttributeId, 2);
        $this->fPriceNoDiscount = Product::getPriceStatic($this->iProductId, $this->bUseTax, $this->iProductAttributeId, 2, null, false, false);

        if (!empty($this->fPriceDiscount)) {
            $this->fTotalValue = (is_float($this->fPriceDiscount) ? number_format($this->fPriceDiscount, 2, '.', '') : $this->fPriceDiscount);
        } else {
            $this->bValid = false;
        }
    }

    /**
     * set category name
     */
    public function setCategoryName()
    {
        if (!empty($this->iCategoryId)) {
            $oCategory = new Category($this->iCategoryId, GRemarketing::$iCurrentLang);
            $this->sCategoryName = parent::$sQuote . str_replace(array('\'', '"',), ' ', $oCategory->name) . parent::$sQuote;
        }
    }

    /**
     * set if the product is on a sale or not
     */
    public function setOnSale()
    {
        $this->bOnSale = 'false';

        if (
            !empty($this->fPriceDiscount)
            && !empty($this->fPriceNoDiscount)
            && $this->fPriceDiscount < $this->fPriceNoDiscount
        ) {
            $this->bOnSale = 'true';
        }
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
