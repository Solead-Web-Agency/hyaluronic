<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from ScaleDEV.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@scaledev.fr
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concédée par la société ScaleDEV.
 * Toute utilisation, reproduction, modification ou distribution du présent
 * fichier source sans contrat de licence écrit de la part de la ScaleDEV est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter ScaleDEV a l'adresse: contact@scaledev.fr
 * ...........................................................................
 *
 * @author ScaleDEV
 * @copyright Copyright (c) 2019 ScaleDEV - 12 RUE BEGAND - 10000 TROYES - FRANCE
 * @license Commercial license
 * @package SdevAtos
 * Support by mail : contact@scaledev.fr
 */

use ScaleDEV\SdevAtos\SdevConfiguration;
use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevTools;
use ScaleDEV\SdevAtos\SdevDate;

require_once(dirname(__FILE__).'../../autoload.php');

class SdevAtosWs
{
    const URL_TEST = 'https://payment-webinit.test.sips-services.com/paymentInit';
    const URL_PROD = 'https://sherlocks-payment-webinit.secure.lcl.fr/paymentInit';
/**https://payment-webinit.sips-atos.com/paymentInit   */

    /** @var Context $context - Context. */
    private $context;

    /** @var Customer $customer - Customer. */
    private $customer;

    /** @var Cart $cart - Cart. */
    private $cart;

    /** @var SdevAtosContract $this->contract - Contract. */
    private $contract;

    /** @var SdevAtosPaymentMethod $payment_method - Payment method. */
    private $payment_method;

