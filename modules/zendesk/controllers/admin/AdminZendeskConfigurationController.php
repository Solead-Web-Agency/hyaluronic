<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Presta-Module
 *  @author    202 ecommerce
 *  @copyright 2009-2016 Presta-Module
 *  @copyright since 2017 202 ecommerce
 *  @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'zendesk/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'zendesk/models/ZendeskResellerApi.php';

use ZendeskClasslib\Utils\Translate\TranslateTrait;

class AdminZendeskConfigurationController extends ModuleAdminController
{
    use TranslateTrait;

    /** @var Zendesk */
    public $module;

    /** @var string Associated object class name */
    public $className = 'Configuration';

    /** @var string Associated table name */
    public $table = 'configuration';

    /** @var bool Is bootstrap enabled */
    public $bootstrap = false;

    /** @var bool */
    private $onboarding = false;

    /** @var ZendeskResellerApi */
    private $reseller_api;

    /** @var bool */
    private $isMultiShop = false;

    /** @var bool */
    private $isContextAllShop = false;

    /** @var mixed */
    private $allShops;

    /** @var mixed */
    private $confAllShops;

    /** @var int */
    private $idFirstShop;

    /** @var int */
    private $currentIdShop;

    public function __construct()
    {
        parent::__construct();

        $this->reseller_api = new ZendeskResellerApi();
        $this->isMultiShop = Shop::isFeatureActive();
        $this->isContextAllShop = Shop::getContext() == Shop::CONTEXT_ALL;
        $this->allShops = Shop::getShops();
        asort($this->allShops);
        $this->idFirstShop = empty($this->allShops) ? 1 : (int) array_values($this->allShops)[0]['id_shop'];
        $this->currentIdShop = $this->isContextAllShop === true ? $this->idFirstShop : $this->context->shop->id;
    }

    /**
     * @see AdminController::initPageHeaderToolbar()
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        // Remove the help icon of the toolbar which no useful for us
        $this->context->smarty->clearAssign('help_link');
    }

    public function initContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitzendeskModule')) == true) {
            $this->postProcess();
        }

        $tpl_vars = [
            'moduleVersion' => $this->module->version,
        ];

        $this->confAllShops = [
            Zendesk::USERNAME => $this->getConfiguration(Zendesk::USERNAME, true),
            Zendesk::APIKEY => $this->getConfiguration(Zendesk::APIKEY, true),
            Zendesk::SUBDOMAIN => $this->getConfiguration(Zendesk::SUBDOMAIN, true),
            Zendesk::CONNECTOR_KEY => Configuration::getGlobalValue(Zendesk::CONNECTOR_KEY),
            Zendesk::ONBOARDING => $this->getConfiguration(Zendesk::ONBOARDING, true),
        ];

        $accountMode = $this->module->api->checkIfSeveralDomains($this->confAllShops);
        if ($this->isMultiShop === true && $this->isContextAllShop === true) {
            $tpl_vars['isThereMesssages'] = count($this->context->controller->errors) > 0 || count($this->context->controller->confirmations) > 0;
            $tplFile = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/oneaccount_selectshop.tpl';
            $tpl = $this->context->smarty->createTemplate($tplFile);
            $tpl->assign($tpl_vars);
            $this->content .= $tpl->fetch();

            return parent::initContent();
        }

        $tpl_vars['psMboDepenciesOk'] = true;
        $tpl_vars['isPs17orMore'] = true;

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            // Load dependencies manager
            $mboInstaller = new Prestashop\ModuleLibMboInstaller\DependencyBuilder($this->module);

            if (!$mboInstaller->areDependenciesMet()) {
                $dependencies = $mboInstaller->handleDependencies();
                $tpl_vars['dependencies'] = $dependencies;
                $tpl_vars['psMboDepenciesOk'] = false;
            }
        } else {
            $this->page_header_toolbar_btn['zendesk_config'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskConfiguration'),
                'desc' => $this->module->l('Configuration'),
                'icon' => 'process-icon-cogs',
            ];
            $this->page_header_toolbar_btn['zendesk_logs'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskProcessLogger'),
                'desc' => $this->module->l('Logs'),
                'icon' => 'process-icon-terminal',
            ];
            $this->page_header_toolbar_btn['zendesk_invoices'] = [
                'href' => $this->context->link->getAdminLink('AdminZendeskPrestaShopInvoices'),
                'desc' => 'PrestaShop ' . $this->module->l('Invoices'),
                'icon' => 'process-icon-envelope',
            ];
            $tpl_vars['isPs17orMore'] = false;
        }
        if (Module::isInstalled('ps_accounts') === false || Module::isEnabled('ps_accounts') === false) {
            $tpl_vars['psMboDepenciesOk'] = false;
        }

        $this->defineIfIsOnBoarding();

        $this->content = '';

        $confZenDeskModule = $accountMode !== 0 ? true : false;

        $tpl_vars['isModuleConfigured'] = $confZenDeskModule;
        $tpl_vars['psAccountInstalled'] = false;
        $tpl_vars['urlAccountsCdn'] = '';
        $tpl_vars['urlBilling'] = '';
        $tpl_vars['subscriptionMessage'] = [];
        $tpl_vars['subscriptionDoneWithOtherShop'] = false;
        $tpl_vars['subscriptionShop'] = 0;

        if ($confZenDeskModule === true) {
            /*********************
            * PrestaShop Account *
            * *******************/
            $accountsService = $this->module->getService('zendesk.ps_account_service');
            $psAccountService = $accountsService->getPsAccount();
            $psAccountInstalled = $psAccountService !== null;
            /**********************
             * PrestaShop Billing *
             * *******************/
            if ($psAccountInstalled) {
                $subscription = $accountsService->getBillings();
                $subscriptionStatus = isset($subscription['status']) ? isset($subscription['status']) : 'none';
                if ($subscriptionStatus == 'active' || $subscriptionStatus == 'in_trial') {
                    $tpl_vars['subscriptionDone'] = true;
                } elseif ($accountMode === 1 && $this->isMultiShop) {
                    $subscription = $accountsService->getActiveSubscription($this->allShops);
                    if ($subscription !== false && isset($subscription['id_shop']) && (int) $subscription['id_shop'] !== $this->context->shop->id) {
                        $tpl_vars['subscriptionDoneWithOtherShop'] = true;
                        $tpl_vars['subscriptionShop'] = $subscription['id_shop'];
                    }
                }
            }

