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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Add `product_tax_override` into ps_marketplace_product_option
 * @param Amazon $module
 * @return bool
 */
function upgrade_module_5_2_64($module)
{
    $table = _DB_PREFIX_ . 'marketplace_product_option';

    if (CommonTools::tableExists($table) && !AmazonTools::amazonFieldExists($table, 'product_tax_override')) {
        $sql = "ALTER TABLE `" . pSQL($table) . "` ADD COLUMN `product_tax_override` varchar(64) DEFAULT NULL";
        return Db::getInstance()->execute($sql);
    }

    return true;
}
