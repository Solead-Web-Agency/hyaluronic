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
 * https://images-na.ssl-images-amazon.com/images/G/01/rainier/help/xsd/release_4_1/OrderAcknowledgement.xsd
 */
class AmazonSPFeedMessageOrderAcknowledgement extends AmazonSPFeedMessage
{
    protected $amzOrderId;
    protected $merchantOrderId;
    protected $statusCode = 'Success';

    public function __construct($amzOrderId, $merchantOrderId)
    {
        parent::__construct();

        $this->amzOrderId = $amzOrderId;
        $this->merchantOrderId = $merchantOrderId;
    }

    protected function generateRemainingMessage($domDoc)
    {
        return array($this->generateOrderAcknowledgement($domDoc));
    }

    /**
     * @param DOMDocument $domDoc
     * @return DOMElement
     * @throws DOMException
     */
    protected function generateOrderAcknowledgement($domDoc)
    {
        $amzOrderId = $domDoc->createElement('AmazonOrderID', $this->amzOrderId);
        $merchantOrderId = $domDoc->createElement('MerchantOrderID', $this->merchantOrderId);
        $statusCode = $domDoc->createElement('StatusCode', $this->statusCode);

        $orderAcknowledgement = $domDoc->createElement('OrderAcknowledgement');
        $orderAcknowledgement->appendChild($amzOrderId);
        $orderAcknowledgement->appendChild($merchantOrderId);
        $orderAcknowledgement->appendChild($statusCode);

        return $orderAcknowledgement;
    }

    public function isValidMessage()
    {
        return true;
    }
}
