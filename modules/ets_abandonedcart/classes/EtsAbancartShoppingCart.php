<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

class EtsAbancartShoppingCart extends ObjectModel
{
    public $id_cart;
    public $id_customer;
    public $cart_name;
    public $date_add;
    public static $definition = array(
        'table' => 'ets_abancart_shopping_cart',
        'primary' => 'id_cart',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'cart_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public static function itemExist($id_cart)
    {
        return (int)Db::getInstance()->getValue('SELECT id_cart FROM '._DB_PREFIX_.pSQL(self::$definition['table']).' WHERE id_cart ='.(int)$id_cart);
    }

    public static function getDataShoppingCart($id_customer, $id_lang, $id_shop=null)
    {
        if(!$id_shop){
            $id_shop = Context::getContext()->shop->id;
        }
        return Db::getInstance()->executeS('
            SELECT sc.id_cart, sc.cart_name, cart.date_upd FROM `' . _DB_PREFIX_ . 'ets_abancart_shopping_cart` sc
            LEFT JOIN `' . _DB_PREFIX_ . 'cart` cart ON (cart.id_cart = sc.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = cart.id_cart)
            WHERE o.id_order is NULL AND cart.id_cart is NOT NULL 
                AND cart.id_shop = ' . (int)$id_shop . ' 
                AND cart.id_lang = ' . (int)$id_lang . '
                AND sc.id_customer = ' . (int)$id_customer . '
        ');
    }
}