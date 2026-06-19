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
require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');

class AdminCustomersOrdersManagerController extends ModuleAdminController
{
    private $message = array();

    /**
     * AdminCustomersOrdersManagerController::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->show_toolbar = true;
        $this->module = Module::getInstanceByName('advancedvatmanager');
        $this->table = 'advancedvatmanager_orders';
        $this->identifier = 'id_advancedvatmanager_orders';
        $this->className = 'CustomersOrders';
        $this->controller_name = 'AdminCustomersOrdersManager';
        $this->allow_export = true;
        $this->lang = false;
        $this->delete = true;
        $this->_use_found_rows = true;
        $this->list_simple_header = false;
        $this->requiredDatabase = true;
        $this->explicitSelect = true;
        
        parent::__construct();
        
        $this->_orderWay = 'DESC';
        $this->addRowAction('view');
        $this->addRowAction('viewCustomer');
        $this->addRowAction('downloadInvoice');
        $this->addRowAction('delete');
        
        $this->bulk_actions = array(
                    'delete' => array('text' => $this->l('Delete','AdminCustomersOrdersManager'), 'icon' => 'far fa-trash-alt','confirm' => $this->l('Would you like to delete selected items from the list?','AdminCustomersOrdersManager')),
                    'downloadInvoices' => array('text' => $this->l('Download invoices','AdminCustomersVatManager'), 'icon' => 'far fa-file-invoice')
                    
        );
        
        $this->_select = 'CONCAT(LEFT(c.`firstname`,1),\'.\',c.`lastname`) `customer_name`, c.`email`, ord.`invoice_date`, ord.`reference`, ord.`payment`, ord.`total_paid`, ord.`total_paid_tax_incl`, ord.`total_paid_tax_excl`, c.`id_shop`';
        
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `' . _DB_PREFIX_ . 'orders` ord ON (ord.`id_order` = a.`id_order`)';
        
        $this->_where = Shop::addSqlRestriction(Shop::SHARE_ORDER, 'ord');
        
        $shops_name = array();
        foreach (Shop::getShops() as $shop) {
            $shops_name[$shop['id_shop']] = $shop['name'];
        }
        
        $this->fields_list = array(
            'id_advancedvatmanager_orders' => array('title' => $this->l('ID','AdminCustomersOrdersManager'), 'type' => 'text', 'class' => 'fixed-width-xs', 'align' => 'text-center'),
            'id_customer' => array('title' => $this->l('ID customer','AdminCustomersOrdersManager'), 'filter_key' => 'a!id_customer', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'id_order' => array('title' => $this->l('ID Order','AdminCustomersOrdersManager'), 'filter_key' => 'a!id_order', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'id_shop' => array('title' => $this->l('Shop','AdminCustomersVatManager'), 'type' => 'text','callback' => 'getShop', 'align' => 'text-center', 'filter_type' => 'int', 'filter_key' => 'c!id_shop', 'list' => $shops_name,'type' => 'select'),
            'reference' => array('title' => $this->l('Reference','AdminCustomersOrdersManager'), 'type' => 'text', 'filter_key' => 'ord!reference', 'align' => 'text-center'),
            'payment' => array('title' => $this->l('Payment','AdminCustomersOrdersManager'), 'type' => 'text', 'filter_key' => 'ord!payment', 'align' => 'text-center'),
            'total_paid' => array('title' => $this->l('Total paid','AdminCustomersOrdersManager'), 'type' => 'price', 'filter_key' => 'ord!total_paid', 'align' => 'text-center'),
            'total_paid_tax_incl' => array('title' => $this->l('Total tax incl','AdminCustomersOrdersManager'), 'type' => 'price', 'filter_key' => 'ord!total_paid_tax_incl', 'align' => 'text-center'),
            'total_paid_tax_excl' => array('title' => $this->l('Total tax excl','AdminCustomersOrdersManager'), 'type' => 'price', 'filter_key' => 'ord!total_paid_tax_excl', 'align' => 'text-center'),
            'customer_name' => array('title' => $this->l('Customer','AdminCustomersOrdersManager'), 'type' => 'text', 'filter_key' => 'c!lastname', 'align' => 'text-center'),
            'email' => array('title' => $this->l('Email','AdminCustomersOrdersManager'), 'type' => 'text', 'filter_key' => 'c!email', 'align' => 'text-center'),
            'notax' => array('title' => $this->l('Tax exempt','AdminCustomersOrdersManager'), 'filter_key' => 'a!notax', 'align' => 'center', 'type' => 'bool', 'class' => 'fixed-width-xs', 'callback' => 'showValidateIcons'),
            'brexit' => array('title' => $this->l('Brexit','AdminCustomersOrdersManager'), 'filter_key' => 'a!brexit', 'align' => 'center', 'type' => 'bool', 'class' => 'fixed-width-xs', 'callback' => 'showBrexitIcons'),
            'invoice' => array('title' => $this->l('Invoice number','AdminCustomersOrdersManager'), 'filter_key' => 'a!invoice', 'align' => 'text-center', 'class' => 'fixed-width-xs'),
            'invoice_date' => array('title' => $this->l('Invoice date','AdminCustomersOrdersManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'ord!invoice_date'),
            'date_upd' => array('title' => $this->l('Date updated','AdminCustomersOrdersManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_upd'),
            'date_add' => array('title' => $this->l('Date added','AdminCustomersOrdersManager'), 'type' => 'datetime', 'align' => 'text-left','filter_key' => 'a!date_add')
        );
    }
    
    /**
     * AdminCustomersOrdersManagerController::getShop()
     * 
     * @param mixed $value
     * @return
     */
    public function getShop($value)
    {
        return Shop::getShop($value)['name'];
    } 
    
