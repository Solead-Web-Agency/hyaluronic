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
class AmazonSellerPartnerResponse
{
    const ERROR_TYPE_INPUT = 'input';
    const ERROR_TYPE_CURL = 'curl';
    const ERROR_TYPE_AMZ = 'amazon';
    const ERROR_TYPE_SERVER = 'server';

    protected $hasError = true;

    protected $errorType;
    protected $errorCode;
    protected $errorMsg;
    /** @var AmazonSellerPartnerResponseUpstream */
    protected $upstream;

    private $rawResponse;
    /** @var object|array json_decode */
    private $payload;
    private $structuredPayload;

    public static function badRequest($errorNo, $errorMsg)
    {
        return new static(self::ERROR_TYPE_INPUT, $errorNo, $errorMsg);
    }

    public static function failedRespCurl($errorNo, $errorMsg)
    {
        return new static(self::ERROR_TYPE_CURL, $errorNo, $errorMsg);
    }

    public static function amzResp($rawResponse, $resParser, $wrapperParent)
    {
        $instance = new static();
        return $instance->constructFromAmzResponse($rawResponse, $resParser, $wrapperParent);
    }

    public static function serverError($response)
    {
        $instance = new static(self::ERROR_TYPE_SERVER);
        $instance->rawResponse = $response;

        return $instance;
    }

    public function __construct($type = null, $code = null, $msg = null)
    {
        $this->errorType = $type;
        $this->errorCode = $code;
        $this->errorMsg = $msg;
    }

    private function constructFromAmzResponse($rawResponse, $resParser, $wrapperParent)
    {
        $this->rawResponse = $rawResponse;

        $apiType = 'N/A';
        if (isset($resParser[0]) && $resParser[0] instanceof AmazonSellerPartnerAPI) {
            $apiType = $resParser[0]->getApiType();
        }

        if (!$rawResponse) {
            $this->errorType = self::ERROR_TYPE_AMZ;
            $this->errorMsg = 'Empty response!';
            $this->ShopifySummaryLog('KO',$apiType, array('type' => $this->errorType, 'error_message' => $this->errorMsg));
            return $this;
        }

        $responseDecoded = json_decode($rawResponse);
        if (!$responseDecoded) {
            $this->errorType = self::ERROR_TYPE_AMZ;
            $this->errorMsg = 'Unable to parse response!';
            $this->ShopifySummaryLog('KO', $apiType, array('type' => $this->errorType, 'error_message' => $this->errorMsg));
            return $this;
        }

        if (isset($responseDecoded->errors)) {
            $this->errorType = self::ERROR_TYPE_AMZ;
            $this->errorCode = $responseDecoded->errors[0]->code;
            $this->errorMsg = $responseDecoded->errors[0]->message;
            $this->upstream = isset($responseDecoded->errors[0]->upstream) ?
                new AmazonSellerPartnerResponseUpstream($responseDecoded->errors[0]->upstream) :
                new AmazonSellerPartnerResponseUpstream(null);
            $this->ShopifySummaryLog('KO', $apiType, array(
                'type' => $this->errorType,
                'code' => $this->errorCode,
                'error_message' => $this->errorMsg,
                'upstream' => $this->upstream
            ));
            return $this;
        }

        if ($wrapperParent) {
            if (!isset($responseDecoded->$wrapperParent)) {
                $this->errorType = self::ERROR_TYPE_AMZ;
                $this->errorMsg = 'Malformed response!';
                $this->ShopifySummaryLog('KO', $apiType, array('type' => $this->errorType, 'error_message' => $this->errorMsg));
                return $this;
            } else {
                $responseDecoded = $responseDecoded->$wrapperParent;
            }
        }

        $this->hasError = false;
        $this->payload = $responseDecoded;
        $this->setStructuredPayload(
            $resParser($this->payload)
        );
        $this->ShopifySummaryLog('OK', $apiType, $resParser($this->payload));
        return $this;
    }

    public function hasError()
    {
        return $this->hasError;
    }

    protected function setStructuredPayload($result)
    {
        $this->structuredPayload = $result;
    }

    public function getErrorType()
    {
        return $this->errorType;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getStructuredPayload()
    {
        return $this->structuredPayload;
    }

    public function getUpstream()
    {
        return $this->upstream;
    }

    public function isThrottle()
    {
        return $this->getErrorCode() === 2001 && $this->getUpstream() && $this->getUpstream()->getCode() === 429;
    }

    public function getThrottleMessage()
    {
        return 'You exceeded your quota for the requested resource. You need to wait for the quota to regenerate';
    }

    private function ShopifySummaryLog($status, $message, $extra = [])
    {
        if (class_exists('ShopifySummaryLog')) {
            $shLog = new ShopifySummaryLog();
            $shLog->setMessage($message)
                ->setChannel(ShopifySummaryLog::TYPE_SP_API)
                ->setExtra($extra);
            if ($status === 'OK') {
                $shLog->info();
            } else {
                $shLog->error();
            }
        }
    }
}
