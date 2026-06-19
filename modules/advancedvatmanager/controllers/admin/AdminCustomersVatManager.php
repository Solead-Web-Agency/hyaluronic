<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/ValidationEngine.php');
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersVAT.php');

class AdminCustomersVatManagerController extends ModuleAdminController
{
    private $message = array();
    private $id_address;

    /**
     * AdminCustomersVatManagerController::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->show_toolbar = true;
        $this->module = Module::getInstanceByName('advancedvatmanager');
        $this->table = 'advancedvatmanager_customers';
        $this->identifier = 'id_advancedvatmanager_customers';
        $this->className = 'CustomersVAT';
        $this->controller_name = 'AdminCustomersVatManager';
        $this->allow_export = true;
        $this->lang = false;
        $this->delete = true;
        $this->_use_found_rows = true;
        $this->list_simple_header = false;
        $this->requiredDatabase = true;
        $this->explicitSelect = true;
        
        parent::__construct();
        
        $this->_orderWay = 'DESC';
        $this->addRowAction('edit');
        $this->addRowAction('viewCustomer');
        $this->addRowAction('checkVAT');
        $this->addRowAction('validateVATManual');
        $this->addRowAction('validateAPI');
        $this->addRowAction('sendValidationEmail');
        $this->addRowAction('deleteVAT');
        $this->addRowAction('delete');
        
        $this->bulk_actions = array(
                    'validateManually' => array('text' => $this->l('Validate VAT Manually','AdminCustomersVatManager'), 'icon' => 'fas fa-user-check','confirm' => $this->l('Would you like to validate VAT number for the selected customers?','AdminCustomersVatManager')),
                    'validateAPI' => array('text' => $this->l('Validate VAT with VIES/GOV.UK API system','AdminCustomersVatManager'), 'icon' => 'fas fa-user-cog','confirm' => $this->l('Would you like to validate VAT number for the selected customers?','AdminCustomersVatManager')),
                    'sendValidationEmail' => array('text' => $this->l('Send VAT validation email request','AdminCustomersVatManager'), 'icon' => 'far fa-envelope','confirm' => $this->l('Would you like to send email to request validate VAT number for the selected customers?','AdminCustomersVatManager')),
                    'deleteVAT' => array('text' => $this->l('Delete VAT','AdminCustomersVatManager'), 'icon' => 'fas fa-user-times','confirm' => $this->l('Would you like to delete VAT number for the selected items?','AdminCustomersVatManager')),
                    'delete' => array('text' => $this->l('Delete','AdminCustomersVatManager'), 'icon' => 'far fa-trash-alt','confirm' => $this->l('Would you like to delete selected items from the list?','AdminCustomersVatManager'))
        );
        
        $this->_select = 'CONCAT(LEFT(ad.`firstname`,1),\'.\',ad.`lastname`) `customer_name`,c.`email`, CONCAT(ad.`address1`,\' \', ad.`address2`) `address`, ad.`company`, cl.`name` as country, co.`iso_code`, gl.`name` as group_name, c.`id_shop`';
        
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
		LEFT JOIN `' . _DB_PREFIX_ . 'address` ad ON (ad.`id_customer` = c.id_customer AND ad.`id_address` = a.`id_address`)
        LEFT JOIN `' . _DB_PREFIX_ . 'country` co ON (co.`id_country` = ad.`id_country`)
        LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (co.`id_country` = cl.`id_country` AND cl.`id_lang` ='.(int)$this->context->language->id.')
        LEFT JOIN `' . _DB_PREFIX_ . 'group_lang` gl ON (gl.`id_group` = c.`id_default_group` AND gl.`id_lang` ='.(int)$this->context->language->id.')';
        
        $this->_where = 'AND ad.`deleted` = 0 AND ad.`active` = 1'.Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');

        $shops_name = array();
        foreach (Shop::getShops() as $shop) {
            $shops_name[$shop['id_shop']] = $shop['name'];
        }
        
        $company_validation = array(
            '0' => $this->l('Invalid','AdminCustomersVatManager'),
            '1' => $this->l('Valid','AdminCustomersVatManager'), 
            '2' => $this->l('Not validated yet','AdminCustomersVatManager')
        );
        
        $this->fields_list = array(
            'id_advancedvatmanager_customers' => array('title' => $this->l('ID','AdminCustomersVatManager'), 'type' => 'text', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'id_customer' => array('title' => $this->l('ID Customer','AdminCustomersVatManager'), 'filter_key' => 'a!id_customer', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'id_address' => array('title' => $this->l('ID Address','AdminCustomersVatManager'), 'filter_key' => 'a!id_address', 'align' => 'text-center', 'class' => 'fixed-width-xs', 'callback' => 'getIdAddress'),
            'customer_name' => array('title' => $this->l('Name','AdminCustomersVatManager'), 'type' => 'text', 'filter_key' => 'ad!lastname', 'align' => 'text-center'),
            'id_shop' => array('title' => $this->l('Shop','AdminCustomersVatManager'), 'type' => 'text','callback' => 'getShop', 'align' => 'text-center', 'filter_type' => 'int', 'filter_key' => 'c!id_shop', 'list' => $shops_name,'type' => 'select',),
            'email' => array('title' => $this->l('Email','AdminCustomersVatManager'), 'type' => 'text', 'align' => 'text-center', 'filter_key' => 'c!email'),
            'company' => array('title' => $this->l('Company','AdminCustomersVatManager'), 'type' => 'text', 'align' => 'text-center', 'filter_key' => 'ad!company'),
            'address' => array('title' => $this->l('Address','AdminCustomersVatManager'), 'type' => 'text', 'filter_key' => 'ad!address1', 'align' => 'text-center'),
            'country' => array('title' => $this->l('Country','AdminCustomersVatManager'), 'type' => 'text', 'filter_key' => 'cl!name', 'align' => 'text-center'),
            'iso_code' => array('title' => $this->l('Country ISO code','AdminCustomersVatManager'), 'type' => 'text', 'filter_key' => 'co!iso_code', 'align' => 'text-center'),
            'group_name' => array('title' => $this->l('Default group','AdminCustomersVatManager'), 'type' => 'text', 'filter_key' => 'gl!name', 'align' => 'text-center'),
            'vat' => array('title' => $this->l('VAT number','AdminCustomersVatManager'), 'type' => 'text', 'callback' => 'vatText', 'align' => 'text-center'),
            'validated' => array('title' => $this->l('VAT Validation', 'AdminCustomersVatManager'), 'align' => 'center','type' => 'bool', 'filter_key' => 'a!validated', 'callback' => 'showValidateIcons', 'class' => 'fixed-width-xs',),
            'validated_company' => array('title' => $this->l('Company validation', 'AdminCustomersVatManager'), 'align' => 'center','list' => $company_validation,'type' => 'select', 'filter_key' => 'a!validated_company', 'callback' => 'showValidateCompanyIcons', 'class' => 'fixed-width-xs',),
            'system_check' => array('title' => $this->l('System check', 'AdminCustomersVatManager'), 'align' => 'center','type' => 'bool','filter_key' => 'a!system_check', 'callback' => 'showValidatesystemIcons', 'class' => 'fixed-width-xs',),
            'status' => array('title' => $this->l('Status','AdminCustomersVatManager'), 'type' => 'text', 'align' => 'center', 'callback' => 'showStatus'),
            'date_upd' => array('title' => $this->l('Date updated','AdminCustomersVatManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_upd'),
            'date_add' => array('title' => $this->l('Date added','AdminCustomersVatManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_add')
        );
    }
    
    /**
     * AdminCustomersVatManagerController::getShop()
     * 
     * @param mixed $value
     * @return
     */
    public function getShop($value)
    {
        return Shop::getShop($value)['name'];
    } 
    
