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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/ProductImage.xsd
 */
class AmazonSPFeedMessageProductImageData extends AmazonSPFeedMessageProductDataGeneral
{
    protected $imageUrl;
    protected $imageType;

    public function __construct($operationType, $sku, $imageUrl, $imageType)
    {
        parent::__construct($operationType, $sku);
        $this->imageUrl = $imageUrl;
        $this->imageType = $imageType;
    }

    public function isValidMessage()
    {
        return parent::isValidMessage() && $this->imageUrl && $this->imageType;
    }

    protected function generateRemainingMessage($domDoc)
    {
        $sku = $domDoc->createElement('SKU', $this->sku);
        $type = $domDoc->createElement('ImageType', $this->imageType);
        $location = $domDoc->createElement('ImageLocation', $this->imageUrl);

        $productImage = $domDoc->createElement('ProductImage');
        $productImage->appendChild($sku);
        $productImage->appendChild($type);
        $productImage->appendChild($location);

        return array($productImage);
    }
}
