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

class Config
{
    const BASE_PATH = _PS_MODULE_DIR_ . 'vivawalletsmartcheckout/config/';

    /**
     * Get configuration value
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     */
    public static function get($key, $default = null)
    {
        $fileName = self::BASE_PATH . strstr($key, '.', true) . '.php';
        if (file_exists($fileName)) {
            $configurationArray = require $fileName;
            $key = \Tools::substr(strstr($key, '.'), 1);

            return General::getArrayValueByKey($configurationArray, $key, $default);
        }

        return null;
    }

    /**
     * Get value from Prestashop configuration, with given key from module configuration
     *
     * @param $key
     * @param null $default
     * @param int|null $shopId
     *
     * @return false|string
     */
    public static function getFromDatabase($key, $default = null, int $shopId = null)
    {
        if (
            (!empty(General::getMultistoreShopContextId())
                || !is_null($shopId))
            && !in_array($key, [
                'app.order.states.pending.field',
                'app.order.states.partially_refund.field',
                'app.order.states.awaiting_payment.field',
            ])
        ) {
            $id_shop = !empty(General::getMultistoreShopContextId()) ? General::getMultistoreShopContextId() : $shopId;
            $query = new \DbQuery();
            $query->select('value')
                ->from('configuration')
                ->where('id_shop = \'' . pSQL($id_shop) . '\'')
                ->where("name = '" . pSQL(self::get($key, $default)) . "'");
            $result = \Db::getInstance()->getRow($query);

            return isset($result['value']) ? $result['value'] : false;
        } else {
            return \Configuration::get(self::get($key, $default));
        }
    }

    /**
     * Get value from request, with given key from module configuration
     *
     * @param $key
     * @param null $default
     *
     * @return false|string
     */
    public static function getFromRequest($key, $default = null)
    {
        return \Tools::getValue(self::get($key, $default));
    }

    /**
     * Update Prestashop configuration with given field
     *
     * @param $field
     * @param $value
     *
     * @return false|string
     */
    public static function updateDatabase($field, $value)
    {
        if (!empty(General::getMultistoreShopContextId())) {
            $id_shop = General::getMultistoreShopContextId();
            $groupId = \Shop::getContextShopGroupID();
            $now = date('Y-m-d H:i:s');
            $result = true;
            if (\Configuration::hasKey($field, null, $groupId, $id_shop)) {
                $query = new \DbQuery();
                $query->select('value')
                    ->from('configuration')
                    ->where('id_shop = \'' . pSQL($id_shop) . '\'')
                    ->where("name = '" . pSQL($field) . "'");
                $select = \Db::getInstance()->getRow($query);
                $storedValue = isset($select['value']) ? $select['value'] : false;
                if ($storedValue !== $value) {
                    $updateSql = 'UPDATE `' . _DB_PREFIX_ . 'configuration' . '`
                        SET value = \'' . pSQL($value) . '\',
                        date_upd = \'' . pSQL($now) . '\'
                        WHERE id_shop = \'' . pSQL($id_shop) . '\'
                        AND name = \'' . pSQL($field) . '\'';
                    $result &= \Db::getInstance()->execute($updateSql);
                } else {
                    return $result;
                }
            } else {
                $result &= \Db::getInstance()->insert('configuration', [
                    'id_shop' => (int) $id_shop,
                    'id_shop_group' => $groupId ? (int) $groupId : null,
                    'name' => pSQL($field),
                    'value' => $value ? pSQL($value) : null,
                    'date_add' => pSQL($now),
                    'date_upd' => pSQL($now),
                ], true);
            }
            \Configuration::set($field, $value, $groupId, $id_shop);

            return $result;
        } else {
            return \Configuration::updateValue($field, $value);
        }
    }
}