    public function getIdAddress($value)
    {
        $this->id_address = $value;
        return $value;    
    }
       
    /**
     * AdminCustomersVatManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showStatus($value)
    {
        return '<i style="font-size:20px;color:#25b9d7;" class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="'.$value.'"></i>';
    }
    
    /**
     * AdminCustomersVatManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showValidateIcons($value)
    {
        if ($value == 1) {
            $content = '<i style="font-size:20px;color:#32db1d;" class="fal fa-badge-percent" data-toggle="tooltip" data-placement="top" title="'.$this->l('VAT validated', 'AdminCustomersVatManager').'"></i>';
            if (CustomersVAT::checkVATWithSystemFails($this->id_address)) {
                $content .= '<i style="font-size:13px;color:#fdc700;position:relative;right:2px;" class="fal fa-exclamation-triangle" data-toggle="tooltip" data-placement="top" title="'.$this->l('VAT number validated by skipping validation when the API system failed. Repeat API validation is required.', 'AdminCustomersVatManager').'"></i>';
            }  
        }
        else if ($value == 0) {
            $content ='<i style="font-size:20px;color:#f92727;" class="fal fa-badge-percent" data-toggle="tooltip" data-placement="top" title="'.$this->l('VAT invalid', 'AdminCustomersVatManager').'"></i>';     
        }
        return $content;
    }
    
    /**
     * AdminCustomersVatManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showValidateCompanyIcons($value)
    {
        if ($value == 1) {
            $text = $this->l('Company name validated', 'AdminCustomersVatManager');
            return '<i style="font-size:20px;color:#32db1d;" class="fal fa-briefcase" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';   
        }
        else if ($value == 2) {
            $text = $this->l('Company name not checked', 'AdminCustomersVatManager');
            return '<i style="font-size:20px;color:#000000;" class="fal fa-briefcase" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';    
        }
        else if ($value == 0) {
            $text = $this->l('Company name invalid', 'AdminCustomersVatManager');
            return '<i style="font-size:20px;color:#f92727;" class="fal fa-briefcase" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';    
        }
    }
    
    /**
     * AdminCustomersVatManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showValidateSystemIcons($value)
    {
        if ($value == 1) {
            $text = $this->l('API request processed successfully', 'AdminCustomersVatManager');
            return '<i style="font-size:20px;color:#32db1d;" class="fas fa-server" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';   
        }
        else if ($value == 0 && $value !== null) {
            $text = $this->l('API request processing error', 'AdminCustomersVatManager');
            return '<i style="font-size:20px;color:#f92727;" class="fas fa-server" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';    
        }
    }
    
    /**
     * AdminCustomersVatManagerController::vatText()
     * 
     * @param mixed $value
     * @return
     */
    public function vatText($value)
    {
        return '<span style="font-size:13px;color:#00a2ef;font-weight:600;">'.$value.'</span>';
    }
   
    /**
     * AdminCustomersVatManagerController::initContent()
     * 
     * @return
     */
    public function initContent()
    {
        parent::initContent();           
    }
    
