<?php
/**
* Override CartRule class
* 
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright 2018 Globo ., Jsc
* @license   please read license in file license.txt
* @link      http://www.globosoftware.net
*/

class CartRule extends CartRuleCore
{
    public static function autoAddToCart(Context $context = null)
    {
        parent::autoAddToCart($context);
        if((int)Tools::getValue('gupsellprodc') > 0){
            if(Module::isInstalled('g_upsellpro') && Module::isEnabled('g_upsellpro')){
                $moduleObj = Module::getInstanceByName('g_upsellpro');
                if($context == null) $context = Context::getContext();
                $moduleObj->createDiscount((int)Tools::getValue('id_product'), (int)Tools::getValue('ipa'),(int)Tools::getValue('id_upsellpro'), (int)$context->shop->id, Tools::getValue('gupsell_showinpage'),(int)Tools::getValue('gupsell_idproextra'));
            }
        }
        if ((int)Tools::getValue('id_upsellpro') > 0) {
            if(Module::isInstalled('g_upsellpro') && Module::isEnabled('g_upsellpro')){
                $moduleObj = Module::getInstanceByName('g_upsellpro');
                if($context == null) $context = Context::getContext();
                $moduleObj->updateUpsellcart((int)Tools::getValue('id_product'), (int)Tools::getValue('ipa'),(int)Tools::getValue('id_upsellpro'), (int)Tools::getValue('remove_productin_cart'), (int)$context->shop->id);
            }
        }
    }
}