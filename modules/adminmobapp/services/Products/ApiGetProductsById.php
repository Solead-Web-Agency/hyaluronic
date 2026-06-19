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
require_once(dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/Pretty.php');
class ApiGetProductsById extends Core
{
    public function getData()
    {
        $product = "";

        $this->initContext();
        

        if ((int) Tools::getValue('id_currency')) {
            $id_currency = (int) Tools::getValue('id_currency');
            $result = Currency::getCurrency($id_currency);

            if ($result) {
                $currency_obj = new Currency($id_currency);
                $this->context->currency->id = $id_currency;
                $this->context->currency->name = $result['name'];
                $this->context->currency->iso_code = $result['iso_code'];
                $this->context->currency->sign = $currency_obj->sign;
            } else {
                $this->response['product_result'] = array(
                    'status' => 'failure',
                    'message' => 'Invalid currency ID'
                );
                return $this->fetchJSONResponse();
            }
        }

        if (!(int) Tools::getValue('id_language')) {
            $id_language = $this->context->language->id;
        } else {
            $id_language = (int) Tools::getValue('id_language');
        }

        // Validate and get product ID
        $id_product = (int) Tools::getValue('id_product', 0);
        if (!$id_product) {
            $this->response['product_result'] = array(
                'status' => 'failure',
                'message' => 'Product ID not found'
            );
            return $this->fetchJSONResponse();
        }

        // Load product
        $this->product = new Product($id_product, false, $id_language);
        
        if (!Validate::isLoadedObject($this->product)) {
            $this->response['product_result'] = array(
                'status' => 'failure',
                'message' => 'Product not found'
            );
        } else {
            $id_image = Product::getCover($id_product);
            if ($id_image) {
                $image = new Image($id_image['id_image']);
                $cover = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            } else {
                $cover = null;
            }

            $result = Pretty::getDetail($id_product, $id_language);

            // Return success response
            $this->response['product_result'] = array(
                'status' => 'success',
                'data' => $result,
                'cover' => $cover
            );
        }

        return $this->fetchJSONResponse();
    }
}
