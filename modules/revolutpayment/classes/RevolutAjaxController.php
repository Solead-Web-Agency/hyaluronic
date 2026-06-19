<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

class RevolutAjaxController
{
    private $context;
    private $module;

    public function __construct()
    {
        $this->checkToken();

        $this->context = Context::getContext();
        if (!Validate::isLoadedObject($this->context->cart) && !empty((int)Tools::getValue('ps_cart_id'))) {
            $this->context->cart = new Cart((int)Tools::getValue('ps_cart_id'));
        }

        $this->module = new RevolutPayment();

        $action = Tools::getValue('action');

        if (empty($action)) {
            return;
        }

        try {
            $this->$action();
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Revolut Payment Buttons Error: ' . $e->getMessage(), 3);
            $this->jsonDie(array(
                'status' => 'fail',
                'message' => $e->getMessage(),
                'success' => false,
                'refresh_token' => Tools::getToken(false),
                'shippingOptions' => array(),
                'total' => array(
                    'amount' => 0
                ),
            ));
        }
    }

    public function actionClearCart()
    {
        if (Validate::isLoadedObject($this->context->cart)) {
            $cart = $this->context->cart;

            if (!$cart->id) {
                return false;
            }

            $products = $cart->getProducts(true);

            if (empty($products)) {
                return false;
            }

            foreach ($products as $product) {
                if ($this->context->cart->deleteProduct(
                    $product['id_product'],
                    isset($product['id_product_attribute']) ? $product['id_product_attribute'] : 0,
                    isset($product['customization_id']) ? $product['customization_id'] : 0
                )) {
                    if (!Cart::getNbProducts((int)$this->context->cart->id)) {
                        $this->context->cart->setDeliveryOption(null);
                        $this->context->cart->gift = 0;
                        $this->context->cart->gift_message = '';
                        $this->context->cart->update();
                    }
                }
            }
        }
    }

    public function actionUpdateOrderTotal()
    {
        $this->updateRevolutOrder();

        return $this->jsonDie(array(
            'status' => 'success',
            'success' => true,
            'total' => array(
                'amount' => $this->module->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
            ),
        ));
    }

    public function actionUpdateShippingOption()
    {
        $id_carrier = Tools::getValue('id_carrier');

        if (!$id_carrier) {
            throw new Exception('Carrier is not available');
        }

        $this->activateAddress($this->context->cart->id_address_delivery);
        $deliveryOption = [$this->context->cart->id_address_delivery => $this->getPsCarrierId($id_carrier) . ','];
        $this->context->cart->setDeliveryOption($deliveryOption);
        $this->context->cart->save();

        $this->updateRevolutOrder();

        return $this->jsonDie(array(
            'status' => 'success',
            'success' => true,
            'refresh_token' => Tools::getToken(false),
            'total' => array(
                'amount' => $this->module->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
            ),
        ));
    }

    public function actionGetShippingOptions()
    {
        if ($this->context->customer->id) {
            $this->context->cart->id_customer = $this->context->customer->id;
        }

        $this->context->cart->id_address_delivery = $this->createAnonymousDeliveryAddress();
        $this->context->cart->id_address_invoice = $this->context->cart->id_address_delivery;
        $this->activateAddress($this->context->cart->id_address_delivery);
        foreach ($this->context->cart->getProducts() as &$product) {
            $this->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $this->context->cart->id_address_delivery);
        }

        $this->context->cart->save();

        $carrier_list = $this->context->cart->getDeliveryOptionList(null, true);
        $carrier_list = $this->formatCarrierList($carrier_list);
        if (!empty($carrier_list)) {
            $deliveryOption = [$this->context->cart->id_address_delivery => $this->getPsCarrierId($carrier_list[0]['id']) . ','];
            $this->context->cart->setDeliveryOption($deliveryOption);
            $this->context->cart->save();
        }

        $this->updateRevolutOrder();

        return $this->jsonDie(array(
            'status' => 'success',
            'success' => true,
            'shippingOptions' => $carrier_list,
            'refresh_token' => Tools::getToken(false),
            'total' => array(
                'amount' => $this->module->createRevolutAmount($this->context->cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
            ),
        ));
    }

