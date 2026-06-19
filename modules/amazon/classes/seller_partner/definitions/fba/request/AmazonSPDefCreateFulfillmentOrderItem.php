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
class AmazonSPDefCreateFulfillmentOrderItem extends AmazonSPDefObject
{
    // Require
    public $sellerSku;
    public $sellerFulfillmentOrderItemId;
    public $quantity;   // integer

    // Optional
    public $giftMessage;
    public $displayableComment;
    public $fulfillmentNetworkSku;
    /** @var AmazonSPDefMoney */
    public $perUnitDeclaredValue;
    /** @var AmazonSPDefMoney */
    public $perUnitPrice;
    /** @var AmazonSPDefMoney */
    public $perUnitTax;

    // Custom
    public $processed = false;

    protected static $complexChildren = array(
        'perUnitDeclaredValue' => AmazonSPDefMoney::class,
        'perUnitPrice' => AmazonSPDefMoney::class,
        'perUnitTax' => AmazonSPDefMoney::class,
    );
    
    protected static $nonPrimitiveOptionalChildrenOnRequest = array(
        'perUnitDeclaredValue', 'perUnitPrice', 'perUnitTax'
    );

    public function validate()
    {
        return $this->sellerSku && $this->sellerFulfillmentOrderItemId && $this->quantity;
    }

    public function sanityItem()
    {
        $this->giftMessage = $this->giftMessage ? mb_substr($this->giftMessage, 0, 512) : '';
        $this->displayableComment = $this->displayableComment ? mb_substr($this->displayableComment, 0, 250) : '';

        return $this;
    }
    
    public function toAPI()
    {
        return array(
            'sellerSku' => $this->sellerSku,
            'sellerFulfillmentOrderItemId' => $this->sellerFulfillmentOrderItemId,
            'quantity' => $this->quantity,
            'giftMessage' => $this->giftMessage,
            'displayableComment' => $this->displayableComment,
            'perUnitDeclaredValue' => $this->perUnitDeclaredValue->toAPI(),
        );
    }
}
