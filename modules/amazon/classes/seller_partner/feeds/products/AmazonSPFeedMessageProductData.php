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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/Product.xsd
 */
class AmazonSPFeedMessageProductData extends AmazonSPFeedMessageProductDataGeneral
{
    protected $productIdType;
    protected $productIdCode;
    protected $conditionType;
    protected $conditionNode;
    protected $productDesc;
    protected $productData = array();
    
    public function __construct($operationType, $sku, $productIdType, $productIdCode, $conditionType, $conditionNode, $productDesc, $productData)
    {
        parent::__construct($operationType, $sku);
        
        $this->productIdType = $productIdType;
        $this->productIdCode = $productIdCode;
        $this->conditionType = $conditionType;
        $this->conditionNode = $conditionNode;
        $this->productDesc = $productDesc;
        $this->productData = $productData;
    }

    public function isValidMessage()
    {
        return parent::isValidMessage()
            && $this->isValidProductTypeAndCode()
            && $this->isValidConditionType()
            && $this->isValidConditionNote();
    }
    
    private function isValidProductTypeAndCode()
    {
        // Product type & code must be both presented or both empty
        return $this->productIdType && $this->productIdCode || !$this->productIdType && !$this->productIdCode;
    }
    
    private function isValidConditionType()
    {
        $validConditions = array(
            'New',
            'UsedLikeNew', 'UsedVeryGood', 'UsedGood', 'UsedAcceptable',
            'CollectibleLikeNew', 'CollectibleVeryGood', 'CollectibleGood', 'CollectibleAcceptable',
            'Refurbished', 'Club'
        );

        return !$this->conditionType || in_array($this->conditionType, $validConditions);
    }
    
    private function isValidConditionNote()
    {
        // If note is presented, type must too
        return !$this->conditionNode || $this->conditionType;
    }

