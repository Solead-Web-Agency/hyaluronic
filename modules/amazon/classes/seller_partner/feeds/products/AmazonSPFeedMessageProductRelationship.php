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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/Relationship.xsd
 */
class AmazonSPFeedMessageProductRelationship extends AmazonSPFeedMessageProductDataGeneral
{
    protected $children = array();

    public function __construct($operationType, $sku, $children)
    {
        parent::__construct($operationType, $sku);
        $this->children = $this->sanityChildrenSku($children);
    }

    public function isValidMessage()
    {
        return parent::isValidMessage() && count($this->children);
    }

    private function sanityChildrenSku($children)
    {
        $result = array();
        foreach ($children as $child) {
            if ($child && $this->isValidSku($child)) {
                $result[] = $child;
            }
        }

        return $result;
    }

    protected function generateRemainingMessage($domDoc)
    {
        $parent = $domDoc->createElement('ParentSKU', $this->sku);

        $relationTags = array();
        foreach ($this->children as $childSku) {
            $relationSku = $domDoc->createElement('SKU', $childSku);
            $relationType = $domDoc->createElement('Type', 'Variation');
            $relation = $domDoc->createElement('Relation');
            $relation->appendChild($relationSku);
            $relation->appendChild($relationType);
            $relationTags[] = $relation;
        }

        $relationship = $domDoc->createElement('Relationship');
        $relationship->appendChild($parent);
        foreach ($relationTags as $relationTag) {
            $relationship->appendChild($relationTag);
        }

        return array($relationship);
    }
}
