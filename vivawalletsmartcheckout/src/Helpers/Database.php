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

use Vivawalletsmartcheckout\Loggers\Logger;

class Database
{
    // <editor-fold desc="Create Tables">

    public static function createTableSchema(): bool
    {
        return self::createOrdersTable() && self::createTransactionTypesTable() && self::createTransactionsTable();
    }

    /**
     * Create database table for orders
     *
     * @return bool
     */
    public static function createOrdersTable(): bool
    {
        return \Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . Config::get('app.database.tables.orders') . '` 
            (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `cart_id` int(10) NOT NULL,
                `prestashop_order_id` int(10) NOT NULL,
                `vivawallet_order_code` varchar(100) NOT NULL,
                `client_id` varchar(100) NOT NULL,
                `currency` varchar(3) NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                `is_demo` boolean NOT NULL DEFAULT false,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `gmt_date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX (`prestashop_order_id`),
                INDEX (`vivawallet_order_code`),
                INDEX (`cart_id`)
            )
            DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
        );
    }

    /**
     * Create database table for transactions
     *
     * @return bool
     */
    public static function createTransactionsTable(): bool
    {
        $ordersTable = _DB_PREFIX_ . Config::get('app.database.tables.orders');
        $transactionTypesTable = _DB_PREFIX_ . Config::get('app.database.tables.transaction_types');

        return \Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . Config::get('app.database.tables.transactions') . '` 
            (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `order_id` int(10) NOT NULL,
                `transaction_id` varchar(100) NOT NULL,
                `transaction_type_id` int(10) NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `gmt_date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX (`order_id`),
                INDEX (`transaction_type_id`),
                UNIQUE  `order_id_transaction_id` ( `order_id`, `transaction_id` ),
                FOREIGN KEY (`order_id`) REFERENCES ' . $ordersTable . '(`id`),
                FOREIGN KEY (`transaction_type_id`) REFERENCES ' . $transactionTypesTable . '(`id`)
            )
            DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
        );
    }

    /**
     * Create database table for transaction types
     *
     * @return bool
     */
    public static function createTransactionTypesTable(): bool
    {
        if (\Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . Config::get('app.database.tables.transaction_types') . '` 
            (
                `id` int(10) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE  `name` ( `name`)
            )
            DEFAULT CHARSET=utf8 AUTO_INCREMENT=1'
        )) {
            try {
                $query = new \DbQuery();
                $query->select('name')
                    ->from(Config::get('app.database.tables.transaction_types'));
                $results = \Db::getInstance()->executeS($query);
                $types = !empty($results) ? array_column($results, 'name') : [];
                $typesMissing = array_diff(['payment', 'refund', 'failed', 'init_async_payment', 'preauthorization', 'capture', 'void'], $types);
                $insertTypes = array_map(function ($value) {
                    return ['name' => $value];
                }, $typesMissing);

                return empty($insertTypes)
                    || \Db::getInstance()->insert(Config::get('app.database.tables.transaction_types'), $insertTypes);
            } catch (\PrestaShopDatabaseException $exception) {
                Logger::log(
                    ['action' => 'insertToTransactionTypesTable', 'exception' => $exception->getMessage()],
                    'databaseError'
                );
            }
        }

        return false;
    }

    // </editor-fold>

    // <editor-fold desc="Insert">

    /**
     * Insert a new vivawallet order
     *
     * @param array $values
     *
     * @return bool
     */
    public static function insertOrder(array $values): bool
    {
        try {
            return \Db::getInstance()->insert(Config::get('app.database.tables.orders'), array_map('pSQL', $values));
        } catch (\PrestaShopDatabaseException $exception) {
            Logger::log(['action' => 'insertOrder', 'exception' => $exception->getMessage()], 'databaseError');
        }

        return false;
    }