    public function actionCreateOrder()
    {
        $order_data = Tools::getAllValues();

        $public_id = $order_data['revolut_public_id'];

        if (empty($public_id)) {
            throw new Exception('Public ID is missing for the session');
        }

        $order_id = $this->module->getRevolutOrderByIdCart($this->context->cart->id);

        if (empty($order_id)) {
            throw new Exception('Can not find revolut order id');
        }

        if (empty($order_data['address'])) {
            throw new Exception('Address information is missing');
        }

        $address_info = $order_data['address'];

        if (empty($address_info['billingAddress'])) {
            throw new Exception('Billing address is missing');
        }

        if (empty($address_info['shippingAddress']) && !$this->context->cart->isVirtualCart()) {
            throw new Exception('Shipping address is missing');
        }

        if (empty($address_info['email'])) {
            throw new Exception('User Email information is missing');
        }

        $id_customer = $this->createCustomer($address_info);
        $customer = new Customer($id_customer);

        if (!$this->context->customer->id || $this->context->customer->id != $id_customer) {
            $this->updateCustomer($customer);
        }

        $id_address_invoice = $this->createInvoiceAddress($address_info, $id_customer);

        if ($this->context->cart->isVirtualCart()) {
            $id_address_delivery = $id_address_invoice;
        } else {
            $id_address_delivery = $this->createDeliveryAddress($address_info, $id_customer);
        }

        $this->context->cart->id_customer = $id_customer;
        $this->context->cart->id_address_invoice = $id_address_invoice;
        $this->context->cart->id_address_delivery = $id_address_delivery;
        $this->context->cart->secure_key = $customer->secure_key;
        $this->context->cart->save();

        $this->jsonDie([
            'status' => 'success',
            'ps_cart_id' => $this->context->cart->id,
            'refresh_token' => Tools::getToken(false),
            'ps_customer_id' => $this->context->customer->id,
        ]);
    }

    public function actionFinaliseOrder()
    {
        $public_id = Tools::getValue('revolut_public_id');
        $id_cart = (int)Tools::getValue('ps_cart_id');

        $this->context->cart = new Cart($id_cart);
        $revolut_order = $this->module->getRevolutOrderByIdCart($this->context->cart->id);

        if (empty($public_id) || empty($revolut_order['id_revolut_order']) || $revolut_order['public_id'] != $public_id) {
            throw new Exception('Invalid Revolut Order');
        }

        $customer = new Customer($this->context->cart->id_customer);

        $ps_order_id = $this->module->createPrestaShopOrder($customer);

        //update order address again, because it gets first address from Product
        $order = new Order($ps_order_id);
        $order->id_address_delivery = $this->context->cart->id_address_delivery;
        $order->save();

        if (!$order->id) {
            throw new Exception('Invalid PrestaShop Order');
        }

        $this->module->processRevolutOrderResult($ps_order_id, $revolut_order);

        $customer = new Customer($order->id_customer);

        $this->jsonDie([
            'status' => 'success',
            'redirect_url' => $this->module->getOrderConfirmationLink($order->id_cart, $customer->secure_key, $ps_order_id),
        ]);
    }

