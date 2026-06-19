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

use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevTools;
use ScaleDEV\SdevAtos\SdevDate;
use ScaleDEV\SdevAtos\SdevConfiguration;

class SdevAtosOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_left = false;
        parent::initContent();
        if (method_exists($this, Tools::getValue('action')) && Tools::getValue('token') == SdevConfiguration::get('TOKEN')) {
            $this->{Tools::getValue('action')}();
        }
    }

    public function confirmation()
    {
        $matches = null;

        // Check if a responseCode key exists on data returned by Atos and stack it into $matches var.
        preg_match('/(responseCode)(=)([0-9]*)/', base64_decode(Tools::getValue('Data')), $matches);

        // Response code 17 = Customer cancellation.
        // If cancellation or errors: redirect to the order page.
        if (is_array($matches)
            && (isset($matches[1]) && $matches[1] == 'responseCode')
            && (isset($matches[3]) && $matches[3] != '00')
        ) {
            Tools::redirect((Tools::version_compare(_PS_VERSION_, '1.5', '>')
                ? 'index.php?controller=order&'
                : 'order.php?'
            ) . 'step=3');
        }

        $order_confirmation_url = SdevTools::getShopDomain(true, true).'index.php'
            .'?controller=order-confirmation'
            .'&id_cart='.(int)Tools::getValue('id_cart')
            .'&id_module='.(int)$this->module->id
            .'&key='.Tools::getValue('key');
        $this->context->smarty->assign(array('order_confirmation_url' => $order_confirmation_url));
        $this->setTemplate(
            ((Tools::version_compare(_PS_VERSION_, '1.7', '>=')
                ? 'module:'.SdevModule::LNAME.'/views/templates/front/'
                : null
            ).'order_confirmation.tpl')
        );
    }

    public function cancel()
    {
        if (empty(Tools::getValue('DATA'))) {
            Tools::redirect('index.php');
            exit;
        }

        $exe_version = SdevAtosContract::getColumnById('exe_version', (int)Tools::getValue('id_contract'));
        $bin = SdevConfiguration::get(SdevConfiguration::PARAM_BINARY_PATH).'response'.((bool)SdevTools::isWindowsUsed() ? '.exe' : ($exe_version ? $exe_version : null));
        $result = explode('!', exec($bin.' pathfile='.SdevConfiguration::get(SdevConfiguration::PARAM_PATHFILE_PATH).'pathfile message='.preg_replace('#[^a-z0-9]#Ui', '', Tools::getValue('DATA'))));
        if (empty($result)) {
            Tools::redirect('index.php');
            exit;
        }

        $Cart = new Cart((int)$result[22]);
        $Customer = new Customer((int)$Cart->id_customer);
        Tools::redirect(
            (SdevConfiguration::get(SdevConfiguration::PARAM_PAYMENT_ERRORS) == SdevConfiguration::PAYMENT_ERRORS_SAVE
                ? (Tools::version_compare(_PS_VERSION_, '1.5', '>')
                    ? 'index.php?controller=order&'
                    : 'order.php?'
                ).'step=3'
                : (Tools::version_compare(_PS_VERSION_, '1.5', '>')
                    ? 'index.php?controller=order-confirmation&'
                    : 'order-confirmation.php?'
                ).'id_cart='.(int)$Cart->id.'&id_module='.(int)$this->module->id.'&key='.$Customer->secure_key
            )
        );
    }

    public function validation()
    {
        if (!Tools::getValue('sips_version') || (Tools::getValue('sips_version') != '10' && Tools::getValue('sips_version') != '20')) {
            header('HTTP/1.1 400 Bad Request');
            echo 'SIPS version parameter is missing.';
            die();
        }

        $this->{'validation'.Tools::getValue('sips_version')}();
    }

    public function validation10()
    {
        if (Tools::getValue('ping')) {
            die($this->ping());
        }

        if (!Tools::getValue('DATA')) {
            header('HTTP/1.1 400 Bad Request');
            echo 'DATA parameter is missing.';
            die();
        }

        $is_payment_valid = false;
        $exe_version = SdevConfiguration::get('BINARY_VERSION');
        $bin = SdevConfiguration::get(SdevConfiguration::PARAM_BINARY_PATH).'response'.((bool)SdevTools::isWindowsUsed() ? '.exe' : ($exe_version ? $exe_version : null));
        $result = explode('!', exec($bin.' pathfile='.SdevConfiguration::get(SdevConfiguration::PARAM_PATHFILE_PATH).'pathfile message='.preg_replace('#[^a-z0-9]#Ui', '', Tools::getValue('DATA'))));

        $code = $result[1];
        $error = $result[2];
        $merchant_id = $result[3];
        $merchant_country = $result[4];
        $amount = $result[5];
        $transaction_id = $result[6];
        $payment_means = $result[7];
        $transmission_date = $result[8];
        $payment_time = $result[9];
        $payment_date = $result[10];
        $response_code = $result[11];
        $payment_certificate = $result[12];
        $authorisation_id = $result[13];
        $currency_code = $result[14];
        $card_number = $result[15];
        $cvv_flag = $result[16];
        $cvv_response_code = $result[17];
        $bank_response_code = $result[18];
        $complementary_code = $result[19];
        $complementary_info = $result[20];
        $return_context = $result[21];
        $caddie = $result[22];
        $receipt_complement = $result[23];
        $merchant_language = $result[24];
        $language = $result[25];
        $id_customer = $result[26];
        $id_order = $result[27];
        $customer_email = $result[28];
        $customer_ip_address = $result[29];
        $capture_day = $result[30];
        $capture_mode = $result[31];
        $data = $result[32];

        // Creation of a log file.
        $fp = fopen(SdevModule::DIR.'logs/'.Tools::str2url(SdevDate::get('Ym')).'.log', 'a');
        if ($code == '' && $error == '') {
            fwrite($fp, SdevDate::get().' - Response executable not found in '.$bin."\n");
        } elseif ($code != 0) {
            fwrite($fp, SdevDate::get().' - Error of API call. Error message: '.$error."\n");
        } else {
            fwrite(
                $fp,
                (
                    SdevDate::get().
                    ' - merchant_id: '.$merchant_id.
                    ' | merchant_country: '.$merchant_country.
                    ' | amount: '.$amount.
                    ' | transaction_id: '.$transaction_id.
                    ' | transmission_date: '.$transmission_date.
                    ' | payment_means: '.$payment_means.
                    ' | payment_time: '.$payment_time.
                    ' | payment_date: '.$payment_date.
                    ' | response_code: '.$response_code.
                    ' | payment_certificate: '.$payment_certificate.
                    ' | authorisation_id: '.$authorisation_id.
                    ' | currency_code: '.$currency_code.
                    ' | card_number: '.$card_number.
                    ' | cvv_flag: '.$cvv_flag.
                    ' | cvv_response_code: '.$cvv_response_code.
                    ' | bank_response_code: '.$bank_response_code.
                    ' | complementary_code: '.$complementary_code.
                    ' | complementary_info: '.$complementary_info.
                    ' | return_context: '.$return_context.
                    ' | caddie: '.$caddie.
                    ' | receipt_complement: '.$receipt_complement.
                    ' | merchant_language: '.$merchant_language.
                    ' | language: '.$language.
                    ' | id_customer: '.$id_customer.
                    ' | id_order: '.$id_order.
                    ' | customer_email: '.$customer_email.
                    ' | customer_ip_address: '.$customer_ip_address.
                    ' | capture_day: '.$capture_day.
                    ' | capture_mode: '.$capture_mode.
                    ' | data: '.$data."\n"
                )
            );
        }
        fclose($fp);

        if ($response_code == '00') {
            $is_payment_valid = true;
        }

        $amount = (int)$amount / 100;
        $Cart = new Cart((int)$id_order);
        $Customer = new Customer((int)$id_customer);

        // Get payment number.
        if (preg_match('/NB_PAYMENT=[0-9]{1}/', $data, $matches) != false) {
            $nb_payment = (int)str_replace('NB_PAYMENT=', '', $matches[0]);
        } else {
            $nb_payment = 1;
        }

        $label = !Tools::version_compare(_PS_VERSION_, '1.6')
            ? ' ('.$payment_means.((int)$nb_payment > 1 ? ' '.(int)$nb_payment.'X' : null).')'
            : null;

        if ((bool)$is_payment_valid) {
            $this->module->validateOrder(
                $id_order,
                Configuration::get('PS_OS_PAYMENT'),
                $amount,
                ($this->module->displayName.$label),
                ($this->module->l('Payment in').' '.$payment_means.' '.(int)$nb_payment.'X'),
                array('transaction_id' => $transaction_id),
                null,
                false,
                $Customer->secure_key
            );
        } elseif (SdevConfiguration::get(SdevConfiguration::PARAM_PAYMENT_ERRORS) == SdevConfiguration::PAYMENT_ERRORS_SAVE) {
            $new_cart = $Cart->duplicate();
            $this->module->validateOrder(
                $new_cart['cart']->id,
                Configuration::get('PS_OS_ERROR'),
                $amount,
                ($this->module->displayName.$label),
                ($this->module->l('Error of payment').' '.$payment_means.' '.(int)$nb_payment.'X'),
                array('transaction_id' => $transaction_id),
                null,
                false,
                $Customer->secure_key
            );
        }

        exit;
    }

    public function validation20()
    {
        $data = array();
        $is_payment_valid = false;

        $response = array(
            'Data' => Tools::getValue('Data'),
            'Encode' => Tools::getValue('Encode'),
            'Seal' => Tools::getValue('Seal'),
            'InterfaceVersion' => Tools::getValue('InterfaceVersion')
        );

        if ($response['Encode'] == 'base64' || $response['Encode'] == 'base64url') {
            $response['Data'] = explode('|', base64_decode($response['Data']));
            foreach ($response['Data'] as $param) {
                $param_exploded = explode('=', $param);
                $data[$param_exploded[0]] = $param_exploded[1];
                unset($param_exploded);
            }
        }

        // Creation of a log file.
        $fp = fopen(SdevModule::DIR.'logs/'.Tools::str2url(SdevDate::get('Ym')).'.log', 'a');
        $log_message = SdevDate::get().' - ';
        switch ($data['responseCode']) {
            case '03':
                $log_message .= 'Invalid merchant contract.';
                break;

            case '05':
                $log_message .= 'Authorization denied.';
                break;

            case '12':
                $log_message .= 'Invalid transaction, check the parameters transferred in the request.';
                break;

            case '14':
                $log_message .= 'Invalid payment method details (e.g. card number or visual cryptogram of the card) or failed AVS check.';
                break;

            case '17':
                $log_message .= 'Cancellation of the buyer.';
                break;

            case '30':
                $log_message .= 'Format error.';
                break;

            case '34':
                $log_message .= 'Suspicion of fraud (seal error).';
                break;

            case '54':
                $log_message .= 'Validity date of the payment method expired.';
                break;

            case '60':
                $log_message .= 'Pending transaction.';
                break;

            case '63':
                $log_message .= 'Security rules not respected, transaction stopped.';
                break;

            case '75':
                $log_message .= 'Number of attempts to enter payment method details under Sips Paypage exceeded.';
                break;

            case '90':
                $log_message .= 'Service temporarily unavailable.';
                break;

            case '94':
                $log_message .= 'Duplicate transaction : the transactionReference of the transaction is already used.';
                break;

            case '97':
                $log_message .= 'Session expired (no user action for 15 minutes), transaction refused.';
                break;

            case '99':
                $log_message .= 'Temporary problem with the payment server.';
                break;

            case '00':
                if ($data['acquirerResponseCode'] == '00') {
                    $log_message .= 'merchant_id: '.$data['merchantId']
                    .' | amount: '.($data['amount'] / 100)
                    .' | transaction_reference: '.$data['transactionReference']
                    .' | transaction_date: '.$data['transactionDateTime']
                    .' | payment_mean: '.$data['paymentMeanType'].' - '.$data['paymentMeanBrand']
                    .' | response_code: '.$data['responseCode']
                    .' | authorisation_id: '.$data['authorisationId']
                    .' | currency_code: '.$data['currencyCode']
                    .(array_key_exists('maskedPan', $data)
                        ? ' | card_number: '.$data['maskedPan']
                        : null
                    )
                    .(array_key_exists('cardCSCResultcode', $data)
                        ? ' | cvv_response_code: '.$data['cardCSCResultCode']
                        : null
                    )
                    . '| bank_response_code: '.$data['acquirerResponseCode']
                    .' | id_customer: '.$data['customerId']
                    .' | id_order: '.$data['orderId']
                    .' | customer_email: '.$data['customerEmail']
                    .' | customer_id_address: '.$data['customerIpAddress']
                    .' | capture_day: '.$data['captureDay']
                    .' | capture_mode: '.$data['captureMode']
                    .' | payment_pattern: '.$data['paymentPattern'];
                }
                break;
        }

        fwrite($fp, $log_message."\n");
        fclose($fp);
        unset($log_message);

        if ($data['responseCode'] == '00') {
            $is_payment_valid = true;
        }

        if ($data['paymentPattern'] == 'ONE_SHOT') {
            $nb_payment = '1X';
        } elseif ($data['paymentPattern'] == 'INSTALMENT') {
            $nb_payment = (int)Tools::getValue('nb_payment').'X';
        }

        $amount = (int)$data['amount'] / 100;
        $Cart = new Cart((int)$data['orderId']);
        $Customer = new Customer((int)$data['customerId']);

        $contract = SdevAtosContract::getByMerchantId($data['merchantId']);
        if ($contract && $contract->transaction_reference == 'id') {
            if (isset($data['s10TransactionId']) && $data['s10TransactionId']) {
                $transaction_id = $data['s10TransactionId'];
            } else {
                $transaction_id = Tools::substr($data['transactionReference'], 2, 2).Tools::substr($data['transactionReference'], 10, 4);
            }
        } else {
            $transaction_id = $data['transactionReference'];
        }

        $label = !Tools::version_compare(_PS_VERSION_, '1.6')
            ? ' ('.$data['paymentMeanBrand'].($data['paymentPattern'] == 'INSTALMENT' ? ' '.$nb_payment : null).')'
            : null;

        if ((bool)$is_payment_valid) {
            $this->module->validateOrder(
                (int)$Cart->id,
                Configuration::get('PS_OS_PAYMENT'),
                $amount,
                ($this->module->displayName.$label),
                ($this->module->l('Payment in').' '.$nb_payment),
                array('transaction_id' => $transaction_id),
                (int)$Cart->id_currency,
                false,
                $Customer->secure_key
            );
        } elseif (SdevConfiguration::get(SdevConfiguration::PARAM_PAYMENT_ERRORS) == SdevConfiguration::PAYMENT_ERRORS_SAVE) {
            $new_cart = $Cart->duplicate();
            $this->module->validateOrder(
                $new_cart['cart']->id,
                Configuration::get('PS_OS_ERROR'),
                $amount,
                ($this->module->displayName.$label),
                ($this->module->l('Error of payment').' '.$nb_payment),
                array('transaction_id' => $transaction_id),
                (int)$Cart->id_currency,
                false,
                $Customer->secure_key
            );
        }

        exit;
    }

    public function ping()
    {
        echo 'ping';
    }
}
