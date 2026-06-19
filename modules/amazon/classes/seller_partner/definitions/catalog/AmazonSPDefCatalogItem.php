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
class AmazonSPDefCatalogItem extends AmazonSPDefObject
{
    // Required
    public $asin;   // string

    // Optional
    public $attributes;     // object
    /** @var AmazonSPDefItemDimensionsByMarketplace[] */
    public $dimensions;
    /** @var AmazonSPDefItemIdentifiersByMarketplace[] */
    public $identifiers;
    /** @var AmazonSPDefItemImagesByMarketplace[] */
    public $images;
    /** @var AmazonSPDefItemProductTypeByMarketplace[] */
    public $productTypes;
    /** @var AmazonSPDefItemRelationshipsByMarketplace[] */
    public $relationships;
    /** @var AmazonSPDefItemSalesRanksByMarketplace[] */
    public $salesRanks;
    /** @var AmazonSPDefItemSummaryByMarketplace[] */
    public $summaries;
    /** @var AmazonSPDefItemVendorDetailsByMarketplace[] */
    public $vendorDetails;

    protected static $listOfComplexChildren = array(
        'dimensions' => AmazonSPDefItemDimensionsByMarketplace::class,
        'identifiers' => AmazonSPDefItemIdentifiersByMarketplace::class,
        'images' => AmazonSPDefItemImagesByMarketplace::class,
        'productTypes' => AmazonSPDefItemProductTypeByMarketplace::class,
        'relationships' => AmazonSPDefItemRelationshipsByMarketplace::class,
        'salesRanks' => AmazonSPDefItemSalesRanksByMarketplace::class,
        'summaries' => AmazonSPDefItemSummaryByMarketplace::class,
        'vendorDetails' => AmazonSPDefItemVendorDetailsByMarketplace::class,
    );

    public function getLang()
    {
        $languageTag = $this->tryToParseLanguageTagFromAttributes();
        if (!$languageTag) {
            return null;
        }

        if (strstr($languageTag, '_')) {
            return explode('_', $languageTag)[0];
        }

        return $languageTag;
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemRelationship|null
     */
    public function getVariationRelationship($mkp)
    {
        $relationships = $this->getRelationshipsByMkp($mkp);
        foreach ($relationships as $relationship) {
            if ($relationship->relationships) {
                foreach ($relationship->relationships as $relationType) {
                    if ($relationType->type == AmazonSPDefItemRelationship::TYPE_VARIATION) {
                        return $relationType;
                    }
                }
            }
        }

        return null;
    }

    public function getParentAsins($mkp)
    {
        $relationships = $this->getRelationshipsByMkp($mkp);
        foreach ($relationships as $relationship) {
            if ($relationship->relationships) {
                foreach ($relationship->relationships as $relationType) {
                    if ($relationType->parentAsins && count($relationType->parentAsins)) {
                        return $relationType->parentAsins;
                    }
                }
            }
        }

        return array();
    }

    public function getChildAsins($mkp)
    {
        $relationships = $this->getRelationshipsByMkp($mkp);
        foreach ($relationships as $relationship) {
            if ($relationship->relationships) {
                foreach ($relationship->relationships as $relationType) {
                    if ($relationType->childAsins && count($relationType->childAsins)) {
                        return $relationType->childAsins;
                    }
                }
            }
        }

        return array();
    }

    public function getVariationChildAsins($mkp)
    {
        $relationships = $this->getRelationshipsByMkp($mkp);
        foreach ($relationships as $relationship) {
            if ($relationship->relationships) {
                foreach ($relationship->relationships as $relationType) {
                    if ($relationType->variationTheme == AmazonSPDefItemRelationship::TYPE_VARIATION
                        && count($relationType->childAsins)) {
                        return $relationType->childAsins;
                    }
                }
            }
        }

        return array();
    }

    public function getBrowseNodeName($mkp)
    {
        // May exist in summary
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->browseClassification && $summary->browseClassification->classificationId) {
                return $summary->browseClassification->displayName;
            }
        }

        // Otherwise, search in salesRanks
        $salesRanks = $this->getSalesRanksByMkp($mkp);
        foreach ($salesRanks as $salesRank) {
            if ($salesRank->classificationRanks) {
                foreach ($salesRank->classificationRanks as $classificationRank) {
                    // Take top node
                    if ($classificationRank->title) {
                        return $classificationRank->title;
                    }
                }
            }
        }

