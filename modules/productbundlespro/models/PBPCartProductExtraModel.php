<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PBPCartProductExtraModel extends ObjectModel
{
    /** @var integer Unique ID */
    public $id_pbp_cart_productextra;

    /** @var integer Parent ID */
    public $id_pbp_cart_productextra_parent;

    /** @var integer Product ID Parent */
    public $id_product_parent;

    /** @var integer Product Attribute ID Parent */
    public $id_product_attribute_parent;

    /** @var integer Customization ID Parent */
    public $id_customization_parent;

    /** @var string Date Time Parent Product */
    public $date_add_parent;

    /** @var integer Cart ID */
    public $id_cart;

    /** @var integer Product ID */
    public $id_product;

    /** @var integer Product Attribute ID */
    public $id_product_attribute;

    /** @var integer quantity */
    public $quantity;

    /** @var integer Product Bundle ID */
    public $id_pbp_bundle;

    /** @var Float Discount amount */
    public $discount_amount;

    /** @var string Discount type */
    public $discount_type;

    /** @var string Discount type */
    public $discount_tax;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'pbp_cart_productextra',
        'primary' => 'id_pbp_cart_productextra',
        'fields' => array(
            'id_pbp_cart_productextra_parent' => array('type' => self::TYPE_INT),
            'id_product_parent' => array('type' => self::TYPE_INT),
            'id_product_attribute_parent' => array('type' => self::TYPE_INT),
            'id_customization_parent' => array('type' => self::TYPE_INT),
            'date_add_parent' => array('type' => self::TYPE_DATE),
            'id_cart' => array('type' => self::TYPE_INT),
            'id_product' => array('type' => self::TYPE_INT),
            'id_product_attribute' => array('type' => self::TYPE_DATE),
            'quantity' => array('type' => self::TYPE_INT),
            'id_pbp_bundle' => array('type' => self::TYPE_INT),
            'discount_amount' => array('type' => self::TYPE_FLOAT),
            'discount_type' => array('type' => self::TYPE_STRING),
            'discount_tax' => array('type' => self::TYPE_STRING)
        )
    );
}
