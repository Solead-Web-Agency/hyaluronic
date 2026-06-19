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

use ScaleDEV\HelperForm;
use ScaleDEV\SdevAtos\SdevModule;
use ScaleDEV\SdevAtos\SdevAtosForm;
use ScaleDEV\SdevAtos\SdevTools;
use ScaleDEV\SdevAtos\SdevConfiguration;

require_once(dirname(__FILE__).'../../../autoload.php');

class AdminConfigsdevatosController extends ModuleAdminController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        parent::__construct();
    }

    /**
     * Display a template.
     */
    public function ajaxProcessDisplay()
    {
        $SdevAtosForm = new SdevAtosForm();
        $HelperForm = new HelperForm();
        $HelperForm->margin_form_attr = array('class' => 'col-lg-12');
        $fields_list = array(
            // START CONTRACT CONFIGURATION
            'general' => array(
                'redirection' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.general.redirection',
                        'ng-model' => 'configuration.general.redirection',
                        'id' => 'configuration[general][redirection]',
                        'class' => 'form-control',
                        'name' => 'configuration[general][redirection]'
                    ),
                    'options' => array(
                        SdevConfiguration::REDIRECTION_SHOP => $this->l('To your shop'),
                        SdevConfiguration::REDIRECTION_ATOS => $this->l('To the Atos confirmation page')
                    )
                )),
                'payment_errors' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.general.payment_errors',
                        'ng-model' => 'configuration.general.payment_errors',
                        'id' => 'configuration[general][payment_errors]',
                        'class' => 'form-control',
                        'name' => 'configuration[general][payment_errors]'
                    ),
                    HelperForm::OPTIONS => array(
                        SdevConfiguration::PAYMENT_ERRORS_SAVE => $this->l('Save the order as a payment error and keep the cart'),
                        SdevConfiguration::PAYMENT_ERRORS_NOTHING => $this->l('Doing nothing')
                    )
                )),
                'binary_path' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.general.binary_path',
                        'ng-model' => 'configuration.general.binary_path',
                        'id' => 'configuration[general][binary_path]',
                        'class' => 'form-control',
                        'name' => 'configuration[general][binary_path]',
                        'maxlength' => 100
                    ),
                    HelperForm::HELPER => $this->l('100 characters maximum').'.'
                )),
                'pathfile_path' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.general.pathfile_path',
                        'ng-model' => 'configuration.general.pathfile_path',
                        'id' => 'configuration[general][pathfile_path]',
                        'class' => 'form-control',
                        'name' => 'configuration[general][pathfile_path]',
                        'maxlength' => 100
                    ),
                    HelperForm::HELPER => $this->l('100 characters maximum').'.'
                )),
                /*
                 * @since 1.1.0
                 * Allows to display the debug mode with an IP filtering.
                 */
                'ip_filtering' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.general.ip_filtering',
                        'ng-model' => 'configuration.general.ip_filtering',
                        'id' => 'configuration[general][ip_filtering]',
                        'name' => 'configuration[general][ip_filtering]',
                        'class' => 'form-control',
                    )
                )),
            ),
            // END GENERAL CONFIGURATION

            // START CONTRACT CONFIGURATION
            'contract' => array(
                'exe_version' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.exe_version',
                        'ng-model' => 'configuration.contract.exe_version',
                        'id' => 'configuration[contract][exe_version]',
                        'name' => 'configuration[contract][exe_version]'
                    ),
                    HelperForm::DEFAULT_OPTION => $this->l('Normal'),
                    HelperForm::OPTIONS => (array)$this->module->getExeList()
                )),
                'bank' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.bank',
                        'ng-model' => 'configuration.contract.bank',
                        'id' => 'configuration[contract][bank]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][bank]'
                    ),
                    HelperForm::DEFAULT_OPTION => array('0' => $this->l('- Select your bank')),
                    HelperForm::OPTIONS => (array)$this->module->getBankList()
                )),
                'merchant_id_1' => array(HelperForm::TypeText => array(
                    HelperForm::FORM_GROUP_ATTR => array('ng-if' => 'configuration.contract.sips_version == \'1.0\''),
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.merchant_id',
                        'ng-model' => 'configuration.contract.merchant_id',
                        'id' => 'configuration[contract][merchant_id]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][merchant_id]',
                        'maxlength' => 15,
                        'pattern' => '[0-9]{15}',
                        'readonly' => true,
                        'autocomplete' => 'off'
                    ),
                    HelperForm::HELPER => $this->l('The Merchant ID is automatically generated by the certificate.')
                )),
                'merchant_id_2' => array(HelperForm::TypeText => array(
                    HelperForm::FORM_GROUP_ATTR => array('ng-if' => 'configuration.contract.sips_version == \'2.0\''),
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.merchant_id',
                        'ng-model' => 'configuration.contract.merchant_id',
                        'id' => 'configuration[contract][merchant_id]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][merchant_id]',
                        'maxlength' => 15,
                        'pattern' => '[0-9]{15}',
                        'autocomplete' => 'off'
                    ),
                    HelperForm::HELPER => $this->l('The Merchant ID is composed of 15 digits. Your bank should have provided you with this information. If this is not the case, please contact your bank advisor.')
                )),
                'certificate' => array(HelperForm::TypeFile => array(
                    HelperForm::ATTR => array(
                        'file' => 'configuration.contract.certificate',
                        'id' => 'configuration[contract][certificate]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][certificate]'
                    ),
                    HelperForm::HELPER => $this->l('The certificate must be named, for example : "certif.fr.012345678912345".')
                )),
                'secrete_key' => array(HelperForm::TypePassword => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.secrete_key',
                        'ng-model' => 'configuration.contract.secrete_key',
                        'id' => 'configuration[contract][secrete_key]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][secrete_key]',
                        'autocomplete' => 'off'
                    )
                )),
                'key_version' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.key_version',
                        'ng-model' => 'configuration.contract.key_version',
                        'id' => 'configuration[contract][key_version]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][key_version]',
                        'maxlength' => 10,
                        'pattern' => '[0-9]+'
                    )
                )),
                'transaction_ref_id' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.transaction_ref_id',
                        'ng-model' => 'configuration.contract.transaction_ref_id',
                        'id' => 'configuration[contract][transaction_ref_id]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][transaction_ref_id]'
                    ),
                    HelperForm::HELPER => $this->l('Leave blank if it is generated by SIPS').'.'
                )),
                'transaction_reference' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.contract.transaction_reference',
                        'ng-model' => 'configuration.contract.transaction_reference',
                        'id' => 'configuration[contract][transaction_reference]',
                        'class' => 'form-control',
                        'name' => 'configuration[contract][transaction_reference]'
                    ),
                    HelperForm::OPTIONS => array(
                        'reference' => $this->l('Reference (composed by my shop name and the date)'),
                        'id' => $this->l('ID (composed by the date)'),
                        'auto' => $this->l('Automatically generated by my bank')
                    ),
                )),
            ),
            // END CONTRACT CONFIGURATION

            // START PAYMENT METHOD CONFIGURATION
            'payment_method' => array(
                'id_contract' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.id_contract; checkPaymentMethodContract()',
                        'ng-model' => 'configuration.payment_method.id_contract',
                        'ng-change' => 'checkPaymentMethodContract()',
                        'id' => 'configuration[payment_method][id_contract]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][id_contract]'
                    ),
                    HelperForm::DEFAULT_OPTION => $this->l('- Select a contract to link'),
                    HelperForm::OPTIONS => array(
                        '[[contract.id_contract]]' => '#[[contract.id_contract]] - [[contract.bank_name]]'
                    ),
                    HelperForm::OPTIONS_ATTR => array(
                        'ng-repeat' => 'contract in contracts'
                    )
                )),
                'name' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.name',
                        'ng-model' => 'configuration.payment_method.name',
                        'ng-keyup' => 'checkPaymentMethodName()',
                        'id' => 'configuration[payment_method][name]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][name]'
                    )
                )),
                'method' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.method',
                        'ng-model' => 'configuration.payment_method.method',
                        'ng-change' => 'checkPaymentMethodMethod()',
                        'id' => 'configuration[payment_method][method]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][method]'
                    ),
                    HelperForm::DEFAULT_OPTION => $this->l('- Select a payment method'),
                    HelperForm::OPTIONS => array(
                        '[[pm_key]]' => '[[pm_value]]'
                    ),
                    HelperForm::OPTIONS_ATTR => array(
                        'ng-repeat' => '(pm_key, pm_value) in payment_method_list'
                    )
                )),
                'authentication_key' => array(HelperForm::TypePassword => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.authentication_key',
                        'ng-model' => 'configuration.payment_method.authentication_key',
                        'ng-keyup' => 'checkPaymenyMethodAuthenticationKey()',
                        'id' => 'configuration[payment_method][authentication_key]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][authentication_key]',
                        'autocomplete' => 'off'
                    )
                )),
                'settlement_mode' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.settlement_mode',
                        'ng-model' => 'configuration.payment_method.settlement_mode',
                        'ng-keyup' => 'checkPaymentMethodSettlementMode()',
                        'id' => 'configuration[payment_method][settlement_mode]',
                        'class' => 'form_control',
                        'name' => 'configuration[payment_method][settlement_mode]',
                    )
                )),
                'settlement_mode_version' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.settlement_mode_version',
                        'ng-model' => 'configuration.payment_method.settlement_mode_version',
                        'ng-keyup' => 'checkPaymentMethodSettlementModeVersion()',
                        'id' => 'configuration[payment_method][settlement_mode_version]',
                        'class' => 'form_control',
                        'name' => 'configuration[payment_method][settlement_mode_version]',
                        'pattern' => '[0-9]+'
                    )
                )),
                'payment_options' => array(HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.payment_options',
                        'ng-model' => 'configuration.payment_method.payment_options',
                        'id' => 'configuration[payment_method][payment_options]',
                        'name' => 'configuration[payment_method][payment_options]',
                        'multiple' => true
                    ),
                    HelperForm::OPTIONS => (array)$this->module->getPaymentOptionsListCofinoga()
                )),
                'min_amount' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.min_amount',
                        'ng-model' => 'configuration.payment_method.min_amount',
                        'ng-keyup' => 'checkPaymentMethodMinAmount()',
                        'id' => 'configuration[payment_method][min_amount]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][min_amount]',
                        'pattern' => '[0-9]+([\.][0-9]+)?'
                    ),
                    HelperForm::PREFIX => '€'
                )),
                'max_amount' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.max_amount',
                        'ng-model' => 'configuration.payment_method.max_amount',
                        'ng-keyup' => 'checkPaymentMethodMaxAmount()',
                        'id' => 'configuration[payment_method][max_amount]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][max_amount]',
                        'pattern' => '[0-9]+([\.][0-9]+)?'
                    ),
                    HelperForm::PREFIX => '€'
                )),
                'has_3d_secure_from' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.has_3d_secure_from',
                        'ng-model' => 'configuration.payment_method.has_3d_secure_from',
                        'ng-keyup' => 'checkPaymentMethodHas3dSecureFrom()',
                        'id' => 'configuration[payment_method][has_3d_secure_from]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][has_3d_secure_from]',
                        'pattern' => '[0-9]+([\.][0-9]+)?'
                    ),
                    HelperForm::PREFIX => '€'
                )),
                'delay' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.delay',
                        'ng-model' => 'configuration.payment_method.delay',
                        'ng-keyup' => 'checkPaymentMethodDelay()',
                        'id' => 'configuration[payment_method][delay]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][delay]',
                        'pattern' => '[0-9]'
                    ),
                    HelperForm::PREFIX => $this->l('days')
                )),
                'first_cashing_percentage' => array(HelperForm::TypeText => array(
                    HelperForm::ATTR => array(
                        'ng-init' => 'configuration.payment_method.first_cashing_percentage',
                        'ng-model' => 'configuration.payment_method.first_cashing_percentage',
                        'ng-keyup' => 'checkPaymentMethodFirstCashingPercentage()',
                        'id' => 'configuration[payment_method][first_cashing_percentage]',
                        'class' => 'form-control',
                        'name' => 'configuration[payment_method][first_cashing_percentage]',
                        'pattern' => '[0-9]+([\.][0-9]+)?'
                    ),
                    HelperForm::PREFIX => '%'
                ))
            )
            // END PAYMENT METHOD CONFIGURATION
        );

        if (Tools::version_compare(_PS_VERSION_, '1.6')) {
            $shops = array();
            foreach (Shop::getShops(false) as $id_shop => $shop) {
                $shops[$id_shop] = $shop['name'];
            }

            $fields_list['payment_method']['shop'] = array(
                HelperForm::TypeSelect => array(
                    HelperForm::ATTR => array(
                        'id' => 'configuration[payment_method][shops]',
                        'name' => 'configuration[payment_method][shops]',
                        'class' => 'method-shop-select',
                        'multiple' => true
                    ),
                    HelperForm::OPTIONS => $shops
                )
            );
        }

        $HelperForm->setFieldList($fields_list);
        $configuration = (array)$HelperForm->getForm();
        $configuration['general']['debug_mode'] = $SdevAtosForm->switcher(array(
            'margin_form_attr' => array('class' => 'col-lg-12'),
            'btn_one' => $this->l('Enabled'),
            'btn_one_attr' => array(
                'ng-model' => 'configuration.general.debug_mode',
                'value' => '1',
                'id' => 'configuration[general][debug_mode]_1',
                'name' => 'configuration[general][debug_mode]'
            ),
            'btn_two' => $this->l('Disabled'),
            'btn_two_attr' => array(
                'ng-model' => 'configuration.general.debug_mode',
                'value' => '0',
                'id' => 'configuration[general][debug_mode]_0',
                'name' => 'configuration[general][debug_mode]'
            ),
            'switcher_attr' => array('ng-init' => 'configuration.general.debug_mode')
        ));

        $configuration['contract']['sips_version'] = $SdevAtosForm->switcher(array(
            'margin_form_attr' => array('class' => 'col-lg-12'),
            'btn_one' => '2.0',
            'btn_one_attr' => array(
                'ng-model' => 'configuration.contract.sips_version',
                'value' => '2.0',
                'id' => 'configuration[contract][sips_version]_1',
                'name' => 'configuration[contract][sips_version]'
            ),
            'btn_two' => '1.0',
            'btn_two_attr' => array(
                'ng-model' => 'configuration.contract.sips_version',
                'value' => '1.0',
                'id' => 'configuration[contract][sips_version]_0',
                'name' => 'configuration[contract][sips_version]'
            ),
            'switcher_attr' => array(
                'ng-init' => 'configuration.contract.sips_version'
            )
        ));

        $configuration['contract']['test_mode'] = $SdevAtosForm->switcher(array(
            'form_group_attr' => array('ng-if' => 'configuration.contract.sips_version == \'2.0\''),
            'margin_form_attr' => array('class' => 'col-lg-12'),
            'btn_one' => $this->l('Enabled'),
            'btn_one_attr' => array(
                'ng-model' => 'configuration.contract.is_test_mode',
                'value' => '1',
                'id' => 'configuration[contract][is_test_mode]_1',
                'name' => 'configuration[contract][is_test_mode]'
            ),
            'btn_two' => $this->l('Disabled'),
            'btn_two_attr' => array(
                'ng-model' => 'configuration.contract.is_test_mode',
                'value' => '0',
                'id' => 'configuration[contract][is_test_mode]_0',
                'name' => 'configuration[contract][is_test_mode]'
            ),
            'switcher_attr' => array(
                'ng-init' => 'configuration.contract.is_test_mode'
            )
        ));

        $configuration['contract']['has_3d_secure'] = $SdevAtosForm->switcher(array(
            'form_group_attr' => array('class' => 'col-lg-3 col-sm-4 col-xs-12'),
            'btn_one' => $this->l('Enabled'),
            'btn_one_attr' => array(
                'ng-model' => 'configuration.contract.has_3d_secure',
                'value' => '1',
                'id' => 'configuration[contract][has_3d_secure]_1',
                'name' => 'configuration[contract][has_3d_secure]'
            ),
            'btn_two' => $this->l('Disabled'),
            'btn_two_attr' => array(
                'ng-model' => 'configuration.contract.has_3d_secure',
                'value' => '0',
                'id' => 'configuration[contract][has_3d_secure]_0',
                'name' => 'configuration[contract][has_3d_secure]'
            ),
            'switcher_attr' => array(
                'ng-init' => 'configuration.contract.has_3d_secure'
            )
        ));

        $configuration['payment_method']['is_enabled'] = $SdevAtosForm->switcher(array(
            'form_group_attr' => array('class' => 'col-lg-3 col-sm-4 col-xs-12'),
            'btn_one' => $this->l('Yes'),
            'btn_one_attr' => array(
                'ng-model' => 'configuration.payment_method.is_enabled',
                'value' => '1',
                'id' => 'configuration[payment_method][is_enabled]_1',
                'name' => 'configuration[payment_method][is_enabled]'
            ),
            'btn_two' => $this->l('No'),
            'btn_two_attr' => array(
                'ng-model' => 'configuration.payment_method.is_enabled',
                'value' => '0',
                'id' => 'configuration[payment_method][is_enabled]_0',
                'name' => 'configuration[payment_method][is_enabled]'
            ),
            'switcher_attr' => array(
                'ng-init' => 'configuration.payment_method.is_enabled'
            )
        ));

        $configuration['payment_method']['cashing_mode'] = $SdevAtosForm->switcher(array(
            'form_group_attr' => ['class' => 'col-lg-3 col-sm-4 col-xs-12'],
            'btn_one' => $this->l('Automatic'),
            'btn_one_attr' => array(
                'ng-model' => 'configuration.payment_method.cashing_mode',
                'value' => 'AUTHOR_CAPTURE',
                'id' => 'configuration[payment_method][cashing_mode]_1',
                'name' => 'configuration[payment_method][cashing_mode]'
            ),
            'btn_two' => $this->l('Manual'),
            'btn_two_attr' => array(
                'ng-model' => 'configuration.payment_method.cashing_mode',
                'value' => 'VALIDATION',
                'id' => 'configuration[payment_method][cashing_mode]_0',
                'name' => 'configuration[payment_method][cashing_mode]'
            ),
            'switcher_attr' => array(
                'ng-init' => 'configuration.payment_method.cashing_mode'
            )
        ));


        if (!Tools::version_compare(_PS_VERSION_, '1.6')) {
            $payment_method_shops_tree = new HelperTreeShops('method-shop-tree', $this->l('Shops'));
            $configuration['payment_method']['shop'] = $payment_method_shops_tree->render();
        }

        SdevModule::smartyAssign(array(
            'form' => $SdevAtosForm,
            'configuration' => (array)$configuration,
            'bank_list' => (array)$this->module->getBankList()
        ));

        die($this->module->display(SdevModule::DIR, 'views/templates/admin/config.tpl'));
    }

    /**
     * Gets the current IP address to add it into the list for IP filtering.
     *
     * @since 1.1.0
     */
    public function ajaxProcessGetIpAddress()
    {
        SdevTools::sendAjaxResult(array(
            'ip_address' => SdevTools::getIpAddress(),
        ));
    }

    /**
     * Get configuration.
     */
    public function ajaxProcessGetConfiguration()
    {
        $has_error = false;
        $errors = array();

        $configuration = array();
        $parameters = (array)SdevConfiguration::getDefaultParameterList(true);
        foreach ($parameters as $parameter) {
            $response = SdevConfiguration::get($parameter);
            if ($response === false) {
                $has_error = true;
                $errors[] = Tools::strtoupper($parameter);
            } else {
                $configuration[$parameter] = $response;
            }
        }

        SdevTools::sendAjaxResult(array(
            'is_success' => $has_error ? false : true,
            'has_error' => (bool)$has_error,
            'configuration' => (array)$configuration,
            'errors' => empty($errors) ? false : (array)$errors
        ));
    }

    /**
     * Update configuration.
     */
    public function ajaxProcessUpdateConfiguration()
    {
        $has_error = false;
        $errors = array();
        $BINARY_PATH_OLD = SdevConfiguration::get(SdevConfiguration::PARAM_BINARY_PATH);
        $PATHFILE_PATH_OLD = SdevConfiguration::get(SdevConfiguration::PARAM_PATHFILE_PATH);
        $configuration = SdevTools::decodeRequest()['configuration'];
        foreach ($configuration as $parameter => $value) {
            if ($parameter == SdevConfiguration::PARAM_BINARY_PATH || $parameter == SdevConfiguration::PARAM_PATHFILE_PATH) {
                $value = rtrim($value, '/').'/';
            }
            if (!SdevConfiguration::updateValue($parameter, $value)) {
                $has_error = true;
                $errors[] = Tools::strtoupper($parameter);
            }
        }

        $BINARY_PATH = SdevConfiguration::get(SdevConfiguration::PARAM_BINARY_PATH);
        if (!$BINARY_PATH) {
            $BINARY_PATH = SdevModule::DIR.'bin/';
        }
        if ($BINARY_PATH_OLD != $BINARY_PATH) {
            $binary_dir = '';
            foreach (explode('/', $BINARY_PATH) as $folder) {
                if (!$folder) {
                    $binary_dir .= '/';
                    continue;
                }
                $binary_dir .= $folder.'/';
                if (!is_dir($binary_dir)) {
                    mkdir($binary_dir);
                }
            }
            unset($binary_dir);
            foreach (scandir($BINARY_PATH_OLD) as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($BINARY_PATH.$file)) {
                        unlink($BINARY_PATH.$file);
                    }
                    rename($BINARY_PATH_OLD.$file, $BINARY_PATH.$file);
                }
            }
        }

        $PATHFILE_PATH = SdevConfiguration::get(SdevConfiguration::PARAM_PATHFILE_PATH);
        if (!$PATHFILE_PATH) {
            $PATHFILE_PATH = SdevModule::DIR.'param/';
        }
        if ($PATHFILE_PATH_OLD != $PATHFILE_PATH) {
            $pathfile_dir = '';
            foreach (explode('/', $PATHFILE_PATH) as $folder) {
                if (!$folder) {
                    $pathfile_dir .= '/';
                    continue;
                }
                $pathfile_dir .= $folder.'/';
                if (!is_dir($pathfile_dir)) {
                    mkdir($pathfile_dir);
                }
            }
            unset($pathfile_dir);
            foreach (scandir($PATHFILE_PATH_OLD) as $file) {
                if ($file != '.' && $file != '..') {
                    if (file_exists($PATHFILE_PATH.$file)) {
                        unlink($PATHFILE_PATH.$file);
                    }
                    rename($PATHFILE_PATH_OLD.$file, $PATHFILE_PATH.$file);
                }
            }
        }

        // START - Creation of the pathfile file.
        $fp = fopen($PATHFILE_PATH.'pathfile', 'w');
        fwrite($fp, 'DEBUG!'.((bool)SdevConfiguration::get('DEBUG_MODE') ? 'YES' : 'NO').'!'."\n");
        fwrite($fp, 'D_LOGO!'.$this->module->getPath().'views/img/payments/!'."\n");
        fwrite($fp, 'F_DEFAULT!'.$PATHFILE_PATH.'parmcom.defaut!'."\n");
        fwrite($fp, 'F_PARAM!'.$PATHFILE_PATH.'parmcom!'."\n");
        fwrite($fp, 'F_CERTIFICATE!'.$PATHFILE_PATH.'certif!'."\n");
        fclose($fp);
        // END - Creation of the pathfile file.

        SdevTools::sendAjaxResult(array(
            'is_success' => $has_error ? false : true,
            'has_error' => (bool)$has_error,
            'errors' => empty($errors) ? false : (array)$errors
        ));
    }

    /**
     * Read all contracts.
     */
    public function ajaxProcessReadContract()
    {
        $contract_list = (array)SdevAtosContract::read();
        foreach ($contract_list as &$contract) {
            $contract['bank_name'] = $this->module->getBankList()[$contract['bank']];
        }
        SdevTools::sendAjaxResult(array(
            'contracts_number' => (int)count($contract_list),
            'contracts' => (array)$contract_list
        ));
    }

    /**
     * Update a contract.
     */
    public function ajaxProcessUpdateContract()
    {
        $is_updated = false;
        $has_error = false;
        $errors = array();
        $contract = Tools::jsonDecode(Tools::getValue('contract'), true);
        if (is_array($contract) && !empty($contract)) {
            if ($contract['sips_version'] == '1.0') {
                if (array_key_exists('file', $_FILES) && !$_FILES['file']['error']) {
                    $file = $_FILES['file'];
                    $filename_exploded = explode('.', $file['name']);
                    $merchant_id = array_pop($filename_exploded);
                    if (Tools::strlen($merchant_id) == 15 && preg_match('/[0-9]/', $merchant_id)) {
                        $certificate_dir = SdevModule::DIR.'param/'.$file['name'];
                        if (file_exists($certificate_dir)) {
                            unlink($certificate_dir);
                        }
                        move_uploaded_file($file['tmp_name'], SdevModule::DIR.'param/'.basename($file['name']));
                    } else {
                        $has_error = true;
                        $errors[] = 'CERTIFICATE';
                    }
                }

                $contract['is_test_mode'] = false;
                $contract['secrete_key'] = null;
                $contract['key_version'] = null;
                $contract['transaction_reference'] = 'id';
            } else {
                $contract['exe_mode'] = 'automatic';
                $contract['exe_version'] = null;
            }

            if (!preg_match('/[0-9]/', $contract['merchant_id']) || Tools::strlen($contract['merchant_id']) != 15) {
                $has_error = true;
                $errors[] = 'MERCHANT_ID';
            }

            if (!(bool)$has_error) {
                $SdevAtosContract = array_key_exists('id_contract', $contract)
                    ? new SdevAtosContract((int)$contract['id_contract'])
                    : new SdevAtosContract();
                $SdevAtosContract->sips_version = $contract['sips_version'];
                $SdevAtosContract->is_test_mode = (bool)$contract['is_test_mode'];
                $SdevAtosContract->exe_mode = $contract['exe_mode'];
                $SdevAtosContract->exe_version = $contract['exe_version'];
                $SdevAtosContract->bank = $contract['bank'];
                $SdevAtosContract->merchant_id = $contract['merchant_id'];
                $SdevAtosContract->secrete_key = $contract['secrete_key'];
                $SdevAtosContract->key_version = $contract['key_version'];
                $SdevAtosContract->transaction_reference = $contract['transaction_reference'];
                $SdevAtosContract->transaction_ref_id = isset($contract['transaction_ref_id']) && $contract['transaction_ref_id'] ? $contract['transaction_ref_id'] : null;
                $SdevAtosContract->has_3d_secure = (bool)$contract['has_3d_secure'];
                $is_updated = (bool)$SdevAtosContract->save();

                // START - Creation of the parmcom file linked to the contract.
                if ($SdevAtosContract->sips_version == '1.0') {
                    $PATHFILE_PATH = SdevConfiguration::get('PATHFILE_PATH');
                    if (!$PATHFILE_PATH) {
                        $PATHFILE_PATH = SdevModule::DIR.'param/';
                    }

                    if (file_exists($PATHFILE_PATH.'parmcom.'.$contract['merchant_id'])) {
                        unlink($PATHFILE_PATH.'parmcom.'.$contract['merchant_id']);
                    }

                    file_put_contents(
                        $PATHFILE_PATH.'parmcom.'.$contract['merchant_id'],
                        Tools::file_get_contents($PATHFILE_PATH.'parmcom.'.$contract['bank'])
                    );
                }
                // END - Creation of the parmcom file linked to the contract.
            }
        }

        SdevTools::sendAjaxResult(array(
            'is_success' => (bool)$is_updated,
            'has_error' => (bool)$has_error,
            'errors' => empty((array)$errors) ? false : (array)$errors
        ));
    }

    /**
     * Delete a contract.
     */
    public function ajaxProcessDeleteContract()
    {
        $is_deleted = false;
        $id_contract = (int)SdevTools::decodeRequest()['id_contract']; // update
        $SdevAtosContract = new SdevAtosContract((int)$id_contract);
        if ((bool)$SdevAtosContract->delete()) {
            $is_deleted = true;

            $certificate = $SdevAtosContract->getCertificateFileName();
            if ($certificate && file_exists(SdevModule::DIR.'param/'.$certificate)) {
                unlink(SdevModule::DIR.'param/'.$certificate);
            }

            $parmcom = $SdevAtosContract->getParmcomFileName();
            if ($parmcom && file_exists(SdevModule::DIR.'param/'.$parmcom)) {
                unlink(SdevModule::DIR.'param/'.$parmcom);
            }
        }

        SdevTools::sendAjaxResult(array(
            'is_success' => (bool)$is_deleted,
            'has_error' => (bool)$is_deleted ? false : true,
            'has_payment_method_deleted' => (bool)SdevAtosPaymentMethod::deleteByIdContract((int)$id_contract)
        ));
    }

    /**
     * Correct execution rights.
     */
    public function ajaxProcessCorrectExeRights()
    {
        $has_error = false;
        $errors = array();
        $exe_path = SdevModule::DIR.'bin/';
        if (is_dir($exe_path) && ($dir = opendir($exe_path))) {
            while (($file = readdir($dir)) !== false) {
                if ($file != '..' && $file != '.' && $file != 'index.php') {
                    $chmod = chmod($exe_path.$file, 0755);
                    if (!$chmod) {
                        $has_error = true;
                        $errors[] = $file;
                    }
                }
            }
        }

        SdevTools::sendAjaxResult(array(
            'is_success' => $has_error ? false : true,
            'has_error' => (bool)$has_error,
            'errors' => empty($errors) ? false : (array)$errors
        ));
    }

    /**
     * Read all payment methods.
     */
    public function ajaxProcessReadPaymentMethod()
    {
        $payment_method_list = (array)SdevAtosPaymentMethod::read();
        if (!empty($payment_method_list)) {
            foreach ($payment_method_list as &$payment_method) {
                $payment_method['payment_options'] = Tools::jsonDecode($payment_method['payment_options'], true);
                $payment_method['shops'] = SdevAtosPaymentMethod::getShops((int)$payment_method['id_payment_method']);
            }
        }
        SdevTools::sendAjaxResult(array(
            'payment_methods_number' => (int)count($payment_method_list),
            'payment_methods' => (array)$payment_method_list
        ));
    }

    /**
     * Update a payment method.
     */
    public function ajaxProcessUpdatePaymentMethod()
    {
        $is_updated = false;
        $payment_method = SdevTools::decodeRequest()['payment_method'];
        if (is_array($payment_method) && !empty($payment_method)) {
            $SdevAtosPaymentMethod = array_key_exists('id_payment_method', $payment_method)
                ? new SdevAtosPaymentMethod($payment_method['id_payment_method'])
                : new SdevAtosPaymentMethod();
            $SdevAtosPaymentMethod->id_contract = (int)$payment_method['id_contract'];
            $SdevAtosPaymentMethod->name = $payment_method['name'];
            $SdevAtosPaymentMethod->method = $payment_method['method'];
            $SdevAtosPaymentMethod->authentication_key = $payment_method['authentication_key'];
            $SdevAtosPaymentMethod->settlement_mode = $payment_method['settlement_mode'];
            $SdevAtosPaymentMethod->settlement_mode_version = $payment_method['settlement_mode_version'];
            $SdevAtosPaymentMethod->payment_options = Tools::jsonEncode((array)$payment_method['payment_options'], true);
            $SdevAtosPaymentMethod->is_enabled = (bool)$payment_method['is_enabled'];
            $SdevAtosPaymentMethod->min_amount = number_format($payment_method['min_amount'], 6, '.', '');
            $SdevAtosPaymentMethod->max_amount = number_format($payment_method['max_amount'], 6, '.', '');
            $SdevAtosPaymentMethod->has_3d_secure_from = number_format($payment_method['has_3d_secure_from'], 6, '.', '');
            $SdevAtosPaymentMethod->cashing_mode = $payment_method['cashing_mode'];
            $SdevAtosPaymentMethod->first_cashing_percentage = number_format($payment_method['first_cashing_percentage'], 6, '.', '');
            $SdevAtosPaymentMethod->delay = (int)$payment_method['delay'];
            $is_updated = (bool)$SdevAtosPaymentMethod->save();

            $SdevAtosPaymentMethod->addShops((array)$payment_method['shops']);
        }

        SdevTools::sendAjaxResult(array(
            'is_success' => (bool)$is_updated,
            'has_error' => (bool)!$is_updated
        ));
    }

    /**
     * Delete a payment method.
     */
    public function ajaxProcessDeletePaymentMethod()
    {
        $is_deleted = (bool)SdevAtosPaymentMethod::deleteById((int)SdevTools::decodeRequest()['id_payment_method']);
        SdevTools::sendAjaxResult(array(
            'is_success' => (bool)$is_deleted,
            'has_error' => (bool)$is_deleted ? false : true
        ));
    }

    /**
     * Get the payment method list.
     */
    public function ajaxProcessGetPaymentMethodList()
    {
        SdevTools::sendAjaxResult(array(
            'payment_method_list' => (array)$this->module->getPaymentMethodList(SdevTools::decodeRequest()['sips_version'])
        ));
    }
}
