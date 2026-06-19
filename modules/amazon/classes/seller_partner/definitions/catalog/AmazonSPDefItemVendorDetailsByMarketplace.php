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
class AmazonSPDefItemVendorDetailsByMarketplace extends AmazonSPDefObject
{
    // Required
    public $marketplaceId;  // string
    
    // Optional
    public $brandCode;              // string
    public $manufacturerCode;       // string
    public $manufacturerCodeParent; // string
    /** @var AmazonSPDefItemVendorDetailsCategory */
    public $productCategory;
    public $productGroup;           // string
    /** @var AmazonSPDefItemVendorDetailsCategory */
    public $productSubcategory;
    public $replenishmentCategory;  // ALLOCATED, BASIC_REPLENISHMENT, IN_SEASON, LIMITED_REPLENISHMENT, MANUFACTURER_OUT_OF_STOCK, NEW_PRODUCT, NON_REPLENISHABLE, NON_STOCKUPABLE, OBSOLETE, PLANNED_REPLENISHMENT
    
    protected static $complexChildren = array(
        'productCategory' => AmazonSPDefItemVendorDetailsCategory::class,
        'productSubcategory' => AmazonSPDefItemVendorDetailsCategory::class,
    );
}
