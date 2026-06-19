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
class ApiGetPsordersById extends Core
{
    public function getData()
    {
        $id_order = (int) Tools::getValue('id_order');

        if (!$id_order) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order ID is missing or invalid'
            );
            return $this->fetchJSONResponse();
        }


        $order = new Order($id_order);
        $orderDetails = $this->getOrderDetails($order);
        $orderDetails['detail'] = $order;
        if (!Validate::isLoadedObject($order)) {
            $this->response['response'] = array(
                'status' => 'failure',
                'message' => 'Order not found'
            );
            return $this->fetchJSONResponse();
        }

        $this->response['response'] = array(
            'status' => 'success',
            'message' => 'Order retrieved successfully',
            'data' => $orderDetails
        );

        return $this->fetchJSONResponse();
    }


    private function getOrderDetails(Order $order)
    {
        if (!(int) Tools::getValue('id_language')) {
            $id_language = $this->context->language->id;
        } else {
            $id_language = (int) Tools::getValue('id_language');
        }
        // Get customer details
        $customer = new Customer($order->id_customer);
        $customerDetails = array(
            'id_customer' => $customer->id,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'email' => $customer->email
        );

        // Get order products
        $products = $order->getProducts();
        
        $productDetails = array();
        foreach ($products as $product) {
            
            $product_id = $product['product_id'];
            $product_attribute_id = $product['product_attribute_id'];
            $productDetails[] = array(
                'id_product' => $product['product_id'],
                'product_name' => $product['product_name'],
                'product_quantity' => $product['product_quantity'],
                'product_data' => $product,
                'product_price' => $product['unit_price_tax_incl']
            );
        }

        // Get order addresses
        $addressDelivery = new Address($order->id_address_delivery);
        $addressInvoice = new Address($order->id_address_invoice);
        $deliveryAddress = array(
            'address1' => $addressDelivery->address1,
            'address2' => $addressDelivery->address2,
            'city' => $addressDelivery->city,
            'postcode' => $addressDelivery->postcode,
            'country' => $addressDelivery->country
        );
        $invoiceAddress = array(
            'address1' => $addressInvoice->address1,
            'address2' => $addressInvoice->address2,
            'city' => $addressInvoice->city,
            'postcode' => $addressInvoice->postcode,
            'country' => $addressInvoice->country
        );

        // Get shipping details
        $carrier = new Carrier($order->id_carrier);
        $shippingDetails = array(
            'carrier_name' => $carrier->name,
            'shipping_cost' => $order->total_shipping
        );

        // Get order payment details
        $payments = $order->getOrderPayments();
        $paymentDetails = array();
        foreach ($payments as $payment) {
            $paymentDetails[] = array(
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'amount' => $payment->amount,
                'date' => $payment->date_add
            );
        }

        $sql = new DbQuery();
        $sql->select('oh.id_order_state, os.name as status, oh.date_add');
        $sql->from('order_history', 'oh');
        $sql->leftJoin('order_state_lang', 'os', 'oh.id_order_state = os.id_order_state AND os.id_lang = ' . (int)$this->context->language->id);
        $sql->where('oh.id_order = ' . (int)$order->id);
        $sql->orderBy('oh.date_add ASC');

        $orderStatusHistory = Db::getInstance()->executeS($sql);

        // Compile all order details
        $orderDetails = array(
            'id_order' => $order->id,
            'order_states' => $orderStatusHistory,
            'reference' => $order->reference,
            'total_paid' => $order->total_paid,
            'date_add' => $order->date_add,
            'customer' => $customerDetails,
            'products' => $productDetails,
            'delivery_address' => $deliveryAddress,
            'invoice_address' => $invoiceAddress,
            'shipping' => $shippingDetails,
            'payments' => $paymentDetails
        );


        return $orderDetails;
    }

}