            $tpl_vars['psAccountInstalled'] = $psAccountInstalled;

            // Load the context for PrestaShop Billing
            if ($psAccountInstalled) {
                // Retrieve the PrestaShop Account CDN
                $tpl_vars['urlAccountsCdn'] = $psAccountService->getAccountsCdn();

                $billingFacade = $this->module->getService('zendesk.ps_billings_facade');
                $partnerLogo = $this->module->getLocalPath() . 'logo.png';

                // PrestaShop Billing
                Media::addJsDef($billingFacade->present([
                    'logo' => $partnerLogo,
                    'tosLink' => 'https://www.202-ecommerce.com/mentions-legales/',
                    'privacyLink' => 'https://www.202-ecommerce.com/mentions-legales/',
                    // This field is deprecated but a valid email must be provided to ensure backward compatibility
                    'emailSupport' => 'contact@202-ecommerce.com',
                ])); // Retrieve plans and addons for your module
            }
            $tpl_vars['urlBilling'] = 'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js';

            // Send for each shop if configured the link
            $apiKeys = [];
            foreach ($this->allShops as $oneShop) {
                $idShop = (int) $oneShop['id_shop'];
                $shop = new Shop($idShop);
                $apiKey = Configuration::get(Zendesk::APIKEY, null, null, $idShop);
                if (in_array($apiKey, $apiKeys) === false) {
                    $messageSubscription = $this->shopLink(
                        $psAccountService === null ? null : $psAccountService->getShopUuid(),
                        [
                            Zendesk::USERNAME => Configuration::get(Zendesk::USERNAME, null, null, $idShop),
                            Zendesk::APIKEY => $apiKey,
                            Zendesk::SUBDOMAIN => Configuration::get(Zendesk::SUBDOMAIN, null, null, $idShop),
                            Zendesk::CONNECTOR_KEY => Configuration::getGlobalValue(Zendesk::CONNECTOR_KEY),
                        ],
                        $shop
                    );
                    if ($messageSubscription !== '') {
                        $apiKeys[] = $apiKey;
                        $response = json_decode($messageSubscription, true);
                        $message = isset($response['data']['message']) ? $response['data']['message'] : '';
                        if (
                            $this->isMultiShop === false
                            || ($accountMode === 2 && (int) $this->currentIdShop === (int) $idShop)
                            || $accountMode === 1
                        ) {
                            $tpl_vars['subscriptionMessage'][] = $message;
                        }
                        if (isset($response['errors'])) {
                            foreach ($response['errors'] as $oneError) {
                                $errorMessage = sprintf(
                                    $this->l('Error in subscription shop %s (N° %d):'),
                                    $shop->domain,
                                    $idShop
                                );
                                $errorMessage .= isset($oneError['origin']) ? ' ' . $oneError['origin'] . ' ' : ' ';
                                $errorMessage .= isset($oneError['message']) ? $oneError['message'] : '';
                                $this->context->controller->errors[] = $errorMessage;
                            }
                        }
                    }
                }
            }
        }

        $contentConfiguration = $this->renderConfiguration();

        $tpl_vars['messages'] = [
            'danger' => $this->context->controller->errors,
            'success' => $this->context->controller->confirmations,
        ];
        $this->context->controller->errors = [];
        $this->context->controller->confirmations = [];

        $tplFile = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/psaccount.tpl';
        $tpl = $this->context->smarty->createTemplate($tplFile);
        $tpl->assign($tpl_vars);
        $this->content .= $tpl->fetch();

        $this->content .= $contentConfiguration;

        parent::initContent();
    }

    /**
     * Render View
     *
     * @see AdminController::renderView()
     */
    public function renderConfiguration()
    {
        if (Tools::getValue('action', 0)) {
            $this->handleAjaxRequest();
        }

        $account = $this->module->api->getMe();

        if (is_object($account) && isset($account->error)) {
            $error = $account->error;
            $error_msg = isset($account->description)
            ? $account->description
            : (
                (
                    isset($error->title)
                    ? $error->title
                    : 'Unexpected error'
                )
                . '. Error message: ' . (isset($error->message) ? $error->message : '-')
            );
            $this->context->controller->errors[] = $this->module->l('Error occured: ' . $error_msg);
        }

        return $this->renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitSubDomainExist')) {
            Configuration::updateValue(Zendesk::SUBDOMAIN, Tools::getValue('subdomain'));
            Configuration::updateValue(Zendesk::ONBOARDING, (int) false);
        } elseif (Tools::isSubmit('submitConfig')) {
            $widget = Tools::getValue('embed-toggle');
            $app = Tools::getValue('settings-toggle');
            $preload_widget = Tools::getValue('preload_widget_toggle');
            $preload_widget_controllers = json_encode(Tools::getValue('preload_widget_controllers'));

            Configuration::updateValue(Zendesk::WIDGET, (int) $widget);
            Configuration::updateValue(Zendesk::APP, (int) $app);
            Configuration::updateValue(Zendesk::SUBDOMAIN, Tools::getValue('subdomain'));
            Configuration::updateValue(Zendesk::USERNAME, Tools::getValue('email'));
            Configuration::updateValue(Zendesk::APIKEY, Tools::getValue('api_key'));
            Configuration::updateValue(Zendesk::ID_BRAND, Tools::getValue('brand_id'));
            Configuration::updateValue(Zendesk::PRELOAD_WIDGET, (int) $preload_widget);
            Configuration::updateValue(Zendesk::PRELOAD_WIDGET_CONTROLLERS, $preload_widget_controllers);

            if ((int) $app) {
                $this->installApp();
            }

            $account = $this->module->api->getMe();

            if (is_object($account) && isset($account->error)) {
                $error = $account->error;
                $this->context->controller->errors[] = $this->module->l('Error occured: ' . (isset($error->title) ? $error->title : 'Unexpected error') . '. Error message: ' . (isset($error->message) ? $error->message : '-'));
            }

            if (!$this->module->api->isValid()) {
                $this->context->controller->errors[] = $this->module->l('Account is not valid');
            }
        }
    }

    /**
     * Handle ajax requests (like verify subdomain owner, subodmain availability, ...)
     *
     * @since 1.0.0
     */
    public function handleAjaxRequest()
    {
        $json = [];
        $action = Tools::getValue('action', 'undefined');

        switch ($action) {
            case 'verifySubdomainAvailability':
                $subdomain = Tools::getValue('subdomain');

                $return = $this->reseller_api->verifySubdomainAvailability($subdomain);
                if (isset($return->success) && $return->success) {
                    $json['success'] = true;
                    $json['msg'] = $this->module->l('Subdomain available');
                } else {
                    $json['success'] = false;
                    $json['msg'] = $this->module->l('Subdomain not available');
                }

                break;
            case 'verifySubdomainOwner':
                Configuration::updateValue(Zendesk::SUBDOMAIN, Tools::getValue('subdomain'));
                Configuration::updateValue(Zendesk::USERNAME, Tools::getValue('username'));
                Configuration::updateValue(Zendesk::APIKEY, Tools::getValue('api_key'));

                $json['success'] = $this->module->api->verifySubdomainOwner();
                if ($json['success']) {
                    $json['msg'] = $this->module->l('Success');
                } else {
                    if (Tools::getValue('screen_is') == 'onboarding') {
                        Configuration::updateValue(Zendesk::ONBOARDING, (int) true);
                    }
                    $json['msg'] = $this->module->l('This account isn\'t yours. Thanks to choose an another one.');
                }
                break;
            case 'createTrialAccount':
                $owner = [
                    'name' => Tools::getValue('owner_name'),
                    'email' => Tools::getValue('owner_email'),
                ];
                $address = [
                    'phone' => Tools::getValue('address_phone'),
                ];
                $account = [
                    'name' => Tools::getValue('company_name'),
                    'subdomain' => Tools::getValue('subdomain'),
                    'help_desk_size' => Tools::getValue('help_desk_size'),
                ];
                $language = Tools::getValue('language');

                $return = $this->reseller_api->createTrialAccount($owner, $address, $account, $language);
                if (!isset($return->error)) {
                    $json['success'] = true;
                    $json['msg'] = $this->module->l('Success');
                    Configuration::updateValue(Zendesk::ONBOARDING, (int) false);
                    Configuration::updateValue(Zendesk::SUBDOMAIN, Tools::getValue('subdomain'));
                    Configuration::updateValue(Zendesk::USERNAME, Tools::getValue('owner_email'));
                } else {
                    $json['success'] = false;
                    $json['msg'] = $this->reseller_api->getLastError(true);
                }
                break;
            case 'undefined':
            default:
                $json['success'] = false;
                $json['msg'] = '';
                break;
        }

        exit(json_encode($json));
    }

    public function renderForm()
    {
        if ($this->onboarding) {
            $content = $this->renderOnBoardingForm();
        } else {
            $content = $this->renderConfigForm();
            $zendeskCoreApi = new ZendeskCoreApi();
            if ($zendeskCoreApi->updateAppHTTPS() === true) {
                $this->context->controller->confirmations[] = $this->module->l(
                    'The ZenDesk App URL has been updated to HTTPS automatically'
                );
            }
        }

        return $content;
    }

    public function renderOnBoardingForm()
    {
        $shop_name = Configuration::get('PS_SHOP_NAME');

        $setting_url = $this->context->link->getAdminLink('AdminZendeskConfiguration');

        $tplVars = [
            'dev_part' => Tools::getValue('part', 'onboarding'),
            'shop_name' => $shop_name,
            'domain_suggestion' => Tools::str2url($shop_name),
            'owner_email' => Configuration::get('PS_SHOP_EMAIL'),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
            'free_trial_url' => 'https://www.zendesk.com/register#getstarted',
            'setting_url' => $setting_url,
        ];

        $tplFile = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/onboarding.tpl';

        $tpl = Context::getContext()->smarty->createTemplate($tplFile);
        $tpl->assign($tplVars);

        return $tpl->fetch();
    }

    public function renderConfigForm()
    {
        $setting_url = $this->context->link->getAdminLink('AdminZendeskConfiguration');

        $widgetControllersConfig = json_decode(
            Configuration::get(
                Zendesk::PRELOAD_WIDGET_CONTROLLERS,
                null,
                null,
                null,
                '["index","product","category","cart","order"]'
            ),
            true
        );

        $tplVars = [
            'zendesk_subdomain' => $this->getConfiguration(Zendesk::SUBDOMAIN),
            'zendesk_api_key' => $this->getConfiguration(Zendesk::APIKEY),
            'zendesk_email' => $this->getConfiguration(Zendesk::USERNAME),
            'zendesk_widget' => (int) $this->getConfiguration(Zendesk::WIDGET),
            'zendesk_app' => (int) $this->getConfiguration(Zendesk::APP),
            'zendesk_order_id_field' => $this->getConfiguration(Zendesk::ORDER_ID_FIELD_ID),
            'zendesk_access_token' => $this->getConfiguration(Zendesk::CONNECTOR_KEY),
            'zendesk_preload_widget' => $this->getConfiguration(Zendesk::PRELOAD_WIDGET),
            'setting_url' => $setting_url,
            'zendesk_preload_widget_controllers' => [
                'index' => [
                    'name' => $this->module->l('Home page'),
                    'value' => $this->getValueWidget($widgetControllersConfig, 'index'),
                ],
                'product' => [
                    'name' => $this->module->l('Product page'),
                    'value' => $this->getValueWidget($widgetControllersConfig, 'product'),
                ],
                'category' => [
                    'name' => $this->module->l('Category page'),
                    'value' => $this->getValueWidget($widgetControllersConfig, 'category'),
                ],
                'cart' => [
                    'name' => $this->module->l('Cart page'),
                    'value' => $this->getValueWidget($widgetControllersConfig, 'cart'),
                ],
                'order' => [
                    'name' => $this->module->l('Order page'),
                    'value' => $this->getValueWidget($widgetControllersConfig, 'order'),
                ],
            ],
            'zendesk_brands' => $this->getBrands(),
        ];

        $tplFile = _PS_MODULE_DIR_ . $this->module->name . '/views/templates/admin/settings.tpl';

        $tpl = Context::getContext()->smarty->createTemplate($tplFile);
        $tpl->assign($tplVars);

        return $tpl->fetch();
    }

    /**
     * @param mixed $config Configuration saved
     *
     * @return bool If widget is checked or not
     */
    private function getValueWidget($config, $element)
    {
        return isset($config[$element]) ? true : false;
    }

    /**
     *  Install the Zendesk App into the Zendesk Manager
     *
     * @since 1.0.0
     */
    private function installApp()
    {
        $need = [
            'to_install' => true,
            'to_update' => false,
        ];

        $shopBaseURL = Context::getContext()->shop->getBaseURL();
        $shopBaseURLHTTPS = str_replace('http://', 'https://', $shopBaseURL);

        $apps = $this->module->api->listAppInstallations();
        if (is_object($apps) && is_array($apps->installations)) {
            foreach ($apps->installations as $installation) {
                if ($installation->app_id == Zendesk::ZENDESK_APP_ID) {
                    $settings = [];
                    if (empty($installation->settings->url) || $installation->settings->url != $shopBaseURLHTTPS) {
                        $need['to_update'] = true;
                        $settings['url'] = $shopBaseURLHTTPS;
                    }
                    if (empty($installation->settings->access_token) || $installation->settings->access_token != Configuration::getGlobalValue(Zendesk::CONNECTOR_KEY)) {
                        $need['to_update'] = true;
                    }
                    if (empty($installation->settings->order_id_field_id)) {
                        $need['to_update'] = true;
                        $ticket_field = [
                            'type' => 'text',
                            'title' => $this->module->l('Order reference'),
                            'removable' => false,
                        ];

                        $ret = $this->module->api->createTicketField($ticket_field);
                        if (isset($ret->ticket_field->id)) {
                            $settings['order_id_field_id'] = $ret->ticket_field->id;
                        }
                    } else {
                        Configuration::updateValue(Zendesk::ORDER_ID_FIELD_ID, $installation->settings->order_id_field_id);
                    }

                    $need['to_install'] = false;

                    // Update settings
                    if ($need['to_update']) {
                        // Don't cast $installation->id as an Int, it may overflow on 32 bits systems and thus fails with Validate::isUnsignedInt... Keep it as a string
                        $ret = $this->module->api->updateApp($installation->id, $settings);
                        if (!empty($ret) && !isset($ret->error)) {
                            // Update our own if success and if needed
                            if (isset($settings['order_id_field_id'])) {
                                Configuration::updateValue(Zendesk::ORDER_ID_FIELD_ID, $settings['order_id_field_id']);
                            }
                            $this->context->controller->confirmations[] = $this->module->l('App update successful');
                        } else {
                            $this->context->controller->errors[] = $this->module->l('Error during update app');
                        }
                    }

                    continue;
                }
            }
        }

        if ($need['to_install']) {
            // Process install
            $ticket_field = [
                'type' => 'text',
                'title' => $this->module->l('Order reference'),
                'removable' => false,
            ];

            $ret = $this->module->api->createTicketField($ticket_field);
            if (isset($ret->ticket_field->id)) {
                $install = $this->module->api->installApp(Zendesk::ZENDESK_APP_ID, $ret->ticket_field->id);
                if (isset($install->id)) {
                    Configuration::updateValue(Zendesk::ORDER_ID_FIELD_ID, $ret->ticket_field->id);
                    $this->context->controller->confirmations[] = $this->module->l('App installed successful');
                } else {
                    $this->context->controller->errors[] = $this->module->l('Error during install app : ' . (!empty($install->description) ? $install->description : $install->error));
                }
            } else {
                $error = 'unknow error';
                if (is_array($ret)) {
                    $error = $ret['error'];
                } elseif (empty($ret->description)) {
                    if (is_string($ret->error)) {
                        $error = $ret->error;
                    } elseif ($ret->error instanceof stdClass) {
                        if (property_exists($ret->error, 'title')) {
                            $error = $ret->error->title;
                        } elseif (property_exists($ret->error, 'message')) {
                            $error = $ret->error->title;
                        }
                    }
                } else {
                    $error = $ret->description;
                }
                $this->context->controller->errors[] = $this->module->l('Can\'t process the creation of the user field. ' . $error);
            }
        }

        return true;
    }

    /**
     * Check if settings are done or if we are on onBoarding
     *
     * @since 1.0.0
     */
    private function defineIfIsOnBoarding()
    {
        $idShop = $this->isContextAllShop === true ? $this->idFirstShop : $this->context->shop->id;
        $conf = [
            Zendesk::ONBOARDING => $this->confAllShops[Zendesk::ONBOARDING][$idShop],
            Zendesk::SUBDOMAIN => $this->confAllShops[Zendesk::SUBDOMAIN][$idShop],
        ];
        $this->onboarding = (bool) $conf[Zendesk::ONBOARDING] || ($conf[Zendesk::SUBDOMAIN] == '') ? true : false;
    }

    private function shopLink($shopUuid, $conf, $shop)
    {
        $baseURI = str_replace('http://', '', $shop->getBaseURL(false));
        if ($conf[Zendesk::SUBDOMAIN] === false || $conf[Zendesk::CONNECTOR_KEY] === false) {
            return '';
        }
        if ($conf[Zendesk::USERNAME] === false || $conf[Zendesk::APIKEY] === false) {
            return '';
        }
        $availableLanguageIsoCode = ['en', 'fr'];
        $language = (new Language($this->context->employee->id_lang));
        if (Validate::isLoadedObject($language) && in_array($language->iso_code, $availableLanguageIsoCode)) {
            $languageIso = $language->iso_code;
        } else {
            $languageIso = $availableLanguageIsoCode[0];
        }
        $zendeskCoreApi = new ZendeskCoreApi();

        return $zendeskCoreApi->shopLink(
            rtrim($baseURI, '/'),
            $conf[Zendesk::USERNAME],
            $conf[Zendesk::APIKEY],
            $languageIso,
            $shopUuid,
            $this->module->version,
            $conf[Zendesk::SUBDOMAIN],
            $conf[Zendesk::CONNECTOR_KEY]
        );
    }

    private function getBrands()
    {
        $params = [
            'item' => 'brands',
            'action' => 'GET',
            'data' => [],
        ];

        $brands = $this->module->api->sendRequest($params);
        $brands = isset($brands->brands) ? $brands->brands : [];

        $brandSelected = Configuration::get(
            ZenDesk::ID_BRAND,
            null,
            null,
            Context::getContext()->shop->id,
            -1
        );

        $brandsToReturn = [
            [
                'name' => $this->l('Default (send without brand)'),
                'id' => -1,
                'selected' => $brandSelected === -1 ? true : false,
            ],
        ];

        foreach ($brands as $oneBrand) {
            $brandsToReturn[] = [
                'name' => $oneBrand->name,
                'id' => $oneBrand->id,
                'selected' => (int) $brandSelected === (int) $oneBrand->id ? true : false,
            ];
        }

        return $brandsToReturn;
    }

    private function getConfiguration($key, $allShops = false)
    {
        $idShop = $this->isContextAllShop === true ? $this->idFirstShop : $this->context->shop->id;
        if ($allShops === false) {
            return Configuration::get($key, null, null, $idShop);
        }

        $conf = [];
        foreach ($this->allShops as $shop) {
            $idShop = (int) $shop['id_shop'];
            $conf[$idShop] = Configuration::get($key, null, null, $idShop);
        }

        return $conf;
    }
}