    /**
     * AdminCustomersOrdersManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showValidateIcons($value)
    {
        $text = $value == 1?$this->l('Tax exempt validated','AdminCustomersOrdersManager'):$this->l('No tax exempt','AdminCustomersOrdersManager');
        return $value ? '<i style="font-size:20px;color:#32db1d;" class="far fa-check-circle" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>' : '<i style="font-size:20px;color:#f92727;" class="far fa-times-circle" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';
    }
    
    /**
     * AdminCustomersOrdersManagerController::showValidateIcons()
     * 
     * @param mixed $value
     * @return
     */
    public function showBrexitIcons($value)
    {
        $text = $value == 1?$this->l('Yes','AdminCustomersOrdersManager'):$this->l('No','AdminCustomersOrdersManager');
        return $value ? '<img src="../modules/advancedvatmanager/views/img/united-kingdom.png" width="20" height="20" data-toggle="tooltip" data-placement="top" title="'.$text.'"/>' : '<i style="font-size:20px;color:#f92727;" class="far fa-times-circle" data-toggle="tooltip" data-placement="top" title="'.$text.'"></i>';
    }
    
    /**
     * AdminCustomersOrdersManagerController::initContent()
     * 
     * @return
     */
    public function initContent()
    {       
        parent::initContent();           
    }
    
    /**
     * AdminCustomersOrdersManagerController::initToolbar()
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
     * AdminCustomersOrdersManagerController::initPageHeaderToolbar()
     * 
     * @return
     */
    public function initPageHeaderToolbar()
    {
        if ($this->display == 'view') {
            $admin_delete_link = $this->context->link->getAdminLink('AdminCustomersOrdersManager') . '&deleteadvancedvatmanager_orders&id_advancedvatmanager_orders=' . (int)Tools::getValue('id_advancedvatmanager_orders');
            $this->page_header_toolbar_btn['back_to_list'] = array(
                'href' => $this->context->link->getAdminLink('AdminCustomersOrdersManager'),
                'desc' => $this->module->l('Back to list','AdminCustomersOrdersManager'),
                'icon' => 'fal fa-arrow-circle-left',
            );
            $this->page_header_toolbar_btn['delete'] = array(
                'href' => $admin_delete_link,
                'desc' => $this->module->l('Delete it','AdminCustomersOrdersManager'),
                'icon' => 'process-icon-delete',
                'js' => "return confirm('" . $this->module->l('Are you sure you want to delete it?','AdminCustomersOrdersManager') .
                    "');",
            );
        }
        parent::initPageHeaderToolbar();
    }

    /**
     * AdminCustomersOrdersManagerController::initProcess()
     * 
     * @return
     */
    public function initProcess()
    {
        parent::initProcess();
    }
    
