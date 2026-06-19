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

require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersVAT.php');

class ValidationEngine extends Module
{
    private static $valid;
    private static $system_fail = null;
    private static $registered_company_name;
    private static $vat;
    private static $vat_number;
    private static $vat_iso_code;
    private static $status = array();   
    private $requestDate;
    private $address;
    private $requester_vat_number;
    private $requester_vat_iso_code;
    private $error_code;
    
    protected $eu_prefix = array();
    
    public static $european_countries_iso = array('AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK');
    public static $brexit_countries_iso = array('GB');
    public static $company_valid = 2;// 1 Valid, 0 Invalid, 2 Not checked
    public static $validation_process = false;
    public static $skip_validation_process = false;
    public static $duplicated = false;
    public static $notax_customer = false;
    public static $customer_with_vat_valid = false;
    public static $brexit_customer = false;
    public static $init_validation_process = false;
    public static $id_address_used;
    public static $merchant_validation = false;
    public static $checkVatCron = false; // True when it is using the Check VAT Cron task 
    public static $checkVatProcess = false; // True when it is using the Check VAT in customer VAT number management 
    public $message;
    public $skip_api_fails = false;
    public static $admin_scan = false;

    /**
     * Characters to scape from Documents
     *
     * @var array
     */
    protected $escapeChars = array(".", ",", "-", "_", " ");
    
    /**
     * ValidationEngine::__construct()
     * 
     * @param bool $vat
     * @return
     */
    public function __construct($vat = null)
    {
        $this->error_code = array(
            'INVALID_INPUT' => $this->l('Invalid country ISO code', 'ValidationEngine'),
            'INVALID_REQUESTER_INFO' => $this->l('Invalid Merchant VAT information', 'ValidationEngine'),
            'SERVICE_UNAVAILABLE' => $this->l('Service unavailable', 'ValidationEngine'),
            'MS_UNAVAILABLE' => $this->l('Request unavailable', 'ValidationEngine'),
            'TIMEOUT' => $this->l('Timeout', 'ValidationEngine'),
            'VAT_BLOCKED' => $this->l('VAT number is blocked', 'ValidationEngine'),
            'IP_BLOCKED' => $this->l('Mercahnt server IP address is blocked', 'ValidationEngine'),
            'GLOBAL_MAX_CONCURRENT_REQ' => $this->l('Maximum global of concurrent requests reached', 'ValidationEngine'),
            'GLOBAL_MAX_CONCURRENT_REQ_TIME' => $this->l('Maximum global time of concurrent requests reached', 'ValidationEngine'),
            'MS_MAX_CONCURRENT_REQ' => $this->l('Maximum of concurrent requests reached', 'ValidationEngine'),
            'MS_MAX_CONCURRENT_REQ_TIME' => $this->l('Maximum time of concurrent requests reached', 'ValidationEngine'),
            'NOT_FOUND' => $this->l('UK VAT number is not found as registered company', 'ValidationEngine'),
            'INVALID_REQUEST' => $this->l('Invalid API request', 'ValidationEngine'),
            'INTERNAL_SERVER_ERROR' => $this->l('Internal server error', 'ValidationEngine'),
        );
        // Save countries ISO code for VAT
        if (Configuration::get('ADVANCEDVATMANAGER_COUNTRY')) {
            $countries_id = json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY'));
            foreach ($countries_id as $id) {
                // Fix brexit with Nord of Ireland
                if (Country::getIsoById($id) == 'GB') {
                    $this->eu_prefix[Country::getIsoById($id)] = 'XI'; 
                }
                else if (Country::getIsoById($id) == 'GR') {
                    $this->eu_prefix[Country::getIsoById($id)] = 'EL'; 
                }
                else {
                    $this->eu_prefix[Country::getIsoById($id)] = Country::getIsoById($id);
                }
            }
        }
        // Register merchant VAT as requester
        if (Tools::getValue('ADVANCEDVATMANAGER_MERCHANT_VAT')) {
            $this->requester_vat_number = Tools::substr(Tools::getValue('ADVANCEDVATMANAGER_MERCHANT_VAT'), 2);
            $this->requester_vat_iso_code = Tools::substr(Tools::getValue('ADVANCEDVATMANAGER_MERCHANT_VAT'), 0, 2);     
        }
        else if (configuration::get('ADVANCEDVATMANAGER_MERCHANT_VAT')) {
            $this->requester_vat_number = Tools::substr(configuration::get('ADVANCEDVATMANAGER_MERCHANT_VAT'), 2);
            $this->requester_vat_iso_code = Tools::substr(configuration::get('ADVANCEDVATMANAGER_MERCHANT_VAT'), 0, 2);     
        }
        
        self::$vat = $this->formatVAT($vat);// Format VAT number
        self::$vat_number = Tools::substr(self::$vat, 2);
        self::$vat_iso_code = Tools::substr(self::$vat, 0, 2);  
        self::$status = array();
        self::$company_valid = 2;
        self::$notax_customer = false;  
        
        if (
            (Configuration::get('ADVANCEDVATMANAGER_SKIPAPISYSTEMFAIL') == 1 && self::$checkVatCron === false && self::$checkVatProcess === false) ||
            (Configuration::get('ADVANCEDVATMANAGER_CRON_SKIPAPISYSTEMFAIL') == 1 && self::$checkVatCron && self::$checkVatProcess === false)
        )
        {
            $this->skip_api_fails = true;
        }
        parent::__construct();
    }
    
    /**
     * ValidationEngine::getVATValidation()
     * Gets VAT number validation (true or false)
     * @return
     */
    public static function getVATValidation()
    {
        return self::$valid;
    }
    
    /**
     * ValidationEngine::setVATValidation()
     * 
     * @param mixed $valid
     * @return
     */
    public static function setVATValidation($valid = null)
    {
        if ($valid !== null) {
            self::$valid = $valid;    
        }
        return self::$valid;
    }
    
    /**
     * ValidationEngine::getSystemFail()
     * 
     * @return
     */
    public static function getSystemFail()
    {
        return self::$system_fail;
    }
    
