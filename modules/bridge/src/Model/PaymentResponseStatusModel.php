<?php
/**
 * Copyright Bridge
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tech@202-ecommerce.com so we can send you a copy immediately.
 *
 * @author    202 ecommerce <tech@202-ecommerce.com>
 * @copyright Bridge
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace BridgeAddon\Model;

use BridgeAddon\API\Object\Response\AbstractResponseObject;

class PaymentResponseStatusModel
{
    /**
     * @var AbstractResponseObject
     */
    private $response;

    /**
     * @var string
     */
    private $status;

    /**
     * @return AbstractResponseObject
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param AbstractResponseObject $response
     *
     * @return PaymentResponseStatusModel
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return PaymentResponseStatusModel
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
