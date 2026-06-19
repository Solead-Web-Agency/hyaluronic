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

class CustomersOrders extends ObjectModel
{
    /* Database column names */
    public $id_advancedvatmanager_orders;
    public $id_order;
    public $id_shop;
    public $id_customer;
    public $notax;
    public $brexit;
    public $invoice;
    public $date_upd;
    public $date_add;

    /* Database structure */
    public static $definition = array(
        'table' => 'advancedvatmanager_orders', 
        'primary' => 'id_advancedvatmanager_orders', 
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'notax' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'brexit' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' =>true, 'required' => false),
            'invoice' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'allow_null' =>true, 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false),
            'date_add' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false)
        )
    ); 
    
    /**
     * CustomersOrders::__construct()
     * 
     * @param int $id
     * @param int $idLang
     * @param int $idShop
     * @return
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);
    }
    
    /**
     * CustomersOrders::addCustomersOrders()
     * Add Elements to table
     * @param object $order
     * @param int $id_customer
     * @param mixed $notax
     * @param mixed $invoice
     * @param mixed $brexit
     * @return
     */
    public function addCustomersOrders($order, $id_customer, $notax, $invoice, $brexit)
    {    
        // Checks if element exists to update it.
        if (self::checkCustomerOrderExists($id_customer, $order->id)) {
            $id = self::getID($id_customer, $order->id);
            $update = new CustomersOrders($id);
            $update->id_customer = $id_customer;
            $update->id_order = $order->id;
            $update->id_shop = Context::getContext()->shop->id;
            $update->notax = $notax;
            $update->invoice = $invoice;
            $update->brexit = $brexit;
            return $update->update();
        }
        // Insert new elements
        else {
            $insert = new CustomersOrders();
            $insert->id_customer = $id_customer;
            $insert->id_order = $order->id;
            $insert->id_shop = Context::getContext()->shop->id;
            $insert->notax = $notax;
            $insert->invoice = $invoice;
            $insert->brexit = $brexit;
            return $insert->add();
        }
    }
    
    /**
     * AdvancedVatManager::getVATOrderInDB()
     * Checks order historic in ps_advancedvatmanager_orders table
     * @param int $id_order
     * @return
     */
    public static function getVATOrderInDB($id_order)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE ord.id_order = '.(int)$id_order.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');
        $result = Db::getInstance()->getRow($sql);
        
        return $result; 
    }
    
    /**
     * CustomersOrders::checkCustomerOrdersExists()
     * Checks if customer order exists
     * @param int $id_customer
     * @param int $id_order
     * @return
     */
    public static function checkCustomerOrderExists($id_customer, $id_order)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE ord.id_customer = '.(int)$id_customer.' AND ord.id_order='.(int)$id_order.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord'); 
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersOrders::checkInvoiceExistsById()
     * Checks if customer order exists
     * @param int $id
     * @return
     */
    public static function checkInvoiceExistsById($id)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE ord.id_advancedvatmanager_orders = '.(int)$id.' AND ord.invoice != 0'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');  
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersOrders::checkOrderWithTaxExempt()
     * Checks if customer order exists
     * @param int $id_order
     * @return
     */
    public static function checkOrderWithTaxExempt($id_order)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE ord.id_order = '.(int)$id_order.' AND ord.notax = 1'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord'); 
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersOrders::getOrderID()
     * Gets order ID
     * @param int $id_advancedvatmanager_orders
     * @return
     */
    public static function getOrderID($id)
    {
        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE id_advancedvatmanager_orders = '.(int)$id.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');  
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersOrders::getID()
     * Gets ID of the record
     * @param int $id_customer
     * @param int $id_order
     * @return
     */
    public static function getID($id_customer, $id_order)
    {
        $sql = 'SELECT id_advancedvatmanager_orders FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` ord WHERE ord.id_order = '.(int)$id_order.' AND ord.id_customer='.(int)$id_customer.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord'); 
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersOrders::getBrexitOrdersByDates()
     * Gets orders ID with Brexit within date range. 
     * @param mixed $from
     * @param mixed $to
     * @return
     */
    public static function getBrexitOrdersByDates($from = null, $to = null)
    {
        if ($from){
            $from = date('Y-m-d H:i:s',strtotime(str_replace('/', '-', $from)));  
        }
        if ($to) {
            $to = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $to)));    
        }
        $sql = 'SELECT aor.id_order  FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` aor LEFT JOIN `' . _DB_PREFIX_ . 'orders` ord ON (ord.`id_order` = aor.`id_order`) WHERE '.($from?'aor.date_add >= "'.$from.'" AND ':'').($to?'aor.date_add <= "'.$to.'" AND ':'').'aor.brexit = 1 AND invoice != 0'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');     
        
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
      
    /**
     * CustomersOrders::getTaxExemptOrdersByDates()
     * Gets orders ID with tax exempt within date range.
     * @param mixed $from
     * @param mixed $to
     * @return
     */
    public static function getTaxExemptOrdersByDates($from = null, $to = null)
    {
        if ($from){
            $from = date('Y-m-d H:i:s',strtotime(str_replace('/', '-', $from)));  
        }
        if ($to) {
            $to = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $to)));    
        }
        $sql = 'SELECT aor.id_order  FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` aor LEFT JOIN `' . _DB_PREFIX_ . 'orders` ord ON (ord.`id_order` = aor.`id_order`) WHERE '.($from?'aor.date_add >= "'.$from.'" AND ':'').($to?'aor.date_add <= "'.$to.'" AND ':'').'aor.notax = 1 AND invoice != 0'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');     
        
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersOrders::getTotalBrexit()
     * Gets total paid in orders with Brexit within date range
     * @param mixed $from
     * @param mixed $to
     * @return
     */
    public static function getTotalBrexit($from = null, $to = null)
    {
        if ($from){
            $from = date('Y-m-d H:i:s',strtotime(str_replace('/', '-', $from)));  
        }
        if ($to) {
            $to = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $to)));    
        }
        $sql = 'SELECT SUM(ord.total_paid)  FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` aor LEFT JOIN `' . _DB_PREFIX_ . 'orders` ord ON (ord.`id_order` = aor.`id_order`) WHERE '.($from?'aor.date_add >= "'.$from.'" AND ':'').($to?'aor.date_add <= "'.$to.'" AND ':'').'aor.brexit = 1 AND aor.invoice != 0'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');     
        
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersOrders::getTotalTaxExempt()
     * Gets total paid in orders with tax exempt within date range
     * @param mixed $from
     * @param mixed $to
     * @return
     */
    public static function getTotalTaxExempt($from = null, $to = null)
    {
        if ($from){
            $from = date('Y-m-d H:i:s',strtotime(str_replace('/', '-', $from)));  
        }
        if ($to) {
            $to = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $to)));    
        }
        $sql = 'SELECT SUM(ord.total_paid)  FROM `' . _DB_PREFIX_ . 'advancedvatmanager_orders` aor LEFT JOIN `' . _DB_PREFIX_ . 'orders` ord ON (ord.`id_order` = aor.`id_order`) WHERE '.($from?'aor.date_add >= "'.$from.'" AND ':'').($to?'aor.date_add <= "'.$to.'" AND ':'').'aor.notax = 1 AND aor.invoice != 0'.Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');     
        
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
}