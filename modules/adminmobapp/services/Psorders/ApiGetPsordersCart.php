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
class ApiGetPsordersCart extends Core
{
    public function getData()
    {
        $id_cart = (int) Tools::getValue('id_cart');
        $id_lang_default = (int) Tools::getValue('id_lang');
        $id_lang_default = $this->context->language->id;
        if (!$id_cart || !Validate::isUnsignedId($id_cart)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Invalid or missing order ID'
            );
            return $this->fetchJSONResponse();
        }

        $cart = new Cart($id_cart);
            // $cart_details = $this->getCartDetails($id_cart);
        $objCart = new Cart($id_cart, $id_lang_default);
        $cartProducts = $objCart->getProducts();
        $id_carrier = $objCart->id_carrier;
        $carrier = new Carrier($id_carrier, $id_lang_default);
        $carrier = $carrier;
        $imagesArray = array();
        foreach ($cartProducts as $k => $value) {
            unset($imagesArray);
            $id_product = $value['id_product'];
            $p = new Product($value['id_product']);
            $price = $value['price'];
            $cartProducts[$k]['display_price'] = Tools::displayPrice($price);

            $id_image = Product::getCover($id_product);
            $image = new Image($id_image['id_image']);
            $cover = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            $cartProducts[$k]['cover_image_url'] = $cover;
            $temp_images = $p->getImages((int) $this->context->language->id);

            foreach ($temp_images as $key => $we) {
                $key = $key;
                $image = new Image($we['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
                $imagesArray[] = $image_url;
            }
            $link = new Link();
            $url = $link->getProductLink($id_product);
            $cartProducts[$k]['images_link'] = $imagesArray;
            $cartProducts[$k]['url'] = $url;
            $cartProducts[$k]['default_currency_sign'] = $this->context->currency->sign;
            $cartProducts[$k]['default_currency_iso_code'] = $this->context->currency->iso_code;
            $cartProducts[$k]['default_currency_name'] = $this->context->currency->name;
        }
        $objCart->id_currency = $this->context->currency->id;
        $objCart->update();
        $cart_rules = $objCart->getCartRules();
        $total = $objCart->getTotalCart($id_cart);
        $total_tax_exc = $objCart->getOrderTotal(false, 1);

        $shipping_cost = $objCart->getTotalShippingCost();
        $match = (float) filter_var($total, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'data populated',
            'cart_total' => Tools::displayPrice($total),
            'cart_total_excl' => Tools::displayPrice($total_tax_exc),
            'cart_total_order' => $match,
            'shipping_cost' => Tools::displayPrice($shipping_cost),
            'id_carrier' => $id_carrier,
            'data' => $cartProducts,
            'cart_rules' => $cart_rules
        );

        return $this->fetchJSONResponse();

    }
}
