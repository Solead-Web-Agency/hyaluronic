<?php

/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2022 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.35
 * @link      https://www.silbersaiten.de
 */

use PrestaShop\PrestaShop\Adapter\StockManager;
use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Localization\CLDR\ComputingPrecision;

require_once(dirname(__FILE__) . '/../../classes/OrderEditHelper.php');

class AdminOrderEditController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context = Context::getContext();
        parent::__construct();
    }

    public function ajaxProcessSaveProductDetail()
    {
        $id_order = (int)Tools::getValue('id_order');
        $id_order_detail = (int)Tools::getValue('id_order_detail');
        $tax_rate = (float)Tools::getValue('tax_rate');
        $result_order_detail_tax = true;

        if ((int)Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'order_detail_tax` WHERE id_order_detail = ' . $id_order_detail)) {
            $id_tax_rate = $this->getTaxIdByTaxRate($tax_rate);
            $result_order_detail_tax = Db::getInstance()->update('order_detail_tax', ['id_tax' => $id_tax_rate], 'id_order_detail = ' . $id_order_detail);
        }

        $result_order_detail = $this->updateOrderDetail($id_order, $id_order_detail, $tax_rate);
        $result_order = $this->updateOrderTotals($id_order);
        $result_invoice = $this->updateOrderInvoiceTotals($id_order);
        $result_carrier = $this->updateOrderCarrierWeight($id_order);

        $result_cart_rules = $this->updateOrderCartRules($id_order);
        if ($result_cart_rules) {
            $result_order = $this->updateOrderTotals($id_order);
            $result_invoice = $this->updateOrderInvoiceTotals($id_order);
        }
        /** ==== We need to change the data in the `ps_order_detail_tax` table */
        $order = new Order($id_order);
        $order->updateOrderDetailTax();
        /** ==== */

        die(json_encode([
            'success' => $result_order_detail && $result_order_detail_tax && $result_order && $result_invoice && $result_carrier,
            'success_msg' => $this->l('Product detail was successfully updated!'),
            'error_msg' => $this->l('Cannot save your changes!'),
        ]));
    }

    /** Copied from src/Adapter/Order/OrderAmountUpdater.php:428 */
    private function updateOrderCartRules($id_order)
    {
        $result = false;
        $order = new Order((int)$id_order);
        $cart = new Cart((int)$order->id_cart);
        $computingPrecision = $this->getPrecisionFromCart($cart);
        $orderInvoiceId = Db::getInstance()->getValue('SELECT `id_order_invoice` FROM `' . _DB_PREFIX_ . 'order_invoice` WHERE number = ' . $order->invoice_number);

        CartRule::autoAddToCart(null, true);
        CartRule::autoRemoveFromCart(null, true);
        $carrierId = $order->id_carrier;

        $newCartRules = $cart->getCartRules(CartRule::FILTER_ACTION_ALL, false);
        // We need the calculator to compute the discount on the whole products because they can interact with each
        // other so they can't be computed independently, it needs to keep order prices
        $calculator = $cart->newCalculator($cart->getProducts(), $newCartRules, $carrierId, $computingPrecision, true /*$this->keepOrderPrices*/);
        $calculator->processCalculation();

        foreach ($order->getCartRules() as $orderCartRuleData) {
            $result = true;
            /** @var CartRuleData $cartRuleData */
            foreach ($calculator->getCartRulesData() as $cartRuleData) {
                $cartRule = $cartRuleData->getCartRule();
                if ($cartRule->id == $orderCartRuleData['id_cart_rule']) {
                    // Cart rule is still in the cart no need to remove it, but we update it as the amount may have changed
                    $orderCartRule = new OrderCartRule($orderCartRuleData['id_order_cart_rule']);
                    $orderCartRule->id_order = $order->id;
                    $orderCartRule->name = $cartRule->name;
                    $orderCartRule->free_shipping = $cartRule->free_shipping;
                    $orderCartRule->value = Tools::ps_round($cartRuleData->getDiscountApplied()->getTaxIncluded(), $computingPrecision);
                    $orderCartRule->value_tax_excl = Tools::ps_round($cartRuleData->getDiscountApplied()->getTaxExcluded(), $computingPrecision);

                    if ($orderCartRule->free_shipping && !Configuration::get('PS_ORDER_RECALCULATE_SHIPPING', null, $this->getOrderShopConstraint($order))) {
                        $orderCartRule->value = $orderCartRule->value - $calculator->getFees()->getInitialShippingFees()->getTaxIncluded() + $order->total_shipping;
                        $orderCartRule->value_tax_excl = $orderCartRule->value_tax_excl - $calculator->getFees()->getInitialShippingFees()->getTaxExcluded() + $order->total_shipping_tax_excl;
                    }

                    $orderCartRule->save();
                    continue 2;
                }
            }

            // This one is no longer in the new cart rules so we delete it
            $orderCartRule = new OrderCartRule($orderCartRuleData['id_order_cart_rule']);
            // This one really needs to be deleted because it doesn't match the applied cart rules any more
            // we don't use soft deleted here (unlike in the handler) but hard delete
            if (!$orderCartRule->delete()) {
                throw new OrderException('Could not delete order cart rule from database.');
            }
        }

        // Finally add the new cart rules that are not in the Order
        foreach ($calculator->getCartRulesData() as $cartRuleData) {
            $cartRule = $cartRuleData->getCartRule();
            foreach ($order->getCartRules() as $orderCartRuleData) {
                if ($cartRule->id == $orderCartRuleData['id_cart_rule']) {
                    // This cart rule is already present no need to add it
                    continue 2;
                }
            }

            // Add missing order cart rule
            $orderCartRule = new OrderCartRule();
            $orderCartRule->id_order = $order->id;
            $orderCartRule->id_cart_rule = $cartRule->id;
            $orderCartRule->id_order_invoice = $orderInvoiceId ?? 0;
            $orderCartRule->name = $cartRule->name;
            $orderCartRule->free_shipping = $cartRule->free_shipping;
            $orderCartRule->value = Tools::ps_round($cartRuleData->getDiscountApplied()->getTaxIncluded(), $computingPrecision);
            $orderCartRule->value_tax_excl = Tools::ps_round($cartRuleData->getDiscountApplied()->getTaxExcluded(), $computingPrecision);
            $orderCartRule->save();
        }
        return $result;
    }

    private function getOrderShopConstraint(Order $order)
    {
        $constraintKey = $order->id_shop . '-' . $order->id_shop_group;
        if (!isset($this->orderConstraints[$constraintKey])) {
            $this->orderConstraints[$constraintKey] = ShopConstraint::shop((int)$order->id_shop);
        }
        return $this->orderConstraints[$constraintKey];
    }

    private function getPrecisionFromCart(Cart $cart)
    {
        $computingPrecision = new ComputingPrecision();
        $currency = new Currency((int)$cart->id_currency);
        return $computingPrecision->getPrecision((int)$currency->precision);
    }

    private function updateOrderDetail($id_order, $id_order_detail, $tax_rate)
    {
        $product_name = Tools::getValue('product_name');
        $product_reference = Tools::getValue('product_reference');
        $product_supplier_reference = Tools::getValue('product_supplier_reference');
        $product_weight = (float)Tools::getValue('product_weight');
        $reduction_percent = (float)Tools::getValue('reduction_percent');
        $product_quantity = (int)Tools::getValue('quantity');
        $product_price_tax_excl = (float)Tools::getValue('price_tax_excl');
        $product_price_tax_incl = (float)Tools::getValue('price_tax_incl');

        $order_detail = new OrderDetail($id_order_detail);
        $order_detail->id_order = $id_order;
        $order_detail->id_warehouse = (int)$order_detail->id_warehouse;
        $order_detail->id_shop = (int)$order_detail->id_shop;
        $order_detail->product_name = pSQL($product_name);
        $order_detail->product_reference = pSQL($product_reference);
        $order_detail->product_supplier_reference = pSQL($product_supplier_reference);
        $order_detail->reduction_percent = $reduction_percent;
        $order_detail->tax_rate = $tax_rate;
        $order_detail->product_weight = $product_weight;
        $order_detail->product_price = Tools::ps_round($product_price_tax_excl, _PS_PRICE_COMPUTE_PRECISION_);
        $order_detail->unit_price_tax_excl = Tools::ps_round($product_price_tax_excl, _PS_PRICE_COMPUTE_PRECISION_);
        $order_detail->unit_price_tax_incl = Tools::ps_round($product_price_tax_incl, _PS_PRICE_COMPUTE_PRECISION_);
        $order_detail->total_price_tax_excl = Tools::ps_round($product_price_tax_excl * $product_quantity, _PS_PRICE_COMPUTE_PRECISION_);
        $order_detail->total_price_tax_incl = Tools::ps_round($product_price_tax_incl * $product_quantity, _PS_PRICE_COMPUTE_PRECISION_);

        $oldQuantity = $order_detail->product_quantity; // Caching old quantity
        $order_detail->product_quantity = $product_quantity;

        $result = $order_detail->save();
        $this->updateStocks($order_detail, $oldQuantity, $product_quantity); // Void answer
        return $result;
    }

    /**
     * This is a native function, located here: src/Adapter/Order/OrderProductQuantityUpdater.php:344
     */
    private function updateStocks(OrderDetail $orderDetail, int $oldQuantity, int $newQuantity): void
    {
        $deltaQuantity = $oldQuantity - $newQuantity;

        if (0 === $deltaQuantity) {
            return;
        }

        if (0 === $newQuantity) {
            // Product deletion. Reinject quantity in stock
            $this->reinjectQuantity($orderDetail, $oldQuantity, $newQuantity, true);
        } elseif ($deltaQuantity > 0) {
            // Increase product quantity
            StockAvailable::updateQuantity(
                $orderDetail->product_id,
                $orderDetail->product_attribute_id,
                $deltaQuantity,
                $orderDetail->id_shop,
                true,
                [
                    'id_order' => $orderDetail->id_order,
                    'id_stock_mvt_reason' => Configuration::get('PS_STOCK_CUSTOMER_RETURN_REASON'),
                ]
            );
        } else {
            // Decrease product quantity. Reinject quantity in stock
            $this->reinjectQuantity($orderDetail, $oldQuantity, $newQuantity, false);
        }
    }

    /**
     * This is a native function, located here: src/Adapter/Order/OrderProductQuantityUpdater.php:384
     */
    protected function reinjectQuantity(
        OrderDetail $orderDetail,
        int         $oldQuantity,
        int         $newQuantity,
                    $delete = false
    )
    {
        // Reinject product
        $reinjectableQuantity = $oldQuantity - $newQuantity;
        $quantityToReinject = $oldQuantity > $reinjectableQuantity ? $reinjectableQuantity : $oldQuantity;

        $product = new Product(
            $orderDetail->product_id,
            false,
            (int)Context::getContext()->language->id,
            (int)$orderDetail->id_shop
        );

        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
            && $product->advanced_stock_management
            && $orderDetail->id_warehouse != 0
        ) {
            $manager = StockManagerFactory::getManager();
            $movements = StockMvt::getNegativeStockMvts(
                $orderDetail->id_order,
                $orderDetail->product_id,
                $orderDetail->product_attribute_id,
                $quantityToReinject
            );

            foreach ($movements as $movement) {
                if ($quantityToReinject > $movement['physical_quantity']) {
                    $quantityToReinject = $movement['physical_quantity'];
                }

                if (Pack::isPack((int)$product->id)) {
                    // Gets items
                    if ($product->pack_stock_type == Pack::STOCK_TYPE_PRODUCTS_ONLY
                        || $product->pack_stock_type == Pack::STOCK_TYPE_PACK_BOTH
                        || ($product->pack_stock_type == Pack::STOCK_TYPE_DEFAULT
                            && Configuration::get('PS_PACK_STOCK_TYPE') > 0)
                    ) {
                        $products_pack = Pack::getItems((int)$product->id, (int)Configuration::get('PS_LANG_DEFAULT'));
                        // Foreach item
                        foreach ($products_pack as $product_pack) {
                            if ($product_pack->advanced_stock_management == 1) {
                                $manager->addProduct(
                                    $product_pack->id,
                                    $product_pack->id_pack_product_attribute,
                                    new Warehouse($movement['id_warehouse']),
                                    $product_pack->pack_quantity * $quantityToReinject,
                                    null,
                                    $movement['price_te']
                                );
                            }
                        }
                    }

                    if ($product->pack_stock_type == Pack::STOCK_TYPE_PACK_ONLY
                        || $product->pack_stock_type == Pack::STOCK_TYPE_PACK_BOTH
                        || (
                            $product->pack_stock_type == Pack::STOCK_TYPE_DEFAULT
                            && (Configuration::get('PS_PACK_STOCK_TYPE') == Pack::STOCK_TYPE_PACK_ONLY
                                || Configuration::get('PS_PACK_STOCK_TYPE') == Pack::STOCK_TYPE_PACK_BOTH)
                        )
                    ) {
                        $manager->addProduct(
                            $orderDetail->product_id,
                            $orderDetail->product_attribute_id,
                            new Warehouse($movement['id_warehouse']),
                            $quantityToReinject,
                            null,
                            $movement['price_te']
                        );
                    }
                } else {
                    $manager->addProduct(
                        $orderDetail->product_id,
                        $orderDetail->product_attribute_id,
                        new Warehouse($movement['id_warehouse']),
                        $quantityToReinject,
                        null,
                        $movement['price_te']
                    );
                }
            }

            $productId = $orderDetail->product_id;

            if ($delete) {
                $orderDetail->delete();
            }

            StockAvailable::synchronize($productId);
        } elseif ($orderDetail->id_warehouse == 0) {
            StockAvailable::updateQuantity(
                $orderDetail->product_id,
                $orderDetail->product_attribute_id,
                $quantityToReinject,
                $orderDetail->id_shop,
                true,
                [
                    'id_order' => $orderDetail->id_order,
                    'id_stock_mvt_reason' => Configuration::get('PS_STOCK_CUSTOMER_RETURN_REASON'),
                ]
            );

            // sync all stock
            (new StockManager())->updatePhysicalProductQuantity(
                (int)$orderDetail->id_shop,
                (int)Configuration::get('PS_OS_ERROR'),
                (int)Configuration::get('PS_OS_CANCELED'),
                null,
                (int)$orderDetail->id_order
            );

            if ($delete) {
                $orderDetail->delete();
            }
        } else {
            throw new OrderException('This product cannot be re-stocked.');
        }
    }

    private function updateOrderTotals($id_order)
    {
        $order_data = $this->getTotalProducts($id_order);

        $order = new Order($id_order);

        OrderEditHelper::saveOriginalOrderBeforeChangeIt($order);

        $cart_rules = [
            'value' => 0,
            'value_tax_excl' => 0
        ];
        foreach ($order->getCartRules() as $cart_rule) {
            $cart_rules['value'] += $cart_rule['value'];
            $cart_rules['value_tax_excl'] += $cart_rule['value_tax_excl'];
        }

        $order->date_upd = date('Y-m-d H:i:s');
        $order->total_products = Tools::ps_round($order_data['total_products'], _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_products_wt = Tools::ps_round($order_data['total_products_wt'], _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_discounts = $cart_rules['value'];
        $order->total_discounts_tax_incl = $cart_rules['value'];
        $order->total_discounts_tax_excl = $cart_rules['value_tax_excl'];
        $order->total_paid = Tools::ps_round(
            $order->total_products_wt + $order->total_shipping_tax_incl + $order->total_wrapping_tax_incl - $cart_rules['value'],
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_excl = Tools::ps_round(
            $order->total_products + $order->total_shipping_tax_excl + $order->total_wrapping_tax_excl - $cart_rules['value_tax_excl'],
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_incl = $order->total_paid;
        $result = $order->save();
        $order->updateOrderDetailTax();
        return $result;
    }

    private function updateOrderInvoiceTotals($id_order)
    {
        $order = new Order($id_order);
        if ($order->invoice_number) { // #FIXME - there may be a problem if the order has more than one invoice.
            $id_order_invoice = Db::getInstance()->getValue('SELECT `id_order_invoice` FROM `' . _DB_PREFIX_ . 'order_invoice` WHERE number = ' . $order->invoice_number);
            $order_invoice = new OrderInvoice($id_order_invoice);
            $order_invoice->total_discount_tax_excl = $order->total_discounts_tax_excl;
            $order_invoice->total_discount_tax_incl = $order->total_discounts_tax_incl;
            $order_invoice->total_paid_tax_excl = $order->total_paid_tax_excl;
            $order_invoice->total_paid_tax_incl = $order->total_paid_tax_incl;
            $order_invoice->total_products = $order->total_products;
            $order_invoice->total_products_wt = $order->total_products_wt;
            return $order_invoice->save();
        }
        return true;
    }

    private function updateOrderCarrierWeight($id_order)
    {
        $order_data = OrderDetail::getList($id_order);
        $total_weight = 0;
        foreach ($order_data as $order_product) {
            $total_weight += (float)$order_product['product_weight'] * $order_product['product_quantity'];
        }
        return Db::getInstance()->update('order_carrier', ['weight' => $total_weight], 'id_order = ' . $id_order);
    }

    private function getTotalProducts($id_order)
    {
        $all_details = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'order_detail` WHERE `id_order` = ' . $id_order);
        $order_data = [
            'total_products' => 0,
            'total_products_wt' => 0,
        ];
        foreach ($all_details as $detail) {
            $order_data['total_products'] += $detail['total_price_tax_excl'];
            $order_data['total_products_wt'] += $detail['total_price_tax_incl'];
        }
        return $order_data;
    }

    public function ajaxProcessSearchOrder()
    {
        $search = Tools::getValue('q');

        $query = new DbQuery();
        $query->select('       
           o.id_order,
           o.reference,
           o.invoice_number,
           pl.name,
           c.firstname,
           c.lastname,
           c.company
        ');
        $query->from('orders', 'o');
        $query->leftJoin('order_detail', 'od', 'o.id_order = od.id_order');
        $query->leftJoin('product', 'p', 'p.id_product = od.product_id');
        $query->leftJoin('product_lang', 'pl', 'pl.id_product = p.id_product');
        $query->leftJoin('product_shop', 'ps', 'ps.id_product = p.id_product');
        $query->leftJoin('customer', 'c', 'c.id_customer = o.id_customer');

        $query->where('pl.id_lang = ' . (int)$this->context->language->id);
        $query->where('pl.id_shop = ' . (int)$this->context->shop->id);
        $query->where('ps.id_shop = ' . (int)$this->context->shop->id);

        $query->where(
            ' o.reference LIKE "%' . pSQL($search) . '%" OR ' .
            ' o.id_order LIKE "%' . pSQL($search) . '%" OR ' .
            ' o.invoice_number LIKE "%' . pSQL($search) . '%" OR ' .
            ' pl.name LIKE "%' . pSQL($search) . '%" OR ' .
            ' c.firstname LIKE "%' . pSQL($search) . '%" OR ' .
            ' c.lastname LIKE "%' . pSQL($search) . '%" OR ' .
            ' c.company LIKE "%' . pSQL($search) . '%" '
        );
        $query->groupBy('o.id_order');
        $result = Db::getInstance()->executeS($query->build());

        if (is_array($result)) {
            foreach ($result as &$item) {
                $item['link'] = $this->context->link->getAdminBaseLink() . basename(_PS_ADMIN_DIR_) . '/index.php/sell/orders/' . $item['id_order'] . '/view?_token='; // Token will be added by js from data-token body's tag
            }
            unset($item);
            die(Tools::jsonEncode($result));
        } else {
            Tools::jsonEncode(new stdClass);
        }
    }

    public function ajaxProcessDeleteOrderHistory()
    {
        $id_order_history = (int)Tools::getValue('id_order_history');
        $id_order = (int)Tools::getValue('id_order');
        $result = true;
        $result &= Db::getInstance()->delete('order_history', 'id_order_history = ' . $id_order_history);
        $order = new Order($id_order);

        OrderEditHelper::saveOriginalOrderBeforeChangeIt($order);

        $order->current_state = OrderEditHelper::getLastOrderStateId($id_order);
        $result &= $order->save();

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order history removed!'),
            'error_msg' => $this->l('Cannot delete order history!'),
        ]));
    }

    public function ajaxProcessUpdateOrderStatusHistory()
    {
        $id_history_state = (int)Tools::getValue('id_history_state');
        $employee_id = (int)Tools::getValue('employee', 0);
        $state_id = (int)Tools::getValue('order_state');
        $status_date = Tools::getValue('status_date');
        $date = date_create_from_format($this->context->language->date_format_full, $status_date);
        $status_date_db = $date->format('Y-m-d H:i:s');

        $result = Db::getInstance()->update(
            'order_history',
            ['date_add' => $status_date_db, 'id_order_state' => $state_id, 'id_employee' => $employee_id],
            'id_order_history = ' . $id_history_state
        );

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order history changed!'),
            'error_msg' => $this->l('Cannot change order history!'),
        ]));
    }

    public function ajaxProcessUpdateOrderPayment()
    {
        $id_payment = (int)Tools::getValue('id_payment');
        $transaction = Tools::getValue('transaction');
        $payment_date = Tools::getValue('payment_date');
        $date = date_create_from_format($this->context->language->date_format_full, $payment_date);
        $payment_date_db = $date->format('Y-m-d H:i:s');

        $result = Db::getInstance()->update(
            'order_payment',
            ['date_add' => $payment_date_db, 'transaction_id' => $transaction],
            'id_order_payment = ' . $id_payment
        );

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order payment changed!'),
            'error_msg' => $this->l('Cannot change order payment!'),
        ]));
    }

    public function ajaxProcessUpdateOrderDocument()
    {
        $result = true;
        $id_order = (int)Tools::getValue('id_order');
        $id_order_document = (int)Tools::getValue('id_order_document');
        $document_date = Tools::getValue('document_date');
        $date = date_create_from_format($this->context->language->date_format_full, $document_date);
        $document_date_db = $date->format('Y-m-d H:i:s');

        switch (Tools::getValue('order_document_type')) {
            case 'invoice';
                $result &= Db::getInstance()->update(
                    'order_invoice',
                    ['date_add' => $document_date_db],
                    '`id_order_invoice` = ' . $id_order_document
                );
                $result &= Db::getInstance()->update(
                    'orders',
                    ['invoice_date' => $document_date_db, 'date_upd' => date('Y-m-d H:i:s')],
                    '`id_order` = ' . $id_order
                );
                break;
            case 'delivery_slip';
                $result &= Db::getInstance()->update(
                    'order_invoice',
                    ['date_add' => $document_date_db, 'delivery_date' => $document_date_db],
                    '`id_order_invoice` = ' . $id_order_document
                );
                $result &= Db::getInstance()->update(
                    'orders',
                    ['delivery_date' => $document_date_db, 'date_upd' => date('Y-m-d H:i:s')],
                    '`id_order` = ' . $id_order
                );
                break;
            case 'credit_slip';
                $result = Db::getInstance()->update(
                    'order_slip',
                    ['date_add' => $document_date_db, 'date_upd' => date('Y-m-d H:i:s')],
                    '`id_order_slip` = ' . $id_order_document
                );
                break;
        }

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order document changed!'),
            'error_msg' => $this->l('Cannot change order document!'),
        ]));
    }

    public function ajaxProcessDeleteOrderDocument()
    {
        $id_order_document = (int)Tools::getValue('id_order_document');
        $order_document_type = Tools::getValue('order_document_type');
        $id_order = (int)Tools::getValue('id_order');
        $result = true;

        if ($order_document_type == 'invoice') {
            $order_invoice = new OrderInvoice($id_order_document);
            if ((int)$order_invoice->delivery_number) {
                $order_invoice->number = 0;
                $result &= $order_invoice->update();
            } else {
                $result &= Db::getInstance()->delete('order_invoice', 'id_order_invoice = ' . $id_order_document . ' AND id_order = ' . $id_order);
            }
        } elseif ($order_document_type == 'delivery_slip') {
            $order_invoice = new OrderInvoice($id_order_document);
            if ((int)$order_invoice->number) {
                $order_invoice->delivery_number = 0;
                $order_invoice->delivery_date = '0000-00-00 00:00:00';
                $result &= $order_invoice->update();
            } else {
                $result &= Db::getInstance()->delete('order_invoice', 'id_order_invoice = ' . $id_order_document . ' AND id_order = ' . $id_order);
            }
        } elseif ($order_document_type == 'credit_slip') {
            $result &= Db::getInstance()->delete('order_slip', 'id_order_slip = ' . $id_order_document . ' AND id_order = ' . $id_order);
        }

        if ($order_document_type == 'invoice') {
            $order = new Order($id_order);

            OrderEditHelper::saveOriginalOrderBeforeChangeIt($order);

            $order->invoice_number = 0;
            $result &= $order->save();

            $products = $order->getProductsDetail();
            foreach ($products as $product) {
                if ($product['id_order_invoice'] == $id_order_document) {
                    $order_detail = new OrderDetail((int)$product['id_order_detail']);
                    $order_detail->id_order_invoice = 0;
                    $result &= $order_detail->save();
                }
            }
        }

        if ($order_document_type == 'delivery_slip') {
            $order = new Order($id_order);

            OrderEditHelper::saveOriginalOrderBeforeChangeIt($order);

            $order->delivery_number = 0;
            $result &= $order->save();
        }

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order document removed!'),
            'error_msg' => $this->l('Cannot delete order document!'),
        ]));
    }

    public function ajaxProcessDeleteOrderPayment()
    {
        $id_order_payment = (int)Tools::getValue('id_order_payment');
        $result_payment = Db::getInstance()->delete('order_payment', 'id_order_payment = ' . $id_order_payment);
        $result_invoice_payment = Db::getInstance()->delete('order_invoice_payment', 'id_order_payment = ' . $id_order_payment);

        die(json_encode([
            'success' => $result_payment && $result_invoice_payment,
            'success_msg' => $this->l('Order payment removed!'),
            'error_msg' => $this->l('Cannot delete order payment!'),
        ]));
    }

    public function ajaxProcessReSendCustomerEmail()
    {
        $id_order = (int)Tools::getValue('id_order');
        $order = new Order($id_order);
        $customer = new Customer($order->id_customer);
        $invoice_address = new Address($order->id_address_invoice);
        $delivery_address = new Address($order->id_address_delivery);
        $currency = new Currency($order->id_currency);
        $products_order = $order->getProducts();
        $discounts = $order->getCartRules();

        $products_by_invoices = array();
        foreach ($products_order as $product) {
            if (!array_key_exists((int)$product['id_order_invoice'], $products_by_invoices)) {
                $products_by_invoices[(int)$product['id_order_invoice']] = array();
            }

            array_push($products_by_invoices[$product['id_order_invoice']], $product);
        }

        $virtual_product = true;
        $carrier_obj = new Carrier($order->id_carrier);

        if (count($products_by_invoices)) {
            $customization_quantities = Customization::countQuantityByCart($order->id_cart);

            $products_list = '';
            $cart_rules_list = '';

            foreach ($products_by_invoices as $products) {
                foreach ($products as $key => $product) {
                    $customized_datas = Product::getAllCustomizedDatas((int)$order->id_cart);
                    $customization_quantity = 0;

                    if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']])) {
                        if (array_key_exists($product['product_id'], $customization_quantities)
                            && array_key_exists($product['product_attribute_id'], $customization_quantities[$product['product_id']])) {
                            $customization_quantity = (int)$customization_quantities[$product['product_id']][$product['product_attribute_id']];
                        }

                        $customization_text = '';
                        foreach ($customized_datas[$product['product_id']][$product['product_attribute_id']][$order->id_address_delivery] as $customization) {
                            if (isset($customization['datas'][Product::CUSTOMIZE_TEXTFIELD])) {
                                foreach ($customization['datas'][Product::CUSTOMIZE_TEXTFIELD] as $text) {
                                    $customization_text .= $text['name'] . ': ' . $text['value'] . '<br />';
                                }
                            }

                            if (isset($customization['datas'][Product::CUSTOMIZE_FILE])) {
                                $customization_text .= sprintf($this->l('%d image(s)'), count($customization['datas'][Product::CUSTOMIZE_FILE])) . '<br />';
                            }

                            $customization_text .= '---<br />';
                        }

                        $customization_text = rtrim($customization_text, '---<br />');

                        $this->context->smarty->assign(array(
                            'orderedit_currency' => $currency,
                            'bg_color_row' => $key % 2 ? '#DDE2E6' : '#EBECEE',
                            'product_reference' => $product['product_reference'],
                            'product_name' => $product['product_name'] . ' - ' . $this->l('Customized') . (!empty($customization_text) ? ' - ' . $customization_text : ''),
                            'unit_price' => Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($product['unit_price_tax_excl'], 2) : $product['unit_price_tax_incl'], $currency, false),
                            'customization_quantity' => $customization_quantity,
                            'total_row_price' => Tools::displayPrice($customization_quantity * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($product['unit_price_tax_excl'], 2) : $product['unit_price_tax_incl']), $currency, false),
                        ));

                        $products_list .= $this->context->smarty->fetch('module:orderedit/views/templates/admin/mail_products.tpl');
                    }

                    if (!$customization_quantity || (int)$product['product_quantity'] > $customization_quantity) {
                        $this->context->smarty->assign(array(
                            'orderedit_currency' => $currency,
                            'bg_color_row' => $key % 2 ? '#DDE2E6' : '#EBECEE',
                            'product_reference' => $product['product_reference'],
                            'product_name' => $product['product_name'],
                            'unit_price' => Tools::displayPrice(Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($product['unit_price_tax_excl'], 2) : $product['unit_price_tax_incl'], $currency, false),
                            'customization_quantity' => ((int)$product['product_quantity'] - $customization_quantity),
                            'total_row_price' => Tools::displayPrice(((int)$product['product_quantity'] - $customization_quantity) * (Product::getTaxCalculationMethod() == PS_TAX_EXC ? Tools::ps_round($product['unit_price_tax_excl'], 2) : $product['unit_price_tax_incl']), $currency, false),
                        ));
                        $products_list .= $this->context->smarty->fetch('module:orderedit/views/templates/admin/mail_products.tpl');
                    }

                    if ($product['download_hash'] == '') {
                        $virtual_product &= false;
                    }
                }
            }

            if ($discounts) {
                $this->context->smarty->assign(array(
                    'voucher' => $this->l('Voucher name:'),
                    'discounts' => $discounts,
                ));
                $cart_rules_list .= $this->context->smarty->fetch('module:orderedit/views/templates/admin/mail_cart_rules.tpl');
            }
        }

        $data = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{delivery_block_txt}' => AddressFormat::generateAddress(
                $delivery_address,
                array('avoid' => array()),
                "\n",
                ' '
            ),
            '{invoice_block_txt}' => AddressFormat::generateAddress(
                $invoice_address,
                array('avoid' => array()),
                "\n",
                ' '
            ),
            '{delivery_block_html}' => AddressFormat::generateAddress(
                $delivery_address,
                array('avoid' => array()),
                "\n",
                ' ',
                array(
                    'firstname' => '<span style="font-weight:bold;">%s</span>',
                    'lastname' => '<span style="font-weight:bold;">%s</span>'
                )
            ),
            '{invoice_block_html}' => AddressFormat::generateAddress(
                $invoice_address,
                array('avoid' => array()),
                "\n",
                ' ',
                array(
                    'firstname' => '<span style="font-weight:bold;">%s</span>',
                    'lastname' => '<span style="font-weight:bold;">%s</span>'
                )
            ),
            '{delivery_company}' => $delivery_address->company,
            '{delivery_firstname}' => $delivery_address->firstname,
            '{delivery_lastname}' => $delivery_address->lastname,
            '{delivery_address1}' => $delivery_address->address1,
            '{delivery_address2}' => $delivery_address->address2,
            '{delivery_city}' => $delivery_address->city,
            '{delivery_postal_code}' => $delivery_address->postcode,
            '{delivery_country}' => $delivery_address->country,
            '{delivery_state}' => '',
            '{delivery_phone}' => ($delivery_address->phone)
                ? $delivery_address->phone
                : $delivery_address->phone_mobile,
            '{delivery_other}' => $delivery_address->other,
            '{invoice_company}' => $invoice_address->company,
            '{invoice_vat_number}' => $invoice_address->vat_number,
            '{invoice_firstname}' => $invoice_address->firstname,
            '{invoice_lastname}' => $invoice_address->lastname,
            '{invoice_address2}' => $invoice_address->address2,
            '{invoice_address1}' => $invoice_address->address1,
            '{invoice_city}' => $invoice_address->city,
            '{invoice_postal_code}' => $invoice_address->postcode,
            '{invoice_country}' => $invoice_address->country,
            '{invoice_state}' => '',
            '{invoice_phone}' => ($invoice_address->phone) ? $invoice_address->phone : $invoice_address->phone_mobile,
            '{invoice_other}' => $invoice_address->other,
            '{order_name}' => $order->getUniqReference(),
            '{date}' => Tools::displayDate(date('Y-m-d H:i:s'), null, 1),
            '{carrier}' => $virtual_product ? $this->l('No carrier') : $carrier_obj->name,
            '{payment}' => Tools::substr($order->payment, 0, 32),
            '{products}' => $products_list,
            '{discounts}' => $cart_rules_list,
            '{total_paid}' => Tools::displayPrice($order->total_paid, $currency, false),
            '{total_products}' => Tools::displayPrice(
                $order->total_paid - $order->total_shipping - $order->total_wrapping + $order->total_discounts,
                $currency,
                false
            ),
            '{total_discounts}' => Tools::displayPrice($order->total_discounts, $currency, false),
            '{total_shipping}' => Tools::displayPrice($order->total_shipping, $currency, false),
            '{total_wrapping}' => Tools::displayPrice($order->total_wrapping, $currency, false),
            '{total_tax_paid}' => Tools::displayPrice(
                ($order->total_products_wt - $order->total_products)
                + ($order->total_shipping_tax_incl - $order->total_shipping_tax_excl),
                $this->context->currency,
                false
            )
        );

        $result = Mail::Send(
            (int)$order->id_lang,
            'm_order_changed',
            $this->l('Order changed'),
            $data,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . 'orderedit/mails/',
            false,
            (int)$order->id_shop
        );

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('The email about the changed order has been sent!'),
            'error_msg' => $this->l('The email cannot be sent!'),
        ]));
    }

    public function ajaxProcessUpdateShipping()
    {
        $id_order_carrier = (int)Tools::getValue('id_order_carrier');
        $id_carrier = (int)Tools::getValue('id_carrier');
        $id_order = (int)Tools::getValue('id_order');
        $weight = (float)Tools::getValue('weight');
        $tax = (float)Tools::getValue('current_tax');
        $shipping_cost_tax_incl = Tools::ps_round((float)Tools::getValue('shipping_cost_tax_incl'), _PS_PRICE_COMPUTE_PRECISION_);
        $shipping_cost_tax_excl = Tools::ps_round((float)Tools::getValue('shipping_cost_tax_excl'), _PS_PRICE_COMPUTE_PRECISION_);
        $tracking_number = pSQL(Tools::getValue('tracking_number'));
        $date_add = date(Tools::getValue('date_add', '0000-00-00 00:00:00'));

        $inserted = [
            'id_carrier' => $id_carrier,
            'id_order' => $id_order,
            'weight' => $weight,
            'shipping_cost_tax_incl' => $shipping_cost_tax_incl,
            'shipping_cost_tax_excl' => $shipping_cost_tax_excl,
            'tracking_number' => $tracking_number,
            'date_add' => $date_add,
        ];

        $order = new Order($id_order);
        $order->id_carrier = $id_carrier;
        $result_order = $order->update();

        if ($id_order_carrier) {
            $result_carrier = Db::getInstance()->update('order_carrier', $inserted, 'id_order_carrier = ' . $id_order_carrier);
        } else {
            $order_carrier = new OrderCarrier();
            $order_carrier->id_order = $id_order;
            $order_carrier->id_carrier = $id_carrier;
            $order_carrier->weight = $weight;
            $order_carrier->shipping_cost_tax_incl = $shipping_cost_tax_incl;
            $order_carrier->shipping_cost_tax_excl = $shipping_cost_tax_excl;
            $order_carrier->tracking_number = $tracking_number;
            $order_carrier->date_add = $date_add;
//            $order_carrier->id_order_invoice = $id_order_invoice; // #FIXME - xz where are the field
            $result_carrier = $order_carrier->save();
        }

        $result_orders = $this->updateOrderCarriers($id_order, $shipping_cost_tax_excl, $shipping_cost_tax_incl, $tax);
        $result_invoice = $this->updateOrderInvoiceCarriers($id_order, $shipping_cost_tax_excl, $shipping_cost_tax_incl);

        die(json_encode([
            'success' => $result_order && $result_carrier && $result_orders && $result_invoice,
            'success_msg' => $this->l('Order shipping has been changed!'),
            'error_msg' => $this->l('Cannot change this shipping!'),
        ]));
    }

    private function updateOrderCarriers($id_order, $shipping_cost_tax_excl, $shipping_cost_tax_incl, $tax)
    {
        $order = new Order($id_order);

        OrderEditHelper::saveOriginalOrderBeforeChangeIt($order);

        $order->total_shipping_tax_excl = $shipping_cost_tax_excl;
        $order->total_shipping_tax_incl = $shipping_cost_tax_incl;
        $order->total_shipping = $shipping_cost_tax_incl;
        $order->total_paid = Tools::ps_round(
            $order->total_products_wt + $order->total_wrapping_tax_incl - $order->total_discounts_tax_incl + $shipping_cost_tax_incl,
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_excl = Tools::ps_round(
            $order->total_products + $order->total_wrapping_tax_excl - $order->total_discounts_tax_excl + $shipping_cost_tax_excl,
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_incl = $order->total_paid;
        $order->carrier_tax_rate = $tax;
        return $order->save();
    }

    private function updateOrderInvoiceCarriers($id_order, $shipping_cost_tax_excl, $shipping_cost_tax_incl)
    {
        $order = new Order($id_order);
        if ($order->invoice_number) { // #FIXME - there may be a problem if the order has more than one invoice.
            $id_order_invoice = Db::getInstance()->getValue('SELECT `id_order_invoice` FROM `' . _DB_PREFIX_ . 'order_invoice` WHERE number = ' . $order->invoice_number);
            $order_invoice = new OrderInvoice($id_order_invoice);
            $order_invoice->total_shipping_tax_excl = $shipping_cost_tax_excl;
            $order_invoice->total_shipping_tax_incl = $shipping_cost_tax_incl;
            $order_invoice->total_paid_tax_excl = $order->total_paid_tax_excl;
            $order_invoice->total_paid_tax_incl = $order->total_paid_tax_incl;
            $order_invoice->total_products = $order->total_products;
            $order_invoice->total_products_wt = $order->total_products_wt;
            return $order_invoice->save();
        }
        return true;
    }

    public function ajaxProcessDeleteOrderDetail()
    {
        $id_order = (int)Tools::getValue('id_order');
        $id_order_detail = (int)Tools::getValue('id_order_detail');
        $order_detail = new OrderDetail($id_order_detail);
        $result_returned = $this->changeProductInStock($order_detail->product_id, $order_detail->product_attribute_id, (int)$order_detail->product_quantity, $order_detail->id_shop);
        $result_detail = Db::getInstance()->delete('order_detail', 'id_order_detail = ' . $id_order_detail);
        $result_order = $this->updateOrderTotals($id_order);
        $result_invoice = $this->updateOrderInvoiceTotals($id_order);
        $result_carrier = $this->updateOrderCarrierWeight($id_order);

        die(json_encode([
            'success' => $result_returned && $result_detail && $result_order && $result_invoice && $result_carrier,
            'success_msg' => $this->l('Order detail removed!'),
            'error_msg' => $this->l('Cannot delete order detail!'),
        ]));
    }

    private function changeProductInStock($product_id, $product_attribute_id, $product_quantity, $id_shop)
    {
        $update_quantity = true;
        if (Configuration::get('PS_STOCK_MANAGEMENT')) {
            $update_quantity = StockAvailable::updateQuantity($product_id, $product_attribute_id, (int)$product_quantity, $id_shop, true);
        }

        return $update_quantity;
    }

    public function ajaxProcessSendOrderChangeMessage()
    {
        $id_order = (int)Tools::getValue('id_order');
        $order = new Order($id_order);
        $customer = new Customer((int)$order->id_customer);
        $old_order = OrderEditHelper::getOrderBeforeLastChange($id_order);
        $msg = Tools::getValue('msg');
        $data = array(
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{email}' => $customer->email,
            '{msg_txt}' => $msg,
            '{msg_html}' => nl2br($msg),
            '{order_name}' => $order->reference,
            '{date}' => $order->date_add,
            '{payment}' => $order->payment,
            '{total_products_old}' => Tools::displayPrice($old_order['total_products_wt']),
            '{total_products_new}' => Tools::displayPrice($order->total_products_wt),
            '{total_discounts_old}' => Tools::displayPrice($old_order['total_discounts']),
            '{total_discounts_new}' => Tools::displayPrice($order->total_discounts),
            '{total_wrapping_old}' => Tools::displayPrice($old_order['total_wrapping']),
            '{total_wrapping_new}' => Tools::displayPrice($order->total_wrapping),
            '{total_shipping_old}' => Tools::displayPrice($old_order['total_shipping']),
            '{total_shipping_new}' => Tools::displayPrice($order->total_shipping),
            '{total_paid_old}' => Tools::displayPrice($old_order['total_paid']),
            '{total_paid_new}' => Tools::displayPrice($order->total_paid),
            '{total_surcharge}' => Tools::displayPrice($order->total_paid - $old_order['total_paid']),
        );

        $result = Mail::Send(
            (int)$order->id_lang,
            'extra_charge',
            $this->l('Extra charge after a change of order'),
            $data,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . 'orderedit/mails/',
            false,
            (int)$order->id_shop
        );

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Email to customer sent!'),
            'error_msg' => $this->l('Cannot send the email!'),
        ]));
    }

    public function ajaxProcessUpdateWrapping()
    {
        $id_order = (int)Tools::getValue('id_order');
        $total_wrapping_tax_excl = (float)Tools::getValue('total_wrapping_tax_excl');
        $total_wrapping_tax_incl = (float)Tools::getValue('total_wrapping_tax_incl');

        $order = new Order($id_order);
        $order->total_wrapping_tax_excl = Tools::ps_round($total_wrapping_tax_excl, _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_wrapping_tax_incl = Tools::ps_round($total_wrapping_tax_incl, _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_wrapping = Tools::ps_round($total_wrapping_tax_incl, _PS_PRICE_COMPUTE_PRECISION_);
        $order->total_paid = Tools::ps_round(
            $order->total_products_wt + $order->total_wrapping_tax_incl - $order->total_discounts_tax_incl + $order->total_shipping_tax_incl,
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_excl = Tools::ps_round(
            $order->total_products + $order->total_wrapping_tax_excl - $order->total_discounts_tax_excl + $order->total_shipping_tax_excl,
            _PS_PRICE_COMPUTE_PRECISION_
        );
        $order->total_paid_tax_incl = $order->total_paid;
        $result = $order->save();

        die(json_encode([
            'success' => $result,
            'success_msg' => $this->l('Order wrapping changed!'),
            'error_msg' => $this->l('Cannot to change the order wrapping!'),
        ]));
    }

    public function ajaxProcessUpdateOrderMainData()
    {
        $result_cart = true;
        $id_order = (int)Tools::getValue('id_order');
        $id_customer = (int)Tools::getValue('id_customer');
        $date_add = Tools::getValue('date_add');
        $date_upd = Tools::getValue('date_upd');
        $reference = Tools::getValue('reference');

        $order = new Order($id_order);
        $order->date_add = $date_add;
        $order->date_upd = $date_upd;
        $order->reference = $reference;

        if ($id_customer && $order->id_customer != $id_customer) {
            $id_address = (int)Address::getFirstCustomerAddressId($id_customer, $active = true);
            if ($id_address) {
                $order->id_customer = $id_customer;
                $order->id_address_delivery = $id_address;
                $order->id_address_invoice = $id_address;

                $cart = new Cart((int)$order->id_cart);
                $cart->id_customer = $id_customer;
                $cart->id_address_delivery = $id_address;
                $cart->id_address_invoice = $id_address;
                $result_cart = $cart->update();
            }
        }

        $result_order = $order->save();

        die(json_encode([
            'success' => $result_order && $result_cart,
            'success_msg' => $this->l('Order main data was changed!'),
            'error_msg' => $this->l('Cannot to change the order main data!'),
        ]));
    }

    public function ajaxProcessDeleteShipping()
    {
        $id_order = (int)Tools::getValue('id_order');
        $id_order_carrier = (int)Tools::getValue('id_order_carrier');

        $result_carrier = Db::getInstance()->delete('order_carrier', 'id_order_carrier = ' . $id_order_carrier);

        $order = new Order($id_order);
        $order->id_carrier = 0;
        $result_order = $order->save();

        die(json_encode([
            'success' => $result_carrier && $result_order,
            'success_msg' => $this->l('Shipping removed!'),
            'error_msg' => $this->l('Cannot delete current shipping!'),
        ]));
    }

    public function ajaxProcessAddPayment()
    {
        $id_order = (int)Tools::getValue('id_order');
        $id_currency = (int)Tools::getValue('id_currency');
        $id_order_invoice = (int)Tools::getValue('id_order_invoice');
        $amount = (float)Tools::getValue('amount');
        $payment_method = pSQL(Tools::getValue('payment_method')); // #todo this will be always the last saved payment name!!
        $payment_module = pSQL(Tools::getValue('payment_module'));
        $transaction_id = pSQL(Tools::getValue('transaction_id'));
        $date_add = pSQL(Tools::getValue('date_add'));
        $dateTime = new DateTime($date_add);
        $date_add_converted = $dateTime->format('Y-m-d H:i:s');

        $order = new Order($id_order);
        $order->payment = $payment_method;
        if ($payment_module) {
            $order->module = $payment_module;
        }
        $result_order = $order->save();

        $payment = new OrderPayment();
        $payment->order_reference = $order->reference;
        $payment->id_currency = $id_currency;
        $payment->amount = 0;
        $payment->payment_method = $payment_method;
        $payment->transaction_id = $transaction_id;
        $payment->date_add = $date_add_converted;
        $result_payment_1 = $payment->save(false, false);

        /** #TODO Because otherwise a negative value would not be recorded */
        $result_payment_2 = Db::getInstance()->update('order_payment', array('amount' => $amount), 'id_order_payment = ' . (int)$payment->id);

        $invoice_payment = array(
            'id_order_invoice' => $id_order_invoice,
            'id_order_payment' => (int)$payment->id,
            'id_order' => $id_order,
        );

        $result_invoice_payment = Db::getInstance()->insert('order_invoice_payment', $invoice_payment);

        die(json_encode([
            'success' => $result_order && $result_payment_1 && $result_payment_2 && $result_invoice_payment,
            'success_msg' => $this->l('Payment saved!'),
            'error_msg' => $this->l('Cannot save payment!'),
        ]));
    }

    private function getTaxIdByTaxRate($tax_rate)
    {
        $tax = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT `id_tax`
			FROM `' . _DB_PREFIX_ . 'tax` 
			WHERE `rate` = ' . (float)$tax_rate . ' AND `active` = 1');

        return $tax ? (int)$tax['id_tax'] : false;
    }
}
