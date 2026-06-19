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
class AmazonSPDefOrderItem extends AmazonSPDefObject
{
    public $ASIN;
    public $OrderItemId;
    public $QuantityOrdered;

    public $SellerSKU;
    public $Title;
    public $QuantityShipped;
    public $IsGift;
    public $BuyerInfo;  // ref=ItemBuyerInfo
    public $IossNumber;
    public $StoreChainStoreId = '';
    public $DeemedResellerCategory; // IOSS | UOSS
    public $PriceDesignation;
    public $PromotionIds = array();
    /** @var AmazonSPDefMoney */
    public $ItemPrice;
    /** @var AmazonSPDefMoney */
    public $ShippingPrice;
    /** @var AmazonSPDefMoney */
    public $ShippingDiscount;
    /** @var AmazonSPDefMoney */
    public $PromotionDiscount;
    /** @var AmazonSPDefMoney */
    public $ItemTax;
    /** @var AmazonSPDefMoney */
    public $ShippingTax;
    /** @var AmazonSPDefMoney */
    public $ShippingDiscountTax;
    /** @var AmazonSPDefMoney */
    public $PromotionDiscountTax;

    public function __construct($input)
    {
        parent::__construct($input);

        $moneyProperties = array(
            'ItemPrice', 'ItemTax',
            'ShippingPrice', 'ShippingTax',
            'ShippingDiscount', 'PromotionDiscount',
            'ShippingDiscountTax', 'PromotionDiscountTax'
        );

        foreach ($moneyProperties as $money) {
            $this->$money = new AmazonSPDefMoney(
                isset($input->$money) ? $input->$money : new stdClass()
            );
        }

        $this->BuyerInfo = new AmazonSPDefItemBuyerInfo(
            isset($input->BuyerInfo) ? $input->BuyerInfo : new stdClass()
        );
    }
}