    public function setProductAddressDelivery($id_product, $id_product_attribute, $old_id_address_delivery, $new_id_address_delivery)
    {
        if ($new_id_address_delivery == $old_id_address_delivery) {
            return true;
        }

        // Checking if the product with the old address delivery exists
        $sql = new DbQuery();
        $sql->select('count(*)');
        $sql->from('cart_product', 'cp');
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_address_delivery = ' . (int)$old_id_address_delivery);
        $sql->where('id_cart = ' . (int)$this->context->cart->id);
        $result = Db::getInstance()->getValue($sql);

        if ($result == 0) {
            return false;
        }

        // Checking if there is no others similar products with this new address delivery
        $sql = new DbQuery();
        $sql->select('sum(quantity) as qty');
        $sql->from('cart_product', 'cp');
        $sql->where('id_product = ' . (int)$id_product);
        $sql->where('id_product_attribute = ' . (int)$id_product_attribute);
        $sql->where('id_address_delivery = ' . (int)$new_id_address_delivery);
        $sql->where('id_cart = ' . (int)$this->context->cart->id);
        $result = Db::getInstance()->getValue($sql);

        // Removing similar products with this new address delivery
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'cart_product
			WHERE id_product = ' . (int)$id_product . '
			AND id_product_attribute = ' . (int)$id_product_attribute . '
			AND id_address_delivery = ' . (int)$new_id_address_delivery . '
			AND id_cart = ' . (int)$this->context->cart->id . '
			LIMIT 1';
        Db::getInstance()->execute($sql);

        // Changing the address
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart_product
			SET `id_address_delivery` = ' . (int)$new_id_address_delivery . ',
			`quantity` = `quantity` + ' . (int)$result . '
			WHERE id_product = ' . (int)$id_product . '
			AND id_product_attribute = ' . (int)$id_product_attribute . '
			AND id_address_delivery = ' . (int)$old_id_address_delivery . '
			AND id_cart = ' . (int)$this->context->cart->id . '
			LIMIT 1';
        Db::getInstance()->execute($sql);

        // Changing the address of the customizations
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customization
			SET `id_address_delivery` = ' . (int)$new_id_address_delivery . '
			WHERE id_product = ' . (int)$id_product . '
			AND id_product_attribute = ' . (int)$id_product_attribute . '
			AND id_address_delivery = ' . (int)$old_id_address_delivery . '
			AND id_cart = ' . (int)$this->context->cart->id;
        Db::getInstance()->execute($sql);

        return true;
    }

    public function parseCustomerName($full_name)
    {
        $full_name_list = explode(" ", $full_name);
        if (count($full_name_list) > 1) {
            $lastname = array_pop($full_name_list);
            $firstname = implode(" ", $full_name_list);
            return [$firstname, $lastname];
        }

        $firstname = $full_name;
        $lastname = "lastname";

        return [$firstname, $lastname];
    }

    public function createDeliveryAddress($address_info, $id_customer)
    {
        $revolut_shipping_address = $address_info['shippingAddress'];
        $revolut_billing_address = $address_info['billingAddress'];

        $revolut_customer_shipping_phone = !empty($revolut_shipping_address['phone']) ? $revolut_shipping_address['phone'] : '';
        $revolut_customer_shipping_postcode = !empty($revolut_shipping_address['postalCode']) ? $revolut_shipping_address['postalCode'] : '';
        $revolut_customer_shipping_full_name = !empty($revolut_shipping_address['recipient']) ? $revolut_shipping_address['recipient'] : '';

        $revolut_customer_full_name = !empty($revolut_billing_address['recipient']) ? $revolut_billing_address['recipient'] : '';
        $revolut_customer_billing_phone = !empty($revolut_billing_address['phone']) ? $revolut_billing_address['phone'] : '';

        $address1 = !empty($revolut_shipping_address['addressLine'][0]) ? $revolut_shipping_address['addressLine'][0] : '';
        $address2 = !empty($revolut_shipping_address['addressLine'][1]) ? $revolut_shipping_address['addressLine'][1] : '';

        if (empty($revolut_customer_shipping_full_name)) {
            $revolut_customer_shipping_full_name = $revolut_customer_full_name;
        }

        if (empty($revolut_customer_shipping_phone)) {
            $revolut_customer_shipping_phone = $revolut_customer_billing_phone;
        }

        list($shipping_firstname, $shipping_lastname) = $this->parseCustomerName($revolut_customer_shipping_full_name);

        $country_code = $revolut_shipping_address['country'];
        $state = $revolut_shipping_address['region'];
        $city = $revolut_shipping_address['city'];
        $id_country = Country::getByIso($country_code);
        $country = new Country($id_country);

        if (!$country->active) {
            throw new PrestaShopException('The Delivery address country is not active.');
        }

        $id_state = State::getIdByIso($state);

        if (empty($id_state)) {
            $id_state = State::getIdByName($state);
        }

        $alias = 'address';

        $id_exist_address = $this->getCustomerAddress($id_customer, $id_country, $id_state, $shipping_firstname, $shipping_lastname, $revolut_customer_billing_phone, $city, $alias, $address1, $address2, $revolut_customer_shipping_postcode);

        if ($id_exist_address) {
            return $id_exist_address;
        }

        $deliveryAddress = new Address();
        $deliveryAddress->firstname = $shipping_firstname;
        $deliveryAddress->lastname = $shipping_lastname;
        $deliveryAddress->phone = $revolut_customer_shipping_phone;
        $deliveryAddress->id_customer = $id_customer;
        $deliveryAddress->id_country = $id_country;
        $deliveryAddress->id_state = $id_state;
        $deliveryAddress->city = $city;
        $deliveryAddress->address1 = $address1;
        $deliveryAddress->address2 = $address2;
        $deliveryAddress->alias = $alias;
        $deliveryAddress->postcode = $revolut_customer_shipping_postcode;

        if (!$deliveryAddress->save()) {
            throw new Exception('Can not update delivery address');
        }

        return $deliveryAddress->id;
    }

