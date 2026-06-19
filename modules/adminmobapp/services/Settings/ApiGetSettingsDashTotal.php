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
class ApiGetSettingsDashTotal extends Core
{
    public function getData()
    {
        $id_language = (int) Tools::getValue('id_language');
        if (!$id_language) {
            $id_language = $this->context->language->id;
        }

        $settings_array = array();

        // SQL queries
        $totalOrdersQuery = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'orders`';
        $totalCustomersQuery = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'customer`';
        $totalProductsQuery = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'product`';
        $totalCategoriesQuery = 'SELECT COUNT(*) FROM `'._DB_PREFIX_.'category`';

        // Fetching data
        try {
            $db = Db::getInstance();
            $totalOrders = (int) $db->getValue($totalOrdersQuery);
            $totalCustomers = (int) $db->getValue($totalCustomersQuery);
            $totalProducts = (int) $db->getValue($totalProductsQuery);
            $totalCategories = (int) $db->getValue($totalCategoriesQuery);
        } catch (Exception $e) {
            error_log('Response error: ' . $e->getMessage());
            
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while preparing the response',
                'error' => $e->getMessage()
            );

            return $this->fetchJSONResponse();
        }

        // Storing results in the array
        $settings_array['totalOrders'] = $totalOrders;
        $settings_array['totalCustomers'] = $totalCustomers;
        $settings_array['totalProducts'] = $totalProducts;
        $settings_array['totalCategories'] = $totalCategories;
        $currency = $this->context->currency;
        $settings_array['currency'] = $currency;
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'data populated',
            'data' => $settings_array
        );
        return $this->fetchJSONResponse();
    }
}
