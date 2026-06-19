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
abstract class AmazonSellerPartnerAPI
{
    const SANDBOX = false;
    const API_TYPE_SELLERS = 'sellers';
    const API_TYPE_ORDERS_GET_LIST = 'getOrders';
    const API_TYPE_ORDERS_GET_ORDER_ITEMS = 'getOrderItems';
    const API_TYPE_FEEDS_GET = 'getFeed';
    const API_TYPE_FEEDS_GET_BULK = 'getFeeds';
    const API_TYPE_FEEDS_GET_DOCUMENT = 'getFeedDocument';
    const API_TYPE_FEEDS_GET_BY_ID = 'getFeedById';
    const API_TYPE_FEEDS_SUBMIT = 'submitFeed';
    const API_TYPE_REPORTS_CREATE = 'createReport';
    const API_TYPE_REPORTS_GET = 'getReport';
    const API_TYPE_REPORTS_DOCUMENT_GET = 'getReportDocument';
    const API_TYPE_PRIME_GET_ELIGIBLE_SHIPMENT_SERVICES = 'getEligibleShipmentServices';
    const API_TYPE_PRIME_CREATE_SHIPMENT = 'createShipment';
    const API_TYPE_PRIME_GET_SHIPMENT = 'getShipment';
    const API_TYPE_CATALOG_SEARCH = 'searchCatalogItems';
    // FBA
    const API_TYPE_FBA_GET_BULK_FULFILLMENT_ORDER = 'listAllFulfillmentOrders'; // Shopify
    const API_TYPE_FBA_LIST_ALL_FULFILLMENT_ORDERS = 'listAllFulfillmentOrders'; // Prestashop
    const API_TYPE_FBA_GET_FULFILLMENT_ORDER = 'getFulfillmentOrder';
    const API_TYPE_FBA_CREATE_FULFILLMENT_ORDER = 'createFulfillmentOrder';
    const API_TYPE_FBA_CANCEL_FULFILLMENT_ORDER = 'cancelFulfillmentOrder';
    const API_TYPE_FBA_GET_PACKAGE_TRACKING_DETAILS = 'getPackageTrackingDetails';
    const API_TYPE_FBA_INVENTORY_GET_SUMMARIES = 'getInventorySummaries';

    protected $apiType;
    protected $devMode = false;
    protected $isSandBox = false;

    protected $responseWrapper;

    /** @var IAmazonSellerPartnerLogger */
    protected $logger;

    /** @var IAmazonSellerPartnerConnector&IAmazonSellerPartnerLicense */
    protected $connector;

    /**
     * @param IAmazonSellerPartnerConnector&IAmazonSellerPartnerLicense $connector
     * @param $apiType
     * @param null $logger
     * @param bool $devMode
     * @param bool $isSandBox
     */
    public function __construct($connector, $apiType, $logger = null, $devMode = false, $isSandBox = false)
    {
        $this->connector = $connector;
        $this->apiType = $apiType;
        $this->logger = $logger;
        $this->devMode = $devMode;
        $this->isSandBox = $isSandBox;
    }

    /**
     * @param array $body
     * @return AmazonSellerPartnerResponse
     */
    protected function doRequest($body = array())
    {
        if (!$this->connector->getRefreshToken() || !$this->connector->resolveSpRegion() || !$this->apiType) {
            return AmazonSellerPartnerResponse::badRequest(400, $this->connector->getUnauthorizedMessage());
        }

        try {
            $this->handleQuotaBeforeDoRequest();
        } catch (Exception $e) {
            return AmazonSellerPartnerResponse::badRequest(429, $e->getMessage());
        }

        $rotationHandler = $this->connector->getEndpointRotation();
        if ($rotationHandler && $rotationHandler->isRotationEnable()) {
            $availableEndpoints = $rotationHandler->getAvailableEndpoints();
            if ($availableEndpoints) {
                $response = null;
                foreach ($availableEndpoints as $try => $availableEndpoint) {
                    $response = $this->_doRequest($availableEndpoint, $body);


                    // if empty response AND error code = 413
                    $responseMsg = $response->httpStatus == '413' ? 'the content sent to the server is too large, please reduce the number and try again.' : $response->response;
                    $response->response = $responseMsg;
                    
                    /**
                     * Do rotation when: empty response OR (server response AND server error).
                     * Below if is NOT of above condition
                     */
                    if (!$this->isEmptyResponse($response) && !($response->serverResponded() && $response->isServerError())) {
                        return $this->buildResponseFromDraft($response);
                    }
                    
                    // do rotation
                    $this->logDebug("SP API: Response: Failed on try: $try", array(
                        'status' => $response->httpStatus,
                        'response' => $responseMsg,
                    ));
                }

                $this->logDebug('SP API: Response: No more way to try!');
                return $response ? $this->buildResponseFromDraft($response) :
                    AmazonSellerPartnerResponse::badRequest(500, 'Wrong way!');
            }
        }

        return $this->buildResponseFromDraft($this->_doRequest($this->proxyEndpoint(), $body));
    }

    /**
     * @param AmazonSellerPartnerAPIDraftResponse $response
     * @return bool
     */
    private function isEmptyResponse(AmazonSellerPartnerAPIDraftResponse $response)
    {
        $ignoreAPIs = array(
            self::API_TYPE_FBA_CREATE_FULFILLMENT_ORDER,
            self::API_TYPE_FBA_CANCEL_FULFILLMENT_ORDER,
        );
        return $response->isEmptyResponse() && !in_array($this->apiType, $ignoreAPIs);
    }