    /**
     * @throws DOMException
     */
    protected function generateRemainingMessage($Document)
    {
        parent::generateRemainingMessage($Document);

        $Product = $this->generatedProduct;
        $ProductIDType = $this->productIdType;
        $ProductIDCode = $this->productIdCode;
        $ProductData = $this->productData;
        $ConditionType = $this->conditionType;
        $ConditionNote = $this->conditionNode;
        $ProductDescription = $this->productDesc;

        // Legacy code
        if ($ProductIDType != null && $ProductIDCode != null) {
            $StandardProductID = $Document->createElement('StandardProductID');
            $Product->appendChild($StandardProductID);
            $Type = $Document->createElement('Type');
            $StandardProductID->appendChild($Type);
            $TypeText = $Document->createTextNode($ProductIDType);
            $Type->appendChild($TypeText);
            $Value = $Document->createElement('Value');
            $StandardProductID->appendChild($Value);
            $ValueText = $Document->createTextNode($ProductIDCode);
            $Value->appendChild($ValueText);
        }

        // 2018-05-29 Adding ItemPackageQuantity
        //
        if ($ProductData && isset($ProductData['ProductTaxCode'])) {
            $ProductTaxCodeTag = $Document->createElement('ProductTaxCode');
            $ProductTaxCodeTag->appendChild($Document->createTextNode($ProductData['ProductTaxCode']));
            $Product->appendChild($ProductTaxCodeTag);
        }

        if ($ConditionType != null) {
            $ConditionX = $Document->createElement('Condition');
            $Product->appendChild($ConditionX);
            $ConditionTypeX = $Document->createElement('ConditionType');
            $ConditionX->appendChild($ConditionTypeX);
            $ConditionTypeText = $Document->createTextNode($ConditionType);
            $ConditionTypeX->appendChild($ConditionTypeText);
            if ($ConditionNote != null) {
                $ConditionNoteX = $Document->createElement('ConditionNote');
                $ConditionX->appendChild($ConditionNoteX);
                $ConditionNoteText = $Document->createTextNode($ConditionNote);
                $ConditionNoteX->appendChild($ConditionNoteText);
            }
        }

        // 2013-03-29 Adding ItemPackageQuantity
        //
        if ($ProductData && isset($ProductData['ItemPackageQuantity'])) {
            $ItemPackageQuantityTag = $Document->createElement('ItemPackageQuantity');
            $ItemPackageQuantityTag->appendChild($Document->createTextNode($ProductData['ItemPackageQuantity']));
            $Product->appendChild($ItemPackageQuantityTag);
            unset($ProductData['ItemPackageQuantity']);
        }


        // 2014-02-26 Adding NumberOfItems
        // 2022-05-23 Amazon inconsistency in attribute name, now it includes attribute name with diff case: Numberofitems
        $numberOfItemsval = isset($ProductData['NumberOfItems']) ? $ProductData['NumberOfItems'] : (
        isset($ProductData['Numberofitems']) ? $ProductData['Numberofitems'] : false
        );
        if ($ProductData
            && $numberOfItemsval !== false
        ) {
            $NumberOfItemsTag = $Document->createElement('NumberOfItems');
            $NumberOfItemsTag->appendChild($Document->createTextNode($numberOfItemsval));
            $Product->appendChild($NumberOfItemsTag);
            unset($ProductData['NumberOfItems']);
            unset($ProductData['Numberofitems']);
        }

        //floag used to identify a parent SKU and excluded additional data fields from XML feed
        $is_parent = isset($ProductData['Parentage']) && $ProductData['Parentage'] == 'parent';
        $parameterXSD = isset($ProductData['Parameters'], $ProductData['Parameters']['xsd'])
            ? $ProductData['Parameters']['xsd'] : array();
        // Send Product Informations
        //
        if (is_array($ProductDescription)) {
            $x = new DOMXPath($Document);
            $DescriptionDataTag = null;

            if (!$is_parent) {
                if (isset($ProductDescription['ShirtSizeClass']) && !isset($ProductData['ShirtSizeClass'])) {
                    $ProductData['ShirtSizeClass'] = $ProductDescription['ShirtSizeClass'];
                    unset($ProductDescription['ShirtSizeClass']);
                }
                
                $DescriptionDataTag = $this->createDescriptionDataNode($Document, $ProductData, $ProductDescription);
            } elseif (is_array($ProductData)) {
                //remove fields that would be included and may be required
                foreach ($ProductData as $key => $value) {
                    if (isset(AmazonSPXSD::$descriptionDataAdditionalFields[$key])) {
                        if (isset(AmazonSPXSD::$descriptionDataAdditionalFields[$key]['includeInParent'])
                            && AmazonSPXSD::$descriptionDataAdditionalFields[$key]['includeInParent']
                        ) {
                            continue;
                        }
                        unset($ProductData['Parameters'][$key]);
                    }
                }
            }

            if ($DescriptionDataTag == null) {
                $DescriptionDataTag = $Document->createElement('DescriptionData');
            }

            if (isset($ProductDescription['Title'])) {
                $TitleTag = $Document->createElement('Title');
                $DescriptionDataTag->appendChild($TitleTag);

                $TitleText = $Document->createTextNode($ProductDescription['Title']);
                $TitleTag->appendChild($TitleText);
            }

            if (isset($ProductDescription['Brand']) && !empty($ProductDescription['Brand'])) {
                $BrandTag = $Document->createElement('Brand');
                $DescriptionDataTag->appendChild($BrandTag);
                $BrandText = $Document->createTextNode($ProductDescription['Brand']);
                $BrandTag->appendChild($BrandText);
            }

            if (isset($ProductDescription['Title']) && isset($ProductDescription['Description'])) {
                $DescriptionTag = $Document->createElement('Description');
                $DescriptionDataTag->appendChild($DescriptionTag);

                $DescriptionText = $Document->createTextNode($ProductDescription['Description']);
                $DescriptionTag->appendChild($DescriptionText);
            }

            if (!$is_parent) {
                if (isset($ProductDescription['BulletPoint']) && is_array($ProductDescription['BulletPoint'])) {
                    $count = 1;
                    foreach ($ProductDescription['BulletPoint'] as $BulletPoint) {
                        if ($count > 5) {
                            break;
                        }

                        $BulletPointTag = $Document->createElement('BulletPoint');
                        $BulletPointTag->appendChild($Document->createTextNode($BulletPoint));

                        $DescriptionDataTag->appendChild($BulletPointTag);
                        $count++;
                    }
                }


                $xQuery = 'ItemDimensions'; //assign
                $exists = $x->query($xQuery, $DescriptionDataTag)->length > 0;

                if (isset($ProductDescription['ItemDimensions']) && is_array($ProductDescription['ItemDimensions']) && !$exists) {
                    $ItemDimensionsTag = $Document->createElement('ItemDimensions');
                    $DescriptionDataTag->appendChild($ItemDimensionsTag);

                    if (isset($ProductDescription['ItemDimensions']['Length']['value'])) {
                        $ItemDimensionsLengthTag = $Document->createElement('Length');
                        $ItemDimensionsTag->appendChild($ItemDimensionsLengthTag);

                        $ItemDimensionsLengthTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Length']['value']));
                        $ItemDimensionsLengthTag->setAttribute('unitOfMeasure',
                            $ProductDescription['ItemDimensions']['Length']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['ItemDimensions']['Width']['value'])) {
                        $ItemDimensionsWidthTag = $Document->createElement('Width');
                        $ItemDimensionsTag->appendChild($ItemDimensionsWidthTag);

                        $ItemDimensionsWidthTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Width']['value']));
                        $ItemDimensionsWidthTag->setAttribute('unitOfMeasure',
                            $ProductDescription['ItemDimensions']['Width']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['ItemDimensions']['Height']['value'])) {
                        $ItemDimensionsHeightTag = $Document->createElement('Height');
                        $ItemDimensionsTag->appendChild($ItemDimensionsHeightTag);

                        $ItemDimensionsHeightTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Height']['value']));
                        $ItemDimensionsHeightTag->setAttribute('unitOfMeasure',
                            $ProductDescription['ItemDimensions']['Height']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['ItemDimensions']['Weight']['value'])) {
                        $ItemDimensionsWeightTag = $Document->createElement('Weight');
                        $ItemDimensionsTag->appendChild($ItemDimensionsWeightTag);

                        $ItemDimensionsWeightTag->appendChild($Document->createTextNode($ProductDescription['ItemDimensions']['Weight']['value']));
                        $ItemDimensionsWeightTag->setAttribute('unitOfMeasure',
                            $ProductDescription['ItemDimensions']['Weight']['unitOfMeasure']);
                    }
                }

                $xQuery = 'PackageDimensions'; //assign
                $exists = $x->query($xQuery, $DescriptionDataTag)->length > 0;
                if (isset($ProductDescription['PackageDimensions']) && is_array($ProductDescription['PackageDimensions']) && !$exists) {
                    $PackageDimensionsTag = $Document->createElement('PackageDimensions');
                    $DescriptionDataTag->appendChild($PackageDimensionsTag);

                    if (isset($ProductDescription['PackageDimensions']['Length']['value'])) {
                        $PackageDimensionsLengthTag = $Document->createElement('Length');
                        $PackageDimensionsTag->appendChild($PackageDimensionsLengthTag);

                        $PackageDimensionsLengthTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Length']['value']));
                        $PackageDimensionsLengthTag->setAttribute('unitOfMeasure',
                            $ProductDescription['PackageDimensions']['Length']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['PackageDimensions']['Width']['value'])) {
                        $PackageDimensionsWidthTag = $Document->createElement('Width');
                        $PackageDimensionsTag->appendChild($PackageDimensionsWidthTag);

                        $PackageDimensionsWidthTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Width']['value']));
                        $PackageDimensionsWidthTag->setAttribute('unitOfMeasure',
                            $ProductDescription['PackageDimensions']['Width']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['PackageDimensions']['Height']['value'])) {
                        $PackageDimensionsHeightTag = $Document->createElement('Height');
                        $PackageDimensionsTag->appendChild($PackageDimensionsHeightTag);

                        $PackageDimensionsHeightTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Height']['value']));
                        $PackageDimensionsHeightTag->setAttribute('unitOfMeasure',
                            $ProductDescription['PackageDimensions']['Height']['unitOfMeasure']);
                    }

                    if (isset($ProductDescription['PackageDimensions']['Weight']['value'])) {
                        $PackageDimensionsWeightTag = $Document->createElement('Weight');
                        $PackageDimensionsTag->appendChild($PackageDimensionsWeightTag);

                        $PackageDimensionsWeightTag->appendChild($Document->createTextNode($ProductDescription['PackageDimensions']['Weight']['value']));
                        $PackageDimensionsWeightTag->setAttribute('unitOfMeasure',
                            $ProductDescription['PackageDimensions']['Weight']['unitOfMeasure']);
                    }
                }

                if (isset($ProductDescription['PackageWeight']) && !empty($ProductDescription['PackageWeight'])) {
                    $PackageWeightTag = $Document->createElement('PackageWeight', $ProductDescription['PackageWeight']);
                    $PackageWeightTag->setAttribute('unitOfMeasure', $ProductDescription['PackageWeightUnit']);
                    $DescriptionDataTag->appendChild($PackageWeightTag);
                }

                if (isset($ProductDescription['ShippingWeight']) && !empty($ProductDescription['ShippingWeight'])) {
                    $PackageWeightTag = $Document->createElement('ShippingWeight',
                        $ProductDescription['ShippingWeight']);
                    $PackageWeightTag->setAttribute('unitOfMeasure', $ProductDescription['ShippingWeightUnit']);
                    $DescriptionDataTag->appendChild($PackageWeightTag);
                }

                if (isset($ProductDescription['MerchantCatalogNumber']) && !empty($ProductDescription['MerchantCatalogNumber'])) {
                    $MerchantCatalogNumber = $Document->createElement('MerchantCatalogNumber');
                    $MerchantCatalogNumber->appendChild($Document->createTextNode(mb_substr($ProductDescription['MerchantCatalogNumber'],
                        0, 40)));
                    $DescriptionDataTag->appendChild($MerchantCatalogNumber);
                }

                if (isset($ProductDescription['CPSIAWarningDescription']) && !empty($ProductDescription['CPSIAWarningDescription'])) {
                    $CPSIAWarningDescription = $Document->createElement('CPSIAWarningDescription');
                    $CPSIAWarningDescription->appendChild($Document->createTextNode($ProductDescription['CPSIAWarningDescription']));
                    $DescriptionDataTag->appendChild($CPSIAWarningDescription);
                }

                if (isset($ProductDescription['CPSIAWarning']) && !empty($ProductDescription['CPSIAWarning'])) {
                    if (is_array($ProductDescription['CPSIAWarning'])) {
                        $result = preg_split('/[,; ]/', $ProductDescription['CPSIAWarning'][0]);
                    } else {
                        $result = preg_split('/[,; ]/', $ProductDescription['CPSIAWarning']);
                    }

                    if (is_array($result)) {
                        foreach ($result as $browsenode) {
                            if (empty($browsenode)) {
                                continue;
                            }
                            $CPSIAWarningTag = $Document->createElement('CPSIAWarning');
                            $DescriptionDataTag->appendChild($CPSIAWarningTag);
                            $CPSIAWarningText = $Document->createTextNode($browsenode);
                            $CPSIAWarningTag->appendChild($CPSIAWarningText);
                        }
                    }
                }
            }

            if (isset($ProductDescription['Manufacturer']) && !empty($ProductDescription['Manufacturer'])) {
                $ManufacturerTag = $Document->createElement('Manufacturer');
                $DescriptionDataTag->appendChild($ManufacturerTag);
                $ManufacturerText = $Document->createTextNode($ProductDescription['Manufacturer']);
                $ManufacturerTag->appendChild($ManufacturerText);
            }

            // Aplly UnitCount and PPUCountType(UnitCountType) for all universe
            if (isset($ProductDescription['UnitCount']) && !empty($ProductDescription['UnitCount'])) {
                $unitCountTag = $Document->createElement('UnitCount');
                $unitCountTag->appendChild($Document->createTextNode($ProductDescription['UnitCount']));
                $DescriptionDataTag->appendChild($unitCountTag);
            }

            if (isset($ProductDescription['PPUCountType']) && !empty($ProductDescription['PPUCountType'])) {
                $unitCountType = $Document->createElement('PPUCountType');
                $unitCountType->appendChild($Document->createTextNode($ProductDescription['PPUCountType']));
                $DescriptionDataTag->appendChild($unitCountType);
            }

            // Updated: 2023/07/12
            if (isset($ProductDescription['SearchTerms'])) {
                if (is_array($ProductDescription['SearchTerms']) && count($ProductDescription['SearchTerms'])) {
                    foreach ($ProductDescription['SearchTerms'] as $searchTerms) {
                        $SearchTermsTag = $Document->createElement('SearchTerms');
                        $DescriptionDataTag->appendChild($SearchTermsTag);
                        $SearchTermsText = $Document->createTextNode($searchTerms);
                        $SearchTermsTag->appendChild($SearchTermsText);
                    }
                } elseif (is_string($ProductDescription['SearchTerms']) && !empty($ProductDescription['SearchTerms'])) {
                    $SearchTermsTag = $Document->createElement('SearchTerms');
                    $DescriptionDataTag->appendChild($SearchTermsTag);
                    $SearchTermsText = $Document->createTextNode($ProductDescription['SearchTerms']);
                    $SearchTermsTag->appendChild($SearchTermsText);
                }
            }

            /**
             * Added: 2023-07-31
             * Source: https://common-services-force.monday.com/boards/1766076400/pulses/4895190938
             */
            if (isset($ProductDescription['MSRP']) && $ProductDescription['MSRP'] >= 0 && !empty($ProductDescription['MSRPCurrency'])) {
                $MSRPTag = $Document->createElement('MSRP', $ProductDescription['MSRP']);
                $MSRPTag->setAttribute('currency', $ProductDescription['MSRPCurrency']);
                $DescriptionDataTag->appendChild($MSRPTag);
            }

            if (isset($ProductDescription['UVPListPrice']) && $ProductDescription['UVPListPrice'] >= 0 && !empty($ProductDescription['UVPListPriceCurrency'])) {
                $UVPListPriceTag = $Document->createElement('UVPListPrice', $ProductDescription['UVPListPrice']);
                $UVPListPriceTag->setAttribute('currency', $ProductDescription['UVPListPriceCurrency']);
                $DescriptionDataTag->appendChild($UVPListPriceTag);
            }

            if (!$is_parent) {
                if (isset($ProductDescription['MfrPartNumber']) && !empty($ProductDescription['MfrPartNumber'])) {
                    $MfrPartNumberTag = $Document->createElement('MfrPartNumber');
                    $DescriptionDataTag->appendChild($MfrPartNumberTag);
                    $MfrPartNumberText = $Document->createTextNode($ProductDescription['MfrPartNumber']);
                    $MfrPartNumberTag->appendChild($MfrPartNumberText);
                }

                // Added : 2014/03/13
                //
                if (isset($ProductDescription['ItemType']) && !empty($ProductDescription['ItemType'])) {
                    $ItemTypeTag = $Document->createElement('ItemType');
                    $DescriptionDataTag->appendChild($ItemTypeTag);
                    $ItemTypeText = $Document->createTextNode($ProductDescription['ItemType']);
                    $ItemTypeTag->appendChild($ItemTypeText);
                }

                // Added: 2021-jan-26 - Erick T.
                if (isset($ProductDescription['TargetAudience']) && !empty($ProductDescription['TargetAudience'])) {
                    $TargetAudience = $Document->createElement('TargetAudience');
                    $DescriptionDataTag->appendChild($TargetAudience);
                    $TargetAudienceText = $Document->createTextNode(is_array($ProductDescription['TargetAudience']) ? $ProductDescription['TargetAudience'][0] : $ProductDescription['TargetAudience']);
                    $TargetAudience->appendChild($TargetAudienceText);
                }

                // Added : 2014/10/15
                //
                if (isset($ProductDescription['IsGiftWrapAvailable'])) {
                    $IsGiftWrapAvailableTag = $Document->createElement('IsGiftWrapAvailable');
                    $DescriptionDataTag->appendChild($IsGiftWrapAvailableTag);
                    $IsGiftWrapAvailableText = $Document->createTextNode($ProductDescription['IsGiftWrapAvailable'] ? 'true' : 'false');
                    $IsGiftWrapAvailableTag->appendChild($IsGiftWrapAvailableText);

                    $IsGiftMessageAvailableTag = $Document->createElement('IsGiftMessageAvailable');
                    $DescriptionDataTag->appendChild($IsGiftMessageAvailableTag);
                    $IsGiftMessageAvailableText = $Document->createTextNode($ProductDescription['IsGiftMessageAvailable'] ? 'true' : 'false');
                    $IsGiftMessageAvailableTag->appendChild($IsGiftMessageAvailableText);
                }

                if (isset($ProductDescription['RecommendedBrowseNode']) && !empty($ProductDescription['RecommendedBrowseNode'])) {
                    if (is_array($ProductDescription['RecommendedBrowseNode'])) {
                        $result = preg_split('/[,; ]/', $ProductDescription['RecommendedBrowseNode'][0]);
                    } else {
                        $result = preg_split('/[,; ]/', $ProductDescription['RecommendedBrowseNode']);
                    }
                    
                    if (is_array($result)) {
                        $count = 1;
                        foreach ($result as $browsenode) {
                            if (empty($browsenode) || !is_numeric($browsenode)) {
                                continue;
                            }

                            $RecommendedBrowseNodeTag = $Document->createElement('RecommendedBrowseNode');
                            $DescriptionDataTag->appendChild($RecommendedBrowseNodeTag);
                            $RecommendedBrowseNodeText = $Document->createTextNode($browsenode);
                            $RecommendedBrowseNodeTag->appendChild($RecommendedBrowseNodeText);

                            if ($count++ >= 2) {
                                break;
                            }
                        }
                    }
                }
                

                // Added: 2023-may-24 - Erick T.
                if (isset($ProductData['Itemweight']) 
                    && !empty($ProductData['Itemweight'])) {
                    
                    //prevent duplicate
                    $toDelete = $DescriptionDataTag->getElementsByTagName("ItemWeight");
                    foreach($toDelete as $node){
                        $node->parentNode->removeChild($node);
                    }
                    $ItemWeightTag = $Document->createElement('ItemWeight');
                    $DescriptionDataTag->appendChild($ItemWeightTag);
                    $ItemWeightText = $Document->createTextNode($ProductData['Itemweight']);
                    $ItemWeightTag->appendChild($ItemWeightText);
                    $ItemWeightTag->setAttribute('unitOfMeasure', 
                                        isset($ProductData['Attributes']) 
                                        && isset($ProductData['Attributes']['Itemweight']) 
                                        && isset($ProductData['Attributes']['Itemweight']['unitOfMeasure']) 
                                        ? $ProductData['Attributes']['Itemweight']['unitOfMeasure']
                                        : '');

                   //prevent duplicate entry
                   unset($ProductData['Itemweight']);                     
                }

                // New: Add the IsExpirationDatedProduct
                $description_fields = array('IsExpirationDatedProduct');
                foreach ($description_fields as $description_field) {
                    if (isset($ProductDescription[$description_field])) {
                        if (is_array($ProductDescription[$description_field]) && count($ProductDescription[$description_field])) {
                            foreach ($ProductDescription[$description_field] as $item) {
                                $temp_tag = $Document->createElement($description_field);
                                $DescriptionDataTag->appendChild($temp_tag);
                                $tempText = $Document->createTextNode($item);
                                $temp_tag->appendChild($tempText);
                            }
                        } elseif (is_string($ProductDescription[$description_field]) && !empty($ProductDescription[$description_field])) {
                            $temp_tag = $Document->createElement($description_field);
                            $DescriptionDataTag->appendChild($temp_tag);
                            $tempText = $Document->createTextNode($ProductDescription[$description_field]);
                            $temp_tag->appendChild($tempText);
                        }
                    }
                }
                if (isset($ProductDescription['ProductExpirationType'])) {
                    $ProductExpirationTypeTag = $Document->createElement('ProductExpirationType');
                    $DescriptionDataTag->appendChild($ProductExpirationTypeTag);
                    $ProductExpirationTypeText = $Document->createTextNode($ProductDescription['ProductExpirationType']);
                    $ProductExpirationTypeTag->appendChild($ProductExpirationTypeText);
                }
                
                // Added : 2015/12/17
                //
                if (isset($ProductDescription['MerchantShippingGroupName'])) {
                    $merchantShippingGroupNameTag = $Document->createElement('MerchantShippingGroupName');
                    $DescriptionDataTag->appendChild($merchantShippingGroupNameTag);
                    $merchantShippingGroupNameText = $Document->createTextNode($ProductDescription['MerchantShippingGroupName']);
                    $merchantShippingGroupNameTag->appendChild($merchantShippingGroupNameText);
                }
            }

            if ($is_parent && isset($ProductData['CountryOfOrigin'])) {
                $xQuery = 'CountryOfOrigin'; //assign
                $exists = $x->query($xQuery, $DescriptionDataTag)->length > 0;
                if (!$exists) {
                    $CountryOfOriginTag = $Document->createElement('CountryOfOrigin');
                    $DescriptionDataTag->appendChild($CountryOfOriginTag);
                    $CountryOfOriginText = $Document->createTextNode($ProductData['CountryOfOrigin']);
                    $CountryOfOriginTag->appendChild($CountryOfOriginText);
                    unset($ProductData['CountryOfOrigin']);
                }
            }

            $this->appendDescriptionData(
                $Document, $Product, $DescriptionDataTag, $ProductDescription, $parameterXSD
            );
        }

        // Clothes exception
        if (isset($ProductData['ClassificationData'])) {
            $productDataNode = $this->clothingToXmlNode($Document, $ProductData, 'ClassificationData');
        } else {
            $productDataNode = $this->convertToXmlNode($Document, $ProductData, 'ProductData');
        }

        //ORDER ELEMENTS ACCORDING TO XSD
        if ($productDataNode && count($parameterXSD)) {
            $productDataNode = $this->sortProductData($Document, $productDataNode, $parameterXSD);
        }

        // 2016-02-28 Promotag
        //
        if ($ProductData && isset($ProductData['PromoTag']) && $ProductData['PromoTag']) {
            $PromoTag = $Document->createElement('PromoTag');
            $promoTagType = $Document->createElement('PromoTagType');
            $effectiveFromDate = $Document->createElement('EffectiveFromDate');
            $effectiveThroughDate = $Document->createElement('EffectiveThroughDate');
            
            $PromoTag->appendChild($promoTagType);
            $PromoTag->appendChild($effectiveFromDate);
            $PromoTag->appendChild($effectiveThroughDate);

            $promoTagType->appendChild($Document->createTextNode($ProductData['PromoTag']));
            $effectiveFromDate->appendChild($Document->createTextNode($ProductData['EffectiveFromDate']));
            $effectiveThroughDate->appendChild($Document->createTextNode($ProductData['EffectiveThroughDate']));

            $Product->appendChild($PromoTag);
        }

        if ($productDataNode) {
            $Product->appendChild($productDataNode);
        }

        // 2016-02-28 EnhancedImageURL
        //
        if ($ProductData && isset($ProductData['EnhancedImageURL']) && $ProductData['EnhancedImageURL']) {
            $EnhancedImageURLTag = $Document->createElement('EnhancedImageURL');
            $EnhancedImageURLTag->appendChild($Document->createTextNode($ProductData['EnhancedImageURL']));
            $Product->appendChild($EnhancedImageURLTag);
        }
        // 2013-03-23 Adding EAN/UPC Exemption
        //
        if ($ProductData && isset($ProductData['RegisteredParameter'])) {
            $RegisteredParameterTag = $Document->createElement('RegisteredParameter');
            $RegisteredParameterTag->appendChild($Document->createTextNode($ProductData['RegisteredParameter']));
            $Product->appendChild($RegisteredParameterTag);
        }

        //additional fields defined out of ProductType structure, but inside Product structure
        foreach (AmazonSPXSD::$productXsdGenericFields as $field => $fieldData) {
            if (isset($ProductData[$field])) {
                $fieldArray = $ProductData[$field];
                if (!is_array($ProductData[$field])) {
                    $fieldArray = array($fieldArray);
                }
                unset($ProductData[$field]);
                if ($field == "ItemForm" && $ProductData['Definition'] != 'FoodAndBeverages'){
                    continue;
                }
                foreach ($fieldArray as $idx => $fieldArrayValue) {
                    $additionalField = $Document->createElement($field);
                    $additionalField->appendChild($Document->createTextNode($fieldArrayValue));
                    $Product->appendChild($additionalField);
                    if (isset($ProductData['Attributes'][$field])) {
                        foreach ($ProductData['Attributes'][$field] as $attrname => $attraval) {
                            $additionalField->setAttribute($attrname, $attraval);
                        }
                    }
                }
            }
        }

        return array($Product);
    }
    