    public function createInvoiceAddress($address_info, $id_customer)
    {
        $revolut_shipping_address = isset($address_info['shippingAddress']) ? $address_info['shippingAddress'] : [];
        $revolut_billing_address = isset($address_info['billingAddress']) ? $address_info['billingAddress'] : [];

        $revolut_customer_full_name = !empty($revolut_billing_address['recipient']) ? $revolut_billing_address['recipient'] : '';
        $revolut_customer_billing_phone = !empty($revolut_billing_address['phone']) ? $revolut_billing_address['phone'] : '';
        $revolut_customer_billing_postcode = !empty($revolut_billing_address['postalCode']) ? $revolut_billing_address['postalCode'] : '';

        $revolut_customer_shipping_full_name = !empty($revolut_shipping_address['recipient']) ? $revolut_shipping_address['recipient'] : '';
        $revolut_customer_shipping_phone = !empty($revolut_shipping_address['phone']) ? $revolut_shipping_address['phone'] : '';

        $address1 = !empty($revolut_billing_address['addressLine'][0]) ? $revolut_billing_address['addressLine'][0] : '';
        $address2 = !empty($revolut_billing_address['addressLine'][1]) ? $revolut_billing_address['addressLine'][1] : '';

        if (empty($revolut_customer_full_name)) {
            $revolut_customer_full_name = $revolut_customer_shipping_full_name;
        }

        if (empty($revolut_customer_billing_phone)) {
            $revolut_customer_billing_phone = $revolut_customer_shipping_phone;
        }

        list($billing_firstname, $billing_lastname) = $this->parseCustomerName($revolut_customer_full_name);

        $country_code = $revolut_billing_address['country'];
        $state = $revolut_billing_address['region'];
        $city = $revolut_billing_address['city'];
        $id_country = Country::getByIso($country_code);

        $country = new Country($id_country);

        if (!$country->active) {
            throw new PrestaShopException('The Invoice address country is not active.');
        }


        $id_state = State::getIdByIso($state);

        if (empty($id_state)) {
            $id_state = State::getIdByName($state);
        }

        $alias = 'address';

        $id_exist_address = $this->getCustomerAddress($id_customer, $id_country, $id_state, $billing_firstname, $billing_lastname, $revolut_customer_billing_phone, $city, $alias, $address1, $address2, $revolut_customer_billing_postcode);

        if ($id_exist_address) {
            return $id_exist_address;
        }

        $addressInvoice = new Address();
        $addressInvoice->id_customer = $id_customer;
        $addressInvoice->firstname = $billing_firstname;
        $addressInvoice->lastname = $billing_lastname;
        $addressInvoice->phone = $revolut_customer_billing_phone;
        $addressInvoice->id_country = $id_country;
        $addressInvoice->id_state = $id_state;
        $addressInvoice->city = $city;
        $addressInvoice->alias = $alias;
        $addressInvoice->address1 = $address1;
        $addressInvoice->address2 = $address2;
        $addressInvoice->postcode = $revolut_customer_billing_postcode;
        $res = $addressInvoice->save();

        try {
            $res = $addressInvoice->save();
        } catch (Exception $e) {
            PrestaShopLogger::addLog($e->getMessage(), 3);
        }

        if (!$res) {
            throw new Exception('Can not create invoice address for the order');
        }

        return $addressInvoice->id;
    }