    /**
     * Insert a new vivawallet transaction
     *
     * @param string $orderCode
     * @param string $transactionId
     * @param float|null $amount
     * @param string $paymentName
     *
     * @return bool
     */
    public static function insertTransaction(
        string $orderCode,
        string $environment,
        string $transactionId,
        ?float $amount = null,
        string $paymentName = 'payment'
    ): bool {
        $result = self::getSmartCheckoutOrderByCode($orderCode, $environment);
        if (!self::transactionExists($transactionId, $result['id'])) {
            try {
                return \Db::getInstance()->insert(
                    Config::get('app.database.tables.transactions'),
                    [
                        'order_id' => pSQL($result['id']),
                        'transaction_id' => pSQL($transactionId),
                        'transaction_type_id' => self::getTransactionTypeId($paymentName),
                        'amount' => is_null($amount) ? pSQL($result['amount']) : pSQL($amount),
                        'date_add' => date('Y-m-d H:i:s'),
                        'gmt_date_add' => gmdate('Y-m-d H:i:s'),
                    ]
                );
            } catch (\PrestaShopDatabaseException $exception) {
                Logger::log(
                    ['action' => 'insertTransaction', 'exception' => $exception->getMessage()],
                    'databaseError'
                );
            }
        }

        return false;
    }

    // </editor-fold>

    // <editor-fold desc="Update">

    /**
     * Update order id
     *
     * @param int $smartCheckoutOrderId
     * @param int $prestashopOrderId
     *
     * @return bool
     */
    public static function updateSmartCheckoutOrder(int $smartCheckoutOrderId, int $prestashopOrderId): bool
    {
        return \Db::getInstance()->update(
            Config::get('app.database.tables.orders'),
            ['prestashop_order_id' => pSQL($prestashopOrderId)],
            'id = ' . pSQL($smartCheckoutOrderId)
        );
    }

    /**
     * Update order id
     *
     * @param int $cartId
     * @param int $prestashopOrderId
     *
     * @return bool
     */
    public static function updateSmartCheckoutOrderByCartId(int $cartId, int $prestashopOrderId): bool
    {
        return \Db::getInstance()->update(
            Config::get('app.database.tables.orders'),
            ['prestashop_order_id' => pSQL($prestashopOrderId)],
            'cart_id = ' . pSQL($cartId)
        );
    }

    // </editor-fold>

    // <editor-fold desc="Boolean">

    /**
     * Get unique transaction
     *
     * @param string $transactionId
     * @param string $orderId
     *
     * @return bool
     */
    private static function transactionExists(string $transactionId, string $orderId): bool
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(Config::get('app.database.tables.transactions'))
            ->where("transaction_id = '" . pSQL($transactionId) . "'")
            ->where("order_id = '" . pSQL($orderId) . "'");

        \Db::getInstance()->execute($query);

