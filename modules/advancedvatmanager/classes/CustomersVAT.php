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

class CustomersVAT extends ObjectModel
{
    /* Database column names */
    public $id_advancedvatmanager_customers;
    public $id_customer;
    public $id_address;
    public $id_shop;
    public $vat;
    public $validated;
    public $validated_company;
    public $system_check;
    public $status;
    public $date_upd;
    public $date_add;

    /* Database structure */
    public static $definition = array(
        'table' => 'advancedvatmanager_customers', 
        'primary' => 'id_advancedvatmanager_customers', 
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'vat' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'validated' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'validated_company' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'system_check' => array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId', 'allow_null' =>true, 'required' => false),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'date_upd' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false),
            'date_add' => array('type' => self::TYPE_DATE,'validate' => 'isDate','size' => 40,'copy_post' => false)
        )
    ); 
    
    /**
     * CustomersVAT::__construct()
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
     * CustomersVAT::addCustomersVAT()
     * Add Elements to table
     * @param mixed $vat
     * @param int $id_customer
     * @param int $id_address
     * @param mixed $validated
     * @param mixed $validated_company
     * @param mixed $company_name (Registered company name)
     * @param mixed $status
     * @return
     */
    public function addCustomersVAT($vat, $id_customer, $id_address, $validated, $validated_company, $company_name = null, $status, $system_fail =  null)
    {
        $customer = new Customer($id_customer);
        if ($customer) {
            $id_shop = $customer->id_shop;    
        }
        else {
            $id_shop =  Context::getContext()->shop->id;   
        }
        
        if ($system_fail !== null) {
            $system_fail = (int)!$system_fail;    
        }

        // Checks if element exists to update it.
        if (self::checkCustomerAddressExists($id_customer, $id_address)) {
            $id = self::getID($id_customer, $id_address);
            $update = new CustomersVAT($id);
            $update->id_customer = $id_customer;
            $update->id_address = $id_address;
            $update->id_shop = $id_shop;
            $update->vat = $vat;
            $update->validated = (int)$validated;
            $update->validated_company = (int)$validated_company;
            $update->system_check = $system_fail;
            $update->status = $status;
            self::updateVATAddress($id_address, $vat);
            // Insert Company name registered in VIES or GOV.UK
            if ($company_name !== null) {
                self::updateCompanyName($id_address, $company_name);        
            }
            return $update->update();
        }
        // Insert new elements
        else {
            $insert = new CustomersVAT();
            $insert->id_customer = $id_customer;
            $insert->id_address = $id_address;
            $insert->id_shop = $id_shop;
            $insert->vat = $vat;
            $insert->validated = (int)$validated;
            $insert->validated_company = (int)$validated_company;
            $insert->system_check = $system_fail;
            $insert->status = $status;
            self::updateVATAddress($id_address, $vat);
            // Insert Company name registered in VIES or GOV.UK
            if ($company_name !== null) {
                self::updateCompanyName($id_address, $company_name);        
            }
            return $insert->add();
        }
    }

    /**
     * CustomersVAT::isEmptyVATList()
     * Checks if table is empty
     * @return
     */
    public static function isEmptyVATList()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE 1'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER); 
        $result = Db::getInstance()->getValue($sql);
        return !(bool)$result;
    }
    
    /**
     * CustomersVAT::getTotalWithVATValid()
     * Checks total addresses with valid VAT
     * @return
     */
    public static function getTotalWithVATValid()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE validated = 1'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER); 
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getTotalWithVATInvalid()
     * Checks total addresses with invalid VAT
     * @return
     */
    public static function getTotalWithVATInvalid()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE validated = 0'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER); 
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getTotalWithCompanyValid
     * Checks total addresses with valid company name
     * @return
     */
    public static function getTotalWithCompanyValid()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE validated_company = 1'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getTotalWithCompanyInvalid
     * Checks total addresses with invalid company name
     * @return
     */
    public static function getTotalWithCompanyInvalid()
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE validated_company = 0'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
  
    /**
     * CustomersVAT::checkCustomerHasVATValid()
     * Checks if customer has a valid VAT number in any address
     * @param int $id_customer
     * @return
     */
    public static function checkCustomerHasVATValid($id_customer)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_customer = '.(int)$id_customer.' AND validated = 1'; 
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerHasVATValidByCountry()
     * Checks if customer has a valid VAT number by country address
     * @param int $id_customer
     * @return
     */
    public static function checkCustomerHasVATValidByCountry($id_customer, $id_country)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.' AND a.id_country ='.pSQL($id_country).($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated = 1 AND a.active = 1 AND a.deleted = 0'; 
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerHasVATInvalid()
     * Checks if customer has an invalid VAT number in any address
     * @param int $id_customer
     * @return
     */
    public static function checkCustomerHasVATInvalid($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated = 0 AND a.active = 1 AND a.deleted = 0';  
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressHasVATInvalid()
     * Checks if customer has an invalid VAT number in any address
     * @param int $id_customer
     * @return
     */
    public static function getCustomerAddressHasVATInvalid($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT ac.`id_address` FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated = 0 AND a.active = 1 AND a.deleted = 0'; 
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::checkCustomerVATValid()
     * Checks if customer has a valid VAT number in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerVATValid($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.id_address = '.(int)$id_address.' AND ac.validated = 1 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerVATValid()
     * Get country ID with valid VAT number
     * @param int $id_customer
     * @return
     */
    public static function getCountryAddressWithValidVAT($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT a.id_address, a.id_country FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address) WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated = 1 AND a.active = 1 AND a.deleted = 0 ORDER BY a.id_address DESC;'; 
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::checkCustomerVATInvalid()
     * Checks if customer has an invalid VAT number in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerVATInvalid($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.id_address = '.(int)$id_address.' AND ac.validated = 0 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerHasAddressWithoutValidation()
     * Checks if customer has a VAT number pending of validation in any address
     * @param int $id_customer
     * @return
     */
    public static function checkCustomerHasAddressWithoutValidation($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'address` a WHERE a.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND a.active = 1 AND a.deleted = 0 AND a.id_address NOT IN (SELECT ac.id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac)';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerAddressWithoutValidation()
     * Checks if customer has a VAT number pending of validation in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerAddressWithoutValidation($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));

        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'address` a WHERE a.id_customer = '.(int)$id_customer.' AND a.id_address = '.(int)$id_address.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND a.active = 1 AND a.deleted = 0 AND a.id_address NOT IN (SELECT ac.id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac)';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressWithoutValidation()
     * Get addresses having a VAT number pending of validation in determined address
     * @param int $id_customer
     * @return
     */
    public static function getCustomerAddressWithoutValidation($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT a.`id_address` FROM `' . _DB_PREFIX_ . 'address` a WHERE id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND a.`active` = 1 AND a.`deleted` = 0 AND  a.`id_address` NOT IN (SELECT ac.`id_address` FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac)';
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::checkCustomerHasCompanyInvalid()
     * Checks if customer has an invalid Company name in any address
     * @param int $id_customer
     * @return
     */
    public static function checkCustomerHasCompanyInvalid($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated_company = 0 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressHasCompanyWithoutValidation()
     * Gets customer address with an Company name without validation
     * @param int $id_customer
     * @return
     */
    public static function getCustomerAddressHasCompanyWithoutValidation($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT ac.id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated_company = 2 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressHasCompanyInvalid()
     * Gets customer address with an invalid Company name
     * @param int $id_customer
     * @return
     */
    public static function getCustomerAddressHasCompanyInvalid($id_customer)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT ac.id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.validated_company = 0 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::checkCustomerCompanyValid()
     * Checks if customer has a valid Company name in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerCompanyValid($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.id_address = '.(int)$id_address.' AND ac.validated_company = 1 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerCompanyInvalid()
     * Checks if customer has an invalid Company in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerCompanyInvalid($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.id_address = '.(int)$id_address.' AND ac.validated_company = 0 AND a.active = 1 AND a.deleted = 0';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerCompanyWithoutValidation()
     * Checks if customer have not company validation in determined address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerCompanyWithoutValidation($id_customer, $id_address)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (ac.id_address = a.id_address)
WHERE ac.id_customer = '.(int)$id_customer.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND ac.id_address = '.(int)$id_address.' AND a.active = 1 AND a.deleted = 0 AND ac.validated_company = 2 OR a.id_address NOT IN (SELECT ac.id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac)';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkCustomerAddressExists()
     * Checks if customer address exists
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function checkCustomerAddressExists($id_customer, $id_address)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_customer = '.(int)$id_customer.' AND id_address='.(int)$id_address;
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::getCustomerAddresses()
     * Gets all customer addresses filtering if they should be validated with VAT number
     * @param int $minIdAddress
     * @return
     */
    public static function getCustomerAddresses($minIdAddress = false)
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));
        
        $sql = 'SELECT a.*, c.firstname, c.lastname, c.email FROM `' . _DB_PREFIX_ . 'address` a LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (a.id_customer = c.id_customer) WHERE a.id_customer != 0'.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND a.active = 1 AND a.deleted = 0'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c').($minIdAddress?' AND a.id_address > '.(int)$minIdAddress:'').(Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional'?' AND vat_number != "" AND vat_number IS NOT NULL':'').' ORDER BY c.id_customer ASC;';
        if ($addresses = Db::getInstance()->executeS($sql)) {
            return $addresses;
        }
        return false;
    }
    
    /**
     * CustomersVAT::getLastCustomerAddressChecked()
     * Gets last customer address checked
     * @return
     */
    public static function getLastCustomerAddressChecked()
    {
        $sql = 'SELECT MAX(id_address) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers`'; 
        $maxIdAddress = Db::getInstance()->getValue($sql);
        return $maxIdAddress; 
    }
    
    /**
     * CustomersVAT::getCustomerAddress()
     * Gets customer address
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function getCustomerAddress($id_customer, $id_address)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (ac.id_customer = c.id_customer) LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (a.id_address = ac.id_address) WHERE a.id_customer = '.(int)$id_customer.' AND a.id_address = '.(int)$id_address.' AND a.deleted = 0'; 
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressesWithValidationInfo()
     * Gets customer address with validation info
     * @return
     */
    public static function getCustomerAddressesWithValidationInfo()
    {
        $countries = implode(',', json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')));

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` ac LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (ac.id_customer = c.id_customer) LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON (a.id_address = ac.id_address) WHERE a.id_customer != 0'.($countries?' AND a.id_country IN ('.pSQL($countries).')':'').' AND a.active = 1 AND a.deleted = 0'; 
        if ($results = Db::getInstance()->executeS($sql)) {
            $addresses = array();
            foreach ($results as $row) {
                $addresses[] = $row;    
            }
            return $addresses;
        }
        return false;
    }
    
    /**
     * CustomersVAT::getCustomerIDByAddress()
     * Gets customer ID
     * @param int $id_address
     * @return
     */
    public static function getCustomerIDByAddress($id_address)
    {
        $sql = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'address` WHERE id_address='.(int)$id_address; 
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getID()
     * Gets ID of the record
     * @param int $id_customer
     * @param int $id_address
     * @return
     */
    public static function getID($id_customer, $id_address)
    {
        $sql = 'SELECT id_advancedvatmanager_customers FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_customer = '.(int)$id_customer.' AND id_address='.(int)$id_address; 
        $result = (int)Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getVATbyID()
     * Gets VAT number by record Id
     * @param int $id
     * @return
     */
    public static function getVATbyID($id)
    {
        $sql = 'SELECT vat FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_advancedvatmanager_customers = '.(int)$id;
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getAddressIDbyID()
     * Gets address Id by record Id
     * @param int $id
     * @return
     */
    public static function getAddressIDbyID($id)
    {
        $sql = 'SELECT id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_advancedvatmanager_customers = '.(int)$id; 
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::getVATbyCustomerID()
     * Gets VAT number by customer id
     * @param int $id
     * @return
     */
    public static function getVATbyCustomerID($id)
    {
        $sql = 'SELECT vat FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_customer = '.(int)$id;
        $result = Db::getInstance()->getValue($sql);
        return $result;
    }
    
    /**
     * CustomersVAT::deleteByIDAddress()
     * Delete by id_address
     * @param int $id_address
     * @return
     */
    public static function deleteByIDAddress($id_address)
    {
        return Db::getInstance()->delete('advancedvatmanager_customers', 'id_address = '.(int)$id_address);    
    }
    
    /**
     * CustomersVAT::deleteVATbyID()
     * Delete VAT number by record id
     * @param int $id
     * @return
     */
    public static function deleteVATbyID($id)
    {
        return Db::getInstance()->update('advancedvatmanager_customers', array('vat' => '') ,'id_advancedvatmanager_customers = '.(int)$id);    
    }
    
    /**
     * CustomersVAT::checkCustomerVATempty()
     * Checks customer VAT number is empty
     * @param int $id
     * @return
     */
    public static function checkCustomerVATempty($id)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE `vat` = "" AND `id_advancedvatmanager_customers` = '.(int)$id;
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkVATWithSystemFails()
     * Checks customer VAT in address when API system fails
     * @param int $id_address
     * @return
     */
    public static function checkVATWithSystemFails($id_address)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE `system_check` = 0 AND `id_address` = '.(int)$id_address;
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::getCustomerAddressesWithSystemFails()
     * Get customer address by customer ID where system fails during VAT number validation process
     * @param int $id_customer
     * @return array $result
     */
    public static function getCustomerAddressesWithSystemFails($id_customer)
    {
        $sql = 'SELECT `id_address` FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE `system_check` = 0 AND `id_customer` ='.(int)$id_customer.';';
        $result = Db::getInstance()->executeS($sql);
        return $result;
    }
    
    /**
     * CustomersVAT:: checkCustomerAddressWithSystemFails()
     * Check customer address by customer ID where system fails during VAT number validation process
     * @param int $id_customer
     * @param int $id_address
     * @return array $result
     */
    public static function checkCustomerAddressWithSystemFails($id_customer, $id_address)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE `system_check` = 0 AND `id_customer` ='.(int)$id_customer.' AND `id_address` ='.(int)$id_address.';';
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::checkduplicated()
     * Checks duplicated VAT number
     * @param mixed $vat
     * @param int $id_customer
     * @return
     */
    public static function checkduplicated($vat, $id_customer)
    {
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'address` WHERE `vat_number` = "'.pSQL($vat).'" AND `id_customer` != '.(int)$id_customer.' AND `deleted` = 0;'; 
        $result = Db::getInstance()->getValue($sql);
        return (bool)$result;
    }
    
    /**
     * CustomersVAT::updateVATAddress()
     * Updates VAT number in ps_address
     * @param int $id_address
     * @param mixed $vat
     * @return
     */
    public static function updateVATAddress($id_address, $vat)
    {
        return Db::getInstance()->update('address', array('vat_number'=>pSQL($vat)), 'id_address ='.(int)$id_address);
    }
    
    /**
     * CustomersVAT::updateCompanyName()
     * Updates Company name in ps_address
     * @param int $id_address
     * @param mixed $company
     * @return
     */
    public static function updateCompanyName($id_address, $company)
    {
        return Db::getInstance()->update('address', array('company'=>pSQL($company)), 'id_address ='.(int)$id_address);
    }
    
    /**
     * CustomersVAT::updateVATStatusById()
     * Updates Status in table
     * @param int $id
     * @param int $status
     * @return
     */
    public static function updateVATStatusById($id, $status)
    {
        return Db::getInstance()->update('advancedvatmanager_customers', array('status'=>pSQL($status)), 'id_advancedvatmanager_customers ='.(int)$id);
    }
    
    /**
     * CustomersVAT::updateVATSystemCheckById()
     * Updates System check in table
     * @param int $id
     * @param int $system_check
     * @return
     */
    public static function updateVATSystemCheckById($id, $system_check)
    {
        if ($system_check !== null) {
            $system_check = !$system_check;
        }

        return Db::getInstance()->update('advancedvatmanager_customers', array('system_check'=>pSQL($system_check)), 'id_advancedvatmanager_customers ='.(int)$id, 0, true);
    }
    
    /**
     * CustomersVAT::validateVATbyID()
     * Validates VAT number by record id
     * @param int $id
     * @param mixed $validated
     * @return
     */
    public static function validateVATbyID($id, $validated)
    {
        return Db::getInstance()->update('advancedvatmanager_customers', array('validated'=>(int)$validated), 'id_advancedvatmanager_customers ='.(int)$id);
    }  
    
    /**
     * CustomersVAT::truncateTable()
     * Truncate module table
     * @return
     */
    public static function truncateTable()
    {
        $sql = 'TRUNCATE TABLE `'._DB_PREFIX_.'advancedvatmanager_customers`;';
        return Db::getInstance()->execute($sql);
    } 
}