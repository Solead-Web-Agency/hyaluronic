<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }

class AmazonFeedsSending
{
    protected $feedContent;
    protected $sendFeed;
    /** @var AmazonSellerPartnerResponse */
    protected $sentResponse;
    protected $submissionFeedId;

    protected $additionalData = array();

    protected function __construct($feedContent, $sendFeed, $sentResponse, $submissionFeedId, $additionalData = array())
    {
        $this->feedContent = $feedContent;
        $this->sendFeed = (bool)$sendFeed;
        $this->sentResponse = $sentResponse;
        $this->submissionFeedId = $submissionFeedId;
        $this->additionalData = $additionalData;
    }

    public function getFeedContent()
    {
        return $this->feedContent;
    }

    public function isSendFeed()
    {
        return $this->sendFeed;
    }

    public function getSentResponse()
    {
        return $this->sentResponse;
    }

    public function getSubmissionFeedId()
    {
        return $this->submissionFeedId;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $orders
     * @param $marketplace
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @param $dev_mode
     * @param $batchStartTime
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedOrderAcknowledge(
        $spConnector,
        $orders,
        $marketplace,
        $batchesKey,
        $batchStartTime,
        $logger,
        $dev_mode
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_ORDER_ACKNOWLEDGEMENT
        );
        foreach ($orders as $ackOrder) {
            $xmlMessage = new AmazonSPFeedMessageOrderAcknowledgement($ackOrder['amz_id'], $ackOrder['seller_id']);
            $xmlFeed->addMessage($xmlMessage);
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, true,
            $batchesKey, 'Acknowledge (Orders)', $batchStartTime,
            0, count($orders), 0,
            $logger,
            array($marketplace),   // Only send feed to target marketplace of these orders
            $dev_mode
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $orders
     * @param $marketplace
     * @param $batchesKey
     * @param $batchStartTime
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedOrderCancel(
        $spConnector,
        $orders,
        $marketplace,
        $batchesKey,
        $batchStartTime,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_ORDER_ACKNOWLEDGEMENT
        );
        foreach ($orders as $order) {
            $xmlMessage = new AmazonSPFeedMessageOrderAcknowledgementCancel(
                $order['mp_order_id'], $order['merchant_order_id'], $order['items']
            );
            $xmlFeed->addMessage($xmlMessage);
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, true,
            $batchesKey, 'Cancel (Orders)', $batchStartTime,
            0, count($orders), 0,
            $logger,
            array($marketplace)
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param AmazonFulfillmentMessage[] $fulfillmentMessages
     * @param $marketplace
     * @param $batchesKey
     * @param $batchStartTime
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedOrderFulfillment(
        $spConnector,
        $fulfillmentMessages,
        $marketplace,
        $batchesKey,
        $batchStartTime,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_ORDER_FULFILLMENT
        );
        $psOrderIds = array();
        foreach ($fulfillmentMessages as $ffMsg) {
            $xmlMessage = new AmazonSPFeedMessageOrderFulfillment(
                $ffMsg->amzId, $ffMsg->getDate(),
                $ffMsg->isAmzCarrier(), $ffMsg->carrierCode, $ffMsg->carrierName,
                $ffMsg->shippingMethod, $ffMsg->shippingNumber
            );
            $xmlFeed->addMessage($xmlMessage);
            $psOrderIds[] = $ffMsg->psId;
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, true,
            $batchesKey, 'Fulfillment', $batchStartTime,
            0, count($fulfillmentMessages), 0,
            $logger, array($marketplace), false,
            array('ps_order_ids' => $psOrderIds)
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $operationMode
     * @param array $productsUpdate
     * @param Params $params
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedProductData(
        $spConnector,
        $operationMode,
        $productsUpdate,
        $params,
        $batchesKey,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_DATA
        );
        switch ($operationMode) {
            case AmazonWebService::OPERATIONS_CREATE:
                $operationType = AmazonSPFeedMessage::OPERATION_TYPE_UPDATE;
                break;
            case AmazonWebService::OPERATIONS_UPDATE:
                $operationType = AmazonSPFeedMessage::OPERATION_TYPE_PARTIAL_UPDATE;
                break;
            case AmazonWebService::OPERATIONS_DELETE:
                $operationType = AmazonSPFeedMessage::OPERATION_TYPE_DELETE;
                break;
            default:
                return new AmazonFeedsSending('', false, null, 0);
        }

        foreach ($productsUpdate as $productUpdate) {
            if (!isset($productUpdate['NoProductFeed']) || !$productUpdate['NoProductFeed']) {
                $xmlMessage = new AmazonSPFeedMessageProductData(
                    $operationType,
                    $productUpdate['SKU'],
                    isset($productUpdate['ProductIDType']) ? $productUpdate['ProductIDType'] : '',
                    isset($productUpdate['ProductIDCode']) ? $productUpdate['ProductIDCode'] : '',
                    isset($productUpdate['ConditionType']) ? $productUpdate['ConditionType'] : '',
                    isset($productUpdate['ConditionNote']) ? $productUpdate['ConditionNote'] : '',
                    isset($productUpdate['ProductDescription']) ? $productUpdate['ProductDescription'] : '',
                    isset($productUpdate['ProductData']) ? $productUpdate['ProductData'] : ''
                );
                $xmlFeed->addMessage($xmlMessage);
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Products', $params->timestart,
            $params->create ? count($productsUpdate) : 0,
            !$params->create ? count($productsUpdate) : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $operationMode
     * @param array $productsUpdate
     * @param Params $params
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedRelationship(
        $spConnector,
        $operationMode,
        $productsUpdate,
        $params,
        $batchesKey,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_RELATIONSHIP_DATA
        );
        $operationType = $operationMode == AmazonWebService::OPERATIONS_DELETE
            ? AmazonSPFeedMessage::OPERATION_TYPE_DELETE : AmazonSPFeedMessage::OPERATION_TYPE_UPDATE;

        foreach ($productsUpdate as $productUpdate) {
            if (isset($productUpdate['parent'], $productUpdate['children'])) {
                $xmlMessage = new AmazonSPFeedMessageProductRelationship(
                    $operationType,
                    $productUpdate['parent'],
                    $productUpdate['children']
                );
                $xmlFeed->addMessage($xmlMessage);
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Relations', $params->timestart,
            $params->create ? count($productsUpdate) : 0,
            !$params->create ? count($productsUpdate) : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $operationMode
     * @param array $productsUpdate
     * @param Params $params
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedInventory(
        $spConnector,
        $operationMode,
        $productsUpdate,
        $params,
        $batchesKey,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_INVENTORY_DATA
        );
        $operationType = $operationMode == AmazonWebService::OPERATIONS_DELETE
            ? AmazonSPFeedMessage::OPERATION_TYPE_DELETE : AmazonSPFeedMessage::OPERATION_TYPE_UPDATE;

        foreach ($productsUpdate as $productUpdate) {
            if (isset($productUpdate['Quantity']) && !(isset($productUpdate['NoQtyExport']) && $productUpdate['NoQtyExport'])) {
                $restockDate = isset($productUpdate['RestockDate']) ? $productUpdate['RestockDate'] : '';
                $fulfillmentLatency = isset($productUpdate['FulfillmentLatency']) ? $productUpdate['FulfillmentLatency'] : '';
                if (isset($productUpdate['FBA'])) {
                    $xmlMessage = new AmazonSPFeedMessageInventoryAvailabilityDataFBA(
                        $operationType,
                        $productUpdate['SKU'],
                        $restockDate,
                        $fulfillmentLatency,
                        $productUpdate['FBA']
                    );
                } else {
                    $xmlMessage = new AmazonSPFeedMessageInventoryAvailabilityDataNonFBA(
                        $operationType,
                        $productUpdate['SKU'],
                        $restockDate,
                        $fulfillmentLatency,
                        $productUpdate['Quantity']
                    );
                }
                $xmlFeed->addMessage($xmlMessage);
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Inventory', $params->timestart,
            $params->create ? count($productsUpdate) : 0,
            !$params->create ? count($productsUpdate) : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param array $productsUpdate
     * @param Params $params
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedPrice($spConnector, $productsUpdate, $params, $batchesKey, $logger)
    {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_PRICING_DATA
        );

        foreach ($productsUpdate as $productUpdate) {
            if (isset($productUpdate['Price']) && !(isset($productUpdate['NoPriceExport']) && $productUpdate['NoPriceExport'])) {
                $xmlMessage = new AmazonSPFeedMessageProductPricingData(
                    '',
                    $productUpdate['SKU'],
                    $spConnector->getCurrency()->iso_code,
                    $productUpdate['Price'],
                    $productUpdate['Business'],
                    $productUpdate['Sales']
                );
                $xmlFeed->addMessage($xmlMessage);
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Prices', $params->timestart,
            $params->create ? count($productsUpdate) : 0,
            !$params->create ? count($productsUpdate) : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $operationMode
     * @param array $productsUpdate
     * @param Params $params
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedOverride(
        $spConnector,
        $operationMode,
        $productsUpdate,
        $params,
        $batchesKey,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_OVERRIDES_DATA
        );

        foreach ($productsUpdate as $productUpdate) {
            if (isset($productUpdate['ShippingPrice'], $productUpdate['ShippingOption'])) {
                $shippingPrice = $productUpdate['ShippingPrice'];
                // todo: Poor implement of legacy code
                if ($shippingPrice === '' || $operationMode == AmazonWebService::OPERATIONS_DELETE) {
                    $operationType = AmazonSPFeedMessage::OPERATION_TYPE_DELETE;
                } else {
                    $operationType = AmazonSPFeedMessage::OPERATION_TYPE_UPDATE;
                }
                $xmlMessage = new AmazonSPFeedMessageProductOverridesData(
                    $operationType,
                    $productUpdate['SKU'],
                    $productUpdate['ShippingOption'],
                    $productUpdate['ShippingPrice'],
                    $spConnector->getCurrency()->iso_code,
                    $productUpdate['ShippingType']
                );
                $xmlFeed->addMessage($xmlMessage);
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Overrides', $params->timestart,
            $params->create ? count($productsUpdate) : 0,
            !$params->create ? count($productsUpdate) : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $operationMode
     * @param array $productsUpdate
     * @param Params $params
     * @param $images_count
     * @param $batchesKey
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedImage(
        $spConnector,
        $operationMode,
        $productsUpdate,
        $params,
        $images_count,
        $batchesKey,
        $logger
    ) {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_IMAGE_DATA
        );

        $useAlternateImage = (bool)AmazonConfiguration::get(AmazonConstant::CONFIG_ALTERNATE_PRODUCT_IMAGE);
        $alternateImageSrc = AmazonConfiguration::get(AmazonConstant::CONFIG_ALTERNATE_PRODUCT_IMAGE_SRC);

        // Each image of each product produces a message
        foreach ($productsUpdate as $productUpdate) {
            $imgIndex = 0;
            if(isset($productUpdate['ProductData']) && isset($productUpdate['ProductData']['ProductImage'])) {
                // Set "main" image if have alternate image setting
                if ($useAlternateImage && !empty($alternateImageSrc)) {
                    $xmlMessage = new AmazonSPFeedMessageProductImageData(
                        $operationMode,
                        $productUpdate['SKU'],
                        $alternateImageSrc,
                        'Main'
                    );
                    $xmlFeed->addMessage($xmlMessage);
                }
                foreach ($productUpdate['ProductData']['ProductImage'] as $imageUrl) {
                    $imgType = $useAlternateImage ? "PT$imgIndex" : ($imgIndex === 0 ? 'Main' : "PT$imgIndex");
                    $imgIndex++;
                    $xmlMessage = new AmazonSPFeedMessageProductImageData(
                        $operationMode,
                        $productUpdate['SKU'],
                        $imageUrl,
                        $imgType
                    );
                    $xmlFeed->addMessage($xmlMessage);
                }
            }
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            $batchesKey, 'Images', $params->timestart,
            $params->create ? $images_count : 0,
            !$params->create ? $images_count : 0,
            0,
            $logger
        );
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param array $productsDelete
     * @param Params $params
     * @param AmazonLogger $logger
     * @return AmazonFeedsSending
     * @throws Exception
     */
    public static function submitFeedDeletion($spConnector, $productsDelete, $params, $logger)
    {
        $xmlFeed = new AmazonSPFeedXMLGenerator(
            $spConnector->getSellerId(),
            AmazonSPFeedXMLGenerator::TYPE_PRODUCT_DATA
        );
        foreach ($productsDelete as $productDelete) {
            $xmlMessage = new AmazonSPFeedMessageProductDataGeneral(
                AmazonSPFeedMessage::OPERATION_TYPE_DELETE,
                $productDelete['SKU']
            );
            $xmlFeed->addMessage($xmlMessage);
        }

        return self::sendFeedFromGenerator(
            $spConnector, $xmlFeed, !$params->xmlOnly,
            AmazonBatches::TYPE_CATALOG_DELETION, 'Deletion', $params->timestart,
            0, 0, count($productsDelete), $logger
        );
    }

    public static function submitFeedVatInvoice($spConnector, $feedContent, $feedSource, $feedOptions, $sendFeed, $logger, $batchStart)
    {
        $feedSubmission = self::sendFeed(
            $spConnector, $feedContent, $feedSource,
            AmazonSPAPIFeeds::FEED_TYPE_UPLOAD_VAT_INVOICE, $feedOptions,
            $sendFeed, $logger
        );
        if (!$feedSubmission->getSentResponse()->hasError()) {
            self::saveBatch(
                $spConnector, $feedSubmission->getSubmissionFeedId(),
                AmazonBatches::TYPE_VAT_INVOICE, AmazonBatch::TYPE_VAT_INVOICE_UPLOAD,
                $batchStart, 1, 0, 0
            );
        }

        return $feedSubmission;
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param AmazonSPFeedXMLGenerator $xmlFeed
     * @param bool $sendFeed
     * @param $batchesKey
     * @param $batchType
     * @param $batchStart
     * @param int $createdCount
     * @param int $updatedCount
     * @param int $deletedCount
     * @param AmazonLogger $logger
     * @param array $marketplaces
     * @param bool $devMode
     * @param array $additionalData
     * @return AmazonFeedsSending
     */
    protected static function sendFeedFromGenerator(
        $spConnector,
        $xmlFeed,
        $sendFeed,
        $batchesKey,
        $batchType,
        $batchStart,
        $createdCount,
        $updatedCount,
        $deletedCount,
        $logger,
        $marketplaces = array(),
        $devMode = false,
        $additionalData = array()
    ) {
        $feedContent = $xmlFeed->generateFeed();
        if ($xmlFeed->countMessages() < 1) {
            return new AmazonFeedsSending($feedContent, false, null, 0, $additionalData);
        }

        $feedSubmission = self::sendFeed(
            $spConnector, $feedContent, '', $xmlFeed->getMessageType(), null, $sendFeed,
            $logger, $marketplaces, $devMode, $additionalData
        );
        if ($feedSubmission->getSentResponse() && !$feedSubmission->getSentResponse()->hasError()) {
            self::saveBatch(
                $spConnector, $feedSubmission->getSubmissionFeedId(), $batchesKey, $batchType, $batchStart,
                $createdCount, $updatedCount, $deletedCount
            );
        }

        return $feedSubmission;
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $feedContent
     * @param $feedType
     * @param $feedOptions
     * @param $sendFeed
     * @param $logger
     * @param array $marketplaces
     * @param bool $devMode
     * @param array $additionalData
     * @return AmazonFeedsSending
     */
    protected static function sendFeed(
        $spConnector,
        $feedContent,
        $feedSource,
        $feedType,
        $feedOptions,
        $sendFeed,
        $logger,
        $marketplaces = array(),
        $devMode = false,
        $additionalData = array()
    ) {
        if (!$sendFeed) {
            return new AmazonFeedsSending($feedContent, false, null, 0, $additionalData);
        }

        $spApiFeedCreation = new AmazonSPAPIFeedsCreation(
            $spConnector, count($marketplaces) ? $marketplaces : $spConnector->getMarketplaces(),
            $feedType, $feedContent, $feedOptions, $logger, $devMode
        );
        $feedCreationResponse = $spApiFeedCreation->setFeedSource($feedSource)->apiSendFeed();
        if ($feedCreationResponse->hasError()) {
            return new AmazonFeedsSending($feedContent, true, $feedCreationResponse, 0, $additionalData);
        }

        /** @var AmazonSPDefCreateFeedResponse $feedCreationResponsePayload */
        $feedCreationResponsePayload = $feedCreationResponse->getStructuredPayload();
        $submissionFeedId = $feedCreationResponsePayload->feedId;

        return new AmazonFeedsSending($feedContent, true, $feedCreationResponse, $submissionFeedId, $additionalData);
    }

    /**
     * @param AmazonSPConnectorPSMkp|AmazonSPConnectorPSRegion $spConnector
     * @param $submissionFeedId
     * @param $batchesKey
     * @param $batchType
     * @param $batchStart
     * @param $createdCount
     * @param $updatedCount
     * @param $deletedCount
     * @return void
     */
    private static function saveBatch(
        $spConnector,
        $submissionFeedId,
        $batchesKey,
        $batchType,
        $batchStart,
        $createdCount,
        $updatedCount,
        $deletedCount
    ) {
        if ($submissionFeedId) {
            $batches = new AmazonBatches($batchesKey);
            $batch = AmazonBatch::initInstance(
                $submissionFeedId, $batchStart, time(), $batchType,
                method_exists($spConnector, 'getIso') ? $spConnector->getIso() : $spConnector->getRegion(),
                $createdCount, $updatedCount, $deletedCount
            );
            $batches->add($batch);
            $batches->save();
        }
    }
}
