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

class CedWishWarehouse extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_warehouse',
        'primary' => 'id_cedwish_warehouse',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'id' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'destination_countries' => array('type' => self::TYPE_DATE, 'db_type' => 'date'),
            'shipping_type' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'name' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'address' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'country_code' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'ship_to_name' => array('type' => self::TYPE_BOOL, 'db_type' => 'text'),
            'city' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'state' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'zipcode' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'street_address1' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
            'street_address2' => array('type' => self::TYPE_STRING, 'db_type' => 'text'),
        ),
    );
    public $id;
    public $id_cedwish_warehouse;
    public $destination_countries;
    public $shipping_type;
    public $country_code;
    public $ship_to_name;
    public $address;
    public $name;
    public $city;
    public $state;
    public $zipcode;
    public $street_address1;
    public $street_address2;

    public function __construct($id_return_warehouse = null)
    {
        if ($id_return_warehouse) {
            parent::__construct($id_return_warehouse);
        }
    }

    public function getWarehouses()
    {
        return Db::getInstance()->executeS("SELECT `id`, `name` FROM `" . _DB_PREFIX_ . "cedwish_warehouse`");
    }
}
