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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedWish
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_1_1($module)
{
    if ($module) {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'cedwish_queue` (
          `id_cedwish_queue` int(11) NOT NULL AUTO_INCREMENT,
          `queue_type` varchar(255) NOT NULL,
          `queued_items` longtext,
          `priority` int(11) DEFAULT 1,
          PRIMARY KEY (`id_cedwish_queue`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }
}