    /**
     * AdminCustomersOrdersManagerController::renderList()
     * 
     * @return
     */
    public function renderList()
    {   
        $helper = new HelperList();
        $helper->module = $this;
        $this->toolbar_title = $this->l('Orders List','AdminCustomersOrdersManager'); // title
        $tpl_panel = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersOrdersManager/orders_vat_manager_panel.tpl');
        $tpl_statistics = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersOrdersManager/order_statistics.tpl');
        $tpl_export_invoices = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersOrdersManager/export_invoices.tpl');
        $tpl_statistics->assign(array(
            'total_brexit' => version_compare(_PS_VERSION_, '1.7.6.0', '>=')?$this->context->getCurrentLocale()->formatPrice(CustomersOrders::getTotalBrexit(), $this->context->currency->iso_code):Tools::displayPrice(CustomersOrders::getTotalBrexit()),
            'total_tax_exempt' => version_compare(_PS_VERSION_, '1.7.6.0', '>=')?$this->context->getCurrentLocale()->formatPrice(CustomersOrders::getTotalTaxExempt(), $this->context->currency->iso_code):Tools::displayPrice(CustomersOrders::getTotalTaxExempt())
        ));
        return $tpl_panel->fetch().$tpl_statistics->fetch().parent::renderList().$tpl_export_invoices->fetch();
    }

    /**
     * AdminCustomersOrdersManagerController::renderView()
     * 
     * @return
     */
    public function renderView()
    {
        $tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'advancedvatmanager/views/templates/admin/AdminCustomersOrdersManager/view.tpl');
        $tpl->assign('advancedvatmanager', $this->object);
        $this->context->smarty->assign(array(
            'shop' => Shop::getShop($this->object->id_shop)['name']
        ));
        