    /**
     * ValidationEngine::setSystemFail()
     * 
     * @param mixed $system_fail
     * @return
     */
    public static function setSystemFail($system_fail)
    {
        self::$system_fail = $system_fail;
        return self::$system_fail;
    }
    
    /**
     * ValidationEngine::getAddress()
     * 
     * @return
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * ValidationEngine::getRegisteredCompanyName()
     * 
     * @return
     */
    public static function getRegisteredCompanyName()
    {
        return self::$registered_company_name;
    }
    
    /**
     * ValidationEngine::getRequestDate()
     * 
     * @return
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }
    
    /**
     * ValidationEngine::getVat()
     * Gets VAT number complete (with ISO code)
     * @return
     */
    public static function getVat()
    {
        return self::$vat;
    }
    
    /**
     * ValidationEngine::getVat()
     * Gets VAT number complete (with ISO code)
     * @return
     */
    public static function setVat($vat = null)
    {
        if ($vat !== null) {
            self::$vat = $vat;    
        }
        return self::$vat;
    }
    
    /**
     * ValidationEngine::getVatNumber()
     * Gets VAT number with no ISO code
     * @return
     */
    public static function getVatNumber()
    {
        return self::$vat_number;
    }
    
    /**
     * ValidationEngine::getVatIso()
     * Gets ISO code from VAT number
     * @return
     */
    public static function getVatIso()
    {
        return self::$vat_iso_code;
    }
    
    /**
     * ValidationEngine::getrequesterVatNumber()
     * 
     * @return
     */
    public function getrequesterVatNumber()
    {
        return $this->requester_vat_number;
    }
    
    /**
     * ValidationEngine::getrequesterVatIso()
     * 
     * @return
     */
    public function getrequesterVatIso()
    {
        return $this->requester_vat_iso_code;
    }
    
    /**
     * ValidationEngine::getVIESresponse()
     * 
     * @return
     */
    public function getVIESresponse()
    {
        return $this->response;
    }
    
    /**
     * ValidationEngine::getEUPrefix()
     * 
     * @return
     */
    public function getEUPrefix()
    {
        return $this->eu_prefix;
    }
    
    /**
     * ValidationEngine::getStatus()
     * 
     * @return
     */
    public static function getStatus()
    {
        return implode(PHP_EOL, self::$status);
    }
    
    /**
     * ValidationEngine::setStatus()
     * 
     * @param mixed $status
     * @return
     */
    public static function setStatus($status = null)
    {
        if ($status !== null) {
            self::$status[] = $status;    
        }
    }
    
    /**
     * ValidationEngine::getMessage()
     * 
     * @return
     */
    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * ValidationEngine::setMessage()
     * 
     * @param mixed $message
     * @return
     */
    public function setMessage($message = null)
    {
        if ($message !== null) {
            $this->message = $message;    
        }
        return $this->message;
    }
    
    /**
     * ValidationEngine::escape()
     * Escape the Document or any string. Remove the $scape_chars of the string.
     * @param mixed $string
     * @return
     */
    public function escape($string)
    {
        return str_replace($this->escapeChars, "", $string);
    }
     
