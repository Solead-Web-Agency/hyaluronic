<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

trait RevolutDatabaseHelper
{
    public function getRevolutOrder($id_order)
    {
        return Db::getInstance()->getRow(
            'SELECT UNHEX(`id_revolut_order`) as id_revolut_order, id_order, UNHEX(`public_id`) as public_id FROM `' . _DB_PREFIX_ . 'revolut_payment_orders`'
            . ' WHERE `id_order` = ' . (int)$id_order
        );
    }

    public function getRevolutOrderByIdCart($id_cart)
    {
        return Db::getInstance()->getRow(
            'SELECT UNHEX(`id_revolut_order`) as id_revolut_order, id_order, UNHEX(`public_id`) as public_id FROM `' . _DB_PREFIX_ . 'revolut_payment_orders`'
            . ' WHERE `id_cart` = ' . (int)$id_cart
        );
    }

    public function removeRevolutOrderByIdCart($id_cart)
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'revolut_payment_orders` WHERE `id_cart` = ' . (int)$id_cart);
    }

    public function updatePsOrderIdRecord($id_new_order, $id_cart)
    {
        return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'revolut_payment_orders` SET `id_order` = ' . $id_new_order . ', `save_card` = 0 WHERE id_cart=' . (int)$id_cart);
    }

    public function getOrderCurrentState($id_order)
    {
        return Db::getInstance()->getValue('SELECT current_state FROM `' . _DB_PREFIX_ . 'orders` 
                                    WHERE id_order=' . (int)$id_order);
    }

    public function createRevolutPaymentRecord($id_revolut_order, $public_id, $id_cart)
    {
        return Db::getInstance()->execute(
            'INSERT INTO `' . _DB_PREFIX_ . 'revolut_payment_orders` (`id_revolut_order`, `public_id`, `id_cart`)'
            . ' VALUES (HEX("' . pSQL($id_revolut_order) . '"), HEX("' . pSQL($public_id) . '"), "' . (int)$id_cart . '")'
        );
    }
}
