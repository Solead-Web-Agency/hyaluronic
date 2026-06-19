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

use Vivawalletsmartcheckout\Helpers\Config;

function upgrade_module_1_4_0()
{
    $query = new \DbQuery();
    $query->select('name')
        ->from(Config::get('app.database.tables.transaction_types'));
    $results = \Db::getInstance()->executeS($query);
    $types = !empty($results) ? array_column($results, 'name') : [];
    $typesMissing = array_diff(['payment', 'refund', 'failed', 'init_async_payment', 'preauthorization', 'capture', 'void'], $types);
    $insertTypes = array_map(function ($value) {
        return ['name' => $value];
    }, $typesMissing);

    if (!empty($insertTypes)) {
        try {
            \Db::getInstance()->execute('START TRANSACTION');
            // Perform the inserts
            \Db::getInstance()->insert(
                Config::get('app.database.tables.transaction_types'),
                $insertTypes
            );
            // Commit the transaction if all inserts succeed
            \Db::getInstance()->execute('COMMIT');
        } catch (Exception $e) {
            \Db::getInstance()->execute('ROLLBACK');
            return false;
        }
    }

    return true;
}
