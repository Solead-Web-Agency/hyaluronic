<?php
/**
 * 2007-2025 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2025 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AmazonPayIPN extends ObjectModel
{
    public $notification_id;

    public $message_payload;

    public static $definition = [
        'table' => 'amazonpay_ipn',
        'primary' => 'id',
        'fields' => [
            'notification_id' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'message_payload' => ['type' => self::TYPE_STRING],
        ],
    ];

    /**
     * @param $notification_id
     *
     * @return AmazonPayIPN|bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByNotificationId($notification_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`id` FROM `' . _DB_PREFIX_ . 'amazonpay_ipn` a
                WHERE a.`notification_id` = "' . pSQL($notification_id) . '"'
        );
        if (is_array($result) && $result['id']) {
            return new self($result['id']);
        }

        return false;
    }
}
