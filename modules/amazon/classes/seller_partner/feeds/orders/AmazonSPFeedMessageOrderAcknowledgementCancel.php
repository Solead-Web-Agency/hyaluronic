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
class AmazonSPFeedMessageOrderAcknowledgementCancel extends AmazonSPFeedMessageOrderAcknowledgement
{
    /** @var AmazonSPFeedMessageOrderAcknowledgementCancelItem[] */
    protected $items = array();
    protected $statusCode = 'Failure';

    public function __construct($amzOrderId, $merchantOrderId, $items)
    {
        parent::__construct($amzOrderId, $merchantOrderId);

        foreach ($items as $item) {
            $feedItem = $item instanceof AmazonSPFeedMessageOrderAcknowledgementCancelItem ? $item :
                AmazonSPFeedMessageOrderAcknowledgementCancelItem::instanceFromInput($item);
            if ($feedItem->isValidItem()) {
                $this->items[] = $feedItem;
            }
        }
    }

    protected function generateOrderAcknowledgement($domDoc)
    {
        $orderAcknowledgement = parent::generateOrderAcknowledgement($domDoc);

        foreach ($this->items as $item) {
            $orderAcknowledgement->appendChild($item->generateItemXml($domDoc));
        }

        return $orderAcknowledgement;
    }
}
