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

namespace Vivawalletsmartcheckout\Loggers;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Vivawalletsmartcheckout\Helpers\Config;

class Logger
{
    /**
     * Log a message
     *
     * @param $message
     * @param string|null $errorCode
     * @param int $severity
     *
     * @return bool
     */
    public static function log($message, ?string $errorCode = null, int $severity = 0): bool
    {
        if (is_array($message)) {
            if (!is_null($errorCode) && $errorCode !== 'generalException') {
                $message['category'] = $errorCode;
            }
            $message = json_encode($message);
        }
        $module = \Module::getInstanceByName(Config::get('app.module.attributes.name'));

        return \PrestaShopLogger::addLog(
            $message,
            !$severity ? Config::get('app.logger.severity') : $severity,
            self::mapErrorCodes($errorCode),
            $module->name,
            $module->id
        );
    }

    private static function mapErrorCodes(?string $errorCode)
    {
        $mapArray = [
            'generalException' => 1,
            'install' => 2,
            'uninstall' => 3,
            'configurationFormValidation' => 4,
            'vivaPayments' => 5,
            'hook' => 6,
            'createPrestashopOrder' => 7,
            'updatePrestashopOrder' => 8,
            'retrievePrestashopOrder' => 9,
            'refundValidation' => 10,
            'databaseError' => 11,
        ];

        return is_null($errorCode) ? 0 : ($mapArray[$errorCode] ?? 0);
    }
}
