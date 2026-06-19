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

class AmazonSPDefShipmentRequestDetails extends AmazonSPDefRequest
{
    public $AmazonOrderId;
    /** @var AmazonSPDefPrimeItem[] */
    public $ItemList = array();
    /** @var AmazonSPDefPrimeAddress */
    public $ShipFromAddress;
    /** @var AmazonSPDefPackageDimensions */
    public $PackageDimensions;
    /** @var AmazonSPDefWeight */
    public $Weight;
    /** @var AmazonSPDefShippingServiceOptions */
    public $ShippingServiceOptions;

    public $SellerOrderId;
    public $MustArriveByDate;
    public $ShipDate;
    /** @var AmazonSPDefLabelCustomization */
    public $LabelCustomization;

    protected static $listOfComplexChildren = array(
        'ItemList' => AmazonSPDefPrimeItem::class,
    );

    protected static $complexChildren = array(
        'ShipFromAddress' => AmazonSPDefPrimeAddress::class,
        'PackageDimensions' => AmazonSPDefPackageDimensions::class,
        'Weight' => AmazonSPDefWeight::class,
        'ShippingServiceOptions' => AmazonSPDefShippingServiceOptions::class,
        'LabelCustomization' => AmazonSPDefLabelCustomization::class,
    );

    protected static $nonPrimitiveOptionalChildrenOnRequest = array(
        'LabelCustomization'
    );

    public function validate()
    {
        if (!$this->AmazonOrderId) {
            $this->addValidationErrors('AmazonOrderId', 'is required!');
        }

        if (!$this->ItemList) {
            $this->addValidationErrors('ItemList', 'is required!');
        }

        if (!$this->PackageDimensions) {
            $this->addValidationErrors('PackageDimensions', 'is required!');
            return false;
        }
        if (!$this->PackageDimensions->validate()) {
            $this->mergeValidationErrors($this->PackageDimensions->getValidationErrors(), 'PackageDimensions.');
            return false;
        }

        if (!$this->ShipFromAddress) {
            $this->addValidationErrors('ShipFromAddress', 'is required!');
            return false;
        }
        if (!$this->ShipFromAddress->validate()) {
            $this->mergeValidationErrors($this->ShipFromAddress->getValidationErrors(), 'ShipFromAddress.');
            return false;
        }

        if (!$this->Weight) {
            $this->addValidationErrors('Weight', 'is required!');
            return false;
        }
        if (!$this->Weight->validate()) {
            $this->mergeValidationErrors($this->Weight->getValidationErrors(), 'Weight.');
            return false;
        }

        if (!$this->ShippingServiceOptions) {
            $this->addValidationErrors('ShippingServiceOptions', 'is required!');
            return false;
        }
        if (!$this->ShippingServiceOptions->validate()) {
            $this->mergeValidationErrors($this->ShippingServiceOptions->getValidationErrors(), 'ShippingServiceOptions.');
            return false;
        }

        return true;
    }
}
