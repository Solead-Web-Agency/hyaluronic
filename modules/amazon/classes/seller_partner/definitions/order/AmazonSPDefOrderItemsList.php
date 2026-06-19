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
class AmazonSPDefOrderItemsList
{
    public $OrderItems = array();   // AmazonSPDefOrderItem
    public $AmazonOrderId;
    public $NextToken;

    public function __construct($OrderItems, $AmazonOrderId, $NextToken)
    {
        $this->AmazonOrderId = $AmazonOrderId;
        $this->NextToken = $NextToken;
        if (count($OrderItems)) {
            $this->OrderItems = array_map(function ($item) {
                return new AmazonSPDefOrderItem($item);
            }, $OrderItems);
        }
    }

    public function getNextToken()
    {
        return $this->NextToken;
    }
}