    public function createCustomer($address_info)
    {
        if ($this->context->customer->id) {
            return $this->context->customer->id;
        }

        $revolut_customer_email = $address_info['email'];

        $exist_customer_id = $this->getCustomerByEmail($revolut_customer_email);

        if ($exist_customer_id) {
            return $exist_customer_id;
        }

        $revolut_shipping_address = $address_info['shippingAddress'];
        $revolut_billing_address = $address_info['billingAddress'];


        $revolut_customer_full_name = !empty($revolut_billing_address['recipient']) ? $revolut_billing_address['recipient'] : '';
        $revolut_customer_shipping_full_name = !empty($revolut_shipping_address['recipient']) ? $revolut_shipping_address['recipient'] : '';

        if (empty($revolut_customer_full_name)) {
            $revolut_customer_full_name = $revolut_customer_shipping_full_name;
        }

        list($billing_firstname, $billing_lastname) = $this->parseCustomerName($revolut_customer_full_name);

        $customer = new Customer();

        $customer->is_guest = 1;
        $customer->lastname = $billing_lastname;
        $customer->firstname = $billing_firstname;
        $customer->email = $revolut_customer_email;
        $customer->passwd = Tools::passwdGen(8, 'RANDOM');
        $customer->addGroups([Configuration::get('PS_CUSTOMER_GROUP')]);
        $customer->id_default_group = Configuration::get('PS_CUSTOMER_GROUP');
        $res = $customer->save();
        if (!$res) {
            throw new Exception('Can not create PrestaShop Customer');
        }

        return $customer->id;
    }

    public function createAnonymousDeliveryAddress()
    {
        $country_code = Tools::getValue('country');
        $state = Tools::getValue('state');
        $city = Tools::getValue('city');
        $postcode = Tools::getValue('postcode');
        $id_country = Country::getByIso($country_code);
        $country = new Country($id_country);

        if (!$country->active) {
            throw new PrestaShopException('The Delivery address country is not active.');
        }

        $id_state = State::getIdByIso($state);

        if (empty($id_state)) {
            $id_state = State::getIdByName($state);
        }

        if (!$id_country) {
            throw new Exception('Location is not available');
        }

        $id_exist_address = $this->getCustomerAddress((int)$this->context->customer->id, $id_country, $id_state, 'unknown', 'unknown', '', $city, 'unknown', 'unknown', '', $postcode);

        if ($id_exist_address) {
            return $id_exist_address;
        }

        $address = new Address();
        $address->firstname = 'unknown';
        $address->lastname = 'unknown';
        $address->alias = 'unknown';
        $address->id_country = $id_country;
        $address->id_state = $id_state;
        $address->postcode = $postcode;
        $address->city = $city;
        $address->id_customer = (int)$this->context->customer->id;
        $address->address1 = 'unknown';
        $res = $address->save();
        if (!$res) {
            throw new Exception('Can not create delivery address for the order');
        }

        return $address->id;
    }

