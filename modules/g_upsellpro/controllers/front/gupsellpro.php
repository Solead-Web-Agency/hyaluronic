<?php
/**
* The file is controller. Do not modify the file if you want to upgrade the module in future
*
* @author    Globo Software Solution JSC <contact@globosoftware.net>
* @copyright  2020 Globo., Jsc
* @license   please read license in file license.txt
* @link	     http://www.globosoftware.net
*/

include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/AdminSettings.php');
include_once(_PS_MODULE_DIR_ . 'g_upsellpro/classes/admin/GupselloffersModel.php');
class G_upsellproGupsellproModuleFrontController extends ModuleFrontController
{
    public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}
    public function postProcess()
    {
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = (int)$this->context->shop->id;
        if(Tools::isSubmit('CheckproductsaddCart')){
            $gupsell_idupsell_rule = (int)Tools::getValue('gupsell_idupsell_rule');
            $sampleproducts = Tools::getValue('sampleproducts');
            $totalprice = 0;
            $totalprice_dc = 0;
            $text_discountprice = 0;

            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $_show_fields = $this->module->getValueConfigshowin((int)$gupsell_idupsell_rule, $gupsell_showinpage, $id_shop_group, $id_shop);
            $volumes = '';
            if (isset($_show_fields[$gupsell_showinpage.'_addition'])) {
                $volumes = $_show_fields[$gupsell_showinpage.'_addition'];
            }
            $text_discount = '';
            $newvolumes = array();
            $minqty = 0;
            $discounttype = 0;
            $discount = 0;
            $id_currency = 0;
            $reduction_tax = 0;$reduction_tax;
            if ($volumes !='') {
                $newvolumes = Tools::jsonDecode($volumes, true);
                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
            }

            if ($sampleproducts !='' && $gupsell_idupsell_rule > 0) {
                $gupsellproObj = new GupselloffersModel($gupsell_idupsell_rule);
                $allproducts = explode(',',$sampleproducts);
                if ($allproducts) {
                    foreach ($allproducts as $allproduct) {
                        $products = explode('|',$allproduct);
                        if ($products) {
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$products['0'], true, (int)$products['1'], 6, null, false, true, (int)$products['2']) * (int)$products['2'];
                                $totalprice += $totalpriceold;
                                if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                                    if ((int)$gupsellproObj->apply_discount == 1) {
                                        if ((int)$gupsellproObj->type_discount == 0) {
                                            $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                            $totalprice_dc += $totalpriceold - $pricediscount;
                                            $text_discountprice += $pricediscount;
                                            $text_discount = Tools::displayPrice($pricediscount);
                                        } else {
                                            $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discountprice += ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discount = (float)$gupsellproObj->amount_discount .$this->l('%', 'g_upsellpro');
                                        }
                                    } else {
                                        $totalprice_dc += $totalpriceold;
                                    }
                                } else {
                                    if ((int)$discounttype == 0) {
                                        $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                        $totalprice_dc += $totalpriceold - $pricediscount;
                                        $text_discountprice += $pricediscount;
                                        $text_discount = Tools::displayPrice($pricediscount);
                                    } else {
                                        $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                                        $text_discountprice +=  ($totalpriceold * ((float)$discount / 100));
                                        $text_discount = (float)$discount .$this->l('%', 'g_upsellpro');
                                    }
                                }
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$products['0'], false, (int)$products['1'], 6, null, false, true, (int)$products['2']) * (int)$products['2'];
                                $totalprice += $totalpriceold;
                                if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                                    if ($gupsellproObj->apply_discount == 1) {
                                        if ($gupsellproObj->type_discount == 0) {
                                            $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                            $totalprice_dc += $totalpriceold - $pricediscount;
                                            $text_discountprice += $pricediscount;
                                            $text_discount = Tools::displayPrice($pricediscount);
                                        } else {
                                            $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discountprice += ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                            $text_discount = (float)$gupsellproObj->amount_discount .$this->l('%', 'g_upsellpro');
                                        }
                                    } else {
                                        $totalprice_dc += $totalpriceold;
                                    }
                                } else {
                                    if ($discounttype == 0) {
                                        $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                        $totalprice_dc += $totalpriceold - $pricediscount;
                                        $text_discountprice += $pricediscount;
                                        $text_discount = Tools::displayPrice($pricediscount);
                                    } else {
                                        $totalprice_dc += $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                                        $text_discountprice += ($totalpriceold * ((float)$discount / 100));
                                        $text_discount = (float)$discount .$this->l('%', 'g_upsellpro');
                                    }
                                }
                            }
                        }
                    }
                }
                
                $date = date('Y-m-d');
                $date_from = date('Y-m-d',strtotime('-1 day', strtotime($date)));
                $addcarts_number = (int)AdminSettings::getTotalFieldsanytic('addcarts',(int)$gupsellproObj->id_g_upsellrule,$date_from, $date,$id_shop);
                AdminSettings::upsellanytic((int)$gupsellproObj->id_g_upsellrule, 0, $addcarts_number + 1, 0, 0, 0, $date_from, $id_shop);
            }

            $results = array(
                'error' => 0,
                'totalprice' => Tools::displayPrice(Tools::convertPriceFull($totalprice)),
                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                'text_discount' => $text_discount,
                'text_discountprice' => Tools::displayPrice(Tools::convertPriceFull($text_discountprice)),
            );
            echo Tools::jsonEncode($results);
            die();
        } elseif(Tools::isSubmit('MathQty')){
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $qtyOld = (int)Tools::getValue('qtyOld');
            $gupsell_idupsell_rule = (int)Tools::getValue('gupsell_idupsell_rule');
            $totalpriceold = 0;
            $totalprice_dc = 0;
            
            $mathqty = Tools::getValue('mathqty');
            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $volumes = '';
            $_show_fields = $this->module->getValueConfigshowin((int)$gupsell_idupsell_rule, $gupsell_showinpage, $id_shop_group, $id_shop);

            $volumes = '';
            if (isset($_show_fields[$gupsell_showinpage.'_addition'])) {
                $volumes = $_show_fields[$gupsell_showinpage.'_addition'];
            }
            $mathqty = Tools::getValue('mathqty');
            if ($id_product > 0 && $gupsell_idupsell_rule > 0) {
                $gupsellproObj = new GupselloffersModel($gupsell_idupsell_rule);
                if (!($product = new Product((int)$id_product, true, $this->context->language->id))) {
                    $results = array(
                        'error' => 1,
                        'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                    );
                    echo Tools::jsonEncode($results);
                    die();
                }
                // Don't try to use a product if not instanciated before due to errors
                if (isset($product) && $product->id) {
                    if ($id_product_attribute != 0) {
                        if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && !Attribute::checkAttributeQty((int)$id_product_attribute, (int)$qtyOld)) {
                            $results = array(
                                'error' => 1,
                                'warrning'=>$this->l('There is not enough product in stock.', 'g_upsellpro'),
                            );
                            echo Tools::jsonEncode($results);
                            die();
                        }
                    } elseif (!$product->checkQty((int)$qtyOld)) {
                        $results = array(
                            'error' => 1,
                            'warrning'=>$this->l('There is not enough product in stock.', 'g_upsellpro'),
                        );
                        echo Tools::jsonEncode($results);
                        die();
                    }
                } else {
                    $results = array(
                        'error' => 1,
                        'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                    );
                    echo Tools::jsonEncode($results);
                    die();
                }
                
                switch ($mathqty) {
                    case 'up':
                        if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);

                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        } else {
                            $newvolumes = array();
                            $minqty = 0;
                            $discounttype = 0;
                            $discount = 0;
                            $id_currency = 0;
                            $reduction_tax = 0;
                            if ($volumes !='') {
                                $newvolumes = Tools::jsonDecode($volumes, true);
                                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                            }
                            $priceDisplay     = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
    
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld + 1);
                            }
                            
                            $totalprice_dc = $totalpriceold;
                            if ($discounttype == 0) {
                                $pricediscount = Tools::convertPrice((float)$discount , (int)$id_currency, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        }
                    case 'down':
                        if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($gupsellproObj->qty > $qtyOld - 1 || $minimal_qty > $qtyOld - 1) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                                }
                                
                                $totalprice_dc = $totalpriceold;
                                if ($gupsellproObj->apply_discount == 1) {
                                    if ($gupsellproObj->type_discount == 0) {
                                        $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                        $totalprice_dc = $totalpriceold - $pricediscount;
                                    } else {
                                        $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                    }
                                }
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$gupsellproObj->qty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            }
                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount, (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        } else {
                            $newvolumes = array();
                            $minqty = 0;
                            $discounttype = 0;
                            $discount = 0;
                            $id_currency = 0;
                            $reduction_tax = 0;
                            if ($volumes !='') {
                                $newvolumes = Tools::jsonDecode($volumes, true);
                                $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                                $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                                $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                                $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                                $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                            }
                            $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                            if ($minqty > $qtyOld - 1 || $minimal_qty > $qtyOld - 1) {
                                if(!$priceDisplay || $priceDisplay == 2) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                } elseif($priceDisplay == 1) {
                                    $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$minqty;
                                }
                                
                                $totalprice_dc = $totalpriceold;
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$minqty, 'g_upsellpro'),
                                    'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                    'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)($qtyOld - 1);
                            }
                            $totalprice_dc = $totalpriceold;
                            if ($discounttype == 0) {
                                $pricediscount = Tools::convertPrice((float)$discount, (int)$discount, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$discount / 100));
                            }
                            $results = array(
                                'error' => 0,
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);die();
                        }
                    default:
                    if (!isset($gupsell_type) || $gupsell_type != 'volume') {
                        $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                        $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                        if ($gupsellproObj->qty > $qtyOld || $minimal_qty > $qtyOld) {
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                            }

                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 1,
                                'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$gupsellproObj->qty, 'g_upsellpro'),
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);
                            die();
                        }
                        if(!$priceDisplay || $priceDisplay == 2) {
                            $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                        } elseif($priceDisplay == 1) {
                            $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                        }
                        
                        $totalprice_dc = $totalpriceold;
                        if ($gupsellproObj->apply_discount == 1) {
                            if ($gupsellproObj->type_discount == 0) {
                                $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                            }
                        }
                        $results = array(
                            'error' => 0,
                            'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                            'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                        );
                        echo Tools::jsonEncode($results);die();
                    } else {
                        $minimal_qty = $id_product_attribute ? Attribute::getAttributeMinimalQty((int)$id_product_attribute) : $product->minimal_quantity;
                        $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                        if ($gupsellproObj->qty > $qtyOld || $minimal_qty > $qtyOld) {
                            if(!$priceDisplay || $priceDisplay == 2) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                            } elseif($priceDisplay == 1) {
                                $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$gupsellproObj->qty;
                            }

                            $totalprice_dc = $totalpriceold;
                            if ($gupsellproObj->apply_discount == 1) {
                                if ($gupsellproObj->type_discount == 0) {
                                    $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                    $totalprice_dc = $totalpriceold - $pricediscount;
                                } else {
                                    $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                                }
                            }
                            $results = array(
                                'error' => 1,
                                'warrning'=> $this->l('You must add a minimum quantity of ' .  (int)$gupsellproObj->qty, 'g_upsellpro'),
                                'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                                'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                            );
                            echo Tools::jsonEncode($results);
                            die();
                        }
                        if(!$priceDisplay || $priceDisplay == 2) {
                            $totalpriceold = Product::getPriceStatic((int)$product->id, true, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                        } elseif($priceDisplay == 1) {
                            $totalpriceold = Product::getPriceStatic((int)$product->id, false, (int)$id_product_attribute, 6, null, false, true, 1) * (int)$qtyOld;
                        }
                        
                        $totalprice_dc = $totalpriceold;
                        if ($gupsellproObj->apply_discount == 1) {
                            if ($gupsellproObj->type_discount == 0) {
                                $pricediscount = Tools::convertPrice((float)$gupsellproObj->amount_discount , (int)$gupsellproObj->id_currency_discount, $this->context->currency);
                                $totalprice_dc = $totalpriceold - $pricediscount;
                            } else {
                                $totalprice_dc = $totalpriceold - ($totalpriceold * ((float)$gupsellproObj->amount_discount / 100));
                            }
                        }
                        $results = array(
                            'error' => 0,
                            'totalpriceold' => Tools::displayPrice(Tools::convertPriceFull($totalpriceold)),
                            'totalprice_dc' => Tools::displayPrice(Tools::convertPriceFull($totalprice_dc)),
                        );
                        echo Tools::jsonEncode($results);die();
                    }
                }
            } else {
                $results = array(
                    'error' => 1,
                    'warrning'=>$this->l('Invalid product', 'g_upsellpro'),
                );
                echo Tools::jsonEncode($results);
                die();
            }
        } elseif (Tools::isSubmit('RemoveproductincartProduct')) {
            $gupsell_idupsell_rule = (int)Tools::getValue('gupsell_idupsell_rule');
            $id_product = (int)Tools::getValue('idProduct');
            $id_product_attribute = (int)Tools::getValue('idCombination');
            $res = true;
            if (!Validate::isInt($id_product)) {
                $results = array(
                    'error' => 1,
                    'warrning'=> $this->l('Invalid product', 'g_upsellpro'),
                );
                echo Tools::jsonEncode($results);
                die();
            }
            if (!Validate::isInt($id_product_attribute)) {
                $results = array(
                    'error' => 1,
                    'warrning'=> $this->l('Invalid combination', 'g_upsellpro'),
                );
                echo Tools::jsonEncode($results);
                die();
            }
            $res &= $this->context->cart->deleteProduct($id_product, $id_product_attribute);
            
            if (!$res) {
                $results = array(
                    'error' => 1,
                );
            } else 
                $results = array(
                    'error' => 0,
                );
            echo Tools::jsonEncode($results);
            die();

        } elseif (Tools::isSubmit('Removeproductincart')) {
            $gupsell_idupsell_rule = (int)Tools::getValue('gupsell_idupsell_rule');
            $id_cart = (int)$this->context->cart->id;
            $id_shop = (int)$this->context->shop->id;
            $res = true;
            if ($gupsell_idupsell_rule && $id_cart) {
                $gupsell_pro = new GupselloffersModel($gupsell_idupsell_rule);
                if ($gupsell_pro->remove_product) {
                    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'cart_product` WHERE `id_cart` = '.(int)$id_cart;
                    switch ($gupsell_pro->display_product) {
                        /*case 'all_product':
                            $sql .= ' WHERE `id_cart` = '.(int)$id_cart;
                            break;*/
                        case 'specific_product':
                            $productids = GupselloffersModel::getProductcombinidproduct((int)$gupsell_pro->id_g_upsellrule, 'display', $id_shop);
                            if ($productids) {
                                $sql .= ' AND (';
                                foreach ($productids as $kstart => $productid) {
                                    $productcombieids = GupselloffersModel::getProductcombininproductid((int)$gupsell_pro->id_g_upsellrule, $productid, 'display', $id_shop);
                                    $productcombieids = implode(',', $productcombieids);
                                    if ($kstart==0) {
                                        $sql .= '(`id_product`= '.(int)$productid.' AND `id_product_attribute` IN ('.pSql($productcombieids ? $productcombieids: 'null').'))';
                                    } else {
                                        $sql .= ' OR (`id_product`= '.(int)$productid.' AND `id_product_attribute` IN ('.pSql($productcombieids ? $productcombieids: 'null').'))';
                                    }
                                }
                                $sql .= ') ';
                            }
                            break;
                        case 'collections_product':
                            $categorys = Tools::jsonDecode($gupsell_pro->display_cateids, true);
                            $sql .= 'AND `id_product` IN (
                                SELECT distinct id_product FROM `'._DB_PREFIX_.'category_product` WHERE id_category IN ('.pSql(implode(',',$categorys)).')
                            ) ';
                            break;
                        default:
                            break;
                    }
                    $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
                    if ($products)
                        foreach ($products as $product) {
                            if (($id_product = (int)$product['id_product']) && !Validate::isInt($id_product)) {
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('Invalid product', 'g_upsellpro'),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            if (($id_product_attribute = (int)$product['id_product_attribute']) && !Validate::isInt($id_product_attribute)) {
                                $results = array(
                                    'error' => 1,
                                    'warrning'=> $this->l('Invalid combination', 'g_upsellpro'),
                                );
                                echo Tools::jsonEncode($results);
                                die();
                            }
                            $res &= $this->context->cart->deleteProduct($id_product, $id_product_attribute);
                        }
                }
                
            }
            if (!$res) {
                $results = array(
                    'error' => 1,
                );
            } else 
                $results = array(
                    'error' => 0,
                );
            echo Tools::jsonEncode($results);
            die();
        } elseif (Tools::isSubmit('refreshCartpopup')){
            $id_cart = (int)$this->context->cart->id;
            $total_cart = 0;
            $html = '';
            $product_carts = array();
            if($id_cart > 0) {
                $total_cart = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
                $product_carts = $this->context->cart->getProducts(true);
            }
            $this->context->smarty->assign(array(
                'link' => $this->context->link,
                'total_cart' => Tools::displayPrice($total_cart, (int)$this->context->currency->id),
                'product_carts' => $product_carts,
            ));
            $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/hook/miniatures/productcart.tpl');
            echo $html;
            die();
        } elseif (Tools::isSubmit('Resetproduct')){
            $id_lang = (int)$this->context->language->id;
            $id_shop = (int)$this->context->shop->id;
            $gupselltotalprice = 0;
            $gupselltotalpricenew = 0;
            $id_upsellpro = (int)Tools::getValue('id_upsellpro');
            $id_attribute = Tools::getValue('id_attribute');
            $qty = (int)Tools::getValue('qty');
            $type_template = Tools::getValue('type_template');
            $html = '';
            $id_product = (int)Tools::getValue('id_product');
            
            $gupsell_idproextra = (int)Tools::getValue('gupsell_idproextra');
            $gupsell_showinpage = Tools::getValue('gupsell_showinpage');
            $gupsell_type = Tools::getValue('gupsell_type');
            $volumes = '';
            $_show_fields = $this->module->getValueConfigshowin((int)$id_upsellpro, $gupsell_showinpage, $id_shop_group, $id_shop);

            $volumes = '';
            if (isset($_show_fields[$gupsell_showinpage.'_addition'])) {
                $volumes = $_show_fields[$gupsell_showinpage.'_addition'];
            }
            $mostpopular = 0;
            
            $newvolumes = array();
            $minqty = 0;
            $discounttype = 0;
            $discount = 0;
            $id_currency = 0;
            $reduction_tax = 0;
            $gupselltotalpricediscount = 0;
            $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
            $getConfigFieldsValues = $this->module->getConfigFieldsValues();
            $newvolumes_extras = array();
            if ($id_attribute) {
                $upsellObj = new GupselloffersModel($id_upsellpro);
                $mostpopular    = GupselloffersModel::getProductmostpopular((int)$upsellObj->id_g_upsellrule, '', $id_shop);
                if ($volumes !='') {
                    $newvolumes = Tools::jsonDecode($volumes, true);
                    $minqty = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['minqty'];
                    $discounttype = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discounttype'];
                    $discount = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['discount'];
                    $id_currency = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['id_currency'];
                    $reduction_tax = (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['reduction_tax'];
                    $mostpopular = isset($newvolumes[$gupsell_idproextra][$gupsell_showinpage]['mostpopular']) ? (int)$newvolumes[$gupsell_idproextra][$gupsell_showinpage]['mostpopular'] : 0 ;
                    if ($mostpopular) {
                        $mostpopular = $id_product;
                    }
                    $newvolumes_extras[] = $newvolumes[$gupsell_idproextra];
                }
                $gupsellvolumeprices = array();
                
                $products = $this->module->getProductsProperties($upsellObj, array($id_product), $id_attribute, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount, $id_lang, $id_shop, 0,false, $newvolumes_extras, $gupsell_type, $gupsell_showinpage);
                $showproductids = GupselloffersModel::getProductcombinidproduct((int)$upsellObj->id_g_upsellrule, '', $id_shop);
                $count_pro = $this->module->getProductsProperties($upsellObj, $showproductids, $id_attribute, $gupselltotalprice, $gupsellvolumeprices, $gupselltotalpricediscount, $id_lang, $id_shop, 0, true, $newvolumes_extras, $gupsell_type, $gupsell_showinpage);
                $amountdiscount = 0;
                if ($upsellObj->apply_discount == 1) {
                    if ($upsellObj->type_discount != 1) {
                        $amountdiscount = (float)Tools::convertPrice($upsellObj->amount_discount, $upsellObj->id_currency_discount, (int)$this->context->currency->id);
                        $gupselltotalpricenew = $gupselltotalprice - (float)$gupselltotalpricediscount;
                    } else {
                        $amountdiscount = (float)$upsellObj->amount_discount;
                        $gupselltotalpricenew = $gupselltotalprice  - $gupselltotalpricediscount;
                    }
                }
                $version17 = false;
                if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
                    $version17 = true;
                $priceDisplay   = Product::getTaxCalculationMethod((int)$this->context->cookie->id_customer);
                $getConfigFieldsValues = $this->module->getConfigFieldsValues();
                $this->context->smarty->assign(array(
                    'id_shop' => $id_shop,
                    'id_lang' => $id_lang,
                    'link' => $this->context->link,
                    'gupsellproducts' => $products,
                    'count_product' => $count_pro,
                    'upsellObj' => $upsellObj,
                    'cart_token'=>Tools::getToken(false),
                    'discountactive'=>Tools::getToken(false),
                    'ajaxcal' => false,
                    'id_currency' => (int)$this->context->currency->id,
                    'gupselltotalprice' => Tools::convertPriceFull($gupselltotalprice),
                    'gupselltotalpricenew' => Tools::convertPriceFull($gupselltotalpricenew),
                    'amountdiscount' => $amountdiscount,
                    'token' => Tools::getToken(false),
                    'urlajaxmodule' => $this->context->link->getModuleLink($this->module->name, 'gupsellpro'),
                    'getConfigFieldsValues' => $getConfigFieldsValues,
                    'controller' => Tools::getValue('controller'),
                    'priceDisplay' => $priceDisplay,
                    'version17' => $version17,
                    'qty' => $qty,
                    'type_template' => $type_template,
                    'mostpopular'   => $mostpopular,
                    'volumes'    => $newvolumes,
                    'numberkey' => $gupsell_idproextra,
                    'showin'    => $gupsell_showinpage,
                    'gupselltotalpricediscount' => $gupselltotalpricediscount,
                    'cart_show' =>$this->context->link->getPageLink(
                        'cart',
                        null,
                        $this->context->language->id,
                        array(
                            'action' => 'show'
                        ),
                        false,
                        null,
                        true
                    ),
                ));
                if(version_compare(_PS_VERSION_, '1.7.0.0 ', '>='))
                    $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/hook/miniatures/product17.tpl');
                else
                    $html .= $this->context->smarty->fetch(_PS_MODULE_DIR_.'g_upsellpro/views/templates/hook/miniatures/product.tpl');
            }
            $results = array(
                'error' => 0,
                'html' => $html
            );
            echo Tools::jsonEncode($results);
            die();
        }
        parent::postProcess();
    }

    /* fix missing function l() : translate in prestashop version 1.6 */
    protected function l($string, $specific = false, $class = null, $addslashes = false, $htmlentities = true)
    {
        $class;$addslashes;$htmlentities;
        if (isset($this->module) && is_a($this->module, 'Module')) {
            return $this->module->l($string, $specific);
        } else {
            return $string;
        }
    }
}
