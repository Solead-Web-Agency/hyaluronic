<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
 
class HTMLTemplateOrderSlip extends HTMLTemplateOrderSlipCore
{
    public function getTaxTabContent()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');
            $noTax = CustomersOrders::checkOrderWithTaxExempt($this->order->id);
            
            $tax_breakdown = $this->getTaxBreakdown();  
            
            if ($noTax) {
                $tax_exempt = true;
                $tax_breakdown = false; 
            }
            else {
                $tax_exempt = Configuration::get('VATNUMBER_MANAGEMENT')
                                && !empty($address->vat_number)
                                && $address->id_country != Configuration::get('VATNUMBER_COUNTRY'); 
            }  
            $this->smarty->assign([
                'tax_exempt' => $tax_exempt,
                'product_tax_breakdown' => $this->getProductTaxesBreakdown(),
                'shipping_tax_breakdown' => $this->getShippingTaxesBreakdown(),
                'order' => $this->order,
                'ecotax_tax_breakdown' => $this->order_slip->getEcoTaxTaxesBreakdown(),
                'is_order_slip' => true,
                'tax_breakdowns' => $tax_breakdown,
                'display_tax_bases_in_breakdowns' => false,
            ]);
    
            return $this->smarty->fetch($this->getTemplate('invoice.tax-tab'));
        }
        else {
            return parent::getTaxTabContent();    
        }  
    }
    
    public function getContent()
    {  
        if (Module::isEnabled('advancedvatmanager')) {
            require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');
            $noTax = CustomersOrders::checkOrderWithTaxExempt($this->order->id); 
            
            $delivery_address = $invoice_address = new Address((int) $this->order->id_address_invoice);
            $formatted_invoice_address = AddressFormat::generateAddress($invoice_address, [], '<br />', ' ');
            $formatted_delivery_address = '';
            if ($this->order->id_address_delivery != $this->order->id_address_invoice) {
                $delivery_address = new Address((int) $this->order->id_address_delivery);
                $formatted_delivery_address = AddressFormat::generateAddress($delivery_address, [], '<br />', ' ');
            }
            $customer = new Customer((int) $this->order->id_customer);
            $this->order->total_paid_tax_excl = $this->order->total_paid_tax_incl = $this->order->total_products = $this->order->total_products_wt = 0;
            if ($this->order_slip->amount > 0) {
                foreach ($this->order->products as &$product) {
                    $product['total_price_tax_excl'] = $product['unit_price_tax_excl'] * $product['product_quantity'];
                    $product['total_price_tax_incl'] = $product['unit_price_tax_incl'] * $product['product_quantity'];
                    if ($this->order_slip->partial == 1) {
                        $order_slip_detail = Db::getInstance()->getRow('
                            SELECT * FROM `' . _DB_PREFIX_ . 'order_slip_detail`
                            WHERE `id_order_slip` = ' . (int) $this->order_slip->id . '
                            AND `id_order_detail` = ' . (int) $product['id_order_detail']);
                        $product['total_price_tax_excl'] = $order_slip_detail['amount_tax_excl'];
                        $product['total_price_tax_incl'] = $order_slip_detail['amount_tax_incl'];
                    }
                    
                    $this->order->total_products += $product['total_price_tax_excl'];
                    $this->order->total_products_wt += $product['total_price_tax_incl'];
                    $this->order->total_paid_tax_excl = $this->order->total_products;
                    $this->order->total_paid_tax_incl = $this->order->total_products_wt;
                }
                if ($this->order->module == 'paypalfeeplus' && Module::isEnabled('paypalfeeplus')) {
                    $transaction = PaypalFeePlusTransaction::getTransactionFromIdOrder($this->order->id);
                    if ((float)$transaction['fee'] > 0) {
                        $this->order->total_paid_tax_excl = $this->order->total_products + $transaction['fee_without_taxes'];
                        $this->order->total_paid_tax_incl = $this->order->total_products_wt + $transaction['fee'];
                    }
                }
                unset($product);
            } else {
                $this->order->products = null;
            }
            if ($this->order_slip->shipping_cost == 0) {
                $this->order->total_shipping_tax_incl = $this->order->total_shipping_tax_excl = 0;
            }
            $tax = new Tax();
            if ($noTax) {
                $tax->rate = 0;
            }
            else {
                $tax->rate = $this->order->carrier_tax_rate;    
            }
            $tax_calculator = new TaxCalculator([$tax]);
            $tax_excluded_display = Group::getPriceDisplayMethod((int) $customer->id_default_group);
            
            $this->order->total_shipping_tax_incl = $this->order_slip->total_shipping_tax_incl;
            $this->order->total_shipping_tax_excl = $this->order_slip->total_shipping_tax_excl;
            
            $this->order_slip->shipping_cost_amount = $noTax?$this->order_slip->total_shipping_tax_incl:($tax_excluded_display ? $this->order_slip->total_shipping_tax_excl : $this->order_slip->total_shipping_tax_incl);
            
    
            $this->order->total_paid_tax_incl += $this->order->total_shipping_tax_incl;
            $this->order->total_paid_tax_excl += $noTax?$this->order->total_shipping_tax_incl:$this->order->total_shipping_tax_excl;
            
            $total_cart_rule = 0;
            if ($this->order_slip->order_slip_type == 1 && is_array($cart_rules = $this->order->getCartRules())) {
                foreach ($cart_rules as $cart_rule) {
                    if ($tax_excluded_display) {
                        $total_cart_rule += $cart_rule['value_tax_excl'];
                    } else {
                        $total_cart_rule += $cart_rule['value'];
                    }
                }
            }
            $this->smarty->assign([
                'order' => $this->order,
                'order_slip' => $this->order_slip,
                'order_details' => $this->order->products,
                'cart_rules' => $this->order_slip->order_slip_type == 1 ? $this->order->getCartRules() : false,
                'amount_choosen' => $this->order_slip->order_slip_type == 2 ? true : false,
                'delivery_address' => $formatted_delivery_address,
                'invoice_address' => $formatted_invoice_address,
                'addresses' => ['invoice' => $invoice_address, 'delivery' => $delivery_address],
                'tax_excluded_display' => $tax_excluded_display,
                'total_cart_rule' => $total_cart_rule,
            ]);
            $tpls = [
                'style_tab' => $this->smarty->fetch($this->getTemplate('invoice.style-tab')),
                'addresses_tab' => $this->smarty->fetch($this->getTemplate('invoice.addresses-tab')),
                'summary_tab' => $this->smarty->fetch($this->getTemplate('order-slip.summary-tab')),
                'product_tab' => $this->smarty->fetch($this->getTemplate('order-slip.product-tab')),
                'total_tab' => $this->smarty->fetch($this->getTemplate('order-slip.total-tab')),
                'payment_tab' => $this->smarty->fetch($this->getTemplate('order-slip.payment-tab')),
                'tax_tab' => $this->getTaxTabContent(),
            ];
            if (Module::isEnabled('ba_prestashop_invoice')) {
                $html = HTMLTemplateOrderSlip::l('Do not Invoice Template actived for this store');
                if (!empty($this->htmlTemplate)) {
                    $html = Tools::htmlentitiesDecodeUTF8($this->htmlTemplate[0]['invoice_template']);
                }
                return $this->replaceToken($html);
            }
            $this->smarty->assign($tpls);
            return $this->smarty->fetch($this->getTemplate('order-slip'));   
        }
        else {
            return parent::getContent();
        }
    } 
    
    /* Only for compatibility purposes with module ba_prestashop_invoice */
    private function baGetTaxBreakdown()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');
            $noTax = CustomersOrders::checkOrderWithTaxExempt($this->order->id);
            if ($noTax) {
                return array();
            }
            $breakdowns = array(
                // 'product_tax' => $this->invoice->getProductTaxesBreakdown($this->order),
                'shipping_tax' => $this->bagetShippingTaxes($this->order),
            );
            foreach ($this->order_slip->getProducts() as $key => $value) {
                $total_price_tax_incl = $value['amount_tax_incl'];
                $total_price_tax_excl = $value['amount_tax_excl'];
                $taxRate = (($total_price_tax_incl-$total_price_tax_excl)/$total_price_tax_excl)*100;
                $taxRate = round($taxRate, 2);
                $breakdowns['product_tax'][$taxRate]['rate'] = $taxRate;
                
                $breakdowns['product_tax'][$taxRate]['total_price_tax_excl'] = $total_price_tax_excl;
                $total_amount = $total_price_tax_incl - $total_price_tax_excl;
                $breakdowns['product_tax'][$taxRate]['total_amount'] = $total_amount;
            }
            // echo '<pre>'; print_r($breakdowns); die;
            foreach ($breakdowns as $type => $bd) {
                if (empty($bd)) {
                    unset($breakdowns[$type]);
                }
            }
            if (empty($breakdowns)) {
                $breakdowns = false;
            }
            if (!empty($breakdowns['product_tax'])) {
                foreach ($breakdowns['product_tax'] as $key => &$bd) {
                    $bd['total_tax_excl'] = $bd['total_price_tax_excl'];
                    if (empty($bd['rate'])) {
                        $bd['rate'] = $key;
                    }
                }
            }
            if (!empty($breakdowns)) {
                foreach ($breakdowns as &$breakdown) {
                    foreach ($breakdown as &$bd) {
                        $bd['total_tax_incl'] = $bd['total_tax_excl'] + $bd['total_amount'];
                    }
                }
            }
            return $breakdowns;
        }
    }     
}
