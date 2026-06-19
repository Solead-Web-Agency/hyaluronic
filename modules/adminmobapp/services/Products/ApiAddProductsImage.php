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
class ApiAddProductsImage extends Core
{
    public function getData()
    {
        $this->initContext();

        $id_product = (int) Tools::getValue('id_product');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->uploadProductImage();
        }

        return $this->fetchJSONResponse();
    }

    private function uploadProductImage()
    {
        $id_product = (int) Tools::getValue('id_product');
        if (!isset($id_product) || !isset($_FILES['image'])) {
            $this->response['product_result'] = array(
                'status' => 'fail',
                'message' => 'Missing required parameters'
            );
            return $this->fetchJSONResponse();
        }

        $idProduct = (int) Tools::getValue('id_product');
        if (!Validate::isUnsignedId($idProduct)) {
            $this->response['product_result'] = array(
                'status' => 'fail',
                'message' => 'Invalid product ID'
            );
            return $this->fetchJSONResponse();
        }

        $imageFile = $_FILES['image'];
        if (!isset($imageFile['tmp_name']) || !Validate::isFileName($imageFile['name'])) {
            $this->response['product_result'] = array(
                'status' => 'fail',
                'message' => 'Invalid image file'
            );
            return $this->fetchJSONResponse();
        }

        if ($this->addImageToProduct($idProduct, $imageFile)) {
            $this->response['product_result'] = array(
                'status' => 'success',
                'message' => 'Image uploaded successfully'
            );
        } else {
            $this->response['product_result'] = array(
                'status' => 'fail',
                'message' => 'Failed to upload image'
            );
        }

        return $this->fetchJSONResponse();
    }

    private function addImageToProduct($idProduct, $imageFile)
    {
        $product = new Product($idProduct);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }
        $is_cover = Tools::getValue('is_cover');
        if ($is_cover == 1) {
            $is_cover = true;
        } else {
            $is_cover = false;
        }
        $image = new Image();
        $image->id_product = $idProduct;
        $image->position = Image::getHighestPosition($idProduct) + 1;
        $image->cover = $is_cover;

        if (!$image->add()) {
            return false;
        }

        $imagePath = _PS_PROD_IMG_DIR_ . Image::getImgFolderStatic($image->id) . $image->id . '.jpg';

        if (!file_exists(dirname($imagePath))) {
            mkdir(dirname($imagePath), 0755, true);
        }

        if (!move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
            $image->delete();
            return false;
        }
        ImageManager::resize($imagePath, $imagePath);
        return true;
    }

}
