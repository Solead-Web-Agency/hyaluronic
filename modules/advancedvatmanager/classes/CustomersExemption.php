<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */
 
if (!defined('_PS_VERSION_')) {
    exit;
};

class CustomersExemption extends ObjectModel
{
    /* Database column names */
    public $id_advancedvatmanager_customers_exemption;
    public $id_customer;
    public $active = 1;
    public $date_upd;
    public $date_add;

    /* Database structure */
    public static $definition = array(
        'table' => 'advancedvatmanager_customers_exemption', 
        'primary' => 'id_advancedvatmanager_customers_exemption', 
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'active' =>   array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_upd' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false),
            'date_add' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false)
        )
    ); 
    
    /**
     * CustomersExemption::__construct()
     * 
     * @param int $id
     * @param int $idLang
     * @param int $idShop
     * @return
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
        Shop::addTableAssociation('advancedvatmanager_customers_exemption', array('type' => 'shop'));
    }
    
    /**
     * CustomersExemption::getIdCustomersSaved()
     * @params $id_excluded id_advancedvatmanager_customers_exemption excluded
     * @return
     */
    public static function getIdCustomersSaved($id_excluded = null)
    {
        $sql = 'SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` a ' . Shop::addSqlAssociation('advancedvatmanager_customers_exemption', 'a') .($id_excluded !== null?' WHERE a.`id_advancedvatmanager_customers_exemption` != '.(int)$id_excluded:'');
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersExemption::checkCustomerExemption()
     * 
     * @return
     */
    public static function checkCustomerExemption($id)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` a' . Shop::addSqlAssociation('advancedvatmanager_customers_exemption', 'a') . ' WHERE a.`active` = 1 AND a.`id_customer` = '.(int)$id;
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersExemption::checkCustomerForceVATCollection()
     * 
     * @return
     */
    public static function checkCustomerForceVATCollection($id)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` a' . Shop::addSqlAssociation('advancedvatmanager_customers_exemption', 'a') . ' WHERE a.`active` = 0 AND a.`id_customer` = '.(int)$id;
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
}