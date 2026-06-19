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
class AmazonSPFeedMessageProductDataGeneral extends AmazonSPFeedMessage
{
    protected $sku;

    /** @var DOMElement */
    protected $generatedProduct;

    public function __construct($operationType, $sku)
    {
        parent::__construct($operationType);
        $this->sku = $sku;
    }

    protected function generateRemainingMessage($domDoc)
    {
        $sku = $domDoc->createElement('SKU', $this->sku);
        $product = $domDoc->createElement('Product');
        $product->appendChild($sku);
        $this->generatedProduct = $product;

        return array($product);
    }

    public function isValidMessage()
    {
        return $this->isValidSku($this->sku);
    }

    protected function isValidSku($sku)
    {
        return !empty($sku) && preg_match('/[\x00-\xFF]{1,40}/', $sku) && preg_match('/[^ ]$/', $sku);
    }
}
