<?php
/**
 * Copyright since 2007 Viva Wallet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@vivawallet.com so we can send you a copy immediately.
 *
 * @author    Viva Wallet <support@vivawallet.com>
 * @copyright Since 2007 Viva Wallet
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

// Namespace import is not used in this file for prestashop 1.6 compatibility

require_once _PS_ROOT_DIR_ . '/modules/vivawalletsmartcheckout/vendor/autoload.php';

class VivawalletSmartCheckout extends PaymentModule
{
    private $container;

    public function __construct()
    {
        $this->name = 'vivawalletsmartcheckout';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.1';
        $this->author = 'Viva.com';
        $this->need_instance = 1;
        $this->module_key = '40216bfc3f77642f87265d86553d9dd0';
        $this->bootstrap = \Vivawalletsmartcheckout\Helpers\Config::get('app.module.attributes.bootstrap');
        $this->ps_versions_compliancy = [
            'min' => '9.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();

        if ($this->container === null) {
            $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }

        $this->displayName = $this->l('Viva.com | Smart Checkout');
        $this->description = $this->l('Official Payment Gateway for Viva.com | Smart Checkout');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        \Vivawallet\VivawalletPhp\Application::setInformation(
            [
                'vivaWallet' => [
                    'version' => $this->version,
                ],
                'cms' => [
                    'version' => _PS_VERSION_,
                    'abbreviation' => \Vivawalletsmartcheckout\Helpers\Config::get('app.prestashop.abbreviation'),
                    'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.prestashop.name'),
                ],
            ]
        );
    }

    // <editor-fold desc="Install">

    public function install(): bool
    {
        $success = $this->checkRequiredPhpVersion()
            && $this->checkRequiredExtensions()
            && parent::install()
            && $this->installOrderStates()
            && $this->installAdminTabs()
            && $this->registerHooks();

        if ($success) {
            if (!($success = \Vivawalletsmartcheckout\Helpers\Database::createTableSchema())) {
                $this->_errors[] = $this->l('Module\' s database tables could not be created.');
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "Database tables of module {$this->name} could not be created",
                    'install'
                );
            } elseif (!($success = $this->toggleModuleStatus(false))) {
                $this->_errors[] = $this->l('Module\' s status could not be updated.');
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "Status of module {$this->name} could not be updated",
                    'install'
                );
            }
        }

        return $success;
    }

    /**
     * Check if php version installed is supported
     *
     * @return bool
     */
    private function checkRequiredPhpVersion(): bool
    {
        $requiredVersion = \Vivawalletsmartcheckout\Helpers\Config::get('app.required.php.version');
        if (Tools::version_compare(Tools::checkPhpVersion(), $requiredVersion)) {
            $this->_errors[] = sprintf($this->l('This module requires at least PHP %s version'), $requiredVersion);
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                "{$this->name} module requires at least PHP $requiredVersion version",
                'install'
            );

            return false;
        }

        return true;
    }

    /**
     * Check if all php required extensions are loaded
     *
     * @return bool
     */
    private function checkRequiredExtensions(): bool
    {
        $unloadedExtensions = array_diff(
            \Vivawalletsmartcheckout\Helpers\Config::get('app.required.php.extensions'),
            get_loaded_extensions()
        );
        if (!empty($unloadedExtensions)) {
            $this->_errors[] = sprintf(
                $this->l('You have to enable the following extensions on your server to install this module: %s'),
                implode(', ', $unloadedExtensions)
            );
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                "Missing extensions for {$this->name} module: " . implode(', ', $unloadedExtensions),
                'install'
            );

            return false;
        }

        return true;
    }

    /**
     * Install module's order states
     *
     * @return bool
     */
    private function installOrderStates(): bool
    {
        $orderStates = \Vivawalletsmartcheckout\Helpers\Config::get('app.order.states');
        foreach ($orderStates as $orderState) {
            try {
                $success = $this->addOrderState($orderState);
            } catch (Exception $exception) {
                $success = false;
            }
            if (!$success) {
                $this->_errors[] = sprintf($this->l('The %s status could not be created!'), $orderState['name']);
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "The {$orderState['name']} status could not be created for module {$this->name}",
                    'install'
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Register all the hooks
     *
     * @return bool
     */
    private function registerHooks(): bool
    {
        $hooks = \Vivawalletsmartcheckout\Helpers\Config::get('app.hooks');
        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                $this->_errors[] = sprintf($this->l('The %s hook could not be created!'), $hook);
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "The $hook hook could not be created for module {$this->name}",
                    'install'
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Add order state
     *
     * @param array $orderStateOptions
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function addOrderState(array $orderStateOptions): bool
    {
        $orderState = new OrderState(Configuration::get($orderStateOptions['field']));
        $orderStateExists = Validate::isLoadedObject($orderState);
        $orderState = $orderStateExists ? $orderState : new OrderState();
        $orderState->name = [];
        foreach (Language::getLanguages() as $language) {
            $orderState->name[$language['id_lang']] = $orderStateOptions['name'];
            if (empty($orderState->name[$language['id_lang']])) {
                return false;
            }
            if (!empty($orderStateOptions['options']['template'])) {
                $orderState->template[$language['id_lang']] = $orderStateOptions['options']['template'];
            }
        }
        $orderState->invoice = $orderStateOptions['options']['invoice'] ?? false;
        $orderState->send_email = $orderStateOptions['options']['sendEmail'] ?? false;
        $orderState->logable = $orderStateOptions['options']['logable'] ?? true;
        $orderState->paid = $orderStateOptions['options']['paid'] ?? true;
        $orderState->color = $orderStateOptions['options']['color'] ?? '#3498D8';
        $orderState->pdf_invoice = $orderStateOptions['options']['pdfInvoice'] ?? false;
        $orderState->module_name = $this->name;
        $orderState->deleted = false;
        $saveOrderSuccess = $orderStateExists ? $orderState->update() : $orderState->add();

        return $saveOrderSuccess && Configuration::updateValue($orderStateOptions['field'], $orderState->id);
    }

    /**
     * Install AdminTabs, necessary for admin controllers
     *
     * @return bool
     */
    public function installAdminTabs(): bool
    {
        $result = true;

        $adminTabs = \Vivawalletsmartcheckout\Helpers\Config::get('app.admin_tabs');
        $alreadyInstalledTabs = Tab::getModuleTabList();

        foreach ($adminTabs as $adminTabData) {
            $tab = new Tab();
            $tab->class_name = $adminTabData['class_name'];
            $tab->module = $this->name;
            $tab->active = $adminTabData['visible'] ?? false;
            $tab->id_parent = isset($adminTabData['parent_class']) ? Tab::getIdFromClassName($adminTabData['parent_class']) : 0;
            foreach (Language::getLanguages(false) as $language) {
                $tab->name[$language['id_lang']] = empty($adminTabData['name'][$language['iso_code']])
                    ? $adminTabData['name']['en']
                    : $adminTabData['name'][$language['iso_code']];
            }

            if (isset($alreadyInstalledTabs[strtolower($adminTabData['class_name'])])
                && $alreadyInstalledTabs[strtolower($adminTabData['class_name'])]['module'] == $this->name
            ) {
                $result = $result && (bool) $tab->update();
            } else {
                $result = $result && (bool) $tab->add();
            }
        }

        return (bool) $result;
    }

    // </editor-fold>

    // <editor-fold desc="Uninstall">

    public function uninstall(): bool
    {
        $onHoldTab = Tab::getIdFromClassName('AdminVivawalletsmartcheckoutOnHold');
        if (isset($onHoldTab) && $onHoldTab) {
            $onHoldTab = new Tab((int) $onHoldTab);
            $onHoldTab->active = false;
            $onHoldTab->update();
        }
        $success = parent::uninstall()
            && $this->uninstallOrderStates()
            && $this->deleteConfigurationItems()
            && \Vivawalletsmartcheckout\Helpers\Database::deleteTablesIfEmpty();

        return $success;
    }

    /**
     * Delete configuration items
     *
     * @return bool
     */
    private function deleteConfigurationItems(): bool
    {
        $configurationItems = array_merge(
            $this->getFormFields(),
            [
                \Vivawalletsmartcheckout\Helpers\Config::get('app.module.enabled'),
                \Vivawalletsmartcheckout\Helpers\Config::get('app.form.merchant_id'),
            ]
        );
        foreach ($configurationItems as $configurationItem) {
            if (!Configuration::deleteByName($configurationItem)) {
                $this->_errors[] = sprintf(
                    $this->l('The %s configuration item could not be deleted!'),
                    $configurationItem
                );
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "$configurationItem configuration item could not be deleted for module {$this->name}",
                    'uninstall'
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall module's order states
     *
     * @return bool
     */
    private function uninstallOrderStates(): bool
    {
        $errorExists = false;
        foreach (\Vivawalletsmartcheckout\Helpers\Config::get('app.order.states') as $orderState) {
            try {
                $success = $this->deleteOrderState($orderState);
            } catch (Exception $exception) {
                $success = false;
            }
            if (!$success) {
                $errorExists = true;
                $this->_errors[] = sprintf($this->l('The %s status could not be deleted!'), $orderState['name']);
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    "{$orderState['name']} status could not be deleted for module {$this->name}",
                    'uninstall'
                );
            }
        }

        return !$errorExists;
    }

    /**
     * Delete order state
     *
     * @param array $orderStateOptions
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function deleteOrderState(array $orderStateOptions): bool
    {
        if (Configuration::hasKey($orderStateOptions['field'])) {
            $orderState = new OrderState(Configuration::get($orderStateOptions['field']));
            if (Validate::isLoadedObject($orderState)) {
                $orderState->deleted = true;

                return $orderState->update();
            }
        }

        return true;
    }

    // </editor-fold>

    // <editor-fold desc="Configuration Page">

    /**
     * Retrieve service
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    /**
     * This method handles the module's configuration page
     *
     * @return string The page's HTML content
     *
     * @throws SmartyException
     */
    public function getContent(): string
    {
        $this->loadConfigurationAssets();

        // Load dependencies manager
        $mboInstaller = new \Prestashop\ModuleLibMboInstaller\DependencyBuilder($this);

        if (!$mboInstaller->areDependenciesMet()) {
            $dependencies = $mboInstaller->handleDependencies();
            $this->smarty->assign('dependencies', $dependencies);

            return $this->display(__FILE__, 'views/templates/admin/dependency_builder.tpl');
        }

        $moduleManager = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance()->build();
        $accountsService = null;

        try {
            $accountsFacade = $this->getService('vivawalletsmartcheckout.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $accountsInstaller = $this->getService('vivawalletsmartcheckout.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->getService('vivawalletsmartcheckout.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->name),
            ]);

            // Retrieve Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();
            return '';
        }

        if ($moduleManager->isInstalled('ps_eventbus')) {
            $eventbusModule = \Module::getInstanceByName('ps_eventbus');
            if (version_compare($eventbusModule->version, '1.9.0', '>=')) {
                $eventbusPresenterService = $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');
                $this->context->smarty->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');

                Media::addJsDef([
                    'contextPsEventbus' => $eventbusPresenterService->expose(
                        $this,
                        [
                            'info',
                            'modules',
                            'themes',
                        ]
                    ),
                ]);
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign(
            [
                'module_dir' => $this->_path,
                'display_name' => $this->displayName,
                'developer_portal_url' => \Vivawalletsmartcheckout\Helpers\Config::get('app.url.developer_portal'),
                'merchant_account_url' => \Vivawalletsmartcheckout\Helpers\Config::get('app.url.merchant_account'),
                'register_account_url' => \Vivawalletsmartcheckout\Helpers\Config::get('app.url.register_account'),
            ]
        );
        $header = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        $dynamicFields = $messages = [];
        $formSubmitted = Tools::isSubmit('submit' . $this->name);
        $contextType = \Vivawalletsmartcheckout\Helpers\General::getContextType(Shop::getContext());
        if (!\Vivawalletsmartcheckout\Helpers\Config::get('app.module.ssl_protected')
            || (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED'))
        ) {
            if ($formSubmitted) {
                $processFormSubmissionResponse = $this->processFormSubmission();
                $messages = $processFormSubmissionResponse['messages'];
                $dynamicFields = $processFormSubmissionResponse['dynamicFields'];
                unset($processFormSubmissionResponse);
            } else {
                if ($contextType === 'shop') {
                    $validateCredentialsResponse = $this->validateCredentials(true);
                    $messages = $validateCredentialsResponse['messages'];
                    $dynamicFields = $validateCredentialsResponse['dynamicFields'];
                    unset($validateCredentialsResponse);
                    if (empty($messages['error'])) {
                        $currencyRestrictions = $this->getCurrencyRestrictions($this->getCredentials());
                        $messages = array_merge_recursive($messages, $currencyRestrictions['messages']);
                    }
                    if (\Vivawalletsmartcheckout\Helpers\Config::get('app.module.auto_create_webhook')
                        && empty($messages['error'])
                    ) {
                        if (!$this->validateWebhooks($this->getCredentials())) {
                            $createWebhookMessages = $this->createWebhook($this->getCredentials());
                            $messages = array_merge_recursive($messages, $createWebhookMessages);
                        }
                    }
                }
            }
        } else {
            $messages['error'][] = $this->l(
                'This site is not SSL protected. Please use a valid certificate to use Viva.com payments.'
            );
        }
        if ($contextType !== 'shop') {
            $notAllShopsEnabled = false;
            $activeShops = Shop::getShops(true);
            foreach ($activeShops as $shop) {
                $shopEnabled = \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.module.enabled', null, (int) $shop['id_shop']);
                $demoMode = \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.demo_mode', null, (int) $shop['id_shop']);
                $shopMode = $demoMode ? 'demo' : '';
                if (!$shopEnabled) {
                    $notAllShopsEnabled = true;
                    $messages['warning'][] = sprintf($this->l('The shop %s is not active for payments'), $shop['name']);
                } else {
                    $messages['confirmation'][] = sprintf($this->l('The shop %1s is active for %2s payments'), $shop['name'], $shopMode);
                }
            }
            if ($notAllShopsEnabled) {
                $messages['warning'][] = $this->l('You are using Multistore, and you are in a Group or All Shops context. You must configure Viva.com | Smart Checkout for each store separately.');
            } else {
                $messages['confirmation'][] = $this->l('All shops are active for payments with Viva.com | Smart Checkout.');
            }
            return $header . implode($this->getAdminNotifications($messages, $formSubmitted, $contextType));
        }
        if (!$formSubmitted || empty($messages['error']) || $this->environmentModeChanged()) {
            $this->toggleModuleStatus(empty($messages['error']));
        }

        return $header
            . implode($this->getAdminNotifications($messages, $formSubmitted, $contextType))
            . $this->displayForm(true, $dynamicFields);
    }

    /**
     * Load assets for configuration page
     */
    private function loadConfigurationAssets()
    {
        $locale = Tools::getContextLocale($this->context);

        Media::addJsDef([
            'samplebank' => $this->l('Sample Bank'),
            'transactionReference' => $this->l('Transaction Reference'),
            'amount' => $this->l('Amount'),
            'yourCompanyName' => $this->l('YourCompanyName'),
            'formatedAmount' => $locale->formatPrice(20, $this->context->currency->iso_code),
        ]);

        $this->context->controller->addJS($this->_path . 'views/js/vivawallet-admin-main.js');
        $this->context->controller->addCSS($this->_path . 'views/css/vivawallet-admin-main.css');
    }

    /**
     * Process the submission of the configuration form
     *
     * @return array
     */
    private function processFormSubmission(): array
    {
        $validateFormResponse = $this->validateForm();
        $messages = $validateFormResponse['messages'];
        $dynamicFields = $validateFormResponse['dynamicFields'];
        unset($validateFormResponse);
        $credentialsFromRequest = $this->getCredentials(false);
        if (empty($messages['error'])) {
            $currencyRestrictions = $this->getCurrencyRestrictions($credentialsFromRequest);
            $messages = array_merge_recursive($messages, $currencyRestrictions['messages']);
        }

        $dynamicDescriptor = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest('app.form.fields.dynamic_descriptor');
        if (!empty($dynamicDescriptor)) {
            if (
                strlen($dynamicDescriptor) > 13
            ) {
                $messages['error'][] = $this->l('Invalid dynamic descriptor. Max length is 13 characters');
            }

            if (
                !preg_match('/^[a-zA-Z0-9 ]+$/', $dynamicDescriptor)
            ) {
                $messages['error'][] = $this->l('Invalid dynamic descriptor. Please remove special characters');
            }

            if (
                is_numeric($dynamicDescriptor)
                && (int) $dynamicDescriptor === 0
            ) {
                $messages['error'][] = $this->l('Invalid dynamic descriptor. Your input is a sequence of zeros.');
            }
        }

        $demoModeField = \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo_mode');
        if ((
            empty($messages['error'])
            && !$this->saveConfigurationForm($dynamicFields)
        )
        || (
            !empty($messages['error'])
            && !Configuration::updateValue($demoModeField, Tools::getValue($demoModeField))
        )
        ) {
            $messages['error'][] = $this->l('Failed to save Viva.com settings');
        }

        if (\Vivawalletsmartcheckout\Helpers\Config::get('app.module.auto_create_webhook')
            && empty($messages['error'])
        ) {
            if (!$this->validateWebhooks($credentialsFromRequest)) {
                $createWebhookMessages = $this->createWebhook($credentialsFromRequest);
                $messages = array_merge_recursive($messages, $createWebhookMessages);
            }
        }

        return ['messages' => $messages, 'dynamicFields' => $dynamicFields];
    }

    private function environmentModeChanged()
    {
        $demoModeField = \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo_mode');
        $fromDatabase = Configuration::get($demoModeField);
        $fromDatabaseMode = empty($fromDatabase) ? 0 : 1;
        $fromUser = Tools::getValue($demoModeField);
        $fromUserMode = empty($fromUser) ? 0 : 1;

        return $fromUserMode !== $fromDatabaseMode;
    }

    /**
     * Validate configuration form
     *
     * @return array
     */
    private function validateForm(): array
    {
        $validateCredentialsResponse = $this->validateCredentials();
        $messages = $validateCredentialsResponse['messages'];
        $dynamicFields = $validateCredentialsResponse['dynamicFields'];
        unset($validateCredentialsResponse);

        $preferredStatus = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest('app.form.fields.preferred_status');
        if (empty($preferredStatus)
            || !in_array($preferredStatus, \Vivawalletsmartcheckout\Helpers\Config::get('app.form.state_options'))
        ) {
            $messages['error'][] = $this->l('Invalid Order status');
        }

        $installmentsPattern = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest('app.form.fields.installments');
        if (!empty($installmentsPattern)) {
            $installmentParts = array_map('trim', explode(',', $installmentsPattern));
            foreach ($installmentParts as $installmentPart) {
                $installmentOptions = array_map('trim', explode(':', $installmentPart));
                if (count($installmentOptions) !== 2
                    || !Validate::isUnsignedInt($installmentOptions[0])
                    || !Validate::isUnsignedInt($installmentOptions[1])
                ) {
                    $messages['error'][] = sprintf($this->l('Invalid %s format'), $this->l('Installments'));
                    break;
                }
            }
        }

        $title = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest('app.form.fields.title');
        if (!empty($title) && !Validate::isGenericName($title)) {
            $messages['error'][] = sprintf($this->l(' %s includes invalid characters'), $this->l('Title'));
        }
        $description = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest('app.form.fields.description');
        if (!empty($description) && !Validate::isGenericName($description)) {
            $messages['error'][] = sprintf($this->l(' %s includes invalid characters'), $this->l('Description'));
        }

        return ['messages' => $messages, 'dynamicFields' => $dynamicFields];
    }

    /**
     * Validate only credentials for current mode
     *
     * @param bool $fromDatabase
     *
     * @return array[]
     */
    private function validateCredentials(bool $fromDatabase = false): array
    {
        $messages = $dynamicFields = [];
        $credentials = $this->getCredentials($fromDatabase);
        $authentication = null;
        if (!empty($credentials['environment'])
            && !empty($credentials['clientId'])
            && !empty($credentials['clientSecret'])
        ) {
            $authentication = $this->getBearerAuthentication(
                [
                    'environment' => $credentials['environment'],
                    'clientId' => $credentials['clientId'],
                    'clientSecret' => $credentials['clientSecret'],
                ]
            );
        }
        if (!is_null($authentication) && $authentication->hasValidToken()) {
            if (!empty($credentials['sourceCode']) && ($fromDatabase || !$this->areCredentialsNew())) {
                $sourceClient = new \Vivawallet\VivawalletPhp\Api\SourceClient($authentication);
                $sourcesResponse = $sourceClient->checkSource($credentials['sourceCode']);
                $domain = Shop::isFeatureActive() ? \Vivawalletsmartcheckout\Helpers\General::getDomain() : Configuration::get('PS_SHOP_DOMAIN');
                if (!$sourcesResponse->isSuccessful() || is_null($sourcesResponse->getBody()->getState())) {
                    $errorMessage = $this->l('The source code does not exist.');
                    $errorMessage .= $fromDatabase
                        ? $this->l('Please select other source code from the list and save again.')
                        : '';
                    $messages['error'][] = $errorMessage;
                } elseif ($sourcesResponse->getBody()->getState() !== 1) {
                    $messages['error'][] = $this->l('The source code is blocked');
                } elseif ($sourcesResponse->getBody()->getDomain() !== $domain) {
                    $errorMessage = $this->l('The source code is not valid for this domain.');
                    $errorMessage .= $fromDatabase
                        ? $this->l('Please select other source code from the list and save again.')
                        : '';
                    $messages['error'][] = $errorMessage;
                } elseif ($sourcesResponse->getBody()->getSuccessUrl() !==
                    \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
                        'success',
                        (string) $credentials['environment']
                    )
                    || $sourcesResponse->getBody()->getFailureUrl() !==
                    \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
                        'failure',
                        (string) $credentials['environment']
                    )
                ) {
                    $errorMessage = $this->l('The source code has wrong redirection urls.');
                    $errorMessage .= $fromDatabase
                        ? $this->l('Please select other source code from the list and save again.')
                        : '';
                    $messages['error'][] = $errorMessage;
                }
            } else {
                $information = [];
                $dynamicFields['sources'][$credentials['environment']] = $this->getSources(
                    $authentication,
                    $information
                );
                $sources = array_values((array) $dynamicFields['sources'][$credentials['environment']]);
                if (empty($sources[0])) {
                    $messages['error'][] = $this->l('Empty Source Code List, please check your configuration and log info.');
                }
            }
        } else {
            $contextType = \Vivawalletsmartcheckout\Helpers\General::getContextType(Shop::getContext());
            if ($contextType === 'shop') {
                $messages['error'][] = $this->l('Cannot connect to Viva.com. Please Check your credentials');
            }
        }

        return ['messages' => $messages, 'dynamicFields' => $dynamicFields];
    }

    /**
     * Save configuration Form in configuration table
     *
     * @param array $dynamicFields
     *
     * @return bool
     */
    private function saveConfigurationForm(array $dynamicFields = []): bool
    {
        $environment = $this->getEnvironment(false);
        $sourceCodeField = \Vivawalletsmartcheckout\Helpers\Config::get("app.form.fields.$environment.source");
        $success = true;
        if (empty(Tools::getValue($sourceCodeField)) || $this->areCredentialsNew()) {
            $sources = array_values((array) $dynamicFields['sources'][$environment]);
            if (!empty($sources[0])
                && $sources[0] instanceof \Vivawallet\VivawalletPhp\Core\Source\SourceItem
                && !empty($sources[0]->getCode())
            ) {
                $success = $success && \Vivawalletsmartcheckout\Helpers\Config::updateDatabase($sourceCodeField, $sources[0]->getCode());
            }
        } else {
            $success = $success && \Vivawalletsmartcheckout\Helpers\Config::updateDatabase($sourceCodeField, Tools::getValue($sourceCodeField));
        }

        $formFields = array_diff(
            $this->getFormFields(),
            \Vivawalletsmartcheckout\Helpers\General::getAllChildrenOfArray(
                \Vivawalletsmartcheckout\Helpers\Config::get(
                    'app.form.fields.' . ($environment == 'demo' ? 'live' : 'demo')
                )
            ),
            [$sourceCodeField]
        );
        $success = $success && !empty($formFields);
        foreach ($formFields as $formField) {
            $success = $success && \Vivawalletsmartcheckout\Helpers\Config::updateDatabase($formField, Tools::getValue($formField));
        }

        return $success;
    }

    private function atLeastOneFieldHasChanged()
    {
        if ($this->areCredentialsNew()) {
            return true;
        }
        $credentialsFromDatabase = $this->getCredentials();
        $credentialsFromUser = $this->getCredentials(false);
        if ((string) $credentialsFromDatabase['sourceCode'] !== (string) $credentialsFromUser['sourceCode']) {
            return true;
        }
        foreach (['title', 'description', 'preferred_status', 'installments', 'brand_color'] as $field) {
            $fromDatabase = \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase("app.form.fields.$field");
            $fromUser = \Vivawalletsmartcheckout\Helpers\Config::getFromRequest("app.form.fields.$field");
            if ((string) $fromDatabase !== (string) $fromUser) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if credentials have changed values
     *
     * @return bool
     */
    private function areCredentialsNew(): bool
    {
        $fromDatabase = $this->getCredentials();
        $fromUser = $this->getCredentials(false);

        return sha1($fromDatabase['environment'] . $fromDatabase['clientId'] . $fromDatabase['clientSecret']) !==
            sha1($fromUser['environment'] . $fromUser['clientId'] . $fromUser['clientSecret']);
    }

    /**
     * Get currency information
     *
     * @param array $credentials
     * @param array $enabledCurrencies
     *
     * @return array
     */
    private function getCurrencyRestrictions(array $credentials = [], array $enabledCurrencies = []): array
    {
        $allowedCurrencies = \Vivawalletsmartcheckout\Helpers\General::convertIsoCodeNumArrayToIsoCode(
            $this->getMerchantAccountCurrencies($credentials)
        );
        $enabledCurrencies = empty($enabledCurrencies)
            ? Currency::getPaymentCurrencies($this->id)
            : $enabledCurrencies;
        $notAllowedCurrencies = is_array($enabledCurrencies)
            ? array_diff(array_column($enabledCurrencies, 'iso_code'), $allowedCurrencies)
            : [];
        $messages = [];
        if (!empty($notAllowedCurrencies)) {
            $errorMessage = sprintf(
                $this->l('Your Viva.com account does not support the selected currencies: %s. '),
                implode(', ', $notAllowedCurrencies)
            );
            $errorMessage .= sprintf(
                $this->l('The allowed currencies are: %s.'),
                implode(', ', $allowedCurrencies)
            );
            $messages['error'][] = $errorMessage;
            $messages['information'][] = $this->l(
                'Please change your currency settings for this module in order to fit the allowed currencies.'
            );
        }

        return ['messages' => $messages];
    }

    /**
     * Get notifications when submitting configuration form
     *
     * @param array $messages
     * @param bool $formSubmitted
     * @aram string $contextType
     *
     * @return array
     */
    private function getAdminNotifications(array $messages, bool $formSubmitted, string $contextType = ''): array
    {
        if ($contextType === 'shop') {
            if ($this->isModuleEnabled()) {
                $messages['confirmation'][] = $this->l('Viva.com settings are valid');
                $messages['confirmation'][] = sprintf(
                    $this->l('Viva.com | Smart Checkout is active for %s payments'),
                    $this->getEnvironment()
                );
            } else {
                $messages['warning'][] = $this->l('Viva.com | Smart Checkout is not active for payments');
            }
        }
        if ($formSubmitted && !empty($messages['error']) && $this->atLeastOneFieldHasChanged()) {
            $messages['information'][] = $this->l('Viva.com | Smart Checkout reserves your previous settings');
        }
        $messages = array_merge(
            array_fill_keys(['error', 'information', 'warning', 'confirmation'], []),
            $messages
        );
        $notifications = [];
        foreach ($messages as $type => $msg) {
            if (!empty($msg)) {
                $type = method_exists($this, 'display' . Tools::ucfirst($type)) ? $type : 'info';
                $notifications = array_merge(
                    $notifications,
                    array_map([$this, 'display' . Tools::ucfirst($type)], $msg)
                );
            }
        }

        return $notifications;
    }

    /**
     * Helper displaying information message(s).
     *
     * @param string|array $information
     *
     * @return string
     */
    public function displayInformation($information): string
    {
        if (method_exists(parent::class, 'displayInformation')) {
            return parent::displayInformation($information);
        }

        return parent::displayWarning($information);
    }

    /**
     * Builds the configuration form
     *
     * @param bool $fieldsFromDb
     * @param array $dynamicFields
     *
     * @return string
     */
    public function displayForm(bool $fieldsFromDb = true, array $dynamicFields = []): string
    {
        $formFields = $this->getFormFields();

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&'
            . http_build_query(
                ['configure' => $this->name]
            );
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        try {
            $helper->fields_value = $fieldsFromDb
                ? Configuration::getMultiple($formFields)
                : array_combine($formFields, array_map([Tools::class, 'getValue'], $formFields));
        } catch (Exception $exception) {
        }
        $credentials = [
            'VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_ID' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.demo.client_id'),
            'VIVAWALLET_SMART_CHECKOUT_DEMO_CLIENT_SECRET' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.demo.client_secret'),
            'VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_ID' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.live.client_id'),
            'VIVAWALLET_SMART_CHECKOUT_LIVE_CLIENT_SECRET' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.live.client_secret'),
        ];
        $fieldsValues = array_merge(
            $helper->fields_value,
            $credentials
        );
        $helper->fields_value = $fieldsValues;
        $helper->name_controller = "{$this->name}-form";
        $helper->identifier = $this->identifier;

        return $helper->generateForm([$this->getFormFieldsStructure($helper, $dynamicFields)]);
    }

    /**
     * Get Configuration form fields settings
     *
     * @param HelperForm $helperForm
     * @param array $dynamicFields
     *
     * @return array[]
     */
    private function getFormFieldsStructure(HelperForm $helperForm, array $dynamicFields = []): array
    {
        $sourceOptions = [];
        foreach (['demo', 'live'] as $environment) {
            if (!isset($dynamicFields['sources'][$environment])) {
                $clientIdField = \Vivawalletsmartcheckout\Helpers\Config::get(
                    "app.form.fields.$environment.client_id"
                );
                $clientSecret = \Vivawalletsmartcheckout\Helpers\Config::get(
                    "app.form.fields.$environment.client_secret"
                );
                $dynamicFields['sources'][$environment] = $this->getSources(
                    $this->getBearerAuthentication(
                        [
                            'environment' => $environment,
                            'clientId' => $helperForm->fields_value[$clientIdField],
                            'clientSecret' => $helperForm->fields_value[$clientSecret],
                        ]
                    )
                );
            }
            $sourceOptions[$environment] = array_values(
                array_map(
                    function (Vivawallet\VivawalletPhp\Core\Source\SourceItem $source) {
                        return [
                            'id_option' => $source->getCode(),
                            'name' => $source->getSourceNameToDisplay(),
                        ];
                    },
                    (array) $dynamicFields['sources'][$environment]
                )
            );
        }

        $sourceDescription = $this->l(
            'Provides a list with all source codes that are set in your Viva.com banking app'
        );

        if (
            !empty($shopId = Context::getContext()->shop->id)
            && Shop::isFeatureActive()
        ) {
            $brandColor = \Vivawalletsmartcheckout\Helpers\Database::getBrandColorByShopId($shopId);
        } else {
            $brandColor = Configuration::get(\Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.brand_color'));
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Demo Mode'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo_mode'),
                        'is_bool' => true,
                        'desc' => $this->l(
                            'If Demo Mode is enabled, please use the credentials you got from demo.vivapayments.com'
                        ),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable  preauthorizations'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.transaction_type'),
                        'is_bool' => true,
                        'desc' => $this->l(
                            'By enabling this you will create order in preauthorized status wich you will need to capture or void manually'
                        ),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Client ID provided by Viva.com'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.client_id'),
                        'label' => $this->l('Demo Client ID'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Client Secret provided by Viva.com'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.client_secret'),
                        'label' => $this->l('Demo Client Secret'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $sourceDescription,
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.source'),
                        'label' => $this->l('Demo Source Code List'),
                        'options' => [
                            'query' => $sourceOptions['demo'] ?? [],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Client ID provided by Viva.com'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.client_id'),
                        'label' => $this->l('Live Client ID'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Client Secret provided by Viva.com'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.client_secret'),
                        'label' => $this->l('Live Client Secret'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $sourceDescription,
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.source'),
                        'label' => $this->l('Live Source Code List'),
                        'options' => [
                            'query' => $sourceOptions['live'] ?? [],
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('This controls the title which the user sees on checkout page'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.title'),
                        'label' => $this->l('Title'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('This controls the description the user sees on checkout page.'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.description'),
                        'label' => $this->l('Description'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'desc' => $this->l(
                            'Order status to set when the payment is completed and order is created'
                        ),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.preferred_status'),
                        'label' => $this->l('Order status after successful payment'),
                        'options' => [
                            'query' => $this->getOrderStateOptions((int) $helperForm->default_form_language),
                            'id' => 'id_option',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'col' => 5,
                        'type' => 'color',
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.brand_color'),
                        'label' => $this->l('Brand color'),
                        'desc' => !empty($brandColor) ? sprintf($this->l('Selected brand color: %s'), $brandColor) : $this->l('Brand color not set'),
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter the value your customers will see on their transactions. Use a recognizable name e.g. the legal entity name or the website address to avoid potential disputes and chargebacks. Can contain only Latin characters, numbers, and space'),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.dynamic_descriptor'),
                        'label' => $this->l('Customer bank statement suffix'),
                        'size' => 13,
                        'maxlength' => 13,
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(
                            'WARNING: Only available to Greek Viva.com accounts. Example: 90:3,180:6. Order total 90 Euros -> allow 0 and 3 installments.
                            Order total 180 Euros -> allow 0, 2, 3, 4, 5 and 6 installments. Leave empty in case you do not want to offer installments. Max installments 36.'
                        ),
                        'name' => \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.installments'),
                        'label' => $this->l('Installments'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Get the available order state to set when the payment is completed and order is created
     *
     * @param int $formLanguage
     *
     * @return array
     */
    private function getOrderStateOptions(int $formLanguage): array
    {
        $orderStateOptions = [];
        $orderStateOptionFields = \Vivawalletsmartcheckout\Helpers\Config::get('app.form.state_options');
        foreach ($orderStateOptionFields as $orderStateOptionField) {
            if (Configuration::hasKey($orderStateOptionField)) {
                try {
                    $orderState = new OrderState(Configuration::get($orderStateOptionField));
                    if (Validate::isLoadedObject($orderState)) {
                        $orderStateOptions[] = [
                            'id_option' => $orderStateOptionField,
                            'name' => $orderState->name[$formLanguage] ?? reset($orderState->name),
                        ];
                    }
                } catch (Exception $exception) {
                }
            }
        }

        return $orderStateOptions;
    }

    // </editor-fold>

    // <editor-fold desc="Hooks">

    // <editor-fold desc="Front">

    /**
     * Add to header of every page
     */
    public function hookDisplayHeader()
    {
        if (
            (
                isset($this->context->controller->php_self)
                && 'order' == $this->context->controller->php_self
            )
            || (
                isset($this->context->controller->page_name)
                && stripos($this->context->controller->page_name, $this->name) !== false
            )
        ) {
            $this->context->controller->addCSS($this->_path . 'views/css/vivawallet-front-main.css');
        }
    }

    /**
     * Hook that is triggered in checkout page (PS version >= 1.7)
     *
     * @param $params
     *
     * @return array
     */
    public function hookPaymentOptions($params): array
    {
        $paymentOptions = $this->processPaymentOptions($params);
        if (!empty($paymentOptions)) {
            $this->smarty->assign('description', $paymentOptions['description']);
            $newOption = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $newOption->setCallToActionText($paymentOptions['title'])
                ->setAction($paymentOptions['action'])
                ->setLogo($paymentOptions['logo']['generic'])
                ->setModuleName($this->name)
                ->setAdditionalInformation(
                    $this->fetch("module:{$this->name}/views/templates/hook/payment_description.tpl")
                );

            return [$newOption];
        }

        return [];
    }

    /**
     * Hook that is triggered in checkout page (PS version < 1.7)
     *
     * @param $params
     *
     * @return string
     */
    public function hookPayment($params): string
    {
        $paymentOptions = $this->processPaymentOptions($params);
        if (!empty($paymentOptions)) {
            $this->smarty->assign($paymentOptions);

            return $this->display(__FILE__, 'payment.tpl');
        }

        return '';
    }

    /**
     * Hook that is triggered in checkout page
     * when the advanced checkout page is activated in the module advancedeucompliance (PS version < 1.7)
     *
     * @param $params
     *
     * @return array
     */
    public function hookDisplayPaymentEU($params): array
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $paymentOptions = $this->processPaymentOptions($params);
            if (!empty($paymentOptions)) {
                return [
                    'cta_text' => $paymentOptions['title'],
                    'logo' => $paymentOptions['logo']['generic_180'],
                    'action' => $paymentOptions['action'],
                ];
            }
        }

        return [];
    }

    /**
     * Actions of payment options hook
     *
     * @param $params
     *
     * @return array
     */
    private function processPaymentOptions($params): array
    {
        if (!\Vivawalletsmartcheckout\Helpers\Config::get('app.module.ssl_protected') || Tools::usingSecureMode()) {
            if ($this->isModuleEnabled() && isset($params['cart']->id_currency)) {
                $currencyObject = new Currency($params['cart']->id_currency);
                $currencyRestrictions = $this->getCurrencyRestrictions(
                    $this->getCredentials(),
                    [['iso_code' => $currencyObject->iso_code]]
                );
                if (empty($currencyRestrictions['messages']['error'])) {
                    $title = \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.title');
                    $description = \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase(
                        'app.form.fields.description'
                    );

                    return [
                        'logo' => [
                            'generic' => Media::getMediaPath(
                                $this->_path . 'views/img/vw-logo-cropped.svg'
                            ),
                            'generic_120' => Media::getMediaPath(
                                $this->_path . 'views/img/vw-logo.svg'
                            ),
                            'generic_180' => Media::getMediaPath(
                                $this->_path . 'views/img/cards-wallets-transfers-more-180.png'
                            ),
                        ],
                        'title' => !empty($title) ? $title : $this->displayName,
                        'description' => !empty($description)
                            ? $description
                            : $this->l('Pay using 30+ methods (cards, digital wallets, local payment methods, online banking, and more)'),
                        'action' => $this->context->link->getModuleLink($this->name, 'createOrder', [], true),
                    ];
                }
            }
        }

        return [];
    }

    /**
     * Hook that triggered in order confirmation
     *
     * @param array $params
     *
     * @return false|string
     *
     * @throws Exception
     */
    public function hookDisplayPaymentReturn(array $params)
    {
        if (!$this->active) {
            return;
        }

        if (version_compare(_PS_VERSION_, '8.0', '>=')) {
            $prestashopVersion = '8.0';
        } elseif (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $prestashopVersion = '1.7';
        } else {
            $prestashopVersion = '1.6';
        }
        $order = $prestashopVersion >= '1.7' ? $params['order'] : $params['objOrder'];

        $statusMapArray = [
            Configuration::get('PS_OS_ERROR') => 'failed',
            Configuration::get('VIVAWALLET_SMART_CHECKOUT_OS_PENDING') => 'pending',
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID') => 'pending',
            Configuration::get('PS_OS_OUTOFSTOCK_PAID') => 'pending',
            Configuration::get('VIVAWALLET_SMART_CHECKOUT_OS_AWAITING_PAYMENT') => 'pending',
        ];
        $status = $statusMapArray[$order->getCurrentOrderState()->id] ?? 'ok';

        $context = \Context::getContext();
        $locale = $context->currentLocale;
        $currency = new Currency($order->id_currency);

        $this->smarty->assign(
            [
                'status' => $status,
                'prestashop_version' => $prestashopVersion,
                'reference' => $order->reference,
                'total' => $locale->formatPrice($order->total_paid, $currency->iso_code),
            ]
        );

        return $this->display(
            __FILE__,
            (version_compare(_PS_VERSION_, '1.7', '>=') ? 'views/templates/front/' : '') . 'payment_return.tpl'
        );
    }

    // </editor-fold>

    // <editor-fold desc="Admin">

    /**
     * Backward compatibility (removed in 1.7.7 in favor of) -> displayAdminOrderMain
     *
     * @param array $params
     *
     * @return false|string
     */
    public function hookDisplayAdminOrderLeft(array $params)
    {
        $this->actionGetAdminOrderButtons($params);

        return $this->displayAdminOrderMain($params);
    }

    /**
     * Pass the appropriate parameters to javascript used in order view
     *
     * @param array $params
     */
    private function actionGetAdminOrderButtons(array $params)
    {
        try {
            $container = SymfonyContainer::getInstance();
            $router = $container->get('router');
            $refundUrl = $router->generate(
                'vivawalletsmartcheckout_refund_ajax',
                ['orderId' => (int) $params['id_order']],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $captureUrl = $router->generate(
                'vivawalletsmartcheckout_capture_ajax',
                ['orderId' => (int) $params['id_order']],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $voidUrl = $router->generate(
                'vivawalletsmartcheckout_void_ajax',
                ['orderId' => (int) $params['id_order']],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $order = new Order($params['id_order']);
        } catch (PrestaShopException $exception) {
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                ['name' => 'actionGetAdminOrderButtons', 'exception' => $exception->getMessage()],
                'retrievePrestashopOrder'
            );
        }
        if (!empty($order)
            && Validate::isLoadedObject($order)
            && $this->name === $order->module
            && $order->current_state !== (int) Configuration::get('PS_OS_REFUND')
        ) {
            Media::addJsDef(
                [
                    'order_id' => $order->id,
                    'order_reference' => $order->reference,
                    'payment_method' => $order->module,
                    'vivawallet_void_url' => $voidUrl,
                    'vivawallet_refund_url' => $refundUrl,
                    'vivawallet_capture_url' => $captureUrl,
                    'general_error_message' => $this->l('An error occurred.'),
                    'amount_error_message' => $this->l('Amount requested is not valid.'),
                ]
            );
        }
    }

    /**
     * Displays content in the order view page in the main column under the details view
     *
     * @param array $params
     *
     * @return false|string
     */
    private function displayAdminOrderMain(array $params)
    {
        if (!$this->active) {
            return false;
        }

        try {
            $order = new Order($params['id_order']);
        } catch (PrestaShopException $exception) {
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                ['name' => 'displayAdminOrderMain', 'exception' => $exception->getMessage()],
                'retrievePrestashopOrder'
            );
        }
        if (!empty($order) && Validate::isLoadedObject($order) && $this->name === $order->module) {
            $refundableAmount = 0;
            $transactions = \Vivawalletsmartcheckout\Helpers\Database::getTransactionsByOrder($order);
            $numOfPayments = 0;
            $numOfRefunds = 0;
            $numOfCaptures = 0;
            $numOfVoids = 0;
            $allProductsRefunded = true;
            foreach ($transactions as $transaction) {
                if ($transaction['transaction_type'] == 'refund') {
                    $refundableAmount -= ((float) $transaction['transaction_amount']);
                    ++$numOfRefunds;
                } elseif ($transaction['transaction_type'] == 'payment') {
                    $refundableAmount += ((float) $transaction['transaction_amount']);
                    ++$numOfPayments;
                } elseif ($transaction['transaction_type'] == 'capture') {
                    $refundableAmount += ((float) $transaction['transaction_amount']);
                    ++$numOfCaptures;
                } elseif ($transaction['transaction_type'] == 'void') {
                    $refundableAmount += ((float) $transaction['transaction_amount']);
                    ++$numOfVoids;
                }
            }
            usort(
                $transactions,
                function ($a, $b) {
                    return strtotime($a['transaction_date_created']) - strtotime($b['transaction_date_created']);
                }
            );
            $smartCheckoutOrder = \Vivawalletsmartcheckout\Helpers\Database::getSmartCheckoutOrderByPrestashopOrder(
                $order
            );
            $orderCurrency = new Currency($order->id_currency);
            $orderDetails = $order->getProductsDetail();
            foreach ($orderDetails as $product) {
                if ($product['product_quantity'] > $product['product_quantity_reinjected']) {
                    $allProductsRefunded = false;
                }
            }
            if (version_compare(_PS_VERSION_, '8.0', '>=')) {
                $prestashopVersion = '8.0';
            } elseif (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $prestashopVersion = '1.7';
            } else {
                $prestashopVersion = '1.6';
            }
            $this->smarty->assign(
                [
                    'transactions' => $transactions,
                    'refundable_amount' => $refundableAmount,
                    'orderCode' => $smartCheckoutOrder['vivawallet_order_code'],
                    'payments_number' => $numOfPayments,
                    'refunds_number' => $numOfRefunds,
                    'captures_number' => $numOfCaptures,
                    'voids_number' => $numOfVoids,
                    'order_details' => $orderDetails,
                    'prestashop_version' => $prestashopVersion,
                    'products_refunded' => $allProductsRefunded,
                    'order_currency' => version_compare(_PS_VERSION_, '1.7', '>=') ? $orderCurrency->symbol : $orderCurrency->sign,
                ]
            );

            return $this->display(__FILE__, 'views/templates/hook/admin_content_order.tpl');
        }

        return false;
    }

    /**
     * Pass the appropriate parameters to javascript used in order view
     *
     * @param array $params
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $this->actionGetAdminOrderButtons($params);
    }

    /**
     * Include javascript and css files of custom order view
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/vivawallet-admin-refunds.js');
        $this->context->controller->addJS($this->_path . 'views/js/vivawallet-admin-capture.js');
        $this->context->controller->addJS($this->_path . 'views/js/vivawallet-admin-utils.js');
        $this->context->controller->addCSS($this->_path . 'views/css/vivawallet-admin-refunds.css');
        $this->context->controller->addCSS($this->_path . 'views/css/vivawallet-admin-capture.css');
    }

    /**
     * Displays content in the order view page in the main column under the details view
     *
     * @param array $params
     *
     * @return false|string
     */
    public function hookDisplayAdminOrderMain(array $params)
    {
        return $this->displayAdminOrderMain($params);
    }

    // </editor-fold>

    // </editor-fold>

    // <editor-fold desc="Viva payments">

    /**
     * Get authentication needed for Vivapayments API
     *
     * @param array $credentials
     *
     * @return \Vivawallet\VivawalletPhp\Http\Authentication\BearerAuthentication
     */
    public function getBearerAuthentication(
        array $credentials = []
    ): Vivawallet\VivawalletPhp\Http\Authentication\BearerAuthentication {
        $credentials = empty($credentials) ? $this->getCredentials() : $credentials;
        $environment = $credentials['environment'] ?? '';
        $clientId = $credentials['clientId'] ?? '';
        $clientSecret = $credentials['clientSecret'] ?? '';
        $hashKey = sha1(implode([$this->name, '_authentication_', $environment, $clientId, $clientSecret]));

        $authenticationArray = Cache::retrieve($hashKey);
        $expirationLimit = \Vivawalletsmartcheckout\Helpers\Config::get(
            'app.viva_payments.token_expiration_time'
        );

        if (!empty($authenticationArray)
            && isset($authenticationArray['authentication'], $authenticationArray['expiration'])
            && $authenticationArray['authentication']
            instanceof \Vivawallet\VivawalletPhp\Http\Authentication\BearerAuthentication
            && $authenticationArray['authentication']->hasValidToken()
            && $authenticationArray['authentication']->getTtl() > $expirationLimit
            && $authenticationArray['expiration'] > time()
        ) {
            $authentication = $authenticationArray['authentication'];
        } else {
            $authentication = new \Vivawallet\VivawalletPhp\Http\Authentication\BearerAuthentication(
                $environment,
                $clientId,
                $clientSecret,
                \Vivawalletsmartcheckout\Helpers\Config::get('app.viva_payments.grant_type'),
                \Vivawalletsmartcheckout\Helpers\Config::get('app.viva_payments.scope')
            );
            if ($authentication->hasValidToken()) {
                Cache::store(
                    $hashKey,
                    [
                        'authentication' => $authentication,
                        'expiration' => time() + $authentication->getTtl() - $expirationLimit,
                    ]
                );
            } elseif (!empty($authentication->getResponse())) {
                \Vivawalletsmartcheckout\Loggers\Logger::log(
                    [
                        'call' => 'getBearerAuthentication',
                        'response' => $authentication->getResponse()->all(),
                    ],
                    'vivaPayments'
                );
            }
        }

        return $authentication;
    }

    /**
     * Retrieve a list of sources.
     *
     * @param \Vivawallet\VivawalletPhp\Http\Authentication\Authentication $authentication
     * @param $information
     *
     * @return \Vivawallet\VivawalletPhp\Core\Source\SourceList
     */
    private function getSources(
        Vivawallet\VivawalletPhp\Http\Authentication\Authentication $authentication,
        &$information = null
    ): Vivawallet\VivawalletPhp\Core\Source\SourceList {
        if ($authentication->hasValidToken()) {
            $urls = [
                'success' => \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
                    'success',
                    (string) $authentication->getEnvironment()
                ),
                'failure' => \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
                    'failure',
                    (string) $authentication->getEnvironment()
                ),
            ];
            $domain = \Vivawalletsmartcheckout\Helpers\General::getDomain();
            if (!empty($domain)) {
                $sourceList = \Vivawallet\VivawalletPhp\Controller\SourceController::getSourceList(
                    $domain,
                    $authentication,
                    $urls,
                    $information
                );
            } else {
                $sourceList = new \Vivawallet\VivawalletPhp\Core\Source\SourceList();
            }
            foreach (['getResponse' => 'getSources', 'createResponse' => 'createSource'] as $field => $call) {
                if (isset($information[$field])
                    && $information[$field] instanceof \Vivawallet\VivawalletPhp\Http\Response
                    && !$information[$field]->isSuccessful()
                ) {
                    \Vivawalletsmartcheckout\Loggers\Logger::log(
                        [
                            'call' => $call,
                            'arguments' => ['domain' => $domain, 'urls' => $urls],
                            'response' => $information[$field]->all(),
                        ],
                        'vivaPayments'
                    );
                }
            }
        } else {
            $sourceList = new \Vivawallet\VivawalletPhp\Core\Source\SourceList();
        }

        return $sourceList;
    }

    /**
     * Get currencies of merchant account
     *
     * @param array $credentials
     *
     * @return array
     */
    public function getMerchantAccountCurrencies(array $credentials = []): array
    {
        $currencies = [];
        $merchantClient = new \Vivawallet\VivawalletPhp\Api\MerchantClient(
            $this->getBearerAuthentication($credentials)
        );
        $response = $merchantClient->getInfo();
        if ($response->isSuccessful() && isset($response->getBody()->currencies)) {
            $currencies = $response->getBody()->currencies;
        } else {
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                ['call' => 'merchantInfo', 'response' => $response->all()],
                'vivaPayments'
            );
        }

        return $currencies;
    }

    /**
     *  Get webhooks of merchant account
     *
     * @param array $credentials
     *
     * @return array
     */
    private function getMerchantAccountWebhooks(array $credentials = []): array
    {
        $webhooks = [];
        $merchantClient = new \Vivawallet\VivawalletPhp\Api\MerchantClient(
            $this->getBearerAuthentication($credentials)
        );
        $response = $merchantClient->getInfo();
        if ($response->isSuccessful() && isset($response->getBody()->webhooks)) {
            $webhooks = (array) $response->getBody()->webhooks;
        } else {
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                ['call' => 'merchantInfoWebhooks', 'response' => $response->all()],
                'vivaPayments'
            );
        }
        return $webhooks;
    }

    /**
     * Check merchant valid webhooks
     *
     * @param array $credentials
     *
     * @return bool
     */
    private function validateWebhooks(array $credentials): bool
    {
        $activeWebhooks = [
            'transactionCreated' => [],
            'transactionFailed' => [],
        ];
        $merchantWebhooks = $this->getMerchantAccountWebhooks($credentials);
        foreach ($merchantWebhooks as $webhook) {
            $parsedUrl = parse_url($webhook->url);
            if ($webhook->isActive) {
                $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '?environment=' . $credentials['environment'];
                if ($webhook->eventTypeId === 1796) {
                    $activeWebhooks['transactionCreated'][] = $url;
                } elseif ($webhook->eventTypeId === 1798) {
                    $activeWebhooks['transactionFailed'][] = $url;
                }
            }
        }
        $domainWebhookUrl = \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
            'webhook',
            (string) $credentials['environment'],
            true
        );

        return in_array($domainWebhookUrl, $activeWebhooks['transactionCreated'])
            && in_array($domainWebhookUrl, $activeWebhooks['transactionFailed']);
    }

    /**
     * Create Webhook Url
     *
     * @param array $credentials
     *
     * @return array
     */
    private function createWebhook(array $credentials = []): array
    {
        $messages = [];
        $mid = '';
        $successUpdateMid = true;
        $authentication = $this->getBearerAuthentication($credentials);
        if ($authentication->hasValidToken()) {
            $bearerToken = $authentication->getToken();
            $tokenArray = explode('.', $bearerToken);
            $payloadDecoded = json_decode(base64_decode($tokenArray[1]), true);
            $mid = $payloadDecoded['urn:viva:payments:client_person_id'];
        }
        $successUpdateMid = $successUpdateMid && \Vivawalletsmartcheckout\Helpers\Config::updateDatabase(\Vivawalletsmartcheckout\Helpers\Config::get('app.form.merchant_id'), $mid);
        $webhookClient = new \Vivawallet\VivawalletPhp\Api\WebhookClient($authentication);
        $webhookUrl = \Vivawalletsmartcheckout\Helpers\General::getUrlWithoutLanguage(
            'webhook',
            (string) $credentials['environment'],
            true
        );
        $webhookUrl = $webhookUrl . '&mid=' . $mid;
        $webhookResponse = $webhookClient->createWebhook($webhookUrl);

        if (!$webhookResponse->isSuccessful() || !$successUpdateMid) {
            \Vivawalletsmartcheckout\Loggers\Logger::log(
                ['call' => 'createWebhook', 'arguments' => $webhookUrl, 'response' => $webhookResponse->all()],
                'vivaPayments'
            );
            $messages['error'][] = $this->l(
                'There was a problem creating hooks for your website. Make sure that your site is public accessible.'
            );
        }

        return $messages;
    }

    // </editor-fold>

    // <editor-fold desc="General">

    /**
     * Enable/Disable the module
     *
     * @param bool $status
     *
     * @return bool
     */
    private function toggleModuleStatus(bool $status = true): bool
    {
        return Configuration::updateValue(
            \Vivawalletsmartcheckout\Helpers\Config::get('app.module.enabled'),
            (int) $status
        );
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    private function isModuleEnabled(): bool
    {
        return (bool) \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.module.enabled');
    }

    /**
     * Get a list of names (and values) of configuration form
     *
     * @return array fields
     */
    private function getFormFields(): array
    {
        return \Vivawalletsmartcheckout\Helpers\General::getAllChildrenOfArray(
            \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields')
        );
    }

    /**
     * Get credentials information
     *
     * @param bool $fromDatabase
     * @param int|null $shopId
     *
     * @return array
     */
    public function getCredentials(bool $fromDatabase = true, int $shopId = null): array
    {
        $environment = $this->getEnvironment($fromDatabase, $shopId);
        $fieldsName = "app.form.fields.$environment";
        if ($fromDatabase) {
            $credentials = [
                'environment' => $environment,
                'clientId' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase("$fieldsName.client_id", null, $shopId),
                'clientSecret' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase("$fieldsName.client_secret", null, $shopId),
                'sourceCode' => \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase("$fieldsName.source", null, $shopId),
            ];
        } else {
            $credentials = [
                'environment' => $environment,
                'clientId' => \Vivawalletsmartcheckout\Helpers\Config::getFromRequest("$fieldsName.client_id"),
                'clientSecret' => \Vivawalletsmartcheckout\Helpers\Config::getFromRequest("$fieldsName.client_secret"),
                'sourceCode' => \Vivawalletsmartcheckout\Helpers\Config::getFromRequest("$fieldsName.source"),
            ];
        }

        return $credentials;
    }

    /**
     * Get environment information
     *
     * @param bool $fromDatabase
     * @param int|null $shopId
     *
     * @return string
     */
    public function getEnvironment(bool $fromDatabase = true, int $shopId = null): string
    {
        $demoModeField = \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo_mode');
        $demoMode = $fromDatabase ? \Vivawalletsmartcheckout\Helpers\Config::getFromDatabase('app.form.fields.demo_mode', null, $shopId) : Tools::getValue($demoModeField);
        return $demoMode ? 'demo' : 'live';
    }

    /**
     * Get credentials from db based on merchant id
     *
     * @param string $mid
     *
     * @return array
     */
    public function getConfigurationsByMid(string $mid): array
    {
        $config = [];
        $configurations = [];
        $queryShop = new DbQuery();
        $queryShop->select('*')
            ->from('configuration')
            ->where('value = \'' . pSQL($mid) . '\'');
        $result = Db::getInstance()->getRow($queryShop);
        $shopId = !empty($result['id_shop']) ? $result['id_shop'] : null;
        $whereShopId = is_null($shopId) ? 'id_shop IS NULL' : 'id_shop = \'' . pSQL($shopId) . '\'';
        $queryVivaConfig = new DbQuery();
        $queryVivaConfig->select('name, value')
            ->from('configuration')
            ->where($whereShopId)
            ->where('name LIKE "%VIVAWALLET_SMART_CHECKOUT%"');
        $results = Db::getInstance()->executeS($queryVivaConfig);
        if (!empty($results)) {
            foreach ($results as $row) {
                if ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.client_id')) {
                    $configurations['demo']['clientId'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.client_secret')) {
                    $configurations['demo']['clientSecret'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo.source')) {
                    $configurations['demo']['sourceCode'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.demo_mode')) {
                    $configurations['environment'] = $row['value'] ? 'demo' : 'live';
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.client_id')) {
                    $configurations['live']['clientId'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.client_secret')) {
                    $configurations['live']['clientSecret'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.live.source')) {
                    $configurations['live']['sourceCode'] = $row['value'];
                } elseif ($row['name'] == \Vivawalletsmartcheckout\Helpers\Config::get('app.form.fields.preferred_status')) {
                    $configurations['preferred_status'] = $row['value'];
                }
            }

            $config['environment'] = $configurations['environment'];
            $config['preferred_status'] = $configurations['preferred_status'];
            $config['clientId'] = $configurations['environment'] === 'demo' ? $configurations['demo']['clientId'] : $configurations['live']['clientId'];
            $config['clientSecret'] = $configurations['environment'] === 'demo' ? $configurations['demo']['clientSecret'] : $configurations['live']['clientSecret'];
            $config['sourceCode'] = $configurations['environment'] === 'demo' ? $configurations['demo']['sourceCode'] : $configurations['live']['sourceCode'];
        }

        return $config;
    }

    /**
     * Get error message by errorCode
     *
     * @param string $errorCode
     *
     * @return string
     */
    public function getErrorMessage(string $errorCode): string
    {
        switch ($errorCode) {
            case 'notValidAmount':
                return $this->l('Amount requested is not valid.');
            case 'notValidTransactionId':
                return $this->l('Not Valid Transaction Id.');
            case 'transactionIdNotFound':
                return $this->l('Transaction id not found for this order');
        }

        return $this->l('An error occurred.');
    }

    // </editor-fold>
}
