<?php
/**
* This file will override module Ps_ImageSlider. Do not modify this file if you want to upgrade the module in future
* 
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2018 Globo JSC
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/

class BlockCartOverride extends BlockCart
{
    public function hookAjaxCall($params)
	{
	    $res = parent::hookAjaxCall($params);
        if(Module::isInstalled('g_upsellpro') && Module::isEnabled('g_upsellpro')){
            $id_product = (int)Tools::getValue('id_product');
            $ipa = (int)Tools::getValue('ipa');
            if($id_product > 0){
                $gupsellproModule = Module::getInstanceByName('g_upsellpro');
                $_res = Tools::jsonDecode($res,true);
                $_res['crossSelling'] = $gupsellproModule->GEThtmlproductsshow(array('id_product'=>$id_product, 'ipa'=> $ipa),true,'showin_cart_popup');
                $res = Tools::jsonEncode($_res);
            }
        }
        return $res;
	}
}