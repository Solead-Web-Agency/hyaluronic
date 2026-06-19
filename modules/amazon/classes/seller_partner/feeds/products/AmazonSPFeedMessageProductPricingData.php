<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (class_exists(Module::class)) {
    if (!defined('_PS_VERSION_')) {
        exit;
    }
}
/**
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/Price.xsd
 */
class AmazonSPFeedMessageProductPricingData extends AmazonSPFeedMessageProductDataGeneral
{
    protected $currency;
    protected $standardPrice;
    /** @var AmazonBusinessPriceMessage|AmazonSPFeedMessageBusinessPrice */
    protected $businessPriceMessage;
    protected $sales = array();

    public function __construct($operationType, $sku, $currency, $standardPrice, $businessPriceMessage, $sales)
    {
        parent::__construct($operationType, $sku);

        $this->currency = $currency;
        $this->standardPrice = $this->sanityPrice($standardPrice);
        $this->businessPriceMessage = $businessPriceMessage;
        $this->sales = $sales;
    }

    public function isValidMessage()
    {
        return parent::isValidMessage() && $this->standardPrice;
    }

    protected function generateRemainingMessage($domDoc)
    {
        $sku = $domDoc->createElement('SKU', $this->sku);
        $standardPrice = $domDoc->createElement('StandardPrice', $this->standardPrice);
        $standardPrice->setAttribute('currency', $this->currency);
        $businessPriceTags = array();
        if ($this->businessPriceMessage instanceof AmazonBusinessPriceMessage) {
            $businessPriceTags = $this->businessPriceMessage->resolveXmlTags($domDoc, $this->standardPrice);
        } elseif ($this->businessPriceMessage instanceof AmazonSPFeedMessageBusinessPrice) {
            $businessPriceTags = $this->businessPriceMessage->resolveXmlTags($domDoc);    
        }
        $saleTag = null;
        if ($this->sales && isset($this->sales['dateStart']) && isset($this->sales['dateEnd']) && isset($this->sales['salePrice'])) {
            $dateStart = $domDoc->createElement('StartDate', $this->sales['dateStart']);
            $dateEnd = $domDoc->createElement('EndDate', $this->sales['dateEnd']);
            $salePrice = $domDoc->createElement('SalePrice', str_replace(',', '.', $this->sales['salePrice']));
            $salePrice->setAttribute('currency', $this->currency);
            $saleTag = $domDoc->createElement('Sale');
            $saleTag->appendChild($dateStart);
            $saleTag->appendChild($dateEnd);
            $saleTag->appendChild($salePrice);
        }

        $price = $domDoc->createElement('Price');
        $price->appendChild($sku);
        $price->appendChild($standardPrice);
        foreach ($businessPriceTags as $businessPriceTag) {
            $price->appendChild($businessPriceTag);
        }
        if ($saleTag) {
            $price->appendChild($saleTag);
        }

        return array($price);
    }

    private function sanityPrice($price)
    {
        if (strpos($price, ',')) {
            $price = str_replace(',', '.', $price);
        }

        if (is_numeric($price)) {
            if ($price >= 0) {
                return $price;
            }
        }

        return 0;
    }
}
