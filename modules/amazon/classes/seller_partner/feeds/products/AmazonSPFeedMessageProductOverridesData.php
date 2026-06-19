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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/Override.xsd
 */
class AmazonSPFeedMessageProductOverridesData extends AmazonSPFeedMessageProductDataGeneral
{
    const TYPE_ADDITIVE = 'Additive';
    const TYPE_EXCLUSIVE = 'Exclusive';

    protected $shipOption;
    protected $shipAmount;
    protected $currency;
    protected $shipType;

    public function __construct($operationType, $sku, $shipOption, $shipAmount, $currency, $shipType)
    {
        parent::__construct($operationType, $sku);

        $this->shipOption = $shipOption;
        $this->shipAmount = $shipAmount;
        $this->currency = $currency;
        $this->shipType = $shipType;
    }

    public function isValidMessage()
    {
        return parent::isValidMessage() && $this->isValidType();
    }

    protected function generateRemainingMessage($domDoc)
    {
        $sku = $domDoc->createElement('SKU', $this->sku);

        $shipOption = $domDoc->createElement('ShipOption', $this->shipOption);
        $type = $domDoc->createElement('Type', $this->shipType);
        $amount = $domDoc->createElement('ShipAmount', $this->isDeleteOperation() ? 0 : $this->shipAmount);
        $amount->setAttribute('currency', $this->currency);
        $shippingOverride = $domDoc->createElement('ShippingOverride');
        $shippingOverride->appendChild($shipOption);
        $shippingOverride->appendChild($type);
        $shippingOverride->appendChild($amount);

        $override = $domDoc->createElement('Override');
        $override->appendChild($sku);
        $override->appendChild($shippingOverride);

        return array($override);
    }

    private function isValidType()
    {
        return $this->shipType && in_array($this->shipType, array(self::TYPE_ADDITIVE, self::TYPE_EXCLUSIVE));
    }
}
