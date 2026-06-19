<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiGetCartData extends Core
{
    public function getData()
    {
        $abandoned = Tools::getValue('abandoned');
        $limit = (int) Tools::getValue('limit', 10);
        $offset = (int) Tools::getValue('offset', 0);
        $sortOrder = Tools::getValue('sort_order', 'DESC');
        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'DESC';
        }
        $abandonedCarts = 0;
        if ($abandoned == 1) {
            $sql = 'SELECT c.* FROM `' . _DB_PREFIX_ . 'cart` c
                    LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON c.id_cart = o.id_cart
                    WHERE o.id_cart IS NULL 
                    AND c.date_add < DATE_SUB(NOW(), INTERVAL 1 DAY)
                    ORDER BY c.date_add ' . pSQL($sortOrder) . '
                    LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

            $abandonedCarts = Db::getInstance()->executeS($sql);
        }
        
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'success',
            'data' => $abandonedCarts
        );

        return $this->fetchJSONResponse();
    }
}