    public function activateAddress($id_address)
    {
        Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'address` set active = 1, deleted = 0 WHERE id_address=' . (int)$id_address);
    }

    public function getCustomerAddress($id_customer, $id_country, $id_state, $firstname, $lastname, $phone, $city, $alias, $address1, $address2, $postcode)
    {
        return (int)Db::getInstance()->getValue('SELECT id_address FROM `' . _DB_PREFIX_ . 'address` WHERE 
        id_customer=' . (int)$id_customer . ' AND
        id_country=' . (int)$id_country . ' AND
        id_state=' . (int)$id_state . ' AND
        firstname="' . pSQL($firstname) . '" AND
        lastname="' . pSQL($lastname) . '" AND
        phone="' . pSQL($phone) . '" AND
        city="' . pSQL($city) . '" AND
        alias="' . pSQL($alias) . '" AND
        address1="' . pSQL($address1) . '" AND
        address2="' . pSQL($address2) . '" AND
        postcode="' . pSQL($postcode) . '"');
    }

    public function updateCustomer($customer)
    {
        if ($this->module->isPs17) {
            $this->context->updateCustomer($customer);
        }

        if (!$this->context->customer->id || $this->context->customer->id != $customer->id) {
            $this->context->customer->id = $customer->id;
            $this->context->customer = $customer;
            $this->context->cookie->customer_lastname = $customer->lastname;
            $this->context->cookie->customer_firstname = $customer->firstname;
            $this->context->cookie->is_guest = $customer->isGuest();
            $this->context->cookie->passwd = $customer->passwd;
            $this->context->cookie->email = $customer->email;
            $this->context->cart->secure_key = $customer->secure_key;
            $this->context->cookie->write();
        }
    }

    public function getCustomerByEmail($email)
    {
        return (int)Db::getInstance()->getValue('SELECT id_customer FROM `' . _DB_PREFIX_ . 'customer` WHERE email = "' . pSQL($email) . '"');
    }

    public function updateRevolutOrder()
    {
        $revolut_public_id = Tools::getValue('revolut_public_id');
        $amount = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $currency = $this->context->currency->iso_code;
        $revolut_order = $this->module->getRevolutOrderByIdCart($this->context->cart->id);
        $update_params = array(
            'amount' => $this->module->createRevolutAmount($amount, $currency),
            'currency' => $currency,
        );

        $public_id = $this->module->revolutApi->updateRevolutOrder($revolut_order['id_revolut_order'], $update_params);

        if ($revolut_public_id != $public_id) {
            throw new Exception('Can not update order');
        }
    }

    public function formatCarrierList($deliveryOptionList)
    {
        $carrier_list = [];

        if (count($deliveryOptionList) > 0) {
            foreach ($deliveryOptionList as $option) {
                foreach ($option as $carriers) {
                    foreach ($carriers['carrier_list'] as $carrier) {
                        if (empty($carrier['instance'])) {
                            continue;
                        }
                        $carrierInstance = $carrier['instance'];
                        $carrier_option = array();
                        $carrier_option['id'] = $this->convertCarrierId($carrierInstance->id);
                        $carrier_option['label'] = $carrierInstance->name;
                        $carrier_option['description'] = '';
                        $carrier_option['amount'] = $this->module->createRevolutAmount($carrier['price_with_tax'], $this->context->currency->iso_code);
                        $carrier_list[] = $carrier_option;
                    }
                }
            }
        }

        return $carrier_list;
    }

    public function convertCarrierId($id_carrier)
    {
        return 'flat_rate:' . $id_carrier;
    }

    public function getPsCarrierId($id_carrier)
    {
        return explode(':', $id_carrier)[1];
    }

    public function checkToken()
    {
        //do not prevent order creation process after charging customer
        if (!$this->isTokenValid() && Tools::getValue('action') != 'actionFinaliseOrder') {
            PrestaShopLogger::addLog('Revolut Payment Buttons Error: Token is not valid', 3);
            $data = [];
            $data['status'] = 'fail';
            $data['success'] = false;
            $data['total']['amount'] = 0;
            $data['refresh_token'] = Tools::getToken(false);
            $data['message'] = 'Impossible to process the order. Please refresh page.';
            $this->jsonDie($data);
        }
    }

    public function isTokenValid()
    {
        if (!Configuration::get('PS_TOKEN_ENABLE')) {
            return true;
        }

        return (strcasecmp(Tools::getToken(false), Tools::getValue('token')) == 0);
    }

    public function jsonDie($data)
    {
        if (defined('REVOLUT_MODULE_TESTSUITE') && REVOLUT_MODULE_TESTSUITE) {
            return $data;
        }

        print_r(Tools::jsonEncode($data));
        die;
    }
}