    /**
     * Get Atos form.
     *
     * @param int $id_payment_method - Payment method ID.
     * @return array
     * @throws Exception
     */
    public function getForm($id_payment_method)
    {
        try {
            if (!is_numeric($id_payment_method)) {
                throw new Exception('The id_payment_method must be an integer, '.gettype($id_payment_method).' given ');
            }

            $this->payment_method = new SdevAtosPaymentMethod((int)$id_payment_method);
            if (!Validate::isLoadedObject($this->payment_method)) {
                return array(
                    'has_error' => true,
                    'error' => 'PaymentMethodInvalid'
                );
            }

            $this->contract = new SdevAtosContract((int)$this->payment_method->id_contract);
            if (!(bool)Validate::isLoadedObject($this->contract)) {
                return array(
                    'has_error' => true,
                    'error' => 'ContractInvalid'
                );
            }

            $PS_SHOP_COUNTRY_ID = Configuration::get('PS_SHOP_COUNTRY_ID');
            if (!$PS_SHOP_COUNTRY_ID) {
                $country_enabled = Db::getInstance()->getValue(
                    'SELECT `id_country`
                    FROM `'._DB_PREFIX_.'country`
                    WHERE `active` = 1'
                );

                if (!$country_enabled || $country_enabled && !Configuration::updateValue('PS_SHOP_COUNTRY_ID', (int)$country_enabled)) {
                    return array(
                        'has_error' => true,
                        'error' => 'PS_SHOP_COUNTRY_ID'
                    );
                }
            }

            $BINARY_PATH = SdevConfiguration::get(SdevConfiguration::PARAM_BINARY_PATH);
            if (!$BINARY_PATH) {
                return array(
                    'has_error' => true,
                    'error' => 'BINARY_PATH'
                );
            }

            $PATHFILE_PATH = SdevConfiguration::get(SdevConfiguration::PARAM_PATHFILE_PATH);
            if (!$PATHFILE_PATH) {
                return array(
                    'has_error' => true,
                    'error' => 'PATHFILE_PATH'
                );
            }

            $Country = new Country($PS_SHOP_COUNTRY_ID);
            $this->context = Context::getContext();
            $this->customer = $this->context->customer;
            if (!(bool)$this->customer->isLogged(true)) {
                return array(
                    'has_error' => true,
                    'error' => 'CustomerNotLogged'
                );
            }

            if ($this->contract->sips_version == '2.0'
                && strpos($this->payment_method->method, SdevAtosPaymentMethod::FACILYPAY) !== false
                && $this->customer->company
            ) {
                return array(
                    'has_error' => true,
                    'error' => 'PaymentMethodInvalidFacilypay'
                );
            }

            $Module = Module::getInstanceByName(SdevModule::LNAME);
            $Cookie = $this->context->cookie;
            $this->cart = new Cart($Cookie->id_cart);
            $Language = new Language($this->cart->id_lang);
            $Currency = new Currency($this->cart->id_currency);

            ob_start();
            $amount = number_format($this->cart->getOrderTotal(), 2, '.', '');
            $php_logs = ob_get_clean(); // Purge PHP logs due to a bug of PrestaShop 1.5.

            $option = array();
            if (!(bool)$this->payment_method->is_enabled
                || (float)$amount < (float)$this->payment_method->min_amount
                || (float)$amount >= (float)$this->payment_method->max_amount
                || ($this->payment_method->cashing_mode != 'AUTHOR_CAPTURE'
                    && $this->payment_method->cashing_mode != 'VALIDATION'
                )
            ) {
                return array(
                    'has_error' => true,
                    'error' => 'PaymentMethodInvalid'
                );
            }

            $has_npayment = false;
            if (preg_match('([2-9]{1}x)', $this->payment_method->method, $matches)) {
                if ($this->payment_method->first_cashing_percentage == 0) {
                    return array(
                        'has_error' => true,
                        'error' => 'FirstCashingPercentage'
                    );
                }
                $has_npayment = true;
                $nb_payment = (int)str_replace('x', '', $matches[0]);
            }

            $normal_return_url = $this->context->link->getModuleLink(
                SdevModule::LNAME,
                'order',
                array(
                    'action' => 'confirmation',
                    'id_cart' => (int)$this->cart->id,
                    'id_module' => (int)$Module->id,
                    'key' => $this->customer->secure_key,
                    'token' => SdevConfiguration::get('TOKEN')
                ),
                true
            );

            $cancel_return_url = $this->context->link->getModuleLink(
                SdevModule::LNAME,
                'order',
                array(
                    'action' => 'cancel',
                    'id_cart' => (int)$this->cart->id,
                    'id_module' => (int)$Module->id,
                    'key' => $this->customer->secure_key,
                    'id_contract' => (int)$this->contract->id,
                    'token' => SdevConfiguration::get('TOKEN')
                ),
                true
            );

            $automatic_response_url = $this->context->link->getModuleLink(
                SdevModule::LNAME,
                'order',
                array(
                    'action' => 'validation',
                    'sips_version' => str_replace('.', '', $this->contract->sips_version),
                    'id_cart' => (int)$this->cart->id,
                    'id_module' => (int)$Module->id,
                    'key' => $this->customer->secure_key,
                    'nb_payment' => ((bool)$has_npayment ? (int)$nb_payment : 1),
                    'token' => SdevConfiguration::get('TOKEN')
                ),
                true
            );

            if ($this->contract->sips_version == '1.0') {
                $data = '';

                if (!(bool)$this->contract->has_3d_secure || (float)$this->payment_method->has_3d_secure_from > (float)$amount) {
                    $data .= '3D_BYPASS;';
                }

                if (SdevConfiguration::get(SdevConfiguration::PARAM_REDIRECTION) == SdevConfiguration::REDIRECTION_SHOP) {
                    $data .= 'NO_RESPONSE_PAGE_POST='.$normal_return_url.';';
                }

                if ((bool)$has_npayment && isset($nb_payment) && (int)$nb_payment > 1) {
                    $data .= 'NB_PAYMENT='.(int)$nb_payment.';'
                        .'PERIOD=30;'
                        .'INITIAL_AMOUNT='.str_replace('.', '', number_format(($amount * ($this->payment_method->first_cashing_percentage / 100)), 2, '.', ''));
                }

                $params = 'order_id='.$this->cart->id
                .' merchant_id='.$this->contract->merchant_id
                .' merchant_country='.Tools::strtolower($Country->iso_code)
                .' language='.$Language->iso_code
                .' currency_code='.$Currency->iso_code_num
                .' amount='.($amount * 100)
                .' caddie='.(int)$this->cart->id
                .' pathfile="'.$PATHFILE_PATH.'pathfile"'
                .' normal_return_url="'.$normal_return_url.'"'
                .' cancel_return_url="'.$cancel_return_url.'"'
                .' automatic_response_url="'.$automatic_response_url.'"'
                .' capture_mode='.((bool)$has_npayment
                    ? 'PAYMENT_N'
                    : $this->payment_method->cashing_mode
                )
                .' capture_day='.(int)$this->payment_method->delay
                .' customer_id='.(int)$this->cart->id_customer
                .' customer_email='.$this->customer->email
                .' payment_means=CB,2,VISA,2,MASTERCARD,2'
                .($data ? ' data="'.$data.'"' : '');

                $bin_path = $BINARY_PATH.'request'.((bool)SdevTools::isWindowsUsed()
                    ? '.exe'
                    : ($this->contract->exe_version
                        ? $this->contract->exe_version
                        : null
                    )
                );

                if (!$response = exec($bin_path.' '.$params)) {
                    return array(
                        'has_error' => true,
                        'error' => 'BinaryExecution'
                    );
                }

                $response_array = explode('!', $response);
                if ($response_array[1] == -1) {
                    return array(
                        'has_error' => true,
                        'error' => 'ApiError',
                        'error_message' => $response_array[2]
                    );
                } elseif (!isset($response_array[3])) {
                    return array(
                        'has_error' => true,
                        'error' => 'RequestExecution'
                    );
                }

                if (!SdevConfiguration::updateValue('BINARY_VERSION', $this->contract->exe_version)) {
                    return array(
                        'has_error' => true,
                        'error' => 'BinaryVersionUpdate'
                    );
                }

                $option = array(
                    'is_success' => true,
                    'template' => $response_array[3]
                );
            } elseif ($this->contract->sips_version == '2.0') {
                $transaction_reference = ($this->contract->transaction_ref_id
                    ? $this->contract->transaction_ref_id
                    : SdevDate::get('yis')
                );

                if ((bool)$has_npayment) {
                    $instalment_data = array(
                        'dates_list' => '',
                        'transaction_references_list' => '',
                        'amounts_list' => ''
                    );

                    $current_amount = $amount_total = number_format($amount, 2, '', '');
                    $amount_first = number_format(round(($amount_total * ($this->payment_method->first_cashing_percentage / 100))), 0, '', '');
                    for ($i = 0; $i < (int)$nb_payment; $i++) {
                        $instalment_data['dates_list'] .= ($i > 0 ? ',' : null).SdevDate::getNextMonth('Ymd', $i);
                        $instalment_data['transaction_references_list'] .= ($i > 0 ? ',' : null).($this->contract->transaction_reference == 'reference'
                            ? str_replace('-', '', SdevTools::getShopName(true)).($i > 0 ? $i : null)
                            : null
                        ).$transaction_reference;
                        switch ($i) {
                            case 0:
                                $amount_part = (int)$amount_first;
                                break;

                            case ((int)$nb_payment - 1):
                                $amount_part = (int)$current_amount;
                                break;

                            default:
                                $amount_part = round((int)$amount_total / ((int)$nb_payment));
                                break;
                        }
                        $current_amount -= (int)$amount_part;
                        $instalment_data['amounts_list'] .= (!$i ? null : ',').(int)$amount_part;
                    }
                }

                $data = 'orderId='.$this->cart->id
                .'|merchantId='.$this->contract->merchant_id
                .'|customerLanguage='.$Language->iso_code
                .'|currencyCode='.$Currency->iso_code_num
                .'|amount='.($amount * 100)
                .'|normalReturnUrl='.$normal_return_url.''
                .'|automaticResponseUrl='.$automatic_response_url
                .'|captureMode='.$this->payment_method->cashing_mode
                .'|captureDay='.(int)$this->payment_method->delay
                .'|customerId='.(int)$this->cart->id_customer
                .'|customerContact.email='.$this->customer->email
                .'|keyVersion='.$this->contract->key_version
                .'|orderChannel=INTERNET'
                .'|paymentPattern='.((bool)$has_npayment
                    ? 'INSTALMENT'
                        .'|instalmentData.number='.(int)$nb_payment
                        .'|instalmentData.datesList='.$instalment_data['dates_list']
                        .($this->contract->transaction_reference != 'auto'
                            ? ('|instalmentData.'
                                .($this->contract->transaction_reference == 'reference'
                                    ? 'transactionReference'
                                    : 's10TransactionId'
                                )
                                .'sList='.$instalment_data['transaction_references_list'].''
                            )
                            : null
                        )
                        .'|instalmentData.amountsList='.$instalment_data['amounts_list'].''
                    : 'ONE_SHOT'
                )
                .((bool)$this->contract->has_3d_secure ? null : '|fraudData.bypass3DS=ALL')
                .($this->contract->transaction_reference != 'auto'
                    ? ($this->contract->transaction_reference == 'reference'
                        ? '|transactionReference='.str_replace('-', '', SdevTools::getShopName(true)).$transaction_reference
                        : '|s10TransactionReference.s10TransactionId='.$transaction_reference
                    )
                    : null
                )
                .$this->getPaymentData();

                $data = base64_encode($data);
                $seal = hash_hmac('sha256', utf8_encode($data), utf8_encode($this->contract->secrete_key));
                $data = 'Data='.$data.'&InterfaceVersion=HP_2.27&Seal='.$seal.'&Encode=base64&SealAlgorithm=HMAC-SHA-256';
                $option = array(
                    'is_success' => true,
                    'data' => $data
                );
            }

            if (array_key_exists('is_success', $option) && (bool)$option['is_success']) {
                $option['has_error'] = false;
                $option['sips_version'] = $this->contract->sips_version;
                $option['is_test_mode'] = $this->contract->is_test_mode;
                $option['payment_method'] = $this->payment_method->name;
            } elseif (array_key_exists('has_error', $option) && (bool)$option['has_error']) {
                $option['is_success'] = false;
            }

            return (array)$option;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get the payment parameter for the Atos request.
     *
     * @return null|string
     * @throws Exception
     */
    private function getPaymentData()
    {
        try {
            if (!is_string($this->payment_method->method)) {
                throw new Exception('The payment_method must be a string, '.gettype($this->payment_method->method).' given !');
            }

            if ($this->payment_method->authentication_key && !ctype_alnum($this->payment_method->authentication_key)) {
                throw new Exception('The authentication key must be an alphanumeric string, '.gettype($this->payment_method->authentication_key).' given !');
            }

            if (strpos($this->payment_method->method, 'payment') !== false) {
                return '|paymentMeanBrandList=CB,VISA,MASTERCARD';
            }

            $data = '|';
            $customer_addresses = $this->customer->getAddresses($this->cart->id_lang);
            $billing_address = $customer_addresses[0];
            $shipping_address = array_key_exists('1', $customer_addresses) && is_array($customer_addresses[1]) && !empty($customer_addresses[1])
                ? $customer_addresses[1]
                : $customer_addresses[0];

            $payment_options = Tools::jsonDecode($this->payment_method->payment_options, true);
            end($payment_options);
            $last_key_payment_options = key($payment_options);
            $payment_options_cofinoga = '';
            if (!empty($payment_options)) {
                foreach ($payment_options as $key => $payment_option) {
                    $payment_options_cofinoga .= $payment_option.($key != $last_key_payment_options ? ',' : null);
                }
            }

            switch ($this->payment_method->method) {
                case SdevAtosPaymentMethod::UNEUROCOM:
                    $data .= 'paymentMeanBrandList=1EUROCOM';
                    break;

                case SdevAtosPaymentMethod::AMEXEA:
                    $data .= 'paymentMeanBrandList=AMEX';
                    break;

                case SdevAtosPaymentMethod::COFIDIS3X:
                    $data .= 'paymentMeanBrandList=COFIDIS_3X';
                    break;

                case SdevAtosPaymentMethod::COFIDIS4X:
                    $data .= 'paymentMeanBrandList=COFIDIS_4X';
                    break;

                case SdevAtosPaymentMethod::COFINOGA:
                    $data .= 'paymentMeanBrandList=COFINOGA'
                    .'|paymentMeanData.cofinoga.paymentMeanTradeOption.paymentMeanTradingName=COFINOGA' // Commercial sign of the partner accepting the means of payment.
                    .'|paymentMeanData.cofinoga.paymentMeanTradeOption.paymentMeanSettlementMode='.$payment_options_cofinoga; // Payments options
                    break;

                case SdevAtosPaymentMethod::COFINOGA3XCB:
                    $data .= 'paymentMeanBrandList=3XCBCOFINOGA'
                    .'|paymentMeanData.cofinoga.paymentMeanTradeOption.paymentMeanTradingName=COFINOGA' // Commercial sign of the partner accepting the means of payment.
                    .'|paymentMeanData.cofinoga.paymentMeanTradeOption.paymentMeanSettlementMode='.$payment_options_cofinoga; // Payments options
                    break;

                case SdevAtosPaymentMethod::FACILYPAY:
                    $data .= 'paymentMeanBrandList=ACCORD'
                    .'|paymentMeanData.facilypay.settlementMode="'.$this->payment_method->settlement_mode.'"' // The code of the commercial operation.
                    .'|paymentMeanData.facilypay.settlementModeVersion='.(int)$this->payment_method->settlement_mode_version // The version of the operator code.
                    .'|paymentMeanData.facilypay.receiverType=2' // 1 for company or 2 for private individual (force to 2 because it is only available for private individual).
                    .$this->getFacilypayData($billing_address, $shipping_address);
                    break;

                case SdevAtosPaymentMethod::FACILYPAY3X:
                    $data .= 'paymentMeanBrandList=ACCORD_3X'
                    .'|paymentMeanData.facilypay.settlementMode="'.$this->payment_method->settlement_mode.'"' // The code of the commercial operation.
                    .'|paymentMeanData.facilypay.settlementModeVersion='.(int)$this->payment_method->settlement_mode_version // The version of the operator code.
                    .'|paymentMeanData.facilypay.receiverType=2' // 1 for company or 2 for private individual (force to 2 because it is only available for private individual).
                    .$this->getFacilypayData($billing_address, $shipping_address);
                    break;

                case SdevAtosPaymentMethod::FACILYPAY4X:
                    $data .= 'paymentMeanBrandList=ACCORD_4X'
                    .'|paymentMeanData.facilypay.settlementMode="'.$this->payment_method->settlement_mode.'"' // The code of the commercial operation.
                    .'|paymentMeanData.facilypay.settlementModeVersion='.(int)$this->payment_method->settlement_mode_version // The version of the operator code.
                    .'|paymentMeanData.facilypay.receiverType=2' // 1 for company or 2 for private individual (force to 2 because it is only available for private individual).
                    .$this->getFacilypayData($billing_address, $shipping_address);
                    break;

                case SdevAtosPaymentMethod::FRANFINANCE3XCB:
                    $data .= 'paymentMeanBrandList=FRANFINANCE_3X'
                    .$this->getFranfinanceData($billing_address)
                    .'|paymentMeanData.franfinance3xcb.authenticationKey='.$this->payment_method->authentication_key;
                    break;

                case SdevAtosPaymentMethod::FRANFINANCE4XCB:
                    $data .= 'paymentMeanBrandList=FRANFINANCE_4X'
                    .$this->getFranfinanceData($billing_address)
                    .'|paymentMeanData.franfinance4xcb.authenticationKey='.$this->payment_method->authentication_key;
                    break;

                case SdevAtosPaymentMethod::PAYPAL:
                    $data .= 'paymentMeanBrandList=PAYPAL'
                    .'|customerLanguage='.Tools::strtolower(Language::getIsoById($this->cart->id_lang));
                    break;

                default:
                    $data = null;
                    break;
            }

            return $data;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get data for Franfinance.
     *
     * @param array $billing_address - Customer billing address.
     * @return string
     * @throws Exception
     */
    private function getFranfinanceData($billing_address)
    {
        try {
            if (!is_array($billing_address)) {
                throw new Exception('The billing address must be an array, '.gettype($billing_address).' given !');
            }
            return '|customerAddress.street="'.Tools::substr($billing_address['address1'], 0, 45).'"'
            .'|customerAddress.addressAdditional1="'.Tools::substr($billing_address['address2'], 0, 45).'"'
            .'|customerAddress.zipCode='.$billing_address['postcode']
            .'|customerAddress.city="'.$billing_address['city'].'"'
            .'|customerContact.email='.$this->customer->email
            .'|customerContact.lastName="'.$this->customer->lastname.'"'
            .'|customerContact.firstName="'.$this->customer->firstname.'"';
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get data for Facilypay.
     *
     * @param array $billing_address - Customer billing address.
     * @param array $shipping_address - Customer shipping address.
     * @return string
     * @throws Exception
     */
    private function getFacilypayData($billing_address, $shipping_address)
    {
        try {
            if (!is_array($billing_address)) {
                throw new Exception('The billing address must be an array, '.gettype($billing_address).' given !');
            }
            if (!is_array($shipping_address)) {
                throw new Exception('The shipping address must be an array, '.gettype($shipping_address).' given !');
            }

            $Carrier = new Carrier($this->cart->id_carrier);
            $shopping_cart_item_list = $this->getShoppingCartItemList();

            $return = '|billingContact.title='.Tools::strtoupper((new Gender($this->customer->id_gender, $this->cart->id_lang, $this->cart->id_shop))->name)
            .'|billingContact.lastname="'.$this->customer->lastname.'"'
            .'|billingAddress.streetNumber='.preg_replace('/\D/', '', $billing_address['address1'])
            .'|billingAddress.street="'.Tools::substr(preg_replace('/(\d+\s)|(\d+)(\,\s)/', '', $shipping_address['address1']), 0, 45).'"'
            .'|billingAddress.zipCode='.$billing_address['postcode']
            .'|billingAddress.city="'.$billing_address['city'].'"'
            .'|billingAddress.country='.SdevTools::getIsoAlpha3ByIdLang($this->cart->id_lang)
            .'|deliveryData.estimatedDeliveryDate='.SdevDate::getNextMonth('Ymd')
            .'|deliveryData.deliveryMode="'.'4'.'"' // 1, 2, 3, 4 or 5.
            .'|deliveryData.deliveryMethod="'.'2'.'"' // 1 for Express (less than 24h) or 2 for Standard.
            .'|deliveryData.deliveryOperator="'.$Carrier->name.'"' // Example : 'CHRONOPOST'.
            .'|deliveryContact.title='.Tools::strtoupper((new Gender($this->customer->id_gender, $this->cart->id_lang, $this->cart->id_shop))->name)
            .'|deliveryContact.lastname="'.$shipping_address['lastname'].'"'
            .'|deliveryContact.firstname="'.$shipping_address['firstname'].'"'
            .'|deliveryContact.phone='.$shipping_address['phone']
            // .'|deliveryContact.mobile='.$shipping_address['phone_mobile']
            .'|deliveryContact.email='.$this->customer->email
            .'|deliveryAddress.streetNumber='.preg_replace('/\D/', '', $shipping_address['address1'])
            .'|deliveryAddress.street="'.Tools::substr(preg_replace('/(\d+\s)|(\d+)(\,\s)/', '', $shipping_address['address1']), 0, 45).'"' // Street without number.
            .'|deliveryAddress.zipCode='.$shipping_address['postcode']
            .'|deliveryAddress.city="'.$shipping_address['city'].'"'
            .'|deliveryAddress.country='.SdevTools::getIsoAlpha3ByIdLang($this->cart->id_lang)
            .'|shoppingCartDetail.shoppingCartTotalQuantity='.(int)$shopping_cart_item_list['shoppingCartTotalQuantity']
            .'|shoppingCartDetail.mainProduct="'.$shopping_cart_item_list['mainProduct'].'"' // The product which has the highest price.
            .'|shoppingCartDetail.'.$shopping_cart_item_list['shoppingCartItemList'];

            unset($Carrier);
            return $return;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Get the 'shoppingCartItemList' data and the main product.
     *
     * @return array
     */
    private function getShoppingCartItemList()
    {
        $return = array(
            'mainProduct' => null,
            'shoppingCartTotalQuantity' => 0,
            'shoppingCartItemList' => 'shoppingCartItemList='
        );

        /** @var array $product - Product. */
        foreach ($this->cart->getProducts() as $product) {
            if (!$return['mainProduct']) {
                $return['mainProduct'] = $product;
            } elseif ((float)$product['price'] > (float)$return['mainProduct']['price']) {
                $return['mainProduct'] = $product;
            }

            $return['shoppingCartTotalQuantity'] += (int)$product['quantity'];
            $return['shoppingCartItemList'] .= '{productCode="'.$product['reference'].'"'
                .',"productDescription"="'.$product['legend'].'"'
                .',"productQuantity"="'.(int)$product['quantity'].'"'
                .',"productSKU"="'.(int)$product['id_product'].(array_key_exists('id_product_attribute', $product) && (int)$product['id_product_attribute'] > 0
                    ? '-'.(int)$product['id_product_attribute']
                    : null
                ).'"'
            .'},';
        }

        $return['mainProduct'] = $return['mainProduct']['reference'];
        $return['shoppingCartItemList'] = rtrim($return['shoppingCartItemList'], ',');
        return (array)$return;
    }
}