        return null;
    }

    public function getBrowseNodeId($mkp)
    {
        // May exist in summary
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->browseClassification && $summary->browseClassification->classificationId) {
                return $summary->browseClassification->classificationId;
            }
        }

        // Otherwise, search in salesRanks
        $salesRanks = $this->getSalesRanksByMkp($mkp);
        foreach ($salesRanks as $salesRank) {
            if ($salesRank->classificationRanks) {
                foreach ($salesRank->classificationRanks as $classificationRank) {
                    // Take top node
                    if ($classificationRank->classificationId) {
                        return $classificationRank->classificationId;
                    }
                }
            }
        }

        return null;
    }

    public function getBrand($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->brand) {
                return $summary->brand;
            }
        }

        return '';
    }

    public function getColor($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->color) {
                return $summary->color;
            }
        }

        return '';
    }

    public function getItemName($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->itemName) {
                return $summary->itemName;
            }
        }

        return '';
    }

    public function getManufacturer($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->manufacturer) {
                return $summary->manufacturer;
            }
        }

        return '';
    }

    public function getPackageQuantity($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->packageQuantity) {
                return $summary->packageQuantity;
            }
        }

        return null;
    }

    public function getPartNumber($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->partNumber) {
                return $summary->partNumber;
            }
        }

        return '';
    }

    public function getSize($mkp)
    {
        $summaries = $this->getSummariesByMkp($mkp);
        foreach ($summaries as $summary) {
            if ($summary->size) {
                return $summary->size;
            }
        }

        return '';
    }

    public function getLargeImage($mkp)
    {
        $images = $this->getImagesByMkp($mkp);
        foreach ($images as $image) {
            if ($image->images) {
                foreach ($image->images as $img) {
                    if ($img->variant == AmazonSPDefItemImage::MAIN) {
                        return $img->link;
                    }
                }
            }
        }

        return null;
    }

    public function getDimensions($mkp)
    {
        $dimensions = $this->getDimensionsByMkp($mkp);
        // Take the first one
        if ($dimensions) {
            return reset($dimensions);
        }

        return null;
    }

    public function getIdentifierSKU($mkp)
    {
        $identifiers = $this->getIdentifiersByMkp($mkp);
        foreach ($identifiers as $identifier) {
            if ($identifier->identifiers) {
                foreach ($identifier->identifiers as $mkpIdentifier) {
                    $sku = $mkpIdentifier->getSKU();
                    if ($sku) {
                        return $sku;
                    }
                }
            }
        }

        return '';
    }

    public function getProductType($mkp)
    {
        $productTypes = $this->getProductTypesMkp($mkp);
        foreach ($productTypes as $productType) {
            return $productType->productType;
        }

        return '';
    }

    public function getAttributesByMkp($mkp)
    {
        $result = new stdClass();
        $attributes = $this->attributes;

        if ($attributes) {
            foreach ($attributes as $name => $values) {
                if ($values) {
                    foreach ($values as $value) {
                        if (isset($value->marketplace_id, $value->value) && $value->marketplace_id == $mkp) {
                            $result->$name = $value->value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemDimensionsByMarketplace[]
     */
    public function getDimensionsByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'dimensions');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemIdentifiersByMarketplace[]
     */
    public function getIdentifiersByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'identifiers');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemImagesByMarketplace[]
     */
    public function getImagesByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'images');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemProductTypeByMarketplace[]
     */
    public function getProductTypesMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'productTypes');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemRelationshipsByMarketplace[]
     */
    public function getRelationshipsByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'relationships');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemSalesRanksByMarketplace[]
     */
    public function getSalesRanksByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'salesRanks');
    }

    /**
     * @param $mkp
     * @return AmazonSPDefItemSummaryByMarketplace[]
     */
    public function getSummariesByMkp($mkp)
    {
        return $this->getPropertyByMkp($mkp, 'summaries');
    }

    private function tryToParseLanguageTagFromAttributes()
    {
        if ($this->attributes) {
            foreach ($this->attributes as $attrs) {
                if (count($attrs)) {
                    foreach ($attrs as $attr) {
                        if (isset($attr->language_tag)) {
                            return $attr->language_tag;
                        }
                    }
                }
            }
        }

        return '';
    }

    private function getPropertyByMkp($mkp, $propertyName)
    {
        $result = array();
        $validProperties = array('dimensions', 'identifiers', 'images', 'productTypes', 'relationships', 'salesRanks', 'summaries', 'vendorDetails');

        if (in_array($propertyName, $validProperties)) {
            if ($this->$propertyName && count($this->$propertyName)) {
                foreach ($this->$propertyName as $propertyByMkp) {
                    if (isset($propertyByMkp->marketplaceId) && $propertyByMkp->marketplaceId == $mkp) {
                        $result[] = $propertyByMkp;
                    }
                }
            }
        }

        return $result;
    }
}
