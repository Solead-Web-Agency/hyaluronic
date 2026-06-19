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
class AmazonSPFeedMessageBusinessPrice
{
    protected $businessPrice;
    protected $rules;
    protected $quantityPriceType;
    public function __construct($businessPrice, $rules, $quantityPriceType)
    {
        $this->businessPrice = $businessPrice;
        $this->rules = $rules;
        $this->quantityPriceType = $quantityPriceType;
    }

    /**
     * @param DOMDocument $document
     * @return array
     * @throws DOMException
     */
    public function resolveXmlTags(DOMDocument $document)
    {
        $resultElements = array();
        $resultElements[] = $document->createElement('BusinessPrice', sprintf('%.02f', $this->businessPrice));
        $rules = $this->rules;
        if (isset($rules) && is_array($rules) && count($rules)) {
            $resultElements[] = $document->createElement('QuantityPriceType', $this->quantityPriceType);
            $isPercentage = $this->quantityPriceType == 'percent';
            $stepsTag = $document->createElement('QuantityPrice');
            foreach (range(1, 3) as $index) {
                if (!isset($rules['QuantityPrice' . $index])) {
                    continue;
                }
                $price = $isPercentage ? (int)$rules['QuantityPrice' . $index] : sprintf('%.02f',
                    $rules['QuantityPrice' . $index]);
                $priceTag = $document->createElement("QuantityPrice$index", $price);
                $stepsTag->appendChild($priceTag);
                $bound = $isPercentage ? (int)$rules['QuantityLowerBound' . $index] : sprintf('%.02f',
                    $rules['QuantityLowerBound' . $index]);
                $boundTag = $document->createElement("QuantityLowerBound$index", $bound);
                $stepsTag->appendChild($boundTag);
            }
            $resultElements[] = $stepsTag;
        }
        return $resultElements;
    }
}