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
class ApiGetCustomerValidate extends Core
{
    public function getData()
    {
        $email = Tools::getValue('email');
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!$email) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'Email parameter is missing or invalid'
            );
            return $this->fetchJSONResponse();
        }

        try {
            $customer = Customer::getCustomersByEmail($email);

            // Check if customer record found
            if ($customer && is_array($customer) && count($customer) > 0) {
                // Return success response with customer data
                $this->response['response'] = array(
                    'status' => 'success',
                    'message' => 'Customer information',
                    'data' => $customer,
                );
            } else {
                // Return error response if customer not found
                $this->response['response'] = array(
                    'status' => 'error',
                    'message' => 'Customer not found'
                );
            }
        } catch (Exception $e) {
            // Handle any exceptions that occur
            error_log('Error fetching customer information: ' . $e->getMessage());
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while fetching customer information'
            );
        }
        return $this->fetchJSONResponse();
    }
}
