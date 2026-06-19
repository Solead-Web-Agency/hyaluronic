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
class ApiAddProductsStock extends Core
{
    public function getData()
    {
        try {
            $id_product = (int) Tools::getValue('id_product', 0);
            if (!$id_product || !Validate::isUnsignedId($id_product)) {
                $this->response['product_result'] = array(
                    'status' => 'failure',
                    'message' => 'Invalid or missing Product ID'
                );
                return $this->fetchJSONResponse();
            }

            $stock = (int) Tools::getValue('stock');
            if (!Validate::isInt($stock)) {
                $this->response['product_result'] = array(
                    'status' => 'failure',
                    'message' => 'Invalid stock value'
                );
                return $this->fetchJSONResponse();
            }

            $stock_status = StockAvailable::setProductOutOfStock($id_product, $stock);

            $this->response['product_result'] = array(
                'status' => 'success',
                'data' => 'success'
            );

        } catch (Exception $e) {
            // Log the error for debugging
            PrestaShopLogger::addLog('Error in getData: ' . $e->getMessage(), 3);

            $this->response['product_result'] = array(
                'status' => 'failure',
                'message' => 'An error occurred while processing your request.'
            );
        }

        return $this->fetchJSONResponse();
    }

}
