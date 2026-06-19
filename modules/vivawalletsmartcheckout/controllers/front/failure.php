<?php
/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

use Vivawallet\VivawalletPhp\Api\OrderClient;
use Vivawalletsmartcheckout\Loggers\Logger;

class VivaWalletSmartCheckoutFailureModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $status = 'failed';
        $vivaPaymentsOrderClient = new OrderClient($this->module->getBearerAuthentication());
        $arguments = [
            'orderCode' => Tools::getValue('s', ''),
        ];
        $vivaPaymentsOrderResponse = $vivaPaymentsOrderClient->retrieveOrder($arguments);
        Logger::log(
            [
                'call' => 'failureUrl',
                'arguments' => [
                    'transactionId' => Tools::getValue('t', ''),
                    'orderCode' => Tools::getValue('s', ''),
                    'eventId' => Tools::getValue('eventId', ''),
                    'retrieveOrderStateID' => $vivaPaymentsOrderResponse->getBody()->stateId
                        ?? null,
                ],
            ],
            'vivaPayments',
            2
        );
        if (
            $vivaPaymentsOrderResponse->isSuccessful()
            && !empty($vivaPaymentsOrderResponse->getBody()->stateId)
        ) {
            if (
                Tools::getValue('cancel')
                && (int) $vivaPaymentsOrderResponse->getBody()->stateId == 2
            ) {
                $status = 'canceled';
            }
        }

        if (version_compare(_PS_VERSION_, '8.0', '>=')) {
            $prestashopVersion = '8.0';
        } elseif (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $prestashopVersion = '1.7';
        } else {
            $prestashopVersion = '1.6';
        }
        $this->context->smarty->assign(
            [
                'reference' => '',
                'status' => $status,
                'prestashop_version' => $prestashopVersion,
            ]
        );
        if ($prestashopVersion != '1.6') {
            $this->setTemplate("module:{$this->module->name}/views/templates/front/order-confirmation-17.tpl");
        } else {
            $this->setTemplate('order-confirmation-16.tpl');
        }
    }
}
