<?php
/**
* 2007-2019 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class WorldlineValidationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->ajax = true;
        
        // file_put_contents('toto.txt', PHP_EOL.date("Y-m-d H:i:s").' : Data : '.$_POST['Data'], FILE_APPEND);
        // PrestaShopLogger::addLog('IP '.$_SERVER['REMOTE_ADDR'].' is callling the file validation.php number -> '.rand());

        $data = Tools::getValue('Data');
        if (isset($data) && !empty($data)) {
            $temp = explode('|', $data);
            $sets = array();

            /* We create an array containing the data returned by atos */
            foreach ($temp as $value) {
                $array = explode('=', $value);
                $sets[trim($array[0])] = trim($array[1]);
            }

            $atos_mode = Configuration::get('ATOS_MODE');
            if ($atos_mode) {
                $secret_key = Configuration::get('ATOS_SECRET_KEY');
            } else {
                $secret_key = '002001000000001_KEY1';
            }

            $seal = hash('sha256', $data.$secret_key);
            $seal_bank = Tools::getValue('Seal');

            if ($seal == $seal_bank) {
                $atos = $this->module;
                switch ($sets['responseCode']) {
                    case '00': /* Accepted */
                        $statut = _PS_OS_PAYMENT_;
                        $error  = 'Autorisation accepted';
                        break;
                    case '02': /* Perform activation via telephone with the issuer */
                        $statut = null;
                        $error  = 'Autorisation request to be performed via telephone with the issuer, ';
                        $error  .= 'as the card authorisationthreshold has been exceeded, ';
                        $error  .= 'if the forcing is authorised for the merchant';
                        break;
                    case '03': /* Invalid distance selling contract */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Invalid distance selling contract';
                        break;
                    case '05': /* Refused */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Autorisation refused';
                        break;
                    case '12': /* Invalid transaction, verify the parameters transferred in the request */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Invalid transaction, verify the parameters transferred in the request';
                        break;
                    case '14': /* Invalid bank details or card security code */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Invalid bank details or card security code';
                        break;
                    case '17': /* Buyer cancellation */
                        $statut = _PS_OS_CANCELED_;
                        $error  = 'Buyer cancellation';
                        break;
                    case '24': /* Operation impossible */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Operation impossible. ';
                        $error  .= 'The operation the merchant wishes to perform is not compatible with the status ';
                        $error  .= 'of the transaction';
                        break;
                    case '25': /* Transaction not found in the Sips database */
                        $statut = _PS_OS_ERROR_;
                        $error  = 'Transaction not found in the Sips database';
                        break;
                    case '94': /* Duplicated transaction */
                        $statut = _PS_OS_CANCELED_;
                        $error  = 'Duplicated transaction';
                        break;
                    default: /* Others cases */
                        $statut = _PS_OS_ERROR_;
                        $error  = '';
                        break;
                }
                $atos_error = Configuration::get('ATOS_ERROR_MAIL');
                // /* If payment is not accepted and user choose to send an email */
                if ($sets['responseCode'] != 00 && ($atos_error == 'mail' || $atos_error == 'mail_save')) {
                    $mails = trim(Configuration::get('ATOS_ERROR_MAILS')); /* Get the mails configured in backoffice */

                    if (strstr($mails, ',')) {
                        $mails = explode(',', $mails);
                        /* loop to send mails */
                        foreach ($mails as $mail) {
                            if (Validate::isEmail($mail)) {
                                Mail::Send(
                                    Configuration::get('PS_LANG_DEFAULT'),
                                    'notification',
                                    $atos->l('Atos notification'),
                                    array(
                                    'error_code' => $sets['responseCode'],
                                    'id_cart' => $sets['transactionReference'],
                                    'error_error' => $error
                                    ),
                                    $mail,
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    _PS_MAIL_DIR_
                                );
                            }
                        }
                    } elseif (Validate::isEmail($mails)) {
                        Mail::Send(
                            Configuration::get('PS_LANG_DEFAULT'),
                            'notification',
                            $atos->l('Atos notification'),
                            array(
                                'message'   => $atos->l('error in atos payment module with error code: '.$sets['responseCode']),
                                'error_code'  => $sets['responseCode'],
                                'id_cart'     => $sets['transactionReference'],
                                'error_error' => $error,
                            ),
                            $mails,
                            null,
                            null,
                            null,
                            null,
                            null,
                            _PS_MAIL_DIR_
                        );
                    }
                }

                if ((
                    $sets['responseCode'] != 00 &&
                    (
                        $atos_error == 'save' ||
                        $atos_error == 'mail_save'
                    )
                ) ||
                $sets['responseCode'] == 00
                ) {
                    if (Configuration::get('ATOS_TRANSACTION_REFERENCE')) {
                        $atos->validate($sets['transactionReference'], $statut, $sets['amount'] / 100);
                    } else {
                        // order id = id cart
                        $atos->validate($sets['orderId'], $statut, $sets['amount'] / 100, $sets['transactionReference']);
                    }
                }
            }
        } else {
            echo 'No data found !';
            exit;
        }

    }
}