    /**
     * AdminCustomersVatManagerController::setMedia()
     * 
     * @param bool $isNewTheme
     * @return
     */
    public function setMedia($isNewTheme = true)
    {
        parent::setMedia(); 
        Media::addJsDef(array(
            'ajax_url_avm_scanner' => $this->context->link->getAdminLink('AdminCustomersVatManager'),
            'scanning' => $this->l('Processing', 'AdminCustomersVatManager'),
            'get_total_addresses' => $this->l('Getting addresses from database', 'AdminCustomersVatManager'),
            'checking_vat_numbers' => $this->l('Verifying VAT numbers and adding to the Customer VAT list', 'AdminCustomersVatManager'),
            'remaining_addresses' => $this->l('Addresses remaining', 'AdminCustomersVatManager'),
            'addresses_found' => $this->l('Addresses found', 'AdminCustomersVatManager'),
            'empty_addresses' => $this->l('There is not addresses saved in database', 'AdminCustomersVatManager'),
            'cancel_process' => $this->l('Scan has been stopped!', 'AdminCustomersVatManager'),
            'process_finished_success' => $this->l('Process finished successfully!', 'AdminCustomersVatManager'),
            'process_finished_error' => $this->l('Process finished with errors!', 'AdminCustomersVatManager'),
            'deleting_empty' => $this->l('Deleting addresses with empty VAT number', 'AdminCustomersVatManager'),
            'deleting_duplicated' => $this->l('Deleting addresses with duplicated VAT number', 'AdminCustomersVatManager'),
            'deleting_invalid' => $this->l('Deleting addresses with invalid VAT number', 'AdminCustomersVatManager'),
            'assign_customerGroups' => $this->l('Assigning customers to groups for valid VAT numbers'),
            'create_file' => $this->l('Generating Customer VAT list file', 'AdminCustomersVatManager'),
            'reset_tablelist' => $this->l('Deleting old list data', 'AdminCustomersVatManager'),
            'send_email' => $this->l('Sending email to customers', 'AdminCustomersVatManager'),
            'allow_duplicated' => (bool)Configuration::get('ADVANCEDVATMANAGER_ALLOWDUPLICATED'),
            'danger' => $this->l('Error'),
            'warning' => $this->l('Warning'),
            'success' => $this->l('Success'),
            'info' => $this->l('Information'),
            'company_validation' => (bool)Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION'),
        ));
        
        //Fontawesome
        $this->addCSS('https://pro.fontawesome.com/releases/v5.15.4/css/all.css'); 
                
        // Slick modal
        $this->addCSS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/slickmodal/css/slickmodal.min.css');
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/slickmodal/js/jquery.slickmodal.min.js');
                
        // Progress circle
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/circle-progress/js/circle-progress.min.js');
                 
        // Timer
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/easytimer/easytimer.min.js');                       
        
        // Bootstrap switches
        $this->addCSS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/bootstrap-switch/css/bootstrap-switch.min.css');
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/bootstrap-switch/js/bootstrap-switch.min.js');
        
        $this->addCSS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/css/admin/AdminCustomersVatManager/vat_manager.css');
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/js/admin/AdminCustomersVatManager/vat_manager.js');
    }

    /**
     * AdminCustomersVatManagerController::initToolbar()
     * 
     * @return
     */
    public function initToolbar()
    {
        parent::initToolbar();

        $this->toolbar_title = $this->meta_title;              
        
        // Remove add new button
         unset($this->toolbar_btn['new']);
    }

    /**
     * AdminCustomersVatManagerController::initPageHeaderToolbar()
     * 
     * @return
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }

    /**
     * AdminCustomersVatManagerController::initProcess()
     * 
     * @return
     */
    public function initProcess()
    {
        parent::initProcess();
    }
    
