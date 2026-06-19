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
class ApiGetProductsFile extends Core
{
    public function getData()
    {
        try {
            $response = array(
                'status' => 'error',
                'data' => array(),
                'message' => ''
            );

            $id_product = (int) Tools::getValue('id_product');
            if (!$id_product || !Validate::isUnsignedId($id_product)) {
                throw new Exception('Invalid product ID');
            }

            $product = new Product($id_product);

            if ($product->is_virtual) {
                $sql = 'SELECT `display_filename`
                        FROM `' . _DB_PREFIX_ . 'product_download`
                        WHERE `id_product` = ' . (int)$id_product . '
                        ORDER BY `id_product_download` DESC';
                $file = Db::getInstance()->getRow($sql);

                // Check if file was found
                if ($file) {
                    $response['status'] = 'success';
                    $response['data'] = array(
                        'id_product' => $id_product,
                        'file_name' => $file['display_filename']
                    );
                } else {
                    $response['message'] = 'No files found for this virtual product.';
                }
            } else {
                $response['message'] = 'The product is not a virtual product.';
            }
        } catch (Exception $e) {
            $response['message'] = 'An error occurred: ' . $e->getMessage();
        }

        $this->response['response'] = $response;

        return $this->fetchJSONResponse();
    }
}
