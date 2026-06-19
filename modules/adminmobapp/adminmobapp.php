<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file.
 * You are not authorized to modify, copy or redistribute this file.
 * Permissions are reserved by FME Modules.
 *
 *  @author    FMM Modules
 *  @copyright FME Modules 2024
 *  @license   Single domain
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Adminmobapp extends Module
{
    protected $config_form = false;
    private $container;
    public $hasSubscription = false;
    public function __construct()
    {
        $this->name = 'adminmobapp';
        $this->tab = 'administration';
        $this->version = '1.0.7';
        $this->author = 'FMM Modules';
        $this->need_instance = 1;
        $this->module_key = '15e2364fe254aefad5f493621c176721';
        
        $this->bootstrap = true;

        parent::__construct();

        if ($this->container === null) {
            $this->container = new PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
        $this->displayName = $this->l('Prestashop Admin Mobile APP');
        $this->description = $this->l('Access your PrestaShop store admin dashboard and manage orders, products, and customers conveniently from your mobile device using this module.');
        $this->confirmUninstall = $this->l('Are you Sure ?');
        $this->ps_versions_compliancy = array('min' => '9.0.0', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        $this->installDependencies();
        $base_url = Tools::getShopDomainSsl(true).__PS_BASE_URI__;
        Configuration::updateValue('ADMINMOBAPP_BASE_URL', $base_url);
        Configuration::updateValue('ADMINMOBAPP_SHOP_NAME', 'PrestashopAdminApp');
        Configuration::updateValue('ADMINMOBAPP_SHOP_FCM', 'H3aK8oA1nV');
        Configuration::updateValue('ADMINMOBAPP_AUTH_TOKEN', 'H3aK8oP6qR2sX9tY56A1bF7gJ4mD0nV');
        Configuration::updateValue('ADMINMOBAPP_CONNECTION_KEY', 'H3aK8oP6');
        Configuration::updateValue('ADMINMOBAPP_ORDER_NOTIFICATION', 1);
        Configuration::updateValue('ADMINMOBAPP_CUSTOMER_NOTIFICATION', 1);
        Configuration::updateValue('ADMINMOBAPP_PRODUCT_PRICE', 1);
        Configuration::updateValue('ADMINMOBAPP_EMPLOYEES', 1);

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('ModuleRoutes') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function installDependencies()
    {
        $moduleManager = ModuleManagerBuilder::getInstance()->build();
        
        /* PS Account */
        if (!$moduleManager->isInstalled('ps_accounts')) {
            $moduleManager->install('ps_accounts');
        } elseif (!$moduleManager->isEnabled('ps_accounts')) {
            $moduleManager->enable('ps_accounts');
            $moduleManager->upgrade('ps_accounts');
        } else {
            $moduleManager->upgrade('ps_accounts');
        }
    }

    public function getShopKeyId()
    {
        $psService = Module::getInstanceByName('ps_accounts')
            ->getService('PrestaShop\Module\PsAccounts\Service\PsAccountsService');
        if (method_exists($psService, 'getShopUuid')) {
            return $psService->getShopUuid();
        }

        return $psService->getShopUuidV4();
    }

    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $mboInstaller = new \Prestashop\ModuleLibMboInstaller\DependencyBuilder($this);

        if( !$mboInstaller->areDependenciesMet() )
        {
            $dependencies = $mboInstaller->handleDependencies();

            $this->smarty->assign('dependencies', $dependencies);

            return $this->display(__FILE__, 'views/templates/admin/dependency_builder.tpl');
        }

        $output = '';
        $moduleManager = ModuleManagerBuilder::getInstance()->build();
        $accountsService = null;
        try {
            $accountsFacade = $this->getService('adminmobapp.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $accountsInstaller = $this->getService('adminmobapp.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->getService('adminmobapp.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->name),
            ]);

            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            return '';
        }
        /**********************
         * PrestaShop Billing *
         * *******************/

        // Load the context for PrestaShop Billing
        $billingFacade = $this->getService('adminmobapp.ps_billings_facade');
        $partnerLogo = $this->getLocalPath() . 'views/img/fmm_logo.png';

        // Retrieve the subscritpion for this module
        $subscription = $this->getService('adminmobapp.ps_billings_service')->getCurrentSubscription();
        $this->hasSubscription = ($subscription && true == $subscription['success']);
        // PrestaShop Billing\
        
        Media::addJsDef($billingFacade->present([
            'logo' => $partnerLogo,
            'tosLink' => 'https://www.fmemodules.com/en/content/1-terms-conditions',
            'privacyLink' => 'https://www.fmemodules.com/en/content/3-privacy-policy',
            'emailSupport' => 'info@fmemodules.com',
            'currentSubscription' => $subscription,
        ]));
        $shopUuid = $this->getShopKeyId();

        $this->context->smarty->assign([
            'urlBilling' => 'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js',
        ]);

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure_saas.tpl');
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/livesupport.tpl');

        /**
         * If values have been submitted in the form, process.
         */
        $adminLink = new Link();
        $admin_url = $adminLink->getAdminLink('AdminLogin', true);
        Configuration::updateValue('ADMINMOBAPP_ADMIN_LOGIN', $admin_url);
        
        if (((bool)Tools::isSubmit('submitAdminmobappModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAdminmobappModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $configValues = $this->getConfigFormValues();
        $this->context->smarty->assign('configValues', $configValues);

        $employees = Employee::getEmployees();
        $employeeOptions = array();
        foreach ($employees as $employee) {
            $employeeOptions[] = array(
                'id_employee' => $employee['id_employee'],
                'name' => $employee['firstname'] . ' ' . $employee['lastname']
            );
        }

        $save_ids = Configuration::get('ADMINMOBAPP_EMPLOYEES');
        $selected_employee = [];
        if (!empty($save_ids) && is_string($save_ids)) {
            $selected_employee = explode(',', $save_ids);
        }
        
        $this->context->smarty->assign(array(
            'selected_employee' => $selected_employee,
        ));

        $this->context->smarty->assign([
            'qrcode_url' => $this->getQRCodeUrl(),
        ]);

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'employeeOptions' => $employeeOptions,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    private function getQRCodeUrl()
    {
        $url = $this->context->shop->getBaseURL(true);
        $connection_key = Configuration::get('ADMINMOBAPP_CONNECTION_KEY');
        $auth_token = Configuration::get('ADMINMOBAPP_AUTH_TOKEN');

        $data = [
            'url' => $url,
            'connection_key' => $connection_key,
            'auth_token' => $auth_token,
        ];
        $qrCodeData = http_build_query($data);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrCodeData);
        return $qrCodeUrl;
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        
        $logoUrl = $this->getLogoUrl();
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'required' => true,
                        'prefix' => '<i class="icon-text-width"></i>',
                        'desc' => $this->l('Enter a valid Shop Name : MegaStore'),
                        'name' => 'ADMINMOBAPP_SHOP_NAME',
                        'label' => $this->l('Shop Name'),
                    ),


                    array(
                        'col' => 4,
                        'type' => 'text',
                        'required' => true,
                        'prefix' => '<i class="icon-unlink"></i>',
                        'name' => 'ADMINMOBAPP_BASE_URL',
                        'readonly' => true,
                        'label' => $this->l('Base URL'),
                    ),
                    array(
                        'type' => 'file',
                        'name' => 'ADMINMOBAPP_LOGO',
                        'label' => $this->l('App Logo'),
                    ),

                    array(
                        'type' => 'html',
                        'name' => 'logo_display',
                        'label' => '',
                        'html_content' => '',
                    ),

                    array(
                        'type' => 'connection',
                        'required' => true,
                        'name' => 'ADMINMOBAPP_CONNECTION_KEY',
                        'label' => $this->l('APP Connection Key'),
                    ),

                    array(
                        'type' => 'auth_token',
                        'required' => true,
                        'name' => 'ADMINMOBAPP_AUTH_TOKEN',
                        'label' => $this->l('API Auth Token'),
                    ),

                    array(
                        'col' => 5,
                        'type' => 'text',
                        'prefix' => '<i class="icon-server"></i>',
                        'desc' => $this->l('Invoice Secure Token to downlaod pdf in app'),
                        'name' => 'ADMINMOBAPP_SHOP_FCM',
                        'label' => $this->l('Invoice Server Key (Download)'),
                    ),
                    array(
                        'type' => 'qrcode',
                        'name' => 'ADMINMOBAPP_CONNECTION_QR',
                        'label' => $this->l('APP QR Code'),
                    ),


                    array(
                        'type' => 'employee_list',
                        'required' => true,
                        'name' => 'module_settings[employee_ids][]',
                        'label' => $this->l('Allow Employees'),
                    ),

                    array(
                        'col' => 5,
                        'type' => 'select',
                        'prefix' => '<i class="icon-home"></i>',
                        'desc' => $this->l('Select the landing page for the mobile app on login'),
                        'name' => 'ADMINMOBAPP_LANDING_PAGE',
                        'label' => $this->l('Mobile App Landing Page'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 'dashboard',
                                    'name' => $this->l('Dashboard')
                                ),
                                array(
                                    'id' => 'menu',
                                    'name' => $this->l('Main Menu')
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),


                    array(
                        'type' => 'switch',
                        'label' => $this->l('Push Order Notification'),
                        'name' => 'ADMINMOBAPP_ORDER_NOTIFICATION',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Push Orders Notifications'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Push Customer Notification'),
                        'name' => 'ADMINMOBAPP_CUSTOMER_NOTIFICATION',
                        'is_bool' => true,
                        'desc' => $this->l('Enable Push Customer Notifications'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show Product price with tax'),
                        'name' => 'ADMINMOBAPP_PRODUCT_PRICE',
                        'is_bool' => true,
                        'desc' => $this->l('Show product prices with tax'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }


    protected function getLogoUrl()
    {
        $logo = Configuration::get('ADMINMOBAPP_LOGO');
        if ($logo) {
            return _PS_BASE_URL_._PS_IMG_.'/'.$logo;
        }
        return '';
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'ADMINMOBAPP_SHOP_NAME' => Configuration::get('ADMINMOBAPP_SHOP_NAME', true),
            'ADMINMOBAPP_SHOP_FCM' => Configuration::get('ADMINMOBAPP_SHOP_FCM', true),
            'ADMINMOBAPP_LANDING_PAGE' => Configuration::get('ADMINMOBAPP_LANDING_PAGE', true),
            
            'ADMINMOBAPP_AUTH_TOKEN' => Configuration::get('ADMINMOBAPP_AUTH_TOKEN', true),
            'ADMINMOBAPP_CONNECTION_KEY' => Configuration::get('ADMINMOBAPP_CONNECTION_KEY', true),
            'ADMINMOBAPP_BASE_URL' => Configuration::get('ADMINMOBAPP_BASE_URL', true),
            'ADMINMOBAPP_ORDER_NOTIFICATION' => Configuration::get('ADMINMOBAPP_ORDER_NOTIFICATION', true),
            'ADMINMOBAPP_CUSTOMER_NOTIFICATION' => Configuration::get('ADMINMOBAPP_CUSTOMER_NOTIFICATION', true),
            'ADMINMOBAPP_EMPLOYEES' => Configuration::get('ADMINMOBAPP_EMPLOYEES', true),
            'ADMINMOBAPP_PRODUCT_PRICE' => Configuration::get('ADMINMOBAPP_PRODUCT_PRICE', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        
        $shopName = Tools::getValue('ADMINMOBAPP_SHOP_NAME');
        $shopFcm = Tools::getValue('ADMINMOBAPP_SHOP_FCM');
        $landing_page = Tools::getValue('ADMINMOBAPP_LANDING_PAGE');
        
        $baseUrl = Tools::getValue('ADMINMOBAPP_BASE_URL');
        $connectionKey = Tools::getValue('ADMINMOBAPP_CONNECTION_KEY');
        $authToken = Tools::getValue('ADMINMOBAPP_AUTH_TOKEN');

        if (!Validate::isCleanHtml($shopName) || !Validate::isGenericName($shopName)) {
            $shopName = '';
        }

        if (!Validate::isCleanHtml($shopFcm) || !Validate::isGenericName($shopFcm)) {
            $shopFcm = '';
        }

        if (!Validate::isUrl($baseUrl)) {
            $baseUrl = '';
        }

        if (!Validate::isString($connectionKey)) {
            $connectionKey = '';
        }

        if (!Validate::isString($authToken)) {
            $authToken = '';
        }

        $shopName = pSQL($shopName);
        $shopFcm = pSQL($shopFcm);
        $landing_page = pSQL($landing_page);
        $baseUrl = pSQL($baseUrl);
        $connectionKey = pSQL($connectionKey);
        $authToken = pSQL($authToken);

        // Validate and upload logo file
        $this->processLogoUpload();

        // Validate form fields
        $errors = $this->validateFormFields($shopName, $baseUrl, $connectionKey, $authToken);

        // Handle errors or save form data
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->context->controller->errors[] = $error;
            }
        } else {
            foreach (array_keys($form_values) as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }

            $moduleSettings = Tools::getValue('module_settings');
            if (is_array($moduleSettings) && isset($moduleSettings['employee_ids'])) {
                $employeeIds = Tools::getValue('module_settings')['employee_ids'];
                if (!empty($employeeIds) && is_array($employeeIds)) {
                    $employeeIds = array_filter($employeeIds);

                    if (!empty($employeeIds)) {
                        $selectedEmployeeIds = implode(',', $employeeIds);
                        Configuration::updateValue('ADMINMOBAPP_EMPLOYEES', $selectedEmployeeIds);
                    }
                }
            }
            

            $this->context->controller->confirmations[] = $this->l('Settings saved successfully.');
        }
    }

    /**
     * Process the logo upload
     */
    protected function processLogoUpload()
    {
        if (isset($_FILES['ADMINMOBAPP_LOGO']) && isset($_FILES['ADMINMOBAPP_LOGO']['tmp_name']) && !empty($_FILES['ADMINMOBAPP_LOGO']['tmp_name'])) {
            $file = $_FILES['ADMINMOBAPP_LOGO'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $allowed_exts = array('jpg', 'jpeg', 'png', 'gif');

            if (in_array($ext, $allowed_exts)) {
                $upload_dir = _PS_IMG_DIR_ . 'adminmobapp/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $target_file = $upload_dir . 'logo.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $target_file)) {
                    Configuration::updateValue('ADMINMOBAPP_LOGO', 'adminmobapp/logo.' . $ext);
                    $this->context->controller->confirmations[] = $this->l('Logo uploaded successfully.');
                } else {
                    $this->context->controller->errors[] = $this->l('Failed to upload logo.');
                }
            } else {
                $this->context->controller->errors[] = $this->l('Invalid file type. Allowed types: jpg, jpeg, png, gif.');
            }
        }
    }

    /**
     * Validate form fields
     */
    protected function validateFormFields($shopName, $baseUrl, $connectionKey, $authToken)
    {
        $errors = [];

        // Check if required fields are empty
        if (empty($shopName)) {
            $errors[] = $this->l('Shop Name is required.');
        }
        if (empty($baseUrl)) {
            $errors[] = $this->l('Base URL is required.');
        }
        if (empty($connectionKey)) {
            $errors[] = $this->l('Connection Key is required.');
        }
        if (empty($authToken)) {
            $errors[] = $this->l('API Auth Token is required.');
        }

        if (!empty($shopName) && !ctype_alnum(str_replace(' ', '', $shopName))) {
            $errors[] = $this->l('Shop Name should contain only alphanumeric characters and spaces.');
        }

        if (!empty($connectionKey) && !ctype_alnum($connectionKey)) {
            $errors[] = $this->l('Connection Key should contain only alphanumeric characters.');
        }
        if (!empty($authToken) && !ctype_alnum($authToken)) {
            $errors[] = $this->l('API Auth Token should contain only alphanumeric characters.');
        }

        return $errors;
    }


    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-'.$this->name.'-datasync' => array(
                'controller' => 'datasync',
                'rule' => 'datasync',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            )
        );
    }

    public function hookActionValidateOrder($params)
    {
        $count=0;
        $count = Configuration::get('ADMINMOBAPP_ORDER_NOTIFY');
        $count = $count+1;
        Configuration::updateValue('ADMINMOBAPP_ORDER_NOTIFY', pSQL($count));
        $email = $params['customer']->email;
        $allow = Configuration::get('ADMINMOBAPP_ORDER_NOTIFICATION');
        $reference = $params['order']->reference;
        $id_order = $params['order']->id;
        $order = new Order($id_order);
        if (Validate::isLoadedObject($order)) {
            $orderTotal = $order->total_paid;
        } else {
            $orderTotal = 0;
        }

        $title = $this->l('New Order Received', 'adminmobapp');
        $content = $this->l('A new order has been placed. Order Reference: ' . $reference . '. Total: ' . Tools::displayPrice($orderTotal) . '.', 'adminmobapp');

        $url = 'https://fcm.googleapis.com/fcm/send';

        $message = array();
        $message['type'] = 'order';
        $message['title'] = $title;
        $message['id_page'] = 1;
        $message['content_available'] = true;
        $message['description'] = $content;
        $message['body'] = $content;
        $registrationIds = array();

        $token = Configuration::get('ADMINMOBAPP_FCM_TOKEN');
        $device = Configuration::get('ADMINMOBAPP_FCM_DEVICE');
        $os = Configuration::get('ADMINMOBAPP_FCM_OS');

        array_push($registrationIds, $token);
        
        $param = array(
            'data' => $message,
            'notification' => $message,
            'registration_ids'  => $registrationIds,
        );
        if ($allow) {
            self::callNotify($url, $params, $title, $content);
        }
        return true;
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $count=0;
        $count = Configuration::get('ADMINMOBAPP_CUSTOMER_NOTIFY');
        $count = $count+1;
        Configuration::updateValue('ADMINMOBAPP_CUSTOMER_NOTIFY', pSQL($count));
        
        $allow = Configuration::get('ADMINMOBAPP_CUSTOMER_NOTIFICATION');

        $title = $this->l('New Customer Registered', 'adminmobapp');
        $content = $this->l('A new customer has registered on your store.', 'adminmobapp');

        $url = 'https://fcm.googleapis.com/fcm/send';

        $message = array();
        $message['type'] = 'new_customer';
        $message['title'] = $title;
        $message['id_page'] = 1;
        $message['content_available'] = true;
        $message['description'] = $content;
        $message['body'] = $content;
        $registrationIds = array();

        $token = Configuration::get('ADMINMOBAPP_FCM_TOKEN');
        $device = Configuration::get('ADMINMOBAPP_FCM_DEVICE');
        $os = Configuration::get('ADMINMOBAPP_FCM_OS');

        array_push($registrationIds, $token);
        
        $param = array(
            'data' => $message,
            'notification' => $message,
            'registration_ids'  => $registrationIds,
        );
        if ($allow) {
            self::callNotify($url, $param, $title, $content);
        }
        return true;
    }

    public static function createJWT($serviceAccountFile) {
        $key = json_decode(file_get_contents($serviceAccountFile), true);
        $now = time();
        $exp = $now + 3600; // Token valid for 1 hour
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];
        $payload = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
        ];

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = '';
        openssl_sign($base64UrlHeader . '.' . $base64UrlPayload, $signature, $key['private_key'], 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
        return $jwt;
    }

    public static function callNotify($url, $params, $title, $content)
    {
        $token = Configuration::get('ADMINMOBAPP_FCM_TOKEN');
        $device = Configuration::get('ADMINMOBAPP_FCM_DEVICE');
        $os = Configuration::get('ADMINMOBAPP_FCM_OS');
        $url = 'https://fcm.googleapis.com/v1/projects/prestaadminapp/messages:send';
        $message = array(
            'token' => $token,
            'notification' => array(
                'title' => $title,
                'body' => $content,
            ),
            'data' => array(
                'type' => 'new_customer',
                'title' => $title,
                'id_page' => '1',
                'content_available' => "true",
                'description' => $content,
                'body' => $content,
            ),
        );

        $params = array(
            'message' => $message,
        );
        $result = Adminmobapp::curlRequest($url, $params, $title, $content);
        
        return $result;
    }

    public static function curlRequest($url, $params, $title, $content)
    {
        $serviceAccountFile = dirname(_PS_MODULE_DIR_).'/modules/adminmobapp/services/prestaadminapp-firebase-adminsdk-tdwsa-93d23af145.json';
        $accessToken = Adminmobapp::getAccessToken($serviceAccountFile);

        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        );
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_POST, true);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($connection, CURLOPT_POSTFIELDS, json_encode($params));

        $result = curl_exec($connection);
        if ($result === false) {
            die('Curl failed: ' . curl_error($connection));
        }
        curl_close($connection);
        return $result;
    }

    public static function getAccessToken($serviceAccountFile) {
        $jwt = Self::createJWT($serviceAccountFile);

        $params = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Request Error: ' . curl_error($ch));
        }
        curl_close($ch);

        $responseDecoded = json_decode($response, true);
        if (isset($responseDecoded['access_token'])) {
            return $responseDecoded['access_token'];
        } else {
            return true;
            //throw new Exception('Error fetching access token: ' . $response);
        }
    }

    public static function encodeJson($params)
    {
        if (method_exists('Tools', 'jsonEncode')) {
            return Tools::jsonEncode($params);
        } else {
            return json_encode($params);
        }
    }
}
