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

namespace Vivawalletsmartcheckout\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Vivawallet\VivawalletPhp\Api\TransactionClient;
use Vivawallet\VivawalletPhp\Http\Response;
use Vivawalletsmartcheckout\Loggers\Logger;

class Transaction
{
    const SUCCESS_STATUS_IDS = ['F', 'A', 'C'];

    const FAILED_STATUS_IDS = ['E'];

    /**
     * Get the transaction status
     *
     * @param array $options
     * @param Response $transactionResponse
     *
     * @return string status of transaction
     */
    public static function getTransactionStatus(array $options, Response $transactionResponse): string
    {
        $status = 'error';
        if ($transactionResponse->isSuccessful()
            && !empty($transactionResponse->getBody())
            && is_object($transactionResponse->getBody())
            && isset($transactionResponse->getBody()->orderCode)
            && $options['orderCode'] == $transactionResponse->getBody()->orderCode
        ) {
            $status = 'failed';
            if (isset($transactionResponse->getBody()->statusId)
                && in_array($transactionResponse->getBody()->statusId, self::SUCCESS_STATUS_IDS)
            ) {
                $status = $transactionResponse->getBody()->statusId == 'A' ? 'pending' : 'successful';
            }
        } elseif ($transactionResponse->hasError()) {
            $response = $transactionResponse->getError()->getResponse();
            if (!empty($response['exception'])) {
                $status = 'exception';
            }
        }

        return $status;
    }

    /**
     * Retrieve transaction information
     *
     * @param array $options
     *
     * @return Response
     */
    public static function retrieveTransaction(array $options): Response
    {
        $result = Database::getSmartCheckoutOrderByCode((string) $options['orderCode'], (string) $options['environment']);
        $environment = $result['is_demo'] ? 'demo' : 'live';
        $module = \Module::getInstanceByName(Config::get('app.module.attributes.name'));
        $transactionClient = new TransactionClient($module->getBearerAuthentication(
            [
                'environment' => $environment,
                'clientId' => !empty($options['credentials']['clientId']) ? $options['credentials']['clientId'] : Config::getFromDatabase("app.form.fields.$environment.client_id"),
                'clientSecret' => !empty($options['credentials']['clientSecret']) ? $options['credentials']['clientSecret'] : Config::getFromDatabase("app.form.fields.$environment.client_secret"),
            ]
        ));
        $transactionResponse = $transactionClient->retrieveTransactionById($options['transactionId']);
        if (!$transactionResponse->isSuccessful()) {
            Logger::log(
                [
                    'call' => 'retrieveTransactionById',
                    'arguments' => $options['transactionId'],
                    'response' => $transactionResponse->all(),
                ],
                'vivaPayments'
            );
        } elseif (isset($transactionResponse->getBody()->orderCode)
            && $options['orderCode'] != $transactionResponse->getBody()->orderCode
        ) {
            Logger::log(
                [
                    'call' => 'retrieveTransactionById',
                    'description' => 'orderCode mismatch',
                    'arguments' => $options,
                    'response' => $transactionResponse->all(),
                ],
                'vivaPayments'
            );
        }

        return $transactionResponse;
    }
}