        return \Db::getInstance()->numRows() > 0;
    }

    // </editor-fold>

    // <editor-fold desc="Getters">

    /**
     * Get transaction type id by payment name
     *
     * @param string $paymentName
     *
     * @return int|mixed
     */
    private static function getTransactionTypeId(string $paymentName)
    {
        $query = new \DbQuery();
        $query->select('id')
            ->from(Config::get('app.database.tables.transaction_types'))
            ->where("name = '" . pSQL($paymentName) . "'");

        $result = \Db::getInstance()->getRow($query);

        return isset($result['id']) && $result['id'] > 0 ? $result['id'] : 0;
    }

    /**
     * Get Smart Checkout Order by vivawallet order code
     *
     * @param string $orderCode
     * @param string $environment
     *
     * @return array|bool|object|null
     */
    public static function getSmartCheckoutOrderByCode(string $orderCode, string $environment)
    {
        $isDemo = $environment == 'live' ? 0 : 1;
        $query = new \DbQuery();
        $query->select('*')
            ->from(Config::get('app.database.tables.orders'))
            ->where("vivawallet_order_code = '" . pSQL($orderCode) . "'")
            ->where("is_demo = '" . pSQL($isDemo) . "'");

        return \Db::getInstance()->getRow($query);
    }

    /**
     * Get Smart Checkout Order by vivawallet order reference
     *
     * @param \Order $order
     *
     * @return array|bool|object|null
     */
    public static function getSmartCheckoutOrderByPrestashopOrder(\Order $order)
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(Config::get('app.database.tables.orders'))
            ->where("prestashop_order_id = '{$order->id}'")
            ->where("cart_id = '{$order->id_cart}'");

        return \Db::getInstance()->getRow($query);
    }

    /**
     * Get order currency by prestashop order id
     *
     * @param string $orderId
     *
     * @return string|null
     */
    public static function getOrderCurrencyByOrderId(int $orderId)
    {
        $query = new \DbQuery();
        $query->select('currency')
            ->from(Config::get('app.database.tables.orders'))
            ->where("prestashop_order_id  = '" . pSQL($orderId) . "'");

        return \Db::getInstance()->getValue($query);
    }

    /**
     * Get brand color by prestashop shop id
     *
     * @param string $shopId
     *
     * @return string|null
     */
    public static function getBrandColorByShopId(int $shopId)
    {
        $query = new \DbQuery();
        $query->select('value')
            ->from('configuration')
            ->where('id_shop = ' .  $shopId)
            ->where('name = "'. Config::get('app.form.fields.brand_color') .'"');

        return \Db::getInstance()->getValue($query);
    }

    /**
     * Get all the transactions by prestashop order
     *
     * @param \Order $order
     * @param string|null $paymentName
     *
     * @return array
     */
    public static function getTransactionsByOrder(\Order $order, ?string $paymentName = null): array
    {
        $selectFields = [
            'cart_id' => 'o.`cart_id`',
            'vivawallet_order_code' => 'o.`vivawallet_order_code`',
            'order_date_created' => 'o.`date_add`',
            'order_gmt_date_created' => 'o.`gmt_date_add`',
            'order_amount' => 'o.`amount`',
            'currency' => 'o.`currency`',
            'transaction_amount' => 't.`amount`',
            'transaction_id' => 't.`transaction_id`',
            'transaction_date_created' => 't.`date_add`',
            'transaction_gmt_date_created' => 't.`gmt_date_add`',
            'transaction_type' => 'tt.`name`',
        ];
        $selectFields = array_map(
            function ($field, $alias) {
                return "$field AS $alias";
            },
            array_values($selectFields),
            array_keys($selectFields)
        );
        $query = new \DbQuery();
        $query->select(implode(', ', $selectFields))
            ->from(Config::get('app.database.tables.transactions'), 't')
            ->innerJoin(Config::get('app.database.tables.orders'), 'o', 'o.id = t.order_id')
            ->innerJoin(Config::get('app.database.tables.transaction_types'), 'tt', 't.transaction_type_id = tt.id')
            ->where("o.prestashop_order_id = '{$order->id}'")
            ->where("o.cart_id = '{$order->id_cart}'");
        if (!empty($paymentName)) {
            $query->where("tt.name = '" . pSQL($paymentName) . "'");
        }

        try {
            $results = \Db::getInstance()->executeS($query);
        } catch (\PrestaShopDatabaseException $exception) {
            Logger::log(
                ['action' => 'getTransactionsByOrder', 'exception' => $exception->getMessage()],
                'databaseError'
            );
        }

        return !empty($results) && is_array($results) ? $results : [];
    }

    // </editor-fold>

    // <editor-fold desc="Delete">

    /**
     * Delete all tables
     *
     * @return bool
     */
    public static function deleteTablesIfEmpty(): bool
    {
        if (self::tableIsEmpty(Config::get('app.database.tables.orders'))
            && self::tableIsEmpty(Config::get('app.database.tables.transactions'))
        ) {
            $tables = [
                _DB_PREFIX_ . Config::get('app.database.tables.transactions'),
                _DB_PREFIX_ . Config::get('app.database.tables.orders'),
                _DB_PREFIX_ . Config::get('app.database.tables.transaction_types'),
            ];
            $success = [];
            foreach ($tables as $table) {
                try {
                    $success[] = \Db::getInstance()->execute("DROP TABLE `$table`");
                } catch (\PrestaShopDatabaseException $exception) {
                    Logger::log(
                        ['action' => 'deleteTablesIfEmpty', 'exception' => $exception->getMessage()],
                        'databaseError'
                    );
                    $success[] = false;
                }
            }

            return array_product($success);
        }

        return true;
    }

    /**
     * Check if given table is empty
     *
     * @param string $table
     *
     * @return bool
     */
    private static function tableIsEmpty(string $table): bool
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from($table);
        try {
            $results = \Db::getInstance()->executeS($query);
        } catch (\PrestaShopDatabaseException $exception) {
            Logger::log(['action' => 'tableIsEmpty', 'exception' => $exception->getMessage()], 'databaseError');
        }

        return empty($results);
    }

    // </editor-fold>
}
