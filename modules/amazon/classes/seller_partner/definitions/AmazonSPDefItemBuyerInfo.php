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
class AmazonSPDefItemBuyerInfo extends AmazonSPDefObject
{
    public $BuyerCustomizedInfo;    // ref=BuyerCustomizedInfoDetail
    /** @var AmazonSPDefMoney */
    public $GiftWrapPrice;
    /** @var AmazonSPDefMoney */
    public $GiftWrapTax;
    public $GiftMessageText;
    public $GiftWrapLevel;

    public function __construct($input)
    {
        parent::__construct($input);

        foreach (array('GiftWrapPrice', 'GiftWrapTax') as $money) {
            $this->$money = new AmazonSPDefMoney(
                isset($input->$money) ? $input->$money : new stdClass()
            );
        }

        $this->BuyerCustomizedInfo = new AmazonSPDefBuyerCustomizedInfoDetail(
            isset($input->BuyerCustomizedInfo) ? $input->BuyerCustomizedInfo : new stdClass()
        );
    }
}
