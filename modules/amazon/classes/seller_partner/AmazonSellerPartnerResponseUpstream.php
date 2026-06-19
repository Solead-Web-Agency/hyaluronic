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
class AmazonSellerPartnerResponseUpstream
{
    protected $code;
    protected $message;
    // Only care about error at this time
    protected $response = array();
    protected $headers = array();

    public function __construct($upstream)
    {
        $this->code = isset($upstream->code) ? $upstream->code : 0;
        $this->message = isset($upstream->message) ? $upstream->message : '';
        $this->headers = isset($upstream->headers) ? $upstream->headers : array();

        if (isset($upstream->response, $upstream->response->errors) && is_array($upstream->response->errors)) {
            foreach ($upstream->response->errors as $error) {
                if (isset($error->code, $error->message)) {
                    $this->response[] = new AmazonSellerPartnerResponseUpstreamError($error->code, $error->message);
                }
            }
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasError()
    {
        return count($this->response) > 0;
    }

    /**
     * @return AmazonSellerPartnerResponseUpstreamError|null
     */
    public function getFirstError()
    {
        if (count($this->response)) {
            return $this->response[0];
        }

        return null;
    }

    public function getError()
    {
        if ($this->hasError()) {
            return $this->getFirstError()->getMessage();
        }

        return '';
    }
}
