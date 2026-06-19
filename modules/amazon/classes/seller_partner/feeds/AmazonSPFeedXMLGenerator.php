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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/amzn-envelope.xsd
 */
class AmazonSPFeedXMLGenerator implements IAmazonFeedContent
{
    const TYPE_ORDER_ACKNOWLEDGEMENT = AmazonSPAPIFeeds::FEED_TYPE_ORDER_ACKNOWLEDGE;
    const TYPE_ORDER_FULFILLMENT = AmazonSPAPIFeeds::FEED_TYPE_ORDER_FULFILLMENT;
    const TYPE_PRODUCT_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_DATA;
    const TYPE_PRODUCT_IMAGE_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_IMAGE_DATA;
    const TYPE_PRODUCT_PRICING_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_PRICING_DATA;
    const TYPE_PRODUCT_OVERRIDES_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_OVERRIDES_DATA;
    const TYPE_PRODUCT_INVENTORY_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_INVENTORY_DATA;
    const TYPE_PRODUCT_RELATIONSHIP_DATA = AmazonSPAPIFeeds::FEED_TYPE_PRODUCT_RELATIONSHIP_DATA;

    private $merchantId;
    private $messageType;
    private $purgeAndReplace;
    /** @var IAmazonFeedMessageContent[] */
    private $messages = array();

    private static $typeMapping = array(
        self::TYPE_ORDER_ACKNOWLEDGEMENT => array(
            'amz_type' => 'OrderAcknowledgement',
            'cs_type' => AmazonSPFeedMessageOrderAcknowledgement::class,
        ),
        self::TYPE_ORDER_FULFILLMENT => array(
            'amz_type' => 'OrderFulfillment',
            'cs_type' => AmazonSPFeedMessageOrderFulfillment::class,
        ),
        self::TYPE_PRODUCT_DATA => array(
            'amz_type' => 'Product',
            'cs_type' => AmazonSPFeedMessageProductDataGeneral::class,
        ),
        self::TYPE_PRODUCT_IMAGE_DATA => array(
            'amz_type' => 'ProductImage',
            'cs_type' => AmazonSPFeedMessageProductImageData::class,
        ),
        self::TYPE_PRODUCT_PRICING_DATA => array(
            'amz_type' => 'Price',
            'cs_type' => AmazonSPFeedMessageProductPricingData::class,
        ),
        self::TYPE_PRODUCT_OVERRIDES_DATA => array(
            'amz_type' => 'Override',
            'cs_type' => AmazonSPFeedMessageProductOverridesData::class,
        ),
        self::TYPE_PRODUCT_INVENTORY_DATA => array(
            'amz_type' => 'Inventory',
            'cs_type' => AmazonSPFeedMessageInventoryAvailabilityData::class,
        ),
        self::TYPE_PRODUCT_RELATIONSHIP_DATA => array(
            'amz_type' => 'Relationship',
            'cs_type' => AmazonSPFeedMessageProductRelationship::class,
        ),
    );

    public function __construct($merchantId, $messageType, $purgeAndReplace = false)
    {
        $this->merchantId = $merchantId;
        if (!isset(self::$typeMapping[$messageType])) {
            throw new Exception('Invalid message type');
        }
        $this->messageType = $messageType;
        $this->purgeAndReplace = $purgeAndReplace;
    }

    // Proxy use XML by default. This is for later extension
    public function contentType($contentType)
    {
        return 'text/xml; charset=UTF-8';
    }

    public function getMessageType()
    {
        return $this->messageType;
    }

    protected function getCsType()
    {
        return self::$typeMapping[$this->messageType]['cs_type'];
    }

    public function addMessage($message)
    {
        if ($message instanceof AmazonSPFeedMessage && $message->isValidMessage()) {
            $this->messages[] = $message;
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function countMessages()
    {
        return count($this->messages);
    }

    public function generateFeed()
    {
        $domDoc = new DOMDocument();
        // Root
        $FeedXmlRootElement = $domDoc->createElement('AmazonEnvelope');
        $FeedXmlRootElement->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $FeedXmlRootElement->setAttribute('xsi:noNamespaceSchemaLocation', 'amzn-envelope.xsd');
        $domDoc->appendChild($FeedXmlRootElement);

        // Header
        $header = $domDoc->createElement('Header');
        $DocumentVersion = $domDoc->createElement('DocumentVersion', '1.01');
        $MerchantIdentifier = $domDoc->createElement('MerchantIdentifier', $this->merchantId);
        // Don't try to chain appendChild(), it does not work
        $header->appendChild($DocumentVersion);
        $header->appendChild($MerchantIdentifier);
        $FeedXmlRootElement->appendChild($header);

        // Message type
        $MessageTypeX = $domDoc->createElement('MessageType', self::$typeMapping[$this->messageType]['amz_type']);
        $FeedXmlRootElement->appendChild($MessageTypeX);

        // Optional tag
        if ($this->purgeAndReplace) {
            $PurgeAndReplace = $domDoc->createElement('PurgeAndReplace', 'true');
            $FeedXmlRootElement->appendChild($PurgeAndReplace);
        }

        // Main content, array of Message
        for ($m = 0; $m < count($this->messages); $m++) {
            $messageId = $domDoc->createElement('MessageID', $m + 1);
            $messageContents = $this->messages[$m]->generateMessage($domDoc);

            $message = $domDoc->createElement('Message');
            $message->appendChild($messageId);
            if (is_array($messageContents)) {
                foreach ($messageContents as $messageContent) {
                    if ($messageContent instanceof DOMElement) {
                        $message->appendChild($messageContent);
                    }
                }
            }
            $FeedXmlRootElement->appendChild($message);
        }

        return $domDoc->saveXML();
    }
}
