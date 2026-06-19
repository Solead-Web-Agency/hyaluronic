<?php
/**
 * 2017-2023 liewebs - Prestashop module developers and website designers.
 *
 * NOTICE OF LICENSE
 *  @author    liewebs <info@liewebs.com>
 *  @copyright 2017-2023 www.liewebs.com - Liewebs
 * 	@module Advanced VAT Manager
 */

require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersExemption.php');

class AdminCustomersExemptionManagerController extends ModuleAdminController
{
    /**
     * AdminCustomersExemptionManagerController::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->show_toolbar = true;
        $this->module = Module::getInstanceByName('advancedvatmanager');
        $this->table = 'advancedvatmanager_customers_exemption';
        $this->identifier = 'id_advancedvatmanager_customers_exemption';
        $this->className = 'CustomersExemption';
        $this->controller_name = 'AdminCustomersExemptionManager';
        $this->allow_export = true;
        $this->lang = false;
        $this->delete = true;
        $this->_use_found_rows = true;
        $this->list_simple_header = false;
        $this->requiredDatabase = true;
        $this->explicitSelect = true;

        $this->_orderWay = 'ASC';
        $this->_defaultOrderBy = 'customer_name';        
        $this->addRowAction('edit');
        $this->addRowAction('viewCustomer');
        $this->addRowAction('delete');
        
        $this->bulk_actions = array(
            'delete' => array('text' => $this->l('Delete','AdminCustomersExemptionManager'), 'icon' => 'far fa-trash-alt','confirm' => $this->l('Would you like to delete selected items from the list?','AdminCustomersExemptionManager'))
        );
        
        $this->_select = 'CONCAT(c.`firstname`,\' \',c.`lastname`) `customer_name`,c.`email`, a.`active`';
        
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)';
        
        $this->_where = Shop::addSqlRestriction(Shop::SHARE_CUSTOMER, 'c');

        $shops_name = array();
        foreach (Shop::getShops() as $shop) {
            $shops_name[$shop['id_shop']] = $shop['name'];
        }
        
        $this->fields_list = array(
            'id_advancedvatmanager_customers_exemption' => array('title' => $this->l('ID','AdminCustomersExemptionManager'), 'type' => 'text', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'id_customer' => array('title' => $this->l('ID Customer','AdminCustomersExemptionManager'), 'filter_key' => 'a!id_customer', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'customer_name' => array('title' => $this->l('Customer name','AdminCustomersExemptionManager'), 'type' => 'text', 'filter_key' => 'c!lastname', 'align' => 'text-center'),
            'email' => array('title' => $this->l('Email','AdminCustomersExemptionManager'), 'type' => 'text', 'align' => 'text-center', 'filter_key' => 'c!email'),
            'id_shop' => array('title' => $this->l('Shop','AdminCustomersExemptionManager'), 'type' => 'text','callback' => 'getShop', 'align' => 'text-center', 'filter_type' => 'int', 'filter_key' => 'c!id_shop', 'list' => $shops_name,'type' => 'select',),
            'active' => array('title' => $this->l('VAT exemption','AdminCustomersExemptionManager'),'align' => 'center','type' => 'bool','orderby' => false,'filter_key' => 'a!active','class' => 'fixed-width-sm', 'callback' => 'showValidateIcons'),
            'date_upd' => array('title' => $this->l('Date updated','AdminCustomersExemptionManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_upd'),
            'date_add' => array('title' => $this->l('Date added','AdminCustomersExemptionManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_add')
        );
        Shop::addTableAssociation('advancedvatmanager_customers_exemption', array('type' => 'shop'));
        parent::__construct();
    }
    
    /**
     * AdminCustomersExemptionManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showValidateIcons($value)
    {
        if ($value == 1) {
            $content = '<i style="font-size:20px;color:#32db1d;" class="fal fa-badge-percent" data-toggle="tooltip" data-placement="top" title="'.$this->l('VAT exemption', 'AdminCustomersExemptionManager').'"></i>'; 
        }
        else if ($value == 0) {
            $content ='<i style="font-size:20px;color:#f92727;" class="fal fa-badge-percent" data-toggle="tooltip" data-placement="top" title="'.$this->l('Force VAT collection', 'AdminCustomersExemptionManager').'"></i>';     
        }
        return $content;
    }
    
    /**
     * AdminCustomersExemptionManagerController::getShop()
     * 
     * @param mixed $value
     * @return
     */
    public function getShop($value)
    {
        return Shop::getShop($value)['name'];
    } 
    
