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
 *
 * ...........................................................................
 *
 * CHANGELOG:
 * 1.0.0 (2019-10-25): First module stable version.
 */

ini_set('auto_detect_line_endings', true);
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__).'/autoload.php');

class SdevAtos extends PaymentModule
{
    /** @var string $root_dir - Root directory. */
    private $root_dir;

    /** @var array $bank_list - Banks list. */
    private $bank_list = array(
        'elysnet' => 'CCF - Elysnet',
        'etransactions' => 'Crédit Agricole - Etransactions',
        'hsbc' => 'HSBC - Elysnet',
        'mercanet' => 'BNP Paribas - Mercanet',
        'scelliusnet' => 'Banque Postale - Scelliusnet',
        'sherlocks' => 'LCL - Sherlocks',
        'sogenactif' => 'Société Générale - Sogenactif',
        'webaffaires' => 'Crédit du Nord - Webaffaires'
    );

    /** @var array $exe_list - Executables list. */
    private $exe_list = array();

    /** @var array $payment_method_list - Payment method list. */
    private $payment_method_list = array();

    /** @var array $module_warning_list - List of modules to which it is necessary to pay attention. */
    private $module_warning_list = array(
        'atos',
        'cw06atos'
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->name = 'sdevatos';
        $this->version = '1.2.1';
        $this->author = 'ScaleDEV';
        $this->displayName = $this->l('Atos - Worldline');
        $this->description = $this->l('Secure payment module for Atos - Worldline');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
        $this->tab = 'payments_gateways';
        $this->controller_tab = 'AdminAtos';
        $this->module_key = 'e2f609d58dd2308b454f1aacc2d9d142';

        $ps_version_max = Tools::version_compare(_PS_VERSION_, '1.7.6') ? '1.7.6' : _PS_VERSION_;
        $this->ps_versions_compliancy = array('min' => '1.5.4', 'max' => $ps_version_max);

        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        if (!function_exists('curl_init')) {
            $this->warning = $this->l('The PHP cURL extension isn\'t installed. The module will doesn\'t work if you don\'t install it.');
        }

        if ((bool)$this->isOtherModuleInstalled()) {
            $this->warning = $this->l('Another Atos module is installed on your shop. This can cause malfunctions independant of our module.');
        }

        $this->setRootDir(_PS_ROOT_DIR_.'/');

        // JavaScript translations.
        $this->setJsDefL(array(
            'ErrorMessage' => $this->l('An error has occured. If the problem persists please contact us.'),
            'DeletingContract' => $this->l('Are you sure you want to delete this contract ?'),
            'DeletingPaymentMethod' => $this->l('Are your sure you want to delete this payment method ?'),
            'AccessDenied' => $this->l('Access denied !'),
            'UnavailableContract' => $this->l('The contract linked to this payment method is unavailable.'),
            'UnavailablePaymentMethod' => $this->l('This payment method is unavailable.')
        ));

        $this->setPaymentMethodList(array(
            SdevAtosPaymentMethod::PAYMENT1XCB => $this->l('Credit card (1x)'),
            SdevAtosPaymentMethod::PAYMENT2XCB => $this->l('Credit card (2x)'),
            SdevAtosPaymentMethod::PAYMENT3XCB => $this->l('Credit card (3x)'),
            SdevAtosPaymentMethod::UNEUROCOM => '1euro.com',
            SdevAtosPaymentMethod::AMEXEA => 'Amex EA (Enhanced Authorization)',
            SdevAtosPaymentMethod::COFIDIS3X => 'Cofidis (3xCB)',
            SdevAtosPaymentMethod::COFIDIS4X => 'Cofidis (4xCB)',
            SdevAtosPaymentMethod::COFINOGA => 'Cofinoga',
            SdevAtosPaymentMethod::COFINOGA3XCB => 'Cofinoga (3xCB)',
            SdevAtosPaymentMethod::FRANFINANCE3XCB => 'Franfinance (3xCB)',
            SdevAtosPaymentMethod::FRANFINANCE4XCB => 'Franfinance (4xCB)',
            SdevAtosPaymentMethod::FACILYPAY => 'Facilypay',
            SdevAtosPaymentMethod::FACILYPAY3X => 'Facilypay (3x)',
            SdevAtosPaymentMethod::FACILYPAY4X => 'Facilypay (4x)',
            SdevAtosPaymentMethod::PAYPAL => 'PayPal',
        ));

        $exe_list = array();
        $exe_path = SdevAtosModule::DIR.'bin/';
        if (is_dir($exe_path) && $dir = opendir($exe_path)) {
            while (($file = readdir($dir)) !== false) {
                if (strstr($file, 'request') !== false && $file != 'request' && $file != 'request.exe') {
                    $exe_list[str_replace('request', '', $file)] = str_replace('request_', '', $file);
                }
            }
        }
        asort($exe_list);
        $this->setExeList($exe_list);
    }