    /**
     * @param AmazonSellerPartnerAPIDraftResponse $draftResp
     * @return AmazonSellerPartnerResponse
     */
    private function buildResponseFromDraft($draftResp)
    {
        if (!$draftResp->serverResponded()) {
            return AmazonSellerPartnerResponse::failedRespCurl($draftResp->errorNumber, $draftResp->error);
        }
        
        if ($draftResp->httpStatus == '413') {
            $response = AmazonSellerPartnerResponse::serverError($draftResp->response);
        } else {
            $response = AmazonSellerPartnerResponse::amzResp($draftResp->response, array($this, 'parsePayload'), $this->responseWrapper);
        }

        $this->handleQuotaAfterDoRequest($response);

        return $response;
    }

    private function _doRequest($endpoint, $body = array())
    {
        if (is_array($body)) {
            $body['requester'] = $this->buildLicenseRequestData();
        } elseif (is_object($body)) {
            $body->requester = $this->buildLicenseRequestData();
        }
        $curlOptions = array(
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_USERAGENT => 'Common-Services/Amazon Marketplace (Language=PHP/' . phpversion() . ')',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FAILONERROR => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            // todo: SSL checker
            // todo: Verbose mode
            CURLOPT_HTTPHEADER => array(
                'Cs-Platform: ' . $this->connector->getPlatform(),
                'Cs-Refresh-Token: ' . $this->connector->getRefreshToken(),
                'Cs-Region: ' . $this->connector->resolveSpRegion(),
                'Cs-Seller-Id: ' . $this->connector->getSellerId(),
                'Cs-Api-Type: ' . $this->apiType,
                'Cs-Api-Sandbox: ' . ($this->isSandBox ? 1 : 0),
                'Content-Type: application/json',
            ),
            CURLOPT_POSTFIELDS => json_encode($body),
        );
        $this->logDebug('SP API: Request', $curlOptions);

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, $curlOptions);

        $result = curl_exec($curlHandle);
        $curlError = curl_error($curlHandle);
        $curlErrorNo = curl_errno($curlHandle);
        $responseHttpStatus = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);
        $this->logDebug('SP API: Response', ['raw' => $result]);

        return new AmazonSellerPartnerAPIDraftResponse($result, $curlError, $curlErrorNo, $responseHttpStatus);
    }

    protected function handleQuotaBeforeDoRequest()
    {
        $quotaHandler = $this->connector->getQuotaHandler();
        if ($quotaHandler) {
            $quotaHandler->handleThrottleBeforeCallApi($this->apiType);
        }
    }

    protected function handleQuotaAfterDoRequest($response)
    {
        $quotaHandler = $this->connector->getQuotaHandler();
        if ($quotaHandler) {
            $quotaHandler->handleThrottleAfterCallApi($this->apiType, $response);
        }
    }

    /**
     * @param object|array $payload
     * @return mixed
     */
    abstract public function parsePayload($payload);

    /**
     * @param $requestBody
     * @param string|array $listProperty
     * @param int $pageLimit
     * @param $isDecodeNextToken
     * @return Generator
     */
    protected function getListAll($requestBody, $listProperty, $pageLimit = 20, $isDecodeNextToken = false)
    {
        $nextToken = '';
        $page = 1;

        while ($page === 1 || ($nextToken && $page <= $pageLimit)) {
            $bodyThisAttempt = $requestBody;
            if ($nextToken) {
                $bodyThisAttempt['next_token'] = $nextToken;
            }

            $this->logDebug('Get list request', $bodyThisAttempt);
            $attempt = $this->doRequest($bodyThisAttempt);
            if ($attempt->hasError()) {
                yield $attempt;
                $nextToken = '';
            } else {
                // Ignore other cases that are not string / array
                if (is_string($listProperty)) {
                    $listProperties = array($listProperty);
                } elseif (is_array($listProperty)) {
                    $listProperties = $listProperty;
                } else {
                    $listProperties = array();
                }
                // The list in structured payload
//                $this->logger->debug('The attempt', $attempt);
                $theList = $attempt->getStructuredPayload();
                $nextToken = $isDecodeNextToken ? urldecode($theList->getNextToken()) : $theList->getNextToken();
//                $this->logger->debug('The payload', $theList);
                foreach ($listProperties as $travelingProperty) {
                    if (isset($theList->$travelingProperty)) {
                        $theList = $theList->$travelingProperty;
//                        $this->logger->debug('The list', $theList);
                    }
                }

                if (is_array($theList) && count($theList)) {
                    foreach ($theList as $item) {
                        yield $item;
                    }
                }
            }

            $page++;
        }
    }

    protected function logDebug($msg, $context = array())
    {
        if ($this->logger) {
            $this->logger->debug($msg, $context);
        }
    }

    protected function proxyEndpoint()
    {
        return 'https://mwsops.common-services.com/v201/';
    }

    protected function buildLicenseRequestData()
    {
        $connector = $this->connector;
        if (!($connector instanceof IAmazonSellerPartnerLicense)) {
            return array();
        }

        return array(
            'php_version' => phpversion(),
            'platform_version' => $connector->getPlatformVersion(),
            'connector_version' => $connector->getConnectorVersion(),
            'domain' => $connector->getDomain(),
            'license' => $connector->getLicense(),
            'seller_id' => $connector->getSellerId(),
            'ip' => $connector->getIP(),
        );
    }

    public function getApiType()
    {
        return $this->apiType;
    }
}
