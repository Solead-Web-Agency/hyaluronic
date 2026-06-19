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
class ApiGetCustomerAddress extends Core
{
    public function getData()
    {
        try {
            $sql = '
                SELECT a.* 
                FROM '._DB_PREFIX_.'address a
                LEFT JOIN '._DB_PREFIX_.'customer c ON a.id_customer = c.id_customer
                WHERE a.id_customer != 0 AND c.deleted = 0
            ';


            $addresses = Db::getInstance()->executeS($sql);
            foreach ($addresses as $address) {
                if (isset($address['deleted']) && $address['deleted'] == 0) {
                    // Include the address if the customer is not deleted
                    $filteredAddresses[] = $address;
                }
            }
            $this->response['response'] = array(
                    'status' => 'success',
                    'message' => 'success',
                    'data' => $filteredAddresses
                );
        } catch (Exception $e) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred while fetching address information'
            );
        }
        return $this->fetchJSONResponse();
    }
}