    private function appendDescriptionData(DOMDocument $Document, DOMElement $Product, $DescriptionDataTag, $ProductDescription, $paramXsd)
    {
        $shippingTemplate = isset($ProductDescription['MerchantShippingGroupName'])
            ? $ProductDescription['MerchantShippingGroupName'] : '';
        $title = isset($ProductDescription['Title']) ? $ProductDescription['Title'] : '';
        if (is_array($paramXsd) && count($paramXsd)) {
            $descriptionDataNode = $this->sortProductData($Document, $DescriptionDataTag, $paramXsd);
            $Product->appendChild($descriptionDataNode);
        } elseif ($shippingTemplate && $title) {
            // If for some reasons, the XSD is empty (no profile, update offer only), and the shipping template is set,
            // we need to set it in the feed also
            // The title is mandatory, set it along
            $titleTag = $Document->createElement('Title', $title);
            $stTag = $Document->createElement('MerchantShippingGroupName', $shippingTemplate);
            $DescriptionDataOnlyShippingTemplate = $Document->createElement('DescriptionData');
            $DescriptionDataOnlyShippingTemplate->appendChild($titleTag);
            $DescriptionDataOnlyShippingTemplate->appendChild($stTag);
            $Product->appendChild($DescriptionDataOnlyShippingTemplate);
        }
    }

