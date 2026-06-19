<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2023
 *  @license   Single domain
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Core.php');
class ApiGetSettings extends Core
{
    public function getData()
    {
        if (!(int) Tools::getValue('id_language')) {
            $id_language = $this->context->language->id;
        } else {
            $id_language = Tools::getValue('id_language');
        }
        
        $settings_array = array();
        $employee_array = array();
        $shop_name = Configuration::get('ADMINMOBAPP_SHOP_NAME', true);
        $landing_page = Configuration::get('ADMINMOBAPP_LANDING_PAGE', true);
        $connection_key = Configuration::get('ADMINMOBAPP_CONNECTION_KEY', true);
        $base_url = Configuration::get('ADMINMOBAPP_BASE_URL', true);
        $order_notification = Configuration::get('ADMINMOBAPP_ORDER_NOTIFICATION', true);
        $customer_notification = Configuration::get('ADMINMOBAPP_CUSTOMER_NOTIFICATION', true);
        $product_price = Configuration::get('ADMINMOBAPP_PRODUCT_PRICE', true);

        $logo = Configuration::get('ADMINMOBAPP_LOGO');
        $shop_logo = '';
        if ($logo) {
            $shop_logo = _PS_BASE_URL_._PS_IMG_.'/'.$logo;
        }
        $settings_array['shop_logo'] = $shop_logo;
        $settings_array['shop_name'] = $shop_name;
        $settings_array['connection_key'] = $connection_key;
        $settings_array['landing_page'] = $landing_page;
        $settings_array['base_url'] = $base_url;
        $settings_array['order_notification'] = $order_notification;
        $settings_array['customer_notification'] = $customer_notification;
        $settings_array['product_price'] = $product_price;
        $employees = Employee::getEmployees();

        foreach ($employees as $employee) {
            $id_employee = $employee['id_employee'];
            $employeeDetails = new Employee($id_employee);
            if($employeeDetails->id_profile == 1){
                $employee_array[]=$employeeDetails;
            }
                
        }
        $settings_array['employee_array'] = $employee_array;
        $save_ids = Configuration::get('ADMINMOBAPP_EMPLOYEES');
        $selected_employee = [];
        if (!empty($save_ids) && is_string($save_ids)) {
            $selected_employee = explode(',', $save_ids);
        }
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'webservice_account`';
        $webserviceAccounts_all = Db::getInstance()->executeS($sql);
        
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'webservice_account` WHERE `description` LIKE "%adminmobapp%"';
        $webserviceAccounts = Db::getInstance()->executeS($sql);
        
        $settings_array['allow_employee_ids'] = $selected_employee;
        $shops = Shop::getShops();
        $settings_array['shops'] = $shops;
        $multishop = (bool) Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        $settings_array['ismultishop'] = $multishop;
        $settings_array['webserviceAccounts'] = $webserviceAccounts;
        $settings_array['webserviceAccounts_all'] = $webserviceAccounts_all;
        $id_default_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $settings_array['id_default_language'] = $id_default_language;

        $id_default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $settings_array['id_default_currency'] = $id_default_currency;

        
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'data populated',
            'data' => $settings_array
        );
        return $this->fetchJSONResponse();
    }
}
