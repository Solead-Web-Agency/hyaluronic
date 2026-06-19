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
/* Is ajax/cron file */
require_once(dirname(__FILE__) . '/../AmazonFunctionWithFeeds.php');
if (!defined('_PS_VERSION_')) { exit; }

abstract class AmazonFunctionFeeds extends AmazonFunctionWithFeeds
{
    protected $feedId;
    protected $isXml;

    public function __construct($feedId, $isXml = true)
    {
        parent::__construct();

        $this->feedId = $feedId;
        $this->isXml = $isXml;
        $this->initSpConnector();
    }

    /**
     * @param $feedId
     * @return SimpleXMLElement|string
     */
    protected function getFeedResultById($feedId)
    {
        $logger = $this->logger;
        $spFeedDoc = new AmazonSPAPIFeedsGetById($this->spConnector, $feedId, $logger);
        $apiResult = $spFeedDoc->apiGet();
        if ($apiResult->hasError()) {
            $upstreamError = $apiResult->getUpstream()->getError();
            throw new AmazonAPIFeedsException(
                "Failed to get feed result. {$apiResult->getErrorMsg()}! Upstream: $upstreamError",
                0, $logger
            );
        }

        $feedResponse = $apiResult->getStructuredPayload();
        if ($feedResponse instanceof AmazonSPDefFeed) {
            $exceptionCode = $feedResponse->isProcessing() ? AmazonAPIFeedsException::FEED_PROCESSING
                : ($feedResponse->isError() ? AmazonAPIFeedsException::FEED_FAILED : 0);
            throw new AmazonAPIFeedsException(
                "Feed is not ready! Feed status: $feedResponse->processingStatus",
                $exceptionCode, $logger
            );
        }

        if ($feedResponse instanceof AmazonSPDefFeedDocument) {
            return $this->decodeFeedDocumentResult($feedResponse);
        }

        throw new AmazonAPIFeedsException("Unknown error!", 0, $logger);
    }

    /**
     * Get the resultDocumentId if feed is processed
     * @param $feedId
     * @return AmazonSPDefFeed
     * @throws AmazonAPIFeedsException
     */
    private function getFeed($feedId)
    {
        $logger = $this->logger;
        $spFeedsGet = new AmazonSPAPIFeedsGet($this->spConnector, $feedId, $logger);
        $apiResult = $spFeedsGet->apiGet();
        if ($apiResult->hasError()) {
            throw new AmazonAPIFeedsException(
                "Failed to get feed submission result. {$apiResult->getErrorMsg()}!",
                0, $logger
            );
        }

        /** @var AmazonSPDefFeed $feed */
        $feed = $apiResult->getStructuredPayload();
        if ($feed->isError()) {
            throw new AmazonAPIFeedsException(
                "Report ends unexpected with status $feed->processingStatus!",
                AmazonAPIFeedsException::FEED_FAILED,
                $logger
            );
        }
        if ($feed->isProcessing()) {
            throw new AmazonAPIFeedsException(
                'Report is not available yet, please wait few minutes',
                AmazonAPIFeedsException::FEED_PROCESSING,
                $logger
            );
        }

        return $feed;
    }

    /**
     * Get the feed document
     * @param $feedDocumentId
     * @return AmazonSPDefFeedDocument
     * @throws AmazonAPIFeedsException
     */
    private function getFeedDocument($feedDocumentId)
    {
        $logger = $this->logger;
        $spFeedDoc = new AmazonSPAPIFeedsGetDocument($this->spConnector, $feedDocumentId, $logger);
        $apiResult = $spFeedDoc->apiGet();
        if ($apiResult->hasError()) {
            throw new AmazonAPIFeedsException(
                "Failed to get feed document. {$apiResult->getErrorMsg()}!",
                0, $logger
            );
        }

        /** @var AmazonSPDefFeedDocument $feedDocument */
        $feedDocument = $apiResult->getStructuredPayload();

        return $feedDocument;
    }

    /**
     * @param AmazonSPDefFeedDocument $feedDocument
     * @return SimpleXMLElement|string
     */
    private function decodeFeedDocumentResult($feedDocument)
    {
        $resourceUrl = $feedDocument->getResourceUrl();
        if (!$resourceUrl) {
            throw new Exception('Resource URL is empty');
        }
        $rawContents = file_get_contents($feedDocument->getResourceUrl());
        $feedContent = $feedDocument->gzipCompression() ? gzdecode($rawContents) : $rawContents;

        if ($this->isXml) {
            return new SimpleXMLElement($feedContent);
        }

        // Return raw content on text result (VAT invoice upload)
        return $feedContent;
    }
}
