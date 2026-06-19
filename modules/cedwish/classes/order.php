<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Team <sales@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @order  Ced
 * @package   CedWish
 */

require_once _PS_MODULE_DIR_ . 'cedwish/classes/queue.php';

class CedWishOrder extends ObjectModel
{
    public static $definition = array(
        'table' => 'cedwish_order',
        'primary' => 'id_cedwish_order',
        'multilang' => false,
        'fields' => array(
            'id_cedwish_order' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'store_order_id' => array(
                'type' => self::TYPE_INT,
                'db_type' => 'int'
            ),
            'state' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'marketplace_order_id' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'released_at' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'order_error' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
            'wish_order' => array(
                'type' => self::TYPE_STRING,
                'db_type' => 'text'
            ),
        ),
    );

    public $id_cedwish_order;
    public $store_order_id;
    public $marketplace_order_id;
    public $order_error;
    public $released_at;
    public $state;
    public $wish_order;

    public function __construct($id_cedwish_order = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id_cedwish_order, $idLang, $idShop);
    }

    public function createOrder($orders)
    {
        $errors = array();
        $total_created_orders = 0;
        $total_new_to_orders = 0;
        if (isset($orders) && !empty($orders)) {
            foreach ($orders as $order) {
                if (isset($order['id']) && $order['id'] && !$this->isOrderAlreadyExist($order['id'])) {
                    if (!empty($order)
                        && isset($order['product_information'])
                        && !empty($order['product_information'])
                    ) {
                        $order_object = new self();
                        $order_object->wish_order = pSQL(json_encode($order));
                        $order_object->marketplace_order_id = pSQL($order['id']);
                        $order_object->state = pSQL($order['state']);
                        $order_object->released_at = pSQL($order['released_at']);

                        try {
                            $order_object->add();
                            $total_new_to_orders++;
                            $prestashopOrderId = $this->createPrestashopOrder($order);
                            if ((int)$prestashopOrderId) {
                                $order_object->store_order_id = (int)$prestashopOrderId;
                                $order_object->order_error = "";
                                $order_object->id_cedwish_order = (int)$order_object->id;
                                try {
                                    $order_object->update();
                                    $total_created_orders[] = (int)$prestashopOrderId;
                                } catch (PrestaShopDatabaseException $e) {
                                    $errors[] = 'Some Error while order creation';
                                    CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
                                    continue;
                                } catch (PrestaShopException $e) {
                                    $errors[] = 'Some Error while order creation';
                                    CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
                                    continue;
                                }
                            } else {
                                $errors[] = 'Failed to Create Order With Id ' . $order['id'] . ' Please check Error 
                                and correct from order grid';
                            }
                        } catch (PrestaShopDatabaseException $e) {
                            $errors[] = $e->getMessage();
                            CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
                            continue;
                        } catch (PrestaShopException $e) {
                            $errors[] = $e->getMessage();
                            CedWishHelper::addLog($e->getMessage().$e->getTraceAsString());
                            continue;
                        }
                    }
                } elseif (isset($order['id'])) {
                    $errors[] = 'Already Created Order With Id ' . $order['id'] . ' OR order with error';
                }
            }
        }
        return array(
            'success' => $total_created_orders,
            'error' => $total_new_to_orders,
            'response' => $errors,
        );
    }

    public function isOrderAlreadyExist($id)
    {
        if ($id) {
            $id_cedwish_order = Db::getInstance()->getValue(
                "SELECT `id_cedwish_order` FROM `" . _DB_PREFIX_ . "cedwish_order` 
                WHERE `marketplace_order_id` LIKE '" . pSQL($id) . "'"
            );
            if ((int)$id_cedwish_order) {
                return true;
            }
        }
        return false;
    }

    public function createPrestashopOrder($order)
    {
        try {
            $wish_order_id = isset($order['id']) ? $order['id'] : '';
            $product_information = isset($order['product_information']) ? $order['product_information'] : array();
            $available_items = $this->validateItems(
                $product_information,
                $order['order_payment']['general_payment_details']
            );

            if (isset($available_items['error']) && !empty($available_items['error'])) {
                foreach ($available_items['error'] as $error) {
                    $this->orderErrorInformation(
                        $order['id'],
                        $error['message']
                    );
                }
            } elseif (isset($available_items['success']) && !empty($available_items['success'])) {
                $context = Context::getContext()->cloneContext();
                $orderId = $order['id'];

                $firstName = false;
                $lastName = false;

                if (isset($order['full_address']['shipping_detail']['name'])
                    && $order['full_address']['shipping_detail']['name']
                ) {
                    $name = $order['full_address']['shipping_detail']['name'];
                    $name = explode(" ", $name, 2);
                    if (isset($name['0']) && $name['0']) {
                        $firstName = $name['0'];
                    } else {
                        $firstName = 'Wish';
                    }

                    if (isset($name['1']) && $name['1']) {
                        $lastName = $name['1'];
                    } else {
                        $lastName = 'Customer';
                    }
                }

                $validityPattern = Tools::cleanNonUnicodeSupport(
                    '/^(?:[^0-9!<>,;?=+()\/\\@#"°*`{}_^$%:¤\[\]|\.。]|[\.。](?:\s|$))*$/u'
                );

                $isValid = preg_match($validityPattern, $firstName);
                if (!$isValid) {
                    $firstName = preg_replace('/[^a-zA-z .]/', '', $firstName);
                }

                $isValid = preg_match($validityPattern, $lastName);
                if (!$isValid) {
                    $lastName = preg_replace('/[^a-zA-z .]/', '', $lastName);
                }

                $email = Configuration::get('CED_WISH_ORDER_EMAIL')
                    ? Configuration::get('CED_WISH_ORDER_EMAIL') :
                    $wish_order_id . '@wish.com';

                $customer = $this->createCustomer($email, $firstName, $lastName);
                if ($customer && $customer->id) {
                    $context->customer = $customer;
                } else {
                    $this->orderErrorInformation(
                        $order['id'],
                        'Customer Name is not valid.'
                    );
                    return false;
                }

                $shippingAddress = false;
                if (isset($order['full_address']['shipping_detail'])
                    && !empty($order['full_address']['shipping_detail'])
                ) {
                    $shippingAddress = $this->addShippingAddress(
                        $context->customer,
                        $order['full_address']['shipping_detail'],
                        $firstName,
                        $lastName
                    );
                } else {
                    $this->orderErrorInformation(
                        $order['id'],
                        'Address is not valid.'
                    );
                    return false;
                }

                $paymentModule = Configuration::get('CED_WISH_ORDER_PAYMENT')
                    ? Configuration::get('CED_WISH_ORDER_PAYMENT') : 'Wish.com';

                $store_carrier_id = Configuration::get('CED_WISH_ORDER_CARRIER')
                    ? Configuration::get('CED_WISH_ORDER_CARRIER') : 'Wish';

                if (Configuration::get('CED_WISH_CURRENCY_ID')) {
                    $idCurrency = Configuration::get('CED_WISH_CURRENCY_ID');
                } elseif (Configuration::get('PS_CURRENCY_DEFAULT')) {
                    $idCurrency = Configuration::get('PS_CURRENCY_DEFAULT');
                } else {
                    $idCurrency = isset($context->currency->id) ? $context->currency->id : 0;
                }

                if ($shippingAddress && $context->customer->id) {
                    $extraVars = $this->createCart(
                        $shippingAddress,
                        $context->customer,
                        $available_items['success'],
                        $store_carrier_id,
                        $idCurrency
                    );
                    if ($extraVars) {
                        $extraVars['merchant_order_id'] = $orderId;
                        $extraVars['customer_reference_order_id'] = $orderId;

                        $secureKey = false;
                        $id_shop = (int)$context->shop->id;
                        $shop = new Shop($id_shop);

                        if (!empty($extraVars['productArray'])) {
                            $prestashop_order_id = $this->addOrderInPrestashop(
                                $extraVars['cart'],
                                $context->customer->id,
                                $shippingAddress->id,
                                $shippingAddress->id,
                                $store_carrier_id,
                                $idCurrency,
                                $extraVars,
                                $extraVars['productArray'],
                                $secureKey,
                                $context,
                                $shop,
                                $paymentModule,
                                $order['state'],
                                $order
                            );

                            if ((int)$prestashop_order_id) {
                                return (int)$prestashop_order_id;
                            } else {
                                $this->orderErrorInformation(
                                    $order['id'],
                                    "Failed to create Order."
                                );
                                return false;
                            }
                        } else {
                            $this->orderErrorInformation(
                                $order['id'],
                                "Not able to create Cart."
                            );
                            return false;
                        }
                    } else {
                        $this->orderErrorInformation(
                            $order['id'],
                            "Not able to create Cart."
                        );
                        return false;
                    }
                } else {
                    $this->orderErrorInformation(
                        $order['id'],
                        "Not able to create Address."
                    );
                    return false;
                }
            }
            return false;
        } catch (Exception $e) {
            $this->orderErrorInformation(
                $order['id'],
                $e->getMessage()
            );
            return false;
        }
    }

    protected function validateItems($item_line, $payment_details)
    {
        $response = array(
            'success' => array(),
            'error' => array()
        );

        $id = '';
        $sku = '';
        $variation_id = '';

        if (isset($item_line['sku']) && $item_line['sku']) {
            $sku = isset($item_line['sku']) ? $item_line['sku'] : '';
        }

        if (isset($item_line['id']) && $item_line['id']) {
            $id = isset($item_line['id']) ? $item_line['id'] : '';
        }

        if (isset($item_line['variation_id']) && $item_line['variation_id']) {
            $variation_id = isset($item_line['variation_id']) ? $item_line['variation_id'] : '';
        }

        $product_info = $this->getProductDetailsByWishInfo($sku, $id, $variation_id);

        if (!empty($product_info) && isset($product_info['id_product']) && $product_info['id_product']) {
            $Title = '';
            if (isset($item_line['name']) && $item_line['name']) {
                $Title = isset($item_line['name']) ? $item_line['name'] : '';
            }
            $id_product = (int)$product_info['id_product'];
            $id_product_attribute = (int)$product_info['id_product_attribute'];
            $qty = isset($payment_details['product_quantity']) ? $payment_details['product_quantity'] : '1';

            $price = isset($payment_details['product_price']['amount']) ? $payment_details['product_price']['amount'] :
                '1';

            $shipping_price = isset($payment_details['product_shipping_price']['amount'])
                ? $payment_details['product_shipping_price']['amount'] : '1';

            $currency_code = isset($payment_details['product_price']['currency_code'])
                ? $payment_details['product_quantity']['currency_code'] : 'USD';

            $product = new Product(
                (int)$id_product,
                false
            );

            if ($product && !$product->id) {
                $response['error'][] = array(
                    'sku' => $sku,
                    'ItemId' => $id,
                    'ItemVariationId' => $variation_id,
                    'message' => 'NO PRODUCT FOUND WITH THE SKU',
                );
            }
            if (!Configuration::get('CED_WISH_CREATE_ORDER')) {
                if ($product && !$product->active) {
                    $response['error'][] = array(
                        'sku' => $sku,
                        'ItemId' => $id,
                        'ItemVariationId' => $variation_id,
                        'message' => 'PRODUCT STATUS IS DISABLED WITH ID',
                    );
                }
                if (!$product->checkQty((int)$qty)) {
                    $response['error'][] = array(
                        'sku' => $sku,
                        'ItemId' => $id,
                        'ItemVariationId' => $variation_id,
                        'message' => "REQUESTED QUANTITY FOR PRODUCT ID " . $id_product . " IS NOT AVAILABLE",
                    );
                }
            }
            $response['success'][] = array(
                'sku' => $sku,
                'ItemId' => $id,
                'ItemVariationId' => $variation_id,
                'Title' => $Title,
                'CurrencyCode' => $currency_code,
                'id_product' => $id_product,
                'id_tax_rules_group' => $product->id_tax_rules_group,
                'id_product_attribute' => $id_product_attribute,
                'ItemPrice' => isset($price) ? (float)$price : 0,
                'ShippingPrice' => isset($shipping_price) ? (float)$shipping_price : 0,
                'QuantityOrdered' => (int)$qty
            );
        } else {
            $response['error'][] = array(
                'sku' => $sku,
                'ItemId' => $id,
                'ItemVariationId' => $variation_id,
                'message' => 'NO PRODUCT FOUND WITH THE SKU',
            );
        }
        return $response;
    }

    public function getProductDetailsByWishInfo($sku, $id, $variation_id)
    {
        $language = new Language((int)Configuration::get('CED_WISH_LANG_ID'));
        if ($language && $language->iso_code) {
            $sku = str_replace("_" . $language->iso_code, "", $sku);
        }
        $response = Db::getInstance()->getRow(
            'SELECT `id_product`, `id_product_attribute` FROM `' . _DB_PREFIX_ . 'cedwish_product` 
                WHERE `marketplace_id` LIKE "' . pSQL($id) . '" AND `variation_id` LIKE "' . pSQL($variation_id) . '"'
        );
        if (!empty($response)) {
            return $response;
        } else {
            $sku_field = trim(Configuration::get('CED_WISH_ITEM_SKU'));
            $response = Db::getInstance()->getRow(
                'SELECT `id_product`, `id_product_attribute` FROM `' . _DB_PREFIX_ . 'product_attribute` 
                WHERE `' . $sku_field . '` LIKE "' . pSQL($sku) . '"'
            );
            if (!empty($response)) {
                return $response;
            } else {
                $response = Db::getInstance()->getRow(
                    'SELECT `id_product` FROM `' . _DB_PREFIX_ . 'product` 
                    WHERE `' . $sku_field . '` LIKE "' . pSQL($sku) . '"'
                );
                if (!empty($response)) {
                    $response['id_product_attribute'] = 0;
                    return $response;
                }
            }
        }
        return array();
    }

    public function orderErrorInformation($marketplace_order_id, $message)
    {
        $db = Db::getInstance();
        $already_exists = "SELECT id_cedwish_order FROM `" . _DB_PREFIX_ . "cedwish_order`
        WHERE `marketplace_order_id` LIKE '" . pSQL($marketplace_order_id) . "'";
        $check_already_exists = $db->getValue($already_exists);
        if ($check_already_exists) {
            $db->update(
                'cedwish_order',
                array(
                    'order_error' => $message
                ),
                "id_cedwish_order = '" . pSQL($check_already_exists) . "'"
            );
        } else {
            $db->insert(
                'cedwish_order',
                array(
                    'order_error' => $message
                )
            );
        }
    }

    protected function createCustomer($email, $firstName, $lastName)
    {
        $idCustomer = 0;
        if (Customer::customerExists($email)) {
            $customer = Customer::getCustomersByEmail($email);
            if (isset($customer[0]) && isset($customer[0]['id_customer']) && $customer[0]['id_customer']) {
                $idCustomer = (int)$customer[0]['id_customer'];
                $customer = new Customer($idCustomer);
                if (isset($customer->id) && $customer->id) {
                    return $customer;
                }
            }
        }

        $validityPattern = Tools::cleanNonUnicodeSupport(
            '/^(?:[^0-9!<>,;?=+()\/\\@#"°*`{}_^$%:¤\[\]|\.。]|[\.。](?:\s|$))*$/u'
        );
        $isValid = preg_match($validityPattern, $lastName);
        if (!$isValid) {
            $lastName = preg_replace('/[^a-zA-z .]/', '', $lastName);
        }

        $isValid = preg_match($validityPattern, $firstName);
        if (!$isValid) {
            $firstName = preg_replace('/[^a-zA-z .]/', '', $firstName);
        }

        $lastName = Tools::substr($lastName, 0, 31);
        $firstName = Tools::substr($firstName, 0, 31);

        if (!$idCustomer) {
            $new_customer = new Customer();
            $new_customer->email = $email;
            $new_customer->lastname = $lastName;
            $new_customer->firstname = $firstName;
            $new_customer->passwd = 'wish.com';
            $new_customer->id_default_group = (int)Configuration::get('CED_WISH_CUSTOMER_GROUP_ID');
            try {
                $new_customer->add();
            } catch (PrestaShopDatabaseException $e) {
                return false;
            } catch (PrestaShopException $e) {
                return false;
            }
            return $new_customer;
        }
        return false;
    }

    protected function addShippingAddress($customer, $shippingAddress, $firstName, $lastName)
    {
        $state = isset($shippingAddress['state']) ? $shippingAddress['state'] : '';

        $countryCode = isset($shippingAddress['country_code']) ?
            $shippingAddress['country_code'] : 'US';

        if (isset($shippingAddress['street_address1']) && $shippingAddress['street_address1']) {
            $address1 = $shippingAddress['street_address1'];
        } else {
            $address1 = false;
        }

        $address2 = isset($shippingAddress['street_address2']) ? $shippingAddress['street_address2'] : '';

        if (!$address1) {
            $address1 = $address2;
            $address2 = '';
        }

        $validityPattern = Tools::cleanNonUnicodeSupport(
            '/^[^!<>?=+@{}_$%]*$/u'
        );
        $isValid = preg_match($validityPattern, $address1);
        if (!$isValid) {
            $address1 = preg_replace('/^[^!<>?=+@{}_$%]*$/u', ' ', $address1);
        }

        $validityPattern = Tools::cleanNonUnicodeSupport(
            '/^[^!<>?=+@{}_$%]*$/u'
        );
        $isValid = preg_match($validityPattern, $address2);
        if (!$isValid) {
            $address2 = preg_replace('/^[^!<>?=+@{}_$%]*$/u', ' ', $address2);
        }

        $postCode = isset($shippingAddress['zipcode']) && !empty($shippingAddress['zipcode'])
            ? $shippingAddress['zipcode'] : '111111';

        $city = isset($shippingAddress['city']) ? $shippingAddress['city'] : '';

        $phone = isset($shippingAddress['phone_number']['number']) ?
            $shippingAddress['phone_number']['number'] : '';
        $phone = preg_replace('/[^0-9\-]/', '', $phone);

        $getLocalizationDetails = $this->getLocalizationDetails($state, $countryCode);

        $idCountry = $getLocalizationDetails['country_id'];
        $idState = $getLocalizationDetails['zone_id'];
        $addressShipping = new Address();
        $addressShipping->id_customer = $customer->id;
        $addressShipping->id_country = $idCountry;
        $addressShipping->alias = 'Wish Shipping';
        $addressShipping->firstname = $firstName;
        $addressShipping->lastname = $lastName;
        $addressShipping->id_state = $idState;
        $addressShipping->address1 = $address1;
        $addressShipping->address2 = $address2;
        $addressShipping->postcode = $postCode;
        $addressShipping->city = $city;
        $addressShipping->phone = $phone;
        $addressShipping->phone_mobile = $phone;
        $addressShipping->dni = '1234567890';
        try {
            $addressShipping->add();
        } catch (Exception $e) {
            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
            return false;
        }
        return $addressShipping;
    }

    public function getLocalizationDetails($Statecode, $countryCode)
    {
        $db = Db::getInstance();
        $sql = "SELECT c.id_country, cl.name FROM `" . _DB_PREFIX_ . "country` c
         LEFT JOIN `" . _DB_PREFIX_ . "country_lang` cl on (c.id_country =cl.id_country)
          WHERE `iso_code` LIKE '" . pSQL($countryCode) . "' 
          AND cl.id_lang ='" . (int)CedWishHelper::getLanguageId() . "'";
        $country = $db->getRow($sql);

        if (empty($country) && Configuration::get('CED_WISH_ORDER_COUNTRY_ID')) {
            $sql = "SELECT c.id_country, cl.name FROM `" . _DB_PREFIX_ . "country` c
                    LEFT JOIN `" . _DB_PREFIX_ . "country_lang` cl on (c.id_country =cl.id_country)
                    WHERE c.`id_country` = '" . (int)Configuration::get('CED_WISH_ORDER_COUNTRY_ID') . "' 
                    AND cl.id_lang ='" . (int)CedWishHelper::getLanguageId() . "'";
            $country = $db->getRow($sql);
        }

        if (!empty($country)) {
            $country_id = 0;
            $country_name = '';
            if (isset($country['id_country']) && $country['id_country']) {
                $country_id = (int)$country['id_country'];
                $country_name = $country['name'];
            }
            if ($country_id) {
                $state = $db->getRow("SELECT `id_state`,`name` FROM 
                 `" . _DB_PREFIX_ . "state` WHERE `id_country`='" . (int)$country_id . "'
                  AND `iso_code` LIKE '" . pSQL($Statecode) . "' OR `name` LIKE '%" . pSQL($Statecode) . "%'");

                if (!empty($state)) {
                    if (isset($state['id_state']) && isset($state['name'])) {
                        return array(
                            'country_id' => $country_id,
                            'zone_id' => $state['id_state'],
                            'name' => $state['name'],
                            'country_name' => $country_name
                        );
                    }
                } else {
                    return array(
                        'country_id' => $country_id,
                        'zone_id' => '',
                        'name' => '',
                        'country_name' => $country_name
                    );
                }
            } else {
                return array(
                    'country_id' => '',
                    'zone_id' => '',
                    'name' => '',
                    'country_name' => ''
                );
            }
        } else {
            return array(
                'country_id' => '',
                'zone_id' => '',
                'name' => '',
                'country_name' => ''
            );
        }
    }

    protected function createCart($shippingAddress, $customer, $items, $store_carrier_id, $idCurrency)
    {
        Context::getContext()->customer = $customer;
        $cart = new Cart();
        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $shippingAddress->id;
        $cart->id_address_invoice = $shippingAddress->id;
        $cart->id_currency = (int)$idCurrency;
        $cart->id_carrier = (int)$store_carrier_id;
        $cart->id_shop_group = (int)Context::getContext()->shop->id_shop_group;
        $cart->recyclable = 0;
        $cart->gift = 0;
        $cart->secure_key = $customer->secure_key;

        try {
            $cart->add();
        } catch (PrestaShopDatabaseException $e) {
            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
            return false;
        } catch (PrestaShopException $e) {
            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
            return false;
        }

        $productArray = array();
        $total_item_cost_incl_tax = 0;
        $total_item_cost_excl_tax = 0;
        $shipping_cost_incl_tax = 0;
        $shipping_cost_excl_tax = 0;
        foreach ($items as $item_line) {
            if (!$item_line['QuantityOrdered']) {
                $item_line['QuantityOrdered'] = 1;
            }

            $cart_resp = $cart->updateQty(
                (int)$item_line['QuantityOrdered'],
                (int)$item_line['id_product'],
                (int)$item_line['id_product_attribute'],
                false,
                'up',
                (int)$shippingAddress->id,
                null,
                false,
                true
            );
            if ($cart_resp) {
                $price_incl_tax = $item_line['ItemPrice'];
                $tax_calculator = TaxManagerFactory::getManager(
                    $shippingAddress,
                    $item_line['id_tax_rules_group']
                )->getTaxCalculator();
                $price_excl_tax = $tax_calculator->removeTaxes($price_incl_tax);
                $total_item_cost_incl_tax += $price_incl_tax * (int)$item_line['QuantityOrdered'];
                $total_item_cost_excl_tax += $price_excl_tax * (int)$item_line['QuantityOrdered'];
                $productArray[(int)$item_line['id_product']][(int)$item_line['id_product_attribute']] =
                    array(
                        'quantity' => (int)$item_line['QuantityOrdered'],
                        'Title' => $item_line['Title'],
                        'price_incl_tax' => $price_incl_tax,
                        'price_excl_tax' => $price_excl_tax,
                        'id_tax_rules_group' => $item_line['id_tax_rules_group']
                    );
                $id_tax_rules_group = Db::getInstance()->getValue(
                    "SELECT id_tax_rules_group FROM `" . _DB_PREFIX_ . "carrier_tax_rules_group_shop` 
                    WHERE `id_carrier` = '" . (int)$store_carrier_id . "' 
                    AND `id_shop` = '" . (int)Context::getContext()->shop->id . "'"
                );

                if ($id_tax_rules_group) {
                    $tax_calculator = TaxManagerFactory::getManager(
                        $shippingAddress,
                        $item_line['id_tax_rules_group']
                    )->getTaxCalculator();
                    $carrier_tax = $tax_calculator->removeTaxes((float)$item_line['ShippingPrice']);
                    $shipping_cost_incl_tax += $item_line['ShippingPrice'];
                    $shipping_cost_excl_tax += $carrier_tax;
                } else {
                    $shipping_cost_incl_tax += $item_line['ShippingPrice'];
                    $shipping_cost_excl_tax += $item_line['ShippingPrice'];
                }
            }

            $specific_price_rule = new SpecificPriceRule();
            $specific_price_rule->name = 'Wish.com Price';
            $specific_price_rule->id_shop = (int)$cart->id_shop;
            $specific_price_rule->id_currency = (int)$cart->id_currency;
            $specific_price_rule->id_country = (int)$shippingAddress->id_country;
            $specific_price_rule->id_group = (int)$customer->id_default_group;
            $specific_price_rule->from_quantity = (int)$item_line['QuantityOrdered'];
            $specific_price_rule->price = (float)number_format(
                $total_item_cost_excl_tax,
                '6',
                '.',
                ''
            );
            $specific_price_rule->reduction = 0;
            $specific_price_rule->reduction_tax = 0;
            $specific_price_rule->reduction_type = 'amount';
            $specific_price_rule->from = date("Y-m-d H:i:s");
            $specific_price_rule->to = date("Y-m-d").' 23:59:59';

            try {
                $specific_price_rule->add();
                $specific_price                     = new SpecificPrice();
                $specific_price->id_cart            = (int)$cart->id;
                $specific_price->id_shop            = $cart->id_shop;
                $specific_price->id_shop_group      = $cart->id_shop_group;
                $specific_price->id_currency        = (int)$cart->id_currency;
                $specific_price->id_country         = (int)$shippingAddress->id_country;
                $specific_price->id_group           = (int)$customer->id_default_group;
                $specific_price->id_customer        = (int)$customer->id;
                $specific_price->id_product         = (int)$item_line['id_product'];
                $specific_price->id_product_attribute = (int)$item_line['id_product_attribute'];
                $specific_price->price              = (float)number_format(
                    $total_item_cost_excl_tax,
                    '6',
                    '.',
                    ''
                );
                $specific_price->from_quantity = (int)$item_line['QuantityOrdered'];
                $specific_price->reduction = 0;
                $specific_price->reduction_type = 'amount';
                $specific_price->from = date("Y-m-d H:i:s", strtotime("-1 hour"));
                $specific_price->to   = date("Y-m-d H:i:s", strtotime("+1 hour"));
                $specific_price->id_specific_price_rule = $specific_price_rule->id;
                $specific_price->add();
            } catch (PrestaShopDatabaseException $e) {
                CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                continue;
            } catch (PrestaShopException $e) {
                CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                continue;
            }
        }

        $order_total_incl_tax = $total_item_cost_incl_tax + $shipping_cost_incl_tax;
        $order_total_excl_tax = $total_item_cost_excl_tax + $shipping_cost_excl_tax;
        $extraVars = array();
        $extraVars['total_paid'] = $order_total_incl_tax;
        $extraVars['total_paid_wt'] = $order_total_excl_tax;
        $extraVars['productArray'] = $productArray;
        $extraVars['total_paid_wt'] = $order_total_excl_tax;
        $extraVars['item_shipping_cost'] = $shipping_cost_incl_tax;
        $extraVars['item_shipping_cost_wt'] = $shipping_cost_excl_tax;
        $extraVars['total_item_cost'] = $total_item_cost_incl_tax;
        $extraVars['total_item_cost_wt'] = $total_item_cost_excl_tax;
        $extraVars['cart'] = $cart;

        try {
            Context::getContext()->cart = $cart;
            Context::getContext()->cart->save();
        } catch (PrestaShopException $e) {
            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
            return $extraVars;
        }
        return $extraVars;
    }

    public function addOrderInPrestashop(
        $cart,
        $id_customer,
        $id_address_delivery,
        $id_address_invoice,
        $id_carrier,
        $id_currency,
        $extra_vars,
        $products,
        $secure_key,
        $context,
        $shop,
        $payment_module,
        $status,
        $orderData = array()
    ) {
        try {
            $prestashop_order_status = $this->getOrderStatusByStatus($status);
            $context->cart = $cart;
            $newOrder = new Order();
            $carrier = new Carrier((int)$id_carrier, $context->cart->id_lang);
            $newOrder->id_address_delivery = $id_address_delivery;
            $address_delivery = new Address((int)$id_address_delivery);
            $newOrder->id_address_invoice = $id_address_invoice;
            $newOrder->id_shop_group = $shop->id_shop_group;
            $newOrder->id_shop = $shop->id;
            $newOrder->id_cart = $cart->id;
            $newOrder->id_currency = $id_currency;
            $newOrder->id_lang = $context->language->id;
            $newOrder->id_customer = $id_customer;
            $newOrder->id_carrier = (int)$id_carrier;
            $newOrder->current_state = (int)$prestashop_order_status;
            $newOrder->secure_key = (
            $secure_key ? pSQL($secure_key) : pSQL($context->customer->secure_key)
            );
            $newOrder->payment = 'Wish.com';
            $newOrder->date_add = pSQL(
                Tools::substr(str_replace("T", " ", $orderData['released_at']), 0, 19)
            );
            $newOrder->module = $payment_module;
            $newOrder->conversion_rate = $context->currency->conversion_rate ?
                $context->currency->conversion_rate : 1;
            $newOrder->recyclable = $context->cart->recyclable;
            $newOrder->gift = (int)$context->cart->gift;
            $newOrder->gift_message = $context->cart->gift_message;
            $newOrder->mobile_theme = $context->cart->mobile_theme;
            $newOrder->total_discounts = 0;
            $newOrder->total_discounts_tax_incl = 0;
            $newOrder->total_discounts_tax_excl = 0;
            $newOrder->total_paid = number_format(
                $extra_vars['total_paid'],
                '6',
                '.',
                ''
            );
            $newOrder->total_paid_tax_incl = number_format(
                $extra_vars['total_paid'],
                '6',
                '.',
                ''
            );
            $newOrder->total_paid_tax_excl = number_format(
                $extra_vars['total_paid_wt'],
                '6',
                '.',
                ''
            );
            $newOrder->total_paid_real = number_format(
                $extra_vars['total_paid'],
                '6',
                '.',
                ''
            );
            $newOrder->total_products = number_format(
                $extra_vars['total_item_cost_wt'],
                '6',
                '.',
                ''
            );
            $newOrder->total_products_wt = number_format(
                $extra_vars['total_item_cost'],
                '6',
                '.',
                ''
            );
            $newOrder->total_shipping = number_format(
                $extra_vars['item_shipping_cost'],
                '6',
                '.',
                ''
            );
            $newOrder->total_shipping_tax_incl = number_format(
                $extra_vars['item_shipping_cost'],
                '6',
                '.',
                ''
            );
            $newOrder->total_shipping_tax_excl = number_format(
                $extra_vars['item_shipping_cost_wt'],
                '6',
                '.',
                ''
            );
            if (!is_null($carrier) && Validate::isLoadedObject($carrier)) {
                $newOrder->carrier_tax_rate = $carrier->getTaxesRate(
                    new Address($context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
                );
                if (!$newOrder->carrier_tax_rate) {
                    $id_tax_rules_group = Carrier::getIdTaxRulesGroupByIdCarrier($carrier->id);
                    $rate = Tax::getCarrierTaxRate($id_tax_rules_group, $address_delivery->id);
                    $newOrder->carrier_tax_rate = $rate;
                }
            }
            $newOrder->total_wrapping = 0;
            $newOrder->total_wrapping_tax_incl = 0;
            $newOrder->total_wrapping_tax_excl = 0;
            $newOrder->invoice_date = '0000-00-00 00:00:00';
            $newOrder->delivery_date = '0000-00-00 00:00:00';
            $newOrder->valid = true;
            if (Configuration::get('CED_WISH_ORDER_ID_ORDER_REFERENCE')) {
                $reference = $orderData['id'];
            } else {
                do {
                    $reference = Order::generateReference();
                } while (Order::getByReference($reference)->count());
            }

            $newOrder->reference = $reference;

            $newOrder->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
            $packageList = $context->cart->getPackageList();
            $orderItems = array();
            foreach ($packageList as $id_address => $packageByAddress) {
                foreach ($packageByAddress as $id_package => $package) {
                    foreach ($package['product_list'] as &$product_line_data) {
                        if (array_key_exists($product_line_data['id_product'], $products)) {
                            $product_line_data['price'] = $products[$product_line_data['id_product']]
                            [$product_line_data['id_product_attribute']]['price_excl_tax'];
                            $product_line_data['price_wt'] = $products[$product_line_data['id_product']]
                            [$product_line_data['id_product_attribute']]['price_incl_tax'];
                            $product_line_data['total'] = $products[$product_line_data['id_product']]
                                [$product_line_data['id_product_attribute']]['price_excl_tax'] *
                                $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                                ['quantity'];
                            $product_line_data['total_wt'] = $products[$product_line_data['id_product']]
                                [$product_line_data['id_product_attribute']]['price_excl_tax'] *
                                $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                                ['quantity'];
                        }
                    }
                    $orderItems = $package['product_list'];
                }
            }
            $packageListss = $context->cart->getProducts();
            if (count($orderItems) < count($packageListss)) {
                foreach ($packageListss as &$product_line_data) {
                    if (array_key_exists($product_line_data['id_product'], $products)) {
                        $product_line_data['price'] = $products[$product_line_data['id_product']]
                        [$product_line_data['id_product_attribute']]['price_excl_tax'];
                        $product_line_data['price_wt'] = $products[$product_line_data['id_product']]
                        [$product_line_data['id_product_attribute']]['price_incl_tax'];
                        $product_line_data['total'] = $products[$product_line_data['id_product']]
                            [$product_line_data['id_product_attribute']]['price_excl_tax'] *
                            $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                            ['quantity'];
                        $product_line_data['total_wt'] = $products[$product_line_data['id_product']]
                            [$product_line_data['id_product_attribute']]['price_incl_tax'] *
                            $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                            ['quantity'];
                    }
                }
                $newOrder->product_list = $packageListss;
            } else {
                $newOrder->product_list = $orderItems;
            }

            try {
                $newOrder->add(false, true);
                if (isset($newOrder->product_list) && !empty($newOrder->product_list)) {
                    foreach ($newOrder->product_list as $od_product) {
                        $tax_manager = TaxManagerFactory::getManager(
                            $address_delivery,
                            $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                            ['id_tax_rules_group']
                        );
                        $tax_rate = $tax_manager->getTaxCalculator()->getTotalRate();
                        $tax_name = $tax_manager->getTaxCalculator()->getTaxesName();

                        $order_detail = new OrderDetail();
                        $order_detail->id_order = (int)$newOrder->id;
                        $order_detail->id_order_invoice = 0;
                        $order_detail->product_id = $od_product['id_product'];
                        $order_detail->id_shop = $od_product['id_shop'];
                        $order_detail->id_warehouse = $packageList[$id_address][$id_package]['id_warehouse'];
                        $order_detail->product_attribute_id = $od_product['id_product_attribute'];
                        $order_detail->product_name
                            = isset($products[$od_product['id_product']][$od_product['id_product_attribute']]['Title'])
                            ? $products[$od_product['id_product']][$od_product['id_product_attribute']]['Title']
                            : $od_product['name'];
                        $order_detail->product_weight = number_format(
                            $od_product['weight'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->product_quantity = (int)$od_product['cart_quantity'];
                        $order_detail->product_quantity_in_stock = (int)$od_product['quantity_available'];
                        $order_detail->product_price = number_format(
                            $od_product['price'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->unit_price_tax_incl = number_format(
                            $od_product['price_wt'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->unit_price_tax_excl = number_format(
                            $od_product['price'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->total_price_tax_incl = number_format(
                            $od_product['total_wt'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->total_price_tax_excl = number_format(
                            $od_product['total'],
                            6,
                            '.',
                            ''
                        );
                        $order_detail->product_ean13 = $od_product['ean13'];
                        $order_detail->product_upc = $od_product['upc'];
                        $order_detail->id_tax_rules_group
                            = $products[$product_line_data['id_product']][$product_line_data['id_product_attribute']]
                        ['id_tax_rules_group'];
                        $order_detail->product_reference = $od_product['reference'];
                        $order_detail->product_supplier_reference = $od_product['supplier_reference'];
                        $order_detail->product_weight = $od_product['weight'];
                        $order_detail->ecotax = $od_product['ecotax'];
                        $order_detail->tax_rate = $tax_rate;
                        $order_detail->tax_name = $tax_name;
                        $order_detail->discount_quantity_applied = $od_product['quantity_discount_applies'];
                        try {
                            $o_res = $order_detail->add();
                            $order_detail->updateTaxAmount($newOrder);
                            $order_detail->setShippingCost($newOrder, $od_product);
                            if (!$o_res) {
                                $newOrder->delete();
                            }
                        } catch (PrestaShopDatabaseException $e) {
                            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                            continue;
                        } catch (PrestaShopException $e) {
                            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                            continue;
                        }
                    }
                    $newOrder->updateOrderDetailTax();
                    $order_status = new OrderState(
                        (int)$prestashop_order_status,
                        (int)$context->language->id
                    );

                    if (empty($newOrder->getOrderPayments())) {
                        $newOrderPayment = new OrderPayment();
                        $newOrderPayment->transaction_id = $orderData['id'];
                        $newOrderPayment->order_reference = $newOrder->reference;
                        $newOrderPayment->conversion_rate = (float)$newOrder->conversion_rate;
                        $newOrderPayment->payment_method = $newOrder->payment;
                        $newOrderPayment->amount = $newOrder->total_paid_tax_incl;
                        $newOrderPayment->id_currency = $newOrder->id_currency;

                        try {
                            $newOrderPayment->add();
                        } catch (PrestaShopDatabaseException $e) {
                            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                            return $newOrder->id;
                        } catch (PrestaShopException $e) {
                            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                            return $newOrder->id;
                        }
                    }

                    /*$product_list = $newOrder->getProducts();
                    if (!empty($product_list)) {
                        foreach ($product_list as $od_product) {
                            $idProd = $od_product['product_id'];
                            $idProdAttr = $od_product['product_attribute_id'];
                            $qtyToReduce = (int)$od_product['product_quantity'] * -1;
                            StockAvailable::updateQuantity($idProd, $idProdAttr, $qtyToReduce, $newOrder->id_shop);
                        }
                    }*/

                    $newOrderCarrier = new OrderCarrier();
                    $newOrderCarrier->id_order = (int)$newOrder->id;
                    $newOrderCarrier->id_carrier = (int)$id_carrier;
                    $newOrderCarrier->weight = (float)$newOrder->getTotalWeight();
                    $newOrderCarrier->shipping_cost_tax_excl = $newOrder->total_shipping_tax_excl;
                    $newOrderCarrier->shipping_cost_tax_incl = $newOrder->total_shipping_tax_incl;

                    try {
                        $newOrderCarrier->add();
                        // Set the order status
                        $new_history = new OrderHistory();
                        $new_history->id_order = (int)$newOrder->id;
                        $new_history->changeIdOrderState(
                            (int)$prestashop_order_status,
                            $newOrder,
                            true
                        );
                        $new_history->add(true, $extra_vars);
                        foreach ($context->cart->getProducts() as $product) {
                            if ($order_status->logable) {
                                ProductSale::addProductSale(
                                    (int)$product['id_product'],
                                    (int)$product['cart_quantity']
                                );
                            }
                        }
                    } catch (PrestaShopDatabaseException $e) {
                        CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                        return $newOrder->id;
                    } catch (PrestaShopException $e) {
                        CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                        return $newOrder->id;
                    }

                    if (isset($newOrder->id) && $newOrder->id) {
                        return $newOrder->id;
                    }
                }
            } catch (PrestaShopDatabaseException $e) {
                CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                return false;
            } catch (PrestaShopException $e) {
                CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
                return false;
            }
            return false;
        } catch (Exception $e) {
            CedWishHelper::addLog($e->getMessage() . __FUNCTION__ . $e->getTraceAsString());
            return false;
        }
    }

    public function getOrderStatusByStatus($status)
    {
        $order_statuses = Configuration::get('CED_WISH_STATUS_MAPPING');
        $id_order_state = 0;
        if ($order_statuses) {
            $order_statuses = json_decode($order_statuses, true);
            if (!empty($order_statuses)) {
                foreach ($order_statuses as $order_status) {
                    if ($status == $order_status['marketplace_status']) {
                        $id_order_state = (int)$order_status['order_status'];
                    }
                }
            }
        }
        if (!$id_order_state) {
            $id_order_state = Configuration::get('CED_WISH_ORDER_CREATE_STATUS');
        }
        return $id_order_state;
    }

    public function getProductDetailsByWishProductId($claro_id)
    {
        return Db::getInstance()->getRow(
            'SELECT `id_product`,`id_product_attribute` FROM `' . _DB_PREFIX_ . 'cedwish_product` 
            WHERE `marketplace_id` LIKE "' . pSQL($claro_id) . '"'
        );
    }

    public function syncStatus($ids)
    {
        $result = array();
        $api = new CedWishApi();
        foreach ($ids as $id) {
            $order = new CedWishOrder((int)$id);
            $id = $order->marketplace_order_id;
            $order_info = $api->getOrder(array($id));
            if (isset($order_info['code']) && ($order_info['code'] == 0)) {
                $order_info = $order_info['data'];
                if (isset($order_info['state']) && $order_info['state']) {
                    $order->state = $order_info['state'];
                    $order->wish_order = pSQL(json_encode($order_info));
                    try {
                        $order->update();
                    } catch (PrestaShopDatabaseException $e) {
                    } catch (PrestaShopException $e) {
                    }
                    $id_order_state = (int)$this->getOrderStatusByStatus($order->state);
                    if ((int)$order->store_order_id && $id_order_state) {
                        $store_order = new Order((int)$order->store_order_id);
                        if ($store_order
                            && $store_order->getCurrentState()
                            && ((int)$store_order->getCurrentState() != (int)$id_order_state)
                        ) {
                            $orderHistory = new OrderHistory();
                            $orderHistory->id_order = (int)$order->store_order_id;
                            $orderHistory->id_order_state = $id_order_state;
                            try {
                                $orderHistory->add();
                            } catch (PrestaShopDatabaseException $e) {
                            } catch (PrestaShopException $e) {
                            }
                        } else {
                            $result['success'][] = 'Status Already Synced for order id ' . $id;
                        }
                    }
                    $result['success'][] = 'Status Sync Successfully for order id ' . $id;
                } else {
                    $result['error'][] = 'Failed to Sync Successfully for order id ' . $id;
                }
            } else {
                $result['error'][] = 'Failed to Sync Successfully for order id ' . $id;
            }
        }
        return $result;
    }

    public function shipOrder($ids)
    {
        $result = array();
        foreach ($ids as $id) {
            $corder = new CedWishOrder((int)$id);
            if ($corder && $corder->store_order_id) {
                try {
                    $id_order = (int)$corder->store_order_id;
                    $wish_order_id = $corder->marketplace_order_id;
                    if ($wish_order_id) {
                        $order = new Order($id_order);
                        if ($order && $order->getCurrentState()) {
                            $id_carrier = $order->id_carrier;
                            if ($id_carrier) {
                                $id_order_carrier = $order->getIdOrderCarrier();
                                if (!empty($id_order_carrier)) {
                                    $trackingNumber = Db::getInstance()->getValue(
                                        "SELECT `tracking_number` FROM `" . _DB_PREFIX_ . "order_carrier` 
                                                WHERE `id_order` = " . $id_order . " 
                                                AND `id_order_carrier` =" . $id_order_carrier
                                    );
                                }
                                if (!$id_carrier) {
                                    $id_carrier = Configuration::get('CED_WISH_ORDER_CARRIER');
                                }

                                try {
                                    $carrier = CedWishHelper::getWishMappedCarrier($id_carrier);

                                    if ($carrier
                                        && in_array($carrier, array_keys(CedWishHelper::getCarriers()))
                                    ) {
                                        $data = [
                                            'origin_country' => Configuration::get('CED_WISH_ORIGIN_COUNTRY'),
                                            'shipping_provider' => $carrier,
                                            'tracking_number' => $trackingNumber,
                                        ];

                                        $api = new CedWishApi();
                                        $response = $api->makeShipment($wish_order_id, $data);
                                        if (isset($response['code']) && ($response['code']==0)) {
                                            $corder->order_error = '';
                                            $corder->state = 'SHIPPED';
                                            $corder->wish_order = pSQL(json_encode($response['data']));
                                            $corder->update();
                                            $result['success'][] = $wish_order_id . ' Shipped Successfully';
                                        } else {
                                            CedWishQueue::addQueue(
                                                'shipment',
                                                array(
                                                    $id_order
                                                ),
                                                4
                                            );
                                            $message = isset($response['message'])
                                                ?'Error While Shipment: ' . $response['message']
                                                : 'Some error while Shipment.';
                                            Db::getInstance()->execute(
                                                "UPDATE `"._DB_PREFIX_."cedwish_order` 
                                                    SET order_error ='".pSQL($message)."' 
                                                    WHERE id_cedwish_order ='".(int)$id."'"
                                            );
                                            $corder->order_error = pSQL($message);
                                            $corder->update();
                                            $result['error'][] = $message;
                                        }
                                    } else {
                                        $result['error'][] = 'Carrier ID ' . $id_carrier . ' is not mapped.';
                                    }
                                } catch (Exception $e) {
                                    $result['error'][] = $e->getMessage();
                                }
                            } else {
                                $result['error'][] = 'Carrier ID ' . $id_carrier . ' is not mapped.';
                            }
                        } else {
                            $result['error'][] = 'Order Not created yet.';
                        }
                    }
                } catch (Exception $e) {
                    $result['error'][] = $e->getMessage();
                    continue;
                }
            } else {
                $result['error'][] = 'Order Not created yet.';
            }
        }
        return $result;
    }

    public function cancelOrder($ids)
    {
        $result = array();
        foreach ($ids as $id) {
            $wish_order = new CedWishOrder((int)$id);
            if ($wish_order && $wish_order->marketplace_order_id) {
                try {
                    $data = [
                        'refund_reason' => "MERCHANT_OUT_OF_STOCK",
                        'refund_reason_note' => "Not able to fulfill order",
                    ];

                    $api = new CedWishApi();
                    $response = $api->cancelOrder(
                        $wish_order->marketplace_order_id,
                        $data
                    );

                    if (isset($response['code']) && ($response['code'] == 0)) {
                        $wish_order->order_error = '';
                        $wish_order->state = 'CANCELLED';
                        $wish_order->wish_order = pSQL(json_encode($response['data']));
                        $wish_order->update();
                        $result['success'][] = 'Cancel request send Successfully';
                    } elseif (isset($response['message']) && $response['message']) {
                        $wish_order->order_error = $response['message'];
                        $wish_order->update();
                        $result['error'][] = $response['message'];
                    } else {
                        $wish_order->order_error = 'Some error in response from wish.com';
                        $wish_order->update();
                        $result['error'][] = 'Some error in response from wish.com';
                    }
                } catch (Exception $e) {
                    $result['error'][] = $e->getMessage();
                    continue;
                }
            }
        }
        return $result;
    }
}