    /**
     * @param DOMDocument $Document
     * @param array $arrayOfData Array containing "Elements" to be added and their corresponding path in "Parameters"
     * @param $referenceToClean
     * @param string $parentTagName the name of the tag where elements will be appended, for example:"DescriptionData"
     * @return boolean
     * @throws DOMException
     */
    private function createDescriptionDataNode(DOMDocument $Document, &$arrayOfData, &$referenceToClean, $parentTagName = 'DescriptionData')
    {
        $details = 0;
        $param = 'Parameters';
        $attributes = 'Attributes';
        $excluded = array('VariationData', 'ClassificationData', 'ProductType', 'ProductSubtype', 'ClothingType', 'ShoeSizeComplianceData', 'AgeRangeDescription');
        $excludedElements = array('ShirtSizeClass');

        if (is_array($arrayOfData)) {
            $parentElement = $Document->createElement($parentTagName);

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }

            $productTypeArr = array();

            if(isset($arrayOfData[$param]['ProductType']) && count($arrayOfData[$param]['ProductType'])) {
                $productTypeArr = $arrayOfData[$param]['ProductType'];
            }

            $validateExclusion = false;
            $exclusionRef = '';
            foreach($productTypeArr as $field){
                if(isset(AmazonSPXSD::$excludedFromDescriptionData[$field])){
                    $validateExclusion = true;
                    $exclusionRef = AmazonSPXSD::$excludedFromDescriptionData[$field];
                }
            }

            foreach ($arrayOfData as $key => $value) {
                // Exclude ShirtSizeClass tag for Clothing universe
                if (in_array($key, $excludedElements)) {
                    continue;
                }

                if ($key == $param || $key == $attributes) {
                    continue;
                }

                if (!isset($arrayOfData[$param][$key])) {
                    continue;
                }
                // Ensure this is a description data item
                $first_item = isset($arrayOfData[$param][$key][0]) ? $arrayOfData[$param][$key][0] : array();
                $second_item = isset($arrayOfData[$param][$key][1]) ? $arrayOfData[$param][$key][1] : array();
                $third_item = isset($arrayOfData[$param][$key][2]) ? $arrayOfData[$param][$key][2] : array();

                //exclude where fiels is included in productData, and should not be included in DescriptionData
                if($validateExclusion && isset($exclusionRef[$first_item])){
                    continue;
                }

                // Exclude Universe
                if (in_array($second_item, $excluded) && !in_array($first_item, $excluded)) {
                    $excluded[] = $first_item;
                }

                // Exclude non description data tags
                if (is_array($arrayOfData[$param][$key]) && count($arrayOfData[$param][$key]) > 2
                    || in_array($first_item, $excluded) || in_array($second_item, $excluded) || in_array($third_item, $excluded)) {
                    continue;
                }

                if (is_array($arrayOfData[$param][$key])) {
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);
                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;

                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);

                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }


