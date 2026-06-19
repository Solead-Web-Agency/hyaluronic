<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.biz
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.biz is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.biz.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 *
 * @author    Olivier B.
 * @copyright Copyright (c) Since 2011 Common Services Co Ltd / Feed.biz
 * @license   Commercial license
 * @package   Amazon Market Place
 * Support by mail:  support.amazon@common-services.com
 */
if (!defined('_PS_VERSION_')) { exit; }
class AmazonOrderImportOrderLocker
{
    const LOCKER_FILE_PATH = '../../import/orders.cron.lock';
    const SEPARATOR = ',';

    /**
     * Clear all orders in lock file
     */
    public static function clear()
    {
        file_put_contents(self::LOCKER_FILE_PATH, '');
    }

    /**
     * Add orderId to lock file
     */
    public static function lock($orderId)
    {
        $orders = self::getOrders();

        if (!in_array($orderId, $orders)) {
            $orders []= $orderId;
            file_put_contents(self::LOCKER_FILE_PATH, implode(self::SEPARATOR, $orders));
            return true;
        }
        
        return false;
    }

    /**
     * Remove orderId from lock file
     */
    public static function unlock($orderId)
    {
        $orders = self::getOrders();

        foreach ($orders as $k => $id) {
            if ($orderId == $id) {
                unset($orders[$k]);
                file_put_contents(self::LOCKER_FILE_PATH, implode(self::SEPARATOR, $orders));
                return true;
            }
        }
        
        return false;
    }

    private static function getOrders()
    {
        $content = file_get_contents(self::LOCKER_FILE_PATH);

        return explode(self::SEPARATOR, $content);
    }
}