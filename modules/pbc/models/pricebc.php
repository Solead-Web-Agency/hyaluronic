<?php

/**
 * PrestaShop module created by VEKIA, a guy from official PrestaShop community ;-)
 *
 * @author    VEKIA https://www.prestashop.com/forums/user/132608-vekia/
 * @copyright 2010-2021 VEKIA
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER http://mypresta.eu
 * support@mypresta.eu
 */
class pricebc extends ObjectModel
{
    public $id_pbc;
    public $id_country;
    public $id_shop;
    public $wtd;
    public $value;
    public $active;
    public static $definition = array(
        'table' => 'pbc',
        'primary' => 'id_pbc',
        'multilang' => false,
        'fields' => array(
            'id_pbc' => array('type' => ObjectModel :: TYPE_INT),
            'id_country' => array('type' => ObjectModel :: TYPE_INT),
            'id_shop' => array('type' => ObjectModel :: TYPE_INT),
            'wtd' => array('type' => ObjectModel :: TYPE_INT),
            'value' => array('type' => ObjectModel :: TYPE_FLOAT),
            'active' => array('type' => ObjectModel :: TYPE_INT),
        ),
    );

    public static function getOne($id_country)
    {
        $record = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'pbc` where id_country="' . $id_country . '" AND active=1');
        return $record;
    }
}