    /**
     * AdminCustomersExemptionManagerController::initContent()
     * 
     * @return
     */
    public function initContent()
    {
        parent::initContent();           
    }
    
    /**
     * AdminCustomersExemptionManagerController::setMedia()
     * 
     * @param bool $isNewTheme
     * @return
     */
    public function setMedia($isNewTheme = true)
    {
        parent::setMedia(); 
        Media::addJsDef(array(
            'ajax_url_avm_search_customers' => $this->context->link->getAdminLink('AdminCustomersExemptionManager'),
            'no_found' => $this->l('No customer found!','AdminCustomersExemptionManager')
        ));
        
        //Fontawesome
        $this->addCSS('https://pro.fontawesome.com/releases/v5.15.4/css/all.css'); 
        
        $this->addCSS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/css/admin/AdminCustomersExemptionManager/customers_exemption.css');
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/js/admin/AdminCustomersExemptionManager/customers_exemption.js');
    }

    /**
     * AdminCustomersExemptionManagerController::initToolbar()
     * 
     * @return
     */
    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_title = $this->meta_title;              
    }

    /**
     * AdminCustomersExemptionManagerController::initPageHeaderToolbar()
     * 
     * @return
     */
    public function initPageHeaderToolbar()
    {
        if ($this->display != 'add') {
            $this->page_header_toolbar_btn['new'] = array(
                'href' => $this->context->link->getAdminLink('AdminCustomersExemptionManager').'&addadvancedvatmanager_customers_exemption&token'.Tools::getAdminTokenLite('AdminCustomersExemptionManager'),
                'desc' => $this->l('Add customer','AdminCustomersExemptionManager'),
                'icon' => 'process-icon-fa-user-plus fas fa-user-plus',
                );
        }
        else {
            $this->page_header_toolbar_btn['back_to_list'] = array(
                'href' => $this->context->link->getAdminLink('AdminCustomersExemptionManager'),
                'desc' => $this->l('Back to list','AdminCustomersExemptionManager'),
                'icon' => 'process-icon-fal fa-arrow-circle-left fal fa-arrow-circle-left',
            );
        }
        parent::initPageHeaderToolbar();
    }

    /**
     * AdminCustomersExemptionManagerController::initProcess()
     * 
     * @return
     */
    public function initProcess()
    {
        parent::initProcess();
    }
    
    /**
     * AdminCustomersExemptionManagerController::renderForm()
     * 
     * @return
     */
    public function renderForm()
    {
        // Gets customers
        $customers = array();
        $customer_saved = array_column(CustomersExemption::getIdCustomersSaved(Tools::getValue('id_advancedvatmanager_customers_exemption')), 'id_customer');
        foreach (Customer::getCustomers() as $customer) {
            if (!empty($customer_saved)) {
                // Avoid to display already customer saved
                if (in_array($customer['id_customer'], $customer_saved)){
                    continue;   
                }    
            }
            $customers[] = array('id' => $customer['id_customer'], 'name' => 'ID#'.$customer['id_customer'].' '.$customer['firstname'].' '.$customer['lastname'].' - '.$customer['email']);    
        }
        $this->fields_form = array(
            'legend' => array('title' => $this->l('Add/Edit customer for VAT force/exemption', 'AdminCustomersExemptionManager'),
                    'icon' => 'fas fa-user-edit'),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => '<span class="search_icon"><i style="font-size:25px;" class="fal fa-search"></i></span>',
                    'id' => 'search_customer',
                    'name' => 'search_customer',
                    'desc' => $this->l('Type customer ID, name or email to find results in live.', 'AdminCustomersExemptionManager'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Customer', 'AdminCustomersVatManager'),
                    'name' => 'id_customer',
                    'desc' => $this->l('Select to add the customer. Only customers who have not been assigned to this list will be available for selection.', 'AdminCustomersVatManager'),
                    'required' => false,
                    'options' => array(
                        'query' => $customers,
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('VAT option', 'AdminCustomersExemptionManager'),
                    'name' => 'active',
                    'desc' => $this->l('Enable VAT exemption or force VAT collection for this customer.', 'AdminCustomersExemptionManager'),
                    'required' => false,
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('VAT exemption', 'AdminCustomersExemptionManager')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Force VAT collection', 'AdminCustomersExemptionManager')
                        )
                    ),
                ),
            ),
            'submit' => array('title' => $this->l('Save', 'AdminCustomersExemptionManager')
            )
        );
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->l('Shop association', 'AdminCustomersExemptionManager'),
                'hint' => $this->l('Select the shops which the VAT exemption will be applied for the customer.', 'AdminCustomersExemptionManager'),
                'name' => 'checkBoxShopAsso',
            );
        }
        return parent::renderForm();
    }

    /**
     * AdminCustomersExemptionManagerController::renderList()
     * 
     * @return
     */
    public function renderList()
    {   
        $helper = new HelperList();
        $helper->module = $this;
        $this->toolbar_title = $this->l('Customer VAT Exemption list','AdminCustomersExemptionManager'); // title
        $tpl_information = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersExemptionManager/information.tpl');

        return $tpl_information->fetch().parent::renderList();
    }

    /**
     * AdminCustomersExemptionManagerController::renderView()
     * 
     * @return
     */
    public function renderView()
    {
        return parent::renderView();  
    }

    /**
     * AdminCustomersExemptionManagerController::postProcess()
     * 
     * @return
     */
    public function postProcess()
    {
        parent::postProcess();
    }   
    
    /**
     * AdminCustomersExemptionManagerController::processBulkDelete()
     * 
     * @return
     */
    public function processBulkDelete() 
    {
        parent::processBulkDelete();  
    }
    
    /**
     * AdminCustomersExemptionManagerController::displayViewCustomerLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayViewCustomerLink($token = null, $id)
    {
        $customer = self::getCustomerId($id);
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersExemptionManager/view_customer.tpl');
		$tpl->assign(array(
				'href' => '?tab=AdminCustomers&id_customer='.(int)$customer.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers'),
				'action' => $this->l('View customer details', 'AdminCustomersExemptionManager'),
		));
	
		return $tpl->fetch();
    }
    
    /**
     * AdminCustomersExemptionManagerController::ajaxProcessSearchCustomer()
     * 
     * @return
     */
    public function ajaxProcessSearchCustomer()
    {
        if (Tools::getValue('token')) {         
            $customers = self::searchByName(Tools::getValue('search_str'));
            die(json_encode(array(
                'customers' => $customers, 
            )));
        }
        else {
            die('Token is not valid!');
        }
    }
    
    /**
     * Light back office search for customers.
     *
     * @param string $query Searched string
     * @param int|null $limit Limit query results
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null Corresponding customers
     *
     * @throws PrestaShopDatabaseException
     */
    public static function searchByName($query, $limit = null)
    {
        $customer_saved = array_column(CustomersExemption::getIdCustomersSaved(), 'id_customer');
        $customer_saved = implode(',', $customer_saved);    
  
        $sql = 'SELECT c.id_customer, c.firstname, c.lastname, c.email
                FROM `' . _DB_PREFIX_ . 'customer` c
                WHERE 1 '.(!empty($customer_saved)?'AND c.id_customer NOT IN ('.$customer_saved.')':'');
        $search_items = explode(' ', $query);
        $research_fields = ['c.id_customer', 'c.firstname', 'c.lastname', 'c.email'];
        $items = [];
        foreach ($research_fields as $field) {
            foreach ($search_items as $item) {
                $items[$item][] = $field . ' LIKE \'%' . pSQL($item) . '%\' ';
            }
        }

        foreach ($items as $likes) {
            $sql .= ' AND (' . implode(' OR ', $likes) . ') ';
        }

        $sql .= Shop::addSqlRestriction(Shop::SHARE_CUSTOMER);

        $sql .= ' GROUP BY c.id_customer ';

        if ($limit) {
            $sql .= ' LIMIT 0, ' . (int) $limit;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
    
    /**
     * AdminCustomersExemptionManagerController::getCustomerId()
     * 
     * @param mixed $id
     * @return
     */
    protected static function getCustomerId($id)
    {
        $sql = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'advancedvatmanager_customers_exemption` WHERE id_advancedvatmanager_customers_exemption = '.(int)$id; 
        $id_customer = Db::getInstance()->getValue($sql);
        return (int)$id_customer;
    }
    
    /**
     * AdminCustomersExemptionManagerController::l()
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