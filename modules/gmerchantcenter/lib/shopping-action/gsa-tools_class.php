<?php

/**
 * Google Merchant Center
 *
 * @author    BusinessTech.fr - https://www.businesstech.fr
 * @copyright Business Tech 2020 - https://www.businesstech.fr
 * @license   Commercial
 *
 *           ____    _______
 *          |  _ \  |__   __|
 *          | |_) |    | |
 *          |  _ <     | |
 *          | |_) |    | |
 *          |____/     |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_GMC_GSA_LIB . 'gsa-dao_class.php');
require_once(_GMC_GSA_PAYMENT_MODULE . 'gsa-payment_class.php');

class BT_GmcGsaTools
{
    /**
     * create the customer account with data provided from the API
     *
     * @param array $aParamData data from API
     * @return object
     */
    public static function createCustomer($aParamData)
    {
        //Create customer account todo get the values from Octopus
        $oCustomer = new Customer();

        // Set the customer information
        $oCustomer->lastname = $aParamData['lastname'];
        $oCustomer->firstname = $aParamData['firstname'];
        $oCustomer->id_shop = GMerchantCenter::$iShopId;
        $oCustomer->id_gender = 1;
        $oCustomer->id_default_group = (int) GMerchantCenter::$conf['GMC_GSA_CUSTOMER_GROUP'];
        $oCustomer->email = $aParamData['email'];
        $oCustomer->passwd = Tools::hash(mt_rand());
        $oCustomer->last_passwd_gen = time();
        $oCustomer->date_add = time();
        $oCustomer->date_upd = time();

        //Manage optin according to the customer preferences from Google
        if ($aParamData['optin'] != "denied") {
            $oCustomer->optin = 1;
            $oCustomer->newsletter = 1;
        }

        $oCustomer->add();

        return $oCustomer;
    }

    /**
     * create the address of the customer
     *
     * @param array $aParamData data from api
     * @param obj $oCustomer
     * @return object
     */
    public static function createAddress(array $aParamData, Customer $oCustomer)
    {
        $aCountry = Country::getCountries(GMerchantCenter::$iCurrentLang, true, false, false);
        $aDefaultCountry = reset($aCountry);

        // We try to get the real country id if active on the shop otherwise we get the 1st country of the shop.
        $iCountryID = !empty(Country::getByIso($aParamData['country'], true)) ? (int) Country::getByIso($aParamData['country'], true) : (int) (int) $aDefaultCountry['id_country'];

        $oAddress = new Address();
        $oAddress->id_customer = (int) $oCustomer->id;
        $oAddress->id_country = (int) $iCountryID;
        $oAddress->country = $aParamData['country'];
        $oAddress->alias = 'Imported address from GSA';
        $oAddress->firstname = (string) $oCustomer->firstname;
        $oAddress->lastname = (string) $oCustomer->lastname;
        $oAddress->address1 = $aParamData['address1'];
        $oAddress->address2 = $aParamData['address2'];
        $oAddress->postcode = $aParamData['postcode'];
        $oAddress->city = $aParamData['city'];
        $oAddress->phone = $aParamData['phone'];
        // Manage if we can
        //$oAddress->phone_mobile = '0606060606';

        $oAddress->add();

        return $oAddress;
    }

    /**
     * manage addresse already exist for a specific alias
     *
     * @param obj $oCustomer
     * @param string $sAlias
     * @return object
     */
    public static function getAddressIdAlreadyExist(Customer $oCustomer, $sAlias)
    {
        //Get the customer addresses
        if (!empty(GMerchantCenter::$bCompare17)) {
            $aCustomerAddresses = $oCustomer->getSimpleAddresses(GMerchantCenter::$iCurrentLang);
            //Search the id of the address associate to the alias used for GSA synch
            foreach ($aCustomerAddresses as $aCustomerAddress) {
                //Compare the alias to find the good address id
                if ($aCustomerAddress['alias'] ===  $sAlias) {
                    $iAddressId = (int) $aCustomerAddress['id'];
                }
            }
        } else {
            $aCustomerAddresses = $oCustomer->getAddresses(GMerchantCenter::$iCurrentLang);
            //Search the id of the address associate to the alias used for GSA synch
            foreach ($aCustomerAddresses as $aCustomerAddress) {
                //Compare the alias to find the good address id
                if ($aCustomerAddress['alias'] ===  $sAlias) {
                    $iAddressId = (int) $aCustomerAddress['id_address'];
                }
            }
        }

        return new Address((int) $iAddressId);
    }

    /**
     * create the cart
     *
     * @param obj $oCustomer
     * @param obj $oAddressDlivery
     * @param obj $oAddressBilling
     * @return object
     */
    public static function createCart(Customer $oCustomer, Address $oAddressDelivery, Address $oAddressBilling)
    {
        $aDeliveryOptions = array();

        //Create a cart with the data provided from api
        $oCart = new Cart();
        $aDeliveryOptions[(int) $oAddressDelivery->id] = (int) GMerchantCenter::$conf['GMC_GSA_DEFAULT_CARRIER'] . ',';

        $oCart->id_shop = GMerchantCenter::$iShopId;
        $oCart->id_address_delivery = (int) $oAddressDelivery->id;
        $oCart->id_address_invoice = (int) $oAddressBilling->id;
        $oCart->id_currency = (int) Currency::getDefaultCurrency()->id;
        $oCart->id_customer = (int) $oCustomer->id;
        $oCart->id_lang = GMerchantCenter::$iCurrentLang;
        $oCart->setDeliveryOption($aDeliveryOptions);
        $oCart->id_carrier = (int) GMerchantCenter::$conf['GMC_GSA_DEFAULT_CARRIER'];

        $oCart->add();

        return $oCart;
    }

    /**
     * create the cart product line
     *
     * @param array $aOrderDetails
     * @param array $aOrderData
     * @param obj $oCart
     * @return bool
     */
    public static function createCartProductLines(array $aOrderDetails, array $aOrderData, Cart $oCart)
    {
        $bAdded = false;
        // Added product line on the cart
        if (is_array($aOrderDetails) && isset($aOrderDetails)) {
            foreach ($aOrderDetails as $orderDetail) {
                $aProductData = BT_GmcGsaDao::getProductIdFromGsa($orderDetail['gtin'], $orderDetail['mpn']);
                if (!empty($aProductData)) { // Removed after test
                    $iProductIdAttribute = isset($aProductData['id_product_attribute']) ? $aProductData['id_product_attribute'] : 0;
                    $oCart->updateQty((int) $orderDetail['quantity'], (int) $aProductData['id_product'], (int) $iProductIdAttribute);
                    if (BT_GmcGsaDao::addProductsOrderData($aOrderData['gsa_id'], (int) $aProductData['id_product'], (int) $iProductIdAttribute, $orderDetail['line_item'], (int) $orderDetail['quantity'], 0, 0, GMerchantCenter::$iShopId)) {
                        $bAdded = true;
                    }
                } else { //Handle case based on the offerID

                    if (!empty(GMerchantCenter::$conf['GMC_SIMPLE_PROD_ID'])) { // Use case for the simple ID export 
                        if (!empty(GMerchantCenter::$conf['GMERCHANTCENTER_P_COMBOS'])) { // Use case if we export each comboniation as a product

                            // Explode the string to an arra to get the id product and the id product attribute 
                            $aProductData = explode('v', $orderDetail['offerId']);

                            $iProductIdAttribute = isset($aProductData[1]) ? $aProductData[1] : 0;
                            $oCart->updateQty((int) $orderDetail['quantity'], (int) $aProductData[0], (int) $iProductIdAttribute);
                            if (BT_GmcGsaDao::addProductsOrderData($aOrderData['gsa_id'], (int) $aProductData[0], (int) $iProductIdAttribute, $orderDetail['line_item'], (int) $orderDetail['quantity'], 0, 0, GMerchantCenter::$iShopId)) {
                                $bAdded = true;
                            }
                        } else { // Use case when we do NOT export each combo as a product
                            $oCart->updateQty((int) $orderDetail['quantity'], (int) $orderDetail['offerId'], 0);
                            if (BT_GmcGsaDao::addProductsOrderData($aOrderData['gsa_id'], (int) $orderDetail['offerId'], 0, $orderDetail['line_item'], (int) $orderDetail['quantity'], 0, 0, GMerchantCenter::$iShopId)) {
                                $bAdded = true;
                            }
                        }
                    } else { // Use case when we do NOT use simple id the data handle is a little more hard to handle

                        $OfferId = $orderDetail['offerId'];
                        //Check if we have prefix and if yes we remove it from the string
                        if (!empty(GMerchantCenter::$conf['GMERCHANTCENTER_ID_PREFIX'])) {
                            $sOfferIdNoPrefix = str_replace((string) GMerchantCenter::$conf['GMERCHANTCENTER_ID_PREFIX'], '', $OfferId);
                        }

                        // We need to removed lang prefix 
                        foreach ($GLOBALS['GMC_LANG_TO_REMOVED_OFFERID'] as $lang) {
                            if (substr($sOfferIdNoPrefix, 0, 2) == $lang) {
                                $sOfferIdClean = str_replace((string) $lang, '', $sOfferIdNoPrefix);
                            }
                        }

                        // Handle cart building
                        if (!empty(GMerchantCenter::$conf['GMERCHANTCENTER_P_COMBOS'])) { // Use case if we export each comboniation as a product
                            // Explode the string to an arra to get the id product and the id product attribute 
                            $aProductData = explode('v', $sOfferIdClean);

                            $iProductIdAttribute = isset($aProductData[1]) ? $aProductData[1] : 0;
                            $oCart->updateQty((int) $orderDetail['quantity'], (int) $aProductData[0], (int) $iProductIdAttribute);
                            if (BT_GmcGsaDao::addProductsOrderData($aOrderData['gsa_id'], (int) $aProductData[0], (int) $iProductIdAttribute, $orderDetail['line_item'], (int) $orderDetail['quantity'], 0, 0, GMerchantCenter::$iShopId)) {
                                $bAdded = true;
                            }
                        } else { // Use case when we do NOT export each combo as a product
                            $oCart->updateQty((int) $orderDetail['quantity'], (int) $sOfferIdClean, 0);
                            if (BT_GmcGsaDao::addProductsOrderData($aOrderData['gsa_id'], (int) $sOfferIdClean, 0, $orderDetail['line_item'], (int) $orderDetail['quantity'], 0, 0, GMerchantCenter::$iShopId)) {
                                $bAdded = true;
                            }
                        }
                    }
                }
            }
        }

        return $bAdded;
    }

    /**
     * create the order 
     *
     * @param array $OrderData
     * @param obj $oCart
     * @param obj $oCustomer
     * @return bool
     */
    public static function createOrder(array $OrderData, Cart $oCart, Customer $oCustomer)
    {
        $bAdded = false;

        //Use custom payment module for the GSA
        $paymentModule = new GsaPayment();
        $aOrderStates = $paymentModule->getOrderStates();

        //Get the status from GSA and make the matching with our PS status
        $iOrderStateId =  (int) $aOrderStates['gsa']['done'];
        $aEmailVars = array('transaction_id' => $OrderData['gsa_id']);

        //Use validate order
        if ($paymentModule->validateOrder((int) $oCart->id, $iOrderStateId, $oCart->getOrderTotal(), $paymentModule->getPublicName(), null, $aEmailVars, 1, false, $oCustomer->secure_key)) {
            //Get the order id recently created
            $iOrderId = Order::getOrderByCartId((int) $oCart->id);
            if (BT_GmcGsaDao::addOrderData($iOrderId, $OrderData['gsa_id'], $OrderData['gsa_order_satus'], 'GSA sync', GMerchantCenter::$iShopId)) {
                $bAdded = true;
            }
        }

        return $bAdded;
    }
}