        parent::renderView();    
        return $tpl->fetch();
    }
    
    /**
     * AdminCustomersOrdersManagerController::setMedia()
     * 
     * @param bool $isNewTheme
     * @return
     */
    public function setMedia($isNewTheme = true)
    {
        parent::setMedia(); 

        Media::addJsDef(array(
            'ajax_url_customersordersmanager' => $this->context->link->getAdminLink('AdminCustomersOrdersManager'),
        ));
        
        //Fontawesome
        $this->addCSS('https://pro.fontawesome.com/releases/v5.15.4/css/all.css'); 
        
        $this->addJqueryUi('ui.datepicker');
        $this->addCSS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/css/admin/AdminCustomersOrdersManager/orders_manager.css');
        $this->addJS(_PS_MODULE_DIR_ . 'advancedvatmanager/views/js/admin/AdminCustomersOrdersManager/orders_manager.js');
        
    }
    
    /**
     * AdminCustomersOrdersManagerController::postProcess()
     * 
     * @return
     */
    public function postProcess()
    {
        parent::postProcess();
        
        $id = Tools::getValue('id_advancedvatmanager_orders');
        // Download excel file
        if (Tools::getValue('action') == 'downloadInvoice') {
            $this->generatePDFInvoice(CustomersOrders::getOrderID($id));    
        }
        if (Tools::isSubmit('submit_export_brexit_invoices')) {
            if ($results = CustomersOrders::getBrexitOrdersByDates(Tools::getValue('invoice_date_input_from'), Tools::getValue('invoice_date_input_to'))) {
                $orders = array();
                foreach ($results as $order) {
                    $orders[] = $order['id_order'];
                }
                if (!empty($orders)) {
                    $this->generatePDFInvoice($orders);    
                }    
            }
            else {
                $this->errors[] = $this->l('No invoices generated in these date range.', 'AdminCustomersOrdersManager');    
            }    
        }
        if (Tools::isSubmit('submit_export_tax_exempt_invoices')) {
            if ($results = CustomersOrders::getTaxexemptOrdersByDates(Tools::getValue('invoice_date_input_from'), Tools::getValue('invoice_date_input_to'))) {
                $orders = array();
                foreach ($results as $order) {
                    $orders[] = $order['id_order'];
                }
                if (!empty($orders)) {
                    $this->generatePDFInvoice($orders);    
                }    
            }
            else {
                $this->errors[] = $this->l('No invoices generated in these date range.', 'AdminCustomersOrdersManager');    
            } 
        }
    }
    
    /**
     * AdminCustomersOrdersManagerController::processBulkDownloadInvoices()
     * 
     * @return
     */
    public function processBulkDownloadInvoices()
    {
        if ($this->boxes) {
            $selected = array();
            foreach ($this->boxes as $id) {
                $order_id = CustomersOrders::getOrderID($id);
                $invoice_exist = CustomersOrders::checkInvoiceExistsById($id);
                if ($invoice_exist) {
                    $selected[] = $order_id;
                }                
            }
            if (!empty($selected)) {
                $this->generatePDFInvoice($selected);     
            }
            else {
                $this->errors[] = $this->l('The orders selected have not invoices generated.', 'AdminCustomersOrdersManager');    
            }             
        }
    }
    
    /**
     * AdminCustomersOrdersManagerController::displayDownloadInvoiceLink()
     * 
     * @param mixed $token
     * @param mixed $id
     * @return
     */
    public function displayDownloadInvoiceLink($token = null, $id)
    {
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ .'advancedvatmanager/views/templates/admin/AdminCustomersOrdersManager/download_invoice.tpl');
        $invoice_exist = CustomersOrders::checkInvoiceExistsById($id);
		$tpl->assign(array(
				'href' => $this->context->link->getAdminLink('AdminCustomersOrdersManager').'&'.$this->identifier.'='.(int)$id.'&action=downloadInvoice&token='.($token != null ? $token : $this->token),
				'action' => $this->l('Validate VAT mnually', 'AdminCustomersOrdersManager'),
                'invoice_exist' => $invoice_exist
		));
	
		return $tpl->fetch();
    }
    
    /**
     * AdminCustomersOrdersManagerController::generatePDFInvoice()
     * 
     * @param mixed $ordersID (array or int)
     * @return
     */
    public function generatePDFInvoice($ordersID)
    {
        $order_invoice_list = array();
        if (is_array($ordersID)) {
            $order_invoice_list = self::getOrderInvoiceCollectionByOrdersID($ordersID) ;  
        }
        else {
            $order = new Order((int)$ordersID);
            if (!Validate::isLoadedObject($order)) {
                $this->errors[] = $this->l('The order cannot be found within your database.', 'AdminCustomersOrdersManager');
            }
            $order_invoice_list = $order->getInvoicesCollection();   
        }
        Hook::exec('actionPDFInvoiceRender', ['order_invoice_list' => $order_invoice_list]);

        $pdf = new PDF($order_invoice_list, PDF::TEMPLATE_INVOICE, Context::getContext()->smarty);
        $pdf->render();
    }
    
    /**
     * AdminCustomersOrdersManagerController::getOrderInvoiceCollectionByOrdersID()
     * Returns all the order invoice that match the id orders.
     *
     * @param int $id_orders
     *
     * @return array collection of OrderInvoice
     */
    public static function getOrderInvoiceCollectionByOrdersID($id_orders)
    {
        $ids = implode(',', $id_orders);
        $order_invoice_list = Db::getInstance()->executeS('
            SELECT oi.*
            FROM `' . _DB_PREFIX_ . 'order_invoice` oi
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE oi.`id_order` IN ('.pSQL($ids).')' . Shop::addSqlRestriction(Shop::SHARE_ORDER, 'o') . '
            AND oi.number > 0 ORDER BY oi.date_add ASC');
        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }
    
    public function ajaxProcessGetTotalsByDates()
    {
        if (Tools::getValue('token')) {
            $date_from = Tools::getValue('date_from');
            $date_to = Tools::getValue('date_to');
            $total_brexit = version_compare(_PS_VERSION_, '1.7.6.0', '>=')?Tools::getContextLocale($this->context)->formatPrice(CustomersOrders::getTotalBrexit($date_from, $date_to), $this->context->currency->iso_code):Tools::displayPrice(CustomersOrders::getTotalBrexit($date_from, $date_to));
            $total_tax_exempt = version_compare(_PS_VERSION_, '1.7.6.0', '>=')?Tools::getContextLocale($this->context)->formatPrice(CustomersOrders::getTotalTaxExempt($date_from, $date_to), $this->context->currency->iso_code):Tools::displayPrice(CustomersOrders::getTotalTaxExempt($date_from, $date_to));
            
            die(json_encode(array(
                'total_brexit' => $total_brexit,
                'total_tax_exempt' => $total_tax_exempt
            )));
        }
        else {
            die('Token is not valid!');
        }
    }
          
    /**
     * AdminCustomersOrdersManagerController::l()
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