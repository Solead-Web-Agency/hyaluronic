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

function upgrade_module_0_0_9($module)
{
    if ($module) {
        $sql = array();
    
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "cedwish_products`
         ADD `wish_product_variant_id` VARCHAR(200) NULL";

        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
        return true;
    }
}