    /**
     * ValidationEngine::skipVATFieldBycountry()
     * Skip validation for non selected countries for validation
     * @param mixed $country_id
     * @return
     */
    public static function skipVATFieldBycountry($country_id = null)
    {
        // Checks countries to skip validation
        if ($country_id && !in_array($country_id, json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')))) {
            return true;    
        }     
        return false;
    }
    /**
     * ValidationEngine::skipVATValidation()
     * Skip validation for determined conditions
     * @param mixed $country_id
     * @param mixed $company
     * @param mixed $vat_number
     * @return
     */
    public static function skipVATValidation($country_id = null, $company =  null, $vat_number = null)
    {
        if ($vat_number === null) {
            $vat_number = Tools::getValue('vat_number');
        }
        
        if ($company === null) {
            $company = Tools::getValue('company');
        }
        
        if ($country_id === null) {
            $country_id = Tools::getValue('id_country');
        }
        
        // Reforce validation system to avoid issues validating wrong VAT numbers in wrong countries what happens in the execution of cron tasks to import orders in third-party modules.
        if ($country_id === null || $country_id == '') {
            return true;    
        }

        // Checks Front validation enabled
        if (Context::getContext()->controller instanceof FrontController) {
            if (Configuration::get('ADVANCEDVATMANAGER_FRONTVALIDATION') == 0 && self::$admin_scan === false) {
                return true;
            }
            // Checks countries to skip validation
            if ((Configuration::get('ADVANCEDVATMANAGER_COUNTRY') && $country_id && !in_array($country_id, json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')))) || Configuration::get('ADVANCEDVATMANAGER_COUNTRY') == '') {
                return true;    
            }
            // Checks if VAT number is empty and field is optional
            if (empty($vat_number) && Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional') {
                return true;        
            }
            // Checks company field
            if (Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') && ((Tools::getIsset('company') && Tools::getValue('company') == '') || empty($company))) {
                return true;    
            }    
        }
        // Checks Admin validation enabled
        else if (Context::getContext()->controller instanceof AdminController) {
            // Admin VAT validation
            if (Context::getContext()->controller->controller_name ==  'AdminVATValidation') {
                if (empty($vat_number)) {
                    return true;
                }
            }
            if (Configuration::get('ADVANCEDVATMANAGER_ADMINVALIDATION') == 0 && self::$admin_scan === false) {
                return true;
            }
             // Checks if VAT number is empty and field is optional
            if (empty($vat_number) && Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'optional') {
                return true;        
            }            
            // Checks countries to skip validation
            if ((Configuration::get('ADVANCEDVATMANAGER_COUNTRY') && $country_id && !in_array($country_id, json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY')))) || Configuration::get('ADVANCEDVATMANAGER_COUNTRY') == '') {
                return true;    
            }  
        }
        return false;
    }
    
    /**
     * ValidationEngine::VATValidationProcess()
     * Validation process included skip validation process
     * If skip validation is success then this method returns false and variable self::$skip_validation_process is true.
     * @param mixed $country_id
     * @param mixed $id_customer
     * @param mixed $id_address
     * @return
     */
    public function VATValidationProcess($country_id = null, $id_customer = null, $id_address = null, $company = null)
    { 
        self::$init_validation_process = true;
        if ($id_customer === null) {
            $id_customer = (isset(Context::getContext()->customer->id) && Context::getContext()->customer->id?Context::getContext()->customer->id:Tools::getValue('id_customer'));    
        }
        if ($id_address === null) {
            $id_address = Tools::getIsset('id_address')?Tools::getValue('id_address'):false;
        }
        
        $customer = new Customer($id_customer);
        
        // Company name
        if ($company === null) {
            $company = Tools::getValue('company');  
        }
        
        $customer_info = array('id_customer' => $customer->id, 'firstname' => $customer->firstname, 'lastname' => $customer->lastname, 'id_country' => $country_id, 'vat' => self::$vat, 'email' => $customer->email, 'id_address' => $id_address);
        
        if ($country_id) {
            $country_iso = Country::getIsoById($country_id);
        }
        
        // Skip validation process
        if (self::skipVATValidation($country_id, $company, self::$vat) === false) {
            // Checks blacklist
            if (Configuration::get('ADVANCEDVATMANAGER_BLACKLIST') != '') {
                $blacklist = explode(',', Configuration::get('ADVANCEDVATMANAGER_BLACKLIST'));
                if (in_array(self::$vat, $blacklist)) {
                    $this->message = sprintf($this->l('The VAT number %s is banned and into a blacklist. You cannot register the address with this VAT number.', 'ValidationEngine'), self::$vat);    
                    return self::$valid = false;
                    
                }
            }
            // Validation type
            if (Configuration::get('ADVANCEDVATMANAGER_VALIDATION_TYPE') == 'format') {
                $this->formatValidation(self::$vat, $country_id, $company);
            }
            elseif (Configuration::get('ADVANCEDVATMANAGER_VALIDATION_TYPE') == 'api') {
                if ($this->formatValidation(self::$vat, $country_id, $company)) {
                    if ($country_iso == 'GB' && self::$vat_iso_code != $this->eu_prefix['GB']) {
                        if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1) {
                            $this->validationForUKVAT();      
                        }
                        else {
                            self::$valid = false;
                            $this->message = sprintf($this->l('The VAT number %s belongs to the United Kingdom and this shop does not have validation activated for VAT numbers from this country.', 'ValidationEngine'), self::$vat);
                            self::$status[] = $this->l('The VAT number belongs to the United Kingdom and Brexit option is disabled.', 'ValidationEngine');
                        }
                    }
                    else if ($country_iso != 'GB' || ($country_iso == 'GB' && self::$vat_iso_code == $this->eu_prefix['GB'])) {
                        Configuration::get('ADVANCEDVATMANAGER_VALIDATION_TYPE') == '1-way'?$this->vatValidationViesOneWay():(configuration::get('ADVANCEDVATMANAGER_MERCHANT_VAT')?$this->vatValidationViesTwoWay():$this->vatValidationViesOneWay());  
                    } 
                }
                // No admin scan and send email after API validation
                if (self::$admin_scan === false) {
                    if (self::$valid) {
                        $module = Module::getInstanceByName('advancedvatmanager');
                        // API system process fails
                        if (self::$system_fail === true) {
                            if (Configuration::get('ADVANCEDVATMANAGER_SENDAPIALERT') == 1) {
                                $module->sendEmail(0, $customer_info);    
                            }   
                        }
                        else {
                            if (Configuration::get('ADVANCEDVATMANAGER_SENDEMAILVALIDATION') == 1) {
                                if ($id_address) {
                                    if (!CustomersVAT::checkCustomerVATValid($customer->id, $id_address)) {
                                        $module->sendEmail(1, $customer_info);    
                                    }    
                                }
                                else {
                                    $module->sendEmail(1, $customer_info);        
                                }
                            } 
                        }
                    }
                }
            }
            // Checks duplicated once validation is performed
            if (CustomersVAT::checkduplicated(self::$vat, $id_customer)) {
                self::$duplicated = true;
                if (!empty(self::$vat) && (bool)Configuration::get('ADVANCEDVATMANAGER_ALLOWDUPLICATED') === false && self::$valid) {
                    $this->message = sprintf($this->l('The VAT number %s is already in our database associated to another customer.', 'ValidationEngine'), self::$vat);
                    self::$status[] = $this->l('The VAT number is duplicated.', 'ValidationEngine');
                    self::$valid = false;
                }
                elseif (!empty(self::$vat) && (bool)Configuration::get('ADVANCEDVATMANAGER_ALLOWDUPLICATED') === true && self::$valid) {
                    $this->message = sprintf($this->l('The VAT number %s is already in our database associated to another customer.', 'ValidationEngine'), self::$vat);
                    self::$status[] = $this->l('The VAT number is duplicated.', 'ValidationEngine');
                    self::$valid = true;
                }   
            }             
            //Checks company name
            if (self::$valid && Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION') && !empty(self::$registered_company_name)) {
                if (!empty($company) && Tools::strtolower(self::$registered_company_name) != Tools::strtolower($company)) {
                    self::$company_valid = 0;
                    self::$status[] = sprintf($this->l('The company name %s does not match the company name registered in VIES/GOV.UK', 'ValidationEngine'), $company);
                    $this->message = sprintf($this->l('The company name %s is not valid. Please enter the exact name of the company that is registered under that VAT number', 'ValidationEngine'), $company);      
                }
                else if (!empty($company) && Tools::strtolower(self::$registered_company_name) == Tools::strtolower($company)) {
                    self::$company_valid = 1;
                    self::$status[] = $this->l('The company validation is successfully', 'ValidationEngine');
                    $this->message = $this->l('The company validation is successfully', 'ValidationEngine');
                }
                else if (empty($company)) {
                    self::$company_valid = 0; 
                    self::$status[] = $this->l('The company name field is empty', 'ValidationEngine');
                    $this->message = $this->l('The company name field is empty', 'ValidationEngine');  
                }  
            }
            if (self::$valid && (Tools::getValue('autofillcompany') == 1 || Configuration::get('ADVANCEDVATMANAGER_COMPANY_AUTOINSERT'))) {
                if (Tools::strtolower(self::$registered_company_name) != Tools::strtolower($company)) {
                    $this->message = sprintf($this->l('The company name %s has beend changed to %s which is the company registered in VIES or GOV.UK for the VAT number validated'),$company, self::$registered_company_name);
                }
                self::$company_valid = 1;
            }
            
            // Assign customer group after validating
            self::manageCustomerGroups($country_id, $id_customer, self::$valid);
            
            // Save static values
            self::$validation_process = true;
            self::$skip_validation_process = false;
            
            return self::$valid;
        }
        else {
            // Save static values
            self::$validation_process = false;
            self::$skip_validation_process = true;
            return false;
        }
    }

    /**
     * ValidationEngine::vatValidationViesOneWay()
     * API to check VAT number in VIES
     * Example of object response
     * public 'countryCode' => string 'ES'
     * public 'vatNumber' => string '98715847R'
     * public 'requestDate' => string '2021-10-12+02:00'
     * public 'valid' => boolean true
     * public 'name' => string '---'
     * public 'address' => string '---'
     * @params $vat string (Full VAT number with iso code included)
     * @param bool $skip_api_system_fail (Skip validation if API system fails))
     * @return self::$valid boolean (true if validation is OK)
    */
    public function vatValidationViesOneWay()
    {
        self::$system_fail = false;
        self::$valid = false;
        // Initialize SOAP Client
        $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array("trace" => 1, "exception" => 1));
        try {
            $validation = $client->checkVat(array(
                'countryCode' => self::$vat_iso_code,
                'vatNumber' => self::$vat_number
            ));
            self::$valid = $validation->valid; 
            $this->requestDate = $validation->requestDate;
            self::$registered_company_name = $validation->name;
            $this->address = $validation->address;
            // Gets response code (200 is OK)
            preg_match("/HTTP\/\d\.\d\s*\K[\d]+/", $client->__getLastResponseHeaders(), $response);
            if ((int)$response[0] != 200) {
                self::$system_fail =  true; 
                if ($this->skip_api_fails) {
                    self::$valid =  true;             
                }
                $this->message = sprintf($this->l('An error occurred while the validation process was running [%s]', 'ValidationEngine'), $response[0]);
            }
            else {
                if (self::$valid) {
                    $this->message = $this->l('VAT number has been validated successfully by VIES.', 'ValidationEngine');        
                }
                else {
                    $this->message = $this->l('VAT number is invalid.', 'ValidationEngine');       
                }
            }
            self::$status[] = sprintf($this->l('[HTTP code %s | 1-Way VIES Validation] - %s', 'ValidationEngine'), $response[0], $this->message);
        } catch (Exception $e)  {
            self::$system_fail =  true; 
            if ($this->skip_api_fails) {
                self::$valid =  true;
                self::$status[] = sprintf($this->l('An error occurred during the VAT number validation process but system has validated the VAT number to avoid blocking ordering. The error code is [%s].', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
                $this->message = sprintf($this->l('An error occurred during the VAT number validation process. The error code is [%s].', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
            }
            else {
                self::$status[] = sprintf($this->l('%s', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
                $this->message = sprintf($this->l('An error occurred during the VAT number validation process. The error code is [%s].', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
            } 
        }
        return self::$valid;
    }
    
    /**
     * ValidationEngine::vatValidationViesTwoWay()
     * API to check VAT number in VIES
     * Example of object response
     * public 'countryCode' => string 'ES'
     * public 'vatNumber' => string '98715847R'
     * public 'requestDate' => string '2021-10-12+02:00'
     * public 'valid' => boolean true
     * public 'name' => string '---'
     * public 'address' => string '---'
     * @params $vat string (Full VAT number with iso code included)
     * @param bool $skip_api_system_fail (Skip validation if API system fails))
     * @return self::$valid boolean (true if validation is OK)
     */
    public function vatValidationViesTwoWay()
    {
        self::$system_fail = false;
        self::$valid = false;
        // Initialize SOAP Client
        $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", array("trace" => 1, "exception" => 1));
        try {
            $validation = $client->checkVatApprox(array(
                'countryCode' => self::$vat_iso_code,
                'vatNumber' => self::$vat_number,
                'requesterVatNumber' => $this->requester_vat_number,
                'requesterCountryCode' => $this->requester_vat_iso_code,
            ));
            self::$valid = $validation->valid;
            $this->requestDate = isset($validation->requestDate)?$validation->requestDate:'';
            self::$registered_company_name = isset($validation->traderName)?$validation->traderName:'';
            $this->address = isset($validation->traderAddress)?$validation->traderAddress:'';
             // Gets response code (200 is OK)
            preg_match("/HTTP\/\d\.\d\s*\K[\d]+/", $client->__getLastResponseHeaders(), $response);
            if ((int)$response[0] != 200) {
                self::$system_fail =  true; 
                if ($this->skip_api_fails) {
                    self::$valid =  true;             
                }
                $this->message = sprintf($this->l('An error occurred while the validation process was running [%s]', 'ValidationEngine'), $response[0]);
            }
            else {
                if (self::$valid) {
                    $this->message = $this->l('VAT number has been validated successfully by VIES.', 'ValidationEngine');
                }
                else {
                    $this->message = $this->l('VAT number is invalid.', 'ValidationEngine');       
                }
            }
            self::$status[] = sprintf($this->l('[HTTP code %s | 2-Way VIES Validation] - %s', 'ValidationEngine'), $response[0], $this->message);
        } catch (Exception $e)  {
            self::$system_fail =  true; 
            if ($this->skip_api_fails) {
                self::$valid =  true; 
                self::$status[] = sprintf($this->l('An error occurred during the VAT number validation process but system has validated the VAT number to avoid blocking ordering. The error code is [%s].', 'ValidationEngine'), $this->error_code[$e->getMessage()]);           
            }
            else {
                self::$status[] = sprintf($this->l('%s', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
                $this->message = sprintf($this->l('An error occurred during the VAT number validation process. The error code is [%s].', 'ValidationEngine'), $this->error_code[$e->getMessage()]);
            } 
        }
        return self::$valid;
    }
    
    /**
     * ValidationEngine::validationForUKVAT()
     * API to check UK VAT number in GOV.UK system
     * 
     * @param bool $skip_api_system_fail (Skip validation if API system fails))
     * @return self::$valid boolean (true if validation is OK)
     */
    public function validationForUKVAT()
    {
        self::$system_fail = false;
        self::$valid = false;
        $headers = array(
            'Accept: application/vnd.hmrc.1.0+json',
        ); 
        
        if (Configuration::get('ADVANCEDVATMANAGER_VALIDATION_SYSTEM') == '1-way') {
            $url = 'https://api.service.hmrc.gov.uk/organisations/vat/check-vat-number/lookup/'.self::$vat_number;  
        }
        else if (Configuration::get('ADVANCEDVATMANAGER_VALIDATION_SYSTEM') == '2-way') {
            $url = 'https://api.service.hmrc.gov.uk/organisations/vat/check-vat-number/lookup/'.self::$vat_number.'/'.$this->requester_vat_number;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        
        $response = json_decode(curl_exec($ch), true);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($http_code == 200) {
            self::$valid =  true;
            $this->message = $this->l('UK VAT number has been validated successfully by HMRC GOV.UK system.', 'ValidationEngine');
            self::$registered_company_name = $response['target']['name'];
            $this->address = $response['target']['address']['line1'].PHP_EOL.$response['target']['address']['line2'].PHP_EOL.$response['target']['address']['line3'].PHP_EOL.$response['target']['address']['line3'];
            $this->requestDate = $response['processingDate'];
            
        }
        else if ($http_code != 404 && $http_code != 400 && $http_code < 500) {
            $this->message = sprintf($this->l('An error occurred during the UK VAT number validation process. The error code is [%s].', 'ValidationEngine'), $http_code);    
        }
        else if ($http_code >= 500) {
            if ($this->skip_api_fails) {
                self::$valid =  true;
                self::$system_fail =  true;   
                self::$status[] = $this->l('An error occurred during the VAT number validation process but system has validated the VAT number to avoid blocking ordering.', 'ValidationEngine');           
            }
            $this->message = sprintf($this->l('An error occurred during the UK VAT number validation process. The error code is [%s].', 'ValidationEngine'), $http_code);    
        }
        else {
            $this->message = sprintf($this->l('VAT number %s is invalid', 'ValidationEngine'), $this->requester_vat_iso_code.$this->requester_vat_number);       
        }
        self::$status[] = sprintf($this->l('[HTTP code %s | %s] - %s', 'ValidationEngine'), $http_code,Configuration::get('ADVANCEDVATMANAGER_VALIDATION_SYSTEM'), $this->message);
        return self::$valid;
    }
    
    /**
     * ValidationEngine::formatVAT()
     * Format VAT number
     * @param mixed $vat
     * @return
     */
    public function formatVAT($vat)
    {
        self::$vat = $vat;    
        
        if (Configuration::get('ADVANCEDVATMANAGER_VALIDATION_MODE') == 'smart') {
            self::$vat = $this->escape(self::$vat);
        }
        if (Configuration::get('ADVANCEDVATMANAGER_SAVETOUPPERCASE')) {
            self::$vat = Tools::strtoupper(self::$vat);
        }
        return self::$vat;      
    }
    
    /**
     * ValidationEngine::formatValidation()
     * Format validation process
     * @param mixed $vat
     * @param mixed $country_id
     * @param mixed $company
     * @return
     */
    public function formatValidation($vat, $country_id = null, $company = null)
    { 
        //Put it in uppercase
        $vat = Tools::strtoupper($vat);
        // VAT iso code
        $vat_iso_code = Tools::substr($vat, 0, 2);
        // Separate 2 parts of number
        $vat_number = Tools::substr($vat, 2);

        $country_iso = Country::getIsoById($country_id);
        
        if ((empty($vat) && Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required') || (empty($vat) && Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY') == 1 && $company)) {
            $this->message = $this->l('VAT number is empty.', 'ValidationEngine');
            self::$status[] = $this->l('VAT number empty', 'ValidationEngine');
            self::$valid = false;
            return false;    
        }
        if (!empty($vat)) {
            // Brexit disabled
            if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 0) {
                if ($country_iso == 'GB' && $vat_iso_code == $country_iso) {
                    $this->message = sprintf($this->l('The VAT number %s belongs to the United Kingdom and this shop does not have validation activated for VAT numbers from this country.', 'ValidationEngine'), $vat);
                    self::$status[] = $this->l('The VAT number belongs to the United Kingdom and Brexit option is disabled.', 'ValidationEngine');
                    self::$valid = false;
                    return false;
                }
                else if ($this->eu_prefix[$country_iso] != $vat_iso_code) {
                        $this->message = $this->l('VAT number format is not correct. Should contain country iso code at beginning.', 'ValidationEngine');
                        self::$status[] = $this->l('VAT number format invalid', 'ValidationEngine');
                        self::$valid = false;
                        return false;
                }
            }
            // Brexit enabled
            else if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1) {
                if (($country_iso == 'GB' && $country_iso != $vat_iso_code && $this->eu_prefix[$country_iso] != $vat_iso_code) || ($country_iso != 'GB' && $this->eu_prefix[$country_iso] != $vat_iso_code)) {
                    $this->message = $this->l('VAT number format is not correct. Should contain country iso code at beginning.', 'ValidationEngine');
                    self::$status[] = $this->l('VAT number format invalid', 'ValidationEngine');
                    self::$valid = false;
                    return false; 
                }
            }
            if (!$this->ISOCodeValidation($vat_number, $vat_iso_code)) {
                self::$status[] = $this->l('VAT number format invalid', 'ValidationEngine');
                self::$valid = false;
                return false;
            }
        }
        
        // Format validation success
        self::$status[] = $this->l('Format validation', 'ValidationEngine');
        self::$valid = true;
        return true;        
    }

    /**
     * ValidationEngine::basicFormatValidation()
     * Basic format validation process
     * @param mixed $vat
     * @return
     */
    public function basicFormatValidation($vat)
    { 
        //Put it in uppercase
        $vat = Tools::strtoupper($vat);
        // VAT iso code
        $vat_iso_code = Tools::substr($vat, 0, 2);
        // Separate 2 parts of number
        $vat_number = Tools::substr($vat, 2);
     
        if (empty($vat)) {
            $this->message = $this->l('VAT number is empty.', 'ValidationEngine');
            return false;    
        }
        if (!$this->ISOCodeValidation($vat_number, $vat_iso_code)) {
            $this->message = $this->l('VAT number format invalid', 'ValidationEngine');
            return false;
        }
       
        // Format validation success
        return true;        
    }
    
    /**
     * ValidationEngine::ISOCodeValidation()
     * VAT ISO code validation
     * @param mixed $vat_number
     * @param mixed $vat_iso_code
     * @return
     */
    public function ISOCodeValidation($vat_number, $vat_iso_code)
    {
        $valid = true;
        switch ($vat_iso_code) {
            // Austria
            case 'AT':
                if (Tools::strlen($vat_number) != 9 || Tools::substr($vat_number, 0, 1) != 'U' || !preg_match('/^\d+$/', Tools::substr($vat_number, 1))) {
                    $valid = false;
                } 
                break;
            // Belgium
            case 'BE':
                if (Tools::strlen($vat_number) != 10 || !in_array(Tools::substr($vat_number, 0, 1), array(0,1)) || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Bulgaria
            case 'BG':
                if (!in_array(Tools::strlen($vat_number),  array(9, 10)) || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Cyprus
            case 'CY':
                if (Tools::strlen($vat_number) != 9 || !self::isInt(Tools::substr($vat_number, 1, -1)) || !Validate::isString(Tools::substr($vat_number, -1, 1))) {
                    $valid = false;
                } 
                break;
            // Czech Republic
            case 'CZ':
                if (!in_array(Tools::strlen($vat_number),  array(8, 9, 10)) || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Germany
            case 'DE':
                if (Tools::strlen($vat_number) != 9 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break; 
            // Denmark
            case 'DK':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;  
            // Estonia
            case 'EE':
                if (Tools::strlen($vat_number) != 9 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Greece
            case 'EL':
                if (Tools::strlen($vat_number) != 9 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Spain
            case 'ES':
                if (!$this->checkSpanishVATStructure($vat_number)) {
                    $valid = false;
                } 
                break; 
            // Finland
            case 'FI':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Finland
            case 'FI':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // France
            case 'FR':
                if (Tools::strlen($vat_number) != 11 || !self::isInt(Tools::substr($vat_number, 2))) {
                    $valid = false;
                } 
                break;
            // UK
            case 'GB':
                if ((Tools::strlen($vat_number) != 12 && Tools::strlen($vat_number) != 9) || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Croatia
            case 'HR':
                if (Tools::strlen($vat_number) != 11 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Hungary
            case 'HU':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Ireland (Could be improved the format validation for Ireland)
            case 'IE':
                if (!in_array(Tools::strlen($vat_number),  array(8, 9))) {
                    $valid = false;
                } 
                break;
            // Italy
            case 'IT':
                if (Tools::strlen($vat_number) != 11 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break; 
            // Lithuania
            case 'LT':
                if (!in_array(Tools::strlen($vat_number),  array(9, 12)) || Tools::substr($vat_number, 0, 1) != 1 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Luxembourg
            case 'LU':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Latvia
            case 'LV':
                if (Tools::strlen($vat_number) != 11 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Malta
            case 'MT':
                if (Tools::strlen($vat_number) != 8 || Tools::substr($vat_number, 0, 1) != 1 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Netherlands  
            case 'NL':
                if (Tools::strlen($vat_number) != 12 || Tools::substr($vat_number, 9, 1) != 'B' || !self::isInt(Tools::substr($vat_number, -2))) {
                    $valid = false;
                } 
                break;
            // Poland  
            case 'PL':
                if (Tools::strlen($vat_number) != 10 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Portugal 
            case 'PT':
                if (Tools::strlen($vat_number) != 9 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Romania
            case 'RO':
                if ((Tools::strlen($vat_number) < 2 && Tools::strlen($vat_number) > 10) || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Sweden
            case 'SE':
                if (Tools::strlen($vat_number) != 12 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Slovenia
            case 'SI':
                if (Tools::strlen($vat_number) != 8 || !self::isInt($vat_number)) {
                    $valid = false;
                } 
                break;
            // Slovakia
            case 'SK':
                if (Tools::strlen($vat_number) != 10 || !self::isInt($vat_number) || ($vat_number % 11 != 0)) {
                    $valid = false;
                } 
                break;
            // North of Ireland
            case 'XI':
                if (!in_array(Tools::strlen($vat_number),  array(5, 9, 12)) || (in_array(Tools::strlen($vat_number),  array(9, 12)) && !self::isInt($vat_number))) {
                    $valid = false;
                } 
                break;
        } 
        if ($valid === false) {
            $this->message = $this->l('VAT number format is not valid.', 'ValidationEngine');
        }
        return $valid; 
    }
    
    /**
     * ValidationEngine::checkSpanishVATStructure()
     * Checks spanish VAT number structure
     * @param mixed $vat_number
     * @return
     */
    public function checkSpanishVATStructure($vat_number)
    {
        $dni_chars = array('t', 'r', 'w', 'a', 'g', 'm', 'y', 'f', 'p', 'd', 'x', 'b', 'n', 'j', 'z', 's', 'q', 'v', 'h', 'l', 'c', 'k', 'e');
        $nie_chars = array('x', 'y', 'z');  
        $cif_chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'n', 'p', 'q', 'r', 's', 'u', 'v', 'w');
        $control_digits = array('j', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i');
        $split_doc = str_split($vat_number);// split number in array.                              
        
        if (Tools::strlen($vat_number) == 9) {
            // Checks if DNI structure
            if (is_numeric(Tools::substr($vat_number, 0, -1)) && is_string($split_doc[8]) && in_array(Tools::strtolower($split_doc[8]), $dni_chars)) {
                return true;    
            }
            // Checks if NIE structure
            elseif (is_string($split_doc[0]) && in_array(Tools::strtolower($split_doc[0]), $nie_chars) && is_string($split_doc[8]) && in_array(Tools::strtolower($split_doc[8]), $dni_chars)) {
                return true;    
            }
            // Checks if CIF structure
            elseif (is_string($split_doc[0]) && in_array(Tools::strtolower($split_doc[0]), $cif_chars) && is_numeric(Tools::substr($vat_number, 1, -1)) && ((is_string($split_doc[8]) && in_array(Tools::strtolower($split_doc[8]), $control_digits)) || is_numeric($split_doc[8]))) {
                return true;    
            }
        }
        return false;  
    }
    
    /**
     * ValidationEngine::checkNoTax()
     * Checks VAT exemption
     * @param mixed $id_customer
     * @param mixed $id_address
     * @return
     */
    public static function checkNoTax($id_customer = null, $id_address = null, $country_id = null)
    {
        $country_iso = '';
        self::$notax_customer = false;
        
        if ($id_customer === null && $id_address === null && $country_id === null) {
            return false;
        }
        
        // Checks local country for tax
        if ($country_id && $country_id == Configuration::get('ADVANCEDVATMANAGER_LOCAL_COUNTRY')) { 
            return false;
        }
        
        // North Ireland code iso 
        $north_ireland_state = State::getIdByIso('GB-NIR');
        
        if ($id_address !== null) {
            $address = new Address($id_address);
        }
        
        if ($country_id !== null) {
            $country_iso = Country::getIsoById($country_id);
        }
        
        // Checks if customer exemtpion is set for this customer
        if (CustomersExemption::checkCustomerExemption($id_customer)) {
            self::$notax_customer = true;
            return true; 
        }
        else if (CustomersExemption::checkCustomerForceVATCollection($id_customer)) {
            self::$notax_customer = false;
            return false; 
        }
        
        // Checks group exemption
        if (self::checkGroupExemption($id_customer)) {
            self::$notax_customer = true;
            return true;    
        }
        
        // brexit mode for VAT exemtpion when cart price is equal or below to 135 pounds and country address is UK
        if (Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED') == 1 && $country_iso == 'GB') {
            // Check North Ireland state as excluded in Brexit.
            if (isset($address->id_state) && $address->id_state != $north_ireland_state) {
                self::$brexit_customer = true;
                $module = Module::getInstanceByName('advancedvatmanager');
                // More than 135GBP no tax in any case
                if (Context::getContext()->cookie->__isset('cart_total') && (float)Context::getContext()->cookie->__get('cart_total') > $module->getGBPcurrencyAmount()) {
                    self::$notax_customer = true;  
                    return true;
                }
                // Less or equal than 135GBP
                else {
                    // If company with UK VAT number valid, then no tax
                    if (CustomersVAT::checkCustomerVATValid($id_customer, $id_address) && Configuration::get('ADVANCEDVATMANAGER_BREXIT_VATEXEMPT_LESSTHAN135GBP') == 1) {
                        self::$notax_customer = true; 
                        self::$customer_with_vat_valid = true; 
                        return true;        
                    }
                    return false;
                }
            }
        }
        
        if (Configuration::get('ADVANCEDVATMANAGER_VATEXEMPTION')) {
            if ($id_address !== null) {
                if(CustomersVAT::checkCustomerVATValid($id_customer, $id_address)) {
                    self::$notax_customer = true;
                    self::$customer_with_vat_valid = true;   
                    return true;
                } 
            }
        }
        return false;
    }
    
    /**
     * ValidationEngine::checkGroupExemption()
     * Checks customer group exemptions
     * @param int $id_customer
     * @return
     */
    public static function checkGroupExemption($id_customer)
    {
        $group_exemption = explode(',',Configuration::get('ADVANCEDVATMANAGER_GROUP_VATEXEMPTION'));
        if (!empty($group_exemption)) {
            foreach ($group_exemption as $group) {
                if (in_array($group, Customer::getGroupsStatic($id_customer))) {
                    return true;
                }    
            }    
        }
        return false;   
    }
    
    /**
     * ValidationEngine::removeCustomerGroup()
     * Remove customer from ID group
     * @param int $customer_id
     * @param int $group_id
     * @return
     */
    public static function removeCustomerGroup($customer_id, $group_id)
    {
        return Db::getInstance()->delete('customer_group', 'id_customer = '.(int)$customer_id.' AND id_group = '.(int)$group_id, 0 , false);
    }
    
    /**
     * ValidationEngine::setDefaultCustomerGroup()
     * Remove customer from ID group
     * @param int $customer_id
     * @param int $default_group
     * @return
     */
    public static function setDefaultCustomerGroup($customer_id, $default_group)
    {
        return Db::getInstance()->update('customer', array('id_default_group' => (int)$default_group), 'id_customer = '.(int)$customer_id); 
    }
    
    /**
     * ValidationEngine::manageCustomerGroups()
     * Manage customer groups depends on valid or not valid VAT number.
     * @param int $id_country
     * @param int $id_customer
     * @param bool $vat_valid
     * @return $output_msg (Displays message in Cron task and Customer VAT scanner)
     */
    public static function manageCustomerGroups($id_country, $id_customer, $vat_valid = false)
    {
        $group_assignation_bycountry = Configuration::get('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$id_country);
        $customer_vat_countries = CustomersVAT::getCountryAddressWithValidVAT($id_customer);
        $customer = new Customer($id_customer);
        $customer_groups = $customer->getGroups();
        $assigned_groups = array();
        $output_msg = '';
        
        if (!empty($customer_vat_countries)) {
            // All groups by country set in this module by customer except the current country
            foreach ($customer_vat_countries as $country) {
                if ($country['id_country'] == $id_country) {
                    continue;    
                }
                if (configuration::get('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country['id_country'])) {
                    $assigned_groups[] = configuration::get('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$country['id_country']);    
                } 
            }
        }
        if (!empty($group_assignation_bycountry)) {
            if ($vat_valid) {
                if (Configuration::get('ADVANCEDVATMANAGER_DELETE_PREVIOUS_GROUPS')) {
                    // Removes customer previous groups and only add the group by country selected in this module.
                    $customer->cleanGroups();
                    $customer->addGroups(array($group_assignation_bycountry));
                    // Set by default Group
                    self::setDefaultCustomerGroup($id_customer, (int)$group_assignation_bycountry);
                }
                else {
                    // Add to group by country
                    $customer->addGroups(array($group_assignation_bycountry));  
                }                
                $output_msg = sprintf(Translate::getModuleTranslation('advancedvatmanager','The client with ID#%s has been assigned to the group %s','ValidationEngine'),$id_customer, self::getGroupAssignation($group_assignation_bycountry));  
                
                // Set as default group.
                if (Configuration::get('ADVANCEDVATMANAGER_DEFAULT_GROUP_ASSIGNATION')) {
                    self::setDefaultCustomerGroup($id_customer, (int)$group_assignation_bycountry);// Set default customer group.
                    $output_msg .= sprintf(Translate::getModuleTranslation('advancedvatmanager','The client with ID#%s has been assigned to the default group %s','ValidationEngine'),$id_customer, self::getGroupAssignation($group_assignation_bycountry));  
                }
            }
            else {
                // Remove from this group
                if (!CustomersVAT::checkCustomerHasVATValidByCountry($id_customer, $id_country)) {                   
                    // Assign Prestashop customer default group if it is empty
                    if (count($customer_groups) == 1 && $customer_groups == array($group_assignation_bycountry)) {
                         $customer->addGroups(array((int)Configuration::get('PS_CUSTOMER_GROUP')));
                         self::setDefaultCustomerGroup($id_customer, (int)Configuration::get('PS_CUSTOMER_GROUP'));
                         self::removeCustomerGroup($id_customer, $group_assignation_bycountry);    
                    }
                    else {
                        self::removeCustomerGroup($id_customer, $group_assignation_bycountry);
                        if (!empty($assigned_groups)) {
                            // Set one default group by country configured in this module by customer
                            self::setDefaultCustomerGroup($id_customer, (int)max($assigned_groups));
                            $output_msg .= sprintf(Translate::getModuleTranslation('advancedvatmanager', 'The client with ID#%s has been assigned to the default group %s','ValidationEngine'),$id_customer, self::getGroupAssignation((int)$assigned_groups[0]));   
                        }
                        else {
                            if ($pos = array_search($group_assignation_bycountry, $customer_groups)) {
                                unset($customer_groups[$pos]);    
                            }
                            self::setDefaultCustomerGroup($id_customer, (int)max($customer_groups));  
                        }     
                    }
                }    
            } 
            if (empty($output_msg)) {
                $output_msg = sprintf(Translate::getModuleTranslation('advancedvatmanager', 'The client with ID#%s has not been assigned to any customer groups','ValidationEngine'),$id_customer);       
            }
            return $output_msg; 
        }
    }
    
    /**
     * ValidationEngine::getGroupAssignation()
     * 
     * @return
     */
    public static function getGroupAssignation($group_assigned)
    {
        $groups = array();
        // Get Selected customer group
        foreach (Group::getGroups(Context::getContext()->language->id) as $group) {            
            if ($group['id_group'] == $group_assigned) {
                $groups[] = $group['name'];
            }
        }
        return implode(',',$groups);
    }
    
    /**
     * ValidationEngine::changeCustomerGroupsByAddress()
     * Change customer default group depends on address country selected during checkout.
     * @param int $id_address
     * @param int $id_customer
     * @return
     */
    public static function changeCustomerGroupsByAddress($id_address, $id_customer)
    {
        $address = Address::getCountryAndState($id_address);
        $group_assignation_bycountry = configuration::get('ADVANCEDVATMANAGER_GROUPS_ASSIGNATION_COUNTRY_'.$address['id_country']);
        $customer_groups = Customer::getGroupsStatic($id_customer);
        // Checks if the customer belongs to this group to assign as default.
        if ($group_assignation_bycountry && in_array($group_assignation_bycountry, $customer_groups)) {
            if (CustomersVAT::checkCustomerVATValid($id_customer, $id_address)) {
                return self::setDefaultCustomerGroup($id_customer, $group_assignation_bycountry);// Set default customer group.
            } 
        }
        return self::setDefaultCustomerGroup($id_customer, (int)max($customer_groups));
    }  
    
    /**
     * ValidationEngine::isInt()
     * Checks customer group exemptions
     * @param mixed $number
     * @return
     */
    public static function isInt($number) {
        return (bool)preg_match('/^\d+$/', $number);
    }
    
    /**
     * ValidationEngine::cleanCache()
     * Clean all Prestashop Cache.
     * @return
     */
    public static function cleanCache()
    {
        // Clear cache.
        if (method_exists('Tools', 'clearAllCache')) {
            Tools::clearAllCache();    
        }
        else if (method_exists('Tools', 'clearSmartyCache')) {
            Tools::clearSmartyCache();    
        }
    }
    
    /**
     * ValidationEngine::l()
     * Fix language translations
     * @return
     */
    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ( _PS_VERSION_ >= '1.7') {
            return Translate::getModuleTranslation('advancedvatmanager',$string, $class);
        } else {
            return parent::l($string, $class, $addslashes, $htmlentities);
        }
    } 
}