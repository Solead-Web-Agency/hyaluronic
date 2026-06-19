<?php
/**
* This file will override module Ps_ImageSlider. Do not modify this file if you want to upgrade the module in future
* 
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2018 Globo JSC
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/

class Ps_ShoppingcartOverride extends Ps_Shoppingcart
{

    public function renderModal(Cart $cart, $id_product, $id_product_attribute, $id_customization=false)
    {
        if (version_compare($this->version, '2.0.2', '<=')) {
            $res = parent::renderModal($cart,$id_product,$id_product_attribute);
        } else {
            $res = parent::renderModal($cart,$id_product,$id_product_attribute, $id_customization);
        }
        $res = parent::renderModal($cart,$id_product,$id_product_attribute, $id_customization);
        if(Module::isInstalled('g_upsellpro') && Module::isEnabled('g_upsellpro')){
            if($id_product > 0){
                $gupsellproModule = Module::getInstanceByName('g_upsellpro');
                $res .= $gupsellproModule->GEThtmlproductsshow(array('id_product'=>$id_product, 'ipa'=> $id_product_attribute),true,'showin_cart_popup');
            }
        }
        return $res;
    }
}