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
class ApiAddProductsFile extends Core
{
    public function getData()
    {
        try {
            $id_product = (int) Tools::getValue('id_product');
            if (!isset($_FILES['virtual_product_file']) || !isset($id_product)) {
                throw new Exception('Missing required parameters');
            }

            $id_product = (int) Tools::getValue('id_product');
            if (!$id_product || !Validate::isUnsignedId($id_product)) {
                throw new Exception('Invalid product ID');
            }

            $file = $_FILES['virtual_product_file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error');
            }

            $filename = sha1(basename($file['name'])) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

            $max_length = 40;
            if (strlen($filename) > $max_length) {
                $filename = substr($filename, 0, $max_length);
            }

            $virtualProductFile = new ProductDownload();
            $virtualProductFile->id_product = $id_product;
            $virtualProductFile->display_filename = pSQL($file['name']);
            $virtualProductFile->filename = $filename;
            $virtualProductFile->nb_days_accessible =0;
            $virtualProductFile->nb_downloadable =0;

            $destination = _PS_DOWNLOAD_DIR_ . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception('Failed to move uploaded file');
            }

            if (!$virtualProductFile->save()) {
                throw new Exception('Failed to save virtual product file');
            }

            $this->response['response'] = array(
                'status' => 'success',
                'data' => array(
                    'id_product' => $id_product,
                    'file_name' => $file['name'],
                    'file_path' => $destination
                )
            );
        } catch (Exception $e) {
            $this->response['response'] = array(
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            );
        }

        return $this->fetchJSONResponse();
    }
}
