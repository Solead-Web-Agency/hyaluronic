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

class HTMLTemplateInvoice extends HTMLTemplateInvoiceCore
{
    public function getTaxTabContent()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            $debug = Tools::getValue('debug');
            
            require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/CustomersOrders.php');
            $noTax = CustomersOrders::checkOrderWithTaxExempt($this->order->id);
            
            $tax_breakdown = $this->getTaxBreakdown();
                      
            if ($noTax) {
                $tax_exempt = true;
                $tax_breakdown = false;
            }
            else {
                $address = new Address((int) $this->order->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                $tax_exempt = Configuration::get('VATNUMBER_MANAGEMENT')
                                && !empty($address->vat_number)
                                && $address->id_country != Configuration::get('VATNUMBER_COUNTRY');
            }        
            $carrier = new Carrier($this->order->id_carrier);
            $data = [
                'tax_exempt' => $tax_exempt,
                'use_one_after_another_method' => $this->order_invoice->useOneAfterAnotherTaxComputationMethod(),
                'display_tax_bases_in_breakdowns' => $this->order_invoice->displayTaxBasesInProductTaxesBreakdown(),
                'product_tax_breakdown' => $this->order_invoice->getProductTaxesBreakdown($this->order),
                'shipping_tax_breakdown' => $this->order_invoice->getShippingTaxesBreakdown($this->order),
                'ecotax_tax_breakdown' => $this->order_invoice->getEcoTaxTaxesBreakdown(),
                'wrapping_tax_breakdown' => $this->order_invoice->getWrappingTaxesBreakdown(),
                'tax_breakdowns' => $tax_breakdown,
                'order' => $debug ? null : $this->order,
                'order_invoice' => $debug ? null : $this->order_invoice,
                'carrier' => $debug ? null : $carrier,
            ];
            if ($debug) {
                return $data;
            }
            $this->smarty->assign($data);
            return $this->smarty->fetch($this->getTemplate('invoice.tax-tab'));
        }
        else {
            return parent::getTaxTabContent();
        }
    }
}