                                foreach ($result as $r) {
                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                $parentElement->appendChild($node);
                            }
                        }
                    }


                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        if (is_array($value)) {
                            foreach ($value as $detail) {
                                foreach ($lastChild as $l) {
                                    if ($l->nodeValue == null) {
                                        //
                                        $l->nodeValue = $detail;
                                    } else {
                                        $el = $Document->createElement(trim($tagName));
                                        $el->nodeValue = $detail;
                                        $l->parentNode->appendChild($el);
                                    }
                                    break;
                                }
                            }
                        } else {
                            foreach ($lastChild as $l) {
                                $l->appendChild($Document->createTextNode(trim($value)));
                                break;
                            }
                        }
                        $details++;
                    }
                    unset($arrayOfData[$tagName]);
                    unset($arrayOfData[$param][$key]);
                    if(isset($referenceToClean[$tagName])){
                        unset($referenceToClean[$tagName]);
                    }

                }
            }

            if ($details > 0) {
                return $parentElement;
            }
        }

        return false;
    }

    /**
     * @param DOMElement $node
     * @param array $productData
     */
    private function setElementAttributes(DOMElement $node, array $productData)
    {
        if (isset($productData['Attributes'][$node->nodeName])) {
            foreach ($productData['Attributes'][$node->nodeName] as $attr => $value) {
                $node->setAttribute($attr, $value);
            }
        }
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement $element
     * @param array $xsdStructure
     * @param string $xpath
     * @return DOMElement|DOMNode
     */
    public function sortProductData(DOMDocument $document, DOMElement $element, array $xsdStructure, $xpath = '/')
    {
        $d = new DOMDocument();
        $newElement = $d->importNode($element->cloneNode(true), true);
        $d->appendChild($newElement);

        $node = $this->setSortedElements($d, $newElement, $xsdStructure, $xpath);

        if ($node) {
            return $document->importNode($node, true);
        } else {
            return $element;
        }
    }

    /**
     * @param DOMDocument $d
     * @param DOMElement $element
     * @param array $xsdStructure
     * @param null $xPath
     * @return bool|DOMElement
     */
    public function setSortedElements(DOMDocument $d, DOMElement $element, array $xsdStructure, $xPath = null)
    {
        $children = array();

        $x = new DOMXPath($d);
        $query = $element->getNodePath().'/*';
        $result = $x->query($query);
        //As root element is ProductData, we look for its children like "Computers" or "Shoes"
        foreach ($result as $r) {
            if (isset($children[$r->nodeName])) {
                if (!is_array($children[$r->nodeName])) {
                    $children[$r->nodeName] = array($children[$r->nodeName]);
                }
                $children[$r->nodeName][] = $r->parentNode->removeChild($r);
            } else {
                $children[$r->nodeName] = $r->parentNode->removeChild($r);
            }
        }

        //append child according to structure order
        foreach ($xsdStructure as $key => $value) {
            if (isset($children[$key])) {
                if (is_array($children[$key])) {
                    foreach ($children[$key] as $e) {
                        $element->appendChild($e);
                        if (is_array($value) && count($value) > 0) {
                            // $children[$key] replaced by e 2018/09/30
                            $this->setSortedElements($d, $e, $xsdStructure[$key]);
                        }
                    }
                } else {
                    $element->appendChild($children[$key]);
                    if (is_array($value) && count($value) > 0) {
                        $returnedElement = $this->setSortedElements($d, $children[$key], $xsdStructure[$key]);
                        if($returnedElement != null){
                            $children[$key] = $returnedElement;
                        }else{
                            $toRemove = $children[$key];
                            $toRemove->parentNode->removeChild($toRemove);
                        }
                        
                    }
                }
            }
        }

        //Verify VariationData includes at least 1 child element or remove it
        if($element->nodeName == "VariationData"){
            $result = $x->query($query);
            if(!count($result)){
                return null;
            }            
        }

        return $element;
    }

    /**
     * @param DOMDocument $Document
     * @param $arrayOfData
     * @param string $parentTagName
     *
     * @return bool|DOMElement
     * @throws DOMException
     */
    private function clothingToXmlNode(DOMDocument $Document, $arrayOfData, $parentTagName = 'ProductData')
    {
        $details = 0;
        $param = 'Parameters';
        if (is_array($arrayOfData)) {
            $root = $Document->createElement('ProductData');
            $clothing = $root->appendChild($Document->createElement('Clothing'));

            if (isset($arrayOfData['Parentage'])) {
                $clothing->appendChild($Document->createElement('VariationData'));
            }

            $element = $clothing->appendChild($Document->createElement($parentTagName));
            $parentElement = $root;

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }

            foreach ($arrayOfData as $key => $value) {
                if ($key == $param) {
                    continue;
                }

                if (is_array($arrayOfData[$key])) {
                    $this->convertArrayToXmlNode($Document, $arrayOfData[$key], $element);
                } elseif (!isset($arrayOfData[$param][$key])) {
                    continue;
                } elseif (is_array($arrayOfData[$param][$key])) {
                    $xPrevQuery = '';
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);

                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;
                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);
                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }

                                foreach ($result as $r) {
                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                try {
                                    $parentElement->insertBefore($node, $element);
                                } catch (Exception $e) {
                                    $parentElement->appendChild($node);
                                }
                            }
                        }
                    }

                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        foreach ($lastChild as $l) {
                            if ($tagName != 'ProductType') {
                                $l->appendChild($Document->createTextNode($value));
                            } else {
                                $l->appendChild($Document->createElement(trim($value)));
                            }
                            break;
                        }
                        $details++;
                    }
                }
            }

            return $root;
        }

        return false;
    }

    /**
     * @param DOMDocument $Document
     * @param $array
     * @param DOMElement $root
     *
     * @return string
     * @throws DOMException
     */
    private function convertArrayToXmlNode(DOMDocument $Document, $array, DOMElement $root)
    {
        $xPrevQuery = '';
        $xQuery = '';
        $first = true;
        $x = new DOMXPath($Document);
        $newElement = false;
        $path = '';

        foreach ($array as $tagName => $tagChild) {
            $xPrevQuery = $xQuery;
            if ($first) {
                $first = false;
                $xQuery = $tagName; //assign
            } else {
                $xQuery .= '/'.$tagName;
            } //concatenate

            $exists = $x->query($xQuery, $root)->length;

            if ($exists == 0) {
                if ($xPrevQuery != '') {
                    $result = $x->query($xPrevQuery, $root);
                    /**
                     * Before we check if it has text as child, and removes it
                     */
                    foreach ($result as $r) {
                        if ($r->hasChildNodes()) {
                            foreach ($r->childNodes as $child) {
                                if ($child->nodeName == '#text') {
                                    $child->parentNode->removeChild($child);
                                }
                            }
                        }
                    }

                    foreach ($result as $r) {
                        $newElement = $Document->createElement($tagName);
                        $this->setElementAttributes($newElement, $array);
                        $r->appendChild($newElement);
                        break; //only one time
                    }
                } else {
                    $newElement = $Document->createElement($tagName);
                    $this->setElementAttributes($newElement, $array);
                    $root->appendChild($newElement);
                }

                if (is_array($tagChild) && $newElement) {
                    $this->convertArrayToXmlNode($Document, $array[$tagChild], $newElement);
                } elseif ($newElement) {
                    $lastChild = $x->query($xQuery, $root);
                    if ($lastChild) {
                        foreach ($lastChild as $l) {
                            if ($tagName != 'ProductType') {
                                $node = $Document->createTextNode($tagChild);
                                $l->appendChild($node);
                            } else {
                                $node = $Document->createElement(trim($tagChild));
                                $this->setElementAttributes($node, $array);
                                $l->appendChild($node);
                            }
                            break;
                        }
                    }
                }
            }
        }

        return $path;
    }

    /**
     * @param DOMDocument $Document
     * @param array $arrayOfData Array containing "Elements" to be added and their corresponding path in "Parameters"
     * @param string $parentTagName the name of the tag where elements will be appended, for example:"ProductData"
     * @return boolean
     * @throws DOMException
     */
    private function convertToXmlNode(DOMDocument $Document, &$arrayOfData, $parentTagName = 'ProductData')
    {
        $details = 0;
        $param = 'Parameters';
        $attributes = 'Attributes';

        if (is_array($arrayOfData)) {
            $parentElement = $Document->createElement($parentTagName);

            /*
            * if [Parameters] is not set in the array or has zero (0) elements,
            * then no XML element is generated
            */
            if (!isset($arrayOfData[$param]) || !is_array($arrayOfData[$param]) || count($arrayOfData[$param]) == 0) {
                return false;
            }
            foreach ($arrayOfData as $key => $value) {
                if ($key == $param || $key == $attributes) {
                    continue;
                }

                if (!isset($arrayOfData[$param][$key])
                    || !is_array($arrayOfData[$param][$key])
                ) {
                    continue;
                }

                $isClassificationData = in_array('ClassificationData',$arrayOfData[$param][$key]);
                $isReallyRequired = isset($arrayOfData[$param]['ReallyRequiredFields']) && in_array($key,array_keys( $arrayOfData[$param]['ReallyRequiredFields'] ));
                //Exclude unneeded fields in Parent Product Data
                if(isset($arrayOfData['Parentage'])
                    && $arrayOfData['Parentage'] == 'parent'
                    && !in_array($key,
                        array('Parentage', 'VariationTheme','ClothingType', 'ProductType')
                    )
                    && !$isClassificationData
                    && !$isReallyRequired
                ){
                    continue;
                }

                //Fix for this ticket: https://3.basecamp.com/3914949/buckets/6077817/todos/3851997427
                if($key == 'VariationTheme'
                    && isset($arrayOfData['Definition'])
                    && isset( AmazonSPXSD::$uppercaseVariationThemes[$arrayOfData['Definition']] )
                    && isset($arrayOfData['ProductType'])
                    && !in_array($arrayOfData['ProductType'], AmazonSPXSD::$uppercaseVariationThemes[$arrayOfData['Definition']])
                ){
                    $value = strtolower($value);
                }

                if (is_array($arrayOfData[$param][$key])) {
                    $xQuery = '';
                    $first = true;
                    $x = new DOMXPath($Document);
                    //Generates Nodes to be added
                    foreach ($arrayOfData[$param][$key] as $tagName) {
                        $xPrevQuery = $xQuery;
                        if ($first) {
                            $first = false;
                            $xQuery = $tagName; //assign
                        } else {
                            $xQuery .= '/'.$tagName;
                        } //concatenate

                        $exists = $x->query($xQuery, $parentElement)->length;

                        if ($exists == 0) {
                            if ($xPrevQuery != '') {
                                $result = $x->query($xPrevQuery, $parentElement);

                                /**
                                 * Before we check if it has text as child, and removes it
                                 */
                                foreach ($result as $r) {
                                    if ($r->hasChildNodes()) {
                                        foreach ($r->childNodes as $child) {
                                            if ($child->nodeName == '#text') {
                                                $child->parentNode->removeChild($child);
                                            }
                                        }
                                    }
                                }


                                foreach ($result as $r) {
                                    // Support case:
                                    // https://support.common-services.com/helpdesk/tickets/42626
                                    // We should not send ClassificationData for parents
                                    //This exclusion is done a few lines before of this function
                                    //if (isset($r->tagName) && $r->tagName == 'ClassificationData' &&
                                    //    isset($arrayOfData['Parentage']) && $arrayOfData['Parentage'] == 'parent'
                                    //    && (isset($arrayOfData['Parameters']['ClothingType']) && $arrayOfData['Parameters']['ClothingType'][0] != 'Shoes' && $tagName != 'ClothingType')) {
                                    //    continue;
                                    //}

                                    $node = $Document->createElement($tagName);
                                    $this->setElementAttributes($node, $arrayOfData);
                                    $r->appendChild($node);
                                    break; //only one time
                                }
                            } else {
                                $node = $Document->createElement($tagName);
                                $this->setElementAttributes($node, $arrayOfData);
                                $parentElement->appendChild($node);
                            }
                        }
                    }


                    $lastChild = $x->query($xQuery, $parentElement);

                    if ($lastChild) {
                        if (is_array($value)) {
                            foreach ($value as $detail) {
                                foreach ($lastChild as $l) {
                                    if ($l->nodeValue == null) {
                                        //
                                        $l->nodeValue = $detail;
                                    } else {
                                        $el = $Document->createElement(trim($tagName));
                                        $el->nodeValue = $detail;
                                        $l->parentNode->appendChild($el);
                                    }
                                    break;
                                }
                            }
                        } else {
                            foreach ($lastChild as $l) {
                                if ($tagName != 'ProductType') {
                                    //
                                    $l->appendChild($Document->createTextNode($value));
                                } else {
                                    // If is not a complex type

                                    if (in_array($arrayOfData['Definition'], array(
                                        'ToysBaby',
                                        'Luggage',
                                        'Sports',
                                        'Miscellaneous'
                                    ))) {
                                        $l->appendChild($Document->createTextNode(trim($value)));
                                    } else {
                                        $l->appendChild($Document->createElement(trim($value)));
                                    }
                                }
                                break;
                            }
                        }
                        $details++;
                    }
                }
                unset($arrayOfData[$key]);
            }

            if ($details > 0) {
                return $parentElement;
            }
        }

        return false;
    }
}
