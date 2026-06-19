<?php
class Product extends ProductCore
{
    
    
    
    /*
    * module: taxexempt
    * date: 2026-06-19 12:02:43
    * version: 1.3.8
    */
    public static function getPriceStatic(
        $id_product,
        $usetax = true,
        $id_product_attribute = null,
        $decimals = 6,
        $divisor = null,
        $only_reduc = false,
        $usereduc = true,
        $quantity = 1,
        $force_associated_tax = false,
        $id_customer = null,
        $id_cart = null,
        $id_address = null,
        &$specific_price_output = null,
        $with_ecotax = true,
        $use_group_reduction = true,
        Context $context = null,
        $use_customer_price = true,
        $id_customization = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        $cur_cart = $context->cart;
        if ($divisor !== null) {
            Tools::displayParameterAsDeprecated('divisor');
        }
        if (!Validate::isBool($usetax) || !Validate::isUnsignedId($id_product)) {
            die(Tools::displayError());
        }
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int) $id_customer);
        }
        if (!$id_group) {
            $id_group = (int) Group::getCurrent()->id;
        }
        if (!is_object($cur_cart) || (Validate::isUnsignedInt($id_cart) && $id_cart && $cur_cart->id != $id_cart)) {
            
            if (!$id_cart && !isset($context->employee)) {
                die(Tools::displayError());
            }
            $cur_cart = new Cart($id_cart);
            if (!Validate::isLoadedObject($context->cart)) {
                $context->cart = $cur_cart;
            }
        }
        $cart_quantity = 0;
        if ((int) $id_cart) {
            $cache_id = 'Product::getPriceStatic_' . (int) $id_product . '-' . (int) $id_cart;
            if (!Cache::isStored($cache_id) || ($cart_quantity = Cache::retrieve($cache_id) != (int) $quantity)) {
                $sql = 'SELECT SUM(`quantity`)
                FROM `' . _DB_PREFIX_ . 'cart_product`
                WHERE `id_product` = ' . (int) $id_product . '
                AND `id_cart` = ' . (int) $id_cart;
                $cart_quantity = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
                Cache::store($cache_id, $cart_quantity);
            } else {
                $cart_quantity = Cache::retrieve($cache_id);
            }
        }
        $id_currency = Validate::isLoadedObject($context->currency) ? (int) $context->currency->id : (int) Configuration::get('PS_CURRENCY_DEFAULT');
        if (!$id_address && Validate::isLoadedObject($cur_cart)) {
            $id_address = $cur_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
        }
        $address = Address::initialize($id_address, true);
        $id_country = (int) $address->id_country;
        $id_state = (int) $address->id_state;
        $zipcode = $address->postcode;
        if (Tax::excludeTaxeOption()) {
            $usetax = false;
        }
        if ($usetax != false
            && !empty($address->vat_number)
            && $address->id_country != Configuration::get('VATNUMBER_COUNTRY')
            && Configuration::get('VATNUMBER_MANAGEMENT')) {
            $usetax = false;
        }
        if (null === $id_customer && Validate::isLoadedObject($context->customer)) {
            $id_customer = $context->customer->id;
        }
        
        $taxexempts = explode(",", Configuration::get('TAXEXEMPT_GROUPS'));
        if (Configuration::get('PS_TAX') == 0) {
            $usetax = 0;
        } elseif (in_array("1", $taxexempts)) {
            $usetax = 0;
        } elseif (count(array_intersect($taxexempts, Customer::getGroupsStatic((int) $id_customer))) > 0) {
            $usetax = 0;
        }
        
        $return = Product::priceCalculation(
            $context->shop->id,
            $id_product,
            $id_product_attribute,
            $id_country,
            $id_state,
            $zipcode,
            $id_currency,
            $id_group,
            $quantity,
            $usetax,
            $decimals,
            $only_reduc,
            $usereduc,
            $with_ecotax,
            $specific_price_output,
            $use_group_reduction,
            $id_customer,
            $use_customer_price,
            $id_cart,
            $cart_quantity,
            $id_customization
        );
        return $return;
    }
    /*
    * module: blockproductsbycountry
    * date: 2026-06-19 12:20:38
    * version: 1.0.2
    */
    public $blocked_country = false;
    
    /*
    * module: blockproductsbycountry
    * date: 2026-06-19 12:20:38
    * version: 1.0.2
    */
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
    /*
    * module: ets_seo
    * date: 2026-06-23 12:18:22
    * version: 2.4.2
    */
    public function getAnchor($id_product_attribute, $with_id = false)
    {
        if(Module::isEnabled('ets_seo')
            && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            && (int)Configuration::get('ETS_SEO_ENABLE_REMOVE_ATTR_ALIAS')){
            return '';
        }
        return parent::getAnchor($id_product_attribute, $with_id);
    }
}
