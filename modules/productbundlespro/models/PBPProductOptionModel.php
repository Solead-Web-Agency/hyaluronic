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

class PBPProductOptionModel extends ObjectModel
{
    /** @var integer Product ID */
    public $id_product;

    /** @var boolean Add to cart */
    public $disabled_addtocart;


    /**
     * @see ObjectModel::$definition
     */

    public static $definition = array(
        'table' => 'pbp_product_option',
        'primary' => 'id_option',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_INT),
            'disabled_addtocart' => array('type' => self::TYPE_INT)
        )
    );

    public function load($id_product)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_product = ' . (int)$id_product);
        $row = Db::getInstance()->getRow($sql);

        if (!empty($row)) {
            return $this->hydrate($row);
        } else {
            return false;
        }
    }
}