    /**
     * Install module.
     *
     * @return bool
     */
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install()
            && (bool)$this->installTabs()
            && (bool)SdevAtosModule::install();
    }

    /**
     * Install tabs.
     *
     * @return bool
     */
    private function installTabs()
    {
        $id_parent = Tools::version_compare(_PS_VERSION_, '1.7', '>=')
            ? Tab::getIdFromClassName('AdminParentModulesSf')
            : Tab::getIdFromClassName('AdminParentModules');

        $tabs = array(
            'Index' => $this->displayName,
            'Info' => $this->l('Informations'),
            'Config' => $this->l('Parameters')
        );

        $i = 0;
        foreach ($tabs as $tab => $lang_name) {
            $Tab = new Tab();
            foreach (Language::getLanguages(true) as $language) {
                $Tab->name[$language['id_lang']] = $lang_name;
            }

            $Tab->class_name = 'Admin'.$tab.$this->name;
            $Tab->module = $this->name;
            $Tab->id_parent = $id_parent;
            $Tab->position = Tab::getNewLastPosition($id_parent);
            $Tab->active = ($i == 0) ? true : false;
            $Tab->add();
            $i++;
        }

        return true;
    }

    /**
     * Uninstall module.
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall()
            && (bool)$this->uninstallTabs()
            && (bool)SdevAtosModule::uninstall();
    }

    /**
     * Uninstall tabs.
     *
     * @return bool
     */
    private function uninstallTabs()
    {
        foreach (TabCore::getCollectionFromModule($this->name) as $tab) {
            if (!$tab->delete()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check the module warning list.
     *
     * @return bool
     */
    private function isOtherModuleInstalled()
    {
        foreach ($this->module_warning_list as $module) {
            if ((bool)Module::isInstalled($module)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get content.
     */
    public function getContent()
    {
        Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminIndex'.$this->name));
    }

    /**
     * Get classes from directories.
     *
     * @return array
     */
    public function getClassesFromDir($path, $host_mode = false)
    {
        $classes = array();
        $root_dir = $host_mode
            ? $this->normalizeDirectory(_PS_ROOT_DIR_)
            : $this->getRootDir();

        foreach (scandir($root_dir.$path) as $file) {
            if ($file[0] != '.') {
                if (is_dir($root_dir.$path.$file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path.$file.'/', $host_mode));
                } elseif (Tools::substr($file, -4) == '.php') {
                    $content = Tools::file_get_contents($root_dir.$path.$file);
                    $namespace_pattern = '[\\a-z0-9_]*[\\]';
                    $pattern = '#\W((abstract\s+)?class|interface)\s+(?P<classname>'
                        .basename($file, '.php').'(?:Core)?)'.'(?:\s+extends\s+'
                        .$namespace_pattern.'[a-z][a-z0-9_]*)?(?:\s+implements\s+'
                        .$namespace_pattern.'[a-z][\\a-z0-9_]*(?:\s*,\s*'
                        .$namespace_pattern.'[a-z][\\a-z0-9_]*)*)?\s*\{#i';
                    if (preg_match($pattern, $content, $m)) {
                        $classes[$m['classname']] = array(
                            'path' => $path.$file,
                            'type' => trim($m[1]),
                            'override' => $host_mode
                        );

                        if (Tools::substr($m['classname'], -4) == 'Core') {
                            $classes[Tools::substr($m['classname'], 0, -4)] = array(
                                'path' => '',
                                'type' => $classes[$m['classname']]['type'],
                                'override' => $host_mode
                            );
                        }
                    }
                }
            }
        }

        return (array)$classes;
    }

    /**
     * Normalize a directory.
     *
     * @return string
     */
    public function normalizeDirectory($directory)
    {
        return rtrim($directory, '/\\').DIRECTORY_SEPARATOR;
    }

    /**
     * Test if this module has overrides.
     *
     * @return bool
     */
    public function hasOverrides()
    {
        return (bool)file_exists($this->getRootDir().'override/'.$this->name.'/'.$this->name.'.php');
    }

    /**
     * Set medias.
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        if (Tools::getValue('controller') != 'AdminIndex'.$this->name) {
            return;
        }

        $views_path = $this->_path.'views/';
        $css_path = $views_path.'css/';
        $js_path = $views_path.'js/';

        $Controller = $this->context->controller;
        $Controller->addCSS($css_path.'back.css');
        $Controller->addCSS($css_path.'font-awesome.min.css');
        if (Tools::version_compare(_PS_VERSION_, '1.6')) {
            $Controller->addCSS($css_path.'bootstrap.min.css');
            $Controller->addCSS($css_path.'back15.css');
        } else {
            $Controller->addJS(_PS_BO_ALL_THEMES_DIR_.'default/js/tree.js');
        }
        if (!(bool)SdevAtosModule::isAngularLoaded($Controller)) {
            $Controller->addJS($js_path.'angular-1.5.0/angular.min.js');
        }
        $Controller->addJS($js_path.'back.js');
    }

    /**
     * Hook for the front-office - Header.
     */
    public function hookHeader($params)
    {
        $this->addFrontAssets();
    }

    /**
     * Hook for the front-office - Display header.
     */
    public function hookDisplayHeader($params)
    {
        $this->addFrontAssets();
    }

    /**
     * Add CSS and JS to the front-office.
     */
    private function addFrontAssets()
    {
        $views_path = $this->_path.'views/';
        $Controller = $this->context->controller;
        $Controller->addCSS($views_path.'css/front.css');
        if (!(bool)SdevAtosModule::isAngularLoaded($Controller)) {
            $Controller->addJS($views_path.'js/angular-1.5.0/angular.min.js');
        }
        $Controller->addJS($views_path.'js/back.js');
    }

    /**
     * Hook - Display payment.
     */
    public function hookDisplayPayment($params)
    {
        if ($params['cart']->getOrderTotal() < 1.00) {
            $has_error = true;
            $payment_method_list = array();
        } else {
            $has_error = false;
            $total_amount = number_format($params['cart']->getOrderTotal(), 2, '.', '') * 100;
            $payment_method_list = (array)SdevAtosPaymentMethod::read();
            foreach ($payment_method_list as $key => $payment_method) {
                if (!SdevAtosPaymentMethod::shopAssociated($payment_method['id_payment_method'], $this->context->shop->id)) {
                    unset($payment_method_list[$key]);
                    continue;
                }

                if (!$payment_method['is_enabled'] || ($payment_method['min_amount'] * 100 > $total_amount) || ($payment_method['max_amount'] * 100 <= $total_amount)) {
                    unset($payment_method_list[$key]);
                }
            }
        }

        // Templates utilities.
        $links = array(
            'front_controller' => Tools::getHttpHost(true)._MODULE_DIR_.$this->name.'/controllers/front/'
        );

        SdevAtosModule::smartyAssign(array(
            'links' => (array)$links,
            'has_error' => (bool)$has_error,
            'payment_method_list' => (array)$payment_method_list
        ));

        return $this->display(__FILE__, SdevAtosModule::HOOK_PATH.'front/display_payment.tpl');
    }

    /**
     * Hook - Payment options.
     *
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function hookPaymentOptions($params)
    {
        if (!(bool)$this->active || !isset($params['cart']) || !Validate::isLoadedObject($params['cart'])) {
            return [];
        }

        $Ws = new SdevAtosWs();
        $option_list = array();
        foreach (SdevAtosPaymentMethod::read() as $payment_method) {
            if (!SdevAtosPaymentMethod::shopAssociated($payment_method['id_payment_method'], $this->context->shop->id)) {
                continue;
            }

            if ($params['cart']->getOrderTotal() <= $payment_method['min_amount']) {
                continue;
            }

            $sipsVersion = SdevAtosPaymentMethod::getSipsVersion($payment_method['id_payment_method']);
            $PaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            if ($sipsVersion == '1.0') {
                ($PaymentOption
                    ->setCallToActionText($payment_method['name'])
                    ->setAction($this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        array(
                            'token' => Configuration::get(Tools::strtoupper($this->name) . '_TOKEN')
                        ),
                        true
                    ))
                    ->setInputs(array(
                        'PaymentMethodId' => array(
                            'name' => $this->name.'-payment-method-id',
                            'type' => 'hidden',
                            'value' => (int)$payment_method['id_payment_method']
                        )
                    ))
                );

                $option_list[] = $PaymentOption;
            } elseif ($sipsVersion == '2.0') {
                $form = $Ws->getForm((int)$payment_method['id_payment_method']);
                $data = explode('&', $form['data']);

                if (array_key_exists('is_success', $form) && (bool)$form['is_success']) {
                    ($PaymentOption
                        ->setCallToActionText($form['payment_method'])
                        ->setAction(((bool)$form['is_test_mode'] ? SdevAtosWs::URL_TEST : SdevAtosWs::URL_PROD))
                        ->setInputs(array(
                            'Data' => array(
                                'name' => 'Data',
                                'type' => 'hidden',
                                'value' => str_replace('Data=', '', $data[0])
                            ),
                            'InterfaceVersion' => array(
                                'name' => 'InterfaceVersion',
                                'type' => 'hidden',
                                'value' => str_replace('InterfaceVersion=', '', $data[1]),
                            ),
                            'Seal' => array(
                                'name' => 'Seal',
                                'type' => 'hidden',
                                'value' => str_replace('Seal=', '', $data[2])
                            ),
                            'Encode' => array(
                                'name' => 'Encode',
                                'type' => 'hidden',
                                'value' => str_replace('Encode=', '', $data[3])
                            ),
                            'SealAlgorithm' => array(
                                'name' => 'SealAlgorithm',
                                'type' => 'hidden',
                                'value' => str_replace('SealAlgorithm=', '', $data[4])
                            )
                        ))
                    );

                    $option_list[] = $PaymentOption;
                }
            }

            unset($PaymentOption);
        }

        return (array)$option_list;
    }

    /**
     * Hook - Payment return.
     */
    public function hookPaymentReturn($params)
    {
        $Order = Tools::version_compare(_PS_VERSION_, '1.7') ? $params['objOrder'] : $params['order'];
        if (!Validate::isLoadedObject($Order)) {
            return;
        }

        if ($Order->module != $this->name) {
            return;
        }

        if ($Order->valid || $Order->current_state == _PS_OS_PAYMENT_) {
            SdevAtosModule::smartyAssign(array(
                'status' => 'ok',
                'id_order' => $Order->id
            ));
        } else {
            SdevAtosModule::smartyAssign(array(
                'status' => 'failed'
            ));
        }

        $this->context->smarty->assign(array(
            'shop_name' => $this->context->shop->name,
            'contact_link' => $this->context->link->getPageLink('contact', true)
        ));

        return $this->display(__FILE__, SdevAtosModule::HOOK_PATH.'front/order_confirmation.tpl');
    }

    /**
     * Get the root directory.
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->root_dir;
    }

    /**
     * Set the root directory.
     *
     * @param string $root_dir - Root directory.
     */
    private function setRootDir($root_dir)
    {
        $this->root_dir = $root_dir;
    }

    /**
     * Get the hook path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /** @var array $js_def_l - JavaScript translations. */
    private $js_def_l;

    /**
     * Get JavaScript translations.
     *
     * @return array
     */
    public function getJsDefL()
    {
        return (array)$this->js_def_l;
    }

    /**
     * Set JavaScript translations.
     *
     * @param array $js_def_l - JavaScript translations.
     */
    private function setJsDefL($js_def_l)
    {
        $this->js_def_l = (array)$js_def_l;
    }

    /**
     * Get banks list.
     *
     * @return array
     */
    public function getBankList()
    {
        return (array)$this->bank_list;
    }

    /**
     * Get payment method list.
     *
     * @param string $sips_version - SIPS version.
     * @return array
     */
    public function getPaymentMethodList($sips_version)
    {
        $payment_method_list = (array)$this->payment_method_list;
        switch ($sips_version) {
            case '1.0':
                foreach (array_keys($payment_method_list) as $payment_method) {
                    if (!preg_match('[(payment)([1-9]*)(x)(cb)]', $payment_method)) {
                        unset($payment_method_list[$payment_method]);
                    }
                }
                return (array)$payment_method_list;
            case '2.0':
                return (array)$payment_method_list;
            default:
                return (array)$payment_method_list;
        }
    }

    /**
     * Set payment method list.
     *
     * @param array $payment_method_list - Payment method list.
     */
    private function setPaymentMethodList($payment_method_list)
    {
        $this->payment_method_list = (array)$payment_method_list;
    }

    /**
     * Get the executables list.
     *
     * @return array
     */
    public function getExeList()
    {
        return (array)$this->exe_list;
    }

    /**
     * Set the executables list.
     *
     * @param array $exe_list - Executables list.
     */
    private function setExeList($exe_list)
    {
        $this->exe_list = (array)$exe_list;
    }

    /**
     * Get the payment options from Cofinoga.
     *
     * @return array
     */
    public function getPaymentOptionsListCofinoga()
    {
        return array(
            'MR_DEBITCB' => $this->l('DEBIT (MR_DEBITCB)'),
            'MR_DIFFERE' => $this->l('DIFFERED (MR_DIFFERE)'),
            'MR_DEBIT' => $this->l('CHOICE OF STATEMENT (MR_DEBIT)'),
            'MR_CPT' => $this->l('PRIVILEGED CASH (MR_CPT)'),
            'MR_CREDITCB' => $this->l('CREDIT (MR_CREDITCB)'),
            'MR_CREDIT' => $this->l('CREDIT (MR_CREDIT)'),
            'MR_REPORT3M' => $this->l('CREDIT WITH REPORT 3 MONTHS (MR_REPORT3M)'),
            'MR_COMP' => $this->l('IMMEDIATE CASH PAYMENT (MR_COMP)'),
            'MR_6X' => $this->l('6 TIMES AT 0% RATE (MR_6X)'),
            'MR_10XCOMPENS' => $this->l('PAYMENT IN 10 TIMES (MR_10XCOMPENS)'),
            'MR_12X' => $this->l('PAYMENT IN 12 TIMES (MR_12X)'),
            'MR_18X' => $this->l('PAYMENT IN 18 TIMES (MR_18X)'),
            'MR_20X' => $this->l('PAYMENT IN 20 TIMES (MR_20X)'),
            'MR_24X' => $this->l('PAYMENT IN 24 TIMES (MR_24X)'),
            'MR_ESC5' => $this->l('DISCOUNT 5% (MR_ESC5)'),
            'MR_ESC5GR3M' => $this->l('DISCOUNT 5% + 3 TIMES AT 0% RATE (MR_ESC5GR3M)'),
            'MR_ESC10' => $this->l('DISCOUNT 10% (MR_ESC10)'),
            'MR_ESC10GR3M' => $this->l('DISCOUNT 10% + 3 FS TAUX 0% (MR_ESC10GR3M)'),
            'MR_ESC20' => $this->l('DISCOUNT 20% (MR_ESC20)'),
            'MR_COFREP3M' => $this->l('FREE REPORT 3 MONTHS (MR_COFREP3M)'),
            'MR_REP3CPT' => $this->l('REPORT 3 MONTH CASH (MR_REP3CPT)'),
            'MR_2XSFRAIS' => $this->l('2 TIMES WITHOUT CHARGE (MR_2XSFRAIS)'),
            'MR_CDREDITGR3M' => $this->l('3 TIMES AT 0% RATE (MR_CDREDITGR3M)'),
            'MR_3X' => $this->l('3 TIMES FEE REQUIRED (MR_3X)'),
            'MR_CDGPGR3M' => $this->l('3 TIMES WITHOUT CHARGE (MR_CDGPGR3M)'),
            'MR_4VERSEG' => $this->l('4 TIMES AT 0% RATE (MR_4VERSEG)'),
            'MR_4XSFRAIS' => $this->l('4 TIMES AT 0% RATE (MR_4XSFRAIS)'),
            'MR_GR5M' => $this->l('5 TIMES AT 0% RATE (MR_GR5M)'),
            'MR_5XFFT' => $this->l('5 TIMES FIXED-CHARGE (MR_5XFFT)'),
            'MR_5X' => $this->l('5 TIMES FEE REQUIRED (MR_5X)'),
            'MR_5XPAY526' => $this->l('5 TIMES FEE REQUIRED (MR_5XPAY526)'),
            'MR_5XPAY538' => $this->l('5 TIMES FEE REQUIRED (MR_5XPAY538)'),
            'MR_6XGR' => $this->l('6 TIMES AT 0% RATE (MR_6XGR)'),
            'MR_CREDITGR6M' => $this->l('6 TIMES AT 0% RATE (MR_CREDITGR6M)'),
            'MR_10XGR' => $this->l('10 TIMES AT 0% RATE (MR_10XGR)'),
            'MR_10XCOF' => $this->l('10 TIMES FEE REQUIRED (MR_10XCOF)'),
            'MR_10XPAY' => $this->l('10 TIMES FEE REQUIRED (MR_10XPAY)'),
            'MR_10XPAY515' => $this->l('10 TIMES FEE REQUIRED (MR_10XPAY515)'),
            'MR_10XPROMO' => $this->l('10 TIMES PROMO (MR_10XPROMO)'),
            'MR_10XCOMP' => $this->l('11 TIMES FEE REQUIRED (MR_10XCOMP)'),
            'MR_20XPAY' => $this->l('20 TIMES FEE REQUIRED (MR_20XPAY)'),
            'MR_20XCOMP' => $this->l('21 TIMES FEE REQUIRED (MR_20XCOMP)'),
        );
    }
}
