<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
class AmazonBusinessPriceMessage
{
    public $emptyMessage = false;
    public $sendDeleteAction = false;

    /**
     * @var AmazonBusinessPriceEntry[]
     * sorted by price descending (Amazon's requirement)
     * all rules have been converted to fixed reduction (2 types of reduction: fixed / percentage)
     */
    public $priceSteps = array();
    public $businessPrice;

    public static function initEmptyInstance()
    {
        $instance = new self();
        $instance->emptyMessage = true;

        return $instance;
    }

    public static function initDeleteInstance()
    {
        $instance = new self();
        $instance->sendDeleteAction = true;

        return $instance;
    }

    /**
     * @param AmazonSingleSpecificPrice[] $psSpecificPrices
     */
    public static function initNormalInstance($normalPrice, $psSpecificPrices, $priceModifier)
    {
        $instance = new self();

        $steps = array();
        foreach ($psSpecificPrices as $psSpecificPrice) {
            if(!is_null($psSpecificPrice->reduction)) {
                $price = $psSpecificPrice->priceAfterRule($normalPrice);
                if (is_callable($priceModifier)) {
                    $price = call_user_func($priceModifier, $price);
                }
                $steps[] = new AmazonBusinessPriceEntry($price, $psSpecificPrice->getQuantity(), $psSpecificPrice->getReduction());
            }
            usort($steps, array($instance, 'sortPrices'));  // Sort by price descending
            $instance->priceSteps = $instance->uniqueSteps($steps);

            // 12-2022: Set business price with special have fixed price for 1
            if ($psSpecificPrice->haveFixedPrice() && $psSpecificPrice->getQuantity() < 2 && !is_null($psSpecificPrice->reduction)) {
                $price = $psSpecificPrice->priceAfterRule($psSpecificPrice->price);
                $instance->businessPrice = new AmazonBusinessPriceEntry($price, $psSpecificPrice->getQuantity(), $psSpecificPrice->getReduction());
            }
        }

        return $instance;
    }

    public function resolveXmlTags(DOMDocument $Document, $standardPrice)
    {
        if ($this->emptyMessage) {
            return array();
        }

        if ($this->sendDeleteAction) {
            return $this->resolveDeleteXmlTags($Document);
        }

        return $this->resolveNormalXmlTags($Document, $standardPrice);
    }

    protected function resolveNormalXmlTags(DOMDocument $Document, $standardPrice)
    {
        $resultElements = array();
        $priceSteps = $this->priceSteps;
        $businessPrice = $this->businessPrice;

        if (count($priceSteps)) {
            $firstStep = $priceSteps[0];
            // If first step is applied for any quantities, it should be the base business price.
            // Otherwise, use standard price as base business price.
            // Because each price in step must be lower than base business price.
            $businessBasePrice = $standardPrice - ($standardPrice * 0.01); // Business price must be less than your standard price.

            if ($firstStep->quantity < 2) {
                $businessBasePrice = ($firstStep->price >= $standardPrice) ? $businessBasePrice : $firstStep->price; // If first step price higher than standard price get businessBasePrice
                array_shift($priceSteps);  // Shift out the first step, because it applies to all quantities
            }
            if (!is_null($businessPrice)) { // 12-2022: Get business price depend on special have fixed price for 1
                $businessBasePrice = ($businessPrice->price >= $standardPrice) ? ($standardPrice - ($standardPrice * 0.01)) : $businessPrice->price;
            }
            $resultElements[] = $Document->createElement('BusinessPrice', sprintf('%.02f', $businessBasePrice));

            // For remain steps, define them
            if (count($priceSteps)) {  // Keep this check, because $priceSteps is manipulated before
                $resultElements[] = $Document->createElement('QuantityPriceType', 'fixed');
                $stepsTag = $Document->createElement('QuantityPrice');
                foreach ($priceSteps as $index => $priceStep) {
                    $step = $index + 1; // Index is 0-based, Amazon step is 1-based
                    $priceTag = $Document->createElement("QuantityPrice$step", sprintf('%.02f', $priceStep->price));
                    $boundTag = $Document->createElement("QuantityLowerBound$step", $priceStep->quantity);
                    $stepsTag->appendChild($priceTag);
                    $stepsTag->appendChild($boundTag);
                    // Amazon accepts up to 5 quantity steps
                    if ($step >= 5) {
                        break;
                    }
                }
                $resultElements[] = $stepsTag;
            }
        }

        /**
         * BusinessPrice => float
         * QuantityPriceType => 'fixed'
         * QuantityPrice => (size <= 5)
         *     QuantityPrice1 => float
         *     QuantityLowerBound1 => int
         */
        return $resultElements;
    }

    protected function resolveDeleteXmlTags(DOMDocument $Document)
    {
        $actionTag = $Document->createElement('PricingAction');
        $actionText = $Document->createTextNode('delete business_price');
        $actionTag->appendChild($actionText);

        return array($actionTag);
    }

    /**
     * @param AmazonBusinessPriceEntry[] $input
     */
    protected function uniqueSteps($input)
    {
        $uniqueAmount = array_unique(
            array_map(function ($item) {
                return $item->quantity;
            }, $input)
        );
        $uniquePrice = array_unique(
            array_map(function ($item) {
                return $item->price;
            }, $input)
        );

        return array_values(   // Reset key
            array_intersect_key($input, $uniqueAmount, $uniquePrice)    // Truncate duplications from $input
        );
    }

    /**
     * @param AmazonBusinessPriceEntry $a
     * @param AmazonBusinessPriceEntry $b
     * @return int
     */
    protected function sortPrices($a, $b)
    {
        return $a->price >= $b->price ? -1 : 1;
    }
}

class AmazonBusinessPriceEntry
{
    public $price;
    public $quantity;
    public $reduction;

    public function __construct($price, $quantity, $reduction)
    {
        $this->price = $price;
        $this->quantity = $quantity;
        $this->reduction = $reduction;
    }
}
