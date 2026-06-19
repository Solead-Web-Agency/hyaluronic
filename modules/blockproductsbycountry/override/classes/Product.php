<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

class Product extends ProductCore
{
    public $blocked_country = false;
    
    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
        if (Module::isEnabled('blockproductsbycountry') && $this->id) {
            if (is_null($context)) {
                $context = Context::getContext();
            }
            if (!empty($context->country)) {
                if (!empty($context->controller->controller_type) && $context->controller->controller_type == 'admin') {
                    return;
                }
                $country = $context->country;
                if (!empty($country->id)) {
                    $id_country = $country->id;
                    $bpbc_module = Module::getInstanceByName('blockproductsbycountry');
                    if ($bpbc_module->isProductBlocked($this->id, $id_country)) {
                        $this->available_for_order = false;
                        $this->blocked_country = true;
                    }
                }
            }
        }
    }
    
 //   public static function getProductProperties($id_lang, $row, Context $context = null)
//    {
  //      $row = parent::getProductProperties($id_lang, $row, $context);
    //    if (Module::isEnabled('blockproductsbycountry') && (int)$row['id_product']) {
      //      if (is_null($context)) {
        //        $context = Context::getContext();
          //  }
            //if (!empty($context->country)) {
              //  $country = $context->country;
                //if (!empty($country->id)) {
                  //  $id_country = $country->id;
                    //$bpbc_module = Module::getInstanceByName('blockproductsbycountry');
                    //if ($bpbc_module->isProductBlocked((int)$row['id_product'], $id_country)) {
                     //   $row['available_for_order'] = false;
                      //  $row['blocked_country'] = true;
                   // }
                //}
            //}
        //}
        //return $row;
    //}
}