    /**
     * AdminCustomersVatManagerController::renderForm()
     * 
     * @return
     */
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array('title' => $this->l('Edit and Validate VAT number', 'AdminCustomersVatManager'),
                    'icon' => 'fas fa-user-edit'),
            'warning' =>  $this->l('If you edit VAT number and validated option here, the changes will be saved without VAT validation process.', 'AdminCustomersVatManager'),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('VAT number', 'AdminCustomersVatManager'),
                    'name' => 'vat',
                    'col' => 3,
                    'required' => true),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Validate VAT', 'AdminCustomersVatManager'),
                    'name' => 'validated',
                    'desc' => $this->l('Select to validate VAT number manually.', 'AdminCustomersVatManager'),
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Validated', 'AdminCustomersVatManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Invalid', 'AdminCustomersVatManager')
                        )
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Validate Company', 'AdminCustomersVatManager'),
                    'name' => 'validated_company',
                    'desc' => $this->l('Select to validate company name manually.', 'AdminCustomersVatManager'),
                    'required' => false,
                    'options' => array(
                            'query' => array(
                                array('id' => 0, 'name' => $this->l('Invalid', 'AdminCustomersVatManager')),
                                array('id' => 1, 'name' => $this->l('Validated', 'AdminCustomersVatManager')),
                                array('id' => 2, 'name' => $this->l('Not validated yet', 'AdminCustomersVatManager')),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                    )
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Status', 'AdminCustomersVatManager'),
                    'name' => 'status',
                    'desc' => $this->l('Select to modify the status manually.', 'AdminCustomersVatManager'),
                ),
            ),
            'submit' => array('title' => $this->l('Save', 'AdminCustomersVatManager')
            )
        );
        return parent::renderForm();
    }

    /**
     * AdminCustomersVatManagerController::renderList()
     * 
     * @return
     */
    public function renderList()
    {   
        $helper = new HelperList();
        $helper->module = $this;
        $this->toolbar_title = $this->l('Customer VAT number list','AdminCustomersVatManager'); // title
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/scan_panel.tpl');
        $tpl_statistics = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/vat_statistics.tpl');
        $tpl_popup = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/checkCustomerVAT.tpl');
        
        $countries_ID = json_decode(Configuration::get('ADVANCEDVATMANAGER_COUNTRY'));
        if (!empty($countries_ID)) {
            $countries = array();
            foreach ($countries_ID as $country_id) {
                $countries[] = Country::getNameById($this->context->language->id, $country_id);       
            }
        }
        
        $tpl_statistics->assign(array(
            'total_vat_valid' => CustomersVAT::getTotalWithVATValid(),
            'total_vat_invalid' => CustomersVAT::getTotalWithVATInvalid(),
            'total_company_valid' => CustomersVAT::getTotalWithCompanyValid(),
            'total_company_invalid' => CustomersVAT::getTotalWithCompanyInvalid(),
        ));
		$tpl->assign(array(
                'href' => $this->context->link->getAdminLink('AdminCustomersVatManager').'&action=downloadList&token='.$this->token,
                'countries' => implode(',', $countries),
                'field_condition' => Configuration::get('ADVANCEDVATMANAGER_VATFIELD') == 'required'?$this->l('Required','AdminCustomersVatManager'):$this->l('Optional','AdminCustomersVatManager'),
                'show_with_company' => (bool)Configuration::get('ADVANCEDVATMANAGER_DISPLAY_WITH_COMPANY'),
                'allow_duplicated' => (bool)Configuration::get('ADVANCEDVATMANAGER_ALLOWDUPLICATED'),
                'company_validation' => (bool)Configuration::get('ADVANCEDVATMANAGER_COMPANY_VALIDATION'),
		));
        return $tpl_statistics->fetch().parent::renderList().$tpl_popup->fetch().$tpl->fetch();
    }

    /**
     * AdminCustomersVatManagerController::renderView()
     * 
     * @return
     */
    public function renderView()
    {
        return parent::renderView();  
    }

    /**
     * AdminCustomersVatManagerController::postProcess()
     * 
     * @return
     */
    public function postProcess()
    {
        parent::postProcess();
        
        $id = Tools::getValue('id_advancedvatmanager_customers');
        // Download excel file
        if (Tools::getValue('action') == 'downloadList') {
            $this->downloadFile();    
        }
        // Validate VAT manually
        else if (Tools::getValue('action') == 'validateVatManually') {
            $this->updateVatProcess($id, 1, $this->l('Manual validation', 'AdminCustomersVatManager'));
            CustomersVAT::updateVATAddress(CustomersVAT::getAddressIDbyID($id), Tools::getValue('vat')); 
            
            $this->confirmations[] =  sprintf($this->l('#%s - The VAT number has been validated', 'AdminCustomersVatManager'),$id);   
        }
        // Delete VAT
        else if (Tools::getValue('action') == 'deleteVAT') {
            CustomersVAT::deleteVATbyID($id);
            $this->confirmations[] = sprintf($this->l('#%s - The VAT number has been deleted', 'AdminCustomersVatManager'),$id);      
        }
        // Validate VAT via API system
        else if (Tools::getValue('action') == 'validateVatAPI') {
            $vat = CustomersVAT::getVATbyID($id);
            $vat_iso = Tools::substr(CustomersVAT::getVATbyID($id), 0, 2);
            $ve =  new ValidationEngine($vat);
            if ($vat_iso == 'GB' && Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED')) {
                if ($ve->validationForUKVAT()) {
                    $this->updateVatProcess($id, 1, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    $this->confirmations[] = sprintf($this->l('#%s - The VAT number %s has been validated', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat());    
                }
                else {
                    $this->updateVatProcess($id, 0, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    $this->errors[] = sprintf($this->l('#%s - The VAT number %s could not be valided [%s]', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat(),$ve->getMessage());       
                }
            }
            else {
                if ($ve->vatValidationViesOneWay()) {
                    $this->updateVatProcess($id, 1, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    $this->confirmations[] = sprintf($this->l('#%s - The VAT number %s has been validated', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat());    
                }
                else {
                    $this->updateVatProcess($id, 0, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                    $this->errors[] = sprintf($this->l('#%s - The VAT number %s could not be valided [%s]', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat(),$ve->getMessage());       
                }  
            } 
        }
        // Send validation email to customer
        else if (Tools::getValue('action') == 'sendValidationEmail') {
            $avm = new AdvancedVatManager();
            if ($avm->sendEmail(4, CustomersVAT::getCustomerAddress(self::getCustomerId($id), self::getAddressId($id)))) {
                $this->confirmations[] = sprintf($this->l('#%s - Email sent successfully!', 'AdminCustomersVatManager'),$id);
            }
            else {
                $this->errors[] = sprintf($this->l('#%s - An error occurred while sending the email!', 'AdminCustomersVatManager'),$id);
            }
        }
        // Update record
        if (Tools::getValue('submitAddadvancedvatmanager_customers')) {
            CustomersVAT::updateVATAddress(CustomersVAT::getAddressIDbyID($id), Tools::getValue('vat'));
            CustomersVAT::updateVATSystemCheckById($id, null);  
        }
    }
    
    /**
     * AdminCustomersVatManagerController::processBulkSendValidationEmail()
     * 
     * @return
     */
    public function processBulkSendValidationEmail()
    {
        if ($this->boxes) {
            foreach ($this->boxes as $id) {
                $customer = new Customer(self::getCustomerId($id));
                $avm = new AdvancedVatManager();
                if ($avm->sendEmail(4, $customer)) {
                    $this->confirmations[] = sprintf($this->l('#%s - Email sent successfully to %s!', 'AdminCustomersVatManager').'<br />',$id, $customer->email);   
                }
                else {
                    $this->errors[] = sprintf($this->l('#%s - An error occurred while sending the email to %s!', 'AdminCustomersVatManager').'<br />',$id, $customer->email);       
                }    
            }
        }
    }
    
    /**
     * AdminCustomersVatManagerController::processBulkValidateManually()
     * 
     * @return
     */
    public function processBulkValidateManually() 
    {
        if ($this->boxes) {
            foreach ($this->boxes as $id) {
                CustomersVAT::validateVATbyID($id, 1);
                $this->confirmations[] = sprintf($this->l('#%s - The VAT number has been validated', 'AdminCustomersVatManager').'<br />',$id);       
            }
        }
    }
    
    /**
     * AdminCustomersVatManagerController::processBulkDeleteVAT()
     * 
     * @return
     */
    public function processBulkDeleteVAT() 
    {
        if ($this->boxes) {
            foreach ($this->boxes as $id) {
                CustomersVAT::deleteVATbyID($id);
                $this->confirmations[] = sprintf($this->l('#%s - The VAT number has been deleted', 'AdminCustomersVatManager').'<br />',$id);        
            }
        }
    }
    
    /**
     * AdminCustomersVatManagerController::processBulkValidateVies()
     * 
     * @return
     */
    public function processBulkValidateAPI() 
    {
        if ($this->boxes) {
            foreach ($this->boxes as $id) {
                $vat = CustomersVAT::getVATbyID($id);
                $vat_iso = Tools::substr(CustomersVAT::getVATbyID($id), 0, 2);
                $ve =  new ValidationEngine($vat);
                if ($vat_iso == 'GB' && Configuration::get('ADVANCEDVATMANAGER_BREXIT_ENABLED')) {
                    if ($ve->validationForUKVAT()) {
                        $this->updateVatProcess($id, 1, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        $this->confirmations[] = sprintf($this->l('#%s - The VAT number %s has been validated', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat());    
                    }
                    else {
                        $this->updateVatProcess($id, 0, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        $this->errors[] = sprintf($this->l('#%s - The VAT number %s could not be valided [%s]', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat(),$ve->getMessage());       
                    }
                }
                else {
                    if ($ve->vatValidationViesOneWay()) {
                        $this->updateVatProcess($id, 1, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        $this->confirmations[] = sprintf($this->l('#%s - The VAT number %s has been validated', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat());    
                    }
                    else {
                        $this->updateVatProcess($id, 0, ValidationEngine::getStatus(), ValidationEngine::getSystemFail());
                        $this->errors[] = sprintf($this->l('#%s - The VAT number %s could not be valided [%s]', 'AdminCustomersVatManager').'<br />',$id,ValidationEngine::getVat(),$ve->getMessage());       
                    }
                }      
            }
        }
    }
    
    
    /**
     * AdminCustomersVatManagerController::processBulkDelete()
     * 
     * @return
     */
    public function processBulkDelete() 
    {
        parent::processBulkDelete();  
    }
    
    /**
     * AdminCustomersVatManagerController::updateVatProcess()
     * 
     * @param int $id
     * @param int $valid
     * @param mixed $status
     * @param int $system
     * @return
     */
    public function updateVatProcess($id, $valid, $status, $system = null)
    {
        CustomersVAT::validateVATbyID($id, $valid);
        CustomersVAT::updateVATStatusById($id, $status);
        CustomersVAT::updateVATSystemCheckById($id, $system);    
    }
    
    /**
     * AdminCustomersVatManagerController::displayViewCustomerLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayViewCustomerLink($token = null, $id)
    {
        $customer = self::getCustomerId($id);
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/view_customer.tpl');
		$tpl->assign(array(
				'href' => '?tab=AdminCustomers&id_customer='.(int)$customer.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers'),
				'action' => $this->l('View customer details', 'AdminCustomersVatManager'),
		));
	
		return $tpl->fetch();
    }
    
    /**
     * AdminCustomersVatManagerController::displayCheckVATLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayCheckVATLink($token = null, $id)
    {
        $vat_details = self::getVATDetails($id);
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/check_vat.tpl');
		$tpl->assign(array(
				'action' => $vat_details['country_iso'] != 'GB'?$this->l('Check VAT in VIES', 'AdminCustomersVatManager'):$this->l('Check VAT in GOV.UK', 'AdminCustomersVatManager'),
                'emptyVAT' => CustomersVAT::checkCustomerVATempty($id),
                'vat_details' => $vat_details,
                'id' => $id,
		));
	
		return $tpl->fetch();
    }
   
    /**
     * AdminCustomersVatManagerController::displayValidateVATManualLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayValidateVATManualLink($token = null, $id)
    {
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/validate_vat_manually.tpl');
		$tpl->assign(array(
				'href' => $this->context->link->getAdminLink('AdminCustomersVatManager').'&'.$this->identifier.'='.(int)$id.'&action=validateVatManually&token='.($token != null ? $token : $this->token),
                'emptyVAT' => CustomersVAT::checkCustomerVATempty($id),
				'action' => $this->l('Validate VAT mnually', 'AdminCustomersVatManager'),
		));
	
		return $tpl->fetch();
    }
    
    /**
     * AdminCustomersVatManagerController::displayValidateAPILink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayValidateAPILink($token = null, $id)
	{
	    $vat_details = self::getVATDetails($id);
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/validate_vat_api.tpl');
		$tpl->assign(array(
				'href' => $this->context->link->getAdminLink('AdminCustomersVatManager').'&'.$this->identifier.'='.(int)$id.'&action=validateVatAPI&token='.($token != null ? $token : $this->token),
                'emptyVAT' => CustomersVAT::checkCustomerVATempty($id),
				'action' => $vat_details['country_iso'] != 'GB'?$this->l('Validate VAT with VIES', 'AdminCustomersVatManager'):$this->l('Validate VAT with GOV.UK', 'AdminCustomersVatManager'),
		));
	
		return $tpl->fetch();
	}
    
    /**
     * AdminCustomersVatManagerController::displayDeleteVATLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayDeleteVATLink($token = null, $id)
	{
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/delete_vat.tpl');
		$tpl->assign(array(
				'href' => $this->context->link->getAdminLink('AdminCustomersVatManager').'&'.$this->identifier.'='.(int)$id.'&action=deleteVAT&token='.($token != null ? $token : $this->token),
                'emptyVAT' => CustomersVAT::checkCustomerVATempty($id),
				'action' => $this->l('Delete VAT number', 'AdminCustomersVatManager'),
				'id' => $id,
		));
	
		return $tpl->fetch();
	}
    
    /**
     * AdminCustomersVatManagerController::displaySendValidationEmailLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displaySendValidationEmailLink($token = null, $id)
	{
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersVatManager/send_validation_email.tpl');
		$tpl->assign(array(
				'href' => $this->context->link->getAdminLink('AdminCustomersVatManager').'&'.$this->identifier.'='.(int)$id.'&action=sendValidationEmail&token='.($token != null ? $token : $this->token),
				'action' => $this->l('Send request validation email', 'AdminCustomersVatManager'),
				'id' => $id,
		));
	
		return $tpl->fetch();
	}
    
    /**
     * AdminCustomersVatManagerController::getCustomerId()
     * 
     * @param mixed $id
     * @return
     */
    protected static function getCustomerId($id)
    {
        $sql = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_advancedvatmanager_customers = '.(int)$id; 
        $id_customer = Db::getInstance()->getValue($sql);
        return (int)$id_customer;
    }
    
    /**
     * AdminCustomersVatManagerController::getAddressId()
     * 
     * @param mixed $id
     * @return
     */
    protected static function getAddressId($id)
    {
        $sql = 'SELECT id_address FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_advancedvatmanager_customers = '.(int)$id; 
        $id_customer = Db::getInstance()->getValue($sql);
        return (int)$id_customer;
    }
    
    /**
     * AdminCustomersVatManagerController::getVATDetails()
     * 
     * @param mixed $id
     * @return
     */
    protected static function getVATDetails($id)
    {
        $sql = 'SELECT vat, id_customer FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers` WHERE id_advancedvatmanager_customers = '.(int)$id; 
        $vat_details = array();
        if ($row = Db::getInstance()->getRow($sql)) {
            $vat_details['vat'] =  Tools::substr($row['vat'], 2);
            $vat_details['country_iso'] =  Tools::substr($row['vat'], 0, 2);
            $vat_details['vat_number'] = $row['vat'];
            $customer = new Customer($row['id_customer']);
            $vat_details['customer'] = $customer->firstname.' '.$customer->lastname;
            return $vat_details;
        }
        return false;
    }
    
    /**
     * AdminCustomersVatManagerController::getMessage()
     * 
     * @return
     */
    private function getMessage()
    {
        return array_unique($this->message);
    }
    
    /**
     * AdminCustomersVatManagerController::downloadFile()
     * 
     * @return
     */
    public function downloadFile()
    {
        $file = _PS_MODULE_DIR_.'advancedvatmanager/download/customers_vat.xlsx';
        // Process download
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            flush(); // Flush system output buffer
            readfile($file);
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessCheckTotalAddress()
     * 
     * @return
     */
    public function ajaxProcessCheckTotalAddress()
    {
        if (Tools::getValue('token')) {
            $results = CustomersVAT::getCustomerAddresses();
            if (filter_var(Tools::getValue('scanFromLastMode'), FILTER_VALIDATE_BOOLEAN) === true) {
                $results = CustomersVAT::getCustomerAddresses(CustomersVAT::getLastCustomerAddressChecked());
            }
            else {
                $results = CustomersVAT::getCustomerAddresses();
            }
            
            if ($results) {
                $this->message['success'] = sprintf($this->l('%s addresses have been found in database','AdminCustomersVatManager'),count($results));    
            }
            else {
                $this->message['info'] = $this->l('No addresses have been found','AdminCustomersVatManager');    
            }
            die(json_encode(array('total' => $results!== false?count($results):0, 'addresses' => $results, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessRemoveVAT()
     * 
     * @return
     */
    public function ajaxProcessRemoveVAT()
    {
        if (Tools::getValue('token')) {
            $vat_address = Tools::getValue('address');
            $result = false;
            if (!empty($vat_address)) {
                if (Tools::getIsset('duplicated')) {
                    $var_duplicated = array();
                    foreach ($vat_address as $element) {
                        $var_duplicated[$element['vat_number']][] = $element['id_address'];
                    }
                    foreach ($var_duplicated as $id_addresses) {
                        $min_address = min($id_addresses);
                        $customer_allowed = CustomersVAT::getCustomerIDByAddress($min_address);
                        foreach ($id_addresses as $id_address) {
                            if ($customer_allowed != CustomersVAT::getCustomerIDByAddress($id_address)) {
                                CustomersVAT::deleteByIDAddress($id_address);
                                if ($result = Db::getInstance()->update('address', array('deleted' => '1'), 'id_address ='.(int)$id_address)) {
                                    $this->message['success'][] = sprintf($this->l('The address with ID#%s has been deleted','AdminCustomersVatManager'),$id_address);
                                }
                                else {
                                    $this->message['danger'][] = sprintf($this->l('The address with ID#%s could not be deleted','AdminCustomersVatManager'),$id_address);    
                                } 
                            }
                        }
                    }
                }
                else {
                    CustomersVAT::deleteByIDAddress($vat_address['id_address']); 
                    if ($result = Db::getInstance()->update('address', array('deleted' => '1'), 'id_address ='.(int)$id_address)) {
                        $this->message['success'] = sprintf($this->l('The address with ID#%s has been deleted','AdminCustomersVatManager'),$vat_address['id_address']);
                    }
                    else {
                        $this->message['danger'] = sprintf($this->l('The address with ID#%s could not be deleted','AdminCustomersVatManager'),$vat_address['id_address']);    
                    }  
                }
            }
            die(json_encode(array('success' => $result, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessAssignCustomerGroup()
     * 
     * @return
     */
    public function ajaxProcessAssignCustomerGroup()
    {
        if (Tools::getValue('token')) {
            $vat_address = Tools::getValue('address');
            if (!empty($vat_address)) {
                $this->message['success'] = ValidationEngine::manageCustomerGroups($vat_address['id_country'], $vat_address['id_customer'], true);
            }
            die(json_encode(array('success' => true, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessAutoFillCompanyName()
     * 
     * @return
     */
    public function ajaxProcessAutoFillCompanyName()
    {
        if (Tools::getValue('token')) {
            $vat_address = Tools::getValue('address');
            
            if (!empty($vat_address)) {
                CustomersVAT::updateCompanyName($vat_address['id_address'], $vat_address['registered_company']);
                 $this->message['success'] = sprintf($this->l('The company name %s has been updated correctly in address ID#%s','AdminCustomersVatManager'), $vat_address['registered_company'], $vat_address['id_address']);
            }
            die(json_encode(array('success' => true, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessCreateFile()
     * 
     * @return
     */
    public function ajaxProcessCreateFile()
    {
        if (Tools::getValue('token')) {
            require_once(_PS_MODULE_DIR_ . 'advancedvatmanager/libs/simplexlsxgen/SimpleXLSXGen.php');
            $vat_address = CustomersVAT::getCustomerAddressesWithValidationInfo();
            $content = array();
            if (!empty($vat_address)) {
                // Create Excel File
                $file = _PS_MODULE_DIR_.'advancedvatmanager/download/customers_vat.xlsx';

                $content[] = [$this->l('ID Address','AdminCustomersVatManager'), $this->l('ID Customer','AdminCustomersVatManager'), $this->l('Customer name','AdminCustomersVatManager'), $this->l('Email','AdminCustomersVatManager'), $this->l('Address','AdminCustomersVatManager'), $this->l('VAT Number','AdminCustomersVatManager'), $this->l('VAT Validated','AdminCustomersVatManager'), $this->l('Company validated','AdminCustomersVatManager'), $this->l('System check','AdminCustomersVatManager'), $this->l('Status','AdminCustomersVatManager')];
                
                foreach ($vat_address as $fields) {
                    $content[] = [$fields['id_address'], $fields['id_customer'], $fields['firstname'].' '.$fields['lastname'], $fields['email'], $fields['address1'].' - '.$fields['address2'], $fields['vat'], (int)$fields['validated'], (int)$fields['validated_company'], (int)$fields['system_check'], $fields['status']];
                }
                
                $xlsx = SimpleXLSXGen::fromArray($content);
                $xlsx->saveAs($file);
                
                $this->message['success'] = $this->l('File with Customer VAT list has been created successfully','AdminCustomersVatManager');
            }
            die(json_encode(array('success' => true, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }          
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessSendEmail()
     * 
     * @return
     */
    public function ajaxProcessSendEmail()
    {
        if (Tools::getValue('token')) {
            $vat_address = Tools::getValue('address');
            if (!empty($vat_address)) {
                $avm = new AdvancedVatManager();
                $customer_address = CustomersVAT::getCustomerAddress($vat_address['id_customer'], $vat_address['id_address']);
                $success = $avm->sendEmail(Tools::getValue('mode'), $customer_address);
                if ($success) {
                    $this->message['success'] = sprintf($this->l('The email has been sent successfully to customer %s with email address %s','AdminCustomersVatManager'),$customer_address['firstname'].' '.$customer_address['lastname'], $customer_address['email']);
                }
                else {
                    $this->message['danger'] = sprintf($this->l('The email could not be sent to customer %s with email address %s due an error.','AdminCustomersVatManager'),$customer_address['firstname'].' '.$customer_address['lastname'], $customer_address['email']);    
                }
                
            }
            die(json_encode(array('success' => $success, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }        
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessResetListTable()
     * 
     * @return
     */
    public function ajaxProcessResetListTable()
    {
        if (Tools::getValue('token')) {
            if ($success = CustomersVAT::truncateTable()) {
                $this->message['success'] = $this->l('Customers VAT list has been deleted successfully!','AdminCustomersVatManager');
            }
            else {
                $this->message['danger'] = $this->l('Customers VAT list could not be deleted due an error!','AdminCustomersVatManager');
            }
            die(json_encode(array('success' => $success, 'message' => $this->getMessage())));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessCheckVATNumber()
     * 
     * @return
     */
    public function ajaxProcessCheckVATNumber()
    {
        if (Tools::getValue('token')) {
            $empty_vat = array();
            $invalid_vat = array();
            $invalid_company = array();
            $empty_company = array();
            $valid_vat = array();
            $valid_vat_skipping_validation = array();
            $duplicated_vat = array();
            $message = '';
            // Address values
            $address =  Tools::getValue('address');
            $id_customer = $address['id_customer'];
            $id_country = $address['id_country'];
            $id_address = $address['id_address'];
            $company = $address['company'];
            $customer_name = $address['firstname'].' '.$address['lastname'];
            $email = $address['email'];
            $vat = $address['vat_number'];
            
            $ve = new ValidationEngine($vat);
            $ve->skip_api_fails = (bool)Tools::getValue('skip_apisystemfails');
            ValidationEngine::$admin_scan = true;
            $valid = $ve->VATValidationProcess($id_country, $id_customer, $id_address, $company);
            $vat_number = ValidationEngine::getVat();
            $validation = ValidationEngine::getVATValidation();
            $status = ValidationEngine::getStatus();
            $validation_company = ValidationEngine::$company_valid;
            $registered_company = ValidationEngine::getRegisteredCompanyName();
            
            if (!ValidationEngine::$skip_validation_process) {
                if (!$valid) {
                    if (!$vat_number){
                        $empty_vat = $address;   
                    }
                    else {
                        $invalid_vat  = $address;       
                    }
                }
                else if ($valid) {
                    // Duplicated VAT
                    if (ValidationEngine::$duplicated === true) {
                        $duplicated_vat = $address;    
                    }
                    // Auto-inser mode on
                    if ((bool)Tools::getValue('autofillcompany')) {
                        $company = $registered_company;  
                    }
                    else {
                        // Checks empty company name
                        if (empty($company) && $validation_company == 0) {
                            $empty_company = $address;    
                        }
                        // Checks invalid company
                        else if (!empty($company) && $validation_company == 0) {
                            $invalid_company  = $address;       
                        } 
                    }
                    
                    if (ValidationEngine::getSystemFail() && $ve->skip_api_fails) {
                        $valid_vat_skipping_validation = $address;
                    }
                    else {
                        $valid_vat  = $address;    
                    }
                }
                // Insert element into table ps_advancedvatmanager_customers
                $cv = new CustomersVAT();
                if ($cv->addCustomersVAT($vat_number, $id_customer, $id_address, $validation, $validation_company, (bool)Tools::getValue('autofillcompany')?$company:null, $status, ValidationEngine::getSystemFail())) {
                    $this->message['success'] = sprintf($this->l('The address with ID#%s and VAT number %s has been saved into the Customer VAT list','AdminCustomersVatManager'),$id_address, $vat_number);
                }
                else {
                    $this->message['danger'] = sprintf($this->l('The address with ID#%s and VAT number %s could not be saved into the Customer VAT list','AdminCustomersVatManager'),$id_address, $vat_number);
                }
            }

            // Build message
            $message .= sprintf($this->l('ID Address: %s', 'AdminCustomersVatManager'), $id_address).'<br />';
            $message .= sprintf($this->l('ID Customer: %s', 'AdminCustomersVatManager'), $id_customer).'<br />';
            $message .= sprintf($this->l('Name: %s', 'AdminCustomersVatManager'), $customer_name).'<br />';
            $message .= sprintf($this->l('Email: %s', 'AdminCustomersVatManager'), $email).'<br />';
            $message .= sprintf($this->l('VAT number: %s', 'AdminCustomersVatManager'), $vat_number).'<br />';
            $message .= sprintf($this->l('Country: %s', 'AdminCustomersVatManager'), Country::getNameById($this->context->language->id, $id_country)).'<br />';
            $message .= sprintf($this->l('Company: %s', 'AdminCustomersVatManager'), $company).'<br />';
            $message .= sprintf($this->l('Message: %s', 'AdminCustomersVatManager'), $ve->getMessage()).'<br />';
            $message .= sprintf($this->l('Status: %s', 'AdminCustomersVatManager'), $status).'<br />'; 
            
            die(json_encode(array(
                'valid' => $validation,
                'valid_vat' => $valid_vat,
                'valid_vat_skipping_validation' => $valid_vat_skipping_validation,  
                'invalid_vat' => $invalid_vat,
                'invalid_company' => $invalid_company,
                'empty_company' => $empty_company,
                'duplicated_vat' => $duplicated_vat, 
                'empty_vat' => $empty_vat, 
                'message' => $message, 
                'message_onlive' => $this->getMessage()
            )));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessCleanCache()
     * 
     * @return
     */
    public function ajaxProcessCleanCache()
    {
        if (method_exists('Tools','clearAllCache')) {
            Tools::clearAllCache();      
        }
        else if (method_exists('Tools','clearSmartyCache')) {
            Tools::clearSmartyCache();    
        }
        die(json_encode(array(
            'success' => true
        )));  
    }
    
    /**
     * AdminCustomersVatManagerController::ajaxProcessCheckVATNumber()
     * 
     * @return
     */
    public function ajaxProcessCheckCustomerVAT()
    {
        if (Tools::getValue('token')) {
            $check_system = '';
            if (Tools::getValue('country_iso') == 'GB') {
                $ve = new ValidationEngine(Tools::getValue('vat_number'));
                $valid = $ve->validationForUKVAT(); 
                $check_system = 'HMRC GOV.UK';  
            }
            else {
                $ve = new ValidationEngine(Tools::getValue('vat_number'));
                $valid = $ve->vatValidationViesOneWay();
                $check_system = 'VIES'; 
            }
            die(json_encode(array(
                'valid' => $valid, 
                'vat_number' => $ve->getVatNumber(),
                'vat_iso' => $ve->getVatIso(),
                'company' => $ve->getRegisteredCompanyName(),
                'address' => $ve->getAddress(),
                'request_date' => $ve->getRequestDate(),
                'customer' => Tools::getValue('customer'),
                'check_system' => $check_system,
                'message' => $ve->getMessage()
            )));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * AdminCustomersVatManagerController::l()
     * Implements translations compatibility
     * @param mixed $string
     * @param mixed $class
     * @param bool $addslashes
     * @param bool $htmlentities
     * @return
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ( _PS_VERSION_ >= '1.7') {
            return Translate::getModuleTranslation('advancedvatmanager',$string, $class);
        } else {
            return parent::l($string, $class, $addslashes, $htmlentities);
        }
    } 
}