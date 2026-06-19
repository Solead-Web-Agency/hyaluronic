<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Team <sales@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedFacebook
 */

class CedWishQueue extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_queue',
        'primary' => 'id_cedwish_queue',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_queue' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'queue_type' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'queued_items' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'priority' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
        ),
    );

    public $id_cedwish_queue;
    public $queue_type;
    public $queued_items;
    public $priority;

    public static function addQueue($queue_type, $queued_items, $priority = 1)
    {
        $queue = new CedWishQueue();
        $queue->priority = (int)$priority;
        $queue->queued_items = pSQL(json_encode($queued_items));
        $queue->queue_type = pSQL($queue_type);
        try {
            return $queue->add();
        } catch (PrestaShopDatabaseException $e) {
            return false;
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    public static function getQueue()
    {
        $sql = "SELECT `id_cedwish_queue`, `queue_type`, `queued_items` 
        FROM `" . _DB_PREFIX_ . "cedwish_queue` WHERE `queue_type` != 'stock' ORDER BY `priority` DESC";
        return Db::getInstance()->getRow($sql);
    }

    public static function deleteQueue($id_cedwish_queue)
    {
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "cedwish_queue` 
        WHERE id_cedwish_queue = '" . (int)$id_cedwish_queue . "'";
        return Db::getInstance()->execute($sql);
    }

    public static function deleteQueueByType($queue_type)
    {
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "cedwish_queue` 
        WHERE queue_type = '" . pSQL($queue_type) . "'";
        return Db::getInstance()->execute($sql);
    }

    public static function getQueueByType($queue_type = 'product', $rows = 0)
    {
        if ($rows) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_queue` 
            WHERE `queue_type` = '" . pSQL($queue_type) . "' ORDER BY `priority` DESC LIMIT ".(int)$rows;
        } else {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . "cedwish_queue` 
            WHERE `queue_type` = '" . pSQL($queue_type) . "' ORDER BY `priority` DESC";
        }
        try {
            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }
}
